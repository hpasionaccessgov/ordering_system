<?php

declare(strict_types=1);

namespace app\services;

use app\models\ActivityLog;
use app\models\AdjustPointsForm;
use app\models\LoyaltyPointTransaction;
use app\models\Notification;
use app\models\Order;
use app\models\Reward;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

final class LoyaltyService
{
    public const TIER_BRONZE = 'bronze';
    public const TIER_SILVER = 'silver';
    public const TIER_GOLD = 'gold';

    public static function tierLabels(): array
    {
        return [
            self::TIER_BRONZE => 'Bronze',
            self::TIER_SILVER => 'Silver',
            self::TIER_GOLD => 'Gold',
        ];
    }

    public static function tierFor(int $lifetimeEarned): string
    {
        return match (true) {
            $lifetimeEarned >= 500 => self::TIER_GOLD,
            $lifetimeEarned >= 100 => self::TIER_SILVER,
            default => self::TIER_BRONZE,
        };
    }

    public static function tierLabel(string $tier): string
    {
        return self::tierLabels()[$tier] ?? 'Bronze';
    }

    public static function tierBadgeClass(string $tier): string
    {
        return match ($tier) {
            self::TIER_GOLD => 'text-bg-warning',
            self::TIER_SILVER => 'text-bg-secondary',
            default => 'text-bg-danger',
        };
    }

    /**
     * @return array{current: int, lifetimeEarned: int, lifetimeRedeemed: int, tier: string}
     */
    public static function summary(int $userId): array
    {
        $lifetimeEarned = self::lifetimeEarned($userId);

        return [
            'current' => self::balance($userId),
            'lifetimeEarned' => $lifetimeEarned,
            'lifetimeRedeemed' => self::lifetimeRedeemed($userId),
            'tier' => self::tierFor($lifetimeEarned),
        ];
    }

    public static function balance(int $userId): int
    {
        return (int) LoyaltyPointTransaction::find()
            ->where(['user_id' => $userId])
            ->sum('points');
    }

    public static function lifetimeEarned(int $userId): int
    {
        return (int) LoyaltyPointTransaction::find()
            ->where(['user_id' => $userId, 'transaction_type' => LoyaltyPointTransaction::TYPE_EARN])
            ->sum('points');
    }

    public static function lifetimeRedeemed(int $userId): int
    {
        return abs((int) LoyaltyPointTransaction::find()
            ->where(['user_id' => $userId, 'transaction_type' => LoyaltyPointTransaction::TYPE_REDEEM])
            ->sum('points'));
    }

    /**
     * Award points once for a completed order. Call inside the order transaction.
     */
    public static function earnForCompletedOrder(Order $order): void
    {
        if ((string) $order->status !== Order::STATUS_COMPLETED) {
            return;
        }

        $userId = (int) $order->user_id;
        $exists = LoyaltyPointTransaction::find()
            ->where([
                'user_id' => $userId,
                'order_id' => $order->id,
                'transaction_type' => LoyaltyPointTransaction::TYPE_EARN,
            ])
            ->exists();
        if ($exists) {
            return;
        }

        $points = self::pointsForAmount((float) $order->total_amount);
        if ($points <= 0) {
            return;
        }

        $beforeTier = self::tierFor(self::lifetimeEarned($userId));
        $transaction = new LoyaltyPointTransaction([
            'user_id' => $userId,
            'order_id' => (int) $order->id,
            'points' => $points,
            'transaction_type' => LoyaltyPointTransaction::TYPE_EARN,
            'remarks' => sprintf('Order #%s completed', $order->order_number),
        ]);
        if (!$transaction->save()) {
            throw new \RuntimeException('Loyalty points could not be recorded: ' . json_encode($transaction->errors));
        }

        NotificationService::notifyUser(
            $userId,
            NotificationService::TYPE_LOYALTY_EARNED,
            sprintf('You earned %d loyalty points from Order #%s.', $points, $order->order_number),
            '/loyalty/index',
            null,
            Notification::CATEGORY_LOYALTY,
        );

        $afterTier = self::tierFor(self::lifetimeEarned($userId));
        if ($afterTier !== $beforeTier) {
            NotificationService::notifyUser(
                $userId,
                NotificationService::TYPE_LOYALTY_TIER_UP,
                sprintf('Congratulations! You have reached %s Tier.', self::tierLabel($afterTier)),
                '/loyalty/index',
                null,
                Notification::CATEGORY_LOYALTY,
            );
        }
    }

    public static function redeem(Reward $reward, int $userId): LoyaltyPointTransaction
    {
        return Yii::$app->db->transaction(static function () use ($reward, $userId): LoyaltyPointTransaction {
            $freshReward = Reward::findOne((int) $reward->id);
            if ($freshReward === null || !$freshReward->isRedeemable()) {
                throw new BadRequestHttpException('This reward is no longer available.');
            }
            self::lockUser($userId);

            $cost = (int) $freshReward->point_cost;
            if (self::balance($userId) < $cost) {
                throw new BadRequestHttpException('You do not have enough points to redeem this reward.');
            }

            $order = (new OrderService())->create(
                $userId,
                [['product_id' => (int) $freshReward->product_id, 'quantity' => 1]],
                null,
                0.0,
            );

            $transaction = new LoyaltyPointTransaction([
                'user_id' => $userId,
                'order_id' => $order->id,
                'points' => -$cost,
                'transaction_type' => LoyaltyPointTransaction::TYPE_REDEEM,
                'remarks' => 'Redeemed: ' . $freshReward->name,
            ]);
            if (!$transaction->save()) {
                throw new \RuntimeException('Reward redemption could not be recorded: ' . json_encode($transaction->errors));
            }

            $remaining = self::balance($userId);
            NotificationService::notifyUser(
                $userId,
                NotificationService::TYPE_LOYALTY_REDEEMED,
                sprintf(
                    'You redeemed %s for %s. %d points were deducted. Remaining balance: %d points. Order #%s is now in the queue.',
                    $freshReward->name,
                    $freshReward->product->name,
                    $cost,
                    $remaining,
                    $order->order_number,
                ),
                Navigation::url(Navigation::ORDER_VIEW)[0],
                (int) $order->id,
                Notification::CATEGORY_LOYALTY,
            );
            ActivityLogger::record(ActivityLog::REWARD_REDEEMED, $userId, 'loyalty/redeem');

            return $transaction;
        });
    }

    public static function adjust(AdjustPointsForm $form, int $actorId): void
    {
        Yii::$app->db->transaction(static function () use ($form, $actorId): void {
            $userId = (int) $form->userId;
            self::lockUser($userId);

            $points = (int) $form->points;
            if ($points < 0 && self::balance($userId) + $points < 0) {
                throw new BadRequestHttpException('A manual adjustment cannot make the balance negative.');
            }

            $transaction = new LoyaltyPointTransaction([
                'user_id' => $userId,
                'order_id' => null,
                'points' => $points,
                'transaction_type' => LoyaltyPointTransaction::TYPE_ADJUST,
                'remarks' => trim((string) $form->remarks) . ' (user #' . $userId . ')',
            ]);
            if (!$transaction->save()) {
                throw new \RuntimeException('Points adjustment could not be recorded: ' . json_encode($transaction->errors));
            }

            ActivityLogger::record(ActivityLog::POINTS_ADJUSTED, $actorId, 'admin-loyalty/adjust');
        });
    }

    /**
     * @return list<LoyaltyPointTransaction>
     */
    public static function recentTransactions(int $userId, int $limit = 5): array
    {
        return LoyaltyPointTransaction::find()
            ->where(['user_id' => $userId])
            ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
            ->limit($limit)
            ->all();
    }

    public static function pointsForAmount(float $amount): int
    {
        return (int) floor(max(0.0, $amount) / 100);
    }

    private static function lockUser(int $userId): void
    {
        $found = Yii::$app->db->createCommand(
            'SELECT [[id]] FROM {{%users}} WHERE [[id]] = :id FOR UPDATE',
            [':id' => $userId],
        )->queryScalar();
        if ($found === false || $found === null) {
            throw new NotFoundHttpException('Customer not found.');
        }
    }
}

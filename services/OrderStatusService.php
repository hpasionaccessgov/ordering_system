<?php

declare(strict_types=1);

namespace app\services;

use app\models\Notification;
use app\models\Order;
use app\models\OrderStatusHistory;
use app\models\User;
use Yii;
use yii\web\BadRequestHttpException;

final class OrderStatusService
{
    public function recordCreation(Order $order, int $changedBy): void
    {
        $this->record(
            $order,
            null,
            Order::STATUS_PENDING,
            $changedBy,
        );
    }

    public function process(Order $order, int $changedBy): Order
    {
        $expectedStatus = Order::nextStatusFor((string) $order->status);
        if ($expectedStatus === null) {
            throw new BadRequestHttpException(
                'This order cannot be processed from its current status.',
            );
        }

        return $this->change($order, $expectedStatus, $changedBy, true);
    }

    public function cancel(Order $order, int $changedBy, bool $canProcess): Order
    {
        $allowed = $canProcess
            ? $order->canBeCancelledByStaff()
            : $order->canBeCancelledByCustomer();
        if (!$allowed) {
            throw new BadRequestHttpException(
                'This order cannot be cancelled at its current status.',
            );
        }

        return $this->change($order, Order::STATUS_CANCELLED, $changedBy, $canProcess, false);
    }

    private function change(
        Order $order,
        string $toStatus,
        int $changedBy,
        bool $canProcess,
        bool $isProcessing = true,
    ): Order {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $freshId = Yii::$app->db->createCommand(
                'SELECT id FROM {{%orders}} WHERE id = :id FOR UPDATE',
                [':id' => $order->id],
            )->queryScalar();
            $fresh = $freshId === false ? null : Order::findOne($freshId);
            if ($fresh === null) {
                throw new BadRequestHttpException('Order not found.');
            }

            $expectedNext = $isProcessing
                ? Order::nextStatusFor((string) $fresh->status)
                : Order::STATUS_CANCELLED;
            if ($expectedNext !== $toStatus) {
                throw new BadRequestHttpException(
                    'The order changed while this action was open. Reload and try again.',
                );
            }
            if (!$isProcessing) {
                $canCancel = $canProcess
                    ? $fresh->canBeCancelledByStaff()
                    : $fresh->canBeCancelledByCustomer();
                if (!$canCancel) {
                    throw new BadRequestHttpException(
                        'This order cannot be cancelled at its current status.',
                    );
                }
            }

            if ($toStatus === Order::STATUS_COMPLETED) {
                ProductIngredientService::validate($fresh);
            }

            $fromStatus = (string) $fresh->status;
            $fresh->status = $toStatus;
            $timestampAttributes = $this->timestampAttributesForStatus($toStatus, $fresh);
            $dirtyAttributes = array_merge(['status', 'updated_at', 'updated_by'], $timestampAttributes);
            if (!$fresh->save(false, $dirtyAttributes)) {
                throw new BadRequestHttpException('Order status could not be updated.');
            }
            $this->record($fresh, $fromStatus, $toStatus, $changedBy);
            NotificationService::notifyUser(
                (int) $fresh->user_id,
                NotificationService::TYPE_ORDER_STATUS,
                $this->customerStatusMessage($fresh),
                '/sales/orders',
                (int) $fresh->id,
                Notification::CATEGORY_ORDER,
                (int) $fresh->id,
                $toStatus,
            );
            if (!$isProcessing && $changedBy === (int) $fresh->user_id) {
                $this->notifyOrderManagers($fresh, $changedBy);
            }
            if ($toStatus === Order::STATUS_COMPLETED) {
                ProductIngredientService::deduct($fresh);
                LoyaltyService::earnForCompletedOrder($fresh);
            }
            $transaction->commit();

            return $fresh;
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            throw $exception;
        }
    }

    public function notifyOrderManagers(Order $order, int $actorId): void
    {
        $auth = Yii::$app->authManager;
        $userIds = User::find()->select('id')->where(['status' => User::STATUS_ACTIVE])->column();
        $rows = [];
        $createdAt = time();
        $message = 'Order ' . $order->order_number . ' was cancelled by the customer.';
        foreach ($userIds as $userId) {
            if ((int) $userId === $actorId || (int) $userId === (int) $order->user_id) {
                continue;
            }
            if (($auth->checkAccess($userId, 'admin.access') || $auth->checkAccess($userId, 'staff.access'))
                && $auth->checkAccess($userId, 'order.view')) {
                $rows[] = [
                    $userId,
                    NotificationService::TYPE_ORDER_CANCELLED,
                    $message,
                    '/sales/orders',
                    $order->id,
                    $createdAt,
                    Notification::CATEGORY_ORDER,
                    $order->id,
                    (string) $order->status,
                ];
            }
        }
        if ($rows !== []) {
            Yii::$app->db->createCommand()->batchInsert(
                Notification::tableName(),
                ['user_id', 'type', 'message', 'target_route', 'target_id', 'created_at', 'category', 'order_id', 'status'],
                $rows,
            )->execute();
        }
    }

    private function customerStatusMessage(Order $order): string
    {
        $orderNumber = (string) $order->order_number;
        return match ((string) $order->status) {
            Order::STATUS_READY_FOR_PICKUP => 'Your order ' . $orderNumber . ' is ready for pickup.',
            Order::STATUS_PENDING => 'Your order ' . $orderNumber . ' is pending.',
            Order::STATUS_CONFIRMED => 'Your order ' . $orderNumber . ' has been confirmed.',
            Order::STATUS_PROCESSING => 'Your order ' . $orderNumber . ' is being prepared.',
            Order::STATUS_COMPLETED => 'Your order ' . $orderNumber . ' has been completed.',
            Order::STATUS_CANCELLED => 'Your order ' . $orderNumber . ' has been cancelled.',
            default => 'Your order ' . $orderNumber . ' status was updated.',
        };
    }

    private function record(
        Order $order,
        ?string $fromStatus,
        string $toStatus,
        int $changedBy,
    ): void {
        $history = new OrderStatusHistory([
            'order_id' => $order->id,
            'changed_by' => $changedBy,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'created_at' => time(),
        ]);
        if (!$history->save()) {
            throw new BadRequestHttpException(
                'Order status history could not be saved.',
            );
        }
    }

    /**
     * @return list<string>
     */
    private function timestampAttributesForStatus(string $status, Order $order): array
    {
        $now = time();
        $map = [
            Order::STATUS_CONFIRMED => 'confirmed_at',
            Order::STATUS_PROCESSING => 'processing_at',
            Order::STATUS_READY_FOR_PICKUP => 'ready_at',
            Order::STATUS_COMPLETED => 'completed_at',
        ];
        $attribute = $map[$status] ?? null;
        if ($attribute === null || !$order->hasAttribute($attribute) || $order->$attribute !== null) {
            return [];
        }
        $order->$attribute = $now;

        return [$attribute];
    }
}

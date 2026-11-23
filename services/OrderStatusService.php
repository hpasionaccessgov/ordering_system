<?php

declare(strict_types=1);

namespace app\services;

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

            $fromStatus = (string) $fresh->status;
            $fresh->status = $toStatus;
            if (!$fresh->save(false, ['status', 'updated_at', 'updated_by'])) {
                throw new BadRequestHttpException('Order status could not be updated.');
            }
            $this->record($fresh, $fromStatus, $toStatus, $changedBy);
            Yii::$app->db->createCommand()->insert('{{%notifications}}', [
                'user_id' => $fresh->user_id,
                'order_id' => $fresh->id,
                'status' => $toStatus,
                'target_route' => '/order/view',
                'target_id' => $fresh->id,
                'created_at' => time(),
            ])->execute();
            if (!$isProcessing && $changedBy === (int) $fresh->user_id) {
                $this->notifyOrderManagers($fresh, $changedBy);
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
        foreach ($userIds as $userId) {
            if ((int) $userId === $actorId || (int) $userId === (int) $order->user_id) {
                continue;
            }
            if (($auth->checkAccess($userId, 'admin.access') || $auth->checkAccess($userId, 'staff.access'))
                && $auth->checkAccess($userId, 'order.view')) {
                $rows[] = [$userId, $order->id, $order->status, '/order/view', $order->id, $createdAt];
            }
        }
        if ($rows !== []) {
            Yii::$app->db->createCommand()->batchInsert(
                '{{%notifications}}',
                ['user_id', 'order_id', 'status', 'target_route', 'target_id', 'created_at'],
                $rows,
            )->execute();
        }
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
}

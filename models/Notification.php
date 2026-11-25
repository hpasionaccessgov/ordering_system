<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class Notification extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%notifications}}';
    }

    public static function visibleTo(int|string $userId, bool $canViewAll): ActiveQuery
    {
        $query = self::find()->where(['user_id' => $userId]);
        if (!$canViewAll) {
            $query->andWhere([
                'or',
                ['order_id' => Order::find()->select('id')->where(['user_id' => $userId])],
                ['order_id' => null],
            ]);
        }

        return $query;
    }

    public function getOrder(): ActiveQuery
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    public function getLabel(): string
    {
        if ($this->message !== null && $this->message !== '') {
            return (string) $this->message;
        }
        if ($this->order !== null) {
            $orderNumber = $this->order->order_number;
            return match ((string) $this->status) {
                Order::STATUS_PENDING => 'New order ' . $orderNumber . ' received.',
                Order::STATUS_READY_FOR_PICKUP => 'Order ' . $orderNumber . ' is ready for pickup.',
                default => 'Order ' . $orderNumber . ' is ' . str_replace('_', ' ', (string) $this->status) . '.',
            };
        }

        return ucfirst(str_replace(['-', '_', '.'], ' ', (string) $this->status)) . '.';
    }

    public function getTargetUrl(): array|string
    {
        $route = (string) $this->target_route;
        $targetId = $this->target_id;
        if (str_starts_with($route, '/') && !str_starts_with($route, '//')) {
            if ($targetId !== null) {
                return match ($route) {
                    '/sales/orders', '/order/view' => ['/sales/orders/' . (int) $targetId],
                    '/sales/cart', '/cart/index' => ['/sales/cart'],
                    default => [$route, 'id' => $targetId],
                };
            }
            return [$route];
        }
        if ($this->order !== null && \Yii::$app->user->can('order.view')) {
            return ['/sales/orders/' . (int) $this->order->id];
        }

        return ['/notifications'];
    }
}

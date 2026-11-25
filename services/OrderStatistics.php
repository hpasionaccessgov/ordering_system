<?php

declare(strict_types=1);

namespace app\services;

use app\models\Order;

final class OrderStatistics
{
    /**
     * @return array<string, int>
     */
    public static function statusCounts(): array
    {
        $counts = [
            Order::STATUS_PENDING => 0,
            Order::STATUS_CONFIRMED => 0,
            Order::STATUS_PROCESSING => 0,
            Order::STATUS_READY_FOR_PICKUP => 0,
            Order::STATUS_COMPLETED => 0,
            Order::STATUS_CANCELLED => 0,
        ];

        $rows = Order::find()
            ->select(['status', 'order_count' => 'COUNT(*)'])
            ->groupBy(['status'])
            ->asArray()
            ->all();

        foreach ($rows as $row) {
            $status = (string) $row['status'];
            if (array_key_exists($status, $counts)) {
                $counts[$status] = (int) $row['order_count'];
            }
        }

        return $counts;
    }
}

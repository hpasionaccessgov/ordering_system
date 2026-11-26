<?php

declare(strict_types=1);

namespace app\services;

use app\models\Order;
use yii\db\ActiveQuery;
use Yii;

final class OrderBoardService
{
    public function __construct(
        private ?int $userId = null,
        private bool $canViewAllOrders = true,
    ) {
    }

    /**
     * @return array<string, list<Order>>
     */
    public function boardColumns(): array
    {
        $columns = [];
        foreach ($this->activeStatuses() as $status) {
            $columns[$status] = [];
        }

        $orders = $this->scope(Order::find())
            ->with(['user', 'items'])
            ->andWhere(['status' => $this->activeStatuses()])
            ->orderBy(['created_at' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        foreach ($orders as $order) {
            $status = (string) $order->status;
            if (isset($columns[$status])) {
                $columns[$status][] = $order;
            }
        }

        return $columns;
    }

    /**
     * @return array<string, int>
     */
    public function statusCounts(): array
    {
        $counts = array_fill_keys($this->activeStatuses(), 0);
        $rows = $this->scope(Order::find())
            ->select(['status', 'count' => 'COUNT(*)'])
            ->andWhere(['status' => $this->activeStatuses()])
            ->groupBy(['status'])
            ->asArray()
            ->all();
        foreach ($rows as $row) {
            $status = (string) $row['status'];
            if (array_key_exists($status, $counts)) {
                $counts[$status] = (int) $row['count'];
            }
        }

        return $counts;
    }

    public function completedToday(): int
    {
        if (!$this->hasTimingColumns()) {
            return (int) $this->scope(Order::find())->andWhere(['status' => Order::STATUS_COMPLETED])->count();
        }
        [$from, $to] = $this->todayRange();

        return (int) $this->scope(Order::find())
            ->andWhere(['status' => Order::STATUS_COMPLETED])
            ->andWhere(['between', 'completed_at', $from, $to])
            ->count();
    }

    public function averagePreparationMinutesToday(): ?float
    {
        if (!$this->hasTimingColumns()) {
            return null;
        }
        [$from, $to] = $this->todayRange();
        $rows = $this->scope(Order::find())
            ->select(['processing_at', 'confirmed_at', 'ready_at', 'completed_at'])
            ->andWhere(['status' => Order::STATUS_COMPLETED])
            ->andWhere(['between', 'completed_at', $from, $to])
            ->andWhere([
                'or',
                ['not', ['ready_at' => null]],
                ['not', ['completed_at' => null]],
            ])
            ->asArray()
            ->all();

        $total = 0;
        $count = 0;
        foreach ($rows as $row) {
            $start = $row['processing_at'] ?? $row['confirmed_at'] ?? null;
            $end = $row['ready_at'] ?? $row['completed_at'] ?? null;
            if ($start === null || $end === null) {
                continue;
            }
            $seconds = (int) $end - (int) $start;
            if ($seconds < 0) {
                continue;
            }
            $total += $seconds;
            $count++;
        }

        if ($count === 0) {
            return null;
        }

        return round(($total / $count) / 60, 1);
    }

    private function scope(ActiveQuery $query): ActiveQuery
    {
        if ($this->userId === null || $this->canViewAllOrders) {
            return $query;
        }

        return OrderAccessPolicy::scopeToOwner($query, $this->userId, false);
    }

    private function hasTimingColumns(): bool
    {
        $schema = Yii::$app->db->getTableSchema('{{%orders}}', true);
        return $schema !== null && isset($schema->columns['completed_at']);
    }

    /**
     * @return list<string>
     */
    public function activeStatuses(): array
    {
        return [
            Order::STATUS_PENDING,
            Order::STATUS_CONFIRMED,
            Order::STATUS_PROCESSING,
            Order::STATUS_READY_FOR_PICKUP,
        ];
    }

    /**
     * @return array{0:int,1:int}
     */
    private function todayRange(): array
    {
        $zone = new \DateTimeZone((string) (Yii::$app->formatter->timeZone ?: Yii::$app->timeZone));
        $start = (new \DateTimeImmutable('today', $zone))->getTimestamp();
        $end = (new \DateTimeImmutable('tomorrow', $zone))->getTimestamp() - 1;

        return [$start, $end];
    }
}

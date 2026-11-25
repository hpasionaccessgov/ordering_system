<?php

declare(strict_types=1);

namespace app\services;

use app\models\Order;
use app\models\ReportFilter;
use yii\db\ActiveQuery;

final class OrderReportService
{
    /**
     * @return array{total: int, completed: int, cancelled: int, pending: int, confirmed: int, processing: int, ready_for_pickup: int}
     */
    public function summary(ReportFilter $filter): array
    {
        $counts = array_fill_keys(Order::statusValues(), 0);
        foreach ($this->statusBreakdown($filter) as $row) {
            $counts[$row['status']] = $row['orders'];
        }

        return [
            'total' => array_sum($counts),
            'completed' => $counts[Order::STATUS_COMPLETED],
            'cancelled' => $counts[Order::STATUS_CANCELLED],
            'pending' => $counts[Order::STATUS_PENDING],
            'confirmed' => $counts[Order::STATUS_CONFIRMED],
            'processing' => $counts[Order::STATUS_PROCESSING],
            'ready_for_pickup' => $counts[Order::STATUS_READY_FOR_PICKUP],
        ];
    }

    /**
     * @return list<array{status: string, orders: int}>
     */
    public function statusBreakdown(ReportFilter $filter): array
    {
        $query = Order::find()->alias('o')->select([
            'status' => 'o.status',
            'orders' => 'COUNT(*)',
        ]);
        $this->apply($query, $filter);
        $rows = $query->groupBy(['o.status'])->orderBy(['o.status' => SORT_ASC])->asArray()->all();

        return array_map(
            static fn(array $row): array => [
                'status' => (string) $row['status'],
                'orders' => (int) $row['orders'],
            ],
            $rows,
        );
    }

    /**
     * @return list<array{date: string, orders: int}>
     */
    public function dailyVolume(ReportFilter $filter): array
    {
        $query = Order::find()->alias('o')->select([
            'date' => 'DATE(FROM_UNIXTIME(o.[[created_at]]))',
            'orders' => 'COUNT(*)',
        ]);
        $this->apply($query, $filter);
        $rows = $query->groupBy(['date'])->orderBy(['date' => SORT_ASC])->asArray()->all();

        return array_map(
            static fn(array $row): array => [
                'date' => (string) $row['date'],
                'orders' => (int) $row['orders'],
            ],
            $rows,
        );
    }

    /**
     * @return list<array{id: int, order_number: string, customer: string, status: string, total_amount: float, created_at: int}>
     */
    public function rows(ReportFilter $filter): array
    {
        $query = Order::find()
            ->alias('o')
            ->leftJoin(['u' => '{{%users}}'], 'u.id = o.user_id')
            ->select([
                'id' => 'o.id',
                'order_number' => 'o.order_number',
                'customer' => 'COALESCE(NULLIF(u.full_name, \'\'), u.username)',
                'status' => 'o.status',
                'total_amount' => 'o.total_amount',
                'created_at' => 'o.created_at',
            ]);
        $this->apply($query, $filter);
        $rows = $query->orderBy(['o.created_at' => SORT_DESC, 'o.id' => SORT_DESC])->asArray()->all();

        return array_map(
            static fn(array $row): array => [
                'id' => (int) $row['id'],
                'order_number' => (string) $row['order_number'],
                'customer' => (string) $row['customer'],
                'status' => (string) $row['status'],
                'total_amount' => (float) $row['total_amount'],
                'created_at' => (int) $row['created_at'],
            ],
            $rows,
        );
    }

    private function apply(ActiveQuery $query, ReportFilter $filter): void
    {
        [$from, $to] = $filter->range();
        $query->andWhere(['between', 'o.created_at', $from, $to]);

        if ($filter->status !== '') {
            $query->andWhere(['o.status' => $filter->status]);
        }
        if ($filter->customerId !== null) {
            $query->andWhere(['o.user_id' => $filter->customerId]);
        }
    }
}

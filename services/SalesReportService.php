<?php

declare(strict_types=1);

namespace app\services;

use app\models\Order;
use app\models\ReportFilter;

final class SalesReportService
{
    /**
     * Revenue counts completed orders only; cancelled and pending orders are counted separately.
     *
     * @return array{revenue: float, orders: int, completed: int, cancelled: int, aov: float}
     */
    public function summary(ReportFilter $filter): array
    {
        [$from, $to] = $filter->range();
        $row = Order::find()
            ->select([
                'revenue' => 'COALESCE(SUM(CASE WHEN [[status]] = :completed THEN [[total_amount]] ELSE 0 END), 0)',
                'orders' => 'COUNT(*)',
                'completed' => 'SUM(CASE WHEN [[status]] = :completed THEN 1 ELSE 0 END)',
                'cancelled' => 'SUM(CASE WHEN [[status]] = :cancelled THEN 1 ELSE 0 END)',
            ])
            ->addParams([
                ':completed' => Order::STATUS_COMPLETED,
                ':cancelled' => Order::STATUS_CANCELLED,
            ])
            ->where(['between', 'created_at', $from, $to])
            ->asArray()
            ->one();

        $revenue = (float) ($row['revenue'] ?? 0);
        $completed = (int) ($row['completed'] ?? 0);

        return [
            'revenue' => $revenue,
            'orders' => (int) ($row['orders'] ?? 0),
            'completed' => $completed,
            'cancelled' => (int) ($row['cancelled'] ?? 0),
            'aov' => $completed > 0 ? round($revenue / $completed, 2) : 0.0,
        ];
    }

    /**
     * @return list<array{date: string, revenue: float, orders: int}>
     */
    public function daily(ReportFilter $filter): array
    {
        [$from, $to] = $filter->range();
        $rows = Order::find()
            ->select([
                'date' => 'DATE(FROM_UNIXTIME([[created_at]]))',
                'revenue' => 'COALESCE(SUM(CASE WHEN [[status]] = :completed THEN [[total_amount]] ELSE 0 END), 0)',
                'orders' => 'COUNT(*)',
            ])
            ->addParams([':completed' => Order::STATUS_COMPLETED])
            ->where(['between', 'created_at', $from, $to])
            ->groupBy(['date'])
            ->orderBy(['date' => SORT_ASC])
            ->asArray()
            ->all();

        return array_map(
            static fn(array $row): array => [
                'date' => (string) $row['date'],
                'revenue' => (float) $row['revenue'],
                'orders' => (int) $row['orders'],
            ],
            $rows,
        );
    }

    /**
     * @return array{revenue: float, orders: int, completed: int, cancelled: int, aov: float}
     */
    public function todaySummary(): array
    {
        $today = new ReportFilter();
        $today->preset = 'today';

        return $this->summary($today);
    }
}

<?php

declare(strict_types=1);

namespace app\services;

use app\models\OrderItem;
use app\models\Product;
use app\models\ReportFilter;

final class ProductReportService
{
    /**
     * @return list<array{product_id: int, product_name: string, quantity: int, revenue: float}>
     */
    public function ranking(ReportFilter $filter): array
    {
        [$from, $to] = $filter->range();
        $rows = OrderItem::find()
            ->alias('oi')
            ->innerJoin(['o' => '{{%orders}}'], 'o.id = oi.order_id')
            ->select([
                'product_id' => 'oi.product_id',
                'product_name' => 'oi.product_name',
                'quantity' => 'SUM(oi.quantity)',
                'revenue' => 'SUM(oi.subtotal)',
            ])
            ->where(['between', 'o.created_at', $from, $to])
            ->groupBy(['oi.product_id', 'oi.product_name'])
            ->orderBy(['quantity' => SORT_DESC, 'oi.product_name' => SORT_ASC])
            ->asArray()
            ->all();

        return $this->normalize($rows);
    }

    /**
     * @return list<array{product_id: int, product_name: string, quantity: int, revenue: float}>
     */
    public function top(ReportFilter $filter, int $limit = 10): array
    {
        return array_slice($this->ranking($filter), 0, $limit);
    }

    /**
     * Active products with no sales in range are included with zero quantities.
     *
     * @return list<array{product_id: int, product_name: string, quantity: int, revenue: float}>
     */
    public function least(ReportFilter $filter, int $limit = 10): array
    {
        [$from, $to] = $filter->range();
        $sales = OrderItem::find()
            ->alias('oi')
            ->select(['oi.product_id', 'oi.quantity', 'oi.subtotal'])
            ->innerJoin(['o' => '{{%orders}}'], 'o.id = oi.order_id')
            ->where(['between', 'o.created_at', $from, $to]);

        $rows = Product::find()
            ->alias('p')
            ->leftJoin(['sales' => $sales], 'sales.product_id = p.id')
            ->select([
                'product_id' => 'p.id',
                'product_name' => 'p.name',
                'quantity' => 'COALESCE(SUM(sales.quantity), 0)',
                'revenue' => 'COALESCE(SUM(sales.subtotal), 0)',
            ])
            ->where(['p.status' => Product::STATUS_ACTIVE])
            ->groupBy(['p.id', 'p.name'])
            ->orderBy(['quantity' => SORT_ASC, 'p.name' => SORT_ASC])
            ->limit($limit)
            ->asArray()
            ->all();

        return $this->normalize($rows);
    }

    /**
     * @param array<array<string, mixed>> $rows
     * @return list<array{product_id: int, product_name: string, quantity: int, revenue: float}>
     */
    private function normalize(array $rows): array
    {
        return array_map(
            static fn(array $row): array => [
                'product_id' => (int) $row['product_id'],
                'product_name' => (string) $row['product_name'],
                'quantity' => (int) $row['quantity'],
                'revenue' => (float) $row['revenue'],
            ],
            $rows,
        );
    }
}

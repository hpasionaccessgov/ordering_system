<?php

declare(strict_types=1);

namespace app\services;

use app\models\Inventory;
use app\models\InventoryTransaction;
use app\models\ReportFilter;

final class InventoryReportService
{
    /**
     * @return array{current_stock: int, low_stock: int, out_of_stock: int, fast_moving: int}
     */
    public function summary(ReportFilter $filter): array
    {
        [$from, $to] = $filter->range();
        $fastMoving = InventoryTransaction::find()
            ->where(['transaction_type' => InventoryTransaction::TYPE_DEDUCT])
            ->andWhere(['between', 'created_at', $from, $to])
            ->select('COUNT(DISTINCT [[inventory_id]])')
            ->scalar();

        return [
            'current_stock' => (int) Inventory::find()->sum('current_stock'),
            'low_stock' => (int) Inventory::find()->lowStock()->count(),
            'out_of_stock' => (int) Inventory::find()->outOfStock()->count(),
            'fast_moving' => (int) $fastMoving,
        ];
    }

    /**
     * Deductions only; rate is units consumed per day in the selected range.
     *
     * @return list<array{inventory_id: int, item: string, unit: string, current_stock: int, minimum_stock: int, consumption: int, rate: float}>
     */
    public function consumption(ReportFilter $filter): array
    {
        [$from, $to] = $filter->range();
        $rows = InventoryTransaction::find()
            ->alias('t')
            ->innerJoin(['i' => '{{%inventory}}'], 'i.id = t.inventory_id')
            ->select([
                'inventory_id' => 't.inventory_id',
                'item' => 'i.name',
                'unit' => 'i.unit',
                'current_stock' => 'i.current_stock',
                'minimum_stock' => 'i.minimum_stock',
                'consumption' => 'COALESCE(SUM(-t.quantity), 0)',
                'rate' => 'ROUND(COALESCE(SUM(-t.quantity), 0) / :days, 2)',
            ])
            ->addParams([':days' => $filter->days()])
            ->where(['t.transaction_type' => InventoryTransaction::TYPE_DEDUCT])
            ->andWhere(['between', 't.created_at', $from, $to])
            ->groupBy(['t.inventory_id', 'i.name', 'i.unit', 'i.current_stock', 'i.minimum_stock'])
            ->orderBy(['consumption' => SORT_DESC, 'i.name' => SORT_ASC])
            ->asArray()
            ->all();

        return array_map(
            static fn(array $row): array => [
                'inventory_id' => (int) $row['inventory_id'],
                'item' => (string) $row['item'],
                'unit' => (string) $row['unit'],
                'current_stock' => (int) $row['current_stock'],
                'minimum_stock' => (int) $row['minimum_stock'],
                'consumption' => (int) $row['consumption'],
                'rate' => (float) $row['rate'],
            ],
            $rows,
        );
    }

    /**
     * @return list<array{id: int, name: string, unit: string, current_stock: int, minimum_stock: int}>
     */
    public function levels(): array
    {
        $rows = Inventory::find()
            ->active()
            ->select(['id', 'name', 'unit', 'current_stock', 'minimum_stock'])
            ->orderBy(['current_stock' => SORT_ASC, 'name' => SORT_ASC])
            ->asArray()
            ->all();

        return array_map(
            static fn(array $row): array => [
                'id' => (int) $row['id'],
                'name' => (string) $row['name'],
                'unit' => (string) $row['unit'],
                'current_stock' => (int) $row['current_stock'],
                'minimum_stock' => (int) $row['minimum_stock'],
            ],
            $rows,
        );
    }
}

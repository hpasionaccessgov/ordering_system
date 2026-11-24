<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\models\Inventory;
use app\services\InventoryService;
use Codeception\Test\Unit;

final class InventoryTest extends Unit
{
    public function testLowStockAndOutOfStockDetection(): void
    {
        $milk = new Inventory([
            'name' => 'Milk',
            'unit' => 'Liter',
            'current_stock' => 3,
            'minimum_stock' => 5,
            'status' => Inventory::STATUS_ACTIVE,
        ]);

        self::assertTrue($milk->isLowStock());
        self::assertFalse($milk->isOutOfStock());
        self::assertSame('Active', $milk->getStatusLabel());
        self::assertSame(
            'Low stock alert: Milk has only 3 liters remaining.',
            InventoryService::lowStockMessage($milk),
        );

        $cups = new Inventory(['current_stock' => 0, 'minimum_stock' => 0]);
        self::assertTrue($cups->isLowStock());
        self::assertTrue($cups->isOutOfStock());

        $beans = new Inventory(['current_stock' => 20, 'minimum_stock' => 5]);
        self::assertFalse($beans->isLowStock());
        self::assertFalse($beans->isOutOfStock());
    }
}

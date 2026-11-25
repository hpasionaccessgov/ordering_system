<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\models\Inventory;
use app\models\Product;
use app\models\ProductIngredient;
use Codeception\Test\Unit;

final class ProductStockStatusTest extends Unit
{
    public function testStockStatusUsesRecipeInventory(): void
    {
        self::assertSame('in', $this->product(10, 5, 2)->stockStatus());
        self::assertSame('low', $this->product(3, 5, 2)->stockStatus());
        self::assertSame('out', $this->product(0, 5, 2)->stockStatus());
        self::assertSame('out', $this->product(10, 5, 2, Product::STATUS_ARCHIVED)->stockStatus());
    }

    public function testStockBadgeMatchesStatus(): void
    {
        self::assertSame('text-bg-success', $this->product(10, 5, 2)->stockBadgeClass());
        self::assertSame('Low Stock', $this->product(3, 5, 2)->stockLabel());
        self::assertSame('text-bg-danger', $this->product(0, 5, 2)->stockBadgeClass());
    }

    private function product(
        int $currentStock,
        int $minimumStock,
        int $required,
        int $status = Product::STATUS_ACTIVE,
    ): Product {
        $inventory = new Inventory([
            'current_stock' => $currentStock,
            'minimum_stock' => $minimumStock,
        ]);
        $ingredient = new ProductIngredient(['quantity_required' => $required]);
        $ingredient->populateRelation('inventory', $inventory);

        $product = new Product(['status' => $status]);
        $product->populateRelation('productIngredients', [$ingredient]);

        return $product;
    }
}

<?php

declare(strict_types=1);

namespace app\commands\seeders;

use app\models\Inventory;
use app\models\Product;
use app\services\InventoryService;
use app\services\ProductIngredientService;
use RuntimeException;

final class InventorySeeder
{
    /** @var array<string, array{unit: string, current: int, minimum: int}> */
    private const ITEMS = [
        'Coffee Beans' => ['unit' => 'sachet', 'current' => 500, 'minimum' => 100],
        'Milk' => ['unit' => 'liter', 'current' => 60, 'minimum' => 12],
        'Coffee Cup' => ['unit' => 'cup', 'current' => 300, 'minimum' => 60],
        'Brown Sugar' => ['unit' => 'sachet', 'current' => 250, 'minimum' => 50],
        'Cinnamon' => ['unit' => 'sachet', 'current' => 120, 'minimum' => 25],
        'Oat Milk' => ['unit' => 'liter', 'current' => 40, 'minimum' => 10],
        'Cocoa Powder' => ['unit' => 'sachet', 'current' => 200, 'minimum' => 40],
        'Cookie Mix' => ['unit' => 'sachet', 'current' => 200, 'minimum' => 40],
        'Butter' => ['unit' => 'gram', 'current' => 2000, 'minimum' => 400],
        'Croissant Dough' => ['unit' => 'piece', 'current' => 150, 'minimum' => 30],
    ];

    /** @var array<string, array<string, int>> */
    private const RECIPES = [
        'Cafebara House Latte' => [
            'Coffee Beans' => 1,
            'Milk' => 1,
            'Coffee Cup' => 1,
        ],
        'Brown Sugar Cold Brew' => [
            'Coffee Beans' => 1,
            'Brown Sugar' => 2,
            'Milk' => 1,
            'Coffee Cup' => 1,
        ],
        'Cinnamon Oat Cappuccino' => [
            'Coffee Beans' => 1,
            'Oat Milk' => 1,
            'Cinnamon' => 1,
            'Coffee Cup' => 1,
        ],
        'Dark Chocolate Cookie' => [
            'Cocoa Powder' => 2,
            'Cookie Mix' => 1,
        ],
        'Butter Croissant' => [
            'Croissant Dough' => 1,
            'Butter' => 20,
        ],
    ];

    /**
     * @return array{items: int, recipes: int}
     */
    public function run(): array
    {
        $items = 0;
        foreach (self::ITEMS as $name => $values) {
            if (Inventory::findOne(['name' => $name]) !== null) {
                continue;
            }

            $inventory = new Inventory([
                'name' => $name,
                'unit' => $values['unit'],
                'current_stock' => $values['current'],
                'minimum_stock' => $values['minimum'],
                'status' => Inventory::STATUS_ACTIVE,
            ]);
            if (!InventoryService::create($inventory)) {
                throw new RuntimeException(
                    "Could not seed inventory item {$name}: " . json_encode($inventory->getErrors()),
                );
            }
            $items++;
        }

        $recipes = 0;
        foreach (self::RECIPES as $productName => $lines) {
            $product = Product::findOne(['name' => $productName]);
            if ($product === null) {
                throw new RuntimeException("Product {$productName} is required before seeding recipes.");
            }

            $rows = [];
            foreach ($lines as $inventoryName => $quantity) {
                $inventory = Inventory::findOne(['name' => $inventoryName]);
                if ($inventory === null) {
                    throw new RuntimeException("Inventory item {$inventoryName} is required before seeding recipes.");
                }
                $rows[] = [
                    'inventory_id' => $inventory->id,
                    'quantity_required' => $quantity,
                ];
            }

            if (!ProductIngredientService::sync($product, $rows)) {
                throw new RuntimeException(
                    "Could not seed recipe for {$productName}: " . $product->getFirstError('ingredients'),
                );
            }
            $recipes++;
        }

        return ['items' => $items, 'recipes' => $recipes];
    }
}

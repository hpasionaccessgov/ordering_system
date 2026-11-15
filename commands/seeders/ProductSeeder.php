<?php

declare(strict_types=1);

namespace app\commands\seeders;

use app\models\Product;

class ProductSeeder
{
    public function run(): int
    {
        $records = [
            ['Cafebara House Latte', 'Espresso, steamed milk, and a soft caramel finish.', 'Coffee', 145.00, true],
            ['Brown Sugar Cold Brew', 'Slow-steeped coffee with brown sugar and cream.', 'Coffee', 160.00, true],
            ['Cinnamon Oat Cappuccino', 'A balanced cappuccino with cinnamon and oat milk.', 'Coffee', 155.00, false],
            ['Dark Chocolate Cookie', 'Cocoa-rich cookie baked for the afternoon break.', 'Pastries', 85.00, true],
            ['Butter Croissant', 'Flaky, lightly golden, and baked fresh each morning.', 'Pastries', 95.00, false],
        ];
        $added = 0;

        foreach ($records as [$name, $description, $category, $price, $featured]) {
            if (Product::find()->where(['name' => $name])->exists()) {
                continue;
            }

            $product = new Product([
                'name' => $name,
                'description' => $description,
                'category' => $category,
                'price' => number_format($price, 2, '.', ''),
                'status' => Product::STATUS_ACTIVE,
                'is_featured' => $featured,
            ]);

            if (!$product->save()) {
                throw new \RuntimeException("Could not seed product {$name}: " . json_encode($product->getErrors()));
            }
            $added++;
        }

        return $added;
    }
}

<?php

declare(strict_types=1);

namespace app\commands\seeders;

use app\models\Category;
use RuntimeException;

final class CategorySeeder
{
    private const NAMES = ['Coffee', 'Pastries'];

    public function run(): int
    {
        $added = 0;
        foreach (self::NAMES as $name) {
            if (Category::findOne(['name' => $name]) !== null) {
                continue;
            }

            $category = new Category(['name' => $name, 'status' => Category::STATUS_ACTIVE]);
            if (!$category->save()) {
                throw new RuntimeException(
                    "Could not seed category {$name}: " . json_encode($category->getErrors()),
                );
            }
            $added++;
        }

        return $added;
    }
}

<?php

declare(strict_types=1);

namespace app\commands\seeders;

use app\models\Category;
use app\models\Product;
use RuntimeException;
use Throwable;
use Yii;
use yii\db\Transaction;

class ProductSeeder
{
    public function run(): int
    {
        $records = [
            [
                'Cafebara House Latte',
                'Espresso, steamed milk, and a soft caramel finish.',
                'Coffee',
                145.00,
                true,
                true,
                'house_latte.webp',
            ],
            [
                'Brown Sugar Cold Brew',
                'Slow-steeped coffee with brown sugas ar and cream.',
                'Coffee',
                160.00,
                true,
                false,
                'Brown_Sugar_Cold_Brew.jpg',
            ],
            [
                'Cinnamon Oat Cappuccino',
                'A balanced cappuccino with cinnamon and oat milk.',
                'Coffee',
                155.00,
                false,
                false,
                'Cinnamon_Oat_Cappuccino.webp',
            ],
            [
                'Dark Chocolate Cookie',
                'Cocoa-rich cookie baked for the afternoon break.',
                'Pastries',
                85.00,
                true,
                true,
                'Dark_Chocolate_Cookie.webp',
            ],
            [
                'Butter Croissant',
                'Flaky, lightly golden, and baked fresh each morning.',
                'Pastries',
                95.00,
                false,
                false,
                'Butter_Croissant.webp',
            ],
        ];
        $added = 0;

        foreach ($records as [$name, $description, $category, $price, $featured, $special, $imageName]) {
            if (Product::findOne(['name' => $name]) !== null) {
                continue;
            }

            $categoryModel = Category::findOne([
                'name' => $category,
                'status' => Category::STATUS_ACTIVE,
            ]);
            if ($categoryModel === null) {
                throw new RuntimeException(
                    "Active category {$category} is required before seeding products.",
                );
            }

            [$imageData, $imageMime] = $this->loadImage($imageName);
            $transaction = Product::getDb()->beginTransaction();

            try {
                $product = new Product();
                $product->setAttributes([
                    'name' => $name,
                    'description' => $description,
                    'category_id' => $categoryModel->id,
                    'price' => number_format($price, 2, '.', ''),
                    'status' => Product::STATUS_ACTIVE,
                    'is_featured' => $featured,
                    'is_special' => $special,
                    'image_data' => $imageData,
                    'image_mime' => $imageMime,
                    'image_name' => $imageName,
                    'image_path' => null,
                ], false);

                if (!$product->save()) {
                    throw new RuntimeException(
                        "Could not seed product {$name}: " . json_encode($product->getErrors()),
                    );
                }

                $transaction->commit();
                $added++;
            } catch (Throwable $exception) {
                $this->rollback($transaction);

                throw $exception;
            }
        }

        return $added;
    }

    /**
     * @return array{string, string}
     */
    private function loadImage(string $imageName): array
    {
        $path = Yii::getAlias('@app')
            . DIRECTORY_SEPARATOR . 'web'
            . DIRECTORY_SEPARATOR . 'images'
            . DIRECTORY_SEPARATOR . 'products'
            . DIRECTORY_SEPARATOR . $imageName;

        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException("Product image is missing or unreadable: {$imageName}");
        }

        $imageData = file_get_contents($path);
        $imageMime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);

        if (
            $imageData === false
            || !in_array($imageMime, ['image/jpeg', 'image/png', 'image/webp'], true)
        ) {
            throw new RuntimeException("Product image is invalid: {$imageName}");
        }

        return [$imageData, $imageMime];
    }

    private function rollback(Transaction $transaction): void
    {
        if (!$transaction->isActive) {
            return;
        }

        try {
            $transaction->rollBack();
        } catch (Throwable $rollbackException) {
            Yii::error(
                'Product seeder rollback failed after the database connection was lost: '
                . $rollbackException->getMessage(),
                __METHOD__,
            );
        }
    }
}

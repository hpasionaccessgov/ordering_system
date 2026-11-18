<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\models\Category;
use app\models\Product;
use app\services\ProductCatalogPolicy;
use Codeception\Test\Unit;
use Yii;
use yii\db\IntegrityException;

final class ProductCategoryTest extends Unit
{
    public function testCategoryMustExistAndBeActiveForNewAssignments(): void
    {
        $active = $this->createCategory(Category::STATUS_ACTIVE);
        $inactive = $this->createCategory(Category::STATUS_INACTIVE);
        $missingId = (int) Category::find()->max('id') + 1;

        foreach ([null, 'invalid', $missingId, $inactive->id] as $categoryId) {
            $product = new Product();
            $product->category_id = $categoryId;
            self::assertFalse($product->validate(['category_id']));
            self::assertNotEmpty($product->getErrors('category_id'));
        }

        $product = new Product();
        $product->category_id = $active->id;
        self::assertTrue($product->validate(['category_id']));

        $product->setOldAttributes(['category_id' => $inactive->id]);
        $product->category_id = $inactive->id;
        self::assertTrue($product->validate(['category_id']));

        $product->setOldAttributes(['category_id' => $active->id]);
        self::assertFalse($product->validate(['category_id']));
    }

    public function testDatabaseRejectsInvalidReferencesAndReferencedCategoryDeletion(): void
    {
        $category = $this->createCategory(Category::STATUS_ACTIVE);
        $missingId = (int) Category::find()->max('id') + 1;
        $attributes = [
            'name' => 'Category reference test',
            'price' => '1.00',
            'status' => Product::STATUS_ACTIVE,
            'created_at' => time(),
            'updated_at' => time(),
        ];

        foreach ([null, $missingId] as $categoryId) {
            try {
                Yii::$app->db->createCommand()->insert(
                    Product::tableName(),
                    $attributes + ['category_id' => $categoryId],
                )->execute();
                self::fail('The database accepted an invalid category reference.');
            } catch (IntegrityException $exception) {
                self::assertNotEmpty($exception->errorInfo);
            }
        }

        Yii::$app->db->createCommand()->insert(
            Product::tableName(),
            $attributes + ['category_id' => $category->id],
        )->execute();
        try {
            $category->delete();
            self::fail('The database allowed deletion of a referenced category.');
        } catch (IntegrityException $exception) {
            self::assertNotEmpty($exception->errorInfo);
        }
    }

    public function testActiveProductRemainsVisibleWhenItsCategoryBecomesInactive(): void
    {
        $category = $this->createCategory(Category::STATUS_ACTIVE);
        $product = new Product([
            'name' => 'Visible product',
            'category_id' => $category->id,
            'price' => '1.00',
            'status' => Product::STATUS_ACTIVE,
        ]);
        self::assertTrue($product->save(), json_encode($product->getErrors()));
        $category->status = Category::STATUS_INACTIVE;
        self::assertTrue($category->save(), json_encode($category->getErrors()));

        $query = ProductCatalogPolicy::applyStatusFilter(
            Product::find()->where(['id' => $product->id]),
            'active',
        );
        self::assertTrue($query->exists());

        $product->status = Product::STATUS_ARCHIVED;
        self::assertTrue($product->save(), json_encode($product->getErrors()));
        self::assertFalse($query->exists());
    }

    private function createCategory(int $status): Category
    {
        $category = new Category([
            'name' => 'Category reference test',
            'status' => $status,
        ]);
        self::assertTrue($category->save(), json_encode($category->getErrors()));

        return $category;
    }
}

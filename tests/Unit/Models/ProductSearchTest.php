<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\models\Category;
use app\models\ApplicationSettings;
use app\models\Product;
use app\models\ProductSearch;
use Codeception\Test\Unit;

final class ProductSearchTest extends Unit
{
    public function testFiltersKeepCatalogAndPublicVisibilityRules(): void
    {
        $suffix = bin2hex(random_bytes(4));
        $category = new Category(['name' => 'Search ' . $suffix, 'status' => Category::STATUS_ACTIVE]);
        self::assertTrue($category->save(), json_encode($category->getErrors()));
        $products = [];
        foreach (
            [
                ['active low', Product::STATUS_ACTIVE, '5.00'],
                ['active high', Product::STATUS_ACTIVE, '15.00'],
                ['archived', Product::STATUS_ARCHIVED, '25.00'],
            ] as [$name, $status, $price]
        ) {
            $product = new Product([
                'name' => $suffix . ' ' . $name,
                'category_id' => $category->id,
                'status' => $status,
                'price' => $price,
            ]);
            self::assertTrue($product->save(), json_encode($product->getErrors()));
            $products[] = $product;
        }

        $filters = ['q' => '  ' . $suffix . '  ', 'category_id' => (string) $category->id, 'status' => 'archived'];
        $manager = new ProductSearch();
        $managerProvider = $manager->search($filters, true, true);
        self::assertSame($suffix, $manager->q);
        self::assertSame([(int) $products[2]->id], array_map(static fn(Product $product): int => (int) $product->id, $managerProvider->getModels()));
        self::assertSame((int) ApplicationSettings::current()->card_page_size, $managerProvider->pagination->getPageSize());

        $customer = new ProductSearch();
        $customerProvider = $customer->search($filters + ['min_price' => '10'], true, false);
        self::assertSame([(int) $products[1]->id], array_map(static fn(Product $product): int => (int) $product->id, $customerProvider->getModels()));
        self::assertSame('active', $customer->catalogStatus);
        self::assertSame(0, (new ProductSearch())->search($filters, false, true)->getTotalCount());
        self::assertSame(0, (new ProductSearch())->search(['category_id' => 'invalid'], true, true)->getTotalCount());

        $public = (new ProductSearch())->searchMenu($filters);
        self::assertEqualsCanonicalizing(
            [(int) $products[0]->id, (int) $products[1]->id],
            array_map(static fn(Product $product): int => (int) $product->id, $public->getModels()),
        );
        self::assertSame((int) ApplicationSettings::current()->card_page_size, $public->pagination->getPageSize());
    }
}

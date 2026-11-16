<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\models\Product;
use app\services\ProductCatalogPolicy;
use Codeception\Test\Unit;
use yii\db\ActiveQuery;

final class ProductCatalogPolicyTest extends Unit
{
    public function testCustomersAlwaysSeeOnlyActiveProducts(): void
    {
        self::assertSame(
            'active',
            ProductCatalogPolicy::normalizeStatus(false, 'archived'),
        );

        $query = ProductCatalogPolicy::applyStatusFilter(
            new ActiveQuery(Product::class),
            'active',
        );

        self::assertSame(['status' => Product::STATUS_ACTIVE], $query->where);
    }

    public function testManagersCanFilterArchivedProducts(): void
    {
        self::assertSame(
            'archived',
            ProductCatalogPolicy::normalizeStatus(true, 'archived'),
        );

        $query = ProductCatalogPolicy::applyStatusFilter(
            new ActiveQuery(Product::class),
            'archived',
        );

        self::assertSame(['status' => Product::STATUS_ARCHIVED], $query->where);
    }

    public function testInvalidManagerFilterFallsBackToAllProducts(): void
    {
        self::assertSame(
            'all',
            ProductCatalogPolicy::normalizeStatus(true, 'unexpected'),
        );

        $query = ProductCatalogPolicy::applyStatusFilter(
            new ActiveQuery(Product::class),
            'all',
        );

        self::assertNull($query->where);
    }
}

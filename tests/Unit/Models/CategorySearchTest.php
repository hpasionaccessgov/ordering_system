<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\models\CategorySearch;
use app\models\ApplicationSettings;
use Codeception\Test\Unit;

final class CategorySearchTest extends Unit
{
    public function testSearchMatchesCategoryFieldsAndLimitsPageSize(): void
    {
        $search = $this->getMockBuilder(CategorySearch::class)
            ->onlyMethods(['attributes', '__get'])
            ->getMock();
        $search->method('attributes')->willReturn([
            'id', 'name', 'description', 'status', 'created_at', 'updated_at',
        ]);
        $search->method('__get')->willReturn(null);

        $provider = $search->search(['q' => '  12  ']);

        self::assertSame([
            'or',
            ['like', 'name', '12'],
            ['like', 'description', '12'],
            ['id' => '12'],
        ], $provider->query->where);
        self::assertSame((int) ApplicationSettings::current()->table_page_size, $provider->pagination->getPageSize());
    }

    public function testSearchHandlesInvalidInputWithoutThrowing(): void
    {
        $search = $this->getMockBuilder(CategorySearch::class)
            ->onlyMethods(['attributes', '__get'])
            ->getMock();
        $search->method('attributes')->willReturn([
            'id', 'name', 'description', 'status', 'created_at', 'updated_at',
        ]);
        $search->method('__get')->willReturn(null);

        $search->search(['q' => ['invalid']]);

        self::assertTrue($search->hasErrors('q'));
    }
}

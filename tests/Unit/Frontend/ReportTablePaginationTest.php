<?php

declare(strict_types=1);

namespace app\tests\Unit\Frontend;

use Codeception\Test\Unit;

final class ReportTablePaginationTest extends Unit
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 3) . '/' . $path);
    }

    public function testSharedReportTableUsesTheConfiguredTablePageSize(): void
    {
        $table = $this->source('views/report/_table.php');

        self::assertStringContainsString('ApplicationSettings::pagination()', $table);
        self::assertStringContainsString('ArrayDataProvider', $table);
        self::assertStringContainsString('LinkPager::widget', $table);
        self::assertStringContainsString('$pageParam', $table);
    }

    /**
     * @dataProvider reportPageParams
     */
    public function testEachReportUsesItsOwnPageParameter(string $view, string $pageParam): void
    {
        self::assertStringContainsString(
            "'pageParam' => '" . $pageParam . "'",
            $this->source('views/report/' . $view . '.php'),
        );
    }

    /**
     * @return list<array{string, string}>
     */
    public static function reportPageParams(): array
    {
        return [
            ['sales', 'salesPage'],
            ['product', 'topProductsPage'],
            ['product', 'leastProductsPage'],
            ['orders', 'ordersPage'],
            ['inventory', 'inventoryPage'],
            ['loyalty', 'loyaltyPage'],
        ];
    }
}

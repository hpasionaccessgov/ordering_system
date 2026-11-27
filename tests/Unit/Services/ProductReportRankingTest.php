<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use Codeception\Test\Unit;

final class ProductReportRankingTest extends Unit
{
    public function testRankingCountsOnlyCompletedOrders(): void
    {
        $service = $this->methodSource('public function ranking', 'public function top');

        self::assertStringContainsString("['between', 'o.created_at', \$from, \$to]", $service);
        self::assertStringContainsString("'o.status' => Order::STATUS_COMPLETED", $service);
    }

    public function testLeastSellerSubqueryCountsOnlyCompletedOrders(): void
    {
        $service = $this->methodSource('public function least', 'private function normalize');

        self::assertStringContainsString("['between', 'o.created_at', \$from, \$to]", $service);
        self::assertStringContainsString("'o.status' => Order::STATUS_COMPLETED", $service);
    }

    private function methodSource(string $startSignature, string $endSignature): string
    {
        $source = (string) file_get_contents(dirname(__DIR__, 3) . '/services/ProductReportService.php');
        $start = strpos($source, $startSignature);
        $end = strpos($source, $endSignature, $start === false ? 0 : $start);

        self::assertNotFalse($start);
        self::assertNotFalse($end);

        return substr($source, $start, $end - $start);
    }
}

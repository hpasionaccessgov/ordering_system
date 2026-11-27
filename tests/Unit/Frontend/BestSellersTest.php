<?php

declare(strict_types=1);

namespace app\tests\Unit\Frontend;

use Codeception\Test\Unit;

final class BestSellersTest extends Unit
{
    public function testRanksComeFromListOrderWhenTheServiceHasNoRankField(): void
    {
        $view = (string) file_get_contents(dirname(__DIR__, 3) . '/views/shared/_best_sellers.php');

        self::assertStringContainsString("\$item['top_sellers_rank'] ?? (\$index + 1)", $view);
        self::assertStringContainsString("'#' . \$rank", $view);
        self::assertStringContainsString("'data-rank' => \$rank", $view);
    }

    public function testRankChipFollowsColorModeTokens(): void
    {
        $css = (string) file_get_contents(dirname(__DIR__, 3) . '/web/css/cafebara-theme.css');

        self::assertStringContainsString('.best-seller-rank[data-rank="1"]', $css);
        self::assertStringContainsString('background: var(--bs-tertiary-bg);', $css);
    }
}

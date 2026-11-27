<?php

declare(strict_types=1);

namespace app\tests\Unit\Frontend;

use Codeception\Test\Unit;

final class KitchenBoardTest extends Unit
{
    public function testTicketRailsScrollInsideTheirOwnContainer(): void
    {
        $css = (string) file_get_contents(dirname(__DIR__, 3) . '/web/css/cafebara-theme.css');

        self::assertMatchesRegularExpression(
            '/\.kitchen-rail-body\s*\{[^}]*max-height:\s*clamp\([^}]*overflow-y:\s*auto/s',
            $css,
        );
        self::assertStringContainsString('overscroll-behavior: contain;', $css);
    }

    public function testScrollableRailIsKeyboardFocusable(): void
    {
        $view = (string) file_get_contents(dirname(__DIR__, 3) . '/views/kitchen/index.php');

        self::assertStringContainsString('class="kitchen-rail-body" tabindex="0"', $view);
    }
}

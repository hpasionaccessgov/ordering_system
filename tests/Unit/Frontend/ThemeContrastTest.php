<?php

declare(strict_types=1);

namespace app\tests\Unit\Frontend;

use Codeception\Test\Unit;

final class ThemeContrastTest extends Unit
{
    private string $css;

    protected function _before(): void
    {
        $this->css = (string) file_get_contents(dirname(__DIR__, 3) . '/web/css/cafebara-theme.css');
    }

    public function testDarkThemeOverridesInCardPrimaryButtons(): void
    {
        self::assertMatchesRegularExpression(
            '/\[data-bs-theme="dark"\] \.btn-outline-primary\s*\{[^}]*--bs-btn-color:\s*var\(--cafebara-cream\)/s',
            $this->css,
        );
        self::assertMatchesRegularExpression(
            '/\[data-bs-theme="dark"\] \.btn-outline-primary\s*\{[^}]*--bs-btn-border-color:\s*rgba\(215, 184, 151, \.72\)/s',
            $this->css,
        );
    }

    public function testDarkThemeOverridesPrimaryLinks(): void
    {
        self::assertMatchesRegularExpression(
            '/\[data-bs-theme="dark"\] \.link-primary\s*\{[^}]*color:\s*#e9b88c\s*!important/s',
            $this->css,
        );
    }
}

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

    public function testDarkThemeOverridesBrownTextOnThemedSurfaces(): void
    {
        self::assertMatchesRegularExpression(
            '/\[data-bs-theme="dark"\] \.product-card-price,[\s\S]*?\[data-bs-theme="dark"\] \.password-toggle-button:hover\s*\{\s*color:\s*var\(--cafebara-cream\)/',
            $this->css,
        );
        self::assertMatchesRegularExpression(
            '/\[data-bs-theme="dark"\] \.app-content-header h1\s*\{\s*color:\s*var\(--bs-body-color\)/',
            $this->css,
        );
    }

    public function testLoyaltyProgressPanelUsesThemeAwareSurface(): void
    {
        $view = (string) file_get_contents(dirname(__DIR__, 3) . '/views/loyalty/index.php');

        self::assertStringContainsString('bg-body-tertiary', $view);
        self::assertStringNotContainsString('bg-light text-dark', $view);
    }

    public function testReportChartsReadBodyColorToken(): void
    {
        $view = (string) file_get_contents(dirname(__DIR__, 3) . '/views/report/_chart.php');

        self::assertMatchesRegularExpression(
            '/getComputedStyle\(document\.documentElement\)\s*\.getPropertyValue\(\'--bs-body-color\'\)/',
            $view,
        );
    }

    public function testThemeToggleBroadcastsTheNewMode(): void
    {
        $script = (string) file_get_contents(dirname(__DIR__, 3) . '/web/js/color-mode.js');

        self::assertStringContainsString('cafebara:themechange', $script);
        self::assertStringContainsString('new CustomEvent', $script);
    }

    public function testEveryChartRefreshesWhenThemeChangesWithoutReload(): void
    {
        $script = (string) file_get_contents(dirname(__DIR__, 3) . '/web/js/color-mode.js');

        self::assertStringContainsString('cafebara:themechange', $script);
        self::assertStringContainsString('document.querySelectorAll("canvas")', $script);
        self::assertStringContainsString('window.Chart.getChart(canvas)', $script);
        self::assertStringContainsString('chart.update("none")', $script);
    }
}

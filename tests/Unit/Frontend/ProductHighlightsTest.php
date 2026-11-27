<?php

declare(strict_types=1);

namespace app\tests\Unit\Frontend;

use Codeception\Test\Unit;

final class ProductHighlightsTest extends Unit
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 3) . '/' . $path);
    }

    public function testSharedPartialRendersBothSectionsAndDeduplicatesBatchInputs(): void
    {
        $partial = $this->source('views/shared/_product_highlights.php');

        self::assertStringContainsString('Today’s specials', $partial);
        self::assertStringContainsString('Featured drinks & bites', $partial);
        self::assertStringContainsString("in_array((int) \$product->id, \$specialIds, true) ? null : \$batchFormId", $partial);
        self::assertStringContainsString('highlights-batch-cart', $partial);
        self::assertStringContainsString('Navigation::url(Navigation::MENU)', $partial);
    }

    public function testOnlyHomeAndDashboardsRenderTheSharedHighlights(): void
    {
        foreach ([
            'views/site/index.php',
            'views/customer/index.php',
            'views/staff/index.php',
            'views/admin/index.php',
        ] as $view) {
            self::assertStringContainsString(
                '_product_highlights',
                $this->source($view),
                $view . ' does not render the highlights partial',
            );
        }
        foreach (['views/site/menu.php', 'views/product/index.php'] as $view) {
            self::assertStringNotContainsString(
                '_product_highlights',
                $this->source($view),
                $view . ' should list all products without highlights',
            );
        }
    }

    public function testControllersSupplyBothHighlightListsOnlyToHighlightViews(): void
    {
        foreach ([
            'controllers/SiteController.php',
            'controllers/CustomerController.php',
            'controllers/StaffController.php',
            'controllers/AdminController.php',
        ] as $controller) {
            foreach (["'specialProducts' =>", "'featuredProducts' =>"] as $key) {
                self::assertStringContainsString(
                    $key,
                    $this->source($controller),
                    $controller . ' does not pass ' . $key,
                );
            }
        }
        foreach (['controllers/ProductController.php'] as $controller) {
            self::assertStringNotContainsString("'specialProducts' =>", $this->source($controller));
            self::assertStringNotContainsString("'featuredProducts' =>", $this->source($controller));
        }
    }

    public function testHighlightSurfaceFollowsColorModeTokens(): void
    {
        self::assertStringContainsString(
            '.cafebara-product-highlights {',
            $this->source('web/css/cafebara-theme.css'),
        );
        self::assertStringContainsString(
            'background-color: var(--bs-body-bg);',
            $this->source('web/css/cafebara-theme.css'),
        );
    }
}

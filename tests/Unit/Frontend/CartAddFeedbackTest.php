<?php

declare(strict_types=1);

namespace app\tests\Unit\Frontend;

use Codeception\Test\Unit;

final class CartAddFeedbackTest extends Unit
{
    public function testMiniCartRendersToastWithViewCartAction(): void
    {
        $view = (string) file_get_contents(dirname(__DIR__, 3) . '/views/layouts/_mini_cart.php');

        self::assertStringContainsString('id="cafebara-cart-toast"', $view);
        self::assertStringContainsString('data-cart-toast-message', $view);
        self::assertStringContainsString("'data-bs-target' => '#cafebara-cart-offcanvas'", $view);
    }

    public function testAjaxCartReplyShowsToastWithNewCount(): void
    {
        $js = (string) file_get_contents(dirname(__DIR__, 3) . '/web/js/mini-cart.js');

        self::assertStringContainsString('showToast(data.message, data.count)', $js);
        self::assertStringContainsString('data-cart-toast-message', $js);
        self::assertStringContainsString('window.bootstrap.Toast.getOrCreateInstance(toast, {delay: 4000}).show()', $js);
    }

    public function testCardQuantityFeedsThePerCardAddForm(): void
    {
        $card = (string) file_get_contents(dirname(__DIR__, 3) . '/views/shared/_product_card.php');
        $js = (string) file_get_contents(dirname(__DIR__, 3) . '/web/js/mini-cart.js');

        self::assertStringContainsString("'data-quantity-source' => '#qty-'", $card);
        self::assertStringContainsString("'data-card-quantity' =>", $card);
        self::assertStringContainsString('form.dataset.quantitySource', $js);
        self::assertStringContainsString('Math.min(99, Math.max(1, value))', $js);
    }

    public function testToastSitsAtTopAndSpansMobileWidth(): void
    {
        $view = (string) file_get_contents(dirname(__DIR__, 3) . '/views/layouts/_mini_cart.php');
        $css = (string) file_get_contents(dirname(__DIR__, 3) . '/web/css/cafebara-theme.css');

        self::assertStringContainsString('position-fixed top-0 end-0 p-3 cafebara-cart-toast-container', $view);
        self::assertStringContainsString('    .cafebara-cart-toast-container {', $css);
        self::assertStringContainsString('.cafebara-cart-toast-container .toast {', $css);
    }
}

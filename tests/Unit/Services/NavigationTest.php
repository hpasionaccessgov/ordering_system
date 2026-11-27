<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\services\Navigation;
use Codeception\Test\Unit;

final class NavigationTest extends Unit
{
    public function testRouteMapGeneratesPrettyUrlRules(): void
    {
        $rules = Navigation::urlRules();

        self::assertSame('site/index', $rules['']);
        self::assertSame('order/view', $rules['sales/orders/<id:\d+>']);
        self::assertSame('order/process', $rules['sales/orders/<id:\d+>/process']);
        self::assertSame('kitchen/index', $rules['sales/kitchen-board']);
        self::assertSame('cart/update', $rules['sales/cart/update/<id:\d+>']);
    }

    public function testBrowserLinksPointAtGetActions(): void
    {
        self::assertSame('cart/update', Navigation::route(Navigation::CART_UPDATE_QUANTITY));
        self::assertSame('notification/view', Navigation::route(Navigation::NOTIFICATION_VIEW));
        self::assertSame('notification/read', Navigation::route(Navigation::NOTIFICATION_READ));
    }

    public function testUnknownRouteIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Navigation::route('does-not-exist');
    }
}

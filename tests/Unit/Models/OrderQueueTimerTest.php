<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\models\Order;
use Codeception\Test\Unit;

final class OrderQueueTimerTest extends Unit
{
    public function testElapsedStartsAtConfirmationAndUsesMinuteBands(): void
    {
        $now = 1_700_000_000;
        $order = new Order(['status' => Order::STATUS_CONFIRMED]);

        $order->confirmed_at = null;
        self::assertNull($order->queueElapsedSeconds($now));
        self::assertSame('normal', $order->priorityLevel($now));

        $order->confirmed_at = $now - 14 * 60;
        self::assertSame(14 * 60, $order->queueElapsedSeconds($now));
        self::assertSame('normal', $order->priorityLevel($now));

        $order->confirmed_at = $now - 15 * 60;
        self::assertSame('warning', $order->priorityLevel($now));

        $order->confirmed_at = $now - 30 * 60;
        self::assertSame('critical', $order->priorityLevel($now));
    }
}

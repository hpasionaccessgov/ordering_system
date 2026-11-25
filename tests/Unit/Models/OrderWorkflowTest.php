<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\models\Order;
use Codeception\Test\Unit;

final class OrderWorkflowTest extends Unit
{
    public function testOrderCanMoveThroughTheStaffWorkflow(): void
    {
        self::assertSame(
            Order::STATUS_CONFIRMED,
            Order::nextStatusFor(Order::STATUS_PENDING),
        );
        self::assertSame(
            Order::STATUS_PROCESSING,
            Order::nextStatusFor(Order::STATUS_CONFIRMED),
        );
        self::assertSame(
            Order::STATUS_READY_FOR_PICKUP,
            Order::nextStatusFor(Order::STATUS_PROCESSING),
        );
        self::assertSame(
            Order::STATUS_COMPLETED,
            Order::nextStatusFor(Order::STATUS_READY_FOR_PICKUP),
        );
    }

    public function testCompletedAndCancelledOrdersCannotAdvance(): void
    {
        self::assertNull(Order::nextStatusFor(Order::STATUS_COMPLETED));
        self::assertNull(Order::nextStatusFor(Order::STATUS_CANCELLED));
    }
}

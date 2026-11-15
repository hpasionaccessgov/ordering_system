<?php

namespace tests\Unit\Models;

use app\models\Order;
use Codeception\Test\Unit;

class OrderTest extends Unit
{
    public function testPendingOrderCanBeEditedByCustomer(): void
    {
        $order = new Order([
            'status' => Order::STATUS_PENDING,
        ]);

        self::assertTrue($order->isEditableByCustomer());
        self::assertTrue($order->canBeCancelledByCustomer());
    }

    public function testCompletedOrderCannotBeEditedOrCancelledByCustomer(): void
    {
        $order = new Order([
            'status' => Order::STATUS_COMPLETED,
        ]);

        self::assertFalse($order->isEditableByCustomer());
        self::assertFalse($order->canBeCancelledByCustomer());
        self::assertFalse($order->canBeCancelledByStaff());
    }

    public function testStaffCanCancelConfirmedOrder(): void
    {
        $order = new Order([
            'status' => Order::STATUS_CONFIRMED,
        ]);

        self::assertTrue($order->canBeCancelledByStaff());
    }
}
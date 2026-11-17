<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\models\Order;
use app\services\OrderService;
use Codeception\Test\Unit;
use yii\web\BadRequestHttpException;

final class OrderServiceTest extends Unit
{
    public function testPendingOrderCannotBeUpdatedWithoutItems(): void
    {
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isEditableByCustomer'])
            ->getMock();
        $order->method('isEditableByCustomer')->willReturn(true);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('An order must contain at least one product.');

        (new OrderService())->update($order, []);
    }
}

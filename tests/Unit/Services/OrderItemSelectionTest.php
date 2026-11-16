<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\services\OrderItemSelection;
use Codeception\Test\Unit;
use yii\web\BadRequestHttpException;

final class OrderItemSelectionTest extends Unit
{
    public function testQuantityZeroProductsAreIgnored(): void
    {
        self::assertSame(
            [
                ['product_id' => 2, 'quantity' => 1],
            ],
            OrderItemSelection::selectedItems([
                ['product_id' => '1', 'quantity' => '0'],
                ['product_id' => '2', 'quantity' => '1'],
            ]),
        );
    }

    public function testEmptySelectionIsRejected(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('An order must contain at least one product.');

        OrderItemSelection::selectedItems([
            ['product_id' => '1', 'quantity' => '0'],
        ]);
    }

    public function testMalformedProductRowsAreRejected(): void
    {
        $this->expectException(BadRequestHttpException::class);

        OrderItemSelection::selectedItems([
            ['product_id' => 'not-an-id', 'quantity' => '1'],
        ]);
    }
}

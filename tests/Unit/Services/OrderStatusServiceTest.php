<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\models\Category;
use app\models\Order;
use app\models\OrderNotification;
use app\models\Product;
use app\models\User;
use app\services\OrderService;
use app\services\OrderStatusService;
use Codeception\Test\Unit;
use yii\web\BadRequestHttpException;

final class OrderStatusServiceTest extends Unit
{
    public function testStatusTransitionsAreLoggedAtomically(): void
    {
        [$user, $product] = $this->records();
        $order = (new OrderService())->create(
            (int) $user->id,
            [['product_id' => $product->id, 'quantity' => 1]],
        );

        self::assertSame(
            ['pending'],
            array_column($order->statusHistory, 'to_status'),
        );

        $statusService = new OrderStatusService();
        $order = $statusService->process($order, (int) $user->id);
        $notification = OrderNotification::findOne([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'status' => Order::STATUS_CONFIRMED,
        ]);
        self::assertNotNull($notification);
        self::assertNull($notification->read_at);
        $order = $statusService->process($order, (int) $user->id);
        $order = $statusService->process($order, (int) $user->id);

        $order->refresh();
        self::assertSame(Order::STATUS_COMPLETED, $order->status);
        self::assertSame(
            ['pending', 'confirmed', 'processing', 'completed'],
            array_column($order->statusHistory, 'to_status'),
        );
        self::assertSame(3, (int) OrderNotification::find()->where(['order_id' => $order->id])->count());

        $this->expectException(BadRequestHttpException::class);
        $statusService->process($order, (int) $user->id);
    }

    public function testCancellationRulesAndHistoryArePreserved(): void
    {
        [$customer, $product] = $this->records();
        $service = new OrderStatusService();
        $customerOrder = (new OrderService())->create(
            (int) $customer->id,
            [['product_id' => $product->id, 'quantity' => 1]],
        );
        $customerOrder = $service->cancel($customerOrder, (int) $customer->id, false);
        self::assertSame(Order::STATUS_CANCELLED, $customerOrder->status);
        self::assertNotNull(OrderNotification::findOne([
            'order_id' => $customerOrder->id,
            'user_id' => $customer->id,
            'status' => Order::STATUS_CANCELLED,
        ]));
        self::assertSame(
            ['pending', 'cancelled'],
            array_column($customerOrder->statusHistory, 'to_status'),
        );

        $staffOrder = (new OrderService())->create(
            (int) $customer->id,
            [['product_id' => $product->id, 'quantity' => 1]],
        );
        $staffOrder = $service->process($staffOrder, (int) $customer->id);
        $staffOrder = $service->cancel($staffOrder, (int) $customer->id, true);
        self::assertSame(Order::STATUS_CANCELLED, $staffOrder->status);

        $this->expectException(BadRequestHttpException::class);
        $service->cancel($staffOrder, (int) $customer->id, false);
    }

    public function testStatusNotificationRollsBackWithOrderChange(): void
    {
        [$user, $product] = $this->records();
        $order = (new OrderService())->create(
            (int) $user->id,
            [['product_id' => $product->id, 'quantity' => 1]],
        );
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            (new OrderStatusService())->process($order, (int) $user->id);
            self::assertSame(1, (int) OrderNotification::find()->where(['order_id' => $order->id])->count());
        } finally {
            $transaction->rollBack();
        }

        self::assertSame(0, (int) OrderNotification::find()->where(['order_id' => $order->id])->count());
        self::assertSame(Order::STATUS_PENDING, Order::findOne($order->id)->status);
    }

    /**
     * @return array{User, Product}
     */
    private function records(): array
    {
        $suffix = substr(bin2hex(random_bytes(4)), 0, 8);
        $user = new User([
            'username' => 'history_' . $suffix,
            'email' => $suffix . '@history.test',
            'full_name' => 'History customer',
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->setPassword('History123!');
        $user->generateAuthKey();
        self::assertTrue($user->save(), json_encode($user->getErrors()));

        $category = new Category([
            'name' => 'History category ' . $suffix,
            'status' => Category::STATUS_ACTIVE,
        ]);
        self::assertTrue($category->save(), json_encode($category->getErrors()));
        $product = new Product([
            'name' => 'History product ' . $suffix,
            'category_id' => $category->id,
            'price' => '25.00',
            'status' => Product::STATUS_ACTIVE,
        ]);
        self::assertTrue($product->save(), json_encode($product->getErrors()));

        return [$user, $product];
    }
}

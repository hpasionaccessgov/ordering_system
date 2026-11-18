<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\models\Category;
use app\models\Order;
use app\models\Product;
use app\models\User;
use app\services\OrderService;
use Codeception\Test\Unit;
use yii\web\BadRequestHttpException;

final class CheckoutServiceTest extends Unit
{
    public function testCheckoutPersistsServerTotalsAndProductSnapshots(): void
    {
        [$user, $product] = $this->createCheckoutRecords();
        $token = bin2hex(random_bytes(24));

        $order = (new OrderService())->create((int) $user->id, [
            ['product_id' => $product->id, 'quantity' => 2],
            ['product_id' => $product->id, 'quantity' => 1],
        ], $token);

        self::assertNotEmpty($order->order_number);
        self::assertSame(Order::STATUS_PENDING, $order->status);
        self::assertSame('45.00', $order->total_amount);
        self::assertCount(1, $order->items);
        self::assertSame('Product before rename', $order->items[0]->product_name);
        self::assertSame('45.00', $order->items[0]->subtotal);
        self::assertSame(3, $order->items[0]->quantity);

        $duplicate = (new OrderService())->create(
            (int) $user->id,
            [['product_id' => $product->id, 'quantity' => 1]],
            $token,
        );
        self::assertSame($order->id, $duplicate->id);
        self::assertSame(1, (int) Order::find()->where(['checkout_token' => $token])->count());

        $product->name = 'Renamed product';
        $product->price = '99.00';
        self::assertTrue($product->save(false, ['name', 'price', 'updated_at']));
        self::assertSame('Product before rename', $order->items[0]->product_name);
        self::assertSame('15.00', $order->items[0]->unit_price);
        self::assertSame('45.00', $order->items[0]->subtotal);
    }

    public function testCheckoutRejectsInactiveProductsAndWrongOwnerReplay(): void
    {
        [$user, $product] = $this->createCheckoutRecords();
        $token = bin2hex(random_bytes(24));
        (new OrderService())->create(
            (int) $user->id,
            [['product_id' => $product->id, 'quantity' => 1]],
            $token,
        );

        $otherUser = $this->createUser('other@example.test');
        try {
            (new OrderService())->create(
                (int) $otherUser->id,
                [['product_id' => $product->id, 'quantity' => 1]],
                $token,
            );
            self::fail('A checkout token must not be reusable by another user.');
        } catch (BadRequestHttpException $exception) {
            self::assertStringContainsString('invalid', $exception->getMessage());
        }

        $product->status = Product::STATUS_ARCHIVED;
        self::assertTrue($product->save(false, ['status', 'updated_at']));
        $this->expectException(BadRequestHttpException::class);
        (new OrderService())->create(
            (int) $user->id,
            [['product_id' => $product->id, 'quantity' => 1]],
        );
    }

    /**
     * @return array{User, Product}
     */
    private function createCheckoutRecords(): array
    {
        $category = new Category([
            'name' => 'Checkout category',
            'status' => Category::STATUS_ACTIVE,
        ]);
        self::assertTrue($category->save(), json_encode($category->getErrors()));
        $product = new Product([
            'name' => 'Product before rename',
            'category_id' => $category->id,
            'price' => '15.00',
            'status' => Product::STATUS_ACTIVE,
        ]);
        self::assertTrue($product->save(), json_encode($product->getErrors()));

        return [$this->createUser('checkout@example.test'), $product];
    }

    private function createUser(string $email): User
    {
        $suffix = substr(bin2hex(random_bytes(4)), 0, 8);
        $user = new User([
            'username' => 'checkout_' . $suffix,
            'email' => $suffix . $email,
            'full_name' => 'Checkout customer',
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->setPassword('Checkout123!');
        $user->generateAuthKey();
        self::assertTrue($user->save(), json_encode($user->getErrors()));

        return $user;
    }
}

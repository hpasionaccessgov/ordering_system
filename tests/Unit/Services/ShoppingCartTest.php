<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\models\Category;
use app\models\Product;
use app\models\User;
use app\services\ShoppingCart;
use Codeception\Test\Unit;
use Yii;
use yii\web\BadRequestHttpException;

final class ShoppingCartTest extends Unit
{
    protected function _before(): void
    {
        parent::_before();
        Yii::$app->session->removeAll();
    }

    public function testCartQuantitiesAndTotalsUseCurrentServerPrices(): void
    {
        $product = $this->createProduct('Cart product', '12.50');
        $cart = new ShoppingCart();

        $cart->add((int) $product->id, 2);
        self::assertSame([(int) $product->id => 2], $cart->items());
        self::assertSame('25.00', $cart->summary()['total']);

        $cart->update((int) $product->id, 3);
        self::assertSame('37.50', $cart->summary()['total']);

        $product->price = '14.00';
        self::assertTrue($product->save(false, ['price', 'updated_at']));
        self::assertSame('42.00', $cart->summary()['total']);

        $cart->remove((int) $product->id);
        self::assertSame([], $cart->items());
    }

    public function testInactiveProductCannotBeAddedOrCheckedOutAtAStaleCartPrice(): void
    {
        $product = $this->createProduct('Inactive cart product', '10.00');
        $cart = new ShoppingCart();
        $cart->add((int) $product->id, 1);

        $product->status = Product::STATUS_ARCHIVED;
        self::assertTrue($product->save(false, ['status', 'updated_at']));
        self::assertSame([], $cart->summary()['lines']);
        self::assertSame(1, $cart->removeUnavailable());
        self::assertSame([], $cart->items());

        $this->expectException(BadRequestHttpException::class);
        $cart->add((int) $product->id, 1);
    }

    public function testQuantityLimitsAndCheckoutTokenAreEnforced(): void
    {
        $product = $this->createProduct('Limited cart product', '1.00');
        $cart = new ShoppingCart();

        try {
            $cart->add((int) $product->id, ShoppingCart::MAX_QUANTITY + 1);
            self::fail('Quantity above the limit should be rejected.');
        } catch (BadRequestHttpException $exception) {
            self::assertStringContainsString('between 1 and 99', $exception->getMessage());
        }

        $cart->add((int) $product->id, 1);
        $token = $cart->checkoutToken();
        $cart->assertCheckoutToken($token);

        $this->expectException(BadRequestHttpException::class);
        $cart->assertCheckoutToken(str_repeat('x', 48));
    }

    public function testAuthenticatedCartPersistsBeyondSessionData(): void
    {
        $user = new User([
            'username' => 'persistent_' . substr(bin2hex(random_bytes(3)), 0, 6),
            'email' => substr(bin2hex(random_bytes(4)), 0, 8) . '@cart.test',
            'full_name' => 'Persistent cart',
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->setPassword('PersistentCart123!');
        $user->generateAuthKey();
        self::assertTrue($user->save(), json_encode($user->getErrors()));
        $product = $this->createProduct('Persistent product', '4.00');

        Yii::$app->user->setIdentity($user);
        $cart = new ShoppingCart();
        $cart->add((int) $product->id, 2);
        Yii::$app->session->removeAll();

        self::assertSame([(int) $product->id => 2], $cart->items());
    }

    public function testSessionCartMergesIntoExistingAccountCart(): void
    {
        $user = new User([
            'username' => 'merge_' . substr(bin2hex(random_bytes(3)), 0, 6),
            'email' => substr(bin2hex(random_bytes(4)), 0, 8) . '@merge.test',
            'full_name' => 'Merged cart',
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->setPassword('MergeCart123!');
        $user->generateAuthKey();
        self::assertTrue($user->save(), json_encode($user->getErrors()));
        $product = $this->createProduct('Merged product', '6.00');

        Yii::$app->user->logout();
        $cart = new ShoppingCart();
        $cart->add((int) $product->id, 1);
        Yii::$app->user->setIdentity($user);
        Yii::$app->db->createCommand()->insert('{{%cart_items}}', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ])->execute();

        $cart->mergeSessionIntoAccount();

        self::assertSame([(int) $product->id => 2], $cart->items());
        self::assertFalse(Yii::$app->session->has('shopping-cart'));
    }

    private function createProduct(string $name, string $price): Product
    {
        $category = new Category([
            'name' => $name . ' category',
            'status' => Category::STATUS_ACTIVE,
        ]);
        self::assertTrue($category->save(), json_encode($category->getErrors()));

        $product = new Product([
            'name' => $name,
            'category_id' => $category->id,
            'price' => $price,
            'status' => Product::STATUS_ACTIVE,
        ]);
        self::assertTrue($product->save(), json_encode($product->getErrors()));

        return $product;
    }
}

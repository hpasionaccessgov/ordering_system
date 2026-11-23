<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\models\Category;
use app\models\Product;
use app\models\User;
use app\tests\Support\FunctionalTester;
use Yii;
use yii\rbac\DbManager;

final class CheckoutCest
{
    private int $productId;

    public function _before(FunctionalTester $I): void
    {
        Yii::$app->set('authManager', DbManager::class);
        $suffix = substr(bin2hex(random_bytes(4)), 0, 8);
        $user = new User([
            'username' => 'checkout_' . $suffix,
            'email' => $suffix . '@checkout.test',
            'full_name' => 'Checkout customer',
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->setPassword('Checkout123!');
        $user->generateAuthKey();
        $I->assertTrue($user->save(), json_encode($user->getErrors()));
        Yii::$app->authManager->assign(
            Yii::$app->authManager->getRole('customer'),
            $user->id,
        );

        $category = new Category([
            'name' => 'Checkout ' . $suffix,
            'status' => Category::STATUS_ACTIVE,
        ]);
        $I->assertTrue($category->save(), json_encode($category->getErrors()));
        $product = new Product([
            'name' => 'Functional checkout product',
            'category_id' => $category->id,
            'price' => '25.00',
            'status' => Product::STATUS_ACTIVE,
        ]);
        $I->assertTrue($product->save(), json_encode($product->getErrors()));
        $this->productId = (int) $product->id;
        $I->amLoggedInAs($user);
    }

    public function customerCanCheckoutAndSeeConfirmation(FunctionalTester $I): void
    {
        $I->amOnRoute('product/view', ['id' => $this->productId]);
        $I->submitForm('form[action*="cart%2Fadd"]', ['quantity' => 2]);
        $I->see('Shopping cart', 'h1');
        $I->see('50.00');

        $I->click('Continue to checkout');
        $I->see('Checkout', 'h1');
        $I->click('Place order');
        $I->see('Thank you. Your order is placed.', 'h1');
        $I->see('Functional checkout product');
        $I->see('50.00');
        $I->seeInCurrentUrl('r=order%2Fconfirmation');
    }
}

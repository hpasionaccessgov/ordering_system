<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\models\Category;
use app\models\Product;
use app\models\User;
use app\services\ShoppingCart;
use app\tests\Support\FunctionalTester;
use Yii;
use yii\rbac\DbManager;

final class CartSummaryCest
{
    private int $productId;

    public function _before(FunctionalTester $I): void
    {
        Yii::$app->set('authManager', DbManager::class);
        $suffix = substr(bin2hex(random_bytes(4)), 0, 8);
        $user = new User([
            'username' => 'cart_ajax_' . $suffix,
            'email' => $suffix . '@cart-ajax.test',
            'full_name' => 'Cart AJAX customer',
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->setPassword('CartAjax123!');
        $user->generateAuthKey();
        $I->assertTrue($user->save(), json_encode($user->getErrors()));
        Yii::$app->authManager->assign(
            Yii::$app->authManager->getRole('customer'),
            $user->id,
        );

        $category = new Category([
            'name' => 'Cart AJAX ' . $suffix,
            'status' => Category::STATUS_ACTIVE,
        ]);
        $I->assertTrue($category->save(), json_encode($category->getErrors()));
        $product = new Product([
            'name' => 'Cart AJAX product ' . $suffix,
            'category_id' => $category->id,
            'price' => '25.00',
            'status' => Product::STATUS_ACTIVE,
        ]);
        $I->assertTrue($product->save(), json_encode($product->getErrors()));
        $this->productId = (int) $product->id;

        $I->amLoggedInAs($user);
        (new ShoppingCart())->add($this->productId, 2);
    }

    public function ajaxSummaryReturnsDrawerMarkup(FunctionalTester $I): void
    {
        $I->sendAjaxGetRequest('/index.php?r=cart%2Fsummary');
        $I->seeResponseCodeIs(200);
        $data = json_decode($I->grabPageSource(), true, 512, JSON_THROW_ON_ERROR);
        $I->assertSame(true, $data['success']);
        $I->assertSame(2, $data['count']);
        $I->assertStringContainsString('cafebara-cart-line', $data['html']);
        $I->assertStringContainsString('Cart AJAX product', $data['html']);
    }

    public function ajaxRemoveRefreshesDrawer(FunctionalTester $I): void
    {
        $I->sendAjaxPostRequest('/index.php?r=cart%2Fremove&id=' . $this->productId);
        $I->seeResponseCodeIs(200);
        $data = json_decode($I->grabPageSource(), true, 512, JSON_THROW_ON_ERROR);
        $I->assertSame(0, $data['count']);
        $I->assertSame('Product removed from your cart.', $data['message']);
        $I->assertStringContainsString('Browse Menu', $data['html']);
    }
}

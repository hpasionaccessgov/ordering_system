<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\models\Category;
use app\models\Product;
use app\models\User;
use app\tests\Support\FunctionalTester;
use Yii;
use yii\rbac\DbManager;

final class BatchCartCest
{
    public function specialFeaturedProductHasOneBatchInputAndIsAdded(FunctionalTester $I): void
    {
        Yii::$app->set('authManager', DbManager::class);
        $suffix = substr(bin2hex(random_bytes(4)), 0, 8);
        $user = new User([
            'username' => 'batch_' . $suffix,
            'email' => $suffix . '@batch.test',
            'full_name' => 'Batch customer',
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->setPassword('BatchTest123!');
        $user->generateAuthKey();
        $I->assertTrue($user->save(), json_encode($user->getErrors()));
        Yii::$app->authManager->assign(Yii::$app->authManager->getRole('customer'), $user->id);

        $category = new Category([
            'name' => 'Batch ' . $suffix,
            'status' => Category::STATUS_ACTIVE,
        ]);
        $I->assertTrue($category->save(), json_encode($category->getErrors()));
        $product = new Product([
            'name' => 'Batch product ' . $suffix,
            'category_id' => $category->id,
            'price' => '30.00',
            'status' => Product::STATUS_ACTIVE,
            'is_featured' => true,
            'is_special' => true,
        ]);
        $I->assertTrue($product->save(), json_encode($product->getErrors()));

        $I->amLoggedInAs($user);
        $I->amOnRoute('site/index');
        $I->seeNumberOfElements('input[name="quantities[' . $product->id . ']"]', 1);

        $I->sendAjaxPostRequest('/index.php?r=cart%2Fadd-batch', [
            'quantities' => [$product->id => 2],
        ]);
        $I->amOnRoute('cart/index');
        $I->see('Batch product ' . $suffix, '.cart-items-panel');
    }
}

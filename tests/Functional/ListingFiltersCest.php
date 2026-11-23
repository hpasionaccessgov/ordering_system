<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\models\Category;
use app\models\Product;
use app\models\User;
use app\tests\Support\FunctionalTester;
use Yii;
use yii\rbac\DbManager;

final class ListingFiltersCest
{
    public function publicMenuFiltersPriceWithoutShowingArchivedProducts(FunctionalTester $I): void
    {
        $suffix = bin2hex(random_bytes(4));
        $category = new Category(['name' => 'Menu filter ' . $suffix, 'status' => Category::STATUS_ACTIVE]);
        $I->assertTrue($category->save(), json_encode($category->getErrors()));
        foreach ([
            ['cheap', Product::STATUS_ACTIVE, '5.00'],
            ['matching', Product::STATUS_ACTIVE, '15.00'],
            ['archived', Product::STATUS_ARCHIVED, '15.00'],
        ] as [$name, $status, $price]) {
            $product = new Product([
                'name' => $suffix . ' ' . $name,
                'category_id' => $category->id,
                'status' => $status,
                'price' => $price,
            ]);
            $I->assertTrue($product->save(), json_encode($product->getErrors()));
        }

        $I->amOnRoute('site/menu');
        $I->see('More filters');
        $I->dontSee('Created by username');
        $I->submitForm('form[role="search"]', [
            'q' => $suffix,
            'min_amount' => '10',
            'max_amount' => '20',
        ]);
        $I->see($suffix . ' matching');
        $I->dontSee($suffix . ' cheap');
        $I->dontSee($suffix . ' archived');
        $I->seeInField('min_amount', '10');
    }

    public function adminCanUseFiltersOnManagementPages(FunctionalTester $I): void
    {
        Yii::$app->set('authManager', DbManager::class);
        $name = 'filters_' . bin2hex(random_bytes(4));
        $user = new User([
            'username' => $name, 'email' => $name . '@example.test',
            'full_name' => 'Filter admin', 'status' => User::STATUS_ACTIVE,
        ]);
        $user->setPassword('FilterTest123!');
        $user->generateAuthKey();
        $I->assertTrue($user->save(), json_encode($user->getErrors()));
        Yii::$app->authManager->assign(Yii::$app->authManager->getRole('admin'), $user->id);
        $I->amLoggedInAs($user);

        foreach (['category/index', 'product/index', 'order/index', 'admin-user/list'] as $route) {
            $I->amOnRoute($route);
            $I->seeResponseCodeIs(200);
            $I->see('Created by username');
            $I->see('Created from');
        }
        $I->amOnRoute('order/index');
        $I->submitForm('form[role="search"]', ['min_amount' => '20', 'max_amount' => '10']);
        $I->see('must be greater than or equal to');
    }

    public function customerDoesNotSeeInternalAuditFilters(FunctionalTester $I): void
    {
        Yii::$app->set('authManager', DbManager::class);
        $name = 'customer_' . bin2hex(random_bytes(4));
        $user = new User([
            'username' => $name, 'email' => $name . '@example.test',
            'full_name' => 'Filter customer', 'status' => User::STATUS_ACTIVE,
        ]);
        $user->setPassword('FilterTest123!');
        $user->generateAuthKey();
        $I->assertTrue($user->save(), json_encode($user->getErrors()));
        Yii::$app->authManager->assign(Yii::$app->authManager->getRole('customer'), $user->id);
        $I->amLoggedInAs($user);

        foreach (['product/index', 'order/index'] as $route) {
            $I->amOnRoute($route);
            $I->seeResponseCodeIs(200);
            $I->see('More filters');
            $I->dontSee('Created by username');
            $I->dontSee('Updated by username');
        }
    }
}

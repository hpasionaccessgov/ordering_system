<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\models\Category;
use app\models\Inventory;
use app\models\Notification;
use app\models\Product;
use app\models\User;
use app\services\NotificationService;
use app\tests\Support\FunctionalTester;
use Yii;
use yii\rbac\DbManager;

final class CatalogNotificationCest
{
    public function _before(): void
    {
        Yii::$app->set('authManager', DbManager::class);
    }

    public function catalogChangesNotifyOnlyTheIntendedUsers(FunctionalTester $I): void
    {
        $suffix = bin2hex(random_bytes(4));
        $admin = $this->user($I, 'admin', $suffix);
        $staff = $this->user($I, 'staff', $suffix);
        $customer = $this->user($I, 'customer', $suffix);
        $category = new Category(['name' => 'Notice category ' . $suffix, 'status' => Category::STATUS_ACTIVE]);
        $I->assertTrue($category->save(), json_encode($category->getErrors()));
        $inventory = new Inventory([
            'name' => 'Notice inventory ' . $suffix,
            'unit' => 'g',
            'current_stock' => 100,
            'minimum_stock' => 10,
            'status' => Inventory::STATUS_ACTIVE,
        ]);
        $I->assertTrue($inventory->save(), json_encode($inventory->getErrors()));

        $productName = 'Notice product ' . $suffix;
        $I->amLoggedInAs($admin);
        $I->amOnRoute('product/create');
        $I->submitForm('form.product-editor-form', [
            'Product[name]' => $productName,
            'Product[category_id]' => (string) $category->id,
            'Product[price]' => '12.00',
            'Product[description]' => 'Catalog notice description.',
            'Product[status]' => (string) Product::STATUS_ACTIVE,
            'ingredients[0][inventory_id]' => (string) $inventory->id,
            'ingredients[0][quantity_required]' => '1',
        ]);
        $I->see('Product created.');
        $product = Product::findOne(['name' => $productName]);
        $I->assertNotNull($product);

        $created = Notification::findOne(['user_id' => $customer->id, 'type' => NotificationService::TYPE_PRODUCT_CREATED]);
        $I->assertNotNull($created);
        $I->assertSame('New product ' . $productName . ' added.', $created->label);
        $I->assertSame(['/product/view', 'id' => $product->id], $created->targetUrl);
        $I->assertNotNull(Notification::findOne([
            'user_id' => $staff->id, 'type' => NotificationService::TYPE_PRODUCT_CREATED,
        ]));
        $I->assertNull(Notification::findOne([
            'user_id' => $admin->id, 'type' => NotificationService::TYPE_PRODUCT_CREATED,
        ]));

        $updatedName = 'Updated product ' . $suffix;
        $I->amOnRoute('product/update', ['id' => $product->id]);
        $I->submitForm('form.product-editor-form', [
            'Product[name]' => $updatedName,
            'Product[category_id]' => (string) $category->id,
            'Product[price]' => '12.00',
            'Product[status]' => (string) Product::STATUS_ACTIVE,
            'ingredients[0][inventory_id]' => (string) $inventory->id,
            'ingredients[0][quantity_required]' => '1',
        ]);
        $I->see('Product updated.');
        $I->assertNotNull(Notification::findOne([
            'user_id' => $staff->id, 'type' => NotificationService::TYPE_PRODUCT_UPDATED,
        ]));
        $I->assertNull(Notification::findOne([
            'user_id' => $customer->id, 'type' => NotificationService::TYPE_PRODUCT_UPDATED,
        ]));

        $newCategoryName = 'Added category ' . $suffix;
        $I->amOnRoute('category/create');
        $I->submitForm('form[action*="category%2Fcreate"]', [
            'Category[name]' => $newCategoryName,
            'Category[status]' => (string) Category::STATUS_ACTIVE,
        ]);
        $newCategory = Category::findOne(['name' => $newCategoryName]);
        $I->assertNotNull($newCategory);
        $categoryCreated = Notification::findOne([
            'user_id' => $staff->id, 'type' => NotificationService::TYPE_CATEGORY_CREATED,
        ]);
        $I->assertNotNull($categoryCreated);
        $I->assertSame(['/category/view', 'id' => $newCategory->id], $categoryCreated->targetUrl);
        $I->assertNull(Notification::findOne([
            'user_id' => $customer->id, 'type' => NotificationService::TYPE_CATEGORY_CREATED,
        ]));

        $editedCategoryName = 'Edited category ' . $suffix;
        $I->amOnRoute('category/update', ['id' => $newCategory->id]);
        $I->submitForm('form[action*="category%2Fupdate"]', [
            'Category[name]' => $editedCategoryName,
            'Category[status]' => (string) Category::STATUS_ACTIVE,
        ]);
        $categoryUpdated = Notification::findOne([
            'user_id' => $staff->id, 'type' => NotificationService::TYPE_CATEGORY_UPDATED,
        ]);
        $I->assertNotNull($categoryUpdated);
        $I->assertSame(['/category/view', 'id' => $newCategory->id], $categoryUpdated->targetUrl);
        $I->assertNull(Notification::findOne([
            'user_id' => $customer->id, 'type' => NotificationService::TYPE_CATEGORY_UPDATED,
        ]));
    }

    private function user(FunctionalTester $I, string $role, string $suffix): User
    {
        $name = $role . '_' . $suffix;
        $user = new User([
            'username' => $name,
            'email' => $name . '@example.test',
            'full_name' => 'Catalog notification test',
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->setPassword('CatalogTest123!');
        $user->generateAuthKey();
        $I->assertTrue($user->save(), json_encode($user->getErrors()));
        Yii::$app->authManager->assign(Yii::$app->authManager->getRole($role), $user->id);

        return $user;
    }
}

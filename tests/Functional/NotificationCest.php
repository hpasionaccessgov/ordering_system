<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\models\Category;
use app\models\OrderNotification;
use app\models\Product;
use app\models\User;
use app\services\OrderService;
use app\services\OrderStatusService;
use app\tests\Support\FunctionalTester;

final class NotificationCest
{
    public function inboxIsPrivateAndCanBeMarkedRead(FunctionalTester $I): void
    {
        $suffix = bin2hex(random_bytes(4));
        $users = [];
        foreach (['one', 'two'] as $name) {
            $user = new User([
                'username' => $name . '_' . $suffix,
                'email' => $name . '_' . $suffix . '@example.test',
                'full_name' => 'Notification customer',
                'status' => User::STATUS_ACTIVE,
            ]);
            $user->setPassword('TestPassword123!');
            $user->generateAuthKey();
            $I->assertTrue($user->save(), json_encode($user->getErrors()));
            $users[] = $user;
        }
        $category = new Category(['name' => 'Notification ' . $suffix, 'status' => Category::STATUS_ACTIVE]);
        $I->assertTrue($category->save(), json_encode($category->getErrors()));
        $product = new Product([
            'name' => 'Notification product',
            'category_id' => $category->id,
            'price' => '25.00',
            'status' => Product::STATUS_ACTIVE,
        ]);
        $I->assertTrue($product->save(), json_encode($product->getErrors()));

        $orders = [];
        foreach ($users as $user) {
            $order = (new OrderService())->create((int) $user->id, [['product_id' => $product->id, 'quantity' => 1]]);
            $orders[] = (new OrderStatusService())->process($order, (int) $user->id);
        }
        $otherNotification = OrderNotification::findOne(['order_id' => $orders[1]->id]);
        $I->assertNotNull($otherNotification);
        $I->amLoggedInAs($users[0]);
        $I->amOnRoute('notification/index');
        $I->see($orders[0]->order_number);
        $I->dontSee($orders[1]->order_number);
        $I->see('1 unread');
        $I->click('Mark as read');
        $I->dontSee('1 unread');
        $I->dontSee('Unread');
        $I->amOnRoute('notification/read', ['id' => $otherNotification->id]);
        $I->seeResponseCodeIs(405);
        $I->sendAjaxPostRequest('/index.php?r=notification%2Fread&id=' . $otherNotification->id);
        $I->seeResponseCodeIs(404);
        $I->assertNull(OrderNotification::findOne($otherNotification->id)->read_at);
    }

    public function guestsCannotAccessInbox(FunctionalTester $I): void
    {
        $I->amOnRoute('notification/index');
        $I->dontSee('No notifications yet.');
        $I->seeInCurrentUrl('login');
    }
}

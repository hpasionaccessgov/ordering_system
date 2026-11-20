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
use Yii;
use yii\rbac\DbManager;

final class NotificationCest
{
    public function inboxIsPrivateAndCanBeMarkedRead(FunctionalTester $I): void
    {
        Yii::$app->set('authManager', DbManager::class);
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
            Yii::$app->authManager->assign(Yii::$app->authManager->getRole('customer'), $user->id);
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
        $otherNotification = OrderNotification::findOne(['order_id' => $orders[1]->id, 'user_id' => $users[1]->id]);
        $I->assertNotNull($otherNotification);
        $I->amLoggedInAs($users[0]);
        $I->amOnRoute('notification/index');
        $I->see($orders[0]->order_number);
        $I->dontSee($orders[1]->order_number);
        $I->seeElement('[data-notification-link][aria-label="Notifications, 1 unread"]');
        $I->click('Mark as read');
        $I->seeElement('[data-notification-link][aria-label="Notifications"]');
        $I->dontSee('Unread');
        $I->amOnRoute('notification/read', ['id' => $otherNotification->id]);
        $I->seeResponseCodeIs(405);
        $I->sendAjaxPostRequest('/index.php?r=notification%2Fread&id=' . $otherNotification->id);
        $I->seeResponseCodeIs(404);
        $I->assertNull(OrderNotification::findOne($otherNotification->id)->read_at);

        for ($i = 0; $i < 10; $i++) {
            $notification = new OrderNotification([
                'user_id' => $users[0]->id,
                'order_id' => $orders[0]->id,
                'status' => 'processing',
                'created_at' => time(),
            ]);
            $I->assertTrue($notification->save(false));
        }
        $I->amOnRoute('notification/index', ['per-page' => 100]);
        $I->seeNumberOfElements('.list-group-item', 9);

        $auth = Yii::$app->authManager;
        $indexOnly = $auth->createRole('notification_index_' . $suffix);
        $auth->add($indexOnly);
        $auth->addChild($indexOnly, $auth->getPermission('notification.index'));
        $auth->revoke($auth->getRole('customer'), $users[0]->id);
        $auth->assign($indexOnly, $users[0]->id);
        $I->amLoggedInAs($users[0]);
        $I->amOnRoute('notification/index');
        $I->seeResponseCodeIs(200);
        $I->dontSee('Mark as read');
        $ownNotification = OrderNotification::findOne(['order_id' => $orders[0]->id, 'user_id' => $users[0]->id]);
        $I->sendAjaxPostRequest('/index.php?r=notification%2Fread&id=' . $ownNotification->id);
        $I->seeResponseCodeIs(403);

        $auth->revoke($indexOnly, $users[0]->id);
        $I->amLoggedInAs($users[0]);
        $I->amOnRoute('site/profile');
        $I->dontSeeElement('[data-notification-link]');
        $I->amOnRoute('notification/index');
        $I->seeResponseCodeIs(403);
        $I->amOnRoute('notification/unread-count');
        $I->seeResponseCodeIs(403);
    }

    public function managersReceiveNewAndCancelledOrderAlerts(FunctionalTester $I): void
    {
        Yii::$app->set('authManager', DbManager::class);
        $auth = Yii::$app->authManager;
        $suffix = bin2hex(random_bytes(4));
        $users = [];
        foreach (['customer', 'admin', 'staff', 'inactive', 'restricted', 'other'] as $name) {
            $user = new User([
                'username' => $name . '_' . $suffix,
                'email' => $name . '_' . $suffix . '@example.test',
                'full_name' => 'Notification ' . $name,
                'status' => $name === 'inactive' ? User::STATUS_INACTIVE : User::STATUS_ACTIVE,
            ]);
            $user->setPassword('TestPassword123!');
            $user->generateAuthKey();
            $I->assertTrue($user->save(), json_encode($user->getErrors()));
            $users[$name] = $user;
        }
        foreach (['admin' => 'admin', 'inactive' => 'staff', 'customer' => 'customer', 'other' => 'customer'] as $name => $role) {
            $auth->assign($auth->getRole($role), $users[$name]->id);
        }
        $inheritedStaff = $auth->createRole('notification_staff_' . $suffix);
        $auth->add($inheritedStaff);
        $auth->addChild($inheritedStaff, $auth->getRole('staff'));
        $auth->assign($inheritedStaff, $users['staff']->id);
        $auth->assign($auth->getPermission('staff.access'), $users['restricted']->id);
        $category = new Category(['name' => 'Alerts ' . $suffix, 'status' => Category::STATUS_ACTIVE]);
        $I->assertTrue($category->save(), json_encode($category->getErrors()));
        $product = new Product([
            'name' => 'Alerts product',
            'category_id' => $category->id,
            'price' => '25.00',
            'status' => Product::STATUS_ACTIVE,
        ]);
        $I->assertTrue($product->save(), json_encode($product->getErrors()));
        $token = bin2hex(random_bytes(24));
        $service = new OrderService();
        $items = [['product_id' => $product->id, 'quantity' => 1]];
        $transaction = Yii::$app->db->beginTransaction();
        $rolledBack = $service->create((int) $users['customer']->id, $items, $token);
        $I->assertSame(1, (int) OrderNotification::find()->where([
            'user_id' => $users['staff']->id, 'order_id' => $rolledBack->id,
        ])->count());
        $transaction->rollBack();
        $I->assertSame(0, (int) OrderNotification::find()->where(['order_id' => $rolledBack->id])->count());
        $order = $service->create((int) $users['customer']->id, $items, $token);
        $service->create((int) $users['customer']->id, $items, $token);
        foreach (['admin', 'staff'] as $name) {
            $I->assertSame(1, (int) OrderNotification::find()->where([
                'user_id' => $users[$name]->id, 'order_id' => $order->id, 'status' => 'pending',
            ])->count());
        }
        foreach (['customer', 'other', 'inactive', 'restricted'] as $name) {
            $I->assertSame(0, (int) OrderNotification::find()->where([
                'user_id' => $users[$name]->id, 'order_id' => $order->id,
            ])->count());
        }
        $I->amLoggedInAs($users['staff']);
        $I->amOnRoute('notification/unread-count');
        $I->seeResponseCodeIs(200);
        $I->see('"count":1');
        $I->amOnRoute('notification/index');
        $I->see('New order ' . $order->order_number . ' received.');
        $I->see('View order');
        $I->amOnRoute('order/view', ['id' => $order->id]);
        $I->seeResponseCodeIs(200);

        $transaction = Yii::$app->db->beginTransaction();
        (new OrderStatusService())->cancel($order, (int) $users['customer']->id, false);
        $I->assertSame(1, (int) OrderNotification::find()->where([
            'user_id' => $users['staff']->id, 'order_id' => $order->id, 'status' => 'cancelled',
        ])->count());
        $transaction->rollBack();
        $I->assertSame(0, (int) OrderNotification::find()->where([
            'user_id' => $users['staff']->id, 'order_id' => $order->id, 'status' => 'cancelled',
        ])->count());
        (new OrderStatusService())->cancel($order, (int) $users['customer']->id, false);
        foreach (['admin', 'staff'] as $name) {
            $I->assertSame(1, (int) OrderNotification::find()->where([
                'user_id' => $users[$name]->id, 'order_id' => $order->id, 'status' => 'cancelled',
            ])->count());
        }
        $I->amLoggedInAs($users['admin']);
        $I->amOnRoute('notification/index');
        $I->see('Order ' . $order->order_number . ' is cancelled.');

        $notification = OrderNotification::findOne(['user_id' => $users['staff']->id, 'order_id' => $order->id]);
        $auth->revoke($inheritedStaff, $users['staff']->id);
        $I->amLoggedInAs($users['staff']);
        $I->amOnRoute('notification/index');
        $I->seeResponseCodeIs(403);
        $I->amOnRoute('notification/unread-count');
        $I->seeResponseCodeIs(403);
        $I->sendAjaxPostRequest('/index.php?r=notification%2Fread&id=' . $notification->id);
        $I->seeResponseCodeIs(403);
    }

    public function guestsCannotAccessInbox(FunctionalTester $I): void
    {
        $I->amOnRoute('notification/index');
        $I->dontSee('No notifications yet.');
        $I->seeInCurrentUrl('login');
    }
}

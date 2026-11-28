<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\models\Category;
use app\models\LoyaltyPointTransaction;
use app\models\Notification;
use app\models\Order;
use app\models\OrderItem;
use app\models\Product;
use app\models\Reward;
use app\models\User;
use app\services\FavoriteService;
use app\services\NotificationService;
use app\tests\Support\FunctionalTester;
use Yii;
use yii\rbac\DbManager;

final class ApiCustomerCest
{
    private const PASSWORD = 'ApiCustomerTest123!';

    private string $suffix;
    private User $customer;
    private User $other;
    private Product $product;
    private ?Reward $reward = null;

    public function _before(FunctionalTester $I): void
    {
        Yii::$app->set('authManager', DbManager::class);
        $this->suffix = substr(bin2hex(random_bytes(4)), 0, 8);
        $this->customer = $this->user($I, 'api_cust_' . $this->suffix, 'customer');
        $this->other = $this->user($I, 'api_other_' . $this->suffix, 'customer');
        $this->product = $this->product($I);
    }

    public function _after(FunctionalTester $I): void
    {
        if ($this->reward !== null) {
            $this->reward->updateAttributes(['status' => Reward::STATUS_INACTIVE]);
            $this->reward = null;
        }
    }

    public function cartEndpointsReuseShoppingCart(FunctionalTester $I): void
    {
        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->login($I, $this->customer->username));

        $I->sendAjaxGetRequest('/index.php?r=api/cart/index');
        $I->seeResponseCodeIs(200);
        $I->assertSame(0, $this->json($I)['data']['count']);

        $this->addToCart($I, 2);
        $I->assertSame(2, $this->json($I)['data']['count']);

        $I->sendAjaxRequest('PATCH', '/index.php?r=api/cart/update&id=' . $this->product->id, ['quantity' => 3]);
        $I->seeResponseCodeIs(200);
        $I->assertSame(3, $this->json($I)['data']['count']);

        $I->sendAjaxRequest('PATCH', '/index.php?r=api/cart/update&id=' . $this->product->id, ['quantity' => 0]);
        $I->seeResponseCodeIs(200);
        $I->assertSame(0, $this->json($I)['data']['count']);

        $this->addToCart($I, 1);
        $I->sendAjaxRequest('DELETE', '/index.php?r=api/cart/remove&id=' . $this->product->id);
        $I->seeResponseCodeIs(200);
        $I->assertSame(0, $this->json($I)['data']['count']);

        $this->addToCart($I, 1);
        $I->sendAjaxRequest('DELETE', '/index.php?r=api/cart/clear');
        $I->seeResponseCodeIs(200);
        $I->assertSame(0, $this->json($I)['data']['count']);

        $I->sendAjaxPostRequest('/index.php?r=api/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 0,
        ]);
        $I->seeResponseCodeIs(422);
        $I->assertSame('validation_failed', $this->json($I)['error']['code']);
    }

    public function customerSummaryScopesSpendAndFavoritesToOwner(FunctionalTester $I): void
    {
        $this->completedOrder($I, $this->customer);
        $I->assertTrue(FavoriteService::add((int) $this->customer->id, (int) $this->product->id));

        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->login($I, $this->customer->username));
        $I->sendAjaxGetRequest('/index.php?r=api/customer/summary');
        $I->seeResponseCodeIs(200);
        $summary = $this->json($I)['data'];
        $I->assertSame(1, $summary['order_count']);
        $I->assertSame('40.00', $summary['total_spent']);
        $I->assertSame(1, $summary['favorite_count']);
        $I->assertSame((int) $this->product->id, $summary['favorite_product']['id']);

        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->login($I, $this->other->username));
        $I->sendAjaxGetRequest('/index.php?r=api/customer/summary');
        $I->seeResponseCodeIs(200);
        $other = $this->json($I)['data'];
        $I->assertSame(0, $other['order_count']);
        $I->assertSame('0', $other['total_spent']);
        $I->assertSame(0, $other['favorite_count']);
    }

    public function loyaltyAndRewardsUseExistingService(FunctionalTester $I): void
    {
        $I->assertTrue($this->points($I, $this->customer, 100));
        $reward = $this->reward($I, 50);

        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->login($I, $this->customer->username));

        $I->sendAjaxGetRequest('/index.php?r=api/loyalty/index');
        $I->seeResponseCodeIs(200);
        $I->assertSame(100, $this->json($I)['data']['summary']['current']);

        $I->sendAjaxGetRequest('/index.php?r=api/loyalty/transactions');
        $I->seeResponseCodeIs(200);
        $I->assertSame('EARN', $this->json($I)['data'][0]['transaction_type']);

        $I->sendAjaxGetRequest('/index.php?r=api/loyalty/rewards');
        $I->seeResponseCodeIs(200);
        $I->assertNotEmpty(array_filter(
            $this->json($I)['data'],
            fn(array $row): bool => (int) $row['id'] === (int) $reward->id,
        ));

        $I->sendAjaxPostRequest('/index.php?r=api/loyalty/redeem&id=' . $reward->id);
        $I->seeResponseCodeIs(201);
        $redeemed = $this->json($I)['data'];
        $I->assertSame(-50, $redeemed['points']);
        $I->assertStringStartsWith('CB-', (string) $redeemed['order_number']);

        $I->sendAjaxGetRequest('/index.php?r=api/loyalty/index');
        $I->assertSame(50, $this->json($I)['data']['summary']['current']);
    }

    public function notificationsAreOwnerScopedAndCanBeReadOrDeleted(FunctionalTester $I): void
    {
        NotificationService::notifyUser(
            (int) $this->customer->id,
            'system_notice',
            'Api customer notice ' . $this->suffix,
            '/notifications',
            null,
            Notification::CATEGORY_SYSTEM,
        );
        $foreignNotification = $this->notification($I, $this->other, 'Api other notice ' . $this->suffix);

        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->login($I, $this->customer->username));

        $I->sendAjaxGetRequest('/index.php?r=api/notification/unread-count');
        $I->seeResponseCodeIs(200);
        $I->assertSame(1, $this->json($I)['data']['count']);

        $I->sendAjaxGetRequest('/index.php?r=api/notification/index');
        $I->seeResponseCodeIs(200);
        $notifications = $this->json($I)['data'];
        $I->assertSame('Api customer notice ' . $this->suffix, $notifications[0]['message']);
        $I->assertFalse(in_array($foreignNotification->message, array_column($notifications, 'message'), true));

        $notificationId = (int) $notifications[0]['id'];
        $I->sendAjaxPostRequest('/index.php?r=api/notification/read&id=' . $notificationId);
        $I->seeResponseCodeIs(200);
        $I->assertNotNull($this->json($I)['data']['read_at']);

        $I->sendAjaxPostRequest('/index.php?r=api/notification/read&id=' . $foreignNotification->id);
        $I->seeResponseCodeIs(404);

        $I->sendAjaxRequest('DELETE', '/index.php?r=api/notification/delete&id=' . $notificationId);
        $I->seeResponseCodeIs(200);
        $I->assertTrue($this->json($I)['data']['deleted']);

        $I->sendAjaxGetRequest('/index.php?r=api/notification/unread-count');
        $I->assertSame(0, $this->json($I)['data']['count']);
    }

    public function favoritesAreOwnerScopedAndStaffIsForbidden(FunctionalTester $I): void
    {
        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->login($I, $this->customer->username));

        $I->sendAjaxPostRequest('/index.php?r=api/favorite/add', ['product_id' => $this->product->id]);
        $I->seeResponseCodeIs(201);
        $I->assertTrue($this->json($I)['data']['added']);

        $I->sendAjaxGetRequest('/index.php?r=api/favorite/index');
        $I->seeResponseCodeIs(200);
        $I->assertSame((int) $this->product->id, $this->json($I)['data'][0]['product_id']);

        $I->sendAjaxRequest('DELETE', '/index.php?r=api/favorite/remove&id=' . $this->product->id);
        $I->seeResponseCodeIs(200);
        $I->assertTrue($this->json($I)['data']['removed']);

        $staff = $this->user($I, 'api_staff_' . $this->suffix, 'staff');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->login($I, $staff->username));
        $I->sendAjaxPostRequest('/index.php?r=api/favorite/add', ['product_id' => $this->product->id]);
        $I->seeResponseCodeIs(403);
    }

    public function reviewsRequireCompletedOwnedOrder(FunctionalTester $I): void
    {
        $order = $this->completedOrder($I, $this->customer);
        $payload = [
            'product_id' => $this->product->id,
            'order_id' => $order->id,
            'rating' => 5,
            'review' => 'Great api coffee ' . $this->suffix,
        ];

        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->login($I, $this->customer->username));
        $I->sendAjaxGetRequest('/index.php?r=api/review/index&product_id=' . $this->product->id);
        $I->assertSame($order->id, $this->json($I)['data']['eligible_orders'][0]['id']);

        $I->sendAjaxPostRequest('/index.php?r=api/review/create', $payload);
        $I->seeResponseCodeIs(201);
        $I->assertSame(5, $this->json($I)['data']['rating']);

        $I->sendAjaxGetRequest('/index.php?r=api/review/index&product_id=' . $this->product->id);
        $I->seeResponseCodeIs(200);
        $reviews = $this->json($I)['data'];
        $I->assertSame($payload['review'], $reviews['reviews'][0]['review']);
        $I->assertSame(1, $reviews['summary']['count']);
        $I->assertCount(0, $reviews['eligible_orders']);

        $I->sendAjaxPostRequest('/index.php?r=api/review/create', $payload);
        $I->seeResponseCodeIs(400);

        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->login($I, $this->other->username));
        $I->sendAjaxPostRequest('/index.php?r=api/review/create', $payload);
        $I->seeResponseCodeIs(400);
    }

    private function addToCart(FunctionalTester $I, int $quantity): void
    {
        $I->sendAjaxPostRequest('/index.php?r=api/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => $quantity,
        ]);
        $I->seeResponseCodeIs(200);
    }

    private function user(FunctionalTester $I, string $username, string $role): User
    {
        $user = new User([
            'username' => $username,
            'email' => $username . '@api-customer.test',
            'full_name' => 'API customer test ' . $role,
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->setPassword(self::PASSWORD);
        $user->generateAuthKey();
        $I->assertTrue($user->save(), json_encode($user->getErrors()));
        $auth = Yii::$app->authManager;
        $I->assertNotNull($auth->getRole($role));
        $auth->assign($auth->getRole($role), $user->id);

        return $user;
    }

    private function product(FunctionalTester $I): Product
    {
        $category = new Category([
            'name' => 'Api customer category ' . $this->suffix,
            'status' => Category::STATUS_ACTIVE,
        ]);
        $I->assertTrue($category->save(), json_encode($category->getErrors()));

        $product = new Product([
            'name' => 'Api customer product ' . $this->suffix,
            'category_id' => $category->id,
            'price' => '40.00',
            'status' => Product::STATUS_ACTIVE,
        ]);
        $I->assertTrue($product->save(), json_encode($product->getErrors()));

        return $product;
    }

    private function points(FunctionalTester $I, User $user, int $points): bool
    {
        $transaction = new LoyaltyPointTransaction([
            'user_id' => $user->id,
            'points' => $points,
            'transaction_type' => LoyaltyPointTransaction::TYPE_EARN,
            'remarks' => 'Api customer fixture',
        ]);

        return $transaction->save();
    }

    private function reward(FunctionalTester $I, int $cost): Reward
    {
        $reward = new Reward([
            'name' => 'Api customer reward ' . $this->suffix,
            'description' => 'Api fixture',
            'point_cost' => $cost,
            'product_id' => $this->product->id,
            'status' => Reward::STATUS_ACTIVE,
        ]);
        $I->assertTrue($reward->save(), json_encode($reward->getErrors()));
        $this->reward = $reward;

        return $reward;
    }

    private function notification(FunctionalTester $I, User $user, string $message): Notification
    {
        NotificationService::notifyUser(
            (int) $user->id,
            'system_notice',
            $message,
            '/notifications',
            null,
            Notification::CATEGORY_SYSTEM,
        );
        $notification = Notification::find()->where(['message' => $message])->one();
        $I->assertNotNull($notification);

        return $notification;
    }

    private function completedOrder(FunctionalTester $I, User $user): Order
    {
        $order = new Order([
            'user_id' => $user->id,
            'order_number' => 'API-' . $this->suffix,
            'status' => Order::STATUS_COMPLETED,
            'total_amount' => '40.00',
        ]);
        $I->assertTrue($order->save(), json_encode($order->getErrors()));

        $item = new OrderItem([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => 1,
            'unit_price' => '40.00',
            'subtotal' => '40.00',
        ]);
        $I->assertTrue($item->save(), json_encode($item->getErrors()));

        return $order;
    }

    private function login(FunctionalTester $I, string $username): string
    {
        $I->sendAjaxPostRequest('/index.php?r=api/auth/login', [
            'username' => $username,
            'password' => self::PASSWORD,
        ]);
        $I->seeResponseCodeIs(200);

        return (string) $this->json($I)['data']['token'];
    }

    /**
     * @return array<string, mixed>
     */
    private function json(FunctionalTester $I): array
    {
        return json_decode($I->grabPageSource(), true, 512, JSON_THROW_ON_ERROR);
    }
}

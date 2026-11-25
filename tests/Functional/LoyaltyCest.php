<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\models\ActivityLog;
use app\models\LoyaltyPointTransaction;
use app\models\Notification;
use app\models\Order;
use app\models\User;
use app\services\LoyaltyService;
use app\services\NotificationService;
use app\services\OrderStatusService;
use app\tests\Support\FunctionalTester;
use Yii;
use yii\rbac\DbManager;
use yii\web\BadRequestHttpException;

final class LoyaltyCest
{
    public function _before(FunctionalTester $I): void
    {
        Yii::$app->set('authManager', DbManager::class);
        $auth = Yii::$app->authManager;
        $customer = $auth->getRole('customer');
        if ($customer === null) {
            $customer = $auth->createRole('customer');
            $auth->add($customer);
        }
        foreach (['customer.access', 'loyalty.view', 'loyalty.redeem'] as $name) {
            $permission = $auth->getPermission($name);
            if ($permission === null) {
                $permission = $auth->createPermission($name);
                $permission->description = $name;
                $auth->add($permission);
            }
            if (!$auth->hasChild($customer, $permission)) {
                $auth->addChild($customer, $permission);
            }
        }
    }

    public function completedOrderEarnsFloorPointsOnce(FunctionalTester $I): void
    {
        $user = $this->user($I, 'earn');
        $order = $this->order($I, $user, '1250');

        LoyaltyService::earnForCompletedOrder($order);
        LoyaltyService::earnForCompletedOrder($order);

        $summary = LoyaltyService::summary((int) $user->id);
        $I->assertSame(12, $summary['current']);
        $I->assertSame(12, $summary['lifetimeEarned']);
        $I->assertSame(1, (int) LoyaltyPointTransaction::find()
            ->where(['user_id' => $user->id, 'order_id' => $order->id, 'transaction_type' => LoyaltyPointTransaction::TYPE_EARN])
            ->count());
    }

    public function orderBelowOneHundredPesosEarnsNothing(FunctionalTester $I): void
    {
        $user = $this->user($I, 'small');
        $order = $this->order($I, $user, '99.99');

        LoyaltyService::earnForCompletedOrder($order);

        $I->assertSame(0, LoyaltyService::balance((int) $user->id));
        $I->assertSame(0, (int) LoyaltyPointTransaction::find()
            ->where(['user_id' => $user->id, 'order_id' => $order->id])
            ->count());
    }

    public function completingOrderThroughStatusServiceAwardsPointsOnce(FunctionalTester $I): void
    {
        $user = $this->user($I, 'flow');
        $order = $this->order($I, $user, '250', Order::STATUS_READY_FOR_PICKUP);

        (new OrderStatusService())->process($order, (int) $user->id);

        $I->assertSame(2, LoyaltyService::balance((int) $user->id));
        $I->assertSame(1, (int) LoyaltyPointTransaction::find()
            ->where(['user_id' => $user->id, 'order_id' => $order->id, 'transaction_type' => LoyaltyPointTransaction::TYPE_EARN])
            ->count());
    }

    public function redemptionDeductsPointsAndCreatesNotification(FunctionalTester $I): void
    {
        $user = $this->user($I, 'redeem');
        LoyaltyService::earnForCompletedOrder($this->order($I, $user, '5000'));
        $I->amLoggedInAs($user);
        $I->amOnRoute('loyalty/index');
        $I->see('Redeem your points', 'h2');
        $I->see('Your loyalty snapshot');
        $I->seeElement('.navbar [aria-label*="My rewards: 50 points"]');
        $I->see('Lifetime earned', '.dropdown-menu');
        $I->see('Lifetime redeemed', '.dropdown-menu');
        $I->submitForm('form[action*="loyalty%2Fredeem"]', []);

        $I->assertSame(0, LoyaltyService::balance((int) $user->id));
        $I->assertSame(50, LoyaltyService::lifetimeRedeemed((int) $user->id));
        $notification = Notification::find()
            ->where(['user_id' => $user->id, 'type' => NotificationService::TYPE_LOYALTY_REDEEMED])
            ->one();
        $I->assertNotNull($notification);
        $I->assertStringContainsString('Free Coffee', (string) $notification->message);
        $I->assertStringContainsString('50 points were deducted', (string) $notification->message);
        $I->assertStringContainsString('Remaining balance: 0 points', (string) $notification->message);
    }

    public function tierUpgradeNotificationIsSentOnce(FunctionalTester $I): void
    {
        $user = $this->user($I, 'tier');
        $order = $this->order($I, $user, '10000');

        LoyaltyService::earnForCompletedOrder($order);
        LoyaltyService::earnForCompletedOrder($order);

        $summary = LoyaltyService::summary((int) $user->id);
        $I->assertSame(LoyaltyService::TIER_SILVER, $summary['tier']);
        $I->assertSame(1, (int) Notification::find()
            ->where(['user_id' => $user->id, 'type' => NotificationService::TYPE_LOYALTY_TIER_UP])
            ->count());
    }

    public function adminCanOpenLoyaltyPages(FunctionalTester $I): void
    {
        $I->amLoggedInAs($this->adminUser($I));

        foreach ([
            'reward/index' => 'Rewards',
            'admin-loyalty/index' => 'Loyalty overview',
            'admin-loyalty/transactions' => 'Loyalty transactions',
            'admin-loyalty/redemptions' => 'Reward redemption history',
            'admin-loyalty/adjust' => 'Adjust loyalty points',
            'report/loyalty' => 'Loyalty report',
        ] as $route => $heading) {
            $I->amOnRoute($route);
            $I->see($heading, 'h1');
        }
    }

    public function adminAdjustmentCreatesLedgerAndAuditEntry(FunctionalTester $I): void
    {
        $user = $this->user($I, 'credit');
        $admin = $this->adminUser($I);
        $I->amLoggedInAs($admin);
        $I->amOnRoute('admin-loyalty/adjust');
        $I->submitForm('form[action*="admin-loyalty%2Fadjust"]', ['AdjustPointsForm' => [
            'userId' => $user->id,
            'points' => 25,
            'remarks' => 'Functional test credit',
        ]]);

        $I->see('Loyalty points adjusted and logged.');
        $I->assertSame(25, LoyaltyService::balance((int) $user->id));
        $I->assertSame(1, (int) ActivityLog::find()
            ->where(['event' => ActivityLog::POINTS_ADJUSTED, 'user_id' => $admin->id])
            ->count());
    }

    public function negativeAdjustmentCannotDriveBalanceBelowZero(FunctionalTester $I): void
    {
        $user = $this->user($I, 'adjust');
        $form = new \app\models\AdjustPointsForm([
            'userId' => $user->id,
            'points' => -5,
            'remarks' => 'Functional test deduction',
        ]);

        try {
            LoyaltyService::adjust($form, (int) $user->id);
            $I->fail('A negative adjustment below zero should be rejected.');
        } catch (BadRequestHttpException $exception) {
            $I->assertSame(0, LoyaltyService::balance((int) $user->id));
        }
    }

    private function adminUser(FunctionalTester $I): User
    {
        $user = $this->user($I, 'admin');
        $auth = Yii::$app->authManager;
        $role = $auth->createRole('loyalty_admin_' . substr(bin2hex(random_bytes(4)), 0, 8));
        $auth->add($role);
        foreach ([
            'reward.index', 'reward.view', 'reward.create', 'reward.update', 'reward.delete',
            'loyalty.view', 'loyalty.transaction.index', 'loyalty.adjust', 'reports.view',
        ] as $name) {
            $permission = $auth->getPermission($name);
            if ($permission === null) {
                $permission = $auth->createPermission($name);
                $auth->add($permission);
            }
            $auth->addChild($role, $permission);
        }
        $auth->assign($role, $user->id);

        return $user;
    }

    private function user(FunctionalTester $I, string $prefix): User
    {
        $suffix = substr(bin2hex(random_bytes(4)), 0, 8);
        $user = new User([
            'username' => $prefix . '_' . $suffix,
            'email' => $prefix . '_' . $suffix . '@example.test',
            'full_name' => 'Loyalty ' . $prefix,
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->setPassword('TestPassword123!');
        $user->generateAuthKey();
        $I->assertTrue($user->save(), json_encode($user->errors));

        $auth = Yii::$app->authManager;
        $auth->assign($auth->getRole('customer'), $user->id);

        return $user;
    }

    private function order(
        FunctionalTester $I,
        User $user,
        string $total,
        string $status = Order::STATUS_COMPLETED,
    ): Order {
        $order = new Order([
            'user_id' => $user->id,
            'order_number' => 'LOY-' . substr(bin2hex(random_bytes(5)), 0, 10),
            'status' => $status,
            'total_amount' => $total,
        ]);
        $I->assertTrue($order->save(), json_encode($order->errors));

        return $order;
    }
}

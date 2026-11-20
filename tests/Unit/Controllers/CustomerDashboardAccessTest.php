<?php

declare(strict_types=1);

namespace app\tests\Unit\Controllers;

use app\controllers\CustomerController;
use app\controllers\SiteController;
use app\models\User;
use Codeception\Test\Unit;
use Yii;
use yii\base\Action;
use yii\rbac\DbManager;

final class CustomerDashboardAccessTest extends Unit
{
    public function testAssignedCustomerCanReachDashboard(): void
    {
        Yii::$app->set('authManager', DbManager::class);
        $name = 'dashboard_' . bin2hex(random_bytes(4));
        $user = new User([
            'username' => $name,
            'email' => $name . '@example.test',
            'full_name' => 'Dashboard Customer',
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->setPassword('Dashboard123!');
        $user->generateAuthKey();
        self::assertTrue($user->save(), json_encode($user->getErrors()));
        Yii::$app->authManager->assign(Yii::$app->authManager->getRole('customer'), $user->id);
        Yii::$app->user->setIdentity($user);
        Yii::$app->request->setScriptUrl('/index.php');

        $site = Yii::createObject(SiteController::class, ['site', Yii::$app]);
        self::assertStringContainsString('customer%2Fdashboard', $site->actionDashboard()->headers->get('Location'));
        $customer = new CustomerController('customer', Yii::$app);
        Yii::$app->controller = $customer;
        self::assertTrue($customer->beforeAction(new Action('dashboard', $customer)));
        self::assertStringContainsString('Dashboard Customer', $customer->actionDashboard());
    }
}

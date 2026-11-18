<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\controllers\OrderController;
use Codeception\Test\Unit;
use Yii;
use yii\base\Action;
use yii\web\ForbiddenHttpException;
use yii\web\User;

final class OrderStatusAccessTest extends Unit
{
    public function testStatusActionsRejectUsersWithoutTheirPermission(): void
    {
        $controller = new OrderController('order', Yii::$app);
        $filter = $controller->getBehavior('access');
        $user = $this->getMockBuilder(User::class)
            ->setConstructorArgs([['identityClass' => \app\models\User::class]])
            ->onlyMethods(['getIsGuest', 'can'])
            ->getMock();
        $user->method('getIsGuest')->willReturn(false);
        $user->method('can')->willReturn(false);
        Yii::$app->set('user', $user);
        $filter->user = $user;

        foreach (['process', 'cancel'] as $action) {
            try {
                $filter->beforeAction(new Action($action, $controller));
                self::fail("{$action} should require an RBAC permission.");
            } catch (ForbiddenHttpException $exception) {
                self::assertSame(403, $exception->statusCode);
            }
        }
    }
}

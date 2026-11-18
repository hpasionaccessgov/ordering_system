<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\controllers\CartController;
use Codeception\Test\Unit;
use Yii;
use yii\base\Action;
use yii\web\ForbiddenHttpException;
use yii\web\User;

final class CartAccessTest extends Unit
{
    public function testEachCartActionRequiresItsOwnPermission(): void
    {
        $controller = new CartController('cart', Yii::$app);
        $filter = $controller->getBehavior('access');

        foreach (['index', 'add', 'update', 'remove', 'checkout'] as $actionName) {
            $user = $this->getMockBuilder(User::class)
                ->setConstructorArgs([['identityClass' => \app\models\User::class]])
                ->onlyMethods(['getIsGuest', 'can'])
                ->getMock();
            $user->method('getIsGuest')->willReturn(false);
            $user->method('can')->willReturnCallback(
                static fn(string $permission): bool => $permission === 'cart.' . $actionName,
            );
            Yii::$app->set('user', $user);
            $filter->user = $user;

            self::assertTrue($filter->beforeAction(new Action($actionName, $controller)));

            $deniedAction = $actionName === 'index' ? 'checkout' : 'index';
            try {
                $filter->beforeAction(new Action($deniedAction, $controller));
                self::fail("{$deniedAction} should require a separate cart permission.");
            } catch (ForbiddenHttpException $exception) {
                self::assertSame(403, $exception->statusCode);
            }
        }
    }
}

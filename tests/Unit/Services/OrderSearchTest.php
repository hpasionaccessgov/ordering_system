<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\controllers\OrderController;
use Codeception\Test\Unit;
use Yii;
use yii\web\User;

final class OrderSearchTest extends Unit
{
    public function testSearchKeepsCustomerOwnershipAndLimitsResults(): void
    {
        $user = $this->getMockBuilder(User::class)
            ->setConstructorArgs([['identityClass' => \app\models\User::class]])
            ->onlyMethods(['getId', 'can'])
            ->getMock();
        $user->method('getId')->willReturn(41);
        $user->method('can')->willReturn(false);
        Yii::$app->set('user', $user);
        Yii::$app->request->setQueryParams(['q' => '12']);

        $controller = $this->getMockBuilder(OrderController::class)
            ->setConstructorArgs(['order', Yii::$app])
            ->onlyMethods(['render'])
            ->getMock();
        $controller->expects(self::once())
            ->method('render')
            ->willReturnCallback(static function (string $view, array $params): string {
                self::assertSame('index', $view);
                self::assertSame([
                    'and',
                    ['user_id' => 41],
                    ['or', ['like', 'status', '12'], ['id' => '12']],
                ], $params['dataProvider']->query->where);
                self::assertSame(10, $params['dataProvider']->pagination->getPageSize());

                return 'rendered';
            });

        self::assertSame('rendered', $controller->actionIndex());
    }
}

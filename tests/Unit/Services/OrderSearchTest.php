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
        Yii::$app->request->setQueryParams(['q' => '12', 'status' => 'pending']);

        $controller = $this->getMockBuilder(OrderController::class)
            ->setConstructorArgs(['order', Yii::$app])
            ->onlyMethods(['render'])
            ->getMock();
        $controller->expects(self::once())
            ->method('render')
            ->willReturnCallback(static function (string $view, array $params): string {
                self::assertSame('index', $view);
                self::assertSame(['user_id' => 41], $params['dataProvider']->query->where[1]);
                self::assertSame(
                    ['like', 'order_number', '12'],
                    $params['dataProvider']->query->where[2][2],
                );
                self::assertSame(
                    ['id' => '12'],
                    $params['dataProvider']->query->where[2][4],
                );
                self::assertSame(
                    ['status' => 'pending'],
                    $params['dataProvider']->query->where[3],
                );
                self::assertSame('pending', $params['status']);
                self::assertSame(10, $params['dataProvider']->pagination->getPageSize());
                self::assertSame(
                    ['created_at' => SORT_DESC, 'id' => SORT_DESC],
                    $params['dataProvider']->sort->getOrders(),
                );

                return 'rendered';
            });

        self::assertSame('rendered', $controller->actionIndex());
    }
}

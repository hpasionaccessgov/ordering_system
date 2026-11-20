<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\controllers\OrderController;
use app\models\Order;
use app\models\OrderSearch;
use app\models\User as Account;
use Codeception\Test\Unit;
use Yii;
use yii\web\User;

final class OrderSearchTest extends Unit
{
    public function testCustomerCannotSearchAnotherCustomersOrders(): void
    {
        $suffix = bin2hex(random_bytes(4));
        $users = [];
        foreach (['one', 'two'] as $name) {
            $account = new Account([
                'username' => $name . '_' . $suffix,
                'email' => $name . '_' . $suffix . '@example.test',
                'full_name' => 'Search customer',
                'status' => Account::STATUS_ACTIVE,
            ]);
            $account->setPassword('SearchTest123!');
            $account->generateAuthKey();
            self::assertTrue($account->save(), json_encode($account->getErrors()));
            $users[] = $account;
        }
        $order = new Order([
            'user_id' => $users[1]->id,
            'order_number' => 'SEARCH-' . $suffix,
            'status' => Order::STATUS_PENDING,
            'total_amount' => 10,
        ]);
        self::assertTrue($order->save(), json_encode($order->getErrors()));

        $params = ['q' => $order->order_number, 'status' => 'pending'];
        self::assertSame(0, (new OrderSearch())->search($params, $users[0]->id, false)->getTotalCount());
        self::assertSame(1, (new OrderSearch())->search($params, $users[0]->id, true)->getTotalCount());
        self::assertSame(0, (new OrderSearch())->search(['q' => [$order->order_number]], $users[0]->id, false)->getTotalCount());
    }

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
                self::assertSame(8, $params['dataProvider']->pagination->getPageSize());
                self::assertSame(
                    ['created_at' => SORT_DESC, 'id' => SORT_DESC],
                    $params['dataProvider']->sort->getOrders(),
                );

                return 'rendered';
            });

        self::assertSame('rendered', $controller->actionIndex());
    }
}

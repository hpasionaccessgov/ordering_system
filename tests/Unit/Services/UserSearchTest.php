<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\controllers\AdminUserController;
use Codeception\Test\Unit;
use Yii;
use yii\rbac\DbManager;

final class UserSearchTest extends Unit
{
    public function testSearchMatchesAccountFieldsAndLimitsResults(): void
    {
        $auth = $this->getMockBuilder(DbManager::class)
            ->onlyMethods(['getRoles'])
            ->getMock();
        $auth->method('getRoles')->willReturn([]);
        Yii::$app->set('authManager', $auth);
        Yii::$app->request->setQueryParams(['q' => '  Ada  ']);

        $controller = $this->getMockBuilder(AdminUserController::class)
            ->setConstructorArgs(['admin-user', Yii::$app])
            ->onlyMethods(['render'])
            ->getMock();
        $controller->expects(self::once())
            ->method('render')
            ->willReturnCallback(static function (string $view, array $params): string {
                self::assertSame('index', $view);
                self::assertSame('Ada', $params['search']);
                self::assertSame([
                    'or',
                    ['like', 'username', 'Ada'],
                    ['like', 'full_name', 'Ada'],
                    ['like', 'email', 'Ada'],
                ], $params['dataProvider']->query->where);
                self::assertSame(8, $params['dataProvider']->pagination->getPageSize());

                return 'rendered';
            });

        self::assertSame('rendered', $controller->actionList());
    }
}

<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use Codeception\Test\Unit;
use Yii;
use yii\rbac\DbManager;
use yii\rbac\Permission;
use yii\rbac\Role;

require_once dirname(__DIR__, 3) . '/migrations/m261118_141000_add_cart_permissions.php';

final class CartPermissionMigrationTest extends Unit
{
    public function testMigrationGrantsCartPermissionsToRolesWithOrderCreate(): void
    {
        $orderCreate = new Permission(['name' => 'order.create']);
        $customer = new Role(['name' => 'customer']);
        $custom = new Role(['name' => 'counter']);
        $observer = new Role(['name' => 'observer']);
        $permissions = ['order.create' => $orderCreate];
        $children = [
            'customer' => ['order.create' => true],
            'counter' => ['order.create' => true],
            'observer' => [],
        ];
        $auth = $this->getMockBuilder(DbManager::class)
            ->onlyMethods([
                'getPermission',
                'getRole',
                'add',
                'getRoles',
                'hasChild',
                'addChild',
            ])
            ->getMock();
        $auth->method('getPermission')->willReturnCallback(
            static fn(string $name) => $permissions[$name] ?? null,
        );
        $auth->method('getRole')->willReturn(null);
        $auth->method('add')->willReturnCallback(
            static function ($item) use (&$permissions): bool {
                $permissions[$item->name] = $item;

                return true;
            },
        );
        $auth->method('getRoles')->willReturn([
            'customer' => $customer,
            'counter' => $custom,
            'observer' => $observer,
        ]);
        $auth->method('hasChild')->willReturnCallback(
            static fn($role, $item): bool => isset($children[$role->name][$item->name]),
        );
        $auth->method('addChild')->willReturnCallback(
            static function ($role, $item) use (&$children): bool {
                $children[$role->name][$item->name] = true;

                return true;
            },
        );
        Yii::$app->set('authManager', $auth);

        $migration = $this->getMockBuilder(\m261118_141000_add_cart_permissions::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        $migration->safeUp();
        $migration->safeUp();

        $expected = [
            'cart.index',
            'cart.add',
            'cart.update',
            'cart.remove',
            'cart.checkout',
        ];
        self::assertSame(array_merge(['order.create'], $expected), array_keys($permissions));
        foreach (['customer', 'counter'] as $roleName) {
            self::assertSame($expected, array_values(array_diff(array_keys($children[$roleName]), ['order.create'])));
        }
        self::assertSame([], array_keys($children['observer']));
    }
}

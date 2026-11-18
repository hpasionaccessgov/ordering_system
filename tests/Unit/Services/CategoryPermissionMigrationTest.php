<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use Codeception\Test\Unit;
use Yii;
use yii\rbac\DbManager;
use yii\rbac\Role;

require_once dirname(__DIR__, 3) . '/migrations/m261118_105000_add_category_permissions.php';

final class CategoryPermissionMigrationTest extends Unit
{
    public function testMigrationIsIdempotentAndGrantsOnlyBuiltInAdmin(): void
    {
        $permissions = [];
        $children = [];
        $admin = new Role(['name' => 'admin']);
        $auth = $this->getMockBuilder(DbManager::class)
            ->onlyMethods(['getRole', 'getPermission', 'add', 'hasChild', 'addChild'])
            ->getMock();
        $auth->method('getRole')->willReturnCallback(
            static fn(string $name) => $name === 'admin' ? $admin : null,
        );
        $auth->method('getPermission')->willReturnCallback(
            static function (string $name) use (&$permissions) {
                return $permissions[$name] ?? null;
            },
        );
        $auth->method('add')->willReturnCallback(static function ($item) use (&$permissions): bool {
            $permissions[$item->name] = $item;

            return true;
        });
        $auth->method('hasChild')->willReturnCallback(
            static function ($role, $permission) use (&$children): bool {
                return isset($children[$role->name][$permission->name]);
            },
        );
        $auth->method('addChild')->willReturnCallback(
            static function ($role, $permission) use (&$children): bool {
                $children[$role->name][$permission->name] = true;

                return true;
            },
        );
        Yii::$app->set('authManager', $auth);

        $migration = $this->getMockBuilder(\m261118_105000_add_category_permissions::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        $migration->safeUp();
        $migration->safeUp();

        self::assertSame(
            ['category.index', 'category.view', 'category.create', 'category.update', 'category.delete'],
            array_keys($permissions),
        );
        self::assertSame(['admin'], array_keys($children));
        self::assertSame(array_keys($permissions), array_keys($children['admin']));
    }
}

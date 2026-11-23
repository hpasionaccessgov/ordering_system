<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use Codeception\Test\Unit;
use Yii;
use yii\rbac\DbManager;

require_once dirname(__DIR__, 3) . '/migrations/m261120_090000_grant_staff_product_delete.php';

final class StaffProductDeleteMigrationTest extends Unit
{
    public function testStaffGrantIsIdempotent(): void
    {
        Yii::$app->set('authManager', DbManager::class);
        $auth = Yii::$app->authManager;
        $staff = $auth->getRole('staff');
        $permission = $auth->getPermission('product.delete');
        self::assertNotNull($staff);
        self::assertNotNull($permission);
        if ($auth->hasChild($staff, $permission)) {
            $auth->removeChild($staff, $permission);
        }

        $migration = new \m261120_090000_grant_staff_product_delete();
        $migration->safeUp();
        $migration->safeUp();
        self::assertTrue($auth->hasChild($staff, $permission));
    }
}

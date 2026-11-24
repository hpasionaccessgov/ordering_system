<?php

declare(strict_types=1);

use yii\db\Migration;
use yii\rbac\Role;

/**
 * Lets staff create and delete inventory items, not just view and update them.
 */
class m261124_040000_grant_staff_full_inventory_access extends Migration
{
    private const PERMISSIONS = [
        'inventory.create',
        'inventory.delete',
    ];

    public function safeUp(): void
    {
        $auth = Yii::$app->authManager;
        $staff = $auth->getRole('staff');
        if (!$staff instanceof Role) {
            throw new RuntimeException('The staff RBAC role does not exist. Run the RBAC migrations first.');
        }

        foreach (self::PERMISSIONS as $name) {
            $permission = $auth->getPermission($name);
            if ($permission === null) {
                throw new RuntimeException("Permission '{$name}' does not exist. Run the inventory permissions migration first.");
            }
            if (!$auth->hasChild($staff, $permission) && !$auth->addChild($staff, $permission)) {
                throw new RuntimeException("Could not grant '{$name}' to the staff role.");
            }
        }
    }

    public function safeDown(): void
    {
        $auth = Yii::$app->authManager;
        $staff = $auth->getRole('staff');
        if (!$staff instanceof Role) {
            return;
        }

        foreach (self::PERMISSIONS as $name) {
            $permission = $auth->getPermission($name);
            if ($permission !== null && $auth->hasChild($staff, $permission)) {
                $auth->removeChild($staff, $permission);
            }
        }
    }
}

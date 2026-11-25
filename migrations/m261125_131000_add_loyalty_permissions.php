<?php

declare(strict_types=1);

use yii\db\Migration;
use yii\rbac\Permission;
use yii\rbac\Role;

/**
 * Grants loyalty access and reward administration permissions.
 */
class m261125_131000_add_loyalty_permissions extends Migration
{
    /** @var array<string, list<string>> */
    private const GRANTS = [
        'loyalty.view' => ['customer', 'staff', 'admin', 'seed'],
        'loyalty.redeem' => ['customer', 'staff', 'admin', 'seed'],
        'loyalty.transaction.index' => ['staff', 'admin'],
        'loyalty.adjust' => ['admin'],
        'loyalty.report' => ['staff', 'admin'],
        'reward.index' => ['admin'],
        'reward.view' => ['admin'],
        'reward.create' => ['admin'],
        'reward.update' => ['admin'],
        'reward.delete' => ['admin'],
    ];

    public function safeUp(): void
    {
        $auth = Yii::$app->authManager;
        $permissions = [];

        foreach (array_keys(self::GRANTS) as $name) {
            $permission = $auth->getPermission($name);
            if ($permission === null) {
                $permission = $auth->createPermission($name);
                $permission->description = $name;
                if (!$auth->add($permission)) {
                    throw new RuntimeException("Could not create permission '{$name}'.");
                }
            }
            $permissions[$name] = $permission;
        }

        foreach (self::GRANTS as $name => $roles) {
            foreach ($roles as $roleName) {
                $role = $auth->getRole($roleName);
                if (!$role instanceof Role) {
                    continue;
                }
                if (!$auth->hasChild($role, $permissions[$name]) && !$auth->addChild($role, $permissions[$name])) {
                    throw new RuntimeException("Could not grant '{$name}' to '{$roleName}'.");
                }
            }
        }
    }

    public function safeDown(): void
    {
        $auth = Yii::$app->authManager;

        foreach (self::GRANTS as $name => $roles) {
            foreach ($roles as $roleName) {
                $role = $auth->getRole($roleName);
                $permission = $auth->getPermission($name);
                if ($role instanceof Role && $permission instanceof Permission && $auth->hasChild($role, $permission)) {
                    $auth->removeChild($role, $permission);
                }
            }
            $permission = $auth->getPermission($name);
            if ($permission instanceof Permission) {
                $auth->remove($permission);
            }
        }
    }
}

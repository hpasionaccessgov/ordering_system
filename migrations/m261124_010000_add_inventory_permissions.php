<?php

declare(strict_types=1);

use yii\db\Migration;
use yii\rbac\Role;

class m261124_010000_add_inventory_permissions extends Migration
{
    private const PERMISSIONS = [
        'inventory.index',
        'inventory.view',
        'inventory.create',
        'inventory.update',
        'inventory.delete',
    ];

    /** @var array<string, list<string>> */
    private const ROLE_GRANTS = [
        'admin' => self::PERMISSIONS,
        'seed' => self::PERMISSIONS,
        'staff' => ['inventory.index', 'inventory.view', 'inventory.update'],
    ];

    public function safeUp(): void
    {
        $auth = Yii::$app->authManager;
        $permissions = [];

        foreach (self::PERMISSIONS as $name) {
            $permission = $auth->getPermission($name);
            if ($permission === null) {
                if ($auth->getRole($name) !== null) {
                    throw new RuntimeException("Cannot create '{$name}': an RBAC role already uses that name.");
                }
                $permission = $auth->createPermission($name);
                $permission->description = $name;
                if (!$auth->add($permission)) {
                    throw new RuntimeException("Could not create the '{$name}' permission.");
                }
            }
            $permissions[$name] = $permission;
        }

        foreach (self::ROLE_GRANTS as $roleName => $names) {
            $role = $auth->getRole($roleName);
            if (!$role instanceof Role) {
                continue;
            }
            foreach ($names as $name) {
                if (!$auth->hasChild($role, $permissions[$name]) && !$auth->addChild($role, $permissions[$name])) {
                    throw new RuntimeException("Could not grant '{$name}' to the '{$roleName}' role.");
                }
            }
        }
    }

    public function safeDown(): void
    {
        $auth = Yii::$app->authManager;

        foreach (self::ROLE_GRANTS as $roleName => $names) {
            $role = $auth->getRole($roleName);
            if (!$role instanceof Role) {
                continue;
            }
            foreach ($names as $name) {
                $permission = $auth->getPermission($name);
                if ($permission !== null && $auth->hasChild($role, $permission)) {
                    $auth->removeChild($role, $permission);
                }
            }
        }

        foreach (self::PERMISSIONS as $name) {
            $permission = $auth->getPermission($name);
            if ($permission !== null) {
                $auth->remove($permission);
            }
        }
    }
}

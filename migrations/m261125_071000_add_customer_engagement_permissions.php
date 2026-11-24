<?php

declare(strict_types=1);

use yii\db\Migration;
use yii\rbac\Permission;
use yii\rbac\Role;

/**
 * Grants every authenticated role access to favorites and verified reviews.
 */
class m261125_071000_add_customer_engagement_permissions extends Migration
{
    private const PERMISSIONS = [
        'favorite.index',
        'favorite.add',
        'favorite.remove',
        'review.create',
    ];

    private const ROLES = ['customer', 'staff', 'admin', 'seed'];

    public function safeUp(): void
    {
        $auth = Yii::$app->authManager;
        $permissions = [];

        foreach (self::PERMISSIONS as $name) {
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

        foreach (self::ROLES as $roleName) {
            $role = $auth->getRole($roleName);
            if (!$role instanceof Role) {
                continue;
            }
            foreach ($permissions as $permission) {
                if (!$auth->hasChild($role, $permission) && !$auth->addChild($role, $permission)) {
                    throw new RuntimeException("Could not grant '{$permission->name}' to '{$roleName}'.");
                }
            }
        }
    }

    public function safeDown(): void
    {
        $auth = Yii::$app->authManager;

        foreach (self::ROLES as $roleName) {
            $role = $auth->getRole($roleName);
            if (!$role instanceof Role) {
                continue;
            }
            foreach (self::PERMISSIONS as $name) {
                $permission = $auth->getPermission($name);
                if ($permission instanceof Permission && $auth->hasChild($role, $permission)) {
                    $auth->removeChild($role, $permission);
                }
            }
        }

        foreach (self::PERMISSIONS as $name) {
            $permission = $auth->getPermission($name);
            if ($permission instanceof Permission) {
                $auth->remove($permission);
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace app\services;

use Yii;
use yii\base\InvalidConfigException;
use yii\rbac\Role;

final class AdminAccessRecovery
{
    public static function restoreAdminRoleAccess(): void
    {
        $auth = Yii::$app->authManager;
        $admin = $auth->getRole('admin');

        if (!$admin instanceof Role) {
            throw new InvalidConfigException('The admin RBAC role is missing. Run the RBAC migrations first.');
        }

        $permissionsToRestore = array_merge(
            ['admin.access'],
            RbacPermissionPolicy::accessDependents()['admin.access'],
        );

        foreach (array_unique($permissionsToRestore) as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            if ($permission === null) {
                $permission = $auth->createPermission($permissionName);
                $permission->description = $permissionName;
                if (!$auth->add($permission)) {
                    throw new \RuntimeException("Could not restore the '{$permissionName}' permission.");
                }
            }

            if (!$auth->hasChild($admin, $permission) && !$auth->addChild($admin, $permission)) {
                throw new \RuntimeException("Could not restore '{$permissionName}' on the admin role.");
            }
        }
    }
}

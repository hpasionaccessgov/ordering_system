<?php

declare(strict_types=1);

namespace app\services;

use Yii;

final class RbacPermissionPolicy
{
    public static function isAllowed(string $roleName, string $permissionName): bool
    {
        if (in_array($permissionName, ['admin.access', 'user.assignRole'], true)) {
            return $roleName === 'admin';
        }

        return true;
    }

    public static function isStaffAccessDisabledForCurrentUser(): bool
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);

        return !self::isStaffRoleAccessAllowed(
            array_keys($roles),
            Yii::$app->user->can('staff.access'),
        );
    }

    /**
     * @param list<string> $roleNames
     */
    public static function isStaffRoleAccessAllowed(array $roleNames, bool $hasStaffAccess): bool
    {
        return in_array('admin', $roleNames, true)
            || !in_array('staff', $roleNames, true)
            || $hasStaffAccess;
    }
}

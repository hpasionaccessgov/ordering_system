<?php

declare(strict_types=1);

namespace app\services;

use Yii;

final class RbacPermissionPolicy
{
    /**
     * Permissions that expose an existing record also require its view permission.
     *
     * @var array<string, list<string>>
     */
    private const VIEW_DEPENDENTS = [
        'user.view' => ['user.update'],
        'product.view' => ['product.update'],
        'order.view' => ['order.update', 'order.receipt.print'],
    ];

    /**
     * @return array<string, list<string>>
     */
    public static function viewDependents(): array
    {
        return self::VIEW_DEPENDENTS;
    }

    /**
     * @param list<string> $permissionNames
     * @return list<string>
     */
    public static function normalizeRolePermissions(array $permissionNames): array
    {
        $selected = array_fill_keys($permissionNames, true);

        foreach (self::VIEW_DEPENDENTS as $viewPermission => $dependentPermissions) {
            if (isset($selected[$viewPermission])) {
                continue;
            }

            foreach ($dependentPermissions as $dependentPermission) {
                unset($selected[$dependentPermission]);
            }
        }

        return array_keys($selected);
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

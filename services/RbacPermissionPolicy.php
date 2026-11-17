<?php

declare(strict_types=1);

namespace app\services;

use Yii;

final class RbacPermissionPolicy
{
    /**
     * Role-entry permissions that control feature bundles in the role editor.
     *
     * @var array<string, list<string>>
     */
    private const ACCESS_DEPENDENTS = [
        'admin.access' => [
            'staff.access',
            'customer.access',
            'user.assignRole',
            'user.index',
            'user.view',
            'user.create',
            'user.update',
            'user.delete',
            'product.index',
            'product.view',
            'product.create',
            'product.update',
            'product.archive',
            'product.delete',
            'order.index',
            'order.view',
            'order.create',
            'order.update',
            'order.cancel',
            'order.process',
            'order.receipt.print',
        ],
        'staff.access' => [
            'product.index',
            'product.view',
            'product.create',
            'product.update',
            'product.archive',
            'product.delete',
            'order.index',
            'order.view',
            'order.create',
            'order.update',
            'order.cancel',
            'order.process',
            'order.receipt.print',
        ],
        'customer.access' => [
            'product.index',
            'product.view',
            'order.index',
            'order.view',
            'order.create',
            'order.update',
            'order.cancel',
        ],
    ];

    /**
     * Module permissions controlled by each module's index checkbox.
     *
     * @var array<string, list<string>>
     */
    private const INDEX_DEPENDENTS = [
        'user.index' => ['user.view', 'user.create', 'user.delete', 'user.update'],
        'product.index' => [
            'product.view',
            'product.create',
            'product.update',
            'product.delete',
            'product.archive',
        ],
        'order.index' => [
            'order.view',
            'order.create',
            'order.update',
            'order.cancel',
            'order.process',
            'order.receipt.print',
        ],
    ];

    /**
     * Permissions that require access to an individual record.
     *
     * @var array<string, list<string>>
     */
    private const VIEW_DEPENDENTS = [
        'user.view' => ['user.create', 'user.delete', 'user.update'],
        'product.view' => ['product.create', 'product.update', 'product.delete', 'product.archive'],
        'order.view' => ['order.create', 'order.update', 'order.cancel', 'order.process', 'order.receipt.print'],
    ];

    /**
     * @return array<string, list<string>>
     */
    public static function indexDependents(): array
    {
        return self::INDEX_DEPENDENTS;
    }

    /**
     * @return array<string, list<string>>
     */
    public static function accessDependents(): array
    {
        return self::ACCESS_DEPENDENTS;
    }

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
    public static function normalizeRolePermissions(array $permissionNames, ?string $roleName = null): array
    {
        $selected = array_fill_keys($permissionNames, true);

        foreach (self::ACCESS_DEPENDENTS as $accessPermission => $dependentPermissions) {
            if (!isset($selected[$accessPermission])) {
                if ($accessPermission === 'admin.access' && $roleName !== 'admin') {
                    continue;
                }

                foreach ($dependentPermissions as $dependentPermission) {
                    unset($selected[$dependentPermission]);
                }

                continue;
            }

            foreach ($dependentPermissions as $dependentPermission) {
                $selected[$dependentPermission] = true;
            }
        }

        foreach (self::INDEX_DEPENDENTS as $indexPermission => $dependentPermissions) {
            if (isset($selected[$indexPermission])) {
                continue;
            }

            foreach ($dependentPermissions as $dependentPermission) {
                unset($selected[$dependentPermission]);
            }
        }

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

    /**
     * @param list<string> $permissionNames
     * @return list<string>
     */
    public static function expandIndexPermission(string $indexPermission, array $permissionNames): array
    {
        $selected = array_fill_keys($permissionNames, true);

        $selected[$indexPermission] = true;
        foreach (self::INDEX_DEPENDENTS[$indexPermission] ?? [] as $dependentPermission) {
            $selected[$dependentPermission] = true;
        }

        return array_keys($selected);
    }

    /**
     * @param list<string> $permissionNames
     * @return list<string>
     */
    public static function expandAccessPermission(string $accessPermission, array $permissionNames): array
    {
        $selected = array_fill_keys($permissionNames, true);
        $selected[$accessPermission] = true;

        foreach (self::ACCESS_DEPENDENTS[$accessPermission] ?? [] as $dependentPermission) {
            $selected[$dependentPermission] = true;
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

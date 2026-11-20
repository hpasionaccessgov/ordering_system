<?php

declare(strict_types=1);

namespace app\services;

final class RbacPermissionPolicy
{
    /**
     * Module permissions controlled by each module's index checkbox.
     *
     * @var array<string, list<string>>
     */
    private const INDEX_DEPENDENTS = [
        'user.index' => ['user.view', 'user.create', 'user.delete', 'user.update'],
        'category.index' => ['category.view', 'category.create', 'category.update', 'category.delete'],
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
        'cart.index' => [
            'cart.add',
            'cart.update',
            'cart.remove',
            'cart.checkout',
        ],
        'activityLog.index' => ['activityLog.view'],
        'notification.index' => ['notification.read'],
        'settings.index' => ['settings.update'],
    ];

    /**
     * Permissions that require access to an individual record.
     *
     * @var array<string, list<string>>
     */
    private const VIEW_DEPENDENTS = [
        'user.view' => ['user.create', 'user.delete', 'user.update'],
        'category.view' => ['category.create', 'category.update', 'category.delete'],
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
}

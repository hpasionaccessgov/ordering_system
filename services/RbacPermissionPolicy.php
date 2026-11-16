<?php

declare(strict_types=1);

namespace app\services;

final class RbacPermissionPolicy
{
    private const BUILT_IN_ROLE_PERMISSIONS = [
        'staff' => [
            'staff.access',
            'product.view',
            'product.create',
            'product.update',
            'product.archive',
            'order.view',
            'order.update',
            'order.cancel',
            'order.process',
        ],
        'customer' => [
            'customer.access',
            'product.view',
            'order.view',
            'order.create',
            'order.update',
            'order.cancel',
        ],
    ];

    public static function isAllowed(string $roleName, string $permissionName): bool
    {
        if ($roleName === 'admin') {
            return true;
        }

        if (isset(self::BUILT_IN_ROLE_PERMISSIONS[$roleName])) {
            return in_array(
                $permissionName,
                self::BUILT_IN_ROLE_PERMISSIONS[$roleName],
                true,
            );
        }

        return $permissionName !== 'admin.access';
    }
}

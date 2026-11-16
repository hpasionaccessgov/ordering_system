<?php

declare(strict_types=1);

namespace app\services;

final class RbacPermissionPolicy
{
    private const FORBIDDEN_PERMISSIONS = [
        'staff' => [
            'admin.access',
            'customer.access',
            'user.view',
            'user.create',
            'user.update',
            'user.delete',
            'user.assignRole',
        ],
        'customer' => [
            'admin.access',
            'staff.access',
            'user.view',
            'user.create',
            'user.update',
            'user.delete',
            'user.assignRole',
            'product.create',
            'product.update',
            'product.archive',
            'product.delete',
            'order.process',
            'order.receipt.print',
        ],
    ];

    public static function isAllowed(string $roleName, string $permissionName): bool
    {
        if ($roleName === 'admin') {
            return true;
        }

        if ($permissionName === 'admin.access') {
            return false;
        }

        if (isset(self::FORBIDDEN_PERMISSIONS[$roleName])) {
            return !in_array(
                $permissionName,
                self::FORBIDDEN_PERMISSIONS[$roleName],
                true,
            );
        }

        return true;
    }
}

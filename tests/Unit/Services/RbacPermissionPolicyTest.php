<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\services\RbacPermissionPolicy;
use Codeception\Test\Unit;

final class RbacPermissionPolicyTest extends Unit
{
    public function testRemovingIndexOrViewRemovesDependentPermissions(): void
    {
        self::assertSame(
            ['order.index'],
            RbacPermissionPolicy::normalizeRolePermissions([
                'order.index',
                'order.update',
                'order.receipt.print',
                'order.process',
            ]),
        );
        self::assertSame(
            ['product.index', 'product.view', 'product.update'],
            RbacPermissionPolicy::normalizeRolePermissions([
                'product.index',
                'product.view',
                'product.update',
            ]),
        );
        self::assertSame(
            ['user.index'],
            RbacPermissionPolicy::normalizeRolePermissions([
                'user.index',
                'user.update',
            ]),
        );
    }

    public function testCheckingIndexExpandsTheWholeModuleInTheRoleEditor(): void
    {
        self::assertSame(
            [
                'user.index',
                'user.view',
                'user.create',
                'user.delete',
                'user.update',
            ],
            RbacPermissionPolicy::expandIndexPermission('user.index', ['user.index']),
        );
    }

    public function testDisablingStaffAccessBlocksOnlyTheStaffRole(): void
    {
        self::assertFalse(RbacPermissionPolicy::isStaffRoleAccessAllowed(['staff'], false));
        self::assertTrue(RbacPermissionPolicy::isStaffRoleAccessAllowed(['staff'], true));
        self::assertTrue(RbacPermissionPolicy::isStaffRoleAccessAllowed(['cashier'], false));
        self::assertTrue(RbacPermissionPolicy::isStaffRoleAccessAllowed(['admin', 'staff'], false));
    }
}

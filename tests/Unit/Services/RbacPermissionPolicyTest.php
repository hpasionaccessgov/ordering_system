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

    public function testAccessPermissionsRemainIndependent(): void
    {
        self::assertSame(
            ['customer.access'],
            RbacPermissionPolicy::normalizeRolePermissions(['customer.access']),
        );
        self::assertSame(
            ['staff.access'],
            RbacPermissionPolicy::normalizeRolePermissions(['staff.access']),
        );
        self::assertSame(
            ['admin.access', 'user.assignRole'],
            RbacPermissionPolicy::normalizeRolePermissions(['admin.access', 'user.assignRole']),
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

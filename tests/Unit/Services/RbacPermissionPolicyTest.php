<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\services\RbacPermissionPolicy;
use Codeception\Test\Unit;

final class RbacPermissionPolicyTest extends Unit
{
    public function testRemovingViewAlsoRemovesPermissionsThatRenderRecordDetails(): void
    {
        self::assertSame(
            [],
            RbacPermissionPolicy::normalizeRolePermissions([
                'order.index',
                'order.update',
                'order.receipt.print',
                'order.process',
            ]),
        );
        self::assertSame(
            ['product.view', 'product.update'],
            RbacPermissionPolicy::normalizeRolePermissions([
                'product.view',
                'product.update',
            ]),
        );
        self::assertSame(
            [],
            RbacPermissionPolicy::normalizeRolePermissions([
                'user.index',
                'user.update',
            ]),
        );
    }

    public function testCheckingViewExpandsTheWholeModuleInTheRoleEditor(): void
    {
        self::assertSame(
            [
                'user.view',
                'user.index',
                'user.create',
                'user.delete',
                'user.update',
            ],
            RbacPermissionPolicy::expandViewPermission('user.view', ['user.view']),
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

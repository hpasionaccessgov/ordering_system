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
            ['order.index', 'order.process'],
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
            ['user.index'],
            RbacPermissionPolicy::normalizeRolePermissions([
                'user.index',
                'user.update',
            ]),
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

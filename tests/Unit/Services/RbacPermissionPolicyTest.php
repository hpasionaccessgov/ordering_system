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
            [],
            RbacPermissionPolicy::normalizeRolePermissions([
                'order.index',
                'order.update',
                'order.receipt.print',
                'order.process',
            ], 'cashier'),
        );
        $customerRolePermissions = RbacPermissionPolicy::normalizeRolePermissions([
            'customer.access',
            'product.index',
            'product.view',
            'product.update',
        ], 'customer');
        self::assertNotContains('product.update', $customerRolePermissions);
        self::assertContains('order.create', $customerRolePermissions);
        self::assertNotContains('product.delete', $customerRolePermissions);
        self::assertSame(
            ['user.index'],
            RbacPermissionPolicy::normalizeRolePermissions([
                'user.index',
                'user.update',
            ], 'cashier'),
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

    public function testAccessPermissionsControlTheirFeatureBundles(): void
    {
        self::assertContains(
            'product.view',
            RbacPermissionPolicy::expandAccessPermission('customer.access', ['customer.access']),
        );
        self::assertNotContains(
            'product.delete',
            RbacPermissionPolicy::expandAccessPermission('customer.access', ['customer.access']),
        );
        $customerPermissions = RbacPermissionPolicy::normalizeRolePermissions(['customer.access'], 'customer');
        self::assertContains('customer.access', $customerPermissions);
        self::assertContains('order.create', $customerPermissions);
        self::assertNotContains('product.delete', $customerPermissions);
        $staffPermissions = RbacPermissionPolicy::normalizeRolePermissions(['staff.access'], 'staff');
        self::assertContains('product.update', $staffPermissions);
        self::assertContains('order.process', $staffPermissions);
        self::assertSame(
            [],
            RbacPermissionPolicy::normalizeRolePermissions(['product.view'], 'cashier'),
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

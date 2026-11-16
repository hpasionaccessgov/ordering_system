<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\services\RbacPermissionPolicy;
use Codeception\Test\Unit;

final class RbacPermissionPolicyTest extends Unit
{
    public function testStaffCanOnlyReceiveOperationalPermissions(): void
    {
        self::assertTrue(RbacPermissionPolicy::isAllowed('staff', 'product.update'));
        self::assertTrue(RbacPermissionPolicy::isAllowed('staff', 'order.process'));
        self::assertTrue(RbacPermissionPolicy::isAllowed('staff', 'order.receipt.print'));
        self::assertTrue(RbacPermissionPolicy::isAllowed('staff', 'order.create'));
        self::assertFalse(RbacPermissionPolicy::isAllowed('staff', 'customer.access'));
        self::assertFalse(RbacPermissionPolicy::isAllowed('staff', 'admin.access'));
        self::assertFalse(RbacPermissionPolicy::isAllowed('staff', 'user.assignRole'));
        self::assertFalse(RbacPermissionPolicy::isAllowed('staff', 'user.delete'));
    }

    public function testCustomerCannotReceiveStaffOrAdministratorPermissions(): void
    {
        self::assertTrue(RbacPermissionPolicy::isAllowed('customer', 'order.create'));
        self::assertTrue(RbacPermissionPolicy::isAllowed('customer', 'order.update'));
        self::assertFalse(RbacPermissionPolicy::isAllowed('customer', 'staff.access'));
        self::assertFalse(RbacPermissionPolicy::isAllowed('customer', 'order.process'));
        self::assertFalse(RbacPermissionPolicy::isAllowed('customer', 'order.receipt.print'));
        self::assertFalse(RbacPermissionPolicy::isAllowed('customer', 'product.archive'));
        self::assertFalse(RbacPermissionPolicy::isAllowed('customer', 'user.view'));
        self::assertFalse(RbacPermissionPolicy::isAllowed('customer', 'admin.access'));
    }

    public function testCustomRolesCanReceivePermissionsExceptAdministratorAccess(): void
    {
        self::assertTrue(RbacPermissionPolicy::isAllowed('cashier', 'order.create'));
        self::assertFalse(RbacPermissionPolicy::isAllowed('cashier', 'admin.access'));
    }

    public function testAdminRetainsFullPermissionAccess(): void
    {
        self::assertTrue(RbacPermissionPolicy::isAllowed('admin', 'admin.access'));
        self::assertTrue(RbacPermissionPolicy::isAllowed('admin', 'user.assignRole'));
    }
}

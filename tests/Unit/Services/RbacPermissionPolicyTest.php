<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\services\RbacPermissionPolicy;
use Codeception\Test\Unit;

final class RbacPermissionPolicyTest extends Unit
{
    public function testDisablingStaffAccessBlocksOnlyTheStaffRole(): void
    {
        self::assertFalse(RbacPermissionPolicy::isStaffRoleAccessAllowed(['staff'], false));
        self::assertTrue(RbacPermissionPolicy::isStaffRoleAccessAllowed(['staff'], true));
        self::assertTrue(RbacPermissionPolicy::isStaffRoleAccessAllowed(['cashier'], false));
        self::assertTrue(RbacPermissionPolicy::isStaffRoleAccessAllowed(['admin', 'staff'], false));
    }
}

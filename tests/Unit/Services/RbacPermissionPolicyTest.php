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

    public function testCategoryDependenciesMatchOtherModules(): void
    {
        self::assertSame(
            ['category.index', 'category.view', 'category.create', 'category.update', 'category.delete'],
            RbacPermissionPolicy::expandIndexPermission('category.index', []),
        );
        self::assertSame(
            ['category.index'],
            RbacPermissionPolicy::normalizeRolePermissions([
                'category.index', 'category.update', 'category.delete',
            ]),
        );
        self::assertSame(
            ['category.index', 'category.view', 'category.update'],
            RbacPermissionPolicy::normalizeRolePermissions([
                'category.index', 'category.view', 'category.update',
            ]),
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

    public function testCartIndexControlsEveryCartAction(): void
    {
        $permissions = ['cart.index'];

        self::assertSame(
            ['cart.index', 'cart.add', 'cart.update', 'cart.remove', 'cart.checkout'],
            RbacPermissionPolicy::expandIndexPermission('cart.index', $permissions),
        );
        self::assertSame(
            ['cart.index', 'cart.add', 'cart.update', 'cart.remove', 'cart.checkout'],
            RbacPermissionPolicy::normalizeRolePermissions([
                'cart.index',
                'cart.add',
                'cart.update',
                'cart.remove',
                'cart.checkout',
            ]),
        );
        self::assertSame(
            [],
            RbacPermissionPolicy::normalizeRolePermissions([
                'cart.add',
                'cart.checkout',
            ]),
        );
    }

    public function testActivityAndNotificationIndexControlTheirActions(): void
    {
        foreach ([
            'activityLog' => ['view'],
            'notification' => ['read', 'delete'],
            'settings' => ['update'],
        ] as $module => $actions) {
            $index = $module . '.index';
            $dependents = array_map(static fn(string $action): string => $module . '.' . $action, $actions);
            self::assertSame(
                array_merge([$index], $dependents),
                RbacPermissionPolicy::expandIndexPermission($index, []),
            );
            self::assertSame([$index], RbacPermissionPolicy::normalizeRolePermissions([$index]));
            self::assertSame([], RbacPermissionPolicy::normalizeRolePermissions($dependents));
        }
    }

    public function testRoleEditorLabelsAndDescribesRecentFeaturePermissions(): void
    {
        $view = (string) file_get_contents(dirname(__DIR__, 3) . '/views/admin-user/roles.php');

        foreach ([
            "'loyalty' => 'Loyalty'",
            "'reward' => 'Rewards'",
            "'reports' => 'Reports'",
            "'order.board' => 'View the live order board.'",
            "'loyalty.report' => 'View the loyalty report.'",
            "'reports.export' => 'Download report exports.'",
            "'reward.index' => 'View the reward catalog.'",
        ] as $expected) {
            self::assertStringContainsString($expected, $view);
        }
    }

    public function testLoyaltyReportCannotBeReachedThroughTheSharedReportsPermission(): void
    {
        $controller = (string) file_get_contents(dirname(__DIR__, 3) . '/controllers/ReportController.php');

        self::assertStringContainsString('requireLoyaltyReportAccess()', $controller);
        self::assertStringContainsString("can('loyalty.report')", $controller);
        self::assertStringContainsString('ForbiddenHttpException', $controller);
    }
}

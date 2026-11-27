<?php

declare(strict_types=1);

namespace app\tests\Unit\Frontend;

use Codeception\Test\Unit;

final class RoleNavigationTest extends Unit
{
    public function testHeaderShowsLoyaltyToCustomersAndAdminsButNotStaff(): void
    {
        $header = (string) file_get_contents(dirname(__DIR__, 3) . '/views/layouts/_header.php');

        self::assertStringContainsString("Yii::\$app->user->can('customer.access')", $header);
        self::assertStringContainsString("Yii::\$app->user->can('admin.access')", $header);
        self::assertStringContainsString('$showHeaderRewards', $header);

        $themeToggle = strpos($header, "_theme_toggle");
        $cartButton = strpos($header, "_cart_button");
        $loyaltyGuard = strpos($header, '<?php if ($showHeaderRewards): ?>');
        self::assertLessThan($loyaltyGuard, $themeToggle);
        self::assertLessThan($loyaltyGuard, $cartButton);
    }

    public function testSidebarKeepsStaffOverviewAndAdminRbacLinks(): void
    {
        $sidebar = (string) file_get_contents(dirname(__DIR__, 3) . '/views/layouts/_sidebar.php');

        self::assertStringContainsString("(\$isCustomer || \$can('admin.access')) && \$can('loyalty.view')", $sidebar);
        self::assertStringContainsString("\$can('loyalty.transaction.index')", $sidebar);
        self::assertStringContainsString("\$can('reward.index')", $sidebar);
        self::assertStringContainsString("\$can('user.assignRole')", $sidebar);
        self::assertStringContainsString("\$can('activityLog.index')", $sidebar);
        self::assertStringContainsString("\$can('settings.index')", $sidebar);
    }

    public function testMobileSidebarToggleSitsBesideTheBrandNotTheThreeDotMenu(): void
    {
        $header = (string) file_get_contents(dirname(__DIR__, 3) . '/views/layouts/_header.php');
        $dots = strpos($header, 'bi-three-dots');

        self::assertLessThan($dots, strpos($header, 'cafebara-sidebar-toggle'));
        self::assertLessThan($dots, strpos($header, 'navbar-brand cafebara-admin-brand'));
    }

    public function testMobileHeaderActionsAlignRightAndMenusFit(): void
    {
        $css = (string) file_get_contents(dirname(__DIR__, 3) . '/web/css/cafebara-theme.css');

        self::assertStringContainsString("    .cafebara-admin-actions {\n        justify-content: flex-end;", $css);
        self::assertStringContainsString('.cafebara-admin-actions .nav-item>.dropdown,', $css);
    }
}

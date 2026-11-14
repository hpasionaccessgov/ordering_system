<?php

declare(strict_types=1);

use yii\helpers\Html;

$currentRoute = Yii::$app->controller->route;
$dashboardRoute = match (true) {
    Yii::$app->user->can('admin.access') => ['/admin/dashboard'],
    Yii::$app->user->can('staff.access') => ['/staff/dashboard'],
    default => ['/customer/dashboard'],
};
$dashboardRouteName = ltrim($dashboardRoute[0], '/');
?>
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <?= Html::a(
            '<span class="brand-text fw-light">' . Html::encode(Yii::$app->name) . '</span>',
            $dashboardRoute,
            ['class' => 'brand-link'],
        ) ?>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2" aria-label="Main navigation">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" data-accordion="false">
                <li class="nav-item">
                    <?= Html::a(
                        '<i class="nav-icon bi bi-speedometer2"></i><p>Dashboard</p>',
                        $dashboardRoute,
                        ['class' => 'nav-link' . ($currentRoute === $dashboardRouteName ? ' active' : '')],
                    ) ?>
                </li>
                <?php if (Yii::$app->user->can('user.assignRole')): ?>
                    <li class="nav-item">
                        <?= Html::a(
                            '<i class="nav-icon bi bi-people"></i><p>User roles</p>',
                            ['/admin-user/index'],
                            ['class' => 'nav-link' . (strpos($currentRoute, 'admin-user/') === 0 ? ' active' : '')],
                        ) ?>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <?= Html::a(
                        '<i class="nav-icon bi bi-person"></i><p>Profile</p>',
                        ['/site/profile'],
                        ['class' => 'nav-link' . ($currentRoute === 'site/profile' ? ' active' : '')],
                    ) ?>
                </li>
                <li class="nav-item">
                    <?= Html::a(
                        '<i class="nav-icon bi bi-key"></i><p>Change password</p>',
                        ['/site/change-password'],
                        ['class' => 'nav-link' . ($currentRoute === 'site/change-password' ? ' active' : '')],
                    ) ?>
                </li>
            </ul>
        </nav>
    </div>
</aside>
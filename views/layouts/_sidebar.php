<?php

declare(strict_types=1);

use yii\helpers\Html;

$currentRoute = Yii::$app->controller->route;
$hasDashboardAccess = Yii::$app->user->can('admin.access')
    || Yii::$app->user->can('staff.access')
    || Yii::$app->user->can('customer.access');
$dashboardRoute = match (true) {
    Yii::$app->user->can('admin.access') => ['/admin/dashboard'],
    Yii::$app->user->can('staff.access') => ['/staff/dashboard'],
    Yii::$app->user->can('customer.access') => ['/customer/dashboard'],
    default => ['/site/index'],
};
$dashboardRouteName = ltrim($dashboardRoute[0], '/');
?>
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <?= Html::a(
            Html::img(Yii::getAlias('@web/Cafebara_logo.png'), [
                'alt' => 'Cafébara',
                'class' => 'cafebara-sidebar-logo',
            ]) . '<span class="brand-text">' . Html::encode(Yii::$app->name) . '</span>',
            $dashboardRoute,
            ['class' => 'brand-link d-flex align-items-center gap-2'],
        ) ?>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2" aria-label="Main navigation">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" data-accordion="false">
                <li class="nav-item">
                    <?= Html::a(
                        '<i class="nav-icon bi bi-house"></i><p>'
                            . ($hasDashboardAccess ? 'Dashboard' : 'Home')
                            . '</p>',
                        $dashboardRoute,
                        ['class' => 'nav-link' . ($currentRoute === $dashboardRouteName ? ' active' : '')],
                    ) ?>
                </li>


                <?php if (
                    Yii::$app->user->can('product.index')
                    || Yii::$app->user->can('product.create')
                ): ?>
                    <li class="nav-item">
                        <?= Html::a(
                            '<i class="nav-icon bi bi-cup-hot"></i><p>Product</p>',
                            Yii::$app->user->can('product.index')
                                ? ['/product/index']
                                : ['/product/create'],
                            ['class' => 'nav-link' . (str_starts_with($currentRoute, 'product/') ? ' active' : '')],
                        ) ?>
                    </li>
                <?php endif; ?>

                <?php if (
                    Yii::$app->user->can('category.index')
                    || Yii::$app->user->can('category.create')
                ): ?>
                    <li class="nav-item">
                        <?= Html::a(
                            '<i class="nav-icon bi bi-tags"></i><p>Categories</p>',
                            Yii::$app->user->can('category.index')
                                ? ['/category/index']
                                : ['/category/create'],
                            ['class' => 'nav-link' . (str_starts_with($currentRoute, 'category/') ? ' active' : '')],
                        ) ?>
                    </li>
                <?php endif; ?>

                <?php if (
                    Yii::$app->user->can('order.index')
                    || Yii::$app->user->can('order.create')
                ): ?>
                    <li class="nav-item">
                        <?= Html::a(
                            '<i class="nav-icon bi bi-receipt"></i><p>Orders</p>',
                            Yii::$app->user->can('order.index')
                                ? ['/order/index']
                                : ['/order/create'],
                            ['class' => 'nav-link' . (str_starts_with($currentRoute, 'order/') ? ' active' : '')],
                        ) ?>
                    </li>
                <?php endif; ?>

                <?php if (
                    Yii::$app->user->can('user.index')
                    || Yii::$app->user->can('user.create')
                ): ?>
                    <li class="nav-item">
                        <?= Html::a(
                            '<i class="nav-icon bi bi-person-lines-fill"></i><p>User list</p>',
                            Yii::$app->user->can('user.index')
                                ? ['/admin-user/list']
                                : ['/admin-user/create'],
                            ['class' => 'nav-link' . ($currentRoute === 'admin-user/list' ? ' active' : '')],
                        ) ?>
                    </li>
                <?php endif; ?>
                <?php if (Yii::$app->user->can('user.assignRole')): ?>
                    <li class="nav-item">
                        <?= Html::a(
                            '<i class="nav-icon bi bi-shield-lock"></i><p>Roles</p>',
                            ['/admin-role/index'],
                            ['class' => 'nav-link' . ($currentRoute === 'admin-role/index' ? ' active' : '')],
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
                <!-- <li class="nav-item">
                    <?= Html::a(
                        '<i class="nav-icon bi bi-key"></i><p>Change password</p>',
                        ['/site/change-password'],
                        ['class' => 'nav-link' . ($currentRoute === 'site/change-password' ? ' active' : '')],
                    ) ?>
                </li> -->
            </ul>
        </nav>
    </div>
</aside>
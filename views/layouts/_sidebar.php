<?php

declare(strict_types=1);

use yii\helpers\Html;

$currentRoute = Yii::$app->controller->route;
$can = static fn(string $permission): bool => Yii::$app->user->can($permission);

$hasDashboardAccess = $can('admin.access') || $can('staff.access') || $can('customer.access');
$dashboardRoute = match (true) {
    $can('admin.access') => ['/admin/dashboard'],
    $can('staff.access') => ['/staff/dashboard'],
    $can('customer.access') => ['/customer/dashboard'],
    default => ['/site/index'],
};
$dashboardRouteName = ltrim($dashboardRoute[0], '/');

// Categorizable menus. A group renders only when at least one of its items is visible.
$navGroups = [
    [
        'label' => 'Catalog',
        'icon' => 'bi-collection',
        'items' => [
            [
                'label' => 'Products',
                'icon' => 'bi-cup-hot',
                'visible' => $can('product.index') || $can('product.create'),
                'route' => $can('product.index') ? ['/catalog/products'] : ['/catalog/products/create'],
                'active' => str_starts_with($currentRoute, 'product/'),
            ],
            [
                'label' => 'Categories',
                'icon' => 'bi-tags',
                'visible' => $can('category.index') || $can('category.create'),
                'route' => $can('category.index') ? ['/catalog/categories'] : ['/catalog/categories/create'],
                'active' => str_starts_with($currentRoute, 'category/'),
            ],
        ],
    ],
    [
        'label' => 'Sales',
        'icon' => 'bi-cash-stack',
        'items' => [
            [
                'label' => 'Orders',
                'icon' => 'bi-receipt',
                'visible' => $can('order.index'),
                'route' => ['/sales/orders'],
                'active' => str_starts_with($currentRoute, 'order/'),
            ],
            [
                'label' => 'Kitchen board',
                'icon' => 'bi-kanban',
                'visible' => $can('order.board') || $can('customer.access'),
                'route' => ['/sales/kitchen-board'],
                'active' => str_starts_with($currentRoute, 'kitchen/'),
            ],
            [
                'label' => 'Cart',
                'icon' => 'bi-cart',
                'visible' => $can('cart.index'),
                'route' => ['/sales/cart'],
                'active' => str_starts_with($currentRoute, 'cart/'),
            ],
            [
                'label' => 'Favorites',
                'icon' => 'bi-heart',
                'visible' => $can('favorite.index'),
                'route' => ['/sales/favorites'],
                'active' => str_starts_with($currentRoute, 'favorite/'),
            ],
            [
                'label' => 'Inventory',
                'icon' => 'bi-box-seam-fill',
                'visible' => $can('inventory.index') || $can('inventory.create'),
                'route' => $can('inventory.index') ? ['/sales/inventory'] : ['/sales/inventory/create'],
                'active' => str_starts_with($currentRoute, 'inventory/'),
            ]
        ],
    ],
    [
        'label' => 'Reports',
        'icon' => 'bi-bar-chart-line',
        'items' => [
            [
                'label' => 'Sales',
                'icon' => 'bi-cash-coin',
                'visible' => $can('reports.view'),
                'route' => ['/reports/sales'],
                'active' => str_starts_with($currentRoute, 'report/sales'),
            ],
            [
                'label' => 'Product performance',
                'icon' => 'bi-graph-up',
                'visible' => $can('reports.view'),
                'route' => ['/reports/products'],
                'active' => str_starts_with($currentRoute, 'report/product'),
            ],
            [
                'label' => 'Orders',
                'icon' => 'bi-receipt',
                'visible' => $can('reports.view'),
                'route' => ['/reports/orders'],
                'active' => str_starts_with($currentRoute, 'report/orders'),
            ],
            [
                'label' => 'Inventory',
                'icon' => 'bi-box-seam',
                'visible' => $can('reports.view'),
                'route' => ['/reports/inventory'],
                'active' => str_starts_with($currentRoute, 'report/inventory'),
            ],
        ],
    ],
    [
        'label' => 'Administration',
        'icon' => 'bi-gear',
        'items' => [
            [
                'label' => 'Users',
                'icon' => 'bi-person-lines-fill',
                'visible' => $can('user.index') || $can('user.create'),
                'route' => $can('user.index') ? ['/admin/users'] : ['/admin/users/create'],
                'active' => $currentRoute === 'admin-user/list',
            ],
            [
                'label' => 'Roles',
                'icon' => 'bi-shield-lock',
                'visible' => $can('user.assignRole'),
                'route' => ['/admin/roles'],
                'active' => $currentRoute === 'admin-role/index',
            ],
            [
                'label' => 'Activity logs',
                'icon' => 'bi-clock-history',
                'visible' => $can('activityLog.index'),
                'route' => ['/admin/activity-logs'],
                'active' => str_starts_with($currentRoute, 'activity-log/'),
            ],
            [
                'label' => 'Settings',
                'icon' => 'bi-sliders',
                'visible' => $can('settings.index'),
                'route' => ['/admin/settings'],
                'active' => str_starts_with($currentRoute, 'settings/'),
            ],
        ]

    ],
];
?>
<aside class="bg-body-secondary shadow app-sidebar" data-bs-theme="dark">
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
            <ul class="flex-column nav sidebar-menu" data-lte-toggle="treeview" data-accordion="false">
                <li class="nav-item">
                    <?= Html::a(
                        '<i class="nav-icon bi bi-house"></i><p>'
                            . ($hasDashboardAccess ? 'Dashboard' : 'Home')
                            . '</p>',
                        $dashboardRoute,
                        ['class' => 'nav-link' . ($currentRoute === $dashboardRouteName ? ' active' : '')],
                    ) ?>
                </li>

                <?php foreach ($navGroups as $group) : ?>
                    <?php
                    $visibleItems = array_values(array_filter(
                        $group['items'],
                        static fn(array $item): bool => $item['visible'],
                    ));
                    if ($visibleItems === []) {
                        continue;
                    }
                    ?>
                    <?php if (count($visibleItems) === 1) : ?>
                        <?php $item = $visibleItems[0]; ?>
                        <li class="nav-item">
                            <?= Html::a(
                                '<i class="nav-icon bi ' . $item['icon'] . '"></i><p>'
                                    . Html::encode($item['label']) . '</p>',
                                $item['route'],
                                ['class' => 'nav-link' . ($item['active'] ? ' active' : '')],
                            ) ?>
                        </li>
                    <?php else : ?>
                        <?php $groupActive = array_filter($visibleItems, static fn(array $item): bool => $item['active']) !== []; ?>
                        <li class="nav-item menu-open<?= $groupActive ? ' has-active-child' : '' ?>">
                            <?= Html::a(
                                '<i class="nav-icon bi ' . $group['icon'] . '"></i><p>'
                                    . Html::encode($group['label'])
                                    . '<i class="nav-arrow bi bi-chevron-right"></i></p>',
                                '#',
                                ['class' => 'nav-link'],
                            ) ?>
                            <ul class="nav nav-treeview">
                                <?php foreach ($visibleItems as $item) : ?>
                                    <li class="nav-item">
                                        <?= Html::a(
                                            '<i class="nav-icon bi ' . $item['icon'] . '"></i><p>'
                                                . Html::encode($item['label']) . '</p>',
                                            $item['route'],
                                            ['class' => 'nav-link' . ($item['active'] ? ' active' : '')],
                                        ) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</aside>
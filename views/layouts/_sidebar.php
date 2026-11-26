<?php

declare(strict_types=1);

use app\services\Navigation;

use yii\helpers\Html;

$currentRoute = Yii::$app->controller->route;
$can = static fn(string $permission): bool => Yii::$app->user->can($permission);
$isCustomer = $can('customer.access') && !$can('staff.access') && !$can('admin.access');

$hasDashboardAccess = $can('admin.access') || $can('staff.access') || $can('customer.access');
$dashboardRoute = match (true) {
    $can('admin.access') => Navigation::url(Navigation::ADMIN_DASHBOARD),
    $can('staff.access') => Navigation::url(Navigation::STAFF_DASHBOARD),
    $can('customer.access') => Navigation::url(Navigation::CUSTOMER_DASHBOARD),
    default => Navigation::url(Navigation::HOME),
};
$dashboardRouteName = ltrim($dashboardRoute[0], '/');

// Categorizable menus. A group renders only when at least one of its items is visible.
$navGroups = [
    [
        'label' => $isCustomer ? 'Menu' : 'Catalog',
        'icon' => $isCustomer ? 'bi-cup-hot' : 'bi-collection',
        'items' => [
            [
                'label' => $isCustomer ? 'Browse menu' : 'Products',
                'icon' => 'bi-cup-hot',
                'visible' => $can('product.index') || $can('product.create'),
                'route' => $can('product.index') ? Navigation::url(Navigation::PRODUCT_INDEX) : Navigation::url(Navigation::PRODUCT_CREATE),
                'active' => str_starts_with($currentRoute, 'product/'),
            ],
            [
                'label' => $isCustomer ? 'Saved favorites' : 'Favorites',
                'icon' => 'bi-heart',
                'visible' => $can('favorite.index'),
                'route' => Navigation::url(Navigation::FAVORITE_INDEX),
                'active' => str_starts_with($currentRoute, 'favorite/'),
            ],
            [
                'label' => $isCustomer ? 'Browse categories' : 'Categories',
                'icon' => 'bi-tags',
                'visible' => $can('category.index') || $can('category.create'),
                'route' => $can('category.index') ? Navigation::url(Navigation::CATEGORY_INDEX) : Navigation::url(Navigation::CATEGORY_CREATE),
                'active' => str_starts_with($currentRoute, 'category/'),
            ],
        ],
    ],
    [
        'label' => $isCustomer ? 'Your orders' : 'Sales',
        'icon' => $isCustomer ? 'bi-receipt' : 'bi-cash-stack',
        'items' => [
            [
                'label' => $isCustomer ? 'Orders' : 'Orders',
                'icon' => 'bi-receipt',
                'visible' => $can('order.index'),
                'route' => Navigation::url(Navigation::ORDER_INDEX),
                'active' => str_starts_with($currentRoute, 'order/'),
            ],
            [
                'label' => $isCustomer ? 'Track your order' : 'Kitchen board',
                'icon' => 'bi-kanban',
                'visible' => $can('order.board') || $can('customer.access'),
                'route' => Navigation::url(Navigation::KITCHEN_BOARD),
                'active' => str_starts_with($currentRoute, 'kitchen/'),
            ],
            [
                'label' => $isCustomer ? 'My cart' : 'Cart',
                'icon' => 'bi-cart',
                'visible' => $can('cart.index'),
                'route' => Navigation::url(Navigation::CART_INDEX),
                'active' => str_starts_with($currentRoute, 'cart/'),
            ],

            [
                'label' => 'Inventory',
                'icon' => 'bi-box-seam-fill',
                'visible' => $can('inventory.index') || $can('inventory.create'),
                'route' => $can('inventory.index') ? Navigation::url(Navigation::INVENTORY_INDEX) : Navigation::url(Navigation::INVENTORY_CREATE),
                'active' => str_starts_with($currentRoute, 'inventory/'),
            ],
        ],
    ],
    [
        'label' => $isCustomer ? 'Your rewards' : 'Rewards',
        'icon' => 'bi-gift',
        'items' => [
            [
                'label' => 'My rewards',
                'icon' => 'bi-stars',
                'visible' => $can('loyalty.view'),
                'route' => Navigation::url(Navigation::REWARDS),
                'active' => str_starts_with($currentRoute, 'loyalty/'),
            ],
            [
                'label' => 'Reward catalog',
                'icon' => 'bi-gift',
                'visible' => $can('reward.index'),
                'route' => Navigation::url(Navigation::REWARD_INDEX),
                'active' => str_starts_with($currentRoute, 'reward/'),
            ],
            [
                'label' => 'Loyalty overview',
                'icon' => 'bi-clipboard-data',
                'visible' => $can('loyalty.transaction.index'),
                'route' => Navigation::url(Navigation::LOYALTY_OVERVIEW),
                'active' => str_starts_with($currentRoute, 'admin-loyalty/'),
            ],

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
                'route' => Navigation::url(Navigation::REPORT_SALES),
                'active' => str_starts_with($currentRoute, 'report/sales'),
            ],
            [
                'label' => 'Product performance',
                'icon' => 'bi-graph-up',
                'visible' => $can('reports.view'),
                'route' => Navigation::url(Navigation::REPORT_PRODUCT),
                'active' => str_starts_with($currentRoute, 'report/product'),
            ],
            [
                'label' => 'Orders',
                'icon' => 'bi-receipt',
                'visible' => $can('reports.view'),
                'route' => Navigation::url(Navigation::REPORT_ORDERS),
                'active' => str_starts_with($currentRoute, 'report/orders'),
            ],
            [
                'label' => 'Inventory',
                'icon' => 'bi-box-seam',
                'visible' => $can('reports.view'),
                'route' => Navigation::url(Navigation::REPORT_INVENTORY),
                'active' => str_starts_with($currentRoute, 'report/inventory'),
            ],
            [
                'label' => 'Loyalty',
                'icon' => 'bi-gift',
                'visible' => $can('reports.view') && $can('loyalty.report'),
                'route' => Navigation::url(Navigation::REPORT_LOYALTY),
                'active' => str_starts_with($currentRoute, 'report/loyalty'),
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
                'route' => $can('user.index') ? Navigation::url(Navigation::ADMIN_USER_INDEX) : Navigation::url(Navigation::ADMIN_USER_CREATE),
                'active' => $currentRoute === 'admin-user/list',
            ],
            [
                'label' => 'Roles',
                'icon' => 'bi-shield-lock',
                'visible' => $can('user.assignRole'),
                'route' => Navigation::url(Navigation::ADMIN_ROLE_INDEX),
                'active' => $currentRoute === 'admin-role/index',
            ],
            [
                'label' => 'Activity logs',
                'icon' => 'bi-clock-history',
                'visible' => $can('activityLog.index'),
                'route' => Navigation::url(Navigation::ACTIVITY_LOG_INDEX),
                'active' => str_starts_with($currentRoute, 'activity-log/'),
            ],
            [
                'label' => 'Settings',
                'icon' => 'bi-sliders',
                'visible' => $can('settings.index'),
                'route' => Navigation::url(Navigation::SETTINGS),
                'active' => str_starts_with($currentRoute, 'settings/'),
            ],
        ],
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
            Navigation::url(Navigation::HOME),
            ['class' => 'brand-link d-flex align-items-center gap-2', 'aria-label' => 'Cafébara home'],
        ) ?>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2" aria-label="Main navigation">
            <ul class="flex-column nav sidebar-menu" data-lte-toggle="treeview" data-accordion="false">
                <li class="nav-item">
                    <?= Html::a(
                        '<i class="nav-icon bi bi-house"></i><p>'
                            . ($hasDashboardAccess ? ($isCustomer ? 'My dashboard' : 'Dashboard') : 'Home')
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
<?php

declare(strict_types=1);

namespace app\services;

use InvalidArgumentException;

/**
 * Single source of truth for browser-facing routes and their pretty URL rules.
 */
final class Navigation
{
    public const HOME = 'home';
    public const DASHBOARD = 'dashboard';
    public const LOGIN = 'login';
    public const LOGOUT = 'logout';
    public const REGISTER = 'register';
    public const PROFILE = 'profile';
    public const CHANGE_PASSWORD = 'change-password';
    public const DEACTIVATE = 'deactivate';
    public const CONTACT = 'contact';
    public const ABOUT = 'about';
    public const MENU = 'menu';

    public const PRODUCT_INDEX = 'product.index';
    public const PRODUCT_CREATE = 'product.create';
    public const PRODUCT_VIEW = 'product.view';
    public const PRODUCT_UPDATE = 'product.update';
    public const PRODUCT_DELETE = 'product.delete';
    public const PRODUCT_RESTORE = 'product.restore';
    public const PRODUCT_ARCHIVE = 'product.archive';
    public const CATEGORY_INDEX = 'category.index';
    public const CATEGORY_CREATE = 'category.create';
    public const CATEGORY_VIEW = 'category.view';
    public const CATEGORY_UPDATE = 'category.update';
    public const CATEGORY_DELETE = 'category.delete';

    public const ORDER_INDEX = 'order.index';
    public const ORDER_CREATE = 'order.create';
    public const ORDER_VIEW = 'order.view';
    public const ORDER_UPDATE = 'order.update';
    public const ORDER_RECEIPT = 'order.receipt';
    public const ORDER_PROCESS = 'order.process';
    public const ORDER_CANCEL = 'order.cancel';
    public const ORDER_AGAIN = 'order.order-again';
    public const ORDER_CONFIRMATION = 'order.confirmation';
    public const KITCHEN_BOARD = 'kitchen.board';
    public const CART_INDEX = 'cart.index';
    public const CART_SUMMARY = 'cart.summary';
    public const CART_ADD = 'cart.add';
    public const CART_ADD_BATCH = 'cart.add-batch';
    public const CART_UPDATE_QUANTITY = 'cart.update-quantity';
    public const CART_REMOVE = 'cart.remove';
    public const CART_CHECKOUT = 'cart.checkout';
    public const FAVORITE_INDEX = 'favorite.index';
    public const FAVORITE_TOGGLE = 'favorite.toggle';
    public const FAVORITE_ADD = 'favorite.add';
    public const FAVORITE_REMOVE = 'favorite.remove';
    public const INVENTORY_INDEX = 'inventory.index';
    public const INVENTORY_CREATE = 'inventory.create';
    public const INVENTORY_VIEW = 'inventory.view';
    public const INVENTORY_UPDATE = 'inventory.update';
    public const INVENTORY_DELETE = 'inventory.delete';
    public const INVENTORY_ADJUST = 'inventory.adjust';

    public const REWARDS = 'loyalty.index';
    public const REWARD_REDEEM = 'loyalty.redeem';
    public const REWARD_INDEX = 'reward.index';
    public const REWARD_CREATE = 'reward.create';
    public const REWARD_VIEW = 'reward.view';
    public const REWARD_UPDATE = 'reward.update';
    public const REWARD_DELETE = 'reward.delete';
    public const LOYALTY_OVERVIEW = 'admin-loyalty.index';
    public const LOYALTY_TRANSACTIONS = 'admin-loyalty.transactions';
    public const LOYALTY_REDEMPTIONS = 'admin-loyalty.redemptions';
    public const LOYALTY_ADJUST = 'admin-loyalty.adjust';

    public const REPORT_SALES = 'report.sales';
    public const REPORT_PRODUCT = 'report.product';
    public const REPORT_ORDERS = 'report.orders';
    public const REPORT_INVENTORY = 'report.inventory';
    public const REPORT_LOYALTY = 'report.loyalty';
    public const REPORT_EXPORT = 'report.export';
    public const REPORT_EXPORT_INDEX = 'report.export.index';

    public const ADMIN_DASHBOARD = 'admin.dashboard';
    public const ADMIN_ACTIVITY = 'admin.activity';
    public const STAFF_DASHBOARD = 'staff.dashboard';
    public const CUSTOMER_DASHBOARD = 'customer.dashboard';
    public const ADMIN_USER_INDEX = 'admin-user.list';
    public const ADMIN_USER_CREATE = 'admin-user.create';
    public const ADMIN_USER_ASSIGN_ROLE = 'admin-user.assign-role';
    public const ADMIN_USER_VIEW = 'admin-user.view';
    public const ADMIN_USER_UPDATE = 'admin-user.update';
    public const ADMIN_USER_DELETE = 'admin-user.delete';
    public const ADMIN_ROLE_INDEX = 'admin-role.index';
    public const ADMIN_ROLE_CREATE = 'admin-role.create';
    public const ADMIN_ROLE_UPDATE_MATRIX = 'admin-role.update-matrix';
    public const ADMIN_ROLE_UPDATE_PERMISSIONS = 'admin-role.update-permissions';
    public const ADMIN_ROLE_UPDATE = 'admin-role.update';
    public const ADMIN_ROLE_DELETE = 'admin-role.delete';
    public const ACTIVITY_LOG_INDEX = 'activity-log.index';
    public const ACTIVITY_LOG_VIEW = 'activity-log.view';
    public const SETTINGS = 'settings.index';

    public const NOTIFICATIONS = 'notification.index';
    public const NOTIFICATION_UNREAD_COUNT = 'notification.unread-count';
    public const NOTIFICATION_READ_ALL = 'notification.read-all';
    public const NOTIFICATION_READ = 'notification.read';
    public const NOTIFICATION_DELETE = 'notification.delete';
    public const REVIEW_CREATE = 'product-review.create';
    public const SESSION_STATUS = 'session.status';
    public const SESSION_ACTIVITY = 'session.activity';

    /**
     * @var array<string, array{route: string, pattern: string|null}>
     */
    private const MAP = [
        self::HOME => ['route' => 'site/index', 'pattern' => ''],
        self::DASHBOARD => ['route' => 'site/dashboard', 'pattern' => 'dashboard'],
        self::LOGIN => ['route' => 'site/login', 'pattern' => 'login'],
        self::LOGOUT => ['route' => 'site/logout', 'pattern' => 'logout'],
        self::REGISTER => ['route' => 'site/register', 'pattern' => 'register'],
        self::PROFILE => ['route' => 'site/profile', 'pattern' => 'profile'],
        self::CHANGE_PASSWORD => ['route' => 'site/change-password', 'pattern' => 'profile/change-password'],
        self::DEACTIVATE => ['route' => 'site/deactivate', 'pattern' => 'profile/deactivate'],
        self::CONTACT => ['route' => 'site/contact', 'pattern' => 'contact'],
        self::ABOUT => ['route' => 'site/about', 'pattern' => 'about'],
        self::MENU => ['route' => 'site/menu', 'pattern' => 'menu'],

        self::PRODUCT_INDEX => ['route' => 'product/index', 'pattern' => 'catalog/products'],
        self::PRODUCT_CREATE => ['route' => 'product/create', 'pattern' => 'catalog/products/create'],
        self::PRODUCT_VIEW => ['route' => 'product/view', 'pattern' => 'catalog/products/<id:\d+>'],
        self::PRODUCT_UPDATE => ['route' => 'product/update', 'pattern' => 'catalog/products/<id:\d+>/update'],
        self::PRODUCT_DELETE => ['route' => 'product/delete', 'pattern' => 'catalog/products/<id:\d+>/delete'],
        self::PRODUCT_RESTORE => ['route' => 'product/restore', 'pattern' => 'catalog/products/<id:\d+>/restore'],
        self::PRODUCT_ARCHIVE => ['route' => 'product/archive', 'pattern' => null],
        self::CATEGORY_INDEX => ['route' => 'category/index', 'pattern' => 'catalog/categories'],
        self::CATEGORY_CREATE => ['route' => 'category/create', 'pattern' => 'catalog/categories/create'],
        self::CATEGORY_VIEW => ['route' => 'category/view', 'pattern' => 'catalog/categories/<id:\d+>'],
        self::CATEGORY_UPDATE => ['route' => 'category/update', 'pattern' => 'catalog/categories/<id:\d+>/update'],
        self::CATEGORY_DELETE => ['route' => 'category/delete', 'pattern' => 'catalog/categories/<id:\d+>/delete'],

        self::ORDER_INDEX => ['route' => 'order/index', 'pattern' => 'sales/orders'],
        self::ORDER_CREATE => ['route' => 'order/create', 'pattern' => 'sales/orders/create'],
        self::ORDER_VIEW => ['route' => 'order/view', 'pattern' => 'sales/orders/<id:\d+>'],
        self::ORDER_UPDATE => ['route' => 'order/update', 'pattern' => null],
        self::ORDER_RECEIPT => ['route' => 'order/receipt', 'pattern' => 'sales/orders/<id:\d+>/receipt'],
        self::ORDER_PROCESS => ['route' => 'order/process', 'pattern' => 'sales/orders/<id:\d+>/process'],
        self::ORDER_CANCEL => ['route' => 'order/cancel', 'pattern' => 'sales/orders/<id:\d+>/cancel'],
        self::ORDER_AGAIN => ['route' => 'order/order-again', 'pattern' => 'sales/orders/<id:\d+>/order-again'],
        self::ORDER_CONFIRMATION => ['route' => 'order/confirmation', 'pattern' => null],
        self::KITCHEN_BOARD => ['route' => 'kitchen/index', 'pattern' => 'sales/kitchen-board'],
        self::CART_INDEX => ['route' => 'cart/index', 'pattern' => 'sales/cart'],
        self::CART_SUMMARY => ['route' => 'cart/summary', 'pattern' => null],
        self::CART_ADD => ['route' => 'cart/add', 'pattern' => 'sales/cart/add'],
        self::CART_ADD_BATCH => ['route' => 'cart/add-batch', 'pattern' => 'cart/add-batch'],
        self::CART_UPDATE_QUANTITY => ['route' => 'cart/update-quantity', 'pattern' => 'sales/cart/update-quantity'],
        self::CART_REMOVE => ['route' => 'cart/remove', 'pattern' => 'sales/cart/remove'],
        self::CART_CHECKOUT => ['route' => 'cart/checkout', 'pattern' => 'sales/cart/checkout'],
        self::FAVORITE_INDEX => ['route' => 'favorite/index', 'pattern' => 'sales/favorites'],
        self::FAVORITE_TOGGLE => ['route' => 'favorite/toggle', 'pattern' => 'sales/favorites/toggle'],
        self::FAVORITE_ADD => ['route' => 'favorite/add', 'pattern' => null],
        self::FAVORITE_REMOVE => ['route' => 'favorite/remove', 'pattern' => null],
        self::INVENTORY_INDEX => ['route' => 'inventory/index', 'pattern' => 'sales/inventory'],
        self::INVENTORY_CREATE => ['route' => 'inventory/create', 'pattern' => 'sales/inventory/create'],
        self::INVENTORY_VIEW => ['route' => 'inventory/view', 'pattern' => 'sales/inventory/<id:\d+>'],
        self::INVENTORY_UPDATE => ['route' => 'inventory/update', 'pattern' => 'sales/inventory/<id:\d+>/update'],
        self::INVENTORY_DELETE => ['route' => 'inventory/delete', 'pattern' => 'sales/inventory/<id:\d+>/delete'],
        self::INVENTORY_ADJUST => ['route' => 'inventory/adjust', 'pattern' => 'sales/inventory/<id:\d+>/adjust'],

        self::REWARDS => ['route' => 'loyalty/index', 'pattern' => 'rewards'],
        self::REWARD_REDEEM => ['route' => 'loyalty/redeem', 'pattern' => 'rewards/<id:\d+>/redeem'],
        self::REWARD_INDEX => ['route' => 'reward/index', 'pattern' => 'admin/rewards'],
        self::REWARD_CREATE => ['route' => 'reward/create', 'pattern' => 'admin/rewards/create'],
        self::REWARD_VIEW => ['route' => 'reward/view', 'pattern' => 'admin/rewards/<id:\d+>'],
        self::REWARD_UPDATE => ['route' => 'reward/update', 'pattern' => 'admin/rewards/<id:\d+>/update'],
        self::REWARD_DELETE => ['route' => 'reward/delete', 'pattern' => 'admin/rewards/<id:\d+>/delete'],
        self::LOYALTY_OVERVIEW => ['route' => 'admin-loyalty/index', 'pattern' => 'admin/loyalty'],
        self::LOYALTY_TRANSACTIONS => ['route' => 'admin-loyalty/transactions', 'pattern' => 'admin/loyalty/transactions'],
        self::LOYALTY_REDEMPTIONS => ['route' => 'admin-loyalty/redemptions', 'pattern' => 'admin/loyalty/redemptions'],
        self::LOYALTY_ADJUST => ['route' => 'admin-loyalty/adjust', 'pattern' => 'admin/loyalty/adjust'],

        self::REPORT_SALES => ['route' => 'report/sales', 'pattern' => 'reports/sales'],
        self::REPORT_PRODUCT => ['route' => 'report/product', 'pattern' => 'reports/product-performance'],
        self::REPORT_ORDERS => ['route' => 'report/orders', 'pattern' => 'reports/orders'],
        self::REPORT_INVENTORY => ['route' => 'report/inventory', 'pattern' => 'reports/inventory'],
        self::REPORT_LOYALTY => ['route' => 'report/loyalty', 'pattern' => 'reports/loyalty'],
        self::REPORT_EXPORT => ['route' => 'report/export', 'pattern' => 'reports/export/<type:[\w-]+>'],
        self::REPORT_EXPORT_INDEX => ['route' => 'report/export', 'pattern' => 'reports/export'],

        self::ADMIN_DASHBOARD => ['route' => 'admin/index', 'pattern' => 'admin/dashboard'],
        self::ADMIN_ACTIVITY => ['route' => 'admin/activity', 'pattern' => null],
        self::STAFF_DASHBOARD => ['route' => 'staff/index', 'pattern' => 'staff/dashboard'],
        self::CUSTOMER_DASHBOARD => ['route' => 'customer/index', 'pattern' => 'customer/dashboard'],
        self::ADMIN_USER_INDEX => ['route' => 'admin-user/list', 'pattern' => 'admin/users'],
        self::ADMIN_USER_CREATE => ['route' => 'admin-user/create', 'pattern' => 'admin/users/create'],
        self::ADMIN_USER_ASSIGN_ROLE => ['route' => 'admin-user/assign-role', 'pattern' => null],
        self::ADMIN_USER_VIEW => ['route' => 'admin-user/view', 'pattern' => 'admin/users/<id:\d+>'],
        self::ADMIN_USER_UPDATE => ['route' => 'admin-user/update', 'pattern' => 'admin/users/<id:\d+>/update'],
        self::ADMIN_USER_DELETE => ['route' => 'admin-user/delete', 'pattern' => 'admin/users/<id:\d+>/delete'],
        self::ADMIN_ROLE_INDEX => ['route' => 'admin-role/index', 'pattern' => 'admin/roles'],
        self::ADMIN_ROLE_CREATE => ['route' => 'admin-role/create', 'pattern' => 'admin/roles/create'],
        self::ADMIN_ROLE_UPDATE_MATRIX => ['route' => 'admin-role/update-matrix', 'pattern' => 'admin/roles/update-matrix'],
        self::ADMIN_ROLE_UPDATE_PERMISSIONS => ['route' => 'admin-role/update-permissions', 'pattern' => 'admin/roles/<name:[\w-]+>/update-permissions'],
        self::ADMIN_ROLE_UPDATE => ['route' => 'admin-role/update', 'pattern' => 'admin/roles/<name:[\w-]+>/update'],
        self::ADMIN_ROLE_DELETE => ['route' => 'admin-role/delete', 'pattern' => 'admin/roles/<name:[\w-]+>/delete'],
        self::ACTIVITY_LOG_INDEX => ['route' => 'activity-log/index', 'pattern' => 'admin/activity-logs'],
        self::ACTIVITY_LOG_VIEW => ['route' => 'activity-log/view', 'pattern' => 'admin/activity-logs/<id:\d+>'],
        self::SETTINGS => ['route' => 'settings/index', 'pattern' => 'admin/settings'],

        self::NOTIFICATIONS => ['route' => 'notification/index', 'pattern' => 'notifications'],
        self::NOTIFICATION_UNREAD_COUNT => ['route' => 'notification/unread-count', 'pattern' => null],
        self::NOTIFICATION_READ_ALL => ['route' => 'notification/read-all', 'pattern' => 'notifications/read-all'],
        self::NOTIFICATION_READ => ['route' => 'notification/read', 'pattern' => 'notifications/<id:\d+>/read'],
        self::NOTIFICATION_DELETE => ['route' => 'notification/delete', 'pattern' => 'notifications/<id:\d+>/delete'],
        self::REVIEW_CREATE => ['route' => 'product-review/create', 'pattern' => 'reviews/create'],
        self::SESSION_STATUS => ['route' => 'session/status', 'pattern' => null],
        self::SESSION_ACTIVITY => ['route' => 'session/activity', 'pattern' => null],
    ];

    /**
     * @param array<string, int|string> $params
     * @return array{0: string, ...}
     */
    public static function url(string $key, array $params = []): array
    {
        return array_merge(['/' . self::route($key)], $params);
    }

    public static function route(string $key): string
    {
        if (!isset(self::MAP[$key])) {
            throw new InvalidArgumentException("Unknown navigation route: {$key}");
        }

        return self::MAP[$key]['route'];
    }

    /**
     * @return array<string, string>
     */
    public static function urlRules(): array
    {
        $rules = [];
        foreach (self::MAP as $entry) {
            if ($entry['pattern'] !== null) {
                $rules[$entry['pattern']] = $entry['route'];
            }
        }

        return $rules;
    }
}

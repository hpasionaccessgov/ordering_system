<?php

declare(strict_types=1);

namespace app\modules\api;

use yii\base\Module as BaseModule;

final class Module extends BaseModule
{
    public $id = 'api';

    /**
     * @return array<string, string>
     */
    public static function urlRules(): array
    {
        return [
            'POST api/login' => 'api/auth/login',
            'POST api/logout' => 'api/auth/logout',
            'GET api/profile' => 'api/auth/profile',
            'POST api/orders' => 'api/order/create',
            'POST api/orders/<id:\d+>/cancel' => 'api/order/cancel',
            'GET api/orders/<id:\d+>' => 'api/order/view',
            'api/login' => 'api/auth/login',
            'api/logout' => 'api/auth/logout',
            'api/profile' => 'api/auth/profile',
            'api/products' => 'api/product/index',
            'api/products/<id:\d+>' => 'api/product/view',
            'api/categories' => 'api/category/index',
            'api/orders/<id:\d+>/cancel' => 'api/order/cancel',
            'api/orders' => 'api/order/index',
            'api/orders/<id:\d+>' => 'api/order/view',
            'GET api/cart' => 'api/cart/index',
            'POST api/cart/items' => 'api/cart/add',
            'PATCH api/cart/items/<id:\d+>' => 'api/cart/update',
            'DELETE api/cart/items/<id:\d+>' => 'api/cart/remove',
            'DELETE api/cart' => 'api/cart/clear',
            'GET api/loyalty/transactions' => 'api/loyalty/transactions',
            'GET api/loyalty' => 'api/loyalty/index',
            'GET api/rewards' => 'api/loyalty/rewards',
            'POST api/rewards/<id:\d+>/redeem' => 'api/loyalty/redeem',
            'GET api/notifications/unread-count' => 'api/notification/unread-count',
            'GET api/notifications' => 'api/notification/index',
            'POST api/notifications/<id:\d+>/read' => 'api/notification/read',
            'DELETE api/notifications/<id:\d+>' => 'api/notification/delete',
            'GET api/favorites' => 'api/favorite/index',
            'POST api/favorites' => 'api/favorite/add',
            'DELETE api/favorites/<id:\d+>' => 'api/favorite/remove',
            'GET api/reviews' => 'api/review/index',
            'POST api/reviews' => 'api/review/create',
            'api/cart' => 'api/cart/index',
            'api/cart/items' => 'api/cart/add',
            'api/cart/items/<id:\d+>' => 'api/cart/update',
            'api/loyalty/transactions' => 'api/loyalty/transactions',
            'api/loyalty' => 'api/loyalty/index',
            'api/rewards' => 'api/loyalty/rewards',
            'api/rewards/<id:\d+>/redeem' => 'api/loyalty/redeem',
            'api/notifications/unread-count' => 'api/notification/unread-count',
            'api/notifications' => 'api/notification/index',
            'api/notifications/<id:\d+>' => 'api/notification/read',
            'api/favorites' => 'api/favorite/index',
            'api/favorites/<id:\d+>' => 'api/favorite/remove',
            'api/reviews' => 'api/review/index',
        ];
    }
}

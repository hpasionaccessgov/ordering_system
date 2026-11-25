<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$mailDsn = trim((string) ($_ENV['MAIL_DSN'] ?? $_SERVER['MAIL_DSN'] ?? ''));
$mailer = [
    'class' => \yii\symfonymailer\Mailer::class,
    'viewPath' => '@app/mail',
    'useFileTransport' => $mailDsn === '',
];
if ($mailDsn !== '') {
    $mailer['transport'] = ['dsn' => $mailDsn];
}

$config = [
    'id' => 'basic',
    'name' => 'Cafébara',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', \app\services\ActivityLogger::class],
    'container' => [
        'singletons' => [
            \yii\mail\MailerInterface::class => [
                ...$mailer,
            ],
        ],
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'authManager' => [
            'class' => \yii\rbac\DbManager::class,
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '86b95d8059e7f70c9acdc17c14c54162a414d241c9d2d0e737091f590d7dc61f',
        ],
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        'formatter' => [
            'class' => \yii\i18n\Formatter::class,
            'locale' => 'en_PH',
            'currencyCode' => 'PHP',
            'timeZone' => 'Asia/Manila',
            'datetimeFormat' => 'php:M j, Y g:i A',
        ],
        'user' => [
            'class' => \app\services\SessionUser::class,
            'identityClass' => \app\models\User::class,
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => $mailer,
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'site/index',
                'dashboard' => 'site/dashboard',
                'login' => 'site/login',
                'logout' => 'site/logout',
                'register' => 'site/register',
                'profile' => 'site/profile',
                'profile/change-password' => 'site/change-password',
                'profile/deactivate' => 'site/deactivate',
                'contact' => 'site/contact',
                'about' => 'site/about',
                'menu' => 'site/menu',

                'catalog/products' => 'product/index',
                'catalog/products/create' => 'product/create',
                'catalog/products/<id:\d+>' => 'product/view',
                'catalog/products/<id:\d+>/update' => 'product/update',
                'catalog/products/<id:\d+>/delete' => 'product/delete',
                'catalog/products/<id:\d+>/restore' => 'product/restore',

                'catalog/categories' => 'category/index',
                'catalog/categories/create' => 'category/create',
                'catalog/categories/<id:\d+>' => 'category/view',
                'catalog/categories/<id:\d+>/update' => 'category/update',
                'catalog/categories/<id:\d+>/delete' => 'category/delete',

                'sales/orders' => 'order/index',
                'sales/orders/create' => 'order/create',
                'sales/orders/<id:\d+>' => 'order/view',
                'sales/orders/<id:\d+>/receipt' => 'order/receipt',
                'sales/orders/<id:\d+>/process' => 'order/process',
                'sales/orders/<id:\d+>/cancel' => 'order/cancel',
                'sales/orders/<id:\d+>/order-again' => 'order/order-again',
                'sales/kitchen-board' => 'kitchen/board',
                'sales/cart' => 'cart/index',
                'sales/cart/add' => 'cart/add',
                'sales/cart/update-quantity' => 'cart/update-quantity',
                'sales/cart/remove' => 'cart/remove',
                'sales/cart/checkout' => 'cart/checkout',
                'sales/favorites' => 'favorite/index',
                'sales/favorites/toggle' => 'favorite/toggle',
                'sales/inventory' => 'inventory/index',
                'sales/inventory/create' => 'inventory/create',
                'sales/inventory/<id:\d+>' => 'inventory/view',
                'sales/inventory/<id:\d+>/update' => 'inventory/update',
                'sales/inventory/<id:\d+>/delete' => 'inventory/delete',
                'sales/inventory/<id:\d+>/adjust' => 'inventory/adjust',

                'reports/sales' => 'report/sales',
                'reports/products' => 'report/product',
                'reports/orders' => 'report/orders',
                'reports/inventory' => 'report/inventory',
                'reports/export/<type:[\w-]+>' => 'report/export',
                'reports/export' => 'report/export',

                'admin/dashboard' => 'admin/dashboard',
                'staff/dashboard' => 'staff/dashboard',
                'customer/dashboard' => 'customer/dashboard',
                'admin/users' => 'admin-user/list',
                'admin/users/create' => 'admin-user/create',
                'admin/users/<id:\d+>' => 'admin-user/view',
                'admin/users/<id:\d+>/update' => 'admin-user/update',
                'admin/users/<id:\d+>/delete' => 'admin-user/delete',
                'admin/roles' => 'admin-role/index',
                'admin/roles/create' => 'admin-role/create',
                'admin/roles/<name:[\w-]+>/update' => 'admin-role/update',
                'admin/roles/<name:[\w-]+>/delete' => 'admin-role/delete',
                'admin/activity-logs' => 'activity-log/index',
                'admin/activity-logs/<id:\d+>' => 'activity-log/view',
                'admin/settings' => 'settings/index',

                'notifications' => 'notification/index',
                'notifications/read-all' => 'notification/read-all',
                'notifications/<id:\d+>/read' => 'notification/read',
                'notifications/<id:\d+>/delete' => 'notification/delete',

                'reviews/create' => 'product-review/create',
            ],
        ],

    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => \yii\gii\Module::class,
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;

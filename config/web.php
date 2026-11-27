<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
require_once dirname(__DIR__) . '/services/Navigation.php';
require_once dirname(__DIR__) . '/modules/api/Module.php';
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
            'parsers' => [
                'application/json' => \yii\web\JsonParser::class,
            ],
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
            'rules' => array_merge(
                \app\services\Navigation::urlRules(),
                \app\modules\api\Module::urlRules(),
            ),
        ],

    ],
    'modules' => [
        'api' => ['class' => \app\modules\api\Module::class],
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

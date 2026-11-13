<?php

use Dotenv\Dotenv;

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

$env = static function (string $name): string {
    $value = $_ENV[$name] ?? $_SERVER[$name] ?? null;

    if (!is_string($value)) {
        throw new RuntimeException("Missing required environment variable {$name}");
    }

    return $value;
};

return [
    'class' => \yii\db\Connection::class,
    'dsn' => $env('DB_DSN'),
    'username' => $env('DB_USERNAME'),
    'password' => $env('DB_PASSWORD'),
    'charset' => 'utf8mb4',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];

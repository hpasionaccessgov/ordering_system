<?php

use Dotenv\Dotenv;

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

$env = static function (string $name, string $default): string {
    $value = $_ENV[$name] ?? $_SERVER[$name] ?? $default;

    return is_string($value) ? $value : $default;
};

return [
    'adminEmail' => $env('MAIL_ADMIN_ADDRESS', 'admin@example.com'),
    'senderEmail' => $env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
    'senderName' => $env('MAIL_FROM_NAME', 'Cafébara'),
];

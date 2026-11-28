<?php

use Dotenv\Dotenv;

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

$env = static function (string $name, string $default): string {
    $value = $_ENV[$name] ?? $_SERVER[$name] ?? $default;

    return is_string($value) ? $value : $default;
};

$apiCorsOrigins = array_filter(array_map('trim', explode(',', $env(
    'API_CORS_ORIGINS',
    'http://localhost:5173,http://localhost:8100,http://localhost,http://127.0.0.1:5173,http://127.0.0.1:8100,http://127.0.0.1',
))));

return [
    'apiCorsOrigins' => array_values($apiCorsOrigins),
    'adminEmail' => $env('MAIL_ADMIN_ADDRESS', 'admin@example.com'),
    'senderEmail' => $env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
    'senderName' => $env('MAIL_FROM_NAME', 'Cafébara'),
];

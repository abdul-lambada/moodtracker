<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$appConfig = config('app');

if (!is_array($appConfig)) {
    throw new RuntimeException('Unable to load application configuration.');
}

if (!headers_sent()) {
    date_default_timezone_set($appConfig['timezone'] ?? 'UTC');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
}

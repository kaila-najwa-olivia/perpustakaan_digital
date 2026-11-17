<?php

$root = dirname(__DIR__);

if (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) === '/__vercel_debug') {
    header('Content-Type: application/json');

    $keys = [
        'APP_KEY',
        'APP_ENV',
        'APP_URL',
        'DB_CONNECTION',
        'DB_HOST',
        'DB_PORT',
        'DB_DATABASE',
        'DB_USERNAME',
        'DB_PASSWORD',
    ];

    $env = [];

    foreach ($keys as $key) {
        $value = $_SERVER[$key] ?? $_ENV[$key] ?? getenv($key);
        $env[$key] = $value !== false && $value !== null && $value !== '';
    }

    echo json_encode([
        'php_version' => PHP_VERSION,
        'laravel_autoload' => file_exists($root.'/vendor/autoload.php'),
        'laravel_bootstrap' => file_exists($root.'/bootstrap/app.php'),
        'public_index' => file_exists($root.'/public/index.php'),
        'storage_writable' => is_writable($root.'/storage'),
        'bootstrap_cache_writable' => is_writable($root.'/bootstrap/cache'),
        'pdo_pgsql' => extension_loaded('pdo_pgsql'),
        'environment_variables' => $env,
    ], JSON_PRETTY_PRINT);

    exit;
}

require $root.'/public/index.php';
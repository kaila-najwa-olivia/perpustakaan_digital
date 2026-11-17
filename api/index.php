<?php

define('LARAVEL_START', microtime(true));

$root = dirname(__DIR__);

if (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) === '/__vercel_debug') {
    header('Content-Type: application/json');

    echo json_encode([
        'php_version' => PHP_VERSION,
        'temporary_directory_writable' => is_writable(sys_get_temp_dir()),
        'pdo_pgsql' => extension_loaded('pdo_pgsql'),
    ], JSON_PRETTY_PRINT);

    exit;
}

// Siapkan direktori sementara untuk Laravel di Vercel.
$storagePath = '/tmp/laravel-storage';

$directories = [
    $storagePath,
    $storagePath . '/app',
    $storagePath . '/framework/cache/data',
    $storagePath . '/framework/sessions',
    $storagePath . '/framework/views',
    $storagePath . '/logs',
    '/tmp/views',
];

foreach ($directories as $directory) {
    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }
}

// Periksa mode maintenance.
if (file_exists($maintenance = $root . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $root . '/vendor/autoload.php';

$app = require_once $root . '/bootstrap/app.php';

// Arahkan penyimpanan Laravel ke direktori sementara.
$app->useStoragePath($storagePath);

try {
    $app->handleRequest(
        Illuminate\Http\Request::capture()
    );
} catch (\Throwable $exception) {
    error_log(
        'LARAVEL_VERCEL_ERROR: ' .
        get_class($exception) . ' - ' .
        $exception->getMessage()
    );

    error_log($exception->getTraceAsString());

    throw $exception;
}
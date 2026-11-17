<?php

define('LARAVEL_START', microtime(true));

$root = dirname(__DIR__);
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Pemeriksaan runtime Vercel.
if ($requestPath === '/__vercel_debug') {
    header('Content-Type: application/json');

    echo json_encode([
        'php_version' => PHP_VERSION,
        'temporary_directory_writable' => is_writable(sys_get_temp_dir()),
        'pdo_pgsql' => extension_loaded('pdo_pgsql'),
    ], JSON_PRETTY_PRINT);

    exit;
}

// Pemeriksaan log Laravel.
if ($requestPath === '/__laravel_log') {
    header('Content-Type: text/plain; charset=utf-8');

    $logFile = '/tmp/laravel-storage/logs/laravel.log';

    if (!file_exists($logFile)) {
        echo 'FILE LOG BELUM DITEMUKAN';
        exit;
    }

    $logContent = file_get_contents($logFile);

    echo substr($logContent, -8000);
    exit;
}

// Siapkan direktori sementara untuk Laravel di Vercel.
$storagePath = '/tmp/laravel-storage';

$directories = [
    $storagePath,
    $storagePath . '/app',
    $storagePath . '/framework',
    $storagePath . '/framework/cache',
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

// Jalankan aplikasi Laravel.
$app->handleRequest(
    Illuminate\Http\Request::capture()
);

<?php

define('LARAVEL_START', microtime(true));

$root = dirname(__DIR__);
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Pemeriksaan koneksi database Supabase.
if ($requestPath === '/__db_check') {
    header('Content-Type: application/json');

    try {
        $host = getenv('DB_HOST');
        $port = getenv('DB_PORT') ?: '6543';
        $database = getenv('DB_DATABASE');
        $username = getenv('DB_USERNAME');
        $password = getenv('DB_PASSWORD');

        $dsn = "pgsql:host=$host;port=$port;dbname=$database;sslmode=require";

        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 10,
        ]);

        $result = $pdo->query('SELECT 1')->fetchColumn();

        echo json_encode([
            'database_connection' => 'SUCCESS',
            'query_test' => $result == 1 ? 'SUCCESS' : 'FAILED',
        ], JSON_PRETTY_PRINT);

    } catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        'database_connection' => 'FAILED',
        'sqlstate' => $e->errorInfo[0] ?? (string) $e->getCode(),
        'error_type' => get_class($e),
        'error_detail' => $e->errorInfo[2] ?? $e->getMessage(),
    ], JSON_PRETTY_PRINT);
}

    exit;
}

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

<?php

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if ($path === '/__vercel_check') {
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'VERCEL_PHP_OK';
    exit;
}

require __DIR__.'/../public/index.php';
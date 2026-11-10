<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Tentukan path direktori inti Laravel:
// 1. Jika di hosting cPanel (folder hanbank_app berada di luar public_html)
// 2. Jika di localhost / pengembangan lokal (struktur standar)
$appPath = is_dir(__DIR__.'/../hanbank_app')
    ? __DIR__.'/../hanbank_app'
    : __DIR__.'/..';

// Maintenance mode
if (file_exists($maintenance = $appPath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Autoloader (prioritaskan vendor standar development lokal, atau vendor_release jika di production cPanel)
if (file_exists($autoload = $appPath.'/vendor/autoload.php')) {
    require $autoload;
} elseif (file_exists($autoload = $appPath.'/vendor_release/autoload.php')) {
    require $autoload;
} else {
    throw new RuntimeException('Autoloader composer tidak ditemukan.');
}

// Bootstrap application
$app = require_once $appPath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());

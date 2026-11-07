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

// Autoloader (prioritaskan vendor_release jika di-deploy oleh workflow, atau vendor standar)
if (file_exists($autoload = $appPath.'/vendor_release/autoload.php')) {
    require $autoload;
} else {
    require $appPath.'/vendor/autoload.php';
}

// Bootstrap application
$app = require_once $appPath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());

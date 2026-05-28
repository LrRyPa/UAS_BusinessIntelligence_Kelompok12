<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

$appCfg = require __DIR__ . '/config/app.php';
date_default_timezone_set($appCfg['timezone'] ?? 'UTC');

spl_autoload_register(function ($class) {
    $candidates = [
        __DIR__ . '/helpers/'         . $class . '.php',
        __DIR__ . '/app/controllers/' . $class . '.php',
        __DIR__ . '/app/models/'      . $class . '.php',
    ];
    foreach ($candidates as $f) {
        if (file_exists($f)) { require_once $f; return; }
    }
});

require_once __DIR__ . '/helpers/url.php';

Auth::start();

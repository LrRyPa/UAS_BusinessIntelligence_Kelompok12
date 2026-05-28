<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$publicFile = __DIR__ . '/public' . $uri;
if ($uri !== '/' && is_file($publicFile)) {
    $ext = strtolower(pathinfo($publicFile, PATHINFO_EXTENSION));
    $mime = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'svg'  => 'image/svg+xml',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'ico'  => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'json' => 'application/json',
    ];
    if (isset($mime[$ext])) {
        header('Content-Type: ' . $mime[$ext]);
    }
    readfile($publicFile);
    return;
}

require __DIR__ . '/public/index.php';

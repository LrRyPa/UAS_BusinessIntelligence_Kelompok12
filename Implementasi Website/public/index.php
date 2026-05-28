<?php

require __DIR__ . '/../bootstrap.php';

$router = new Router();
$register = require __DIR__ . '/../routes/web.php';
$register($router);

try {
    $router->dispatch();
} catch (Throwable $e) {
    http_response_code(500);
    if (Auth::check()) {
        (new Controller())->render('errors/500', ['error' => $e]);
    } else {
        echo '<h1>Something went wrong</h1><p>' . e($e->getMessage()) . '</p>';
    }
}

<?php

class Router
{
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, $handler): void  { $this->routes['GET'][$this->normalize($path)] = $handler; }
    public function post(string $path, $handler): void { $this->routes['POST'][$this->normalize($path)] = $handler; }

    private function normalize(string $p): string { return trim($p, '/'); }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $route  = $this->normalize($_GET['r'] ?? '');
        $handler = $this->routes[$method][$route] ?? null;

        if (!$handler) {
            if ($method === 'POST') {
                $handler = $this->routes['GET'][$route] ?? null;
            }
        }

        if (!$handler) {
            http_response_code(404);
            (new Controller())->render('errors/404', ['route' => $route]);
            return;
        }

        [$class, $action] = $handler;
        if (!class_exists($class)) {
            throw new RuntimeException("Controller [$class] not found.");
        }
        $instance = new $class();
        if (!method_exists($instance, $action)) {
            throw new RuntimeException("Method [$class::$action] not found.");
        }
        $instance->$action();
    }
}

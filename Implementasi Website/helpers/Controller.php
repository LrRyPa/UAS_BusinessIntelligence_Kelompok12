<?php

class Controller
{
    public function render(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        $appCfg = require __DIR__ . '/../config/app.php';
        $data['app']   = $appCfg;
        $data['user']  = Auth::user();
        $data['flash'] = get_flash();

        extract($data, EXTR_OVERWRITE);

        $viewFile = __DIR__ . '/../app/views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            throw new RuntimeException("View not found: $view");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout === '' || $layout === null) {
            echo $content;
            return;
        }

        $layoutFile = __DIR__ . '/../app/views/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            echo $content;
            return;
        }
        require $layoutFile;
    }

    public function json($payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }

    public function back(): void
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? base_url();
        header('Location: ' . $ref);
        exit;
    }
}
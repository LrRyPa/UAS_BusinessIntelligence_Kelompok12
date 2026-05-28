<?php

function base_url(): string
{
    $cfg = require __DIR__ . '/../config/app.php';
    if (!empty($cfg['base_url'])) return rtrim($cfg['base_url'], '/');
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $script = str_replace('\\', '/', $script);
    if ($script === '/' || $script === '.') $script = '';
    return $scheme . '://' . $host . rtrim($script, '/');
}

function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return base_url() . '/index.php' . ($path ? '?r=' . urlencode($path) : '');
}

function asset(string $path): string
{
    return base_url() . '/assets/' . ltrim($path, '/');
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money(float $value, string $currency = '$'): string
{
    return $currency . number_format($value, 2);
}

function compact_money(float $value, string $currency = '$'): string
{
    $abs = abs($value);
    if ($abs >= 1_000_000_000) return $currency . number_format($value / 1_000_000_000, 2) . 'B';
    if ($abs >= 1_000_000)    return $currency . number_format($value / 1_000_000, 2) . 'M';
    if ($abs >= 1_000)        return $currency . number_format($value / 1_000, 1) . 'K';
    return $currency . number_format($value, 2);
}

function compact_number(float $value): string
{
    $abs = abs($value);
    if ($abs >= 1_000_000) return number_format($value / 1_000_000, 2) . 'M';
    if ($abs >= 1_000)     return number_format($value / 1_000, 1) . 'K';
    return number_format($value);
}

function flash(string $type, string $message): void
{
    Auth::start();
    $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    Auth::start();
    if (isset($_SESSION['_flash'])) {
        $f = $_SESSION['_flash'];
        unset($_SESSION['_flash']);
        return $f;
    }
    return null;
}

function active(string $section, string $current): string
{
    return $section === $current ? 'active' : '';
}

function input(string $key, $default = null)
{
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

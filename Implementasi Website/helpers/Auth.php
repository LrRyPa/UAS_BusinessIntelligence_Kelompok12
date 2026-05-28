<?php

class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $appCfg = require __DIR__ . '/../config/app.php';
            session_set_cookie_params(['lifetime' => $appCfg['session_lifetime']]);
            session_start();
        }
    }

    public static function attempt(string $username, string $password): bool
    {
        self::start();
        $user = (new UserModel())->findByUsername($username);
        if (!$user) return false;
        if (!password_verify($password, $user['password'])) return false;
        if ((int)$user['is_active'] !== 1) return false;

        $_SESSION['user'] = [
            'id'        => (int)$user['id'],
            'username'  => $user['username'],
            'full_name' => $user['full_name'],
            'role'      => $user['role'],
            'email'     => $user['email'],
        ];
        $_SESSION['_token'] = bin2hex(random_bytes(16));
        return true;
    }

    public static function logout(): void
    {
        self::start();
        $_SESSION = [];
        session_destroy();
    }

    public static function check(): bool
    {
        self::start();
        return isset($_SESSION['user']);
    }

    public static function user(): ?array
    {
        self::start();
        return $_SESSION['user'] ?? null;
    }

    public static function role(): ?string
    {
        return self::user()['role'] ?? null;
    }

    public static function roles(): array
    {
        return require __DIR__ . '/../config/roles.php';
    }

    public static function can(string $permission): bool
    {
        $role = self::role();
        if (!$role) return false;
        $cfg = self::roles();
        $perm = $cfg['roles'][$role]['permissions'][$permission] ?? false;
        return $perm === true || $perm === 'view';
    }

    public static function canEdit(string $permission): bool
    {
        $role = self::role();
        $cfg = self::roles();
        return ($cfg['roles'][$role]['permissions'][$permission] ?? false) === true;
    }

    public static function menu(): array
    {
        $role = self::role();
        if (!$role) return [];
        $cfg = self::roles();
        return $cfg['roles'][$role]['menu'] ?? [];
    }

    public static function require(string $permission): void
    {
        if (!self::check()) {
            header('Location: ' . url('login'));
            exit;
        }
        if (!self::can($permission)) {
            http_response_code(403);
            (new Controller())->render('errors/403', ['perm' => $permission]);
            exit;
        }
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: ' . url('login'));
            exit;
        }
    }

    public static function csrfToken(): string
    {
        self::start();
        if (empty($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(16));
        }
        return $_SESSION['_token'];
    }

    public static function checkCsrf(?string $token): bool
    {
        self::start();
        return isset($_SESSION['_token']) && hash_equals($_SESSION['_token'], (string)$token);
    }
}

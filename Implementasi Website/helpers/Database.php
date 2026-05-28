<?php

class Database
{
    private static ?PDO $instance = null;

    public static function connection(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $appCfg = require __DIR__ . '/../config/app.php';
        $dbCfg  = require __DIR__ . '/../config/database.php';
        $driver = $appCfg['db_driver'] ?? 'sqlite';
        $cfg    = $dbCfg[$driver] ?? null;

        if (!$cfg) {
            throw new RuntimeException("Database driver [$driver] is not configured.");
        }

        try {
            if ($driver === 'sqlite') {
                if (!file_exists($cfg['database'])) {
                    throw new RuntimeException("SQLite database not found: {$cfg['database']}");
                }
                $pdo = new PDO('sqlite:' . $cfg['database']);
                $pdo->exec('PRAGMA foreign_keys = ON;');
            } else {
                $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s',
                    $cfg['host'], $cfg['port'], $cfg['database'], $cfg['charset']);
                $pdo = new PDO($dsn, $cfg['username'], $cfg['password']);
            }
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage());
        }

        self::$instance = $pdo;
        return $pdo;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function fetchValue(string $sql, array $params = [])
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : null;
    }

    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}

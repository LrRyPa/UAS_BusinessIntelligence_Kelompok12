<?php

class UserModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
        $this->ensureSchema();
    }

    private function ensureSchema(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS app_users (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            username    TEXT UNIQUE NOT NULL,
            password    TEXT NOT NULL,
            full_name   TEXT NOT NULL,
            email       TEXT,
            role        TEXT NOT NULL,
            is_active   INTEGER NOT NULL DEFAULT 1,
            created_at  TEXT DEFAULT (datetime('now')),
            updated_at  TEXT DEFAULT (datetime('now'))
        )";
        $this->db->exec($sql);

        $count = (int)$this->db->query("SELECT COUNT(*) FROM app_users")->fetchColumn();
        if ($count === 0) {
            $defaults = [
                ['admin',     'admin123',     'Carlos Hernandez', 'admin@maventoys.mx',     'admin'],
                ['manager',   'manager123',   'Sofia Ramirez',    'manager@maventoys.mx',   'manager'],
                ['sales',     'sales123',     'Diego Lopez',      'sales@maventoys.mx',     'sales'],
                ['warehouse', 'warehouse123', 'Maria Gonzalez',   'warehouse@maventoys.mx', 'warehouse'],
            ];
            $stmt = $this->db->prepare(
                "INSERT INTO app_users (username, password, full_name, email, role)
                 VALUES (?, ?, ?, ?, ?)"
            );
            foreach ($defaults as $u) {
                $stmt->execute([
                    $u[0],
                    password_hash($u[1], PASSWORD_BCRYPT),
                    $u[2], $u[3], $u[4],
                ]);
            }
        }
    }

    public function findByUsername(string $username): ?array
    {
        return Database::fetchOne("SELECT * FROM app_users WHERE username = ?", [$username]);
    }

    public function find(int $id): ?array
    {
        return Database::fetchOne("SELECT * FROM app_users WHERE id = ?", [$id]);
    }

    public function all(string $search = '', int $limit = 50, int $offset = 0): array
    {
        $where  = '';
        $params = [];
        if ($search !== '') {
            $where  = "WHERE username LIKE ? OR full_name LIKE ? OR email LIKE ?";
            $like   = "%$search%";
            $params = [$like, $like, $like];
        }
 
        try {
            $rows = Database::fetchAll(
                "SELECT id, username, full_name, email, role, is_active, created_at
                 FROM app_users $where ORDER BY id DESC LIMIT $limit OFFSET $offset",
                $params
            );
            $total = Database::fetchValue(
                "SELECT COUNT(*) FROM app_users $where",
                $params
            );
            return [
                'rows'  => $rows  ?? [],
                'total' => (int)($total ?? 0),
            ];
        } catch (Throwable $e) {
            error_log($e->getMessage());
            return [
                'rows'  => [],
                'total' => 0,
            ];
        }
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO app_users (username, password, full_name, email, role, is_active)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['username'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['full_name'],
            $data['email'] ?? null,
            $data['role'],
            isset($data['is_active']) ? (int)$data['is_active'] : 1,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = ['full_name = ?', 'email = ?', 'role = ?', 'is_active = ?', "updated_at = datetime('now')"];
        $params = [$data['full_name'], $data['email'] ?? null, $data['role'], (int)($data['is_active'] ?? 1)];
        if (!empty($data['password'])) {
            $fields[] = 'password = ?';
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        $params[] = $id;
        $stmt = $this->db->prepare(
            "UPDATE app_users SET " . implode(', ', $fields) . " WHERE id = ?"
        );
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM app_users WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

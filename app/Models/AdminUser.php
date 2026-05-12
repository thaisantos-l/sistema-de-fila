<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class AdminUser
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Database::connection();
    }

    /** @return array<string, mixed>|null */
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->connection->prepare('SELECT * FROM admin_users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    public function countUsers(): int
    {
        $stmt = $this->connection->query('SELECT COUNT(*) AS total FROM admin_users');
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function create(string $username, string $plainPassword): int
    {
        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
        $stmt = $this->connection->prepare('INSERT INTO admin_users (username, password_hash) VALUES (:username, :password_hash)');
        $stmt->execute([
            'username' => $username,
            'password_hash' => $hash,
        ]);

        return (int) $this->connection->lastInsertId();
    }
}

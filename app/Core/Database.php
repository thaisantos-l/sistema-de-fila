<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        /** @var array<string, mixed> $db */
        $db = Config::get('database', []);

        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            $db['driver'] ?? 'mysql',
            $db['host'] ?? 'localhost',
            (int) ($db['port'] ?? 3306),
            $db['database'] ?? '',
            $db['charset'] ?? 'utf8mb4'
        );

        try {
            self::$connection = new PDO(
                $dsn,
                (string) ($db['username'] ?? ''),
                (string) ($db['password'] ?? ''),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $exception) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao conectar no banco de dados.',
                'error' => Config::get('app.debug', false) ? $exception->getMessage() : null,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        return self::$connection;
    }
}

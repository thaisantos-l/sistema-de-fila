<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class QueueTicket
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Database::connection();
    }

    public function create(string $nome, ?string $telefone): int
    {
        $sql = 'INSERT INTO queue_tickets (nome, telefone, status) VALUES (:nome, :telefone, :status)';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            'nome' => $nome,
            'telefone' => $telefone,
            'status' => 'aguardando',
        ]);

        return (int) $this->connection->lastInsertId();
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $sql = 'SELECT * FROM queue_tickets WHERE id = :id LIMIT 1';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    /** @return array<int, array<string, mixed>> */
    public function listAll(): array
    {
        $sql = "SELECT * FROM queue_tickets
                ORDER BY
                    CASE status
                        WHEN 'aguardando' THEN 1
                        WHEN 'em_atendimento' THEN 2
                        WHEN 'finalizado' THEN 3
                        WHEN 'cancelado' THEN 4
                    END,
                    created_at ASC,
                    id ASC";

        $stmt = $this->connection->query($sql);
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function updateStatus(int $id, string $status, ?string $calledAt, ?string $finishedAt, ?string $canceledAt): bool
    {
        $sql = 'UPDATE queue_tickets
                SET status = :status,
                    called_at = :called_at,
                    finished_at = :finished_at,
                    canceled_at = :canceled_at,
                    updated_at = NOW()
                WHERE id = :id';

        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([
            'status' => $status,
            'called_at' => $calledAt,
            'finished_at' => $finishedAt,
            'canceled_at' => $canceledAt,
            'id' => $id,
        ]);
    }

    public function deleteById(int $id): bool
    {
        $stmt = $this->connection->prepare('DELETE FROM queue_tickets WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function countAhead(int $id, string $createdAt): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM queue_tickets
                WHERE status = 'aguardando'
                  AND (
                    created_at < :created_at_before
                    OR (created_at = :created_at_equal AND id < :id)
                  )";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            'created_at_before' => $createdAt,
            'created_at_equal' => $createdAt,
            'id' => $id,
        ]);

        $result = $stmt->fetch();
        if (!is_array($result)) {
            return 0;
        }

        return (int) ($result['total'] ?? 0);
    }

    /** @return array<string, int|float> */
    public function metrics(): array
    {
        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = 'aguardando' THEN 1 ELSE 0 END) AS aguardando,
                    SUM(CASE WHEN status = 'em_atendimento' THEN 1 ELSE 0 END) AS em_atendimento,
                    SUM(CASE WHEN status = 'finalizado' THEN 1 ELSE 0 END) AS finalizado,
                    SUM(CASE WHEN status = 'cancelado' THEN 1 ELSE 0 END) AS cancelado,
                    AVG(CASE
                        WHEN called_at IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, created_at, called_at)
                        ELSE NULL
                    END) AS tempo_medio_espera_min
                FROM queue_tickets";

        $stmt = $this->connection->query($sql);
        $result = $stmt->fetch();
        if (!is_array($result)) {
            return [
                'total' => 0,
                'aguardando' => 0,
                'em_atendimento' => 0,
                'finalizado' => 0,
                'cancelado' => 0,
                'tempo_medio_espera_min' => 0.0,
            ];
        }

        return [
            'total' => (int) ($result['total'] ?? 0),
            'aguardando' => (int) ($result['aguardando'] ?? 0),
            'em_atendimento' => (int) ($result['em_atendimento'] ?? 0),
            'finalizado' => (int) ($result['finalizado'] ?? 0),
            'cancelado' => (int) ($result['cancelado'] ?? 0),
            'tempo_medio_espera_min' => round((float) ($result['tempo_medio_espera_min'] ?? 0), 2),
        ];
    }

    /** @return array<string, mixed>|null */
    public function lockNextWaiting(): ?array
    {
        $sql = "SELECT *
                FROM queue_tickets
                WHERE status = 'aguardando'
                ORDER BY created_at ASC, id ASC
                LIMIT 1
                FOR UPDATE";

        $stmt = $this->connection->query($sql);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    /** @return array<string, mixed>|null */
    public function findActiveByPhone(string $telefone): ?array
    {
        $sql = "SELECT *
                FROM queue_tickets
                WHERE telefone = :telefone
                  AND status IN ('aguardando', 'em_atendimento')
                ORDER BY created_at ASC, id ASC
                LIMIT 1";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['telefone' => $telefone]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    /** @return array<string, mixed>|null */
    public function lockActiveByPhone(string $telefone): ?array
    {
        $sql = "SELECT *
                FROM queue_tickets
                WHERE telefone = :telefone
                  AND status IN ('aguardando', 'em_atendimento')
                ORDER BY created_at ASC, id ASC
                LIMIT 1
                FOR UPDATE";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['telefone' => $telefone]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }
}

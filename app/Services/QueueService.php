<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\QueueTicket;
use PDOException;

final class QueueService
{
    private QueueTicket $tickets;

    public function __construct()
    {
        $this->tickets = new QueueTicket();
    }

    /** @return array<string, mixed> */
    public function enqueue(string $nome, ?string $telefone): array
    {
        $nome = trim($nome);
        $telefone = $this->sanitizeTelefone($telefone);
        $connection = Database::connection();

        if ($nome === '' || strlen($nome) < 3) {
            return [
                'ok' => false,
                'message' => 'Nome deve ter pelo menos 3 caracteres.',
            ];
        }

        if ($telefone === null || strlen($telefone) < 10) {
            return [
                'ok' => false,
                'message' => 'Informe um telefone válido com DDD.',
            ];
        }

        try {
            $connection->beginTransaction();

            $existingActive = $this->tickets->lockActiveByPhone($telefone);
            if ($existingActive !== null) {
                $existingActive['position'] = $this->positionForTicket($existingActive);
                $connection->commit();

                return [
                    'ok' => false,
                    'message' => 'Já existe uma senha ativa para este telefone.',
                    'data' => $existingActive,
                ];
            }

            $ticketId = $this->tickets->create($nome, $telefone);
            $ticket = $this->tickets->findById($ticketId);

            if ($ticket === null) {
                $connection->rollBack();
                return [
                    'ok' => false,
                    'message' => 'Não foi possível criar a senha da fila.',
                ];
            }

            $position = $this->positionForTicket($ticket);
            $ticket['position'] = $position;
            $connection->commit();

            return [
                'ok' => true,
                'message' => 'Pessoa cadastrada na fila com sucesso.',
                'data' => $ticket,
            ];
        } catch (PDOException $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            return [
                'ok' => false,
                'message' => 'Erro ao cadastrar na fila.',
                'error' => $exception->getMessage(),
            ];
        }
    }

    /** @return array<string, mixed> */
    public function listQueue(): array
    {
        $rows = $this->tickets->listAll();
        $waitingPosition = 0;

        foreach ($rows as &$row) {
            if (($row['status'] ?? '') === 'aguardando') {
                $waitingPosition++;
                $row['position'] = $waitingPosition;
                continue;
            }

            $row['position'] = null;
        }
        unset($row);

        return [
            'ok' => true,
            'message' => 'Fila listada com sucesso.',
            'data' => $rows,
        ];
    }

    /** @return array<string, mixed> */
    public function find(int $id): array
    {
        $ticket = $this->tickets->findById($id);
        if ($ticket === null) {
            return [
                'ok' => false,
                'message' => 'Registro não encontrado.',
            ];
        }

        $ticket['position'] = $this->positionForTicket($ticket);

        return [
            'ok' => true,
            'message' => 'Registro encontrado.',
            'data' => $ticket,
        ];
    }

    /** @return array<string, mixed> */
    public function callNext(): array
    {
        $connection = Database::connection();

        try {
            $connection->beginTransaction();
            $next = $this->tickets->lockNextWaiting();

            if ($next === null) {
                $connection->commit();
                return [
                    'ok' => false,
                    'message' => 'Não há pessoas aguardando na fila.',
                ];
            }

            $this->tickets->updateStatus((int) $next['id'], 'em_atendimento', date('Y-m-d H:i:s'), null, null);
            $connection->commit();

            return $this->find((int) $next['id']);
        } catch (PDOException $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            return [
                'ok' => false,
                'message' => 'Erro ao chamar próximo da fila.',
                'error' => $exception->getMessage(),
            ];
        }
    }

    /** @return array<string, mixed> */
    public function cancel(int $id): array
    {
        $ticket = $this->tickets->findById($id);
        if ($ticket === null) {
            return ['ok' => false, 'message' => 'Registro não encontrado.'];
        }

        if (in_array($ticket['status'], ['finalizado', 'cancelado'], true)) {
            return ['ok' => false, 'message' => 'Esse registro não pode ser cancelado.'];
        }

        $this->tickets->updateStatus($id, 'cancelado', $ticket['called_at'], $ticket['finished_at'], date('Y-m-d H:i:s'));
        return $this->find($id);
    }

    /** @return array<string, mixed> */
    public function finish(int $id): array
    {
        $ticket = $this->tickets->findById($id);
        if ($ticket === null) {
            return ['ok' => false, 'message' => 'Registro não encontrado.'];
        }

        if ($ticket['status'] !== 'em_atendimento') {
            return ['ok' => false, 'message' => 'Apenas registros em atendimento podem ser finalizados.'];
        }

        $calledAt = $ticket['called_at'] ?? date('Y-m-d H:i:s');
        $this->tickets->updateStatus($id, 'finalizado', $calledAt, date('Y-m-d H:i:s'), $ticket['canceled_at']);

        return $this->find($id);
    }

    /** @return array<string, mixed> */
    public function updateStatus(int $id, string $newStatus): array
    {
        $allowed = ['aguardando', 'em_atendimento', 'finalizado', 'cancelado'];
        if (!in_array($newStatus, $allowed, true)) {
            return ['ok' => false, 'message' => 'Status inválido.'];
        }

        $ticket = $this->tickets->findById($id);
        if ($ticket === null) {
            return ['ok' => false, 'message' => 'Registro não encontrado.'];
        }

        $current = (string) $ticket['status'];
        if (!$this->canTransition($current, $newStatus)) {
            return [
                'ok' => false,
                'message' => sprintf('Transição inválida: %s -> %s.', $current, $newStatus),
            ];
        }

        $calledAt = $ticket['called_at'];
        $finishedAt = $ticket['finished_at'];
        $canceledAt = $ticket['canceled_at'];

        if ($newStatus === 'em_atendimento' && $calledAt === null) {
            $calledAt = date('Y-m-d H:i:s');
        }

        if ($newStatus === 'finalizado') {
            if ($calledAt === null) {
                $calledAt = date('Y-m-d H:i:s');
            }
            $finishedAt = date('Y-m-d H:i:s');
            $canceledAt = null;
        }

        if ($newStatus === 'cancelado') {
            $canceledAt = date('Y-m-d H:i:s');
            $finishedAt = null;
        }

        if ($newStatus === 'aguardando') {
            $calledAt = null;
            $finishedAt = null;
            $canceledAt = null;
        }

        $this->tickets->updateStatus($id, $newStatus, $calledAt, $finishedAt, $canceledAt);
        return $this->find($id);
    }

    /** @return array<string, mixed> */
    public function delete(int $id): array
    {
        $ticket = $this->tickets->findById($id);
        if ($ticket === null) {
            return ['ok' => false, 'message' => 'Registro não encontrado.'];
        }

        $this->tickets->deleteById($id);

        return [
            'ok' => true,
            'message' => 'Registro removido com sucesso.',
            'data' => ['id' => $id],
        ];
    }

    /** @return array<string, int|float> */
    public function metrics(): array
    {
        return $this->tickets->metrics();
    }

    /** @param array<string, mixed> $ticket */
    private function positionForTicket(array $ticket): ?int
    {
        if (($ticket['status'] ?? '') !== 'aguardando') {
            return null;
        }

        $ahead = $this->tickets->countAhead((int) $ticket['id'], (string) $ticket['created_at']);
        return $ahead + 1;
    }

    private function sanitizeTelefone(?string $telefone): ?string
    {
        if ($telefone === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $telefone) ?: '';
        if ($digits === '') {
            return null;
        }

        return substr($digits, 0, 11);
    }

    private function canTransition(string $from, string $to): bool
    {
        if ($from === $to) {
            return true;
        }

        $map = [
            'aguardando' => ['em_atendimento', 'cancelado'],
            'em_atendimento' => ['finalizado', 'cancelado'],
            'finalizado' => [],
            'cancelado' => [],
        ];

        return in_array($to, $map[$from] ?? [], true);
    }
}

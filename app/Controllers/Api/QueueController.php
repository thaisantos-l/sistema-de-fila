<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Auth;
use App\Core\Response;
use App\Core\Request;
use App\Services\QueueService;

final class QueueController
{
    private QueueService $service;

    public function __construct()
    {
        $this->service = new QueueService();
    }

    public function index(Request $request): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $result = $this->service->listQueue();
        $status = $result['ok'] ? 200 : 400;

        Response::json([
            'success' => $result['ok'],
            'message' => $result['message'],
            'data' => $result['data'] ?? null,
        ], $status);
    }

    public function store(Request $request): void
    {
        $nome = (string) $request->input('nome', '');
        $telefone = $request->input('telefone');
        $telefone = is_string($telefone) ? $telefone : null;

        $result = $this->service->enqueue($nome, $telefone);
        $status = 201;
        if (!$result['ok']) {
            $status = ($result['message'] ?? '') === 'Já existe uma senha ativa para este telefone.' ? 409 : 422;
        }

        Response::json([
            'success' => $result['ok'],
            'message' => $result['message'],
            'data' => $result['data'] ?? null,
        ], $status);
    }

    public function show(Request $request): void
    {
        $id = (int) $request->param('id', 0);
        if ($id <= 0) {
            Response::json([
                'success' => false,
                'message' => 'ID inválido.',
            ], 422);
            return;
        }

        $result = $this->service->find($id);
        $status = $result['ok'] ? 200 : 404;

        Response::json([
            'success' => $result['ok'],
            'message' => $result['message'],
            'data' => $result['data'] ?? null,
        ], $status);
    }

    public function callNext(Request $request): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $result = $this->service->callNext();
        $status = $result['ok'] ? 200 : 409;

        Response::json([
            'success' => $result['ok'],
            'message' => $result['message'],
            'data' => $result['data'] ?? null,
        ], $status);
    }

    public function cancel(Request $request): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $id = (int) $request->param('id', 0);
        $result = $this->service->cancel($id);
        $status = $result['ok'] ? 200 : 422;

        Response::json([
            'success' => $result['ok'],
            'message' => $result['message'],
            'data' => $result['data'] ?? null,
        ], $status);
    }

    public function finish(Request $request): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $id = (int) $request->param('id', 0);
        $result = $this->service->finish($id);
        $status = $result['ok'] ? 200 : 422;

        Response::json([
            'success' => $result['ok'],
            'message' => $result['message'],
            'data' => $result['data'] ?? null,
        ], $status);
    }

    public function updateStatus(Request $request): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $id = (int) $request->param('id', 0);
        $statusValue = (string) $request->input('status', '');

        $result = $this->service->updateStatus($id, $statusValue);
        $status = $result['ok'] ? 200 : 422;

        Response::json([
            'success' => $result['ok'],
            'message' => $result['message'],
            'data' => $result['data'] ?? null,
        ], $status);
    }

    public function destroy(Request $request): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $id = (int) $request->param('id', 0);
        $result = $this->service->delete($id);
        $status = $result['ok'] ? 200 : 404;

        Response::json([
            'success' => $result['ok'],
            'message' => $result['message'],
            'data' => $result['data'] ?? null,
        ], $status);
    }

    public function metrics(Request $request): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $metrics = $this->service->metrics();

        Response::json([
            'success' => true,
            'message' => 'Métricas carregadas com sucesso.',
            'data' => $metrics,
        ]);
    }

    private function requireAdmin(): bool
    {
        if (Auth::check()) {
            return true;
        }

        Response::json([
            'success' => false,
            'message' => 'Não autorizado. Faça login no painel administrativo.',
        ], 401);

        return false;
    }
}

<?php

declare(strict_types=1);

use App\Controllers\Api\QueueController;

/** @var App\Core\Router $router */
$router->add('GET', '/api/queue', [QueueController::class, 'index']);
$router->add('POST', '/api/queue', [QueueController::class, 'store']);
$router->add('GET', '/api/queue/{id}', [QueueController::class, 'show']);
$router->add('PATCH', '/api/queue/next/call', [QueueController::class, 'callNext']);
$router->add('PATCH', '/api/queue/{id}/cancel', [QueueController::class, 'cancel']);
$router->add('PATCH', '/api/queue/{id}/finish', [QueueController::class, 'finish']);
$router->add('PATCH', '/api/queue/{id}/status', [QueueController::class, 'updateStatus']);
$router->add('DELETE', '/api/queue/{id}', [QueueController::class, 'destroy']);
$router->add('GET', '/api/metrics', [QueueController::class, 'metrics']);

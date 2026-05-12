<?php

declare(strict_types=1);

session_start();

use App\Core\Config;
use App\Core\Env;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/../app/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});

Env::load(__DIR__ . '/../.env');
Config::load('app', __DIR__ . '/../config/app.php');
Config::load('database', __DIR__ . '/../config/database.php');

$appUrlPath = parse_url((string) Config::get('app.url', ''), PHP_URL_PATH);
$basePath = is_string($appUrlPath) ? rtrim($appUrlPath, '/') : '';
if ($basePath === '/' || $basePath === '.') {
    $basePath = '';
}

$rawPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$isApiRequest = false;
if ($basePath !== '' && str_starts_with($rawPath, $basePath . '/')) {
    $relative = substr($rawPath, strlen($basePath));
    $isApiRequest = str_starts_with($relative, '/api/');
} else {
    $isApiRequest = str_starts_with($rawPath, '/api/');
}

if ($isApiRequest) {
    ini_set('display_errors', '0');
    ini_set('html_errors', '0');
}

set_exception_handler(static function (Throwable $exception) use ($isApiRequest): void {
    if ($isApiRequest) {
        Response::json([
            'success' => false,
            'message' => 'Erro interno no servidor.',
            'error' => Config::get('app.debug', false) ? $exception->getMessage() : null,
        ], 500);
        return;
    }

    http_response_code(500);
    echo 'Erro interno no servidor.';
});

$router = new Router();
require __DIR__ . '/../routes/web.php';
require __DIR__ . '/../routes/api.php';

$request = Request::captureWithBasePath($basePath);

$router->dispatch($request);

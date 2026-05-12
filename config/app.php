<?php

declare(strict_types=1);

return [
    'name' => $_ENV['APP_NAME'] ?? 'Sistema de Filas',
    'env' => $_ENV['APP_ENV'] ?? 'local',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOL),
    'url' => $_ENV['APP_URL'] ?? (function (): string {
        $https = $_SERVER['HTTPS'] ?? 'off';
        $scheme = ($https !== 'off' && $https !== '') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8888';
        $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
        $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        if ($basePath === '' || $basePath === '.' || $basePath === '/') {
            return $scheme . '://' . $host;
        }

        return $scheme . '://' . $host . $basePath;
    })(),
    'admin_session_key' => $_ENV['ADMIN_SESSION_KEY'] ?? 'queue_admin_auth',
];

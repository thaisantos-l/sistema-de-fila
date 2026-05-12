<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    /** @param array<string, mixed> $data */
    public static function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public static function redirect(string $path): void
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            header('Location: ' . $path);
            exit;
        }

        $appUrlPath = parse_url((string) Config::get('app.url', ''), PHP_URL_PATH);
        $basePath = is_string($appUrlPath) ? rtrim($appUrlPath, '/') : '';
        if ($basePath === '/' || $basePath === '.') {
            $basePath = '';
        }

        $target = str_starts_with($path, '/') ? $path : '/' . $path;
        header('Location: ' . $basePath . $target);
        exit;
    }
}

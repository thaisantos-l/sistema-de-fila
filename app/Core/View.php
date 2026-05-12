<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    /** @param array<string, mixed> $data */
    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $viewFile = __DIR__ . '/../../public/views/' . $view . '.php';
        $layoutFile = __DIR__ . '/../../public/views/layouts/' . $layout . '.php';
        $appUrlPath = parse_url((string) Config::get('app.url', ''), PHP_URL_PATH);
        $basePath = is_string($appUrlPath) ? rtrim($appUrlPath, '/') : '';
        if ($basePath === '/' || $basePath === '.') {
            $basePath = '';
        }

        if (!is_file($viewFile)) {
            http_response_code(500);
            echo 'View não encontrada: ' . htmlspecialchars($view);
            return;
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $viewFile;
        $content = (string) ob_get_clean();

        if (is_file($layoutFile)) {
            include $layoutFile;
            return;
        }

        echo $content;
    }
}

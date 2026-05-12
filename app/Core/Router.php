<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<int, array<string, mixed>> */
    private array $routes = [];

    /** @param callable|array{0:class-string,1:string} $handler */
    public function add(string $method, string $pattern, callable|array $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'regex' => $this->toRegex($pattern),
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }

            if (!preg_match($route['regex'], $request->path(), $matches)) {
                continue;
            }

            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }
            $request->setParams($params);

            $handler = $route['handler'];
            if (is_array($handler)) {
                [$class, $method] = $handler;
                $instance = new $class();
                $instance->{$method}($request);
                return;
            }

            $handler($request);
            return;
        }

        $isApi = str_starts_with($request->path(), '/api/');
        if ($isApi) {
            Response::json([
                'success' => false,
                'message' => 'Rota não encontrada.',
            ], 404);
            return;
        }

        http_response_code(404);
        echo 'Página não encontrada.';
    }

    private function toRegex(string $pattern): string
    {
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern) ?: $pattern;
        return '#^' . $regex . '$#';
    }
}

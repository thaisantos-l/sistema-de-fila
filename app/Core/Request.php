<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    /** @param array<string, string> $params */
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query,
        private readonly array $body,
        private array $params = []
    ) {
    }

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        $query = $_GET;
        $body = self::parseBody($method);

        return new self($method, $path, $query, $body);
    }

    public static function captureWithBasePath(string $basePath): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        $normalizedBasePath = rtrim($basePath, '/');
        if ($normalizedBasePath !== '' && $normalizedBasePath !== '/' && str_starts_with($path, $normalizedBasePath)) {
            $trimmed = substr($path, strlen($normalizedBasePath));
            $path = $trimmed === '' ? '/' : $trimmed;
        }

        $query = $_GET;
        $body = self::parseBody($method);

        return new self($method, $path, $query, $body);
    }

    /** @return array<string, mixed> */
    private static function parseBody(string $method): array
    {
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return [];
        }

        $contentType = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input') ?: '';
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : [];
        }

        if (in_array($method, ['PUT', 'PATCH', 'DELETE'], true)) {
            $raw = file_get_contents('php://input') ?: '';
            parse_str($raw, $parsed);
            return is_array($parsed) ? $parsed : [];
        }

        return $_POST;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return $this->body;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    /** @param array<string, string> $params */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }
}

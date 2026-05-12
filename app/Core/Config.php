<?php

declare(strict_types=1);

namespace App\Core;

final class Config
{
    /** @var array<string, mixed> */
    private static array $items = [];

    public static function load(string $name, string $file): void
    {
        if (!is_file($file)) {
            self::$items[$name] = [];
            return;
        }

        $config = require $file;
        self::$items[$name] = is_array($config) ? $config : [];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = self::$items;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}

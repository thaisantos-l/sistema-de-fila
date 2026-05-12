<?php

declare(strict_types=1);

namespace App\Core;

final class Auth
{
    public static function login(int $adminId, string $username): void
    {
        $key = (string) Config::get('app.admin_session_key', 'queue_admin_auth');
        $_SESSION[$key] = [
            'id' => $adminId,
            'username' => $username,
        ];
    }

    public static function logout(): void
    {
        $key = (string) Config::get('app.admin_session_key', 'queue_admin_auth');
        unset($_SESSION[$key]);
    }

    public static function check(): bool
    {
        $key = (string) Config::get('app.admin_session_key', 'queue_admin_auth');
        return isset($_SESSION[$key]['id']);
    }

    /** @return array<string, mixed>|null */
    public static function user(): ?array
    {
        $key = (string) Config::get('app.admin_session_key', 'queue_admin_auth');
        return $_SESSION[$key] ?? null;
    }
}

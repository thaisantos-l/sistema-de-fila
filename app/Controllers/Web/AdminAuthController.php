<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\AdminUser;

final class AdminAuthController
{
    public function showLogin(Request $request): void
    {
        if (Auth::check()) {
            Response::redirect('/admin');
        }

        View::render('admin/login', [
            'title' => 'Login administrativo',
            'error' => null,
        ]);
    }

    public function login(Request $request): void
    {
        $username = trim((string) $request->input('username', ''));
        $password = (string) $request->input('password', '');

        if ($username === '' || $password === '') {
            View::render('admin/login', [
                'title' => 'Login administrativo',
                'error' => 'Informe usuário e senha.',
            ]);
            return;
        }

        $admins = new AdminUser();

        // Se não existir nenhum admin, cria o primeiro usuário automaticamente.
        if ($admins->countUsers() === 0) {
            $admins->create($username, $password);
        }

        $user = $admins->findByUsername($username);
        if ($user === null || !password_verify($password, (string) $user['password_hash'])) {
            View::render('admin/login', [
                'title' => 'Login administrativo',
                'error' => 'Credenciais inválidas.',
            ]);
            return;
        }

        Auth::login((int) $user['id'], (string) $user['username']);
        Response::redirect('/admin');
    }

    public function logout(Request $request): void
    {
        Auth::logout();
        Response::redirect('/admin/login');
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

final class AdminDashboardController
{
    public function index(Request $request): void
    {
        if (!Auth::check()) {
            Response::redirect('/admin/login');
        }

        View::render('admin/dashboard', [
            'title' => 'Painel administrativo',
            'admin' => Auth::user(),
        ]);
    }
}

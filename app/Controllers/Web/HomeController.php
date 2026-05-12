<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Config;
use App\Core\Request;
use App\Core\View;

final class HomeController
{
    public function index(Request $request): void
    {
        View::render('public/home', [
            'title' => Config::get('app.name', 'Sistema de Filas'),
        ]);
    }
}

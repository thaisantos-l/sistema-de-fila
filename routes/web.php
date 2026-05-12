<?php

declare(strict_types=1);

use App\Controllers\Web\AdminAuthController;
use App\Controllers\Web\AdminDashboardController;
use App\Controllers\Web\HomeController;

/** @var App\Core\Router $router */
$router->add('GET', '/', [HomeController::class, 'index']);
$router->add('GET', '/admin/login', [AdminAuthController::class, 'showLogin']);
$router->add('POST', '/admin/login', [AdminAuthController::class, 'login']);
$router->add('POST', '/admin/logout', [AdminAuthController::class, 'logout']);
$router->add('GET', '/admin', [AdminDashboardController::class, 'index']);

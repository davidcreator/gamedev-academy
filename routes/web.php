<?php
// routes/web.php

use App\Controllers\HomeController;
use App\Controllers\Auth\LoginController;
use App\Controllers\Auth\RegisterController;

// Rotas Públicas
$router->get('/', [HomeController::class, 'index']);
$router->get('/courses', [HomeController::class, 'courses']);
$router->get('/course/{slug}', [HomeController::class, 'course']);
$router->get('/news', [HomeController::class, 'news']);
$router->get('/news/{slug}', [HomeController::class, 'newsArticle']);

// Autenticação (apenas visitantes)
$router->group(['middleware' => 'guest'], function ($router) {
    $router->get('/login', [LoginController::class, 'showForm']);
    $router->post('/login', [LoginController::class, 'login']);
    $router->get('/register', [RegisterController::class, 'showForm']);
    $router->post('/register', [RegisterController::class, 'register']);
    $router->get('/forgot-password', [LoginController::class, 'forgotPassword']);
    $router->post('/forgot-password', [LoginController::class, 'sendResetLink']);
    $router->get('/reset-password/{token}', [LoginController::class, 'resetPassword']);
    $router->post('/reset-password', [LoginController::class, 'updatePassword']);
});

// Logout (apenas autenticados)
$router->group(['middleware' => 'auth'], function ($router) {
    $router->get('/logout', [LoginController::class, 'logout']);
    $router->post('/logout', [LoginController::class, 'logout']);
});
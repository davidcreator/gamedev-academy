<?php
// routes/api.php

use App\Controllers\User\DashboardController;
use App\Controllers\User\ProfileController;
use App\Controllers\User\CourseController;
use App\Controllers\User\AchievementController;

// Rotas do Painel do Usuário
$router->group(['prefix' => 'user', 'middleware' => 'auth'], function ($router) {
    
    // Dashboard
    $router->get('/', [DashboardController::class, 'index']);
    
    // Perfil
    $router->get('/profile', [ProfileController::class, 'show']);
    $router->get('/profile/edit', [ProfileController::class, 'edit']);
    $router->put('/profile', [ProfileController::class, 'update']);
    $router->post('/profile/avatar', [ProfileController::class, 'updateAvatar']);
    $router->put('/profile/password', [ProfileController::class, 'updatePassword']);
    
    // Cursos
    $router->get('/courses', [CourseController::class, 'index']);
    $router->get('/courses/{slug}', [CourseController::class, 'show']);
    $router->post('/courses/{id}/enroll', [CourseController::class, 'enroll']);
    $router->get('/learn/{slug}', [CourseController::class, 'learn']);
    $router->get('/learn/{slug}/lesson/{lessonId}', [CourseController::class, 'lesson']);
    $router->post('/lessons/{id}/complete', [CourseController::class, 'completeLesson']);
    
    // Conquistas
    $router->get('/achievements', [AchievementController::class, 'index']);
    
    // Leaderboard
    $router->get('/leaderboard', [DashboardController::class, 'leaderboard']);
});
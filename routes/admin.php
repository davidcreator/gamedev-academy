<?php
// routes/admin.php

use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\UserController;
use App\Controllers\Admin\CourseController;
use App\Controllers\Admin\NewsController;
use App\Controllers\Admin\AchievementController;
use App\Controllers\Admin\SettingsController;

$router->group(['prefix' => 'admin', 'middleware' => 'admin'], function ($router) {
    
    // Dashboard
    $router->get('/', [DashboardController::class, 'index']);
    
    // Usuários
    $router->get('/users', [UserController::class, 'index']);
    $router->get('/users/create', [UserController::class, 'create']);
    $router->post('/users', [UserController::class, 'store']);
    $router->get('/users/{id}/edit', [UserController::class, 'edit']);
    $router->put('/users/{id}', [UserController::class, 'update']);
    $router->delete('/users/{id}', [UserController::class, 'destroy']);
    $router->post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus']);
    
    // Cursos
    $router->get('/courses', [CourseController::class, 'index']);
    $router->get('/courses/create', [CourseController::class, 'create']);
    $router->post('/courses', [CourseController::class, 'store']);
    $router->get('/courses/{id}/edit', [CourseController::class, 'edit']);
    $router->put('/courses/{id}', [CourseController::class, 'update']);
    $router->delete('/courses/{id}', [CourseController::class, 'destroy']);
    $router->post('/courses/{id}/toggle-publish', [CourseController::class, 'togglePublish']);
    
    // Módulos e Lições
    $router->get('/courses/{id}/modules', [CourseController::class, 'modules']);
    $router->post('/courses/{id}/modules', [CourseController::class, 'storeModule']);
    $router->put('/modules/{id}', [CourseController::class, 'updateModule']);
    $router->delete('/modules/{id}', [CourseController::class, 'destroyModule']);
    
    $router->get('/modules/{id}/lessons', [CourseController::class, 'lessons']);
    $router->post('/modules/{id}/lessons', [CourseController::class, 'storeLesson']);
    $router->put('/lessons/{id}', [CourseController::class, 'updateLesson']);
    $router->delete('/lessons/{id}', [CourseController::class, 'destroyLesson']);
    
    // Notícias
    $router->get('/news', [NewsController::class, 'index']);
    $router->get('/news/create', [NewsController::class, 'create']);
    $router->post('/news', [NewsController::class, 'store']);
    $router->get('/news/{id}/edit', [NewsController::class, 'edit']);
    $router->put('/news/{id}', [NewsController::class, 'update']);
    $router->delete('/news/{id}', [NewsController::class, 'destroy']);
    
    // Conquistas
    $router->get('/achievements', [AchievementController::class, 'index']);
    $router->post('/achievements', [AchievementController::class, 'store']);
    $router->put('/achievements/{id}', [AchievementController::class, 'update']);
    $router->delete('/achievements/{id}', [AchievementController::class, 'destroy']);
    
    // Configurações
    $router->get('/settings', [SettingsController::class, 'index']);
    $router->post('/settings', [SettingsController::class, 'update']);
});
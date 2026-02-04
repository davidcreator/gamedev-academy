<?php
// user/includes/header.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Auth.php';
require_once __DIR__ . '/../../classes/Course.php';
require_once __DIR__ . '/../../classes/Gamification.php';
require_once __DIR__ . '/../../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$currentUser = $auth->getCurrentUser();
$userModel = new User();
$gamification = new Gamification();

$stats = $userModel->getStats($currentUser['id']);
$currentLevel = $userModel->getLevel($currentUser['xp_total']);
$progressToNext = $gamification->getProgressToNextLevel($currentUser['id']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard' ?> - <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/user.css') ?>">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="<?= url() ?>" class="navbar-brand">
                <span class="logo-icon">🎮</span>
                <span>GameDev Academy</span>
            </a>
            
            <ul class="navbar-nav hide-mobile">
                <li><a href="<?= url() ?>" class="nav-link">Site</a></li>
                <li><a href="<?= url('courses.php') ?>" class="nav-link">Cursos</a></li>
                <li><a href="<?= url('news.php') ?>" class="nav-link">Novidades</a></li>
            </ul>
            
            <div class="navbar-actions">
                <div class="xp-display hide-mobile">
                    ⚡ <?= number_format($currentUser['xp_total']) ?> XP
                </div>
                <div class="streak-display hide-mobile">
                    🔥 <?= $currentUser['streak_days'] ?> dias
                </div>
                <div class="coins-display hide-mobile">
                    🪙 <?= number_format($currentUser['coins']) ?>
                </div>
                
                <div class="navbar-user">
                    <img src="<?= getAvatar($currentUser['avatar']) ?>" alt="Avatar" class="avatar">
                </div>
            </div>
        </div>
    </nav>

    <div class="dashboard-layout">
        <!-- Sidebar -->
        <?php include __DIR__ . '/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="main-content"></main>
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
    <div class="user-layout">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <main class="user-main">
            <div class="user-header">
                <h1><?= $pageTitle ?? 'Dashboard' ?></h1>
                <div class="d-flex align-center gap-2">
                    <span>XP: <?= number_format($currentUser['xp_total']) ?></span>
                    <span>🔥 <?= $currentUser['streak_days'] ?> dias</span>
                    <span>🪙 <?= number_format($currentUser['coins']) ?></span>
                    <a href="<?= url() ?>" class="btn btn-sm btn-outline">Ver Site</a>
                    <a href="<?= url('logout.php') ?>" class="btn btn-sm btn-secondary">Sair</a>
                </div>
            </div>

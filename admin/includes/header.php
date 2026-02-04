<?php
// admin/includes/header.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Auth.php';
require_once __DIR__ . '/../../classes/Course.php';
require_once __DIR__ . '/../../classes/News.php';
require_once __DIR__ . '/../../includes/functions.php';

$auth = new Auth();
$auth->requireAdmin();

$currentUser = $auth->getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin' ?> - <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <?php include __DIR__ . '/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1><?= $pageTitle ?? 'Dashboard' ?></h1>
                <div class="d-flex align-center gap-2">
                    <span>Olá, <?= escape($currentUser['full_name']) ?></span>
                    <a href="<?= url() ?>" class="btn btn-sm btn-outline">Ver Site</a>
                    <a href="<?= url('logout.php') ?>" class="btn btn-sm btn-secondary">Sair</a>
                </div>
            </div>
            
            <div class="admin-content"></div>
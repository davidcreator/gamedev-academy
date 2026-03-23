<?php
// admin/includes/header.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Auth.php';
require_once __DIR__ . '/../../classes/Course.php';
require_once __DIR__ . '/../../classes/News.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/editorjs-loader.php';

$auth = new Auth();
$auth->requireLogin();

$currentUser = $auth->getCurrentUser();
$GLOBALS['currentUser'] = $currentUser;
$allowedRoles = ['admin', 'super_admin', 'instructor'];

if (!$currentUser || !in_array($currentUser['role'] ?? '', $allowedRoles, true)) {
    header('Location: ' . SITE_URL . '/user/');
    exit;
}

$adminInstructorAllowedPages = [
    '/admin/courses/courses.php',
    '/admin/courses/course-edit.php',
    '/admin/modules/modules.php',
    '/admin/lessons/lessons.php',
    '/admin/lessons/lessons-create.php',
    '/admin/lessons/lessons-edit.php',
    '/admin/lessons/lessons-list.php',
    '/admin/lessons/lessons-delete.php',
];

if (!function_exists('adminCurrentUser')) {
    function adminCurrentUser(): array
    {
        return $GLOBALS['currentUser'] ?? [];
    }

    function adminUserIsAdmin(): bool
    {
        $user = adminCurrentUser();
        return in_array($user['role'] ?? '', ['admin', 'super_admin'], true);
    }

    function adminUserIsInstructor(): bool
    {
        return (adminCurrentUser()['role'] ?? '') === 'instructor';
    }

    function adminCanManageCourse(?array $course): bool
    {
        if (!$course) {
            return false;
        }

        if (adminUserIsAdmin()) {
            return true;
        }

        return (int) ($course['instructor_id'] ?? 0) === (int) (adminCurrentUser()['id'] ?? 0);
    }

    function adminRequireCourseAccess(?array $course, string $redirect = 'admin/courses/courses.php'): void
    {
        if (!adminCanManageCourse($course)) {
            flash('error', 'Seu perfil pode gerenciar apenas os seus proprios cursos.');
            redirect(url($redirect));
        }
    }
}

$currentScript = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
if (adminUserIsInstructor()) {
    $hasAccess = false;

    foreach ($adminInstructorAllowedPages as $page) {
        if (substr($currentScript, -strlen($page)) === $page) {
            $hasAccess = true;
            break;
        }
    }

    if (!$hasAccess) {
        flash('error', 'Seu perfil tem acesso restrito ao gerenciamento de conteudo.');
        redirect(url('admin/courses/courses.php'));
    }
}

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
<?php
    if (class_exists('EditorJSLoader')) {
        EditorJSLoader::renderStyles();
    }
?>
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
                    <span>Olá, <?= escape($currentUser['name']) ?></span>
                    <a href="<?= url() ?>" class="btn btn-sm btn-outline">Ver Site</a>
                    <a href="<?= url('logout.php') ?>" class="btn btn-sm btn-secondary">Sair</a>
                </div>
            </div>
            
            <div class="admin-content"></div>

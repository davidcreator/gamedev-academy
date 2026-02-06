<?php
// course.php - Página Pública de Curso (detalhes + matrícula)

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Course.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/includes/functions.php';

$auth = new Auth();
$courseModel = new Course();
$db = Database::getInstance();

$slug = trim($_GET['slug'] ?? '');
$course = $courseModel->findBySlug($slug);

if (!$course || !$course['is_published']) {
    http_response_code(404);
    echo "Curso não encontrado.";
    exit;
}

// Matrícula
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'enroll') {
    if ($auth->isLoggedIn()) {
        $user = $auth->getCurrentUser();
        if ($courseModel->enroll($user['id'], $course['id'])) {
            flash('success', 'Matrícula realizada com sucesso!');
        } else {
            flash('info', 'Você já está matriculado neste curso.');
        }
        redirect(url('learn.php?course=' . $course['slug']));
    } else {
        flash('error', 'Faça login para se matricular no curso.');
        redirect(url('login.php'));
    }
}

// Módulos do curso
$modules = $courseModel->getModules($course['id']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape($course['title']) ?> - <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <section class="section">
        <div class="container">
            <?= showFlashMessages() ?>
            
            <div class="section-header">
                <div class="section-title">
                    <span>📚</span>
                    <span><?= escape($course['title']) ?></span>
                </div>
                <div>
                    <?php if ($auth->isLoggedIn()): ?>
                        <?php 
                        $isEnrolled = $courseModel->isEnrolled($auth->getCurrentUser()['id'], $course['id']);
                        if ($isEnrolled): ?>
                            <a href="<?= url('learn.php?course=' . $course['slug']) ?>" class="btn btn-primary">Continuar Curso</a>
                        <?php else: ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="enroll">
                                <button type="submit" class="btn btn-primary">Matricular-se</button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="<?= url('login.php') ?>" class="btn btn-primary">Entrar para Matricular</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="course-thumbnail">
                    <img src="<?= escape($course['cover_image'] ?? $course['thumbnail'] ?? asset('images/default.png')) ?>" alt="">
                    <?php if (!empty($course['is_free'])): ?>
                        <div class="course-badge">
                            <span class="course-free-badge">GRÁTIS</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="course-content">
                    <div class="course-category"><?= escape($course['category_name'] ?? 'Geral') ?></div>
                    <h3 class="course-title"><?= escape($course['title']) ?></h3>
                    <p class="course-description"><?= escape($course['description'] ?? '') ?></p>
                    
                    <div class="course-meta">
                        <span>👨‍🏫 <?= escape($course['instructor_name'] ?? 'Instrutor') ?></span>
                        <span>⏱️ <?= $course['estimated_hours'] ?>h</span>
                        <span>⚡ <?= $course['xp_reward'] ?> XP</span>
                        <span>👥 <?= $course['total_students'] ?> alunos</span>
                    </div>
                </div>
            </div>
            
            <h3 class="mt-4">Grade Curricular</h3>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Módulo</th>
                            <th>Lições</th>
                            <th>XP</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($modules as $m): ?>
                        <tr>
                            <td><?= intval($m['order_index']) ?></td>
                            <td><?= escape($m['title']) ?></td>
                            <td><?= intval($m['total_lessons'] ?? 0) ?></td>
                            <td><?= intval($m['xp_reward']) ?></td>
                            <td><span class="badge <?= $m['is_published'] ? 'badge-success' : 'badge-warning' ?>"><?= $m['is_published'] ? 'Publicado' : 'Rascunho' ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

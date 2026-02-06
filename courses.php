<?php
// courses.php - Listagem Pública de Cursos

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Course.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/includes/functions.php';

$auth = new Auth();
$courseModel = new Course();

$courses = $courseModel->getAll(true, 100);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cursos - <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <section class="section">
        <div class="container">
            <div class="section-header">
                <div class="section-title">
                    <span>📚</span>
                    <span>Todos os Cursos</span>
                </div>
            </div>
            
            <div class="courses-grid">
                <?php foreach ($courses as $course): ?>
                <a class="course-card" href="<?= url('course.php?slug=' . $course['slug']) ?>">
                    <div class="course-thumbnail">
                        <img src="<?= escape($course['thumbnail'] ?? asset('images/default.png')) ?>" alt="">
                        <?php if (!empty($course['is_free'])): ?>
                            <div class="course-badge">
                                <span class="course-free-badge">GRÁTIS</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="course-content">
                        <div class="course-category"><?= escape($course['category_name'] ?? 'Geral') ?></div>
                        <h3 class="course-title"><?= escape($course['title']) ?></h3>
                        <p class="course-description"><?= escape(truncate($course['short_description'] ?: ($course['description'] ?? ''), 100)) ?></p>
                        <div class="course-meta">
                            <span>📚 <?= $course['total_modules'] ?? 0 ?> módulos</span>
                            <span>⏱️ <?= $course['estimated_hours'] ?>h</span>
                            <span class="course-xp">⚡ <?= $course['xp_reward'] ?> XP</span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

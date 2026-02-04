<?php
// user/index.php - Dashboard do Usuário

$pageTitle = 'Dashboard';
include 'includes/header.php';

$courseModel = new Course();
$userCourses = $courseModel->getUserCourses($currentUser['id']);
$xpHistory = $gamification->getXPHistory($currentUser['id'], 5);
$achievements = $gamification->getUserAchievements($currentUser['id']);
$unlockedCount = count(array_filter($achievements, fn($a) => $a['unlocked_at'] !== null));
?>

<div class="d-flex justify-between align-center mb-4">
    <div>
        <h1>Olá, <?= escape($currentUser['full_name']) ?>! 👋</h1>
        <p class="text-muted">Continue sua jornada de aprendizado</p>
    </div>
    <a href="<?= url('courses.php') ?>" class="btn btn-primary">
        📚 Explorar Cursos
    </a>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">⚡</div>
        <div class="stat-value"><?= number_format($stats['xp_total']) ?></div>
        <div class="stat-label">XP Total</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">🎓</div>
        <div class="stat-value"><?= $stats['courses_completed'] ?>/<?= $stats['courses_enrolled'] ?></div>
        <div class="stat-label">Cursos Concluídos</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">📖</div>
        <div class="stat-value"><?= $stats['lessons_completed'] ?></div>
        <div class="stat-label">Lições Completadas</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">🔥</div>
        <div class="stat-value"><?= $stats['streak_days'] ?></div>
        <div class="stat-label">Dias de Streak</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">🏆</div>
        <div class="stat-value"><?= $unlockedCount ?></div>
        <div class="stat-label">Conquistas</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">🪙</div>
        <div class="stat-value"><?= number_format($stats['coins']) ?></div>
        <div class="stat-label">Moedas</div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Cursos em Andamento -->
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">📚 Cursos em Andamento</h3>
            <a href="<?= url('user/courses.php') ?>">Ver todos</a>
        </div>
        <div class="widget-body">
            <?php if (empty($userCourses)): ?>
                <div class="text-center" style="padding: 2rem; color: var(--gray-500);">
                    <p style="font-size: 3rem; margin-bottom: 1rem;">📚</p>
                    <p>Você ainda não começou nenhum curso.</p>
                    <a href="<?= url('courses.php') ?>" class="btn btn-primary btn-sm mt-2">
                        Explorar Cursos
                    </a>
                </div>
            <?php else: ?>
                <?php foreach (array_slice($userCourses, 0, 3) as $course): ?>
                <a href="<?= url('course.php?slug=' . $course['slug']) ?>" class="course-progress-card">
                    <div class="course-progress-thumb">🎮</div>
                    <div class="course-progress-info">
                        <div class="course-progress-title"><?= escape($course['title']) ?></div>
                        <div class="progress">
                            <div class="progress-bar" style="width: <?= $course['progress_percentage'] ?>%"></div>
                        </div>
                        <div class="course-progress-meta">
                            <span class="text-muted"><?= escape($course['category_name'] ?? '') ?></span>
                            <span class="course-progress-percent"><?= round($course['progress_percentage']) ?>%</span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Atividade Recente -->
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">⚡ Atividade Recente</h3>
        </div>
        <div class="widget-body">
            <?php if (empty($xpHistory)): ?>
                <p class="text-muted text-center">Nenhuma atividade ainda</p>
            <?php else: ?>
                <?php foreach ($xpHistory as $activity): ?>
                <div class="activity-item">
                    <div class="activity-icon">
                        <?php 
                        $icon = match($activity['action_type']) {
                            'lesson_complete' => '📖',
                            'course_complete' => '🎓',
                            'achievement' => '🏆',
                            'streak' => '🔥',
                            default => '⚡'
                        };
                        echo $icon;
                        ?>
                    </div>
                    <div class="activity-content">
                        <div class="activity-text">
                            <?= escape($activity['description'] ?: $activity['action_type']) ?>
                            <span class="activity-xp">+<?= $activity['xp_amount'] ?> XP</span>
                        </div>
                        <div class="activity-time"><?= timeAgo($activity['created_at']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Conquistas Recentes -->
<div class="widget mt-4">
    <div class="widget-header">
        <h3 class="widget-title">🏆 Conquistas</h3>
        <a href="<?= url('user/achievements.php') ?>">Ver todas</a>
    </div>
    <div class="widget-body">
        <div class="achievements-preview">
            <?php foreach (array_slice($achievements, 0, 6) as $achievement): ?>
            <div class="achievement-preview-item <?= $achievement['unlocked_at'] ? '' : 'locked' ?>" 
                 title="<?= escape($achievement['name']) ?>">
                <div class="achievement-preview-icon"><?= $achievement['icon'] ?></div>
                <div class="achievement-preview-name"><?= escape($achievement['name']) ?></div>
            </div>
            <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
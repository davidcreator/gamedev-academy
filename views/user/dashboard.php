<?php
// views/user/dashboard.php
?>

<div class="welcome-banner">
    <h2>Olá, <?= $user['full_name'] ?>! 👋</h2>
    <p>Continue sua jornada de aprendizado</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">⚡</div>
        <div class="stat-value"><?= number_format($stats['xp']) ?></div>
        <div class="stat-label">XP Total</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">🎯</div>
        <div class="stat-value"><?= $stats['level'] ?></div>
        <div class="stat-label">Nível</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">🔥</div>
        <div class="stat-value"><?= $stats['streak'] ?></div>
        <div class="stat-label">Dias de Streak</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">🪙</div>
        <div class="stat-value"><?= number_format($stats['coins']) ?></div>
        <div class="stat-label">Moedas</div>
    </div>
</div>

<div class="user-panel">
    <h3>Cursos em Andamento</h3>
    
    <?php if (empty($courses)): ?>
        <div class="empty-state">
            <p>Você ainda não está matriculado em nenhum curso.</p>
            <a href="<?= url('courses') ?>" class="btn btn-primary">Explorar Cursos</a>
        </div>
    <?php else: ?>
        <div class="courses-list">
            <?php foreach ($courses as $course): ?>
            <div class="course-progress-card">
                <h4><?= $course['title'] ?></h4>
                <div class="progress">
                    <div class="progress-bar" style="width: <?= $course['progress_percentage'] ?>%"></div>
                </div>
                <span><?= $course['progress_percentage'] ?>% completo</span>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
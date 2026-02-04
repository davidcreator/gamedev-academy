<?php
// user/includes/sidebar.php

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <img src="<?= getAvatar($currentUser['avatar']) ?>" alt="Avatar" class="sidebar-avatar">
        <div class="sidebar-username"><?= escape($currentUser['username']) ?></div>
        <div class="sidebar-level">
            <?= $currentLevel['badge_icon'] ?> Nível <?= $currentLevel['level_number'] ?> - <?= escape($currentLevel['title']) ?>
        </div>
        
        <div class="sidebar-xp-bar">
            <div class="sidebar-xp-info">
                <span><?= number_format($currentUser['xp_total']) ?> XP</span>
                <?php if (!$progressToNext['is_max_level']): ?>
                    <span><?= number_format($progressToNext['next_level_xp']) ?> XP</span>
                <?php else: ?>
                    <span>MAX</span>
                <?php endif; ?>
            </div>
            <div class="progress">
                <div class="progress-bar" style="width: <?= $progressToNext['progress'] ?>%"></div>
            </div>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <a href="<?= url('user/') ?>" class="sidebar-nav-item <?= $currentPage === 'index' ? 'active' : '' ?>">
            <span class="sidebar-nav-icon">📊</span>
            <span>Dashboard</span>
        </a>
        
        <a href="<?= url('user/courses.php') ?>" class="sidebar-nav-item <?= $currentPage === 'courses' ? 'active' : '' ?>">
            <span class="sidebar-nav-icon">📚</span>
            <span>Meus Cursos</span>
        </a>
        
        <a href="<?= url('user/achievements.php') ?>" class="sidebar-nav-item <?= $currentPage === 'achievements' ? 'active' : '' ?>">
            <span class="sidebar-nav-icon">🏆</span>
            <span>Conquistas</span>
        </a>
        
        <a href="<?= url('user/leaderboard.php') ?>" class="sidebar-nav-item <?= $currentPage === 'leaderboard' ? 'active' : '' ?>">
            <span class="sidebar-nav-icon">🎖️</span>
            <span>Ranking</span>
        </a>
        
        <div class="sidebar-nav-divider"></div>
        
        <a href="<?= url('user/profile.php') ?>" class="sidebar-nav-item <?= $currentPage === 'profile' ? 'active' : '' ?>">
            <span class="sidebar-nav-icon">👤</span>
            <span>Meu Perfil</span>
        </a>
        
        <a href="<?= url('user/settings.php') ?>" class="sidebar-nav-item <?= $currentPage === 'settings' ? 'active' : '' ?>">
            <span class="sidebar-nav-icon">⚙️</span>
            <span>Configurações</span>
        </a>
        
        <div class="sidebar-nav-divider"></div>
        
        <a href="<?= url('logout.php') ?>" class="sidebar-nav-item">
            <span class="sidebar-nav-icon">🚪</span>
            <span>Sair</span>
        </a>
    </nav>
</aside>
<?php
// admin/includes/sidebar.php
$currentSection = basename(dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$isInstructorSidebar = function_exists('adminUserIsInstructor') && adminUserIsInstructor();
?>
<aside class="admin-sidebar">
    <div class="admin-sidebar-header">
        <div class="admin-sidebar-brand">
            <span>🎮</span>
            <span>Admin Panel</span>
        </div>
    </div>

    <nav class="admin-nav">
        <a href="<?= url('admin/courses/courses.php') ?>" class="admin-nav-item <?= $currentPage === 'courses' ? 'active' : '' ?>">
            <span>📚</span>
            <span>Cursos</span>
        </a>

        <?php if (!$isInstructorSidebar): ?>
            <a href="<?= url('admin/') ?>" class="admin-nav-item <?= $currentPage === 'index' ? 'active' : '' ?>">
                <span>📊</span>
                <span>Dashboard</span>
            </a>

            <a href="<?= url('admin/users/users.php') ?>" class="admin-nav-item <?= $currentPage === 'users' ? 'active' : '' ?>">
                <span>👥</span>
                <span>Usuários</span>
            </a>

            <a href="<?= url('admin/finance/index.php') ?>" class="admin-nav-item <?= $currentSection === 'finance' ? 'active' : '' ?>">
                <span>💰</span>
                <span>Financeiro</span>
            </a>

            <a href="<?= url('admin/news/news.php') ?>" class="admin-nav-item <?= $currentPage === 'news' ? 'active' : '' ?>">
                <span>📰</span>
                <span>Notícias</span>
            </a>

            <a href="<?= url('admin/archievements/achievements.php') ?>" class="admin-nav-item <?= $currentPage === 'achievements' ? 'active' : '' ?>">
                <span>🏆</span>
                <span>Conquistas</span>
            </a>

            <a href="<?= url('admin/levels/levels.php') ?>" class="admin-nav-item <?= $currentPage === 'levels' ? 'active' : '' ?>">
                <span>🎯</span>
                <span>Níveis</span>
            </a>

            <a href="<?= url('admin/settings/settings.php') ?>" class="admin-nav-item <?= $currentPage === 'settings' ? 'active' : '' ?>">
                <span>⚙️</span>
                <span>Configurações</span>
            </a>
        <?php endif; ?>
    </nav>
</aside>

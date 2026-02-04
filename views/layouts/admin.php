<?php
// views/layouts/admin.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin' ?> - GameDev Academy</title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body class="admin-layout">
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h3>🎮 Admin Panel</h3>
            </div>
            
            <nav class="sidebar-nav">
                <a href="<?= url('admin') ?>" class="nav-item">
                    📊 Dashboard
                </a>
                <a href="<?= url('admin/users') ?>" class="nav-item">
                    👥 Usuários
                </a>
                <a href="<?= url('admin/courses') ?>" class="nav-item">
                    📚 Cursos
                </a>
                <a href="<?= url('admin/news') ?>" class="nav-item">
                    📰 Notícias
                </a>
                <hr>
                <a href="<?= url('/') ?>" class="nav-item">
                    🏠 Ver Site
                </a>
                <a href="<?= url('logout') ?>" class="nav-item">
                    🚪 Sair
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <h1><?= $title ?? 'Dashboard' ?></h1>
                <div class="admin-user">
                    Olá, <?= $user['full_name'] ?>
                </div>
            </header>
            
            <div class="admin-content">
                <?php require $content; ?>
            </div>
        </main>
    </div>
</body>
</html>
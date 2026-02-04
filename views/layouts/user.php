<?php
// views/layouts/user.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard' ?> - GameDev Academy</title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body class="user-layout">
    <div class="user-container">
        <!-- Sidebar -->
        <aside class="user-sidebar">
            <div class="sidebar-header">
                <img src="<?= url('assets/images/default-avatar.png') ?>" alt="Avatar" class="avatar">
                <h3><?= $user['username'] ?></h3>
                <p>Nível <?= $user['level'] ?> • <?= number_format($user['xp_total']) ?> XP</p>
                
                <div class="progress">
                    <div class="progress-bar" style="width: 60%"></div>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="<?= url('user') ?>" class="nav-item">
                    📊 Dashboard
                </a>
                <a href="<?= url('user/courses') ?>" class="nav-item">
                    📚 Meus Cursos
                </a>
                <a href="<?= url('user/achievements') ?>" class="nav-item">
                    🏆 Conquistas
                </a>
                <a href="<?= url('user/profile') ?>" class="nav-item">
                    👤 Perfil
                </a>
                <hr>
                <a href="<?= url('/') ?>" class="nav-item">
                    🏠 Ir para o Site
                </a>
                <a href="<?= url('logout') ?>" class="nav-item">
                    🚪 Sair
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="user-main">
            <header class="user-header">
                <h1><?= $title ?? 'Dashboard' ?></h1>
                <div class="user-stats">
                    <span>🔥 <?= $user['streak_days'] ?> dias</span>
                    <span>🪙 <?= number_format($user['coins']) ?> moedas</span>
                </div>
            </header>
            
            <div class="user-content">
                <?php require $content; ?>
            </div>
        </main>
    </div>
</body>
</html>
<?php
// views/layouts/main.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'GameDev Academy' ?></title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <a href="<?= url('/') ?>" class="navbar-brand">
                <span>🎮</span> GameDev Academy
            </a>
            
            <ul class="navbar-nav">
                <li><a href="<?= url('/') ?>">Início</a></li>
                <li><a href="<?= url('courses') ?>">Cursos</a></li>
                <li><a href="<?= url('news') ?>">Notícias</a></li>
            </ul>
            
            <div class="navbar-actions">
                <?php if ($auth->isLoggedIn()): ?>
                    <div class="navbar-user">
                        <span><?= $user['username'] ?></span>
                        <a href="<?= $auth->isAdmin() ? url('admin') : url('user') ?>" class="btn btn-sm">
                            Painel
                        </a>
                        <a href="<?= url('logout') ?>" class="btn btn-sm btn-secondary">
                            Sair
                        </a>
                    </div>
                <?php else: ?>
                    <a href="<?= url('login') ?>" class="btn btn-secondary">Entrar</a>
                    <a href="<?= url('register') ?>" class="btn btn-primary">Criar Conta</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <!-- Content -->
    <?php require $content; ?>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> GameDev Academy. Todos os direitos reservados.</p>
        </div>
    </footer>
    
    <script src="<?= url('assets/js/main.js') ?>"></script>
</body>
</html>
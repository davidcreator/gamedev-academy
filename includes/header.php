<?php
// includes/header.php - Header padrão do site

// Verificar se as classes necessárias foram carregadas
if (!class_exists('Auth')) {
    require_once __DIR__ . '/../classes/Auth.php';
}

// Inicializar Auth se ainda não foi inicializado
if (!isset($auth)) {
    $auth = new Auth();
}

// Obter informações do usuário logado
$currentUser = $auth->isLoggedIn() ? $auth->getCurrentUser() : null;

// Definir título padrão se não foi definido
if (!isset($pageTitle)) {
    $pageTitle = SITE_NAME . ' - Aprenda Desenvolvimento de Jogos';
}

// Definir descrição padrão se não foi definida
if (!isset($pageDescription)) {
    $pageDescription = 'Plataforma completa para aprender desenvolvimento de jogos do zero ao avançado';
}

// Definir página ativa para navegação
if (!isset($activePage)) {
    $activePage = basename($_SERVER['PHP_SELF'], '.php');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    
    <!-- Preconnect para otimização -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS Principal -->
    <link rel="stylesheet" href="<?= asset('/../assets/css/admin.css') ?>">
    <link rel="stylesheet" href="<?= asset('/../assets/css/main.css') ?>">
    <link rel="stylesheet" href="<?= asset('/../assets/css/auth.css') ?>">
    <link rel="stylesheet" href="<?= asset('/../assets/css/email.css') ?>">
    <link rel="stylesheet" href="<?= asset('/../assets/css/user.css') ?>">
    
    <!-- CSS Adicional para páginas específicas -->
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?= asset($css) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= asset('img/favicon.ico') ?>">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta property="og:image" content="<?= url('assets/img/og-image.jpg') ?>">
    <meta property="og:url" content="<?= url($_SERVER['REQUEST_URI']) ?>">
    <meta property="og:type" content="website">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta name="twitter:image" content="<?= url('assets/img/og-image.jpg') ?>">
</head>
<body<?= isset($bodyClass) ? ' class="' . htmlspecialchars($bodyClass) . '"' : '' ?>>
    
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <!-- Logo/Brand -->
            <a href="<?= url() ?>" class="navbar-brand">
                <span class="logo-icon">🎮</span>
                <span>GameDev Academy</span>
            </a>
            
            <!-- Mobile Menu Toggle -->
            <button class="navbar-toggle" onclick="toggleMobileMenu()" aria-label="Menu">
                <span class="navbar-toggle-icon">☰</span>
            </button>
            
            <!-- Navigation Links -->
            <ul class="navbar-nav" id="navbarNav">
                <li>
                    <a href="<?= url() ?>" class="nav-link<?= $activePage === 'index' ? ' active' : '' ?>">
                        Início
                    </a>
                </li>
                <li>
                    <a href="<?= url('courses.php') ?>" class="nav-link<?= $activePage === 'courses' ? ' active' : '' ?>">
                        Cursos
                    </a>
                </li>
                <li>
                    <a href="<?= url('news.php') ?>" class="nav-link<?= $activePage === 'news' ? ' active' : '' ?>">
                        Novidades
                    </a>
                </li>
                <li>
                    <a href="<?= url('leaderboard.php') ?>" class="nav-link<?= $activePage === 'leaderboard' ? ' active' : '' ?>">
                        Ranking
                    </a>
                </li>
                <?php if ($auth->isLoggedIn()): ?>
                    <li class="nav-mobile-only">
                        <a href="<?= url('user/dashboard.php') ?>" class="nav-link">
                            Meu Perfil
                        </a>
                    </li>
                    <?php if ($auth->isAdmin()): ?>
                        <li class="nav-mobile-only">
                            <a href="<?= url('admin/') ?>" class="nav-link">
                                Admin
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-mobile-only">
                        <a href="<?= url('logout.php') ?>" class="nav-link">
                            Sair
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <!-- User Actions -->
            <div class="navbar-actions">
                <?php if ($auth->isLoggedIn()): ?>
                    <!-- User Dropdown -->
                    <div class="navbar-user-dropdown">
                        <a href="#" class="navbar-user" onclick="toggleUserMenu(event)">
                            <div class="navbar-user-info hide-mobile">
                                <div class="navbar-user-name">
                                    <?= escape($currentUser['username'] ?? 'Usuário') ?>
                                </div>
                                <div class="navbar-user-level">
                                    Nível <?= (int)($currentUser['level'] ?? 1) ?> • 
                                    <?= number_format((int)($currentUser['xp_total'] ?? 0)) ?> XP
                                </div>
                            </div>
                            <img src="<?= getAvatar($currentUser['avatar'] ?? 'default.png') ?>" 
                                 alt="Avatar" 
                                 class="avatar">
                        </a>
                        
                        <!-- Dropdown Menu -->
                        <div class="user-dropdown-menu" id="userDropdownMenu">
                            <div class="dropdown-header">
                                <strong><?= escape($currentUser['username'] ?? 'Usuário') ?></strong>
                                <small><?= escape($currentUser['email'] ?? '') ?></small>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="<?= url('user/dashboard.php') ?>" class="dropdown-item">
                                <i class="icon-dashboard"></i> Dashboard
                            </a>
                            <a href="<?= url('user/profile.php') ?>" class="dropdown-item">
                                <i class="icon-user"></i> Meu Perfil
                            </a>
                            <a href="<?= url('user/courses.php') ?>" class="dropdown-item">
                                <i class="icon-book"></i> Meus Cursos
                            </a>
                            <a href="<?= url('user/achievements.php') ?>" class="dropdown-item">
                                <i class="icon-trophy"></i> Conquistas
                            </a>
                            <?php if ($auth->isAdmin()): ?>
                                <div class="dropdown-divider"></div>
                                <a href="<?= url('admin/') ?>" class="dropdown-item">
                                    <i class="icon-settings"></i> Administração
                                </a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a href="<?= url('logout.php') ?>" class="dropdown-item">
                                <i class="icon-logout"></i> Sair
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Login/Register Buttons -->
                    <a href="<?= url('login.php') ?>" class="btn btn-secondary btn-sm">
                        Entrar
                    </a>
                    <a href="<?= url('register.php') ?>" class="btn btn-primary btn-sm">
                        Criar Conta
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Container -->
    <main class="main-content"></main>
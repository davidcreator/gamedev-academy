<?php
// index.php - Landing Page

require_once 'config/database.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Auth.php';
require_once 'classes/Course.php';
require_once 'classes/News.php';
require_once 'includes/functions.php';

$auth = new Auth();
$courseModel = new Course();
$newsModel = new News();
$userModel = new User();

$featuredCourses = $courseModel->getFeatured(6);
$latestNews = $newsModel->getLatest(3);
$leaderboard = $userModel->getLeaderboard(5);
$totalUsers = $userModel->count();
$totalCourses = $courseModel->count();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> - Aprenda Desenvolvimento de Jogos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="<?= url() ?>" class="navbar-brand">
                <span class="logo-icon">🎮</span>
                <span>GameDev Academy</span>
            </a>
            
            <button class="navbar-toggle" onclick="toggleMobileMenu()">☰</button>
            
            <ul class="navbar-nav" id="navbarNav">
                <li><a href="<?= url() ?>" class="nav-link active">Início</a></li>
                <li><a href="<?= url('courses.php') ?>" class="nav-link">Cursos</a></li>
                <li><a href="<?= url('news.php') ?>" class="nav-link">Novidades</a></li>
                <li><a href="#leaderboard" class="nav-link">Ranking</a></li>
            </ul>
            
            <div class="navbar-actions">
                <?php if ($auth->isLoggedIn()): ?>
                    <?php $user = $auth->getCurrentUser() ?? []; ?>
                    <a href="<?= $auth->isAdmin() ? url('admin/') : url('user/') ?>" class="navbar-user">
                        <div class="navbar-user-info hide-mobile">
                            <div class="navbar-user-name"><?= escape($user['username'] ?? 'Usuário') ?></div>
                            <div class="navbar-user-level">Nível <?= (int)($user['level'] ?? 1) ?> • <?= number_format((int)($user['xp_total'] ?? 0)) ?> XP</div>
                        </div>
                        <img src="<?= getAvatar($user['avatar'] ?? 'default.png') ?>" alt="Avatar" class="avatar">
                    </a>
                <?php else: ?>
                    <a href="<?= url('login.php') ?>" class="btn btn-secondary btn-sm">Entrar</a>
                    <a href="<?= url('register.php') ?>" class="btn btn-primary btn-sm">Criar Conta</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">
                    Aprenda a criar <span class="text-gradient">jogos incríveis</span> com Phaser e React
                </h1>
                <p class="hero-subtitle">
                    Domine o desenvolvimento de jogos 2D de forma prática e gamificada. 
                    Ganhe XP, suba de nível e conquiste badges enquanto aprende!
                </p>
                <div class="hero-actions">
                    <a href="<?= url('register.php') ?>" class="btn btn-primary btn-lg">
                        🚀 Começar Agora - Grátis
                    </a>
                    <a href="<?= url('courses.php') ?>" class="btn btn-outline btn-lg">
                        Ver Cursos
                    </a>
                </div>
                
                <div class="hero-stats">
                    <div class="hero-stat">
                        <div class="hero-stat-value"><?= number_format($totalUsers) ?>+</div>
                        <div class="hero-stat-label">Estudantes</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-value"><?= $totalCourses ?>+</div>
                        <div class="hero-stat-label">Cursos</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-value">100%</div>
                        <div class="hero-stat-label">Gamificado</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="section" style="background: var(--gray-800);">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Por que aprender aqui?</h2>
                <p class="section-subtitle">Uma experiência de aprendizado única e envolvente</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🎮</div>
                    <h3 class="feature-title">Aprendizado Gamificado</h3>
                    <p class="feature-text">
                        Ganhe XP, suba de nível e conquiste badges enquanto aprende. 
                        Transforme seu estudo em uma aventura!
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">💻</div>
                    <h3 class="feature-title">Projetos Práticos</h3>
                    <p class="feature-text">
                        Construa jogos reais do início ao fim. 
                        Aprenda fazendo, não apenas assistindo.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🏆</div>
                    <h3 class="feature-title">Sistema de Ranking</h3>
                    <p class="feature-text">
                        Compete com outros estudantes no ranking semanal. 
                        Mostre sua dedicação e ganhe reconhecimento!
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">⚛️</div>
                    <h3 class="feature-title">Phaser + React</h3>
                    <p class="feature-text">
                        Domine as tecnologias mais modernas para 
                        desenvolvimento de jogos web profissionais.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🔥</div>
                    <h3 class="feature-title">Streak de Estudos</h3>
                    <p class="feature-text">
                        Mantenha sua sequência de estudos diários 
                        e ganhe recompensas especiais!
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🎯</div>
                    <h3 class="feature-title">Conquistas</h3>
                    <p class="feature-text">
                        Desbloqueie conquistas ao atingir marcos importantes 
                        na sua jornada de aprendizado.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Courses Section -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Cursos em Destaque</h2>
                <p class="section-subtitle">Comece sua jornada no desenvolvimento de jogos</p>
            </div>
            
            <div class="courses-grid">
                <?php foreach ($featuredCourses as $course): ?>
                <a href="<?= url('course.php?slug=' . $course['slug']) ?>" class="course-card">
                    <div class="course-thumbnail">
                        <?php if ($course['thumbnail']): ?>
                            <img src="<?= url('uploads/courses/' . $course['thumbnail']) ?>" alt="<?= escape($course['title']) ?>">
                        <?php else: ?>
                            <div style="background: var(--gradient-primary); height: 100%; display: flex; align-items: center; justify-content: center; font-size: 4rem;">
                                🎮
                            </div>
                        <?php endif; ?>
                        <?php if ($course['is_free']): ?>
                            <div class="course-badge">
                                <span class="course-free-badge">GRÁTIS</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="course-content">
                        <div class="course-category"><?= escape($course['category_name'] ?? 'Geral') ?></div>
                        <h3 class="course-title"><?= escape($course['title']) ?></h3>
                        <p class="course-description"><?= escape(truncate($course['description'], 100)) ?></p>
                        <div class="course-meta">
                            <span>📚 <?= $course['total_modules'] ?? 0 ?> módulos</span>
                            <span>⏱️ <?= $course['estimated_hours'] ?>h</span>
                            <span class="course-xp">⚡ <?= $course['xp_reward'] ?> XP</span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="<?= url('courses.php') ?>" class="btn btn-outline">Ver Todos os Cursos →</a>
            </div>
        </div>
    </section>

    <!-- Leaderboard Section -->
    <section class="section" id="leaderboard" style="background: var(--gray-800);">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">🏆 Ranking da Semana</h2>
                <p class="section-subtitle">Os estudantes mais dedicados desta semana</p>
            </div>
            
            <div style="max-width: 600px; margin: 0 auto;">
                <div class="leaderboard">
                    <div class="leaderboard-header">
                        <div class="d-flex align-center justify-between">
                            <span>Top Estudantes</span>
                            <span>XP Semanal</span>
                        </div>
                    </div>
                    <?php foreach ($leaderboard as $index => $player): ?>
                    <div class="leaderboard-item">
                        <div class="leaderboard-rank <?= $index < 3 ? 'top-' . ($index + 1) : '' ?>">
                            <?php if ($index === 0): ?>🥇
                            <?php elseif ($index === 1): ?>🥈
                            <?php elseif ($index === 2): ?>🥉
                            <?php else: ?><?= $index + 1 ?>
                            <?php endif; ?>
                        </div>
                        <div class="leaderboard-user">
                            <img src="<?= getAvatar($player['avatar']) ?>" alt="" class="avatar">
                            <div>
                                <div class="leaderboard-name"><?= escape($player['username']) ?></div>
                                <div class="leaderboard-level">Nível <?= $player['level'] ?></div>
                            </div>
                        </div>
                        <div class="leaderboard-xp">
                            ⚡ <?= number_format($player['xp_total']) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($leaderboard)): ?>
                    <div class="leaderboard-item" style="justify-content: center; color: var(--gray-500);">
                        Nenhum dado disponível ainda
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- News Section -->
    <?php if (!empty($latestNews)): ?>
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">📰 Últimas Novidades</h2>
                <p class="section-subtitle">Fique por dentro das atualizações da plataforma</p>
            </div>
            
            <div class="news-grid">
                <?php foreach ($latestNews as $news): ?>
                <a href="<?= url('news/' . $news['slug']) ?>" class="news-card">
                    <div class="news-thumbnail">
                        <?php if ($news['thumbnail']): ?>
                            <img src="<?= url('uploads/news/' . $news['thumbnail']) ?>" alt="">
                        <?php else: ?>
                            📰
                        <?php endif; ?>
                    </div>
                    <div class="news-content">
                        <span class="news-category"><?= escape($news['category']) ?></span>
                        <h3 class="news-title"><?= escape($news['title']) ?></h3>
                        <p class="news-excerpt"><?= escape(truncate($news['excerpt'], 120)) ?></p>
                        <div class="news-meta">
                            <span><?= escape($news['author_name'] ?? 'Admin') ?></span>
                            <span><?= timeAgo($news['published_at'] ?? $news['created_at']) ?></span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CTA Section -->
    <section class="section" style="background: var(--gradient-primary);">
        <div class="container">
            <div style="text-align: center; padding: 2rem 0;">
                <h2 style="font-size: 2.5rem; margin-bottom: 1rem;">
                    Pronto para começar sua jornada?
                </h2>
                <p style="font-size: 1.25rem; margin-bottom: 2rem; opacity: 0.9;">
                    Junte-se a milhares de estudantes e comece a criar jogos incríveis hoje!
                </p>
                <a href="<?= url('register.php') ?>" class="btn btn-lg" style="background: white; color: var(--primary);">
                    🚀 Criar Conta Gratuita
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <div class="footer-brand">
                        <span>🎮</span> GameDev Academy
                    </div>
                    <p class="footer-description">
                        A melhor plataforma para aprender desenvolvimento de jogos 
                        com Phaser e React de forma prática e gamificada.
                    </p>
                    <div class="footer-social">
                        <a href="#" title="GitHub">📦</a>
                        <a href="#" title="Discord">💬</a>
                        <a href="#" title="YouTube">📺</a>
                        <a href="#" title="Twitter">🐦</a>
                    </div>
                </div>
                
                <div>
                    <h4 class="footer-title">Plataforma</h4>
                    <ul class="footer-links">
                        <li><a href="<?= url('courses.php') ?>">Cursos</a></li>
                        <li><a href="<?= url('news.php') ?>">Novidades</a></li>
                        <li><a href="#">Roadmap</a></li>
                        <li><a href="#">Comunidade</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="footer-title">Recursos</h4>
                    <ul class="footer-links">
                        <li><a href="#">Documentação</a></li>
                        <li><a href="#">Tutoriais</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="footer-title">Suporte</h4>
                    <ul class="footer-links">
                        <li><a href="#">Contato</a></li>
                        <li><a href="#">Termos de Uso</a></li>
                        <li><a href="#">Privacidade</a></li>
                        <li><a href="#">Ajuda</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> GameDev Academy. Todos os direitos reservados.</p>
                <p>Feito com ❤️ para desenvolvedores de jogos</p>
            </div>
        </div>
    </footer>

    <script>
    function toggleMobileMenu() {
        document.getElementById('navbarNav').classList.toggle('active');
    }
    </script>
</body>
</html>

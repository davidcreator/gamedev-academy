<?php
/**
 * GameDev Academy - Página Inicial
 * 
 * @version 2.0.0
 * @author GameDev Academy Team
 */

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Carregar configurações
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Verificar conexão com banco de dados
try {
    $pdo = getDBConnection();
} catch (Exception $e) {
    // Redirecionar para instalação se não houver conexão
    header('Location: /install/');
    exit;
}

// Inicializar variáveis do usuário com valores padrão
$isLoggedIn = false;
$user = null;
$userLevel = 1;
$userXP = 0;
$userCoins = 0;
$userStreak = 0;
$userAvatar = 'default.png';
$userName = 'Visitante';
$nextLevelXP = 100;
$xpProgress = 0;
$levelInfo = null;

// Verificar se usuário está logado
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT u.*, l.title as level_title, l.badge_icon, l.color as level_color
            FROM users u
            LEFT JOIN levels l ON u.level = l.level_number
            WHERE u.id = ? AND u.is_active = 1
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $isLoggedIn = true;
            $userLevel = $user['level'] ?? 1;
            $userXP = $user['xp_total'] ?? 0;
            $userCoins = $user['coins'] ?? 0;
            $userStreak = $user['streak_days'] ?? 0;
            $userAvatar = $user['avatar'] ?? 'default.png';
            $userName = $user['full_name'] ?? $user['username'] ?? 'Usuário';
            
            // Calcular progresso para próximo nível
            $stmtNext = $pdo->prepare("
                SELECT xp_required 
                FROM levels 
                WHERE level_number = ? 
                LIMIT 1
            ");
            $stmtNext->execute([$userLevel + 1]);
            $nextLevel = $stmtNext->fetch(PDO::FETCH_ASSOC);
            
            if ($nextLevel) {
                $nextLevelXP = $nextLevel['xp_required'];
                
                // XP do nível atual
                $stmtCurrent = $pdo->prepare("
                    SELECT xp_required 
                    FROM levels 
                    WHERE level_number = ? 
                    LIMIT 1
                ");
                $stmtCurrent->execute([$userLevel]);
                $currentLevel = $stmtCurrent->fetch(PDO::FETCH_ASSOC);
                $currentLevelXP = $currentLevel['xp_required'] ?? 0;
                
                // Calcular porcentagem
                $xpInLevel = $userXP - $currentLevelXP;
                $xpNeeded = $nextLevelXP - $currentLevelXP;
                $xpProgress = ($xpNeeded > 0) ? min(100, ($xpInLevel / $xpNeeded) * 100) : 100;
            } else {
                // Usuário no nível máximo
                $xpProgress = 100;
                $nextLevelXP = $userXP;
            }
            
            // Info do nível
            $levelInfo = [
                'title' => $user['level_title'] ?? 'Iniciante',
                'icon' => $user['badge_icon'] ?? '🌱',
                'color' => $user['level_color'] ?? '#6366f1'
            ];
            
            // Atualizar última atividade
            $pdo->prepare("UPDATE users SET last_activity = CURDATE() WHERE id = ?")->execute([$_SESSION['user_id']]);
        } else {
            // Usuário não encontrado ou inativo, limpar sessão
            session_destroy();
            session_start();
        }
    } catch (PDOException $e) {
        error_log("Erro ao carregar usuário: " . $e->getMessage());
    }
}

// Buscar estatísticas gerais do site
$siteStats = [
    'total_courses' => 0,
    'total_students' => 0,
    'total_lessons' => 0,
    'total_completions' => 0
];

try {
    // Total de cursos publicados
    $stmt = $pdo->query("SELECT COUNT(*) FROM courses WHERE is_published = 1");
    $siteStats['total_courses'] = $stmt->fetchColumn() ?: 0;
    
    // Total de estudantes
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student' AND is_active = 1");
    $siteStats['total_students'] = $stmt->fetchColumn() ?: 0;
    
    // Total de lições
    $stmt = $pdo->query("SELECT COUNT(*) FROM lessons WHERE is_published = 1");
    $siteStats['total_lessons'] = $stmt->fetchColumn() ?: 0;
    
    // Total de cursos completados
    $stmt = $pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'completed'");
    $siteStats['total_completions'] = $stmt->fetchColumn() ?: 0;
} catch (PDOException $e) {
    error_log("Erro ao buscar estatísticas: " . $e->getMessage());
}

// Buscar cursos em destaque
$featuredCourses = [];
try {
    $stmt = $pdo->query("
        SELECT c.*, 
               cat.name as category_name, 
               cat.icon as category_icon,
               u.full_name as instructor_name,
               u.avatar as instructor_avatar
        FROM courses c
        LEFT JOIN categories cat ON c.category_id = cat.id
        LEFT JOIN users u ON c.instructor_id = u.id
        WHERE c.is_published = 1 AND c.is_featured = 1
        ORDER BY c.total_students DESC
        LIMIT 6
    ");
    $featuredCourses = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    error_log("Erro ao buscar cursos: " . $e->getMessage());
}

// Se não houver cursos em destaque, buscar os mais recentes
if (empty($featuredCourses)) {
    try {
        $stmt = $pdo->query("
            SELECT c.*, 
                   cat.name as category_name, 
                   cat.icon as category_icon,
                   u.full_name as instructor_name,
                   u.avatar as instructor_avatar
            FROM courses c
            LEFT JOIN categories cat ON c.category_id = cat.id
            LEFT JOIN users u ON c.instructor_id = u.id
            WHERE c.is_published = 1
            ORDER BY c.created_at DESC
            LIMIT 6
        ");
        $featuredCourses = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        error_log("Erro ao buscar cursos recentes: " . $e->getMessage());
    }
}

// Buscar categorias
$categories = [];
try {
    $stmt = $pdo->query("
        SELECT c.*, COUNT(co.id) as course_count
        FROM categories c
        LEFT JOIN courses co ON c.id = co.category_id AND co.is_published = 1
        WHERE c.is_active = 1
        GROUP BY c.id
        ORDER BY c.order_index ASC
        LIMIT 8
    ");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    error_log("Erro ao buscar categorias: " . $e->getMessage());
}

// Buscar ranking semanal
$weeklyLeaderboard = [];
try {
    $stmt = $pdo->query("
        SELECT u.id, u.username, u.full_name, u.avatar, u.xp_total, u.level,
               l.badge_icon, l.color as level_color
        FROM users u
        LEFT JOIN levels l ON u.level = l.level_number
        WHERE u.is_active = 1
        ORDER BY u.xp_total DESC
        LIMIT 10
    ");
    $weeklyLeaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    error_log("Erro ao buscar leaderboard: " . $e->getMessage());
}

// Buscar últimas notícias
$latestNews = [];
try {
    $stmt = $pdo->query("
        SELECT n.*, u.full_name as author_name, u.avatar as author_avatar
        FROM news n
        LEFT JOIN users u ON n.author_id = u.id
        WHERE n.is_published = 1
        ORDER BY n.published_at DESC
        LIMIT 3
    ");
    $latestNews = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    error_log("Erro ao buscar notícias: " . $e->getMessage());
}

// Título da página
$pageTitle = "GameDev Academy - Aprenda Desenvolvimento de Jogos";
$pageDescription = "Plataforma gamificada de ensino de desenvolvimento de jogos. Aprenda Phaser, React, JavaScript e muito mais!";

// Incluir header
include __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-content">
        <div class="hero-text">
            <h1 class="hero-title">
                Aprenda a criar <span class="gradient-text">jogos incríveis</span>
            </h1>
            <p class="hero-subtitle">
                Plataforma gamificada de ensino com cursos práticos, projetos reais e uma comunidade ativa de desenvolvedores.
            </p>
            <div class="hero-buttons">
                <?php if (!$isLoggedIn): ?>
                    <a href="/auth/register.php" class="btn btn-primary btn-lg">
                        <span>🚀</span> Começar Gratuitamente
                    </a>
                    <a href="/courses/" class="btn btn-secondary btn-lg">
                        <span>📚</span> Ver Cursos
                    </a>
                <?php else: ?>
                    <a href="/dashboard/" class="btn btn-primary btn-lg">
                        <span>🎮</span> Ir para Dashboard
                    </a>
                    <a href="/courses/" class="btn btn-secondary btn-lg">
                        <span>📚</span> Explorar Cursos
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="hero-visual">
            <div class="floating-cards">
                <div class="float-card card-1">
                    <span class="card-icon">🎮</span>
                    <span class="card-text">Phaser 3</span>
                </div>
                <div class="float-card card-2">
                    <span class="card-icon">⚛️</span>
                    <span class="card-text">React</span>
                </div>
                <div class="float-card card-3">
                    <span class="card-icon">📜</span>
                    <span class="card-text">JavaScript</span>
                </div>
                <div class="float-card card-4">
                    <span class="card-icon">🏆</span>
                    <span class="card-text">Certificados</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Stats Bar -->
    <div class="stats-bar">
        <div class="stat-item">
            <span class="stat-number"><?= number_format($siteStats['total_courses']) ?></span>
            <span class="stat-label">Cursos</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?= number_format($siteStats['total_students']) ?></span>
            <span class="stat-label">Estudantes</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?= number_format($siteStats['total_lessons']) ?></span>
            <span class="stat-label">Lições</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?= number_format($siteStats['total_completions']) ?></span>
            <span class="stat-label">Conclusões</span>
        </div>
    </div>
</section>

<?php if ($isLoggedIn): ?>
<!-- User Progress Section (só para usuários logados) -->
<section class="user-progress-section">
    <div class="container">
        <div class="progress-card">
            <div class="user-info">
                <div class="user-avatar">
                    <img src="<?= getUserAvatar($userAvatar ?? null, $userName ?? '') ?>" 
                         alt="<?= htmlspecialchars($userName) ?>">
                    <span class="level-badge" style="background: <?= htmlspecialchars($levelInfo['color'] ?? '#6366f1') ?>">
                        <?= htmlspecialchars($levelInfo['icon'] ?? '🌱') ?>
                    </span>
                </div>
                <div class="user-details">
                    <h3>Olá, <?= htmlspecialchars($userName) ?>!</h3>
                    <p class="level-title">
                        Nível <?= $userLevel ?> - <?= htmlspecialchars($levelInfo['title'] ?? 'Iniciante') ?>
                    </p>
                </div>
            </div>
            
            <div class="progress-stats">
                <div class="xp-progress">
                    <div class="progress-header">
                        <span>XP Total</span>
                        <span><?= number_format($userXP) ?> / <?= number_format($nextLevelXP) ?></span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= round($xpProgress, 1) ?>%"></div>
                    </div>
                </div>
                
                <div class="quick-stats">
                    <div class="quick-stat">
                        <span class="stat-icon">🔥</span>
                        <span class="stat-value"><?= $userStreak ?></span>
                        <span class="stat-name">Streak</span>
                    </div>
                    <div class="quick-stat">
                        <span class="stat-icon">🪙</span>
                        <span class="stat-value"><?= number_format($userCoins) ?></span>
                        <span class="stat-name">Moedas</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Featured Courses Section -->
<section class="courses-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">
                <span class="title-icon">🌟</span>
                Cursos em Destaque
            </h2>
            <a href="/courses/" class="view-all-link">Ver todos →</a>
        </div>
        
        <?php if (!empty($featuredCourses)): ?>
        <div class="courses-grid">
            <?php foreach ($featuredCourses as $course): ?>
            <div class="course-card">
                <div class="course-thumbnail">
                    <img src="<?= htmlspecialchars($course['thumbnail'] ?: '/assets/images/courses/default.jpg') ?>" 
                         alt="<?= htmlspecialchars($course['title']) ?>">
                    <?php if ($course['is_free']): ?>
                        <span class="badge badge-free">Grátis</span>
                    <?php endif; ?>
                    <span class="badge badge-difficulty badge-<?= $course['difficulty'] ?>">
                        <?= ucfirst($course['difficulty']) ?>
                    </span>
                </div>
                
                <div class="course-content">
                    <div class="course-category">
                        <span class="category-icon"><?= $course['category_icon'] ?? '📚' ?></span>
                        <span><?= htmlspecialchars($course['category_name'] ?? 'Geral') ?></span>
                    </div>
                    
                    <h3 class="course-title">
                        <a href="/course/<?= htmlspecialchars($course['slug']) ?>">
                            <?= htmlspecialchars($course['title']) ?>
                        </a>
                    </h3>
                    
                    <p class="course-description">
                        <?= htmlspecialchars(substr($course['short_description'] ?? $course['description'], 0, 100)) ?>...
                    </p>
                    
                    <div class="course-meta">
                        <div class="meta-item">
                            <span class="meta-icon">📖</span>
                            <span><?= $course['total_lessons'] ?> lições</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-icon">⏱️</span>
                            <span><?= $course['estimated_hours'] ?>h</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-icon">⭐</span>
                            <span><?= number_format($course['average_rating'], 1) ?></span>
                        </div>
                    </div>
                    
                    <div class="course-footer">
                        <div class="instructor">
                            <img src="<?= getUserAvatar($course['instructor_avatar'] ?? null, $course['instructor_name'] ?? '') ?>" 
                                 alt="<?= htmlspecialchars($course['instructor_name'] ?? 'Instrutor') ?>"
                                 class="instructor-avatar">
                            <span><?= htmlspecialchars($course['instructor_name'] ?? 'Instrutor') ?></span>
                        </div>
                        <div class="course-rewards">
                            <span class="reward">+<?= $course['xp_reward'] ?> XP</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">📚</div>
            <h3>Cursos em breve!</h3>
            <p>Estamos preparando conteúdos incríveis para você. Volte em breve!</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Categories Section -->
<?php if (!empty($categories)): ?>
<section class="categories-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">
                <span class="title-icon">📂</span>
                Explore por Categoria
            </h2>
        </div>
        
        <div class="categories-grid">
            <?php foreach ($categories as $category): ?>
            <a href="/courses/?category=<?= htmlspecialchars($category['slug']) ?>" 
               class="category-card" 
               style="--category-color: <?= htmlspecialchars($category['color']) ?>">
                <span class="category-icon"><?= $category['icon'] ?? '📚' ?></span>
                <h3 class="category-name"><?= htmlspecialchars($category['name']) ?></h3>
                <span class="category-count"><?= $category['course_count'] ?> cursos</span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Leaderboard Section -->
<?php if (!empty($weeklyLeaderboard)): ?>
<section class="leaderboard-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">
                <span class="title-icon">🏆</span>
                Top 10 da Semana
            </h2>
            <a href="/leaderboard/" class="view-all-link">Ver ranking completo →</a>
        </div>
        
        <div class="leaderboard-list">
            <?php foreach ($weeklyLeaderboard as $index => $player): ?>
            <div class="leaderboard-item <?= $index < 3 ? 'top-3' : '' ?>">
                <div class="rank">
                    <?php if ($index === 0): ?>
                        <span class="rank-badge gold">🥇</span>
                    <?php elseif ($index === 1): ?>
                        <span class="rank-badge silver">🥈</span>
                    <?php elseif ($index === 2): ?>
                        <span class="rank-badge bronze">🥉</span>
                    <?php else: ?>
                        <span class="rank-number"><?= $index + 1 ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="player-info">
                    <img src="<?= getUserAvatar($player['avatar'] ?? null, $player['username'] ?? '') ?>" 
                         alt="<?= htmlspecialchars($player['full_name'] ?? $player['username']) ?>"
                         class="player-avatar">
                    <div class="player-details">
                        <span class="player-name"><?= htmlspecialchars($player['full_name'] ?? $player['username']) ?></span>
                        <span class="player-level" style="color: <?= $player['level_color'] ?? '#6366f1' ?>">
                            <?= $player['badge_icon'] ?? '🌱' ?> Nível <?= $player['level'] ?>
                        </span>
                    </div>
                </div>
                
                <div class="player-xp">
                    <span class="xp-value"><?= number_format($player['xp_total']) ?></span>
                    <span class="xp-label">XP</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- News Section -->
<?php if (!empty($latestNews)): ?>
<section class="news-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">
                <span class="title-icon">📰</span>
                Últimas Novidades
            </h2>
            <a href="/news/" class="view-all-link">Ver todas →</a>
        </div>
        
        <div class="news-grid">
            <?php foreach ($latestNews as $article): ?>
            <article class="news-card">
                <div class="news-thumbnail">
                    <img src="<?= htmlspecialchars($article['thumbnail'] ?: '/assets/images/news/default.jpg') ?>" 
                         alt="<?= htmlspecialchars($article['title']) ?>">
                    <span class="news-category"><?= ucfirst($article['category']) ?></span>
                </div>
                <div class="news-content">
                    <h3 class="news-title">
                        <a href="/news/<?= htmlspecialchars($article['slug']) ?>">
                            <?= htmlspecialchars($article['title']) ?>
                        </a>
                    </h3>
                    <p class="news-excerpt"><?= htmlspecialchars($article['excerpt'] ?? '') ?></p>
                    <div class="news-meta">
                        <span class="news-date">
                            <?= date('d/m/Y', strtotime($article['published_at'])) ?>
                        </span>
                        <span class="news-author">
                            Por <?= htmlspecialchars($article['author_name'] ?? 'Admin') ?>
                        </span>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<?php if (!$isLoggedIn): ?>
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Pronto para começar sua jornada?</h2>
            <p>Junte-se a milhares de desenvolvedores que já estão aprendendo de forma divertida e gamificada.</p>
            <a href="/auth/register.php" class="btn btn-primary btn-xl">
                <span>🎮</span> Criar Conta Grátis
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
// Incluir footer
include __DIR__ . '/includes/footer.php';
?>

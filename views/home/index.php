<?php
// views/home/index.php
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Aprenda a criar <span class="text-gradient">jogos incríveis</span></h1>
        <p>Domine Phaser 3 e React com nossa plataforma gamificada</p>
        
        <div class="hero-actions">
            <?php if (!$auth->isLoggedIn()): ?>
                <a href="<?= url('register') ?>" class="btn btn-primary btn-lg">
                    Começar Agora - Grátis
                </a>
            <?php else: ?>
                <a href="<?= url('user') ?>" class="btn btn-primary btn-lg">
                    Acessar Dashboard
                </a>
            <?php endif; ?>
            <a href="<?= url('courses') ?>" class="btn btn-outline btn-lg">
                Ver Cursos
            </a>
        </div>
        
        <div class="hero-stats">
            <div class="stat">
                <h3><?= number_format($totalStudents) ?>+</h3>
                <p>Estudantes</p>
            </div>
            <div class="stat">
                <h3><?= count($courses) ?></h3>
                <p>Cursos</p>
            </div>
            <div class="stat">
                <h3>100%</h3>
                <p>Gamificado</p>
            </div>
        </div>
    </div>
</section>

<!-- Cursos em Destaque -->
<section class="section">
    <div class="container">
        <h2 class="section-title">Cursos em Destaque</h2>
        
        <div class="courses-grid">
            <?php foreach ($courses as $course): ?>
            <div class="course-card">
                <div class="course-thumbnail">
                    <span class="course-icon">🎮</span>
                    <?php if ($course['is_free']): ?>
                        <span class="badge badge-success">GRÁTIS</span>
                    <?php endif; ?>
                </div>
                <div class="course-content">
                    <h3><?= $course['title'] ?></h3>
                    <p><?= $course['description'] ?></p>
                    <div class="course-meta">
                        <span>⏱️ <?= $course['estimated_hours'] ?>h</span>
                        <span>⚡ <?= $course['xp_reward'] ?> XP</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Últimas Notícias -->
<section class="section bg-dark">
    <div class="container">
        <h2 class="section-title">Últimas Notícias</h2>
        
        <div class="news-grid">
            <?php foreach ($news as $article): ?>
            <div class="news-card">
                <h3><?= $article['title'] ?></h3>
                <p><?= $article['excerpt'] ?></p>
                <small><?= date('d/m/Y', strtotime($article['published_at'])) ?></small>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php
// news.php - Página pública de notícias

require_once 'config/database.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Auth.php';
require_once 'classes/News.php';
require_once 'includes/functions.php';

$auth = new Auth();
$newsModel = new News();

// Parâmetros da página
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$category = isset($_GET['category']) ? $_GET['category'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : null;
$perPage = 12;

// Obter notícias
$newsList = $newsModel->getAll($page, $perPage, $category, $search);
$totalNews = $newsModel->count('published');
$totalPages = ceil($totalNews / $perPage);

// Obter notícias em destaque
$featuredNews = $newsModel->getFeatured(3);

// Categorias disponíveis
$categories = $newsModel->getCategories();

// Configurações da página
$pageTitle = 'Notícias - ' . SITE_NAME;
$pageDescription = 'Últimas notícias e atualizações sobre desenvolvimento de jogos';
$activePage = 'news';

// Incluir header
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="news-hero">
    <div class="container">
        <h1 class="hero-title">📰 Notícias e Atualizações</h1>
        <p class="hero-subtitle">Fique por dentro das últimas novidades do mundo gamedev</p>
        
        <!-- Search Bar -->
        <div class="news-search">
            <form method="GET" action="" class="search-form">
                <input type="text" 
                       name="search" 
                       placeholder="Buscar notícias..." 
                       value="<?= htmlspecialchars($search ?? '') ?>"
                       class="search-input">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
    </div>
</section>

<!-- Categories Filter -->
<section class="news-categories">
    <div class="container">
        <div class="categories-wrapper">
            <a href="news.php" 
               class="category-pill <?= !$category ? 'active' : '' ?>">
                Todas
            </a>
            <?php foreach ($categories as $key => $label): ?>
                <a href="?category=<?= $key ?>" 
                   class="category-pill <?= $category === $key ? 'active' : '' ?>">
                    <?= $label ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured News (apenas na primeira página sem filtros) -->
<?php if ($page == 1 && !$category && !$search && !empty($featuredNews)): ?>
<section class="featured-news">
    <div class="container">
        <h2 class="section-title">Em Destaque</h2>
        <div class="featured-grid">
            <?php foreach ($featuredNews as $index => $news): ?>
                <article class="featured-card <?= $index === 0 ? 'featured-main' : '' ?>">
                    <a href="news-detail.php?id=<?= $news['slug'] ?? $news['id'] ?>">
                        <div class="featured-image">
                            <img src="<?= getNewsImage($news['image']) ?>" 
                                 alt="<?= htmlspecialchars($news['title']) ?>">
                            <span class="featured-badge">Destaque</span>
                        </div>
                        <div class="featured-content">
                            <div class="featured-meta">
                                <span class="category-tag"><?= $categories[$news['category']] ?? 'Geral' ?></span>
                                <span class="date"><?= formatDate($news['published_at']) ?></span>
                            </div>
                            <h3 class="featured-title"><?= htmlspecialchars($news['title']) ?></h3>
                            <p class="featured-excerpt">
                                <?= htmlspecialchars(limitText($news['excerpt'], 150)) ?>
                            </p>
                            <div class="featured-author">
                                <img src="<?= getAvatar($news['author_avatar']) ?>" 
                                     alt="<?= htmlspecialchars($news['author_name']) ?>"
                                     class="author-avatar">
                                <span class="author-name">Por <?= htmlspecialchars($news['author_name']) ?></span>
                            </div>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- News List -->
<section class="news-list">
    <div class="container">
        <?php if ($search || $category): ?>
            <div class="filter-info">
                <?php if ($search): ?>
                    <p>Resultados para: <strong><?= htmlspecialchars($search) ?></strong></p>
                <?php endif; ?>
                <?php if ($category): ?>
                    <p>Categoria: <strong><?= $categories[$category] ?? $category ?></strong></p>
                <?php endif; ?>
                <a href="news.php" class="clear-filters">Limpar filtros</a>
            </div>
        <?php endif; ?>
        
        <?php if (empty($newsList)): ?>
            <div class="no-results">
                <i class="fas fa-newspaper"></i>
                <h3>Nenhuma notícia encontrada</h3>
                <p>Tente ajustar seus filtros ou fazer uma nova busca</p>
                <a href="news.php" class="btn btn-primary">Ver todas as notícias</a>
            </div>
        <?php else: ?>
            <div class="news-grid">
                <?php foreach ($newsList as $news): ?>
                    <article class="news-card">
                        <a href="news-detail.php?id=<?= $news['slug'] ?? $news['id'] ?>" class="news-link">
                            <div class="news-image">
                                <img src="<?= getNewsImage($news['thumbnail'] ?? $news['image']) ?>" 
                                     alt="<?= htmlspecialchars($news['title']) ?>"
                                     loading="lazy">
                                <span class="news-category"><?= $categories[$news['category']] ?? 'Geral' ?></span>
                            </div>
                            <div class="news-content">
                                <div class="news-meta">
                                    <span class="news-date">
                                        <i class="far fa-calendar"></i>
                                        <?= formatDate($news['published_at']) ?>
                                    </span>
                                    <span class="news-views">
                                        <i class="far fa-eye"></i>
                                        <?= number_format($news['views_count'] ?? 0) ?>
                                    </span>
                                    <?php if (isset($news['comments_count'])): ?>
                                        <span class="news-comments">
                                            <i class="far fa-comment"></i>
                                            <?= number_format($news['comments_count']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <h3 class="news-title"><?= htmlspecialchars($news['title']) ?></h3>
                                <p class="news-excerpt">
                                    <?= htmlspecialchars(limitText($news['excerpt'] ?? $news['content'], 120)) ?>
                                </p>
                                <div class="news-footer">
                                    <div class="news-author">
                                        <img src="<?= getAvatar($news['author_avatar']) ?>" 
                                             alt="<?= htmlspecialchars($news['author_name']) ?>"
                                             class="author-avatar-small">
                                        <span><?= htmlspecialchars($news['author_name']) ?></span>
                                    </div>
                                    <span class="read-more">
                                        Ler mais <i class="fas fa-arrow-right"></i>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?><?= $category ? '&category=' . $category : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
                           class="pagination-btn pagination-prev">
                            <i class="fas fa-chevron-left"></i> Anterior
                        </a>
                    <?php endif; ?>
                    
                    <div class="pagination-numbers">
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <a href="?page=<?= $i ?><?= $category ? '&category=' . $category : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
                               class="pagination-number <?= $i === $page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?><?= $category ? '&category=' . $category : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
                           class="pagination-btn pagination-next">
                            Próxima <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Newsletter CTA -->
<section class="newsletter-cta">
    <div class="container">
        <div class="newsletter-box">
            <div class="newsletter-content">
                <h3>📬 Não perca nenhuma novidade!</h3>
                <p>Receba as últimas notícias e atualizações diretamente no seu email</p>
            </div>
            <form class="newsletter-form-inline" onsubmit="subscribeNewsletter(event)">
                <input type="email" 
                       placeholder="seu@email.com" 
                       required 
                       class="newsletter-input">
                <button type="submit" class="btn btn-primary">
                    Inscrever-se
                </button>
            </form>
        </div>
    </div>
</section>

<?php
// Scripts específicos da página
$additionalJS = ['js/news.js'];

// Incluir footer
require_once 'includes/footer.php';
?>
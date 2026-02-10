<?php
// news-detail.php - Página de detalhes da notícia

require_once 'config/database.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Auth.php';
require_once 'classes/News.php';
require_once 'includes/functions.php';

$auth = new Auth();
$newsModel = new News();

// Obter ID ou slug da notícia
$newsId = $_GET['id'] ?? null;

if (!$newsId) {
    header('Location: news.php');
    exit;
}

// Obter notícia
$news = $newsModel->getById($newsId);

if (!$news || $news['status'] !== 'published') {
    header('HTTP/1.0 404 Not Found');
    $pageTitle = 'Notícia não encontrada';
    require_once 'includes/header.php';
    echo '<div class="container"><div class="error-404">
            <h1>404</h1>
            <p>Notícia não encontrada</p>
            <a href="news.php" class="btn btn-primary">Ver todas as notícias</a>
          </div></div>';
    require_once 'includes/footer.php';
    exit;
}

// Registrar visualização
$userId = $auth->isLoggedIn() ? $auth->getCurrentUser()['id'] : null;
$newsModel->registerView($news['id'], $userId);

// Obter notícias relacionadas
$relatedNews = $newsModel->getRelated($news['id'], $news['category'], 4);

// Categorias
$categories = $newsModel->getCategories();

// Configurações da página
$pageTitle = $news['meta_title'] ?? $news['title'] . ' - ' . SITE_NAME;
$pageDescription = $news['meta_description'] ?? $news['excerpt'];
$activePage = 'news';

// Adicionar CSS específico
$additionalCSS = ['css/news-detail.css', 'css/prism.css'];

// Incluir header
require_once 'includes/header.php';
?>

<!-- Article Header -->
<article class="article-container">
    <header class="article-header">
        <div class="container">
            <div class="article-meta-top">
                <a href="news.php?category=<?= $news['category'] ?>" class="category-link">
                    <?= $categories[$news['category']] ?? 'Geral' ?>
                </a>
                <span class="separator">•</span>
                <time datetime="<?= $news['published_at'] ?>">
                    <?= formatDate($news['published_at'], 'full') ?>
                </time>
                <span class="separator">•</span>
                <span class="reading-time">
                    <i class="far fa-clock"></i>
                    <?= calculateReadingTime($news['content']) ?> min de leitura
                </span>
            </div>
            
            <h1 class="article-title"><?= htmlspecialchars($news['title']) ?></h1>
            
            <?php if ($news['excerpt']): ?>
                <p class="article-excerpt"><?= htmlspecialchars($news['excerpt']) ?></p>
            <?php endif; ?>
            
            <div class="article-author">
                <img src="<?= getAvatar($news['author_avatar']) ?>" 
                     alt="<?= htmlspecialchars($news['author_name']) ?>"
                     class="author-avatar-large">
                <div class="author-info">
                    <div class="author-name">
                        Por <strong><?= htmlspecialchars($news['author_name']) ?></strong>
                    </div>
                    <?php if ($news['author_bio']): ?>
                        <div class="author-bio"><?= htmlspecialchars($news['author_bio']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="article-stats">
                    <span class="stat-item">
                        <i class="far fa-eye"></i>
                        <?= number_format($news['views_count'] ?? 0) ?> visualizações
                    </span>
                    <span class="stat-item">
                        <i class="far fa-comment"></i>
                        <?= number_format($news['comments_count'] ?? 0) ?> comentários
                    </span>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Featured Image -->
    <?php if ($news['image']): ?>
        <div class="article-featured-image">
            <div class="container-wide">
                <img src="<?= getNewsImage($news['image']) ?>" 
                     alt="<?= htmlspecialchars($news['title']) ?>">
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Article Content -->
    <div class="article-content">
        <div class="container container-narrow">
            <div class="content-wrapper">
                <?= $news['content'] ?>
            </div>
            
            <!-- Tags -->
            <?php if ($news['tags']): ?>
                <div class="article-tags">
                    <?php 
                    $tags = explode(',', $news['tags']);
                    foreach ($tags as $tag): 
                        $tag = trim($tag);
                    ?>
                        <a href="news.php?search=<?= urlencode($tag) ?>" class="tag">
                            #<?= htmlspecialchars($tag) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Share Buttons -->
            <div class="article-share">
                <h4>Compartilhar:</h4>
                <div class="share-buttons">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(getCurrentUrl()) ?>" 
                       target="_blank" 
                       class="share-btn share-facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?text=<?= urlencode($news['title']) ?>&url=<?= urlencode(getCurrentUrl()) ?>" 
                       target="_blank" 
                       class="share-btn share-twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode(getCurrentUrl()) ?>&title=<?= urlencode($news['title']) ?>" 
                       target="_blank" 
                       class="share-btn share-linkedin">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="https://wa.me/?text=<?= urlencode($news['title'] . ' ' . getCurrentUrl()) ?>" 
                       target="_blank" 
                       class="share-btn share-whatsapp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <button onclick="copyToClipboard('<?= getCurrentUrl() ?>')" 
                            class="share-btn share-link">
                        <i class="fas fa-link"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Author Box -->
    <div class="author-box">
        <div class="container container-narrow">
            <div class="author-box-content">
                <img src="<?= getAvatar($news['author_avatar']) ?>" 
                     alt="<?= htmlspecialchars($news['author_name']) ?>"
                     class="author-box-avatar">
                <div class="author-box-info">
                    <h4>Sobre o autor</h4>
                    <h3><?= htmlspecialchars($news['author_name']) ?></h3>
                    <p><?= htmlspecialchars($news['author_bio'] ?? 'Membro da equipe GameDev Academy') ?></p>
                </div>
            </div>
        </div>
    </div>
</article>

<!-- Related News -->
<?php if (!empty($relatedNews)): ?>
<section class="related-news">
    <div class="container">
        <h2 class="section-title">Notícias Relacionadas</h2>
        <div class="related-grid">
            <?php foreach ($relatedNews as $related): ?>
                <article class="related-card">
                    <a href="news-detail.php?id=<?= $related['slug'] ?? $related['id'] ?>">
                        <div class="related-image">
                            <img src="<?= getNewsImage($related['thumbnail'] ?? $related['image']) ?>" 
                                 alt="<?= htmlspecialchars($related['title']) ?>">
                        </div>
                        <div class="related-content">
                            <span class="related-date"><?= formatDate($related['published_at']) ?></span>
                            <h4 class="related-title"><?= htmlspecialchars($related['title']) ?></h4>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Comments Section -->
<section class="comments-section">
    <div class="container container-narrow">
        <div class="comments-header">
            <h3>Comentários (<?= number_format($news['comments_count'] ?? 0) ?>)</h3>
        </div>
        
        <?php if ($auth->isLoggedIn()): ?>
            <!-- Comment Form -->
            <form class="comment-form" id="commentForm" onsubmit="postComment(event)">
                <input type="hidden" name="news_id" value="<?= $news['id'] ?>">
                <div class="form-group">
                    <textarea name="comment" 
                              rows="4" 
                              placeholder="Deixe seu comentário..." 
                              required
                              class="form-control"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    Publicar Comentário
                </button>
            </form>
        <?php else: ?>
            <div class="login-prompt">
                <p>Faça login para comentar</p>
                <a href="login.php?redirect=<?= urlencode(getCurrentUrl()) ?>" class="btn btn-primary">
                    Fazer Login
                </a>
            </div>
        <?php endif; ?>
        
        <!-- Comments List -->
        <div class="comments-list" id="commentsList">
            <!-- Comentários carregados via AJAX -->
        </div>
    </div>
</section>

<?php
// Scripts específicos da página
$additionalJS = ['js/news-detail.js', 'js/prism.js'];

// Incluir footer
require_once 'includes/footer.php';
?>
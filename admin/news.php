<?php
// admin/news.php - Gerenciamento de notícias

session_start();
require_once '../config/database.php';
require_once '../classes/Database.php';
require_once '../classes/User.php';
require_once '../classes/Auth.php';
require_once '../classes/News.php';
require_once '../includes/functions.php';

$auth = new Auth();

// Verificar se é admin
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$newsModel = new News();
$currentUser = $auth->getCurrentUser();

// Ações (deletar, publicar, etc.)
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $newsId = $_POST['news_id'] ?? null;
    
    switch ($action) {
        case 'delete':
            if ($newsId) {
                $newsModel->delete($newsId);
                $_SESSION['success'] = 'Notícia excluída com sucesso!';
            }
            break;
            
        case 'publish':
            if ($newsId) {
                $newsModel->update($newsId, ['status' => 'published', 'published_at' => date('Y-m-d H:i:s')]);
                $_SESSION['success'] = 'Notícia publicada com sucesso!';
            }
            break;
            
        case 'unpublish':
            if ($newsId) {
                $newsModel->update($newsId, ['status' => 'draft']);
                $_SESSION['success'] = 'Notícia despublicada com sucesso!';
            }
            break;
            
        case 'toggle_featured':
            if ($newsId) {
                $news = $newsModel->getById($newsId);
                $newsModel->update($newsId, ['featured' => !$news['featured']]);
                $_SESSION['success'] = 'Status de destaque alterado!';
            }
            break;
    }
    
    header('Location: news.php');
    exit;
}

// Parâmetros de filtro e paginação
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$status = $_GET['status'] ?? null;
$search = $_GET['search'] ?? null;
$perPage = 20;

// Obter notícias (admin vê todas, não apenas publicadas)
$sql = "SELECT n.*, u.username as author_name
        FROM news n
        LEFT JOIN users u ON n.author_id = u.id
        WHERE 1=1";

$params = [];

if ($status && $status !== 'all') {
    $sql .= " AND n.status = :status";
    $params['status'] = $status;
}

if ($search) {
    $sql .= " AND (n.title LIKE :search OR n.content LIKE :search)";
    $params['search'] = '%' . $search . '%';
}

$sql .= " ORDER BY n.created_at DESC";

// Contar total
$countSql = str_replace("SELECT n.*, u.username as author_name", "SELECT COUNT(*)", $sql);
$countSql = preg_replace('/ORDER BY.*$/', '', $countSql);
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalNews = $stmt->fetchColumn();
$totalPages = ceil($totalNews / $perPage);

// Adicionar paginação
$offset = ($page - 1) * $perPage;
$sql .= " LIMIT :offset, :limit";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue('offset', $offset, PDO::PARAM_INT);
$stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
$stmt->execute();
$newsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Categorias
$categories = $newsModel->getCategories();

// Estatísticas
$stats = [
    'total' => $newsModel->count('all'),
    'published' => $newsModel->count('published'),
    'draft' => $newsModel->count('draft'),
    'deleted' => $newsModel->count('deleted')
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Notícias - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="admin-body">
    
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="admin-container">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="admin-header">
                <h1>📰 Gerenciar Notícias</h1>
                <a href="news-edit.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nova Notícia
                </a>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['success'] ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($stats['published']) ?></div>
                    <div class="stat-label">Publicadas</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($stats['draft']) ?></div>
                    <div class="stat-label">Rascunhos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($stats['total']) ?></div>
                    <div class="stat-label">Total</div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="filters">
                <form method="GET" class="filter-form">
                    <div class="filter-group">
                        <select name="status" onchange="this.form.submit()">
                            <option value="all">Todos os Status</option>
                            <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Publicado</option>
                            <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Rascunho</option>
                            <option value="deleted" <?= $status === 'deleted' ? 'selected' : '' ?>>Excluído</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <input type="text" 
                               name="search" 
                               placeholder="Buscar notícias..." 
                               value="<?= htmlspecialchars($search ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-secondary">Filtrar</button>
                    <a href="news.php" class="btn btn-outline">Limpar</a>
                </form>
            </div>
            
            <!-- News Table -->
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>Categoria</th>
                            <th>Status</th>
                            <th>Destaque</th>
                            <th>Views</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($newsList as $news): ?>
                            <tr>
                                <td>#<?= $news['id'] ?></td>
                                <td>
                                    <div class="news-title-cell">
                                        <?php if ($news['thumbnail']): ?>
                                            <img src="<?= getNewsImage($news['thumbnail']) ?>" 
                                                 alt="" 
                                                 class="news-thumb-small">
                                        <?php endif; ?>
                                        <div>
                                            <strong><?= htmlspecialchars($news['title']) ?></strong>
                                            <?php if ($news['slug']): ?>
                                                <br>
                                                <small class="text-muted">/<?= $news['slug'] ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($news['author_name']) ?></td>
                                <td>
                                    <span class="badge badge-info">
                                        <?= $categories[$news['category']] ?? $news['category'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = [
                                        'published' => 'success',
                                        'draft' => 'warning',
                                        'deleted' => 'danger'
                                    ][$news['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge badge-<?= $statusClass ?>">
                                        <?= ucfirst($news['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle_featured">
                                        <input type="hidden" name="news_id" value="<?= $news['id'] ?>">
                                        <button type="submit" 
                                                class="btn-icon <?= $news['featured'] ? 'active' : '' ?>"
                                                title="<?= $news['featured'] ? 'Remover destaque' : 'Destacar' ?>">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    </form>
                                </td>
                                <td><?= number_format($news['views'] ?? 0) ?></td>
                                <td><?= formatDate($news['created_at']) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="../news-detail.php?id=<?= $news['slug'] ?? $news['id'] ?>" 
                                           class="btn-icon" 
                                           target="_blank"
                                           title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="news-edit.php?id=<?= $news['id'] ?>" 
                                           class="btn-icon"
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <?php if ($news['status'] === 'draft'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="publish">
                                                <input type="hidden" name="news_id" value="<?= $news['id'] ?>">
                                                <button type="submit" 
                                                        class="btn-icon"
                                                        title="Publicar">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        <?php elseif ($news['status'] === 'published'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="unpublish">
                                                <input type="hidden" name="news_id" value="<?= $news['id'] ?>">
                                                <button type="submit" 
                                                        class="btn-icon"
                                                        title="Despublicar">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <form method="POST" 
                                              style="display: inline;"
                                              onsubmit="return confirm('Tem certeza que deseja excluir esta notícia?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="news_id" value="<?= $news['id'] ?>">
                                            <button type="submit" 
                                                    class="btn-icon btn-danger"
                                                    title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?>&status=<?= $status ?>&search=<?= urlencode($search ?? '') ?>" 
                           class="pagination-link <?= $i === $page ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <script src="../assets/js/admin.js"></script>
</body>
</html>
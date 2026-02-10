<?php
session_start();

// Verificar se está logado como admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// ===== ADICIONE ESTE BLOCO DE CONEXÃO =====
// Conexão com o banco de dados
$db_host = 'localhost';
$db_name = 'gamedev_academy';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        )
    );
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}
// ===== FIM DO BLOCO DE CONEXÃO =====

$page_title = "Gerenciar Notícias";
$current_page = 'news';

// Incluir configurações
require_once '../config/database.php';

$page_title = "Gerenciar Notícias";
$current_page = 'news';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $news_id = $_POST['news_id'] ?? 0;
    
    switch ($_POST['action']) {
        case 'delete':
            $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
            $stmt->execute([$news_id]);
            $_SESSION['success'] = "Notícia excluída com sucesso!";
            break;
            
        case 'publish':
            $stmt = $pdo->prepare("UPDATE news SET status = 'published', published_at = NOW() WHERE id = ?");
            $stmt->execute([$news_id]);
            $_SESSION['success'] = "Notícia publicada com sucesso!";
            break;
            
        case 'unpublish':
            $stmt = $pdo->prepare("UPDATE news SET status = 'draft' WHERE id = ?");
            $stmt->execute([$news_id]);
            $_SESSION['success'] = "Notícia despublicada!";
            break;
            
        case 'toggle_featured':
            $stmt = $pdo->prepare("UPDATE news SET featured = NOT featured WHERE id = ?");
            $stmt->execute([$news_id]);
            $_SESSION['success'] = "Status de destaque alterado!";
            break;
    }
    
    header('Location: news.php');
    exit();
}

// Paginação
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Buscar notícias
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

$where = [];
$params = [];

if ($search) {
    $where[] = "(title LIKE ? OR content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status_filter) {
    $where[] = "status = ?";
    $params[] = $status_filter;
}

$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Contar total
$count_sql = "SELECT COUNT(*) FROM news $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total = $count_stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

// Buscar notícias com autor
$sql = "SELECT n.*, u.username as author_name 
        FROM news n 
        LEFT JOIN users u ON n.author_id = u.id 
        $where_clause 
        ORDER BY n.created_at DESC 
        LIMIT $per_page OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$news_list = $stmt->fetchAll();

// Estatísticas
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(status = 'published') as published,
        SUM(status = 'draft') as draft,
        SUM(featured = 1) as featured,
        SUM(views) as total_views
    FROM news
")->fetch();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
        }
        .sidebar .nav-link:hover {
            color: white;
        }
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            color: #667eea;
            font-size: 2rem;
            margin: 0;
        }
        .news-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center text-white mb-4">
                        <h4>🎮 GameDev Academy</h4>
                        <small>Painel Admin</small>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-dashboard"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="news.php">
                                <i class="fas fa-newspaper"></i> Notícias
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="courses.php">
                                <i class="fas fa-graduation-cap"></i> Cursos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users"></i> Usuários
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../logout.php">
                                <i class="fas fa-sign-out-alt"></i> Sair
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>📰 Gerenciar Notícias</h1>
                    <a href="news-edit.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nova Notícia
                    </a>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $_SESSION['success'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <!-- Estatísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h3><?= number_format($stats['total'] ?? 0) ?></h3>
                            <p class="text-muted mb-0">Total de Notícias</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h3><?= number_format($stats['published'] ?? 0) ?></h3>
                            <p class="text-muted mb-0">Publicadas</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h3><?= number_format($stats['featured'] ?? 0) ?></h3>
                            <p class="text-muted mb-0">Em Destaque</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h3><?= number_format($stats['total_views'] ?? 0) ?></h3>
                            <p class="text-muted mb-0">Visualizações</p>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Buscar notícias..." 
                                       value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="">Todos os Status</option>
                                    <option value="published" <?= $status_filter == 'published' ? 'selected' : '' ?>>Publicadas</option>
                                    <option value="draft" <?= $status_filter == 'draft' ? 'selected' : '' ?>>Rascunhos</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                <a href="news.php" class="btn btn-secondary">Limpar</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabela de Notícias -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Imagem</th>
                                <th>Título</th>
                                <th>Categoria</th>
                                <th>Autor</th>
                                <th>Status</th>
                                <th>Destaque</th>
                                <th>Views</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($news_list)): ?>
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <p class="mb-0">Nenhuma notícia encontrada.</p>
                                        <a href="news-edit.php" class="btn btn-primary mt-3">
                                            <i class="fas fa-plus"></i> Criar Primeira Notícia
                                        </a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($news_list as $news): ?>
                                    <tr>
                                        <td><?= $news['id'] ?></td>
                                        <td>
                                            <?php if ($news['thumbnail']): ?>
                                                <img src="../uploads/news/<?= $news['thumbnail'] ?>" class="news-thumb">
                                            <?php else: ?>
                                                <div class="news-thumb bg-secondary d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-image text-white"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($news['title']) ?></strong>
                                            <?php if ($news['excerpt']): ?>
                                                <br><small class="text-muted"><?= substr($news['excerpt'], 0, 50) ?>...</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?= $news['category'] ?? 'geral' ?></span>
                                        </td>
                                        <td><?= $news['author_name'] ?? 'Admin' ?></td>
                                        <td>
                                            <?php
                                            $badge_class = [
                                                'published' => 'success',
                                                'draft' => 'warning',
                                                'archived' => 'secondary'
                                            ][$news['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?= $badge_class ?>"><?= ucfirst($news['status']) ?></span>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="toggle_featured">
                                                <input type="hidden" name="news_id" value="<?= $news['id'] ?>">
                                                <button type="submit" class="btn btn-link p-0">
                                                    <i class="fas fa-star <?= $news['featured'] ? 'text-warning' : 'text-muted' ?>"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td><?= number_format($news['views']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($news['created_at'])) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="../news-detail.php?id=<?= $news['id'] ?>" 
                                                   class="btn btn-outline-primary" target="_blank" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="news-edit.php?id=<?= $news['id'] ?>" 
                                                   class="btn btn-outline-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <?php if ($news['status'] == 'draft'): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="publish">
                                                        <input type="hidden" name="news_id" value="<?= $news['id'] ?>">
                                                        <button type="submit" class="btn btn-outline-success btn-sm" title="Publicar">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="unpublish">
                                                        <input type="hidden" name="news_id" value="<?= $news['id'] ?>">
                                                        <button type="submit" class="btn btn-outline-secondary btn-sm" title="Despublicar">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <form method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Tem certeza que deseja excluir?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="news_id" value="<?= $news['id'] ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Excluir">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginação -->
                <?php if ($total_pages > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= $status_filter ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
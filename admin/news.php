<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar login (temporário para testes)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'Admin';
}

// ============ CONEXÃO ============
$host = 'localhost';
$dbname = 'gamedev_academy';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

// ============ DETECTAR ESTRUTURA DA TABELA ============
$columns = [];
$stmt = $pdo->query("SHOW COLUMNS FROM news");
while ($row = $stmt->fetch()) {
    $columns[$row['Field']] = $row['Type'];
}

// Mapear colunas (detectar quais existem)
$col_map = [
    'id' => 'id',
    'title' => 'title',
    'slug' => 'slug',
    'created_at' => 'created_at'
];

// Status
if (isset($columns['status'])) {
    $col_map['status'] = 'status';
} elseif (isset($columns['is_published'])) {
    $col_map['status'] = "CASE WHEN is_published = 1 THEN 'published' ELSE 'draft' END";
} else {
    $col_map['status'] = "'draft'";
}

// Featured
if (isset($columns['featured'])) {
    $col_map['featured'] = 'featured';
} elseif (isset($columns['is_featured'])) {
    $col_map['featured'] = 'is_featured';
} else {
    $col_map['featured'] = '0';
}

// Views
if (isset($columns['views_count'])) {
    $col_map['views'] = 'views_count';
} elseif (isset($columns['views'])) {
    $col_map['views'] = 'views';
} else {
    $col_map['views'] = '0';
}

// Category
if (isset($columns['category'])) {
    $col_map['category'] = 'category';
} else {
    $col_map['category'] = "NULL";
}

// Summary/Excerpt
if (isset($columns['summary'])) {
    $col_map['summary'] = 'summary';
} elseif (isset($columns['excerpt'])) {
    $col_map['summary'] = 'excerpt';
} else {
    $col_map['summary'] = "NULL";
}

// Image
if (isset($columns['image_url'])) {
    $col_map['image'] = 'image_url';
} elseif (isset($columns['thumbnail'])) {
    $col_map['image'] = 'thumbnail';
} elseif (isset($columns['image'])) {
    $col_map['image'] = 'image';
} else {
    $col_map['image'] = "NULL";
}

// Author
if (isset($columns['author_id'])) {
    $col_map['author_id'] = 'author_id';
} else {
    $col_map['author_id'] = "NULL";
}

// Published at
if (isset($columns['published_at'])) {
    $col_map['published_at'] = 'published_at';
} else {
    $col_map['published_at'] = 'created_at';
}

// ============ PAGINAÇÃO E FILTROS ============
$items_per_page = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $items_per_page;

$search = trim($_GET['search'] ?? '');
$filter_status = $_GET['status'] ?? '';
$filter_category = $_GET['category'] ?? '';

// ============ CONSTRUIR QUERY ============
$where_clauses = [];
$params = [];

// Filtro de busca
if ($search) {
    $where_clauses[] = "(n.title LIKE :search OR n.slug LIKE :search)";
    $params[':search'] = "%$search%";
}

// Filtro de status
if ($filter_status) {
    if (isset($columns['status'])) {
        $where_clauses[] = "n.status = :status";
        $params[':status'] = $filter_status;
    } elseif (isset($columns['is_published'])) {
        if ($filter_status == 'published') {
            $where_clauses[] = "n.is_published = 1";
        } elseif ($filter_status == 'draft') {
            $where_clauses[] = "n.is_published = 0";
        }
    }
}

// Filtro de categoria
if ($filter_category && isset($columns['category'])) {
    $where_clauses[] = "n.category = :category";
    $params[':category'] = $filter_category;
}

$where_sql = $where_clauses ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Contar total
$count_sql = "SELECT COUNT(*) FROM news n $where_sql";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_items = $stmt->fetchColumn();
$total_pages = max(1, ceil($total_items / $items_per_page));

// Buscar notícias
$sql = "SELECT 
            n.{$col_map['id']} as id,
            n.{$col_map['title']} as title,
            n.{$col_map['slug']} as slug,
            n.{$col_map['category']} as category,
            {$col_map['status']} as status,
            n.{$col_map['featured']} as featured,
            n.{$col_map['views']} as views_count,
            n.{$col_map['image']} as image,
            n.{$col_map['created_at']} as created_at,
            n.{$col_map['published_at']} as published_at";

// Adicionar join com users se author_id existir
if (isset($columns['author_id'])) {
    $sql .= ", u.username as author_name FROM news n LEFT JOIN users u ON n.author_id = u.id";
} else {
    $sql .= ", 'Sistema' as author_name FROM news n";
}

$sql .= " $where_sql ORDER BY n.created_at DESC LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$news_items = $stmt->fetchAll();

// Buscar categorias para filtro
$categories = [];
if (isset($columns['category'])) {
    $stmt = $pdo->query("SELECT DISTINCT category FROM news WHERE category IS NOT NULL AND category != '' ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Verificar se precisa migração
$needs_migration = !isset($columns['status']) || !isset($columns['views_count']) || !isset($columns['image_url']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Notícias - GameDev Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #6f42c1;
            --primary-dark: #5a32a3;
        }
        
        body {
            background-color: #f4f6f9;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, #8e5bd4 100%);
            color: white;
            padding: 1.5rem 0;
            margin-bottom: 2rem;
        }
        
        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            border-radius: 10px;
        }
        
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: #6c757d;
            border-bottom: 2px solid #e9ecef;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .badge-status {
            padding: 0.4rem 0.8rem;
            font-size: 0.75rem;
            font-weight: 500;
            border-radius: 50px;
        }
        
        .badge-published { background: #d4edda; color: #155724; }
        .badge-draft { background: #fff3cd; color: #856404; }
        .badge-archived { background: #e2e3e5; color: #383d41; }
        
        .news-title {
            font-weight: 500;
            color: #212529;
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .news-title:hover {
            color: var(--primary);
        }
        
        .news-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .news-image-placeholder {
            width: 50px;
            height: 50px;
            background: #e9ecef;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #adb5bd;
        }
        
        .featured-star {
            color: #ffc107;
            font-size: 1rem;
        }
        
        .btn-action {
            padding: 0.375rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .filter-card {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .stats-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .stats-label {
            font-size: 0.75rem;
            color: #6c757d;
            text-transform: uppercase;
        }
        
        .alert-migration {
            background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%);
            border: none;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="page-header">
        <div class="container-fluid px-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="bi bi-newspaper me-2"></i>
                        Gerenciar Notícias
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 mt-2" style="font-size: 0.875rem;">
                            <li class="breadcrumb-item"><a href="dashboard.php" class="text-white-50">Dashboard</a></li>
                            <li class="breadcrumb-item active text-white">Notícias</li>
                        </ol>
                    </nav>
                </div>
                <a href="news-create.php" class="btn btn-light">
                    <i class="bi bi-plus-lg me-1"></i> Nova Notícia
                </a>
            </div>
        </div>
    </div>
    
    <div class="container-fluid px-4">
        <?php if ($needs_migration): ?>
        <div class="alert alert-migration mb-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-3 fs-4 text-warning"></i>
                <div class="flex-grow-1">
                    <strong>Atenção:</strong> A estrutura da tabela 'news' está desatualizada.
                    <br><small class="text-muted">Recomendamos executar a migração para ter acesso a todas as funcionalidades.</small>
                </div>
                <a href="migrate-news-table.php" class="btn btn-warning btn-sm">
                    <i class="bi bi-arrow-repeat me-1"></i> Migrar Agora
                </a>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Estatísticas Rápidas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $total_items; ?></div>
                    <div class="stats-label">Total de Notícias</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <?php
                    if (isset($columns['status'])) {
                        $published = $pdo->query("SELECT COUNT(*) FROM news WHERE status = 'published'")->fetchColumn();
                    } elseif (isset($columns['is_published'])) {
                        $published = $pdo->query("SELECT COUNT(*) FROM news WHERE is_published = 1")->fetchColumn();
                    } else {
                        $published = 0;
                    }
                    ?>
                    <div class="stats-number text-success"><?php echo $published; ?></div>
                    <div class="stats-label">Publicadas</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <?php
                    if (isset($columns['status'])) {
                        $drafts = $pdo->query("SELECT COUNT(*) FROM news WHERE status = 'draft'")->fetchColumn();
                    } elseif (isset($columns['is_published'])) {
                        $drafts = $pdo->query("SELECT COUNT(*) FROM news WHERE is_published = 0")->fetchColumn();
                    } else {
                        $drafts = 0;
                    }
                    ?>
                    <div class="stats-number text-warning"><?php echo $drafts; ?></div>
                    <div class="stats-label">Rascunhos</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <?php
                    $featured_col = isset($columns['featured']) ? 'featured' : (isset($columns['is_featured']) ? 'is_featured' : null);
                    $featured_count = $featured_col ? $pdo->query("SELECT COUNT(*) FROM news WHERE $featured_col = 1")->fetchColumn() : 0;
                    ?>
                    <div class="stats-number text-primary"><?php echo $featured_count; ?></div>
                    <div class="stats-label">Em Destaque</div>
                </div>
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="filter-card">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Título ou slug..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="published" <?php echo $filter_status == 'published' ? 'selected' : ''; ?>>
                            Publicado
                        </option>
                        <option value="draft" <?php echo $filter_status == 'draft' ? 'selected' : ''; ?>>
                            Rascunho
                        </option>
                        <?php if (isset($columns['status'])): ?>
                        <option value="archived" <?php echo $filter_status == 'archived' ? 'selected' : ''; ?>>
                            Arquivado
                        </option>
                        <?php endif; ?>
                    </select>
                </div>
                
                <?php if (count($categories) > 0): ?>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Categoria</label>
                    <select name="category" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" 
                                <?php echo $filter_category == $cat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(ucfirst($cat)); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Tabela de Notícias -->
        <div class="card">
            <div class="card-body p-0">
                <?php if (count($news_items) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px;">Imagem</th>
                                <th>Título</th>
                                <th style="width: 120px;">Categoria</th>
                                <th style="width: 100px;">Status</th>
                                <th style="width: 100px;">Autor</th>
                                <th style="width: 80px;">Views</th>
                                <th style="width: 120px;">Data</th>
                                <th style="width: 120px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($news_items as $item): ?>
                            <tr>
                                <td>
                                    <?php if ($item['image']): ?>
                                    <img src="../<?php echo htmlspecialchars($item['image']); ?>" 
                                         alt="" class="news-image">
                                    <?php else: ?>
                                    <div class="news-image-placeholder">
                                        <i class="bi bi-image"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($item['featured']): ?>
                                        <i class="bi bi-star-fill featured-star me-2" title="Em destaque"></i>
                                        <?php endif; ?>
                                        <div>
                                            <a href="news-edit.php?id=<?php echo $item['id']; ?>" class="news-title">
                                                <?php echo htmlspecialchars($item['title']); ?>
                                            </a>
                                            <br>
                                            <small class="text-muted">/<?php echo htmlspecialchars($item['slug']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($item['category']): ?>
                                    <span class="badge bg-light text-dark">
                                        <?php echo htmlspecialchars(ucfirst($item['category'])); ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-status badge-<?php echo $item['status']; ?>">
                                        <?php 
                                        $status_labels = [
                                            'published' => 'Publicado',
                                            'draft' => 'Rascunho',
                                            'archived' => 'Arquivado'
                                        ];
                                        echo $status_labels[$item['status']] ?? $item['status'];
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <small><?php echo htmlspecialchars($item['author_name'] ?? 'Sistema'); ?></small>
                                </td>
                                <td>
                                    <span class="text-muted">
                                        <i class="bi bi-eye me-1"></i>
                                        <?php echo number_format($item['views_count'], 0, ',', '.'); ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y', strtotime($item['created_at'])); ?>
                                        <br>
                                        <?php echo date('H:i', strtotime($item['created_at'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="news-edit.php?id=<?php echo $item['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary btn-action"
                                           title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if ($item['status'] == 'published'): ?>
                                        <a href="../news/<?php echo $item['slug']; ?>" 
                                           target="_blank"
                                           class="btn btn-sm btn-outline-info btn-action"
                                           title="Visualizar">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php endif; ?>
                                        <button onclick="deleteNews(<?php echo $item['id']; ?>, '<?php echo addslashes($item['title']); ?>')" 
                                                class="btn btn-sm btn-outline-danger btn-action"
                                                title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-newspaper text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-muted">Nenhuma notícia encontrada</h5>
                    <p class="text-muted">
                        <?php if ($search || $filter_status || $filter_category): ?>
                        Tente ajustar os filtros de busca.
                        <?php else: ?>
                        Clique em "Nova Notícia" para criar a primeira.
                        <?php endif; ?>
                    </p>
                    <a href="news-create.php" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Nova Notícia
                    </a>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($total_pages > 1): ?>
            <div class="card-footer bg-white">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $filter_status; ?>&category=<?php echo urlencode($filter_category); ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        
                        <?php 
                        $start = max(1, $page - 2);
                        $end = min($total_pages, $page + 2);
                        
                        if ($start > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=1&search=<?php echo urlencode($search); ?>&status=<?php echo $filter_status; ?>&category=<?php echo urlencode($filter_category); ?>">1</a>
                        </li>
                        <?php if ($start > 2): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $filter_status; ?>&category=<?php echo urlencode($filter_category); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($end < $total_pages): ?>
                        <?php if ($end < $total_pages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $total_pages; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $filter_status; ?>&category=<?php echo urlencode($filter_category); ?>">
                                <?php echo $total_pages; ?>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $filter_status; ?>&category=<?php echo urlencode($filter_category); ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <div class="text-center mt-2">
                    <small class="text-muted">
                        Mostrando <?php echo count($news_items); ?> de <?php echo $total_items; ?> notícias
                    </small>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Debug Info (remover em produção) -->
        <?php if (isset($_GET['debug'])): ?>
        <div class="card mt-4">
            <div class="card-header bg-dark text-white">
                <i class="bi bi-bug"></i> Debug Info
            </div>
            <div class="card-body">
                <h6>Colunas detectadas:</h6>
                <pre><?php print_r($columns); ?></pre>
                
                <h6>Mapeamento:</h6>
                <pre><?php print_r($col_map); ?></pre>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir a notícia:</p>
                    <p class="fw-bold" id="deleteNewsTitle"></p>
                    <p class="text-danger"><small>Esta ação não pode ser desfeita!</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="deleteForm" method="POST" action="news-delete.php" style="display: inline;">
                        <input type="hidden" name="id" id="deleteNewsId">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteNews(id, title) {
            document.getElementById('deleteNewsId').value = id;
            document.getElementById('deleteNewsTitle').textContent = title;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>
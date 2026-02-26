<?php
$pageTitle = 'Gerenciar Notícias';
include '../includes/header.php';

$db = Database::getInstance();

// Filtros
$status = $_GET['status'] ?? 'all';
$category = $_GET['category'] ?? 'all';
$search = trim($_GET['search'] ?? '');

// Construir query
$where = [];
$params = [];

if ($status !== 'all') {
    $where[] = "status = ?";
    $params[] = $status;
}

if ($category !== 'all') {
    $where[] = "category_id = ?";
    $params[] = $category;
}

if ($search) {
    $where[] = "(title LIKE ? OR content LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Paginação
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Buscar notícias
$query = "SELECT n.*, u.full_name as author_name 
          FROM blog_posts n 
          LEFT JOIN users u ON n.author_id = u.id 
          {$whereClause}
          ORDER BY n.created_at DESC 
          LIMIT {$perPage} OFFSET {$offset}";

$news = $db->fetchAll($query, $params);

// Contar total
$countQuery = "SELECT COUNT(*) as total FROM blog_posts {$whereClause}";
$total = $db->fetch($countQuery, $params)['total'] ?? 0;
$totalPages = ceil($total / $perPage);

// Estatísticas
$stats = [
    'total' => $db->fetch("SELECT COUNT(*) as count FROM blog_posts")['count'],
    'published' => $db->fetch("SELECT COUNT(*) as count FROM blog_posts WHERE status = 'published'")['count'],
    'draft' => $db->fetch("SELECT COUNT(*) as count FROM blog_posts WHERE status = 'draft'")['count'],
    'archived' => $db->fetch("SELECT COUNT(*) as count FROM blog_posts WHERE status = 'archived'")['count'],
];

showFlashMessages();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Notícias</h2>
        <p class="text-muted mb-0">Gerencie todas as notícias do site</p>
    </div>
    <div>
        <a href="<?= url('admin/news/news-create.php') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nova Notícia
        </a>
    </div>
</div>

<!-- Estatísticas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total</p>
                        <h3 class="mb-0"><?= number_format($stats['total']) ?></h3>
                    </div>
                    <div class="text-primary">
                        <i class="fas fa-newspaper fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Publicadas</p>
                        <h3 class="mb-0 text-success"><?= number_format($stats['published']) ?></h3>
                    </div>
                    <div class="text-success">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Rascunhos</p>
                        <h3 class="mb-0 text-warning"><?= number_format($stats['draft']) ?></h3>
                    </div>
                    <div class="text-warning">
                        <i class="fas fa-edit fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Arquivadas</p>
                        <h3 class="mb-0 text-info"><?= number_format($stats['archived']) ?></h3>
                    </div>
                    <div class="text-info">
                        <i class="fas fa-archive fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Buscar</label>
                <input type="text" name="search" class="form-control" placeholder="Título ou conteúdo..." value="<?= escape($search) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Todos</option>
                    <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Publicadas</option>
                    <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Rascunhos</option>
                    <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>>Arquivadas</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Categoria</label>
                <select name="category" class="form-control">
                    <option value="all" <?= $category === 'all' ? 'selected' : '' ?>>Todas</option>
                    <option value="lancamentos" <?= $category === 'lancamentos' ? 'selected' : '' ?>>Lançamentos</option>
                    <option value="tutoriais" <?= $category === 'tutoriais' ? 'selected' : '' ?>>Tutoriais</option>
                    <option value="industria" <?= $category === 'industria' ? 'selected' : '' ?>>Indústria</option>
                    <option value="eventos" <?= $category === 'eventos' ? 'selected' : '' ?>>Eventos</option>
                    <option value="entrevistas" <?= $category === 'entrevistas' ? 'selected' : '' ?>>Entrevistas</option>
                    <option value="comunidade" <?= $category === 'comunidade' ? 'selected' : '' ?>>Comunidade</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Notícias -->
<div class="card">
    <div class="card-body">
        <?php if (empty($news)): ?>
            <div class="text-center py-5">
                <i class="fas fa-newspaper fa-4x text-muted mb-3"></i>
                <h4>Nenhuma notícia encontrada</h4>
                <p class="text-muted">Comece criando sua primeira notícia</p>
                <a href="<?= url('admin/news/news-create.php') ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Criar Notícia
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 60px;">Imagem</th>
                            <th>Título</th>
                            <th style="width: 120px;">Categoria</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 150px;">Autor</th>
                            <th style="width: 150px;">Data</th>
                            <th style="width: 150px;" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($news as $item): ?>
                        <tr>
                            <td>
                                <?php if (!empty($item['featured_image'])): ?>
                                    <img src="<?= escape($item['featured_image']) ?>" alt="Thumbnail" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div>
                                    <strong><?= escape($item['title']) ?></strong>
                                    <?php if ($item['is_featured']): ?>
                                        <span class="badge bg-warning text-dark ms-1">Destaque</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($item['excerpt'])): ?>
                                    <small class="text-muted d-block" style="max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?= escape($item['excerpt']) ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($item['category']): ?>
                                    <span class="badge bg-secondary"><?= ucfirst(escape($item['category'])) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $statusColors = [
                                    'published' => 'success',
                                    'draft' => 'warning',
                                    'scheduled' => 'info',
                                    'archived' => 'secondary'
                                ];
                                $statusLabels = [
                                    'published' => 'Publicada',
                                    'draft' => 'Rascunho',
                                    'scheduled' => 'Agendada',
                                    'archived' => 'Arquivada'
                                ];
                                $color = $statusColors[$item['status']] ?? 'secondary';
                                $label = $statusLabels[$item['status']] ?? $item['status'];
                                ?>
                                <span class="badge bg-<?= $color ?>"><?= $label ?></span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= escape($item['author_name'] ?? 'Desconhecido') ?>
                                </small>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= date('d/m/Y', strtotime($item['created_at'])) ?>
                                    <br>
                                    <?= date('H:i', strtotime($item['created_at'])) ?>
                                </small>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <?php if ($item['status'] === 'published'): ?>
                                        <a href="<?= url('noticias/' . $item['slug']) ?>" class="btn btn-outline-primary" title="Ver" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= url('admin/news/news-edit.php?id=' . $item['id']) ?>" class="btn btn-outline-secondary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $item['id'] ?>, '<?= escape($item['title']) ?>')" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?>&status=<?= $status ?>&category=<?= $category ?>&search=<?= urlencode($search) ?>">
                                Anterior
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&status=<?= $status ?>&category=<?= $category ?>&search=<?= urlencode($search) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&status=<?= $status ?>&category=<?= $category ?>&search=<?= urlencode($search) ?>">
                                Próximo
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmDelete(id, title) {
    if (confirm(`Tem certeza que deseja excluir a notícia "${title}"?\n\nEsta ação não pode ser desfeita.`)) {
        window.location.href = '<?= url('admin/news/news-delete.php') ?>?id=' + id;
    }
}
</script>

<?php include '../includes/footer.php'; ?>
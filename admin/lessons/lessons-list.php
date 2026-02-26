<?php
$pageTitle = 'Gerenciar Lições';
include '../includes/header.php';

$db = Database::getInstance();

// Parâmetros de filtro
$moduleId = intval($_GET['module_id'] ?? 0);
$courseId = intval($_GET['course_id'] ?? 0);
$status = $_GET['status'] ?? 'all';
$contentType = $_GET['content_type'] ?? 'all';
$search = trim($_GET['search'] ?? '');

// Buscar informações do curso e módulo se fornecidos
$course = null;
$module = null;

if ($courseId > 0) {
    $course = $db->fetch("SELECT * FROM courses WHERE id = ?", [$courseId]);
}

if ($moduleId > 0) {
    $module = $db->fetch("SELECT * FROM course_modules WHERE id = ?", [$moduleId]);
}

// Construir query
$where = [];
$params = [];

if ($moduleId > 0) {
    $where[] = "l.module_id = ?";
    $params[] = $moduleId;
}

if ($courseId > 0 && $moduleId == 0) {
    // Se curso selecionado mas módulo não, pegar todas as lições do curso
    $where[] = "m.course_id = ?";
    $params[] = $courseId;
}

if ($status === 'published') {
    $where[] = "l.is_published = 1";
} elseif ($status === 'draft') {
    $where[] = "l.is_published = 0";
}

if ($contentType !== 'all') {
    $where[] = "l.content_type = ?";
    $params[] = $contentType;
}

if ($search) {
    $where[] = "(l.title LIKE ? OR l.content LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Buscar lições
$query = "SELECT l.*, 
          m.title as module_title,
          c.title as course_title,
          c.id as course_id
          FROM course_lessons l 
          LEFT JOIN course_modules m ON l.module_id = m.id
          LEFT JOIN courses c ON m.course_id = c.id
          {$whereClause}
          ORDER BY l.sort_order ASC, l.created_at DESC";

$lessons = $db->fetchAll($query, $params);

// Estatísticas
$statsWhere = $moduleId > 0 ? "WHERE module_id = {$moduleId}" : "";
$stats = [
    'total' => $db->fetch("SELECT COUNT(*) as count FROM course_lessons {$statsWhere}")['count'],
    'published' => $db->fetch("SELECT COUNT(*) as count FROM course_lessons {$statsWhere} " . ($moduleId > 0 ? 'AND' : 'WHERE') . " is_published = 1")['count'],
    'draft' => $db->fetch("SELECT COUNT(*) as count FROM course_lessons {$statsWhere} " . ($moduleId > 0 ? 'AND' : 'WHERE') . " is_published = 0")['count'],
    'free' => $db->fetch("SELECT COUNT(*) as count FROM course_lessons {$statsWhere} " . ($moduleId > 0 ? 'AND' : 'WHERE') . " is_free_preview = 1")['count'],
];

showFlashMessages();
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard.php') ?>">Dashboard</a></li>
        <?php if ($course): ?>
            <li class="breadcrumb-item"><a href="<?= url('admin/courses.php') ?>">Cursos</a></li>
            <li class="breadcrumb-item"><a href="<?= url('admin/modules/modules.php?course_id=' . $courseId) ?>"><?= escape($course['title']) ?></a></li>
        <?php endif; ?>
        <?php if ($module): ?>
            <li class="breadcrumb-item active"><?= escape($module['title']) ?></li>
        <?php else: ?>
            <li class="breadcrumb-item active">Todas as Lições</li>
        <?php endif; ?>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>
            Lições 
            <?php if ($module): ?>
                <small class="text-muted">- <?= escape($module['title']) ?></small>
            <?php endif; ?>
        </h2>
        <p class="text-muted mb-0">Gerencie as lições do curso</p>
    </div>
    <div>
        <?php if ($moduleId > 0): ?>
            <a href="<?= url('admin/lessons/create.php?module_id=' . $moduleId . '&course_id=' . $courseId) ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nova Lição
            </a>
        <?php else: ?>
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-plus"></i> Nova Lição
                </button>
                <div class="dropdown-menu">
                    <h6 class="dropdown-header">Selecione um módulo primeiro</h6>
                    <a class="dropdown-item" href="<?= url('admin/modules/modules.php?course_id=' . ($courseId ?: '')) ?>">
                        <i class="fas fa-folder"></i> Ver Módulos
                    </a>
                </div>
            </div>
        <?php endif; ?>
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
                        <i class="fas fa-graduation-cap fa-2x"></i>
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
                        <p class="text-muted mb-1">Gratuitas</p>
                        <h3 class="mb-0 text-info"><?= number_format($stats['free']) ?></h3>
                    </div>
                    <div class="text-info">
                        <i class="fas fa-unlock fa-2x"></i>
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
            <?php if ($moduleId > 0): ?>
                <input type="hidden" name="module_id" value="<?= $moduleId ?>">
            <?php endif; ?>
            <?php if ($courseId > 0): ?>
                <input type="hidden" name="course_id" value="<?= $courseId ?>">
            <?php endif; ?>
            
            <div class="col-md-4">
                <label class="form-label">Buscar</label>
                <input type="text" name="search" class="form-control" placeholder="Título ou resumo..." value="<?= escape($search) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Todos</option>
                    <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Publicadas</option>
                    <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Rascunhos</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipo de Conteúdo</label>
                <select name="content_type" class="form-control">
                    <option value="all" <?= $contentType === 'all' ? 'selected' : '' ?>>Todos</option>
                    <option value="text" <?= $contentType === 'text' ? 'selected' : '' ?>>Texto</option>
                    <option value="video" <?= $contentType === 'video' ? 'selected' : '' ?>>Vídeo</option>
                    <option value="quiz" <?= $contentType === 'quiz' ? 'selected' : '' ?>>Quiz</option>
                    <option value="exercise" <?= $contentType === 'exercise' ? 'selected' : '' ?>>Exercício</option>
                    <option value="project" <?= $contentType === 'project' ? 'selected' : '' ?>>Projeto</option>
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

<!-- Lista de Lições -->
<div class="card">
    <div class="card-body">
        <?php if (empty($lessons)): ?>
            <div class="text-center py-5">
                <i class="fas fa-book-open fa-4x text-muted mb-3"></i>
                <h4>Nenhuma lição encontrada</h4>
                <?php if ($moduleId > 0): ?>
                    <p class="text-muted">Comece criando a primeira lição deste módulo</p>
                    <a href="<?= url('admin/lessons/create.php?module_id=' . $moduleId . '&course_id=' . $courseId) ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Criar Lição
                    </a>
                <?php else: ?>
                    <p class="text-muted">Selecione um módulo para gerenciar suas lições</p>
                    <a href="<?= url('admin/modules/modules.php?course_id=' . ($courseId ?: '')) ?>" class="btn btn-primary">
                        <i class="fas fa-folder"></i> Ver Módulos
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 30px;">#</th>
                            <th>Título</th>
                            <th style="width: 120px;">Tipo</th>
                            <?php if ($moduleId == 0): ?>
                                <th style="width: 200px;">Módulo/Curso</th>
                            <?php endif; ?>
                            <th style="width: 80px;">Duração</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 150px;" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="sortable-lessons">
                        <?php foreach ($lessons as $lesson): ?>
                        <tr data-id="<?= $lesson['id'] ?>">
                            <td>
                                <i class="fas fa-grip-vertical text-muted" style="cursor: move;" title="Arrastar para reordenar"></i>
                                <span class="ms-2"><?= $lesson['order_position'] ?></span>
                            </td>
                            <td>
                                <div>
                                    <strong><?= escape($lesson['title']) ?></strong>
                                    <?php if ($lesson['is_free_preview']): ?>
                                        <span class="badge bg-info text-white ms-1">Grátis</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($lesson['summary'])): ?>
                                    <small class="text-muted d-block" style="max-width: 400px;">
                                        <?= escape($lesson['summary']) ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $typeIcons = [
                                    'text' => 'fa-file-alt',
                                    'video' => 'fa-video',
                                    'quiz' => 'fa-question-circle',
                                    'exercise' => 'fa-code',
                                    'project' => 'fa-project-diagram',
                                    'live' => 'fa-broadcast-tower',
                                    'download' => 'fa-download'
                                ];
                                $icon = $typeIcons[$lesson['content_type']] ?? 'fa-file';
                                ?>
                                <i class="fas <?= $icon ?>"></i>
                                <span class="ms-1"><?= ucfirst($lesson['content_type']) ?></span>
                            </td>
                            <?php if ($moduleId == 0): ?>
                            <td>
                                <small class="text-muted">
                                    <div><?= escape($lesson['module_title']) ?></div>
                                    <div class="text-muted"><?= escape($lesson['course_title']) ?></div>
                                </small>
                            </td>
                            <?php endif; ?>
                            <td>
                                <?php if ($lesson['duration_minutes'] > 0): ?>
                                    <small>
                                        <i class="fas fa-clock"></i>
                                        <?= $lesson['duration_minutes'] ?> min
                                    </small>
                                <?php else: ?>
                                    <small class="text-muted">-</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($lesson['is_published']): ?>
                                    <span class="badge bg-success">Publicada</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Rascunho</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <?php if ($lesson['is_published']): ?>
                                        <a href="<?= url('curso/' . $lesson['course_id'] . '/licao/' . $lesson['id']) ?>" class="btn btn-outline-primary" title="Ver" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= url('admin/lessons/edit.php?id=' . $lesson['id'] . '&module_id=' . $lesson['module_id'] . '&course_id=' . ($lesson['course_id'] ?? $courseId)) ?>" class="btn btn-outline-secondary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $lesson['id'] ?>, '<?= escape($lesson['title']) ?>')" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($moduleId > 0): ?>
            <!-- Informação sobre Reordenamento -->
            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle"></i>
                <strong>Dica:</strong> Arraste as lições para reorganizar a ordem. As mudanças são salvas automaticamente.
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function confirmDelete(id, title) {
    if (confirm(`Tem certeza que deseja excluir a lição "${title}"?\n\nEsta ação não pode ser desfeita.`)) {
        const moduleId = <?= $moduleId ?>;
        const courseId = <?= $courseId ?>;
        window.location.href = `<?= url('admin/lessons/delete.php') ?>?id=${id}&module_id=${moduleId}&course_id=${courseId}`;
    }
}

// Implementar reordenamento drag & drop
<?php if ($moduleId > 0 && !empty($lessons)): ?>
const sortable = new Sortable(document.getElementById('sortable-lessons'), {
    animation: 150,
    handle: '.fa-grip-vertical',
    onEnd: function(evt) {
        const items = Array.from(evt.to.children).map(row => row.dataset.id);
        
        // Salvar nova ordem via AJAX
        fetch('<?= url('admin/lessons/reorder.php') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                lesson_ids: items
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Atualizar números de ordem na interface
                evt.to.querySelectorAll('tr').forEach((row, index) => {
                    row.querySelector('td:first-child span').textContent = index + 1;
                });
            } else {
                alert('Erro ao reordenar lições');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao reordenar lições');
        });
    }
});
<?php endif; ?>
</script>

<?php include '../includes/footer.php'; ?>
<?php
// admin/modules.php - Gerenciar Módulos de um Curso

$pageTitle = 'Módulos do Curso';
include '../includes/header.php';

$db = Database::getInstance();
$courseModel = new Course();

$courseId = intval($_GET['course_id'] ?? 0);
$course = $courseModel->find($courseId);
if (!$course) {
    flash('error', 'Curso não encontrado.');
    redirect(url('admin/courses/courses.php'));
}

adminRequireCourseAccess($course);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $data = [
            'course_id' => $courseId,
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'sort_order' => intval($_POST['order_index'] ?? 0),
            'xp_reward' => intval($_POST['xp_reward'] ?? 50),
            'duration_minutes' => intval($_POST['estimated_minutes'] ?? 0),
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
            'is_free_preview' => isset($_POST['is_free_preview']) ? 1 : 0,
            'unlock_after_module' => intval($_POST['unlock_after_module'] ?? 0) ?: null,
        ];
        if (!$data['title']) {
            flash('error', 'Informe o título do módulo.');
        } else {
            $db->insert('course_modules', $data);
            flash('success', 'Módulo criado com sucesso!');
        }
        redirect(url('admin/modules/modules.php?course_id=' . $courseId));
    }

    if ($action === 'update') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'sort_order' => intval($_POST['order_index'] ?? 0),
                'xp_reward' => intval($_POST['xp_reward'] ?? 50),
                'duration_minutes' => intval($_POST['estimated_minutes'] ?? 0),
                'is_published' => isset($_POST['is_published']) ? 1 : 0,
                'is_free_preview' => isset($_POST['is_free_preview']) ? 1 : 0,
                'unlock_after_module' => intval($_POST['unlock_after_module'] ?? 0) ?: null,
            ];
            $db->update('course_modules', $data, 'id = :id', ['id' => $id]);
            flash('success', 'Módulo atualizado!');
        }
        redirect(url('admin/modules/modules.php?course_id=' . $courseId));
    }

    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $db->delete('course_modules', 'id = :id', ['id' => $id]);
            flash('success', 'Módulo removido!');
        }
        redirect(url('admin/modules/modules.php?course_id=' . $courseId));
    }
}

$modules = $db->fetchAll("SELECT * FROM course_modules WHERE course_id = ? ORDER BY sort_order ASC", [$courseId]);
?>

<?= showFlashMessages() ?>

<div class="d-flex justify-between align-center mb-4">
    <div>
        <a href="<?= url('admin/courses/courses.php') ?>" class="btn btn-secondary">← Voltar</a>
    </div>
    <div class="d-flex align-center gap-2">
        <h2><?= escape($course['title']) ?></h2>
        <span class="badge badge-primary"><?= count($modules) ?> módulos</span>
    </div>
    <button class="btn btn-success" onclick="document.getElementById('create-module').removeAttribute('hidden')">Novo Módulo</button>
    </div>

<!-- Criar Módulo -->
<div id="create-module" class="card mb-4" hidden>
    <div class="card-body">
        <h3 class="card-title">Criar Módulo</h3>
        <form method="POST" class="grid-cols-2 gap-2">
            <input type="hidden" name="action" value="create">
            <label>Título
                <input type="text" name="title" class="form-control" required>
            </label>
            <label>Descrição
                <input type="text" name="description" class="form-control">
            </label>
            <div class="d-flex gap-2">
                <label>Ordem
                    <input type="number" name="order_index" class="form-control" value="<?= count($modules) ?>">
                </label>
                <label>XP ao Concluir
                    <input type="number" name="xp_reward" class="form-control" value="50">
                </label>
                <label>Minutos Estimados
                    <input type="number" name="estimated_minutes" class="form-control" value="0">
                </label>
            </div>
            <div class="d-flex align-center gap-2 mt-2">
                <label class="d-flex align-center gap-1">
                    <input type="checkbox" name="is_published" checked> Publicado
                </label>
                <label class="d-flex align-center gap-1">
                    <input type="checkbox" name="is_free_preview"> Preview Grátis
                </label>
            </div>
            <div class="mt-3 grid-full">
                <button type="submit" class="btn btn-primary">Criar Módulo</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('create-module').setAttribute('hidden', 'true')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<div class="admin-table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Ordem</th>
                <th>Título</th>
                <th>Lições</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($modules as $m): ?>
            <tr>
                <td><?= $m['sort_order'] ?></td>
                <td>
                    <strong><?= escape($m['title']) ?></strong>
                    <div class="text-muted small"><?= escape($m['description']) ?></div>
                </td>
                <td>
                    <?php
                    $lessonCount = $db->fetch("SELECT COUNT(*) as count FROM course_lessons WHERE module_id = ?", [$m['id']])['count'];
                    echo $lessonCount;
                    ?>
                </td>
                <td>
                    <span class="badge <?= $m['is_published'] ? 'badge-success' : 'badge-warning' ?>">
                        <?= $m['is_published'] ? 'Publicado' : 'Rascunho' ?>
                    </span>
                </td>
                <td>
                    <div class="admin-actions">
                        <a href="<?= url('admin/lessons/lessons.php?module_id=' . $m['id'] . '&course_id=' . $courseId) ?>" class="btn-action" title="Lições">📖</a>
                        <button class="btn-action edit" onclick="editModule(<?= htmlspecialchars(json_encode($m)) ?>)">✏️</button>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Excluir módulo e todas as suas lições?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                            <button type="submit" class="btn-action delete">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal de Edição (Simples) -->
<div id="edit-module-container" hidden>
    <div class="modal-backdrop" onclick="closeEdit()"></div>
    <div class="modal-content card" style="max-width: 600px;">
        <div class="card-body">
            <h3>Editar Módulo</h3>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit-id">
                
                <div class="mb-2">
                    <label>Título</label>
                    <input type="text" name="title" id="edit-title" class="form-control" required>
                </div>
                
                <div class="mb-2">
                    <label>Descrição</label>
                    <textarea name="description" id="edit-description" class="form-control"></textarea>
                </div>
                
                <div class="grid-cols-2 gap-2">
                    <label>Ordem
                        <input type="number" name="order_index" id="edit-order" class="form-control">
                    </label>
                    <label>XP ao Concluir
                        <input type="number" name="xp_reward" id="edit-xp" class="form-control">
                    </label>
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEdit()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editModule(m) {
    document.getElementById('edit-id').value = m.id;
    document.getElementById('edit-title').value = m.title;
    document.getElementById('edit-description').value = m.description || '';
    document.getElementById('edit-order').value = m.sort_order;
    document.getElementById('edit-xp').value = m.xp_reward;
    document.getElementById('edit-module-container').removeAttribute('hidden');
}
function closeEdit() {
    document.getElementById('edit-module-container').setAttribute('hidden', 'true');
}
</script>

<?php include '../includes/footer.php'; ?>

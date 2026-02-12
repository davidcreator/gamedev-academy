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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $data = [
            'course_id' => $courseId,
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'order_index' => intval($_POST['order_index'] ?? 0),
            'xp_reward' => intval($_POST['xp_reward'] ?? 50),
            'estimated_minutes' => intval($_POST['estimated_minutes'] ?? 0),
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
            'is_free_preview' => isset($_POST['is_free_preview']) ? 1 : 0,
            'unlock_after_module' => intval($_POST['unlock_after_module'] ?? 0) ?: null,
        ];
        if (!$data['title']) {
            flash('error', 'Informe o título do módulo.');
        } else {
            $db->insert('modules', $data);
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
                'order_index' => intval($_POST['order_index'] ?? 0),
                'xp_reward' => intval($_POST['xp_reward'] ?? 50),
                'estimated_minutes' => intval($_POST['estimated_minutes'] ?? 0),
                'is_published' => isset($_POST['is_published']) ? 1 : 0,
                'is_free_preview' => isset($_POST['is_free_preview']) ? 1 : 0,
                'unlock_after_module' => intval($_POST['unlock_after_module'] ?? 0) ?: null,
            ];
            $db->update('modules', $data, 'id = :id', ['id' => $id]);
            flash('success', 'Módulo atualizado!');
        }
        redirect(url('admin/modules/modules.php?course_id=' . $courseId));
    }

    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $db->delete('modules', 'id = :id', ['id' => $id]);
            flash('success', 'Módulo removido!');
        }
        redirect(url('admin/modules/modules.php?course_id=' . $courseId));
    }
}

$modules = $courseModel->getModules($courseId);
?>

<?= showFlashMessages() ?>

<div class="d-flex justify-between align-center mb-4">
    <div>
        <a href="<?= url('admin/courses/course-edit.php?id=' . $course['id']) ?>" class="btn btn-secondary">← Voltar</a>
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
                <label>XP
                    <input type="number" name="xp_reward" class="form-control" value="50">
                </label>
                <label>Minutos
                    <input type="number" name="estimated_minutes" class="form-control" value="0">
                </label>
            </div>
            <div class="d-flex gap-2">
                <label class="d-flex align-center gap-1">
                    <input type="checkbox" name="is_published"> Publicado
                </label>
                <label class="d-flex align-center gap-1">
                    <input type="checkbox" name="is_free_preview"> Prévia grátis
                </label>
            </div>
            <label>Desbloquear após módulo (ID)
                <input type="number" name="unlock_after_module" class="form-control" min="0">
            </label>
            <div class="mt-2">
                <button class="btn btn-success" type="submit">Salvar</button>
                <button class="btn btn-secondary" type="button" onclick="this.closest('#create-module').setAttribute('hidden','')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Módulos -->
<div class="admin-table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Ordem</th>
                <th>Lições</th>
                <th>XP</th>
                <th>Minutos</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($modules as $m): ?>
            <tr>
                <td>#<?= $m['id'] ?></td>
                <td><?= escape($m['title']) ?></td>
                <td><?= intval($m['order_index']) ?></td>
                <td><?= intval($m['total_lessons'] ?? 0) ?></td>
                <td><?= intval($m['xp_reward']) ?></td>
                <td><?= intval($m['estimated_minutes']) ?></td>
                <td>
                    <span class="badge <?= $m['is_published'] ? 'badge-success' : 'badge-warning' ?>">
                        <?= $m['is_published'] ? 'Publicado' : 'Rascunho' ?>
                    </span>
                </td>
                <td>
                    <div class="admin-actions">
                        <button class="btn-action edit" title="Editar" onclick="toggleEdit(<?= $m['id'] ?>)">✏️</button>
                        <a href="<?= url('admin/lessons/lessons.php?module_id=' . $m['id'] . '&course_id=' . $courseId) ?>" class="btn-action" title="Lições">📖</a>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Remover este módulo?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                            <button class="btn-action delete" title="Deletar">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
            <tr id="edit-<?= $m['id'] ?>" hidden>
                <td colspan="8">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Editar Módulo #<?= $m['id'] ?></h4>
                            <form method="POST" class="grid-cols-2 gap-2">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                <label>Título
                                    <input type="text" name="title" class="form-control" value="<?= escape($m['title']) ?>" required>
                                </label>
                                <label>Descrição
                                    <input type="text" name="description" class="form-control" value="<?= escape($m['description'] ?? '') ?>">
                                </label>
                                <div class="d-flex gap-2">
                                    <label>Ordem
                                        <input type="number" name="order_index" class="form-control" value="<?= intval($m['order_index']) ?>">
                                    </label>
                                    <label>XP
                                        <input type="number" name="xp_reward" class="form-control" value="<?= intval($m['xp_reward']) ?>">
                                    </label>
                                    <label>Minutos
                                        <input type="number" name="estimated_minutes" class="form-control" value="<?= intval($m['estimated_minutes']) ?>">
                                    </label>
                                </div>
                                <div class="d-flex gap-2">
                                    <label class="d-flex align-center gap-1">
                                        <input type="checkbox" name="is_published" <?= $m['is_published'] ? 'checked' : '' ?>> Publicado
                                    </label>
                                    <label class="d-flex align-center gap-1">
                                        <input type="checkbox" name="is_free_preview" <?= $m['is_free_preview'] ? 'checked' : '' ?>> Prévia grátis
                                    </label>
                                </div>
                                <label>Desbloquear após módulo (ID)
                                    <input type="number" name="unlock_after_module" class="form-control" value="<?= intval($m['unlock_after_module'] ?? 0) ?>">
                                </label>
                                <div class="mt-2">
                                    <button class="btn btn-success" type="submit">Salvar</button>
                                    <button class="btn btn-secondary" type="button" onclick="toggleEdit(<?= $m['id'] ?>)">Cancelar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function toggleEdit(id) {
    const tr = document.getElementById('edit-' + id);
    if (tr.hasAttribute('hidden')) tr.removeAttribute('hidden'); else tr.setAttribute('hidden','');
}
</script>

<?php include '../includes/footer.php'; ?>

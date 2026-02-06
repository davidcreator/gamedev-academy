<?php
// admin/lessons.php - Gerenciar Lições de um Módulo

$pageTitle = 'Lições do Módulo';
include 'includes/header.php';

$db = Database::getInstance();
$courseModel = new Course();

$moduleId = intval($_GET['module_id'] ?? 0);
$courseId = intval($_GET['course_id'] ?? 0);

$module = $db->fetch("SELECT * FROM modules WHERE id = ?", [$moduleId]);
$course = $courseModel->find($courseId);
if (!$module || !$course) {
    flash('error', 'Módulo ou curso não encontrado.');
    redirect(url('admin/courses.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $data = [
            'module_id' => $moduleId,
            'title' => trim($_POST['title'] ?? ''),
            'content_type' => $_POST['content_type'] ?? 'text',
            'order_index' => intval($_POST['order_index'] ?? 0),
            'xp_reward' => intval($_POST['xp_reward'] ?? 10),
            'coin_reward' => intval($_POST['coin_reward'] ?? 1),
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
            'is_free_preview' => isset($_POST['is_free_preview']) ? 1 : 0,
            'video_url' => trim($_POST['video_url'] ?? ''),
            'video_provider' => $_POST['video_provider'] ?? 'youtube',
            'duration_minutes' => intval($_POST['duration_minutes'] ?? 0),
        ];
        if (!$data['title']) {
            flash('error', 'Informe o título da lição.');
        } else {
            $db->insert('lessons', $data);
            flash('success', 'Lição criada com sucesso!');
        }
        redirect(url('admin/lessons.php?module_id=' . $moduleId . '&course_id=' . $courseId));
    }

    if ($action === 'update') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'content_type' => $_POST['content_type'] ?? 'text',
                'order_index' => intval($_POST['order_index'] ?? 0),
                'xp_reward' => intval($_POST['xp_reward'] ?? 10),
                'coin_reward' => intval($_POST['coin_reward'] ?? 1),
                'is_published' => isset($_POST['is_published']) ? 1 : 0,
                'is_free_preview' => isset($_POST['is_free_preview']) ? 1 : 0,
                'video_url' => trim($_POST['video_url'] ?? ''),
                'video_provider' => $_POST['video_provider'] ?? 'youtube',
                'duration_minutes' => intval($_POST['duration_minutes'] ?? 0),
            ];
            $db->update('lessons', $data, 'id = :id', ['id' => $id]);
            flash('success', 'Lição atualizada!');
        }
        redirect(url('admin/lessons.php?module_id=' . $moduleId . '&course_id=' . $courseId));
    }

    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $db->delete('lessons', 'id = :id', ['id' => $id]);
            flash('success', 'Lição removida!');
        }
        redirect(url('admin/lessons.php?module_id=' . $moduleId . '&course_id=' . $courseId));
    }
}

$lessons = $courseModel->getLessons($moduleId);
?>

<?= showFlashMessages() ?>

<div class="d-flex justify-between align-center mb-4">
    <div class="d-flex gap-2">
        <a href="<?= url('admin/modules.php?course_id=' . $courseId) ?>" class="btn btn-secondary">← Voltar</a>
        <h2><?= escape($course['title']) ?> · <?= escape($module['title']) ?></h2>
    </div>
    <button class="btn btn-success" onclick="document.getElementById('create-lesson').removeAttribute('hidden')">Nova Lição</button>
    </div>

<!-- Criar Lição -->
<div id="create-lesson" class="card mb-4" hidden>
    <div class="card-body">
        <h3 class="card-title">Criar Lição</h3>
        <form method="POST" class="grid-cols-2 gap-2">
            <input type="hidden" name="action" value="create">
            <label>Título
                <input type="text" name="title" class="form-control" required>
            </label>
            <label>Tipo de Conteúdo
                <select name="content_type" class="form-control">
                    <?php
                    $types = ['text'=>'Texto','video'=>'Vídeo','quiz'=>'Quiz','exercise'=>'Exercício','project'=>'Projeto','live'=>'Live','download'=>'Download'];
                    foreach ($types as $k=>$v): ?>
                        <option value="<?= $k ?>"><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="d-flex gap-2">
                <label>Ordem
                    <input type="number" name="order_index" class="form-control" value="<?= count($lessons) ?>">
                </label>
                <label>XP
                    <input type="number" name="xp_reward" class="form-control" value="10">
                </label>
                <label>Moedas
                    <input type="number" name="coin_reward" class="form-control" value="1">
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
            <div class="d-flex gap-2">
                <label>Vídeo URL
                    <input type="text" name="video_url" class="form-control">
                </label>
                <label>Provedor
                    <select name="video_provider" class="form-control">
                        <?php foreach (['youtube','vimeo','cloudflare','bunny','self'] as $p): ?>
                            <option value="<?= $p ?>"><?= ucfirst($p) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Duração (min)
                    <input type="number" name="duration_minutes" class="form-control" value="0">
                </label>
            </div>
            <div class="mt-2">
                <button class="btn btn-success" type="submit">Salvar</button>
                <button class="btn btn-secondary" type="button" onclick="this.closest('#create-lesson').setAttribute('hidden','')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Lições -->
<div class="admin-table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Tipo</th>
                <th>Ordem</th>
                <th>XP</th>
                <th>Moedas</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lessons as $l): ?>
            <tr>
                <td>#<?= $l['id'] ?></td>
                <td><?= escape($l['title']) ?></td>
                <td><span class="badge badge-secondary"><?= escape($l['content_type']) ?></span></td>
                <td><?= intval($l['order_index']) ?></td>
                <td><?= intval($l['xp_reward']) ?></td>
                <td><?= intval($l['coin_reward']) ?></td>
                <td>
                    <span class="badge <?= $l['is_published'] ? 'badge-success' : 'badge-warning' ?>">
                        <?= $l['is_published'] ? 'Publicado' : 'Rascunho' ?>
                    </span>
                </td>
                <td>
                    <div class="admin-actions">
                        <a href="<?= url('admin/lesson-edit.php?id=' . $l['id'] . '&module_id=' . $moduleId . '&course_id=' . $courseId) ?>" class="btn-action edit" title="Editar">✏️</a>
                        <button class="btn-action edit" title="Editar rápido" onclick="toggleEdit(<?= $l['id'] ?>)">⚙️</button>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Remover esta lição?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $l['id'] ?>">
                            <button class="btn-action delete" title="Deletar">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
            <tr id="edit-<?= $l['id'] ?>" hidden>
                <td colspan="8">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Editar Lição #<?= $l['id'] ?></h4>
                            <form method="POST" class="grid-cols-2 gap-2">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?= $l['id'] ?>">
                                <label>Título
                                    <input type="text" name="title" class="form-control" value="<?= escape($l['title']) ?>" required>
                                </label>
                                <label>Tipo de Conteúdo
                                    <select name="content_type" class="form-control">
                                        <?php
                                        $types = ['text','video','quiz','exercise','project','live','download'];
                                        foreach ($types as $t): ?>
                                            <option value="<?= $t ?>" <?= $l['content_type'] === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <div class="d-flex gap-2">
                                    <label>Ordem
                                        <input type="number" name="order_index" class="form-control" value="<?= intval($l['order_index']) ?>">
                                    </label>
                                    <label>XP
                                        <input type="number" name="xp_reward" class="form-control" value="<?= intval($l['xp_reward']) ?>">
                                    </label>
                                    <label>Moedas
                                        <input type="number" name="coin_reward" class="form-control" value="<?= intval($l['coin_reward']) ?>">
                                    </label>
                                </div>
                                <div class="d-flex gap-2">
                                    <label class="d-flex align-center gap-1">
                                        <input type="checkbox" name="is_published" <?= $l['is_published'] ? 'checked' : '' ?>> Publicado
                                    </label>
                                    <label class="d-flex align-center gap-1">
                                        <input type="checkbox" name="is_free_preview" <?= $l['is_free_preview'] ? 'checked' : '' ?>> Prévia grátis
                                    </label>
                                </div>
                                <div class="d-flex gap-2">
                                    <label>Vídeo URL
                                        <input type="text" name="video_url" class="form-control" value="<?= escape($l['video_url'] ?? '') ?>">
                                    </label>
                                    <label>Provedor
                                        <select name="video_provider" class="form-control">
                                            <?php foreach (['youtube','vimeo','cloudflare','bunny','self'] as $p): ?>
                                                <option value="<?= $p ?>" <?= ($l['video_provider'] ?? 'youtube') === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </label>
                                    <label>Duração (min)
                                        <input type="number" name="duration_minutes" class="form-control" value="<?= intval($l['duration_minutes'] ?? 0) ?>">
                                    </label>
                                </div>
                                <div class="mt-2">
                                    <button class="btn btn-success" type="submit">Salvar</button>
                                    <button class="btn btn-secondary" type="button" onclick="toggleEdit(<?= $l['id'] ?>)">Cancelar</button>
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

<?php include 'includes/footer.php'; ?>

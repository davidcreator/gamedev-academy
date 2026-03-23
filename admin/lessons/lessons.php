<?php
// admin/lessons/lessons.php - Gerenciar Lições de um Módulo

$pageTitle = 'Gerenciar Lições do Módulo';
include '../includes/header.php';

// Lessons DB Config
$db = Database::getInstance();
$courseModel = new Course();

$id = intval($_GET['id'] ?? 0);
$moduleId = intval($_GET['module_id'] ?? 0);
$courseId = intval($_GET['course_id'] ?? 0);

$module = $db->fetch("SELECT * FROM course_modules WHERE id = ?", [$moduleId]);
$course = $courseId > 0 ? $db->fetch("SELECT * FROM courses WHERE id = ?", [$courseId]) : null;

if ($course) {
    adminRequireCourseAccess($course);
}

if (!$module || !$course) {
    flash('error', 'Módulo ou curso não encontrado.');
    redirect(url('admin/courses/courses.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $data = [
            'module_id' => $moduleId,
            'course_id' => $courseId,
            'title' => trim($_POST['title'] ?? ''),
            'slug' => slugify($_POST['title'] ?? ''),
            'content_type' => $_POST['content_type'] ?? 'text',
            'sort_order' => intval($_POST['order_index'] ?? 0),
            'xp_reward' => intval($_POST['xp_reward'] ?? 10),
            'coin_reward' => intval($_POST['coin_reward'] ?? 1),
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
            'is_free_preview' => isset($_POST['is_free_preview']) ? 1 : 0,
            'video_url' => trim($_POST['video_url'] ?? ''),
            'video_provider' => $_POST['video_provider'] ?? 'youtube',
            'video_duration' => intval($_POST['duration_minutes'] ?? 0),
        ];
        if (!$data['title']) {
            flash('error', 'Informe o título da lição.');
        } else {
            $db->insert('course_lessons', $data);
            flash('success', 'Lição criada com sucesso!');
        }
        redirect(url('admin/lessons/lessons.php?module_id=' . $moduleId . '&course_id=' . $courseId));
    }

    if ($action === 'update') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'content_type' => $_POST['content_type'] ?? 'text',
                'sort_order' => intval($_POST['order_index'] ?? 0),
                'xp_reward' => intval($_POST['xp_reward'] ?? 10),
                'coin_reward' => intval($_POST['coin_reward'] ?? 1),
                'is_published' => isset($_POST['is_published']) ? 1 : 0,
                'is_free_preview' => isset($_POST['is_free_preview']) ? 1 : 0,
                'video_url' => trim($_POST['video_url'] ?? ''),
                'video_provider' => $_POST['video_provider'] ?? 'youtube',
                'video_duration' => intval($_POST['duration_minutes'] ?? 0),
            ];
            $db->update('course_lessons', $data, 'id = :id', ['id' => $id]);
            flash('success', 'Lição atualizada!');
        }
        redirect(url('admin/lessons/lessons.php?module_id=' . $moduleId . '&course_id=' . $courseId));
    }

    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $db->delete('course_lessons', 'id = :id', ['id' => $id]);
            flash('success', 'Lição removida!');
        }
        redirect(url('admin/lessons/lessons.php?module_id=' . $moduleId . '&course_id=' . $courseId));
    }
}

// Buscar todas as aulas do módulo
try {
    $stmt = $db->query("SELECT * FROM course_lessons WHERE module_id = ? ORDER BY sort_order ASC", [$moduleId]);
    $lessons = $stmt->fetchAll();
    $total = count($lessons);
} catch(PDOException $e) {
    die("Erro ao buscar aulas: " . $e->getMessage());
}

// Mensagens de sessão
$success = $_SESSION['success_message'] ?? null;
$error = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

showFlashMessages() 
?>
<!-- Funções -->
 <div class="d-flex justify-beteween align-center mb-8">
    <div class="d-flex justify-beteween align-right gap-2">
        <a href="<?= url('admin/modules/modules.php?course_id=' . $courseId) ?>" class="btn btn-secondary">← Voltar </a>
        <h2><?= escape($course['title']) ?> &nbsp;.&nbsp; <?= escape($module['title']) ?>&nbsp;&nbsp;</h2>
    </div>
    <div class="d-flex justify-beteween align-right gap-2">
        <button class="btn btn-success" onclick="document.getElementById('create-lesson').removeAttribute('hidden')">Nova Lição</button>
    </div>
 </div>
 <br>
 <!-- Fim das Funções -->
 <!-- Criar Lição -->
 <div id="create-lesson" class="card mb-4" hidden>
    <div class="card-body">
        <h3 class="card-title">Criar Lição</h3>
        <!-- Inicio do Formulário -->
        <form method="POST" class="grid-cols-2 gap-2">
            <input type="hidden" name="action" value="create">
            <label>Título
                <input type="text" name="title" class="form-control" required>
            </label>            
            <div class="d-flex cols-2 gap-10px">
                <label>Tipo de Conteúdo
                <select name="content_type" class="form-control">
                    <?php
                    $types = ['text'=>'Texto','video'=>'Vídeo','quiz'=>'Quiz','exercise'=>'Exercício','project'=>'Projeto','live'=>'Live','download'=>'Download'];
                    foreach ($types as $k=>$v): ?>
                        <option value="<?= $k ?>"><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
             <label>Ordem
                    <input type="number" name="order_index" class="form-control" value="<?= count($lessons) ?>">
                </label>
                <label>XP
                    <input type="number" name="xp_reward" class="form-control" value="10">
                </label>
                <label>Moedas
                    <input type="number" name="coin_reward" class="form-control" value="1">
                </label>
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
            <div class="d-flex gap-2">
                <label class="d-flex align-center gap-1">
                    <input type="checkbox" name="is_published"> Publicado
                </label>
                <label class="d-flex align-center gap-1">
                    <input type="checkbox" name="is_free_preview"> Prévia grátis
                </label>
                <label class="d-flex align-center gap-1">
                    <button class="btn btn-success" type="submit">Salvar</button>
                </label>
                <label class="d-flex align-center gap-1">
                    <button class="btn btn-secondary" type="button" onclick="this.closest('#create-lesson').setAttribute('hidden','')">Cancelar</button>
                </label>
            </div>            
        </form>
        <!-- Fim do Formulário -->
    </div>
 </div>
 <!-- Fim da Criar Lição -->
 <!-- Inicio da Lista de Lições -->
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
                <td><?= intval($l['sort_order']) ?></td>
                <td><?= intval($l['xp_reward']) ?></td>
                <td><?= intval($l['coin_reward']) ?></td>
                <td>
                    <span class="badge <?= $l['is_published'] ? 'badge-success' : 'badge-warning' ?>">
                        <?= $l['is_published'] ? 'Publicado' : 'Rascunho' ?>
                    </span>
                </td>
                <td>
                    <div class="admin-actions">
                        <a href="<?= url('admin/lessons/lessons-edit.php?id=' . $l['id'] . '&module_id=' . $moduleId . '&course_id=' . $courseId) ?>" class="btn-action edit" title="Editar">✏️</a>
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
                                        <input type="number" name="order_index" class="form-control" value="<?= intval($l['sort_order']) ?>">
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
 <!-- Fim da Lista de Lições -->
<?php include '../includes/footer.php'; ?>

<?php
$pageTitle = 'Editar Lição';
include '../includes/header.php';

$db = Database::getInstance();
$id = intval($_GET['id'] ?? 0);
$moduleId = intval($_GET['module_id'] ?? 0);
$courseId = intval($_GET['course_id'] ?? 0);

// Validar ID
if ($id <= 0) {
    flash('error', 'ID da lição inválido.');
    redirect(url('admin/lessons/lessons-list.php?module_id=' . $moduleId . '&course_id=' . $courseId));
}

// Buscar lição com informações do módulo e curso
$lesson = $db->fetch("
    SELECT l.*, 
           m.title as module_title,
           m.id as module_id,
           c.title as course_title,
           c.id as course_id
    FROM course_lessons l
    LEFT JOIN course_modules m ON l.module_id = m.id
    LEFT JOIN courses c ON m.course_id = c.id
    WHERE l.id = ?
", [$id]);

$courseAccess = $lesson
    ? $db->fetch("SELECT id, instructor_id FROM courses WHERE id = ?", [$lesson['course_id']])
    : null;

if (!$lesson) {
    flash('error', 'Lição não encontrada.');
    redirect(url('admin/lessons/lessons-list.php?module_id=' . $moduleId . '&course_id=' . $courseId));
}

// Atualizar IDs se não foram passados na URL
if ($courseAccess) {
    adminRequireCourseAccess($courseAccess);
}

if ($moduleId == 0) {
    $moduleId = $lesson['module_id'];
}
if ($courseId == 0) {
    $courseId = $lesson['course_id'];
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validar campos obrigatórios
    $title = trim($_POST['title'] ?? '');
    if (empty($title)) {
        $errors[] = 'O título é obrigatório.';
    }
    
    $data = [
        'title' => $title,
        'summary' => trim($_POST['summary'] ?? ''),
        'content_type' => $_POST['content_type'] ?? 'text',
        'content' => $_POST['content'] ?? '',
        'video_url' => trim($_POST['video_url'] ?? ''),
        'video_provider' => $_POST['video_provider'] ?? 'youtube',
        'video_duration' => intval($_POST['duration_minutes'] ?? 0),
        'attachment_url' => trim($_POST['attachment_url'] ?? ''),
        'is_published' => isset($_POST['is_published']) ? 1 : 0,
        'is_free_preview' => isset($_POST['is_free_preview']) ? 1 : 0,
    ];
    
    // Validar URL de vídeo se fornecida
    if (!empty($data['video_url']) && !filter_var($data['video_url'], FILTER_VALIDATE_URL)) {
        $errors[] = 'URL do vídeo inválida.';
    }
    
    if (empty($errors)) {
        $db->update('course_lessons', $data, 'id = :id', ['id' => $id]);
        flash('success', 'Lição atualizada com sucesso!');
        // Recarregar dados atualizados
        $lesson = $db->fetch("
            SELECT l.*, 
                   m.title as module_title,
                   m.id as module_id,
                   c.title as course_title,
                   c.id as course_id
            FROM course_lessons l
            LEFT JOIN course_modules m ON l.module_id = m.id
            LEFT JOIN courses c ON m.course_id = c.id
            WHERE l.id = ?
        ", [$id]);
    } else {
        foreach ($errors as $error) {
            flash('error', $error);
        }
    }
}

// Renderizar estilos do EditorJS
EditorJSLoader::renderStyles();
?>

<?= showFlashMessages() ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('admin/dashboard.php') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('admin/courses/courses.php') ?>">Cursos</a></li>
                <li class="breadcrumb-item"><a href="<?= url('admin/modules/modules.php?course_id=' . $courseId) ?>"><?= escape($lesson['course_title']) ?></a></li>
                <li class="breadcrumb-item"><a href="<?= url('admin/lessons/lessons-list.php?module_id=' . $moduleId . '&course_id=' . $courseId) ?>"><?= escape($lesson['module_title']) ?></a></li>
                <li class="breadcrumb-item active">Editar Lição</li>
            </ol>
        </nav>
        <div class="d-flex gap-2">
            <a href="<?= url('admin/lessons/lessons-list.php?module_id=' . $moduleId . '&course_id=' . $courseId) ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Voltar
            </a>
            <?php if ($lesson['is_published']): ?>
                <a href="<?= url('courses/lesson.php?id=' . $id) ?>" target="_blank" class="btn btn-outline-primary">
                    <i class="fas fa-eye me-1"></i> Ver no Site
                </a>
            <?php endif; ?>
        </div>
    </div>

    <form method="POST" id="lessonForm">
        <div class="row">
            <!-- Coluna Principal -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-file-alt me-2"></i>Conteúdo da Lição</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Título da Lição <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-lg border-2" value="<?= escape($lesson['title']) ?>" required placeholder="Ex: Introdução ao Unity">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Resumo da Lição</label>
                            <textarea name="summary" class="form-control" rows="2" placeholder="Uma breve descrição do que será aprendido..."><?= escape($lesson['summary'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold">Corpo da Lição</label>
                            <div id="editorjs" class="border rounded bg-light" style="min-height: 400px;"></div>
                            <textarea name="content" id="content" class="d-none"><?= $lesson['content'] ?? '' ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-video me-2"></i>Vídeo e Anexos</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">URL do Vídeo</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-link"></i></span>
                                    <input type="text" name="video_url" class="form-control" value="<?= escape($lesson['video_url'] ?? '') ?>" placeholder="https://youtube.com/watch?v=...">
                                </div>
                                <small class="text-muted">YouTube, Vimeo, Cloudflare, etc.</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Provedor</label>
                                <select name="video_provider" class="form-select border-2">
                                    <?php 
                                    $providers = ['youtube' => 'YouTube', 'vimeo' => 'Vimeo', 'cloudflare' => 'Cloudflare', 'bunny' => 'Bunny', 'self_hosted' => 'Próprio'];
                                    foreach ($providers as $k => $v): 
                                    ?>
                                        <option value="<?= $k ?>" <?= ($lesson['video_provider'] ?? 'youtube') === $k ? 'selected' : '' ?>><?= $v ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold">Materiais Complementares (URL)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-paperclip"></i></span>
                                <input type="text" name="attachment_url" class="form-control" value="<?= escape($lesson['attachment_url'] ?? '') ?>" placeholder="GitHub, Drive, ZIP...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barra Lateral -->
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-cog me-2"></i>Configurações</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-bold d-block">Visibilidade</label>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_published" id="is_published" <?= ($lesson['is_published'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_published">Publicada</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_free_preview" id="is_free_preview" <?= ($lesson['is_free_preview'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_free_preview">Prévia Gratuita</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipo de Conteúdo</label>
                            <select name="content_type" class="form-select border-2">
                                <?php 
                                $types = ['text' => 'Texto', 'video' => 'Vídeo', 'quiz' => 'Quiz', 'exercise' => 'Exercício', 'project' => 'Projeto', 'download' => 'Download'];
                                foreach ($types as $k => $v): 
                                ?>
                                    <option value="<?= $k ?>" <?= ($lesson['content_type'] ?? 'text') === $k ? 'selected' : '' ?>><?= $v ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Duração Estimada (min)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-clock"></i></span>
                                <input type="number" name="duration_minutes" class="form-control border-2" value="<?= intval($lesson['duration_minutes'] ?? 10) ?>" min="0">
                            </div>
                        </div>

                        <hr>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary w-100 btn-lg fw-bold shadow-sm">
                                <i class="fas fa-save me-2"></i> Salvar Alterações
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4 border-0 bg-light">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">ID: <strong><?= $lesson['id'] ?></strong></span>
                            <span class="text-muted small">Ordem: <strong>#<?= $lesson['sort_order'] ?></strong></span>
                        </div>
                        <div class="text-muted small">
                            <i class="fas fa-calendar-alt me-1"></i> Criado em: <?= date('d/m/Y', strtotime($lesson['created_at'])) ?>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-danger">
                    <div class="card-body p-3">
                        <button type="button" class="btn btn-outline-danger w-100" onclick="confirmDelete()">
                            <i class="fas fa-trash me-2"></i> Excluir Lição
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function confirmDelete() {
    if (confirm('Tem certeza que deseja excluir esta lição? Esta ação não pode ser desfeita.')) {
        window.location.href = '<?= url('admin/lessons/lessons-delete.php?id=' . $id . '&module_id=' . $moduleId . '&course_id=' . $courseId) ?>';
    }
}
</script>

<?php
// Carregar Scripts do EditorJS
EditorJSLoader::renderScripts();
EditorJSLoader::init($lesson['content'] ?? '', 'editorjs', 'content');
?>

<?php include '../includes/footer.php'; ?>

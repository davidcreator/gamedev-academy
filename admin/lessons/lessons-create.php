<?php
$pageTitle = 'Criar Nova Lição';
include '../includes/header.php';

$db = Database::getInstance();
$moduleId = intval($_GET['module_id'] ?? 0);
$courseId = intval($_GET['course_id'] ?? 0);

// Validar módulo
if ($moduleId <= 0) {
    flash('error', 'Módulo inválido.');
    redirect(url('admin/modules/modules.php?course_id=' . $courseId));
}

// Buscar informações do módulo e curso
$module = $db->fetch("SELECT * FROM course_modules WHERE id = ?", [$moduleId]);
$course = $courseId > 0 ? $db->fetch("SELECT * FROM courses WHERE id = ?", [$courseId]) : null;

if ($course) {
    adminRequireCourseAccess($course);
}

if (!$module) {
    flash('error', 'Módulo não encontrado.');
    redirect(url('admin/modules/modules.php?course_id=' . $courseId));
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validar campos obrigatórios
    $title = trim($_POST['title'] ?? '');
    if (empty($title)) {
        $errors[] = 'O título é obrigatório.';
    }
    
    // Preparar dados
    $data = [
        'module_id' => $moduleId,
        'course_id' => $courseId,
        'title' => $title,
        'slug' => slugify($title),
        'content_type' => $_POST['content_type'] ?? 'text',
        'content' => $_POST['content'] ?? '',
        'summary' => trim($_POST['summary'] ?? ''),
        'video_url' => trim($_POST['video_url'] ?? ''),
        'video_provider' => $_POST['video_provider'] ?? 'youtube',
        'video_duration' => intval($_POST['duration_minutes'] ?? 0),
        'attachment_url' => trim($_POST['attachment_url'] ?? ''),
        'is_published' => isset($_POST['is_published']) ? 1 : 0,
        'is_free_preview' => isset($_POST['is_free_preview']) ? 1 : 0,
        'sort_order' => 0, // Será definido automaticamente
    ];
    
    // Validar URL de vídeo se fornecida
    if (!empty($data['video_url']) && !filter_var($data['video_url'], FILTER_VALIDATE_URL)) {
        $errors[] = 'URL do vídeo inválida.';
    }
    
    // Definir ordem automaticamente (última posição)
    $lastLesson = $db->fetch(
        "SELECT MAX(sort_order) as max_order FROM course_lessons WHERE module_id = ?",
        [$moduleId]
    );
    $data['sort_order'] = ($lastLesson['max_order'] ?? 0) + 1;
    
    if (empty($errors)) {
        $lessonId = $db->insert('course_lessons', $data);
        flash('success', 'Lição criada com sucesso!');
        redirect(url('admin/lessons/lessons-list.php?module_id=' . $moduleId . '&course_id=' . $courseId));
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
                <?php if ($course): ?>
                    <li class="breadcrumb-item"><a href="<?= url('admin/courses/courses.php') ?>">Cursos</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/modules/modules.php?course_id=' . $courseId) ?>"><?= escape($course['title']) ?></a></li>
                <?php endif; ?>
                <li class="breadcrumb-item"><a href="<?= url('admin/lessons/lessons-list.php?module_id=' . $moduleId . '&course_id=' . $courseId) ?>">Lições</a></li>
                <li class="breadcrumb-item active">Nova Lição</li>
            </ol>
        </nav>
        <div class="d-flex gap-2">
            <a href="<?= url('admin/lessons/lessons-list.php?module_id=' . $moduleId . '&course_id=' . $courseId) ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Voltar
            </a>
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
                            <input type="text" name="title" class="form-control form-control-lg border-2" required placeholder="Ex: Introdução ao Unity">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Resumo da Lição</label>
                            <textarea name="summary" class="form-control" rows="2" placeholder="Uma breve descrição do que será aprendido..."></textarea>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold">Corpo da Lição</label>
                            <div id="editorjs" class="border rounded bg-light" style="min-height: 400px;"></div>
                            <textarea name="content" id="content" class="d-none"></textarea>
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
                                    <input type="text" name="video_url" class="form-control" placeholder="https://youtube.com/watch?v=...">
                                </div>
                                <small class="text-muted">YouTube, Vimeo, Cloudflare, etc.</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Provedor</label>
                                <select name="video_provider" class="form-select border-2">
                                    <option value="youtube">YouTube</option>
                                    <option value="vimeo">Vimeo</option>
                                    <option value="cloudflare">Cloudflare</option>
                                    <option value="bunny">Bunny</option>
                                    <option value="self_hosted">Próprio</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold">Materiais Complementares (URL)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-paperclip"></i></span>
                                <input type="text" name="attachment_url" class="form-control" placeholder="GitHub, Drive, ZIP...">
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
                                <input class="form-check-input" type="checkbox" name="is_published" id="is_published" checked>
                                <label class="form-check-label" for="is_published">Publicada</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_free_preview" id="is_free_preview">
                                <label class="form-check-label" for="is_free_preview">Prévia Gratuita</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipo de Conteúdo</label>
                            <select name="content_type" class="form-select border-2">
                                <option value="text">Texto</option>
                                <option value="video">Vídeo</option>
                                <option value="quiz">Quiz</option>
                                <option value="exercise">Exercício</option>
                                <option value="project">Projeto</option>
                                <option value="download">Download</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Duração Estimada (min)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-clock"></i></span>
                                <input type="number" name="duration_minutes" class="form-control border-2" value="10" min="0">
                            </div>
                        </div>

                        <hr>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary w-100 btn-lg fw-bold shadow-sm">
                                <i class="fas fa-save me-2"></i> Criar Lição
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm bg-light border-0">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center text-muted small">
                            <i class="fas fa-info-circle me-2"></i>
                            <span>O conteúdo será salvo automaticamente no formato EditorJS.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php
// Carregar Scripts do EditorJS
EditorJSLoader::renderScripts();
EditorJSLoader::init('', 'editorjs', 'content');
?>

<?php include '../includes/footer.php'; ?>

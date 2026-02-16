<?php
$pageTitle = 'Criar Nova Lição';
include '../includes/header.php';
require_once '../../includes/editorjs-loader.php';

$db = Database::getInstance();
$moduleId = intval($_GET['module_id'] ?? 0);
$courseId = intval($_GET['course_id'] ?? 0);

// Validar módulo
if ($moduleId <= 0) {
    flash('error', 'Módulo inválido.');
    redirect(url('admin/modules/list.php?course_id=' . $courseId));
}

// Buscar informações do módulo e curso
$module = $db->fetch("SELECT * FROM modules WHERE id = ?", [$moduleId]);
$course = $courseId > 0 ? $db->fetch("SELECT * FROM courses WHERE id = ?", [$courseId]) : null;

if (!$module) {
    flash('error', 'Módulo não encontrado.');
    redirect(url('admin/modules/list.php'));
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
        'title' => $title,
        'summary' => trim($_POST['summary'] ?? ''),
        'content_type' => $_POST['content_type'] ?? 'text',
        'content' => $_POST['content'] ?? '',
        'video_url' => trim($_POST['video_url'] ?? ''),
        'video_provider' => $_POST['video_provider'] ?? 'youtube',
        'duration_minutes' => intval($_POST['duration_minutes'] ?? 0),
        'is_published' => isset($_POST['is_published']) ? 1 : 0,
        'is_free_preview' => isset($_POST['is_free_preview']) ? 1 : 0,
        'attachment_url' => trim($_POST['attachment_url'] ?? ''),
        'order_position' => 0, // Será definido automaticamente
    ];
    
    // Validar URL de vídeo se fornecida
    if (!empty($data['video_url']) && !filter_var($data['video_url'], FILTER_VALIDATE_URL)) {
        $errors[] = 'URL do vídeo inválida.';
    }
    
    // Definir ordem automaticamente (última posição)
    $lastLesson = $db->fetch(
        "SELECT MAX(order_position) as max_order FROM lessons WHERE module_id = ?",
        [$moduleId]
    );
    $data['order_position'] = ($lastLesson['max_order'] ?? 0) + 1;
    
    if (empty($errors)) {
        $lessonId = $db->insert('lessons', $data);
        flash('success', 'Lição criada com sucesso!');
        redirect(url('admin/lessons/list.php?module_id=' . $moduleId . '&course_id=' . $courseId));
    } else {
        foreach ($errors as $error) {
            flash('error', $error);
        }
    }
}

showFlashMessages();
EditorJSLoader::renderStyles();
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard.php') ?>">Dashboard</a></li>
        <?php if ($course): ?>
            <li class="breadcrumb-item"><a href="<?= url('admin/courses.php') ?>">Cursos</a></li>
            <li class="breadcrumb-item"><a href="<?= url('admin/modules/list.php?course_id=' . $courseId) ?>"><?= escape($course['title']) ?></a></li>
        <?php endif; ?>
        <li class="breadcrumb-item"><a href="<?= url('admin/lessons/list.php?module_id=' . $moduleId . '&course_id=' . $courseId) ?>">Lições</a></li>
        <li class="breadcrumb-item active">Nova Lição</li>
    </ol>
</nav>

<!-- Navegação -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="<?= url('admin/lessons/list.php?module_id=' . $moduleId . '&course_id=' . $courseId) ?>" class="btn btn-secondary">
        ← Voltar para Lições
    </a>
</div>

<!-- Formulário de Criação -->
<div class="card p-4" style="max-width: 1200px; margin: 0 auto;">
    <div class="mb-4">
        <h2>Criar Nova Lição</h2>
        <p class="text-muted">Preencha os dados da nova lição</p>
    </div>

    <form method="POST" id="lessonForm">
        <!-- Seção: Informações Básicas -->
        <div class="mb-4">
            <h5 class="mb-3">Informações Básicas</h5>
            <div class="row">                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Título da Lição *</label>
                    <input type="text" name="title" class="form-control" required placeholder="Ex: Introdução ao Unity">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tipo de Conteúdo</label>
                    <select name="content_type" class="form-control">
                        <?php 
                        $types = [
                            'text' => 'Texto',
                            'video' => 'Vídeo',
                            'quiz' => 'Quiz',
                            'exercise' => 'Exercício',
                            'project' => 'Projeto',
                            'live' => 'Live',
                            'download' => 'Download'
                        ];
                        foreach ($types as $k => $v): 
                        ?>
                            <option value="<?= $k ?>"><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Duração (minutos)</label>
                    <input type="number" name="duration_minutes" class="form-control" value="0" min="0" placeholder="0">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Resumo</label>
                <input type="text" name="summary" class="form-control" placeholder="Breve descrição da lição">
            </div>
        </div>

        <hr class="my-4">

        <!-- Seção: Conteúdo -->
        <div class="mb-4">
            <h5 class="mb-3">Conteúdo da Lição</h5>
            
            <div class="mb-3">
                <label for="content" class="form-label">Texto do Conteúdo</label>
                <!-- Container do Editor.js com toolbar fixa -->
                <div id="editorjs"></div>
                <!-- Textarea original (oculto, recebe o JSON) -->
                <textarea class="form-control d-none" id="content" name="content" rows="15"></textarea>
                <small class="form-text text-muted">
                    Use a barra de ferramentas acima para adicionar diferentes tipos de conteúdo.
                </small>
            </div>
        </div>

        <hr class="my-4">

        <!-- Seção: Recursos Multimídia -->
        <div class="mb-4">
            <h5 class="mb-3">Recursos Multimídia</h5>
            
            <div class="row">
                <div class="col-md-7 mb-3">
                    <label class="form-label">URL do Vídeo</label>
                    <input type="text" name="video_url" class="form-control" placeholder="https://youtube.com/watch?v=...">
                    <small class="form-text text-muted">Cole o link do vídeo do YouTube, Vimeo, etc.</small>
                </div>
                <div class="col-md-5 mb-3">
                    <label class="form-label">Provedor de Vídeo</label>
                    <select name="video_provider" class="form-control">
                        <?php 
                        $providers = [
                            'youtube' => 'YouTube',
                            'vimeo' => 'Vimeo',
                            'cloudflare' => 'Cloudflare',
                            'bunny' => 'Bunny',
                            'self' => 'Próprio'
                        ];
                        foreach ($providers as $k => $v): 
                        ?>
                            <option value="<?= $k ?>"><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Repositório de Código ou Materiais Complementares</label>
                <input type="text" name="attachment_url" class="form-control" placeholder="https://github.com/usuario/repositorio">
                <small class="form-text text-muted">Link para GitHub, arquivos ZIP, ou outros recursos adicionais</small>
            </div>
        </div>

        <hr class="my-4">

        <!-- Seção: Configurações de Publicação -->
        <div class="mb-4">
            <h5 class="mb-3">Configurações de Publicação</h5>
            <div class="d-flex gap-4 mt-2">
                <label class="d-flex align-items-center gap-2">
                    <input type="checkbox" name="is_published" checked>
                    <span>Publicado</span>
                </label>
                <label class="d-flex align-items-center gap-2">
                    <input type="checkbox" name="is_free_preview">
                    <span>Prévia Gratuita</span>
                </label>
            </div>
            <small class="form-text text-muted">
                Marque "Publicado" para tornar a lição visível. "Prévia Gratuita" permite acesso sem assinatura.
            </small>
        </div>

        <!-- Botões de Ação -->
        <div class="d-flex justify-content-end gap-3">
            <a href="<?= url('admin/lessons.php?module_id=' . $moduleId . '&course_id=' . $courseId) ?>" class="btn btn-secondary">
                Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Criar Lição
            </button>
        </div>
    </form>
</div>

<?php
// Render Editor.js scripts
EditorJSLoader::renderScripts();

// Initialize Editor.js (sem conteúdo inicial)
EditorJSLoader::init('', 'editorjs', 'content');
?>

<?php include '../includes/footer.php'; ?>
<?php
$pageTitle = 'Editar Lição';
include '../includes/header.php';
require_once '../../includes/editorjs-loader.php';

$db = Database::getInstance();
$id = intval($_GET['id'] ?? 0);
$moduleId = intval($_GET['module_id'] ?? 0);
$courseId = intval($_GET['course_id'] ?? 0);

// Validar ID
if ($id <= 0) {
    flash('error', 'ID da lição inválido.');
    redirect(url('admin/lessons.php?module_id=' . $moduleId . '&course_id=' . $courseId));
}

// Buscar lição
$lesson = $db->fetch("SELECT * FROM lessons WHERE id = ?", [$id]);
if (!$lesson) {
    flash('error', 'Lição não encontrada.');
    redirect(url('admin/lessons.php?module_id=' . $moduleId . '&course_id=' . $courseId));
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'summary' => trim($_POST['summary'] ?? ''),
        'content_type' => $_POST['content_type'] ?? 'text',
        'content' => $_POST['content'] ?? '',
        'video_url' => trim($_POST['video_url'] ?? ''),
        'video_provider' => $_POST['video_provider'] ?? 'youtube',
        'duration_minutes' => intval($_POST['duration_minutes'] ?? 0),
        'is_published' => isset($_POST['is_published']) ? 1 : 0,
        'is_free_preview' => isset($_POST['is_free_preview']) ? 1 : 0,
        'attachment_url' => trim($_POST['attachment_url'] ?? ''),
    ];
    
    if (!$data['title']) {
        flash('error', 'Informe o título.');
    } else {
        $db->update('lessons', $data, 'id = :id', ['id' => $id]);
        flash('success', 'Lição atualizada com sucesso!');
        // Recarregar dados atualizados
        $lesson = $db->fetch("SELECT * FROM lessons WHERE id = ?", [$id]);
    }
}

showFlashMessages();
EditorJSLoader::renderStyles();
?>

<!-- Navegação -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="<?= url('admin/lessons.php?module_id=' . $moduleId . '&course_id=' . $courseId) ?>" class="btn btn-secondary">
        ← Voltar para Lições
    </a>
    <div>
        <span class="badge bg-info">ID: <?= $lesson['id'] ?></span>
    </div>
</div>

<!-- Formulário de Edição -->
<div class="card p-4" style="max-width: 1200px; margin: 0 auto;">
    <div class="mb-4">
        <h2><?= escape($lesson['title']) ?></h2>
        <p class="text-muted">Última atualização: <?= date('d/m/Y H:i', strtotime($lesson['updated_at'] ?? $lesson['created_at'])) ?></p>
    </div>

    <form method="POST" id="lessonForm">
        <!-- Seção: Informações Básicas -->
        <div class="mb-4">
            <h5 class="mb-3">Informações Básicas</h5>
            <div class="row">                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Título da Lição *</label>
                    <input type="text" name="title" class="form-control" value="<?= escape($lesson['title']) ?>" required>
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
                            <option value="<?= $k ?>" <?= ($lesson['content_type'] ?? 'text') === $k ? 'selected' : '' ?>>
                                <?= $v ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Duração (minutos)</label>
                    <input type="number" name="duration_minutes" class="form-control" value="<?= intval($lesson['duration_minutes'] ?? 0) ?>" min="0">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Resumo</label>
                <input type="text" name="summary" class="form-control" value="<?= escape($lesson['summary'] ?? '') ?>" placeholder="Breve descrição da lição">
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
                <textarea class="form-control d-none" id="content" name="content" rows="15"><?= escape($lesson['content'] ?? '') ?></textarea>
                <small class="form-text text-muted">
                    Use a barra de ferramentas acima para modificar o conteúdo.
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
                    <input type="text" name="video_url" class="form-control" value="<?= escape($lesson['video_url'] ?? '') ?>" placeholder="https://youtube.com/watch?v=...">
                    
                    <?php if (!empty($lesson['video_url'])): ?>
                    <div class="mt-2">
                        <small class="text-success">
                            <i class="fas fa-check-circle"></i> Vídeo configurado
                        </small>
                    </div>
                    <?php endif; ?>
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
                            <option value="<?= $k ?>" <?= ($lesson['video_provider'] ?? 'youtube') === $k ? 'selected' : '' ?>>
                                <?= $v ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Repositório de Código ou Materiais Complementares</label>
                <input type="text" name="attachment_url" class="form-control" value="<?= escape($lesson['attachment_url'] ?? '') ?>" placeholder="https://github.com/usuario/repositorio">
                
                <?php if (!empty($lesson['attachment_url'])): ?>
                <div class="mt-2">
                    <a href="<?= escape($lesson['attachment_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt"></i> Visualizar Recurso
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <hr class="my-4">

        <!-- Seção: Configurações de Publicação -->
        <div class="mb-4">
            <h5 class="mb-3">Configurações de Publicação</h5>
            <div class="d-flex gap-4 mt-2">
                <label class="d-flex align-items-center gap-2">
                    <input type="checkbox" name="is_published" <?= ($lesson['is_published'] ?? 0) ? 'checked' : '' ?>>
                    <span>Publicado</span>
                </label>
                <label class="d-flex align-items-center gap-2">
                    <input type="checkbox" name="is_free_preview" <?= ($lesson['is_free_preview'] ?? 0) ? 'checked' : '' ?>>
                    <span>Prévia Gratuita</span>
                </label>
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <a href="<?= url('admin/lessons.php?module_id=' . $moduleId . '&course_id=' . $courseId) ?>" class="btn btn-secondary">
                    Cancelar
                </a>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                    <i class="fas fa-trash"></i> Excluir
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar Alterações
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function confirmDelete() {
    if (confirm('Tem certeza que deseja excluir esta lição? Esta ação não pode ser desfeita.')) {
        window.location.href = '<?= url('admin/lessons/delete.php?id=' . $id . '&module_id=' . $moduleId . '&course_id=' . $courseId) ?>';
    }
}

// Prevenção de perda de dados
let formChanged = false;
document.getElementById('lessonForm').addEventListener('change', function() {
    formChanged = true;
});

window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = '';
    }
});

document.getElementById('lessonForm').addEventListener('submit', function() {
    formChanged = false;
});
</script>

<?php
// Render Editor.js scripts
EditorJSLoader::renderScripts();

// Initialize Editor.js with existing lesson content
EditorJSLoader::init($lesson['content'] ?? '', 'editorjs', 'content');
?>

<?php include '../includes/footer.php'; ?>
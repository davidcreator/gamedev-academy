<?php
// admin/lesson-edit.php - Editar Lição (Conteúdo)

$pageTitle = 'Editar Lição';
include 'includes/header.php';

$db = Database::getInstance();

$id = intval($_GET['id'] ?? 0);
$moduleId = intval($_GET['module_id'] ?? 0);
$courseId = intval($_GET['course_id'] ?? 0);

$lesson = $db->fetch("SELECT * FROM lessons WHERE id = ?", [$id]);
if (!$lesson) {
    flash('error', 'Lição não encontrada.');
    redirect(url('admin/lessons.php?module_id=' . $moduleId . '&course_id=' . $courseId));
}

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
        flash('success', 'Lição atualizada!');
        // Recarrega os dados
        $lesson = $db->fetch("SELECT * FROM lessons WHERE id = ?", [$id]);
    }
}
?>

<?= showFlashMessages() ?>

<div class="d-flex justify-between align-center mb-4">
    <a href="<?= url('admin/lessons.php?module_id=' . $moduleId . '&course_id=' . $courseId) ?>" class="btn btn-secondary">← Voltar</a>
    <h2><?= escape($lesson['title']) ?></h2>
    </div>

<div class="card p-4" style="max-width: 1000px; margin: 0 auto;">
    <form method="POST">
        <div class="row">
            <div class="col-md-8 mb-3">
                <label class="form-label">Título</label>
                <input type="text" name="title" class="form-control" value="<?= escape($lesson['title']) ?>" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Tipo de Conteúdo</label>
                <select name="content_type" class="form-control">
                    <?php
                    $types = ['text'=>'Texto','video'=>'Vídeo','quiz'=>'Quiz','exercise'=>'Exercício','project'=>'Projeto','live'=>'Live','download'=>'Download'];
                    foreach ($types as $k=>$v): ?>
                        <option value="<?= $k ?>" <?= ($lesson['content_type'] ?? 'text') === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Resumo</label>
            <input type="text" name="summary" class="form-control" value="<?= escape($lesson['summary'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Conteúdo</label>
            <div class="d-flex align-center gap-2 mb-2">
                <label class="d-flex align-center gap-1">
                    <input type="checkbox" id="lesson-markdown-toggle"> Salvar como Markdown
                </label>
            </div>
            <div class="editor-toolbar" data-editor-for="content">
                <button type="button" class="btn btn-sm btn-secondary" data-cmd="bold">Negrito</button>
                <button type="button" class="btn btn-sm btn-secondary" data-cmd="italic">Itálico</button>
                <button type="button" class="btn btn-sm btn-secondary" data-cmd="underline">Sublinhado</button>
                <button type="button" class="btn btn-sm btn-secondary" data-cmd="formatBlock" data-value="h2">H2</button>
                <button type="button" class="btn btn-sm btn-secondary" data-cmd="formatBlock" data-value="h3">H3</button>
                <button type="button" class="btn btn-sm btn-secondary" data-cmd="insertUnorderedList">Lista</button>
                <button type="button" class="btn btn-sm btn-secondary" data-cmd="insertOrderedList">Lista Num.</button>
                <button type="button" class="btn btn-sm btn-secondary" data-cmd="createLink" data-prompt="URL">Link</button>
                <button type="button" class="btn btn-sm btn-secondary" data-action="insertImage">Imagem</button>
                <button type="button" class="btn btn-sm btn-secondary" data-action="insertYouTube">YouTube</button>
                <button type="button" class="btn btn-sm btn-secondary" data-action="insertIframe">Streaming</button>
                <button type="button" class="btn btn-sm btn-secondary" data-action="insertRepo">Repo</button>
                <button type="button" class="btn btn-sm btn-secondary" data-cmd="removeFormat">Limpar</button>
            </div>
            <div id="editor-content" class="editor-area" contenteditable="true"><?= $lesson['content'] ?? '' ?></div>
            <textarea name="content" id="textarea-content" class="form-control" hidden><?= $lesson['content'] ?? '' ?></textarea>
        </div>

        <div class="row">
            <div class="col-md-5 mb-3">
                <label class="form-label">Vídeo URL</label>
                <input type="text" name="video_url" class="form-control" value="<?= escape($lesson['video_url'] ?? '') ?>">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Provedor</label>
                <select name="video_provider" class="form-control">
                    <?php foreach (['youtube','vimeo','cloudflare','bunny','self'] as $p): ?>
                        <option value="<?= $p ?>" <?= ($lesson['video_provider'] ?? 'youtube') === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 mb-3">
                <label class="form-label">Duração (min)</label>
                <input type="number" name="duration_minutes" class="form-control" value="<?= intval($lesson['duration_minutes'] ?? 0) ?>">
            </div>
            <div class="col-md-2 mb-3">
                <label class="form-label">Status</label>
                <div class="d-flex align-center gap-2 mt-1">
                    <label class="d-flex align-center gap-1">
                        <input type="checkbox" name="is_published" <?= ($lesson['is_published'] ?? 0) ? 'checked' : '' ?>> Publicado
                    </label>
                    <label class="d-flex align-center gap-1">
                        <input type="checkbox" name="is_free_preview" <?= ($lesson['is_free_preview'] ?? 0) ? 'checked' : '' ?>> Prévia grátis
                    </label>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Repositório de Código (URL)</label>
                <input type="text" name="attachment_url" class="form-control" value="<?= escape($lesson['attachment_url'] ?? '') ?>" placeholder="https://github.com/...">
            </div>
        </div>

        <div class="d-flex justify-end gap-2 mt-3">
            <a href="<?= url('admin/lessons.php?module_id=' . $moduleId . '&course_id=' . $courseId) ?>" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
    </form>
</div>

<script type="module">
import { ClassicEditor, Essentials, Paragraph, Bold, Italic, Underline, Strikethrough, Link, List, BlockQuote, Table, TableToolbar, Image, ImageToolbar, ImageCaption, ImageStyle, ImageResize, ImageInsert, Autoformat, Markdown } from 'https://cdn.ckeditor.com/ckeditor5/47.1.1/ckeditor5.js';
const textarea = document.getElementById('textarea-content');
const toggle = document.getElementById('lesson-markdown-toggle');
let editorInstance = null;
const basePlugins = [ Essentials, Paragraph, Bold, Italic, Underline, Strikethrough, Link, List, BlockQuote, Table, TableToolbar, Image, ImageToolbar, ImageCaption, ImageStyle, ImageResize, ImageInsert, Autoformat ];
const baseToolbar = [ 'heading','|','bold','italic','underline','strikethrough','link','bulletedList','numberedList','blockQuote','insertTable','imageInsert','undo','redo' ];
function initEditor(useMarkdown) {
  if (editorInstance) editorInstance.destroy();
  const plugins = useMarkdown ? [ Markdown, ...basePlugins ] : basePlugins;
  ClassicEditor.create(textarea, {
    plugins,
    toolbar: baseToolbar,
    image: { toolbar: [ 'imageTextAlternative','toggleImageCaption','imageStyle:inline','imageStyle:block','imageStyle:side' ] }
  }).then(ed => {
    editorInstance = ed;
    document.getElementById('editor-content').style.display = 'none';
    document.querySelector('.editor-toolbar[data-editor-for="content"]').style.display = 'none';
  });
}
initEditor(false);
toggle.addEventListener('change', () => initEditor(toggle.checked));
document.querySelector('form').addEventListener('submit', () => {
  if (editorInstance) textarea.value = editorInstance.getData();
});
</script>

<?php include 'includes/footer.php'; ?>

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
            </div>
            <div class="editor-toolbar" data-editor-for="content">
            <!-- Inicialização do TinyMCE -->
            <!-- TinyMCE - Colocar antes do </body> -->
            <script src="../assets/js/tinymce/tinymce.min.js"></script>
            <script>
            tinymce.init({
                selector: '#content',
                height: 500,
                language: 'pt_BR',
                plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount codesample',
                toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | codesample code image link | help',
                menubar: 'file edit view insert format tools table help',
                branding: false,
                promotion: false,
                codesample_languages: [
                    { text: 'HTML/XML', value: 'markup' },
                    { text: 'JavaScript', value: 'javascript' },
                    { text: 'CSS', value: 'css' },
                    { text: 'PHP', value: 'php' },
                    { text: 'C#', value: 'csharp' },
                    { text: 'C++', value: 'cpp' },
                    { text: 'Python', value: 'python' }
                ],
                setup: function(editor) {
                    editor.on('change', function() {
                        editor.save();
                    });
                }
            });

            document.querySelector('form').addEventListener('submit', function() {
                if (tinymce.get('content')) {
                    tinymce.get('content').save();
                }
            });
            </script>
             <!-- Fim do TinyMCE -->
            <form method="POST">
    <!-- outros campos -->
    
    <div class="mb-3">
        <label for="content" class="form-label">Conteúdo</label>
        <textarea class="form-control" id="content" name="content" rows="15"><?php echo htmlspecialchars($lesson['content']); ?></textarea>
    </div>
    
    <!-- outros campos -->
    
    <button type="submit" class="btn btn-primary">Salvar</button>
    </form>
            <!-- Scripts no final, antes de </body> -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            <script src="../assets/js/tinymce/tinymce.min.js"></script>
            <script>
            tinymce.init({
                selector: '#content',
                license_key: 'gpl',
                height: 500,
                language: 'pt_BR',
                plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount codesample',
                toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | codesample code image link | help',
                menubar: 'file edit view insert format tools table help',
                branding: false,
                promotion: false,
                codesample_languages: [
                    { text: 'HTML/XML', value: 'markup' },
                    { text: 'JavaScript', value: 'javascript' },
                    { text: 'CSS', value: 'css' },
                    { text: 'PHP', value: 'php' },
                    { text: 'C#', value: 'csharp' },
                    { text: 'C++', value: 'cpp' },
                    { text: 'Python', value: 'python' }
                ],
                setup: function(editor) {
                    editor.on('change', function() {
                        editor.save();
                    });
                }
            });

            document.querySelector('form').addEventListener('submit', function() {
                if (tinymce.get('content')) {
                    tinymce.get('content').save();
                }
            });
            </script>
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

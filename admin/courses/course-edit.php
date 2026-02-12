<?php
// admin/course-edit.php - Criar/Editar Curso

$pageTitle = 'Editar Curso';
include '../includes/header.php';

$db = Database::getInstance();
$courseModel = new Course();

$id = intval($_GET['id'] ?? 0);
$course = $id ? $courseModel->find($id) : null;
$isEdit = $course !== null;
$pageTitle = $isEdit ? 'Editar Curso' : 'Novo Curso';

$categories = $courseModel->getCategories();
$instructors = $db->fetchAll("SELECT id, full_name FROM users WHERE role IN ('instructor','admin') ORDER BY full_name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'category_id' => intval($_POST['category_id'] ?? 0) ?: null,
        'instructor_id' => intval($_POST['instructor_id'] ?? 0) ?: null,
        'short_description' => trim($_POST['short_description'] ?? ''),
        'description' => $_POST['description'] ?? '',
        'difficulty' => $_POST['difficulty'] ?? 'beginner',
        'language' => $_POST['language'] ?? 'pt-BR',
        'estimated_hours' => intval($_POST['estimated_hours'] ?? 0),
        'xp_reward' => intval($_POST['xp_reward'] ?? 100),
        'coin_reward' => intval($_POST['coin_reward'] ?? 10),
        'is_free' => isset($_POST['is_free']) ? 1 : 0,
        'price' => floatval($_POST['price'] ?? 0),
        'is_published' => isset($_POST['is_published']) ? 1 : 0,
        'thumbnail' => trim($_POST['thumbnail'] ?? ''),
        'cover_image' => trim($_POST['cover_image'] ?? ''),
        'preview_video' => trim($_POST['preview_video'] ?? ''),
        'trailer_url' => trim($_POST['trailer_url'] ?? ''),
    ];

    if (!$data['title']) {
        flash('error', 'Informe o título do curso.');
    } else {
        if ($isEdit) {
            $courseModel->update($id, $data);
            flash('success', 'Curso atualizado com sucesso!');
        } else {
            $newId = $courseModel->create($data);
            flash('success', 'Curso criado com sucesso!');
            redirect(url('admin/courses/course-edit.php?id=' . $newId));
        }
    }
}
?>

<?= showFlashMessages() ?>

<div class="d-flex justify-between align-center mb-4">
    <a href="<?= url('admin/courses/courses.php') ?>" class="btn btn-secondary">← Voltar</a>
    <?php if ($isEdit): ?>
        <div class="d-flex gap-2">
            <a href="<?= url('admin/modules/modules.php?course_id=' . $course['id']) ?>" class="btn btn-primary">Gerenciar Módulos</a>
            <a href="<?= url('courses/course.php?slug=' . $course['slug']) ?>" class="btn btn-outline">Ver Curso</a>
        </div>
    <?php endif; ?>
    </div>

<div class="card p-4" style="max-width: 1000px; margin: 0 auto;">
    <h2 class="mb-3"><?= $isEdit ? 'Editar Curso' : 'Novo Curso' ?></h2>

    <form method="POST">
        <div class="row">
            <div class="col-md-8 mb-3">
                <label class="form-label">Título <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" value="<?= escape($course['title'] ?? '') ?>" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Categoria</label>
                <select name="category_id" class="form-control">
                    <option value="">Selecione...</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($course['category_id'] ?? null) == $cat['id'] ? 'selected' : '' ?>>
                            <?= escape($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Instrutor</label>
                <select name="instructor_id" class="form-control">
                    <option value="">Selecione...</option>
                    <?php foreach ($instructors as $ins): ?>
                        <option value="<?= $ins['id'] ?>" <?= ($course['instructor_id'] ?? null) == $ins['id'] ? 'selected' : '' ?>>
                            <?= escape($ins['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Dificuldade</label>
                <select name="difficulty" class="form-control">
                    <?php
                        $diffs = ['beginner' => 'Iniciante', 'intermediate' => 'Intermediário', 'advanced' => 'Avançado', 'expert' => 'Especialista'];
                        foreach ($diffs as $val => $label):
                    ?>
                        <option value="<?= $val ?>" <?= ($course['difficulty'] ?? 'beginner') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Idioma</label>
                <select name="language" class="form-control">
                    <option value="pt-BR" <?= ($course['language'] ?? 'pt-BR') === 'pt-BR' ? 'selected' : '' ?>>Português (BR)</option>
                    <option value="en" <?= ($course['language'] ?? 'pt-BR') === 'en' ? 'selected' : '' ?>>Inglês</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Descrição Curta</label>
            <input type="text" name="short_description" class="form-control" value="<?= escape($course['short_description'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Descrição Completa</label>
            <div class="d-flex align-center gap-2 mb-2">
                <label class="d-flex align-center gap-1">
                    <input type="checkbox" id="course-markdown-toggle"> Salvar como Markdown
                </label>
            </div>
            <div class="editor-toolbar" data-editor-for="description">
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
            <div id="editor-description" class="editor-area" contenteditable="true"><?= $course ? ($course['description'] ?? '') : '' ?></div>
            <textarea name="description" id="textarea-description" class="form-control" hidden><?= $course ? ($course['description'] ?? '') : '' ?></textarea>
        </div>

        <div class="row">
            <div class="col-md-3 mb-3">
                <label class="form-label">Horas Estimadas</label>
                <input type="number" name="estimated_hours" class="form-control" min="0" value="<?= intval($course['estimated_hours'] ?? 0) ?>">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">XP</label>
                <input type="number" name="xp_reward" class="form-control" min="0" value="<?= intval($course['xp_reward'] ?? 100) ?>">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Moedas</label>
                <input type="number" name="coin_reward" class="form-control" min="0" value="<?= intval($course['coin_reward'] ?? 10) ?>">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Preço (R$)</label>
                <input type="number" name="price" class="form-control" step="0.01" min="0" value="<?= floatval($course['price'] ?? 0) ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Thumbnail (URL)</label>
                <input type="text" name="thumbnail" class="form-control" value="<?= escape($course['thumbnail'] ?? '') ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Imagem de Capa (URL)</label>
                <input type="text" name="cover_image" class="form-control" value="<?= escape($course['cover_image'] ?? '') ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Vídeo de Prévia (URL)</label>
                <input type="text" name="preview_video" class="form-control" value="<?= escape($course['preview_video'] ?? '') ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Trailer (URL)</label>
                <input type="text" name="trailer_url" class="form-control" value="<?= escape($course['trailer_url'] ?? '') ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Publicação</label>
                <div class="d-flex align-center gap-2">
                    <label class="d-flex align-center gap-1">
                        <input type="checkbox" name="is_published" <?= ($course['is_published'] ?? 0) ? 'checked' : '' ?>> Publicado
                    </label>
                    <label class="d-flex align-center gap-1">
                        <input type="checkbox" name="is_free" <?= ($course['is_free'] ?? 0) ? 'checked' : '' ?>> Curso Grátis
                    </label>
                </div>
            </div>
        </div>

        <div class="d-flex justify-end gap-2 mt-3">
            <a href="<?= url('admin/courses/courses.php') ?>" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
    </form>
</div>
<script type="module">
import { ClassicEditor, Essentials, Paragraph, Bold, Italic, Underline, Strikethrough, Link, List, BlockQuote, Table, TableToolbar, Image, ImageToolbar, ImageCaption, ImageStyle, ImageResize, ImageInsert, Autoformat, Markdown } from 'https://cdn.ckeditor.com/ckeditor5/47.1.1/ckeditor5.js';
window.CKEDITOR5_ACTIVE = true;
const textarea = document.getElementById('textarea-description');
const toggle = document.getElementById('course-markdown-toggle');
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
    document.getElementById('editor-description').style.display = 'none';
    document.querySelector('.editor-toolbar[data-editor-for=\"description\"]').style.display = 'none';
  });
}
initEditor(false);
toggle.addEventListener('change', () => initEditor(toggle.checked));
document.querySelector('form').addEventListener('submit', () => {
  if (editorInstance) textarea.value = editorInstance.getData();
});
</script>

<script src="/vendor/ckeditor/ckeditor/ckeditor.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.CKEDITOR && !window.CKEDITOR5_ACTIVE) {
        document.getElementById('editor-description').style.display = 'none';
        document.querySelector('.editor-toolbar[data-editor-for=\"description\"]').style.display = 'none';
        CKEDITOR.replace('textarea-description', {
            height: 400,
            removePlugins: 'imageUpload',
            toolbar: [
                { name: 'document', items: ['Source','-','Preview'] },
                { name: 'clipboard', items: ['Cut','Copy','Paste','Undo','Redo'] },
                { name: 'basicstyles', items: ['Bold','Italic','Underline','Strike','RemoveFormat'] },
                { name: 'paragraph', items: ['NumberedList','BulletedList','Blockquote'] },
                { name: 'links', items: ['Link','Unlink'] },
                { name: 'insert', items: ['Image','Table','HorizontalRule','SpecialChar'] },
                { name: 'styles', items: ['Format','Font','FontSize'] },
                { name: 'colors', items: ['TextColor','BGColor'] }
            ]
        });
    } else if (window.AdminEditor && typeof window.AdminEditor.init === 'function') {
        window.AdminEditor.init('description', 'editor-description', 'textarea-description');
    } else {
        var area = document.getElementById('editor-description');
        var ta = document.getElementById('textarea-description');
        var toolbar = document.querySelector('.editor-toolbar[data-editor-for=\"description\"]');
        function sync() { ta.value = area.innerHTML; }
        area.addEventListener('input', sync);
        toolbar.addEventListener('click', function (e) {
            var btn = e.target.closest('button[data-cmd]');
            if (!btn) return;
            var cmd = btn.getAttribute('data-cmd');
            var val = btn.getAttribute('data-value');
            var promptVal = btn.getAttribute('data-prompt');
            if (cmd === 'createLink') {
                val = prompt(promptVal || 'URL', 'https://');
                if (!val) return;
            }
            document.execCommand(cmd, false, val);
            sync();
        });
        sync();
    }
</script>
<?php include '../includes/footer.php'; ?>

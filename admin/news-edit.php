<?php
$pageTitle = 'Editor de Postagens';
include 'includes/header.php';

$db = Database::getInstance();
$newsModel = new News();
$currentUser = $auth->getCurrentUser();

$id = intval($_GET['id'] ?? 0);
$article = $id ? $newsModel->find($id) : null;
$isEdit = $article !== null;
$pageTitle = $isEdit ? 'Editar Postagem' : 'Nova Postagem';

$categories = ['update' => 'Atualização', 'tutorial' => 'Tutorial', 'news' => 'Notícia', 'event' => 'Evento', 'announcement' => 'Aviso'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'excerpt' => trim($_POST['excerpt'] ?? ''),
        'content' => $_POST['content'] ?? '',
        'thumbnail' => trim($_POST['thumbnail'] ?? ''),
        'category' => $_POST['category'] ?? 'news',
        'tags' => trim($_POST['tags'] ?? ''),
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'is_published' => isset($_POST['is_published']) ? 1 : 0,
        'allow_comments' => isset($_POST['allow_comments']) ? 1 : 0,
        'author_id' => intval($currentUser['id'] ?? 0)
    ];
    if (!$data['title']) {
        flash('error', 'Informe o título da postagem.');
    } else {
        if ($isEdit) {
            $newsModel->update($id, $data);
            flash('success', 'Postagem atualizada com sucesso!');
            $article = $newsModel->find($id);
        } else {
            $newId = $newsModel->create($data);
            flash('success', 'Postagem criada com sucesso!');
            redirect(url('admin/news-edit.php?id=' . $newId));
        }
    }
}
?>

<?= showFlashMessages() ?>

<div class="d-flex justify-between align-center mb-4">
    <a href="<?= url('admin/news.php') ?>" class="btn btn-secondary">← Voltar</a>
    <h2><?= $isEdit ? 'Editar Postagem' : 'Nova Postagem' ?></h2>
</div>

<div class="card p-4">
    <form method="POST">
        <div class="row">
            <div class="col-md-8 mb-3">
                <label class="form-label">Título</label>
                <input type="text" name="title" class="form-control" value="<?= escape($article['title'] ?? '') ?>" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Categoria</label>
                <select name="category" class="form-control">
                    <?php foreach ($categories as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($article['category'] ?? 'news') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Resumo (Excerpt)</label>
            <input type="text" name="excerpt" class="form-control" value="<?= escape($article['excerpt'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Conteúdo</label>
            <div class="d-flex align-center gap-2 mb-2">
                <label class="d-flex align-center gap-1">
                    <input type="checkbox" id="content-markdown-toggle"> Salvar como Markdown
                </label>
            </div>
            <textarea name="content" id="editor-content" class="form-control" rows="12"><?= $article['content'] ?? '' ?></textarea>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Thumbnail (URL)</label>
                <input type="text" name="thumbnail" class="form-control" value="<?= escape($article['thumbnail'] ?? '') ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Tags</label>
                <input type="text" name="tags" class="form-control" value="<?= escape($article['tags'] ?? '') ?>" placeholder="ex.: gamedev, unity, dicas">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Opções</label>
                <div class="d-flex align-center gap-2 mt-1">
                    <label class="d-flex align-center gap-1">
                        <input type="checkbox" name="is_featured" <?= ($article['is_featured'] ?? 0) ? 'checked' : '' ?>> Destaque
                    </label>
                    <label class="d-flex align-center gap-1">
                        <input type="checkbox" name="is_published" <?= ($article['is_published'] ?? 0) ? 'checked' : '' ?>> Publicado
                    </label>
                    <label class="d-flex align-center gap-1">
                        <input type="checkbox" name="allow_comments" <?= ($article['allow_comments'] ?? 1) ? 'checked' : '' ?>> Permitir comentários
                    </label>
                </div>
            </div>
        </div>

        <div class="d-flex justify-end gap-2 mt-3">
            <a href="<?= url('admin/news.php') ?>" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
    </form>
</div>

<script type="module">
import { ClassicEditor, Essentials, Paragraph, Bold, Italic, Underline, Strikethrough, Link, List, BlockQuote, Table, TableToolbar, Image, ImageToolbar, ImageCaption, ImageStyle, ImageResize, ImageInsert, Autoformat, Markdown } from 'https://cdn.ckeditor.com/ckeditor5/47.1.1/ckeditor5.js';
const textarea = document.getElementById('editor-content');
const toggle = document.getElementById('content-markdown-toggle');
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
  });
}
initEditor(false);
toggle.addEventListener('change', () => initEditor(toggle.checked));
document.querySelector('form').addEventListener('submit', () => {
  if (editorInstance) textarea.value = editorInstance.getData();
});
</script>

<?php include 'includes/footer.php'; ?>

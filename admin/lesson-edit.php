<?php
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
        $lesson = $db->fetch("SELECT * FROM lessons WHERE id = ?", [$id]);
    }
}
?>

<?= showFlashMessages() ?>

<div class="mb-4">
    <a href="<?= url('admin/lessons.php?module_id=' . $moduleId . '&course_id=' . $courseId) ?>" class="btn btn-secondary">← Voltar</a>
</div>

<div class="card p-4" style="max-width: 1200px; margin: 0 auto;">
    <div class="mb-4">
        <h2><?= escape($lesson['title']) ?></h2>
    </div>

    <form method="POST">
        <!-- Seção: Informações Básicas -->
        <div class="mb-4">
            <h5 class="mb-3">Informações Básicas</h5>
            
            <div class="row">
                <div class="col-md-9 mb-3">
                    <label class="form-label">Título da Lição</label>
                    <input type="text" name="title" class="form-control" value="<?= escape($lesson['title']) ?>" required>
                </div>
                <div class="col-md-3 mb-3">
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
                <input type="text" name="summary" class="form-control" value="<?= escape($lesson['summary'] ?? '') ?>" placeholder="Breve descrição da lição">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Duração Estimada</label>
                    <div class="d-flex align-center gap-2">
                        <input type="number" name="duration_minutes" class="form-control" value="<?= intval($lesson['duration_minutes'] ?? 0) ?>" placeholder="0">
                        <span>minutos</span>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label d-block">Configurações de Publicação</label>
                    <div class="d-flex gap-4 mt-2">
                        <label class="d-flex align-center gap-2">
                            <input type="checkbox" name="is_published" <?= ($lesson['is_published'] ?? 0) ? 'checked' : '' ?>>
                            <span>Publicado</span>
                        </label>
                        <label class="d-flex align-center gap-2">
                            <input type="checkbox" name="is_free_preview" <?= ($lesson['is_free_preview'] ?? 0) ? 'checked' : '' ?>>
                            <span>Prévia Gratuita</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <!-- Seção: Conteúdo -->
        <div class="mb-4">
            <h5 class="mb-3">Conteúdo da Lição</h5>
            
            <div class="mb-3">
                <label for="content" class="form-label">Texto do Conteúdo</label>
                <textarea class="form-control" id="content" name="content" rows="15"><?= escape($lesson['content'] ?? '') ?></textarea>
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
                </div>
                <div class="col-md-5 mb-3">
                    <label class="form-label">Provedor de Vídeo</label>
                    <select name="video_provider" class="form-control">
                        <?php foreach (['youtube'=>'YouTube','vimeo'=>'Vimeo','cloudflare'=>'Cloudflare','bunny'=>'Bunny','self'=>'Próprio'] as $k=>$v): ?>
                            <option value="<?= $k ?>" <?= ($lesson['video_provider'] ?? 'youtube') === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Repositório de Código ou Materiais Complementares</label>
                <input type="text" name="attachment_url" class="form-control" value="<?= escape($lesson['attachment_url'] ?? '') ?>" placeholder="https://github.com/usuario/repositorio">
            </div>
        </div>

        <hr class="my-4">

        <!-- Botões de Ação -->
        <div class="d-flex justify-end gap-3">
            <a href="<?= url('admin/lessons.php?module_id=' . $moduleId . '&course_id=' . $courseId) ?>" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        </div>
    </form>
</div>

<!-- TinyMCE -->
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

<?php include 'includes/footer.php'; ?>
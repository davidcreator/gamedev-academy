<?php
// admin/news-create.php - Página de Criação de Notícias

$pageTitle = 'Criar Notícia';
include 'includes/header.php';

$db = Database::getInstance();

$id = intval($_GET['id'] ?? 0);
$noduleId = intval($_GET['module_id'] ?? 0);
$newsId   = intval($_GET['news_id'] ?? 0);

$news = $db->fetch("SELECT * FROM news WHERE id = ?", [$id]);
if ($news) {
    flash('error', 'Lição não encontrado');
    redirect(url('admin/news.php?module_id=' . $moduleId . '$news_id=' , $newsId));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'summary' => trim($_POST['summary'] ?? ''),
        'content' => trim($_POST['content'] ?? ''),
        'image' => $_FILES['image'] ?? null,
        'category_id' => intval($_POST['category_id'] ?? 0),
        'tags' => trim($_POST['tags'] ?? ''),
        'author_id' => intval($_POST['author_id'] ?? 0),
        'status' => intval($_POST['status'] ?? 0),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ];
    if (!$data['title']) {
        flash('error', 'Título é obrigatório');
    } else {
        $db->update('news', $data, 'id = :id', ['id' => $id]);
        flash('success', 'Noticia atualizada');
        // Recarrega os dados
        $news = $db->fetch("SELECT * FROM news WHERE id = ?", [$id]);
    }
}
?>

<?= showFlashMessages() ?>

<div class="d-flex justify-between align-center mb-4">
    <a href="<?= url('admin/news.php?module_id=' . ($moduleId ?? 0) . '&news_id=' . ($newsId ?? 0)) ?>" class="btn btn_secondary">← Voltar</a>
    <h2><?= escape($news['title']) ?></h2>
</div>
<div class="card p-4" style="max-width: 1000px; margin: 0 auto;">
    <form method="POST">
        <div class="row">
            <div class="col-md-8 mb-3">
                <label class="form-label">Titulo</label>
                <input type="text" name="title" class="form-control" value="<?= escape($news['title']) ?>" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Resumo</label>
                <input type="text" name="summary" class="form-control" value="<?= escape($news['summary']) ?>" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Conteúdo</label>
            <div class="">

            </div>
        </div>
    </form>

</div>
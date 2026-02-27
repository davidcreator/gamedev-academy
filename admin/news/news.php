<?php
// admin/news.php - Manejo de Notícias

$pageTitle = 'Gerenciar Noticias';
include '../includes/header.php';

$newsModel = new News();
$items = $newsModel->getAll(false); // incluir noticias não publicadas

// Ação de publicar/ remover publicação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $newsId = intval($_POST['news_id'] ?? 0);

    if ($action === 'toggle_publish' && $newsId) {
        $news = $newsModel->find($newsId);
        if ($news) {
            $newsModel->update($newsId, ['is_published' => !$news['is_published']]);
            flash('success', 'Status da noticia alterado com sucesso!');
            redirect(url('admin/news/news.php'));
        }
    }

    if ($action === 'delete' && $newsId) {
        if ($newsModel->delete($newsId)) {
            flash('success', 'Noticia excluída com sucesso!');            
        } else {
            flash('error', 'Erro ao excluir noticia!');
        }
        redirect(url('admin/news/news.php'));
    }
}
?>
<div class="wrapper-container">
    <!-- Página de Gerenciamento de Notícias-->
    <div class="d-flex justify-between align-center mb-4">
        <div>
            <p class="text-muted">Total de <?= count($items) ?> Notícias</p>
        </div>

        <a href="<?=  url('admin/news/news-create.php') ?>" class="btn btn-primary"> + Noticias</a>
    </div>
    <?= showFlashMessages() ?> 

    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Status</th>
                    <th>Publicado</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $news): ?>
                <tr>
                    <td>#<?=  $news['id'] ?></td>
                    <td>
                        <div>
                            <div><?= escape($news['title']) ?></div>
                            <div class="text-muted"><?= escape(truncate($news['content'], 100)) ?></div>
                        </div>
                    </td>
                    <td><?= escape(($news['is_published'] ?? false) ? 'Sim' : 'Não') ?></td>
                    <td><?= getDateTime($news['published_at'] ?? '') ?></td>
                    <td><?= $news['total_comments'] ?? 0 ?></td>
                    <td><span class="badge badge-primary"><?= $news['category_name'] ?? 'Sem categoria' ?></span></td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="toggle_publish">
                            <input type="hidden" name="news_id" value="<?= $news['id'] ?>">
                            <?php if ($news['is_published'] ?? false): ?>
                                <button type="submit" class="badge badge-success">Publicado</button>
                                <?php else: ?>
                                <button type="submit" class="badge badge-warning">Rascunho</button>
                            <?php endif; ?>
                        </form>
                    </td>
                    <td><?= formatDate($news['created_at'] ?? '') ?></td>
                    <td>
                        <div class="admin-actions">
                            <a href="<?= url('admin/news/news-edit.php?id=' . $news['id']) ?>" class="btn-action edit" title="Editar">✏️</a>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta noticia?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="news_id" value="<?= $news['id'] ?>">
                                <button type="submit" class="btn-action delete" title="Excluir">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
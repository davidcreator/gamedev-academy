<?php
// admin/news.php - Gerenciar Notícias

$pageTitle = 'Gerenciar Notícias';
include 'includes/header.php';

$newsModel = new News();
$news = $newsModel->getAll(false); // Incluir não publicadas

// Ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $newsId = intval($_POST['news_id'] ?? 0);
    
    if ($action === 'toggle_publish' && $newsId) {
        $article = $newsModel->find($newsId);
        if ($article) {
            $data = ['is_published' => !$article['is_published']];
            if (!$article['is_published']) {
                $data['published_at'] = date('Y-m-d H:i:s');
            }
            $newsModel->update($newsId, $data);
            flash('success', 'Status da notícia atualizado!');
            redirect(url('admin/news.php'));
        }
    }
    
    if ($action === 'toggle_featured' && $newsId) {
        $article = $newsModel->find($newsId);
        if ($article) {
            $newsModel->update($newsId, ['is_featured' => !$article['is_featured']]);
            flash('success', 'Destaque atualizado!');
            redirect(url('admin/news.php'));
        }
    }
    
    if ($action === 'delete' && $newsId) {
        if ($newsModel->delete($newsId)) {
            flash('success', 'Notícia excluída com sucesso!');
        } else {
            flash('error', 'Erro ao excluir notícia.');
        }
        redirect(url('admin/news.php'));
    }
}
?>

<div class="d-flex justify-between align-center mb-4">
    <div>
        <p class="text-muted">Total de <?= count($news) ?> notícias</p>
    </div>
    
    <a href="<?= url('admin/news-edit.php') ?>" class="btn btn-primary">
        + Nova Notícia
    </a>
</div>

<?= showFlashMessages() ?>

<div class="admin-table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Categoria</th>
                <th>Autor</th>
                <th>Views</th>
                <th>Destaque</th>
                <th>Status</th>
                <th>Publicado em</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($news as $article): ?>
            <tr>
                <td>#<?= $article['id'] ?></td>
                <td>
                    <div>
                        <div><?= escape($article['title']) ?></div>
                        <div class="text-muted">
                            <?= escape(truncate($article['excerpt'], 60)) ?>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge badge-primary"><?= escape($article['category']) ?></span>
                </td>
                <td><?= escape($article['author_name'] ?? 'Admin') ?></td>
                <td><?= number_format($article['views']) ?></td>
                <td>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="action" value="toggle_featured">
                        <input type="hidden" name="news_id" value="<?= $article['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline">
                            <?= $article['is_featured'] ? '⭐' : '☆' ?>
                        </button>
                    </form>
                </td>
                <td>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="action" value="toggle_publish">
                        <input type="hidden" name="news_id" value="<?= $article['id'] ?>">
                        <?php if ($article['is_published']): ?>
                            <button type="submit" class="badge badge-success">
                                Publicado
                            </button>
                        <?php else: ?>
                            <button type="submit" class="badge badge-warning">
                                Rascunho
                            </button>
                        <?php endif; ?>
                    </form>
                </td>
                <td>
                    <?= $article['published_at'] ? formatDate($article['published_at']) : '-' ?>
                </td>
                <td>
                    <div class="admin-actions">
                        <a href="<?= url('admin/news-edit.php?id=' . $article['id']) ?>" 
                           class="btn-action edit" title="Editar">✏️</a>
                        <form method="POST" class="d-inline" 
                              onsubmit="return confirm('Tem certeza que deseja excluir esta notícia?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="news_id" value="<?= $article['id'] ?>">
                            <button type="submit" class="btn-action delete" title="Deletar">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>

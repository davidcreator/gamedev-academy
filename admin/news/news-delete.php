<?php
/**
 * Delete News
 * Script para excluir notícias
 */

require_once '../../includes/init.php';

// Verificar autenticação
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'Acesso negado.');
    redirect(url('admin/login.php'));
}

$db = Database::getInstance();
$id = intval($_GET['id'] ?? 0);

// Validar ID
if ($id <= 0) {
    flash('error', 'ID da notícia inválido.');
    redirect(url('admin/news/news-list.php'));
}

// Buscar notícia
$news = $db->fetch("SELECT * FROM news WHERE id = ?", [$id]);

if (!$news) {
    flash('error', 'Notícia não encontrada.');
    redirect(url('admin/news/news-list.php'));
}

// Se for requisição POST, executar exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Opcional: Excluir imagem associada
    if (!empty($news['featured_image'])) {
        $imagePath = str_replace(url(''), '', $news['featured_image']);
        if (file_exists('../' . $imagePath)) {
            @unlink('../' . $imagePath);
        }
    }
    
    // Excluir notícia
    $deleted = $db->delete('news', 'id = ?', [$id]);
    
    if ($deleted) {
        flash('success', 'Notícia excluída com sucesso!');
    } else {
        flash('error', 'Erro ao excluir notícia.');
    }
    
    redirect(url('admin/news/news-list.php'));
}

// Mostrar página de confirmação
$pageTitle = 'Excluir Notícia';
include '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0">
                    <i class="fas fa-exclamation-triangle"></i>
                    Confirmar Exclusão
                </h4>
            </div>
            <div class="card-body">
                <div class="alert alert-danger">
                    <strong>Atenção!</strong> Esta ação não pode ser desfeita.
                </div>

                <h5>Você está prestes a excluir a seguinte notícia:</h5>
                
                <div class="card mt-3">
                    <div class="card-body">
                        <?php if (!empty($news['featured_image'])): ?>
                            <img src="<?= escape($news['featured_image']) ?>" alt="Imagem" class="img-fluid rounded mb-3" style="max-height: 200px;">
                        <?php endif; ?>
                        
                        <h4><?= escape($news['title']) ?></h4>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <strong>Status:</strong> 
                                    <?php
                                    $statusLabels = [
                                        'published' => 'Publicada',
                                        'draft' => 'Rascunho',
                                        'scheduled' => 'Agendada',
                                        'archived' => 'Arquivada'
                                    ];
                                    echo $statusLabels[$news['status']] ?? $news['status'];
                                    ?>
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <strong>Categoria:</strong> 
                                    <?= ucfirst(escape($news['category'] ?? '-')) ?>
                                </small>
                            </div>
                        </div>
                        
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <strong>Criada em:</strong> 
                                    <?= date('d/m/Y H:i', strtotime($news['created_at'])) ?>
                                </small>
                            </div>
                            <?php if ($news['published_at']): ?>
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <strong>Publicada em:</strong> 
                                    <?= date('d/m/Y H:i', strtotime($news['published_at'])) ?>
                                </small>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($news['excerpt'])): ?>
                            <p class="mt-3 mb-0">
                                <small><?= escape($news['excerpt']) ?></small>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-4">
                    <h6>O que será excluído:</h6>
                    <ul>
                        <li>Todos os dados da notícia</li>
                        <li>A imagem destacada (se houver)</li>
                        <?php if ($news['status'] === 'published'): ?>
                            <li class="text-danger">
                                <strong>A notícia está PUBLICADA - links externos podem quebrar</strong>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <form method="POST" class="mt-4">
                    <div class="d-flex gap-3 justify-content-end">
                        <a href="<?= url('admin/news/news-list.php') ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Sim, Excluir Definitivamente
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Opções Alternativas -->
        <div class="alert alert-info mt-4">
            <h6><i class="fas fa-lightbulb"></i> Alternativas à exclusão:</h6>
            <p class="mb-2">Em vez de excluir, você pode:</p>
            <ul class="mb-0">
                <li>
                    <strong>Arquivar:</strong> 
                    <a href="<?= url('admin/news/news-edit.php?id=' . $id) ?>">Editar a notícia</a> e mudar o status para "Arquivada"
                </li>
                <li>
                    <strong>Despublicar:</strong> 
                    Mudar o status para "Rascunho" para remover do site mas manter os dados
                </li>
            </ul>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<?php
$pageTitle = 'Editar Notícia';
include '../includes/header.php';
require_once '../../includes/editorjs-loader.php';

$db = Database::getInstance();
$id = intval($_GET['id'] ?? 0);

// Validar ID
if ($id <= 0) {
    flash('error', 'ID da notícia inválido.');
    redirect(url('admin/news.php'));
}

// Buscar notícia
$news = $db->fetch("SELECT * FROM news WHERE id = ?", [$id]);
if (!$news) {
    flash('error', 'Notícia não encontrada.');
    redirect(url('admin/news.php'));
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'excerpt' => trim($_POST['excerpt'] ?? ''),
        'content' => $_POST['content'] ?? '',
        'featured_image' => trim($_POST['featured_image'] ?? ''),
        'category' => trim($_POST['category'] ?? ''),
        'status' => $_POST['status'] ?? 'draft',
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
    ];
    
    // Atualizar published_at se mudou para publicado
    if ($data['status'] === 'published' && $news['status'] !== 'published') {
        $data['published_at'] = date('Y-m-d H:i:s');
    }
    
    // Gerar slug se necessário
    if (empty($data['slug']) && !empty($data['title'])) {
        $data['slug'] = generateSlug($data['title']);
    }
    
    if (!$data['title']) {
        flash('error', 'Informe o título da notícia.');
    } else {
        $db->update('news', $data, 'id = :id', ['id' => $id]);
        flash('success', 'Notícia atualizada com sucesso!');
        // Recarregar dados
        $news = $db->fetch("SELECT * FROM news WHERE id = ?", [$id]);
    }
}

showFlashMessages();
EditorJSLoader::renderStyles();
?>

<!-- Navegação -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="<?= url('admin/news.php') ?>" class="btn btn-secondary">
        ← Voltar para Notícias
    </a>
    <div>
        <span class="badge bg-info">ID: <?= $news['id'] ?></span>
        <?php if ($news['status'] === 'published'): ?>
            <a href="<?= url('noticias/' . $news['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-external-link-alt"></i> Ver Publicada
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Formulário de Edição -->
<div class="row">
    <!-- Coluna Principal -->
    <div class="col-lg-8">
        <div class="card p-4 mb-4">
            <div class="mb-3">
                <small class="text-muted">
                    Criada em: <?= date('d/m/Y H:i', strtotime($news['created_at'])) ?>
                    <?php if ($news['updated_at']): ?>
                        | Última atualização: <?= date('d/m/Y H:i', strtotime($news['updated_at'])) ?>
                    <?php endif; ?>
                </small>
            </div>

            <form method="POST" id="newsForm">
                <!-- Título -->
                <div class="mb-4">
                    <label class="form-label">Título da Notícia *</label>
                    <input type="text" name="title" id="newsTitle" class="form-control form-control-lg" value="<?= escape($news['title']) ?>" required>
                </div>

                <!-- Slug -->
                <div class="mb-4">
                    <label class="form-label">Slug (URL amigável)</label>
                    <div class="input-group">
                        <span class="input-group-text"><?= url('noticias/') ?></span>
                        <input type="text" name="slug" id="newsSlug" class="form-control" value="<?= escape($news['slug']) ?>">
                    </div>
                    <small class="form-text text-muted">
                        <?php if ($news['status'] === 'published'): ?>
                            <i class="fas fa-exclamation-triangle text-warning"></i> 
                            Cuidado ao alterar o slug de uma notícia publicada - links antigos não funcionarão
                        <?php else: ?>
                            Deixe em branco para gerar automaticamente a partir do título
                        <?php endif; ?>
                    </small>
                </div>

                <!-- Resumo/Excerpt -->
                <div class="mb-4">
                    <label class="form-label">Resumo</label>
                    <textarea name="excerpt" class="form-control" rows="3"><?= escape($news['excerpt'] ?? '') ?></textarea>
                    <small class="form-text text-muted">Este texto aparecerá nas listagens e compartilhamentos</small>
                </div>

                <hr class="my-4">

                <!-- Conteúdo -->
                <div class="mb-4">
                    <label for="content" class="form-label">Conteúdo da Notícia</label>
                    <!-- Container do Editor.js com toolbar fixa -->
                    <div id="editorjs"></div>
                    <!-- Textarea original (oculto, recebe o JSON) -->
                    <textarea class="form-control d-none" id="content" name="content" rows="20"><?= escape($news['content'] ?? '') ?></textarea>
                </div>
            </form>
        </div>

        <!-- Estatísticas (se implementado) -->
        <?php if (isset($news['views']) || isset($news['likes'])): ?>
        <div class="card p-3 mb-4">
            <h6 class="mb-3">Estatísticas</h6>
            <div class="row text-center">
                <?php if (isset($news['views'])): ?>
                <div class="col">
                    <div class="fs-4 text-primary"><?= number_format($news['views']) ?></div>
                    <small class="text-muted">Visualizações</small>
                </div>
                <?php endif; ?>
                <?php if (isset($news['likes'])): ?>
                <div class="col">
                    <div class="fs-4 text-danger"><?= number_format($news['likes']) ?></div>
                    <small class="text-muted">Curtidas</small>
                </div>
                <?php endif; ?>
                <?php if (isset($news['comments_count'])): ?>
                <div class="col">
                    <div class="fs-4 text-success"><?= number_format($news['comments_count']) ?></div>
                    <small class="text-muted">Comentários</small>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar de Configurações -->
    <div class="col-lg-4">
        <!-- Status e Publicação -->
        <div class="card p-3 mb-3">
            <h6 class="mb-3">Publicação</h6>
            
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" form="newsForm" class="form-control">
                    <option value="draft" <?= $news['status'] === 'draft' ? 'selected' : '' ?>>Rascunho</option>
                    <option value="published" <?= $news['status'] === 'published' ? 'selected' : '' ?>>Publicado</option>
                    <option value="scheduled" <?= $news['status'] === 'scheduled' ? 'selected' : '' ?>>Agendado</option>
                    <option value="archived" <?= $news['status'] === 'archived' ? 'selected' : '' ?>>Arquivado</option>
                </select>
            </div>

            <?php if ($news['published_at']): ?>
            <div class="mb-3">
                <small class="text-muted">
                    <i class="fas fa-calendar"></i> 
                    Publicado em: <?= date('d/m/Y H:i', strtotime($news['published_at'])) ?>
                </small>
            </div>
            <?php endif; ?>

            <div class="mb-3">
                <label class="d-flex align-items-center gap-2">
                    <input type="checkbox" name="is_featured" form="newsForm" <?= ($news['is_featured'] ?? 0) ? 'checked' : '' ?>>
                    <span>Destacar na página inicial</span>
                </label>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" form="newsForm" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar Alterações
                </button>
                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                    <i class="fas fa-trash"></i> Excluir
                </button>
            </div>
        </div>

        <!-- Categoria -->
        <div class="card p-3 mb-3">
            <h6 class="mb-3">Categoria</h6>
            
            <select name="category" form="newsForm" class="form-control">
                <option value="">Selecione...</option>
                <?php
                $categories = [
                    'lancamentos' => 'Lançamentos',
                    'tutoriais' => 'Tutoriais',
                    'industria' => 'Indústria',
                    'eventos' => 'Eventos',
                    'entrevistas' => 'Entrevistas',
                    'comunidade' => 'Comunidade'
                ];
                foreach ($categories as $key => $label):
                ?>
                    <option value="<?= $key ?>" <?= ($news['category'] ?? '') === $key ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Imagem Destacada -->
        <div class="card p-3 mb-3">
            <h6 class="mb-3">Imagem Destacada</h6>
            
            <div class="mb-3">
                <input type="text" name="featured_image" id="featuredImageUrl" form="newsForm" class="form-control" value="<?= escape($news['featured_image'] ?? '') ?>" placeholder="URL da imagem">
            </div>

            <?php if (!empty($news['featured_image'])): ?>
            <div class="mb-2">
                <img src="<?= escape($news['featured_image']) ?>" alt="Preview" class="img-fluid rounded" style="max-height: 200px;">
            </div>
            <?php endif; ?>

            <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="openImageUploader()">
                <i class="fas fa-upload"></i> Alterar Imagem
            </button>
        </div>

        <!-- Autor -->
        <div class="card p-3">
            <h6 class="mb-3">Autor</h6>
            <div class="d-flex align-items-center gap-2">
                <?php
                $author = $db->fetch("SELECT * FROM users WHERE id = ?", [$news['author_id']]);
                if ($author):
                ?>
                    <div class="flex-grow-1">
                        <div><?= escape($author['name']) ?></div>
                        <small class="text-muted"><?= escape($author['email']) ?></small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-gerar slug a partir do título (apenas se slug estiver vazio)
document.getElementById('newsTitle').addEventListener('input', function() {
    const slugField = document.getElementById('newsSlug');
    if (!slugField.value) {
        slugField.placeholder = generateSlugFromTitle(this.value);
    }
});

function generateSlugFromTitle(title) {
    return title
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^\w\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/--+/g, '-')
        .trim();
}

function openImageUploader() {
    // Implementar modal de upload
    alert('Funcionalidade de upload em desenvolvimento');
}

function confirmDelete() {
    if (confirm('Tem certeza que deseja excluir esta notícia? Esta ação não pode ser desfeita.')) {
        window.location.href = '<?= url('admin/news/delete.php?id=' . $id) ?>';
    }
}

// Prevenção de perda de dados
let formChanged = false;
document.getElementById('newsForm').addEventListener('change', function() {
    formChanged = true;
});

document.getElementById('newsForm').addEventListener('input', function() {
    formChanged = true;
});

window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = '';
    }
});

document.getElementById('newsForm').addEventListener('submit', function() {
    formChanged = false;
});
</script>

<?php
// Render Editor.js scripts
EditorJSLoader::renderScripts();

// Initialize Editor.js with existing content
EditorJSLoader::init($news['content'] ?? '', 'editorjs', 'content');
?>

<?php include '../includes/footer.php'; ?>
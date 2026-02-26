<?php
$pageTitle = 'Editar Notícia';
include '../includes/header.php';

$db = Database::getInstance();
$id = intval($_GET['id'] ?? 0);

// Validar ID
if ($id <= 0) {
    flash('error', 'ID da notícia inválido.');
    redirect(url('admin/news/news-list.php'));
}

// Buscar notícia
$news = $db->fetch("SELECT * FROM blog_posts WHERE id = ?", [$id]);
if (!$news) {
    flash('error', 'Notícia não encontrada.');
    redirect(url('admin/news/news-list.php'));
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validar campos obrigatórios
    $title = trim($_POST['title'] ?? '');
    if (empty($title)) {
        $errors[] = 'O título é obrigatório.';
    }
    
    $data = [
        'title' => $title,
        'slug' => trim($_POST['slug'] ?? ''),
        'excerpt' => trim($_POST['excerpt'] ?? ''),
        'content' => $_POST['content'] ?? '',
        'featured_image' => trim($_POST['featured_image'] ?? ''),
        'image' => trim($_POST['featured_image'] ?? ''),
        'category_id' => intval($_POST['category_id'] ?? 0) ?: null,
        'status' => $_POST['status'] ?? 'draft',
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'meta_title' => trim($_POST['meta_title'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
    ];
    
    // Atualizar published_at se mudou para publicado
    if ($data['status'] === 'published' && $news['status'] !== 'published') {
        $data['published_at'] = date('Y-m-d H:i:s');
    }
    
    // Gerar slug se necessário
    if (empty($data['slug']) && !empty($data['title'])) {
        $data['slug'] = slugify($data['title']);
    }
    
    // Verificar se slug já existe (em outra notícia)
    $existingSlug = $db->fetch("SELECT id FROM blog_posts WHERE slug = ? AND id != ?", [$data['slug'], $id]);
    if ($existingSlug) {
        $errors[] = 'Este slug já está em uso por outra notícia.';
    }
    
    if (empty($errors)) {
        $db->update('blog_posts', $data, 'id = :id', ['id' => $id]);
        flash('success', 'Notícia atualizada com sucesso!');
        // Recarregar dados
        $news = $db->fetch("SELECT * FROM blog_posts WHERE id = ?", [$id]);
    } else {
        foreach ($errors as $error) {
            flash('error', $error);
        }
    }
}

// Renderizar estilos do EditorJS
EditorJSLoader::renderStyles();
?>

<?= showFlashMessages() ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('admin/dashboard.php') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('admin/news/news-list.php') ?>">Notícias</a></li>
                <li class="breadcrumb-item active">Editar Notícia</li>
            </ol>
        </nav>
        <div class="d-flex gap-2">
            <a href="<?= url('admin/news/news-list.php') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Voltar
            </a>
            <?php if ($news['status'] === 'published'): ?>
                <a href="<?= url('noticias/' . $news['slug']) ?>" target="_blank" class="btn btn-outline-primary">
                    <i class="fas fa-eye me-1"></i> Ver no Site
                </a>
            <?php endif; ?>
        </div>
    </div>

    <form method="POST" id="newsForm">
        <div class="row">
            <!-- Coluna Principal -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-newspaper me-2"></i>Conteúdo da Notícia</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Título da Notícia <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="newsTitle" class="form-control form-control-lg border-2" value="<?= escape($news['title']) ?>" required placeholder="Ex: O Futuro do Phaser 4">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Slug (URL amigável)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">/noticias/</span>
                                <input type="text" name="slug" id="newsSlug" class="form-control" value="<?= escape($news['slug']) ?>" placeholder="o-futuro-do-phaser-4">
                            </div>
                            <?php if ($news['status'] === 'published'): ?>
                                <small class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Alterar o slug de uma notícia publicada pode quebrar links existentes.</small>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Resumo (Excerpt)</label>
                            <textarea name="excerpt" class="form-control" rows="3" placeholder="Um resumo curto para as listagens..."><?= escape($news['excerpt'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold">Corpo da Notícia</label>
                            <div id="editorjs" class="border rounded bg-light" style="min-height: 500px;"></div>
                            <textarea name="content" id="content" class="d-none"><?= $news['content'] ?? '' ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- SEO Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-search me-2"></i>Otimização SEO</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Meta Título</label>
                            <input type="text" name="meta_title" class="form-control border-2" value="<?= escape($news['meta_title'] ?? '') ?>" placeholder="Título para motores de busca">
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold">Meta Descrição</label>
                            <textarea name="meta_description" class="form-control border-2" rows="3" placeholder="Descrição para motores de busca"><?= escape($news['meta_description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barra Lateral -->
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-cog me-2"></i>Publicação</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" class="form-select border-2">
                                <option value="draft" <?= $news['status'] === 'draft' ? 'selected' : '' ?>>Rascunho</option>
                                <option value="published" <?= $news['status'] === 'published' ? 'selected' : '' ?>>Publicado</option>
                                <option value="archived" <?= $news['status'] === 'archived' ? 'selected' : '' ?>>Arquivado</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" <?= ($news['is_featured'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_featured">Notícia em Destaque</label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Categoria</label>
                            <?php
                            $newsCategories = [
                                'lancamentos' => 'Lançamentos',
                                'tutoriais' => 'Tutoriais',
                                'industria' => 'Indústria',
                                'eventos' => 'Eventos'
                            ];
                            ?>
                            <select name="category_id" class="form-select border-2">
                                <option value="">Sem Categoria</option>
                                <?php foreach ($newsCategories as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= ($news['category_id'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <small class="text-muted d-block mb-1">Criado em: <strong><?= date('d/m/Y H:i', strtotime($news['created_at'])) ?></strong></small>
                            <?php if ($news['published_at']): ?>
                                <small class="text-muted d-block">Publicado em: <strong><?= date('d/m/Y H:i', strtotime($news['published_at'])) ?></strong></small>
                            <?php endif; ?>
                        </div>

                        <hr>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary w-100 btn-lg fw-bold shadow-sm">
                                <i class="fas fa-save me-2"></i> Salvar Alterações
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-image me-2"></i>Imagem de Destaque</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">URL da Imagem</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-image"></i></span>
                                <input type="text" name="featured_image" id="featured_image" class="form-control" value="<?= escape($news['featured_image'] ?? '') ?>" placeholder="https://...">
                            </div>
                        </div>
                        <div id="imagePreview" class="mt-3 text-center <?= empty($news['featured_image']) ? 'd-none' : '' ?>">
                            <img src="<?= escape($news['featured_image'] ?? '') ?>" id="previewImg" class="img-fluid rounded shadow-sm" style="max-height: 200px;">
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-danger">
                    <div class="card-body p-3 text-center">
                        <button type="button" class="btn btn-outline-danger w-100" onclick="confirmDelete()">
                            <i class="fas fa-trash me-2"></i> Excluir Notícia
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Auto-slug
document.getElementById('newsTitle').addEventListener('input', function() {
    const slugInput = document.getElementById('newsSlug');
    // Só gera slug se o slug estiver vazio ou for igual ao slug gerado pelo título anterior
    if (this.value) {
        slugInput.placeholder = this.value
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^\w\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/--+/g, '-')
            .trim();
    }
});

// Image Preview
document.getElementById('featured_image').addEventListener('input', function() {
    const preview = document.getElementById('imagePreview');
    const img = document.getElementById('previewImg');
    if (this.value) {
        img.src = this.value;
        preview.classList.remove('d-none');
    } else {
        preview.classList.add('d-none');
    }
});

function confirmDelete() {
    if (confirm('Tem certeza que deseja excluir esta notícia? Esta ação não pode ser desfeita.')) {
        window.location.href = '<?= url('admin/news/news-delete.php?id=' . $id) ?>';
    }
}
</script>

<?php
// Carregar Scripts do EditorJS
EditorJSLoader::renderScripts();
EditorJSLoader::init($news['content'] ?? '', 'editorjs', 'content');
?>

<?php include '../includes/footer.php'; ?>
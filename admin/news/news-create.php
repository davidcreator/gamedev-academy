<?php
$pageTitle = 'Criar Notícia';
include '../includes/header.php';

$db = Database::getInstance();

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validar campos obrigatórios
    $title = trim($_POST['title'] ?? '');
    if (empty($title)) {
        $errors[] = 'O título é obrigatório.';
    }
    
    // Preparar dados
    $data = [
        'title' => $title,
        'slug' => trim($_POST['slug'] ?? ''),
        'excerpt' => trim($_POST['excerpt'] ?? ''),
        'content' => $_POST['content'] ?? '',
        'featured_image' => trim($_POST['featured_image'] ?? ''),
        'image' => trim($_POST['featured_image'] ?? ''),
        'category_id' => intval($_POST['category_id'] ?? 0) ?: null,
        'author_id' => $_SESSION['user_id'] ?? 1,
        'status' => $_POST['status'] ?? 'draft',
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'meta_title' => trim($_POST['meta_title'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
    ];
    
    // Gerar slug automaticamente se não fornecido
    if (empty($data['slug']) && !empty($data['title'])) {
        $data['slug'] = slugify($data['title']);
    }
    
    // Verificar se slug já existe
    $existingSlug = $db->fetch("SELECT id FROM blog_posts WHERE slug = ?", [$data['slug']]);
    if ($existingSlug) {
        $data['slug'] = $data['slug'] . '-' . time();
    }
    
    // Definir data de publicação
    if ($data['status'] === 'published') {
        $data['published_at'] = date('Y-m-d H:i:s');
    }
    
    // Auto-gerar excerpt se vazio
    if (empty($data['excerpt']) && !empty($data['content'])) {
        $data['excerpt'] = substr(strip_tags($data['content']), 0, 160);
    }
    
    if (empty($errors)) {
        $newsId = $db->insert('blog_posts', $data);
        flash('success', 'Notícia criada com sucesso!');
        redirect(url('admin/news/news-edit.php?id=' . $newsId));
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
                <li class="breadcrumb-item active">Nova Notícia</li>
            </ol>
        </nav>
        <div class="d-flex gap-2">
            <a href="<?= url('admin/news/news-list.php') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Voltar
            </a>
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
                            <input type="text" name="title" id="newsTitle" class="form-control form-control-lg border-2" required placeholder="Ex: O Futuro do Phaser 4">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Slug (URL amigável)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">/noticias/</span>
                                <input type="text" name="slug" id="newsSlug" class="form-control" placeholder="o-futuro-do-phaser-4">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Resumo (Excerpt)</label>
                            <textarea name="excerpt" class="form-control" rows="3" placeholder="Um resumo curto para as listagens..."></textarea>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold">Corpo da Notícia</label>
                            <div id="editorjs" class="border rounded bg-light" style="min-height: 500px;"></div>
                            <textarea name="content" id="content" class="d-none"></textarea>
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
                            <input type="text" name="meta_title" class="form-control border-2" placeholder="Título para motores de busca">
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold">Meta Descrição</label>
                            <textarea name="meta_description" class="form-control border-2" rows="3" placeholder="Descrição para motores de busca"></textarea>
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
                                <option value="draft">Rascunho</option>
                                <option value="published">Publicado</option>
                                <option value="archived">Arquivado</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured">
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
                                    <option value="<?= $val ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <hr>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary w-100 btn-lg fw-bold shadow-sm">
                                <i class="fas fa-save me-2"></i> Criar Notícia
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
                                <input type="text" name="featured_image" id="featured_image" class="form-control" placeholder="https://...">
                            </div>
                        </div>
                        <div id="imagePreview" class="mt-3 text-center d-none">
                            <img src="" id="previewImg" class="img-fluid rounded shadow-sm" style="max-height: 200px;">
                        </div>
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
    if (this.value) {
        slugInput.value = this.value
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
</script>

<?php
// Carregar Scripts do EditorJS
EditorJSLoader::renderScripts();
EditorJSLoader::init('', 'editorjs', 'content');
?>

<?php include '../includes/footer.php'; ?>
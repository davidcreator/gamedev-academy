<?php
$pageTitle = 'Criar Notícia';
include '../includes/header.php';
require_once '../../includes/editorjs-loader.php';

$db = Database::getInstance();

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'excerpt' => trim($_POST['excerpt'] ?? ''),
        'content' => $_POST['content'] ?? '',
        'featured_image' => trim($_POST['featured_image'] ?? ''),
        'category' => trim($_POST['category'] ?? ''),
        'author_id' => $_SESSION['user_id'] ?? 1,
        'status' => $_POST['status'] ?? 'draft',
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'published_at' => isset($_POST['publish_now']) ? date('Y-m-d H:i:s') : null,
    ];
    
    // Gerar slug automaticamente se não fornecido
    if (empty($data['slug']) && !empty($data['title'])) {
        $data['slug'] = generateSlug($data['title']);
    }
    
    if (!$data['title']) {
        flash('error', 'Informe o título da notícia.');
    } else {
        $newsId = $db->insert('news', $data);
        flash('success', 'Notícia criada com sucesso!');
        redirect(url('admin/news/edit.php?id=' . $newsId));
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
</div>

<!-- Formulário de Criação -->
<div class="row">
    <!-- Coluna Principal -->
    <div class="col-lg-8">
        <div class="card p-4 mb-4">
            <form method="POST" id="newsForm">
                <!-- Título -->
                <div class="mb-4">
                    <label class="form-label">Título da Notícia *</label>
                    <input type="text" name="title" id="newsTitle" class="form-control form-control-lg" required placeholder="Digite o título da notícia..." autofocus>
                </div>

                <!-- Slug -->
                <div class="mb-4">
                    <label class="form-label">Slug (URL amigável)</label>
                    <div class="input-group">
                        <span class="input-group-text"><?= url('noticias/') ?></span>
                        <input type="text" name="slug" id="newsSlug" class="form-control" placeholder="gerado-automaticamente">
                    </div>
                    <small class="form-text text-muted">Deixe em branco para gerar automaticamente a partir do título</small>
                </div>

                <!-- Resumo/Excerpt -->
                <div class="mb-4">
                    <label class="form-label">Resumo</label>
                    <textarea name="excerpt" class="form-control" rows="3" placeholder="Breve resumo da notícia (opcional, mas recomendado para SEO)"></textarea>
                    <small class="form-text text-muted">Este texto aparecerá nas listagens e compartilhamentos em redes sociais</small>
                </div>

                <hr class="my-4">

                <!-- Conteúdo -->
                <div class="mb-4">
                    <label for="content" class="form-label">Conteúdo da Notícia</label>
                    <!-- Container do Editor.js (visível) -->
                    <div id="editorjs"></div>
                    <!-- Textarea original (oculto, recebe o JSON) -->
                    <textarea class="form-control d-none" id="content" name="content" rows="20"></textarea>
                    <small class="form-text text-muted">
                        Use o editor para criar o conteúdo. Pressione Tab para ver blocos disponíveis.
                    </small>
                </div>
            </form>
        </div>
    </div>

    <!-- Sidebar de Configurações -->
    <div class="col-lg-4">
        <!-- Status e Publicação -->
        <div class="card p-3 mb-3">
            <h6 class="mb-3">Publicação</h6>
            
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" form="newsForm" class="form-control">
                    <option value="draft">Rascunho</option>
                    <option value="published">Publicado</option>
                    <option value="scheduled">Agendado</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="d-flex align-items-center gap-2">
                    <input type="checkbox" name="publish_now" form="newsForm" checked>
                    <span>Publicar agora</span>
                </label>
            </div>

            <div class="mb-3">
                <label class="d-flex align-items-center gap-2">
                    <input type="checkbox" name="is_featured" form="newsForm">
                    <span>Destacar na página inicial</span>
                </label>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" form="newsForm" class="btn btn-primary">
                    <i class="fas fa-save"></i> Criar Notícia
                </button>
                <a href="<?= url('admin/news.php') ?>" class="btn btn-outline-secondary">
                    Cancelar
                </a>
            </div>
        </div>

        <!-- Categoria -->
        <div class="card p-3 mb-3">
            <h6 class="mb-3">Categoria</h6>
            
            <select name="category" form="newsForm" class="form-control">
                <option value="">Selecione...</option>
                <option value="lancamentos">Lançamentos</option>
                <option value="tutoriais">Tutoriais</option>
                <option value="industria">Indústria</option>
                <option value="eventos">Eventos</option>
                <option value="entrevistas">Entrevistas</option>
                <option value="comunidade">Comunidade</option>
            </select>
        </div>

        <!-- Imagem Destacada -->
        <div class="card p-3 mb-3">
            <h6 class="mb-3">Imagem Destacada</h6>
            
            <div class="mb-3">
                <input type="text" name="featured_image" id="featuredImageUrl" form="newsForm" class="form-control" placeholder="URL da imagem">
            </div>

            <div id="imagePreview" class="mb-2" style="display: none;">
                <img id="previewImg" src="" alt="Preview" class="img-fluid rounded" style="max-height: 200px;">
            </div>

            <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="openImageUploader()">
                <i class="fas fa-upload"></i> Fazer Upload
            </button>
            
            <small class="form-text text-muted mt-2 d-block">
                Recomendado: 1200x630px (formato 1.91:1)
            </small>
        </div>

        <!-- Tags (se implementado) -->
        <div class="card p-3">
            <h6 class="mb-3">Tags</h6>
            <input type="text" class="form-control" placeholder="unity, c#, tutorial" disabled>
            <small class="form-text text-muted">Em desenvolvimento</small>
        </div>
    </div>
</div>

<script>
// Auto-gerar slug a partir do título
document.getElementById('newsTitle').addEventListener('input', function() {
    const slug = generateSlugFromTitle(this.value);
    document.getElementById('newsSlug').placeholder = slug;
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

// Preview de imagem
document.getElementById('featuredImageUrl').addEventListener('input', function() {
    const url = this.value;
    const preview = document.getElementById('imagePreview');
    const img = document.getElementById('previewImg');
    
    if (url) {
        img.src = url;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
});

function openImageUploader() {
    // Implementar modal de upload ou abrir seletor de arquivos
    alert('Funcionalidade de upload em desenvolvimento');
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

// Initialize Editor.js (sem conteúdo inicial)
EditorJSLoader::init('', 'editorjs', 'content');
?>

<?php include '../includes/footer.php'; ?>
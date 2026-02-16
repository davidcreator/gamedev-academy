<?php
$pageTitle = 'Criar Notícia';
include '../includes/header.php';
require_once '../../includes/editorjs-loader.php';

$db = Database::getInstance();

// Função auxiliar para gerar slug
function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

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
        'category' => trim($_POST['category'] ?? ''),
        'author_id' => $_SESSION['user_id'] ?? 1,
        'status' => $_POST['status'] ?? 'draft',
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'meta_title' => trim($_POST['meta_title'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
    ];
    
    // Gerar slug automaticamente se não fornecido
    if (empty($data['slug']) && !empty($data['title'])) {
        $data['slug'] = generateSlug($data['title']);
    }
    
    // Verificar se slug já existe
    $existingSlug = $db->fetch("SELECT id FROM news WHERE slug = ?", [$data['slug']]);
    if ($existingSlug) {
        $data['slug'] = $data['slug'] . '-' . time();
    }
    
    // Definir data de publicação
    if ($data['status'] === 'published' && isset($_POST['publish_now'])) {
        $data['published_at'] = date('Y-m-d H:i:s');
    } elseif ($data['status'] === 'scheduled' && !empty($_POST['scheduled_date'])) {
        $data['published_at'] = $_POST['scheduled_date'];
    }
    
    // Auto-gerar excerpt se vazio
    if (empty($data['excerpt']) && !empty($data['content'])) {
        $contentData = json_decode($data['content'], true);
        if (isset($contentData['blocks'][0]['data']['text'])) {
            $data['excerpt'] = substr(strip_tags($contentData['blocks'][0]['data']['text']), 0, 160);
        }
    }
    
    if (empty($errors)) {
        $newsId = $db->insert('news', $data);
        flash('success', 'Notícia criada com sucesso!');
        redirect(url('admin/news/news-edit.php?id=' . $newsId));
    } else {
        foreach ($errors as $error) {
            flash('error', $error);
        }
    }
}

showFlashMessages();
EditorJSLoader::renderStyles();
?>

<!-- Navegação -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="<?= url('admin/news/news-list.php') ?>" class="btn btn-secondary">
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
                    <!-- Container do Editor.js com toolbar fixa -->
                    <div id="editorjs"></div>
                    <!-- Textarea original (oculto, recebe o JSON) -->
                    <textarea class="form-control d-none" id="content" name="content" rows="20"></textarea>
                    <small class="form-text text-muted">
                        Use a barra de ferramentas para formatar seu conteúdo.
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
                <select name="status" id="newsStatus" form="newsForm" class="form-control">
                    <option value="draft">Rascunho</option>
                    <option value="published">Publicado</option>
                    <option value="scheduled">Agendado</option>
                </select>
            </div>

            <div class="mb-3" id="publishNowGroup">
                <label class="d-flex align-items-center gap-2">
                    <input type="checkbox" name="publish_now" form="newsForm" checked>
                    <span>Publicar agora</span>
                </label>
            </div>

            <div class="mb-3" id="scheduledDateGroup" style="display: none;">
                <label class="form-label">Data e Hora Agendada</label>
                <input type="datetime-local" name="scheduled_date" form="newsForm" class="form-control">
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
                <a href="<?= url('admin/news/news-list.php') ?>" class="btn btn-outline-secondary">
                    Cancelar
                </a>
            </div>
        </div>

        <!-- Categoria -->
        <div class="card p-3 mb-3">
            <h6 class="mb-3">Categoria</h6>
            
            <select name="category" form="newsForm" class="form-control">
                <option value="">Sem categoria</option>
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

        <!-- SEO -->
        <div class="card p-3">
            <h6 class="mb-3">
                <i class="fas fa-search"></i> SEO
            </h6>
            
            <div class="mb-3">
                <label class="form-label">Meta Título</label>
                <input type="text" name="meta_title" form="newsForm" class="form-control" placeholder="Deixe vazio para usar o título da notícia" maxlength="60">
                <small class="form-text text-muted">Máximo 60 caracteres</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Meta Descrição</label>
                <textarea name="meta_description" form="newsForm" class="form-control" rows="3" placeholder="Deixe vazio para usar o resumo" maxlength="160"></textarea>
                <small class="form-text text-muted">Máximo 160 caracteres</small>
            </div>
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

// Controlar exibição de campos baseado no status
document.getElementById('newsStatus').addEventListener('change', function() {
    const publishNowGroup = document.getElementById('publishNowGroup');
    const scheduledDateGroup = document.getElementById('scheduledDateGroup');
    
    if (this.value === 'scheduled') {
        publishNowGroup.style.display = 'none';
        scheduledDateGroup.style.display = 'block';
    } else if (this.value === 'published') {
        publishNowGroup.style.display = 'block';
        scheduledDateGroup.style.display = 'none';
    } else {
        publishNowGroup.style.display = 'none';
        scheduledDateGroup.style.display = 'none';
    }
});

// Preview de imagem
document.getElementById('featuredImageUrl').addEventListener('input', function() {
    const url = this.value;
    const preview = document.getElementById('imagePreview');
    const img = document.getElementById('previewImg');
    
    if (url) {
        img.src = url;
        img.onerror = function() {
            preview.style.display = 'none';
        };
        img.onload = function() {
            preview.style.display = 'block';
        };
    } else {
        preview.style.display = 'none';
    }
});

// Contador de caracteres para SEO
const metaTitle = document.querySelector('input[name="meta_title"]');
const metaDescription = document.querySelector('textarea[name="meta_description"]');

if (metaTitle) {
    metaTitle.addEventListener('input', function() {
        const length = this.value.length;
        const small = this.nextElementSibling;
        small.textContent = `${length}/60 caracteres`;
        if (length > 60) {
            small.classList.add('text-danger');
        } else {
            small.classList.remove('text-danger');
        }
    });
}

if (metaDescription) {
    metaDescription.addEventListener('input', function() {
        const length = this.value.length;
        const small = this.nextElementSibling;
        small.textContent = `${length}/160 caracteres`;
        if (length > 160) {
            small.classList.add('text-danger');
        } else {
            small.classList.remove('text-danger');
        }
    });
}

function openImageUploader() {
    // Implementar modal de upload ou abrir seletor de arquivos
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = function(e) {
        const file = e.target.files[0];
        if (file) {
            // Implementar upload aqui
            alert('Upload de imagem: implementar integração com endpoint');
        }
    };
    input.click();
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
<?php
$pageTitle = 'Editar Notícia';
include '../includes/header.php';
require_once '../../includes/editorjs-loader.php';

$db = Database::getInstance();
$id = intval($_GET['id'] ?? 0);

// Validar ID
if ($id <= 0) {
    flash('error', 'ID da notícia inválido.');
    redirect(url('admin/news/news.php'));
}

// Buscar notícia
$news = $db->fetch("SELECT * FROM news WHERE id = ?", [$id]);
if (!$news) {
    flash('error', 'Notícia não encontrada.');
    redirect(url('admin/news/news.php'));
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
        'category' => trim($_POST['category'] ?? ''),
        'status' => $_POST['status'] ?? 'draft',
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'meta_title' => trim($_POST['meta_title'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
    ];
    
    // Atualizar published_at se mudou para publicado
    if ($data['status'] === 'published' && $news['status'] !== 'published') {
        $data['published_at'] = date('Y-m-d H:i:s');
    } elseif ($data['status'] === 'scheduled' && !empty($_POST['scheduled_date'])) {
        $data['published_at'] = $_POST['scheduled_date'];
    }
    
    // Gerar slug se necessário
    if (empty($data['slug']) && !empty($data['title'])) {
        $data['slug'] = generateSlug($data['title']);
    }
    
    // Verificar se slug já existe (em outra notícia)
    $existingSlug = $db->fetch("SELECT id FROM news WHERE slug = ? AND id != ?", [$data['slug'], $id]);
    if ($existingSlug) {
        $errors[] = 'Este slug já está em uso por outra notícia.';
    }
    
    if (empty($errors)) {
        $db->update('news', $data, 'id = :id', ['id' => $id]);
        flash('success', 'Notícia atualizada com sucesso!');
        // Recarregar dados
        $news = $db->fetch("SELECT * FROM news WHERE id = ?", [$id]);
    } else {
        foreach ($errors as $error) {
            flash('error', $error);
        }
    }
}

// Função auxiliar para gerar slug
function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

showFlashMessages();
EditorJSLoader::renderStyles();
?>

<!-- Navegação -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="<?= url('admin/news/news.php') ?>" class="btn btn-secondary">
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
                <select name="status" id="newsStatus" form="newsForm" class="form-control">
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

            <div class="mb-3" id="scheduledDateGroup" style="display: <?= $news['status'] === 'scheduled' ? 'block' : 'none' ?>;">
                <label class="form-label">Data e Hora Agendada</label>
                <input type="datetime-local" name="scheduled_date" form="newsForm" class="form-control" value="<?= $news['published_at'] ? date('Y-m-d\TH:i', strtotime($news['published_at'])) : '' ?>">
            </div>

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
                <option value="">Sem categoria</option>
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
            <div class="mb-2" id="imagePreview">
                <img id="previewImg" src="<?= escape($news['featured_image']) ?>" alt="Preview" class="img-fluid rounded" style="max-height: 200px;">
            </div>
            <?php else: ?>
            <div id="imagePreview" class="mb-2" style="display: none;">
                <img id="previewImg" src="" alt="Preview" class="img-fluid rounded" style="max-height: 200px;">
            </div>
            <?php endif; ?>

            <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="openImageUploader()">
                <i class="fas fa-upload"></i> Alterar Imagem
            </button>
        </div>

        <!-- SEO -->
        <div class="card p-3 mb-3">
            <h6 class="mb-3">
                <i class="fas fa-search"></i> SEO
            </h6>
            
            <div class="mb-3">
                <label class="form-label">Meta Título</label>
                <input type="text" name="meta_title" form="newsForm" class="form-control" value="<?= escape($news['meta_title'] ?? '') ?>" placeholder="Deixe vazio para usar o título" maxlength="60">
                <small class="form-text text-muted">Máximo 60 caracteres</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Meta Descrição</label>
                <textarea name="meta_description" form="newsForm" class="form-control" rows="3" placeholder="Deixe vazio para usar o resumo" maxlength="160"><?= escape($news['meta_description'] ?? '') ?></textarea>
                <small class="form-text text-muted">Máximo 160 caracteres</small>
            </div>
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

// Controlar exibição de campos baseado no status
document.getElementById('newsStatus').addEventListener('change', function() {
    const scheduledDateGroup = document.getElementById('scheduledDateGroup');
    
    if (this.value === 'scheduled') {
        scheduledDateGroup.style.display = 'block';
    } else {
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
    const updateMetaTitleCount = function() {
        const length = this.value.length;
        const small = this.nextElementSibling;
        small.textContent = `${length}/60 caracteres`;
        if (length > 60) {
            small.classList.add('text-danger');
        } else {
            small.classList.remove('text-danger');
        }
    };
    metaTitle.addEventListener('input', updateMetaTitleCount);
    updateMetaTitleCount.call(metaTitle); // Executar uma vez ao carregar
}

if (metaDescription) {
    const updateMetaDescCount = function() {
        const length = this.value.length;
        const small = this.nextElementSibling;
        small.textContent = `${length}/160 caracteres`;
        if (length > 160) {
            small.classList.add('text-danger');
        } else {
            small.classList.remove('text-danger');
        }
    };
    metaDescription.addEventListener('input', updateMetaDescCount);
    updateMetaDescCount.call(metaDescription); // Executar uma vez ao carregar
}

function openImageUploader() {
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

function confirmDelete() {
    if (confirm('Tem certeza que deseja excluir esta notícia? Esta ação não pode ser desfeita.')) {
        window.location.href = '<?= url('admin/news/news-delete.php?id=' . $id) ?>';
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
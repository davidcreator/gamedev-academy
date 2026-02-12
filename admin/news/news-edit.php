<?php
session_start();
require_once '../config/database.php';

// Verificar se o ID foi passado
$news_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($news_id <= 0) {
    $_SESSION['error_message'] = "ID da notícia inválido.";
    header('Location: news-list.php');
    exit;
}

// Buscar dados da notícia
try {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([$news_id]);
    $news = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$news) {
        $_SESSION['error_message'] = "Notícia não encontrada.";
        header('Location: news-list.php');
        exit;
    }
} catch(PDOException $e) {
    die("Erro ao buscar notícia: " . $e->getMessage());
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $content = $_POST['content'] ?? '';
    $excerpt = $_POST['excerpt'] ?? '';
    $category = $_POST['category'] ?? '';
    $status = $_POST['status'] ?? 'draft';
    $featured_image = $_POST['featured_image'] ?? '';
    
    // Gerar slug se não informado
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE news SET 
                title = ?,
                slug = ?,
                content = ?,
                excerpt = ?,
                category = ?,
                status = ?,
                featured_image = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->execute([
            $title, $slug, $content, $excerpt, 
            $category, $status, $featured_image, $news_id
        ]);
        
        $_SESSION['success_message'] = "Notícia atualizada com sucesso!";
        header("Location: news-edit.php?id=$news_id");
        exit;
    } catch(PDOException $e) {
        $error = "Erro ao atualizar notícia: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Notícia - GameDev Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include '../includes/admin-header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/admin-sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-pencil-square"></i> Editar Notícia
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="previewContent()">
                            <i class="bi bi-eye"></i> Preview
                        </button>
                        <a href="news-list.php" class="btn btn-sm btn-outline-secondary ms-2">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
                
                <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $_SESSION['success_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>
                
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="bi bi-file-text"></i> Conteúdo</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Título da Notícia <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?php echo htmlspecialchars($news['title'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="slug" class="form-label">Slug (URL)</label>
                                        <input type="text" class="form-control" id="slug" name="slug" 
                                               value="<?php echo htmlspecialchars($news['slug'] ?? ''); ?>"
                                               placeholder="deixe-em-branco-para-gerar-automaticamente">
                                        <small class="text-muted">Deixe em branco para gerar automaticamente</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="excerpt" class="form-label">Resumo</label>
                                        <textarea class="form-control" id="excerpt" name="excerpt" rows="3"
                                                  placeholder="Breve descrição da notícia..."><?php echo htmlspecialchars($news['excerpt'] ?? ''); ?></textarea>
                                        <small class="text-muted">Aparece na listagem de notícias</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="content" class="form-label">Conteúdo Completo <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="content" name="content" rows="15"><?php echo htmlspecialchars($news['content'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="bi bi-gear"></i> Configurações</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="category" class="form-label">Categoria</label>
                                        <select class="form-select" id="category" name="category">
                                            <option value="">Selecione...</option>
                                            <option value="noticias" <?php echo (($news['category'] ?? '') == 'noticias') ? 'selected' : ''; ?>>Notícias</option>
                                            <option value="tutoriais" <?php echo (($news['category'] ?? '') == 'tutoriais') ? 'selected' : ''; ?>>Tutoriais</option>
                                            <option value="reviews" <?php echo (($news['category'] ?? '') == 'reviews') ? 'selected' : ''; ?>>Reviews</option>
                                            <option value="dicas" <?php echo (($news['category'] ?? '') == 'dicas') ? 'selected' : ''; ?>>Dicas</option>
                                            <option value="eventos" <?php echo (($news['category'] ?? '') == 'eventos') ? 'selected' : ''; ?>>Eventos</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="draft" <?php echo (($news['status'] ?? 'draft') == 'draft') ? 'selected' : ''; ?>>Rascunho</option>
                                            <option value="published" <?php echo (($news['status'] ?? '') == 'published') ? 'selected' : ''; ?>>Publicado</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="featured_image" class="form-label">Imagem Destacada</label>
                                        <input type="url" class="form-control" id="featured_image" name="featured_image" 
                                               value="<?php echo htmlspecialchars($news['featured_image'] ?? ''); ?>"
                                               placeholder="https://exemplo.com/imagem.jpg">
                                    </div>
                                    
                                    <?php if (!empty($news['featured_image'])): ?>
                                    <div class="mb-3">
                                        <img src="<?php echo htmlspecialchars($news['featured_image']); ?>" 
                                             class="img-fluid rounded" alt="Preview">
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save"></i> Salvar Alterações
                                        </button>
                                        <a href="news.php?id=<?php echo $news_id; ?>" class="btn btn-outline-info">
                                            <i class="bi bi-eye"></i> Visualizar
                                        </a>
                                        <a href="news-list.php" class="btn btn-outline-secondary">
                                            <i class="bi bi-list"></i> Voltar à Lista
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Informações</h6>
                                </div>
                                <div class="card-body">
                                    <small class="text-muted">
                                        <strong>ID:</strong> #<?php echo $news['id']; ?><br>
                                        <?php if (!empty($news['created_at'])): ?>
                                        <strong>Criado:</strong> <?php echo date('d/m/Y H:i', strtotime($news['created_at'])); ?><br>
                                        <?php endif; ?>
                                        <?php if (!empty($news['updated_at'])): ?>
                                        <strong>Atualizado:</strong> <?php echo date('d/m/Y H:i', strtotime($news['updated_at'])); ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>
    
    <!-- Modal de Preview -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Preview da Notícia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="previewContent"></div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/tinymce/tinymce.min.js"></script>
    
    <script>
        // Inicialização do TinyMCE
        tinymce.init({
            selector: '#content',
            height: 500,
            language: 'pt_BR',
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount codesample',
            toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | image link media | codesample code | fullscreen preview | help',
            menubar: 'file edit view insert format tools table help',
            branding: false,
            promotion: false,
            codesample_languages: [
                { text: 'HTML/XML', value: 'markup' },
                { text: 'JavaScript', value: 'javascript' },
                { text: 'CSS', value: 'css' },
                { text: 'PHP', value: 'php' },
                { text: 'C#', value: 'csharp' },
                { text: 'C++', value: 'cpp' },
                { text: 'Python', value: 'python' },
                { text: 'GDScript', value: 'gdscript' }
            ],
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save();
                });
            }
        });
        
        // Auto-gerar slug
        document.getElementById('title').addEventListener('blur', function() {
            const slugField = document.getElementById('slug');
            if (slugField.value === '') {
                const slug = this.value
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/(^-|-$)/g, '');
                slugField.value = slug;
            }
        });
        
        // Função de Preview
        function previewContent() {
            const title = document.getElementById('title').value;
            const content = tinymce.get('content').getContent();
            const excerpt = document.getElementById('excerpt').value;
            const image = document.getElementById('featured_image').value;
            
            let imageHtml = '';
            if (image) {
                imageHtml = `<img src="${image}" class="img-fluid rounded mb-4" alt="Imagem destacada">`;
            }
            
            document.getElementById('previewContent').innerHTML = `
                <article>
                    ${imageHtml}
                    <h1 class="mb-3">${title || 'Título da Notícia'}</h1>
                    ${excerpt ? `<p class="lead text-muted">${excerpt}</p><hr>` : ''}
                    <div class="content">${content || '<p>Conteúdo da notícia...</p>'}</div>
                </article>
            `;
            
            new bootstrap.Modal(document.getElementById('previewModal')).show();
        }
        
        // Sincronizar TinyMCE antes de enviar
        document.querySelector('form').addEventListener('submit', function() {
            if (tinymce.get('content')) {
                tinymce.get('content').save();
            }
        });
    </script>
</body>
</html>
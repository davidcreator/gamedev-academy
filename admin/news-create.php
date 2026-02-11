<?php
/**
 * news-create.php - Criar Nova Notícia
 * GameDev Academy - Admin Panel
 */

session_start();

// Verificar autenticação
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
require_once '../app/Models/NewsModel.php';

$pageTitle = 'Criar Notícia';
$currentPage = 'news';

$errors = [];
$success = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validação
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // Validações
    if (empty($title)) {
        $errors[] = 'O título é obrigatório.';
    }
    
    if (empty($content)) {
        $errors[] = 'O conteúdo é obrigatório.';
    }
    
    // Gerar slug automático se vazio
    if (empty($slug)) {
        $slug = createSlug($title);
    }
    
    // Upload de imagem
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadImage($_FILES['image']);
        if ($uploadResult['success']) {
            $imagePath = $uploadResult['path'];
        } else {
            $errors[] = $uploadResult['error'];
        }
    }
    
    // Salvar no banco
    if (empty($errors)) {
        try {
            $pdo = getConnection();
            $stmt = $pdo->prepare("
                INSERT INTO news (title, slug, content, excerpt, category, image, status, featured, author_id, created_at, updated_at)
                VALUES (:title, :slug, :content, :excerpt, :category, :image, :status, :featured, :author_id, NOW(), NOW())
            ");
            
            $stmt->execute([
                ':title' => $title,
                ':slug' => $slug,
                ':content' => $content,
                ':excerpt' => $excerpt,
                ':category' => $category,
                ':image' => $imagePath,
                ':status' => $status,
                ':featured' => $featured,
                ':author_id' => $_SESSION['admin_id'] ?? 1
            ]);
            
            $success = 'Notícia criada com sucesso!';
            
            // Redirecionar após 2 segundos
            header('Refresh: 2; URL=news.php');
            
        } catch (PDOException $e) {
            $errors[] = 'Erro ao salvar: ' . $e->getMessage();
        }
    }
}

/**
 * Criar slug a partir do título
 */
function createSlug($string) {
    $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    $slug = preg_replace('/[^a-zA-Z0-9\s-]/', '', $slug);
    $slug = strtolower(trim($slug));
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    return $slug;
}

/**
 * Upload de imagem
 */
function uploadImage($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'error' => 'Tipo de arquivo não permitido.'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'Arquivo muito grande (máx. 5MB).'];
    }
    
    $uploadDir = '../uploads/news/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('news_') . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'path' => 'uploads/news/' . $filename];
    }
    
    return ['success' => false, 'error' => 'Erro ao fazer upload.'];
}

// Incluir header
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="admin-main">
    <div class="admin-content">
        <!-- Breadcrumb -->
        <nav class="breadcrumb-nav">
            <ol class="breadcrumb">
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="news.php">Notícias</a></li>
                <li class="active">Criar Notícia</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-plus-circle"></i> Criar Nova Notícia</h1>
            <a href="news.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>

        <!-- Alertas -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <!-- Formulário -->
        <form action="" method="POST" enctype="multipart/form-data" class="admin-form">
            <div class="form-grid">
                <!-- Coluna Principal -->
                <div class="form-main">
                    <div class="card">
                        <div class="card-header">
                            <h3>Informações da Notícia</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="title">Título *</label>
                                <input 
                                    type="text" 
                                    id="title" 
                                    name="title" 
                                    class="form-control"
                                    value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                                    required
                                    placeholder="Digite o título da notícia"
                                >
                            </div>

                            <div class="form-group">
                                <label for="slug">Slug (URL amigável)</label>
                                <input 
                                    type="text" 
                                    id="slug" 
                                    name="slug" 
                                    class="form-control"
                                    value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>"
                                    placeholder="deixe-em-branco-para-gerar-automaticamente"
                                >
                                <small class="form-text">Deixe em branco para gerar automaticamente</small>
                            </div>

                            <div class="form-group">
                                <label for="excerpt">Resumo</label>
                                <textarea 
                                    id="excerpt" 
                                    name="excerpt" 
                                    class="form-control"
                                    rows="3"
                                    placeholder="Breve descrição da notícia"
                                ><?= htmlspecialchars($_POST['excerpt'] ?? '') ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="content">Conteúdo *</label>
                                <textarea 
                                    id="content" 
                                    name="content" 
                                    class="form-control editor"
                                    rows="15"
                                    required
                                    placeholder="Digite o conteúdo completo da notícia"
                                ><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Coluna Lateral -->
                <div class="form-sidebar">
                    <!-- Publicação -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Publicação</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="draft" <?= ($_POST['status'] ?? '') === 'draft' ? 'selected' : '' ?>>
                                        Rascunho
                                    </option>
                                    <option value="published" <?= ($_POST['status'] ?? '') === 'published' ? 'selected' : '' ?>>
                                        Publicado
                                    </option>
                                    <option value="scheduled" <?= ($_POST['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>
                                        Agendado
                                    </option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input 
                                        type="checkbox" 
                                        name="featured" 
                                        value="1"
                                        <?= isset($_POST['featured']) ? 'checked' : '' ?>
                                    >
                                    <span>Notícia em destaque</span>
                                </label>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save"></i> Salvar Notícia
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Categoria -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Categoria</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <select id="category" name="category" class="form-control">
                                    <option value="">Selecione...</option>
                                    <option value="lancamento" <?= ($_POST['category'] ?? '') === 'lancamento' ? 'selected' : '' ?>>
                                        Lançamento
                                    </option>
                                    <option value="atualizacao" <?= ($_POST['category'] ?? '') === 'atualizacao' ? 'selected' : '' ?>>
                                        Atualização
                                    </option>
                                    <option value="tutorial" <?= ($_POST['category'] ?? '') === 'tutorial' ? 'selected' : '' ?>>
                                        Tutorial
                                    </option>
                                    <option value="evento" <?= ($_POST['category'] ?? '') === 'evento' ? 'selected' : '' ?>>
                                        Evento
                                    </option>
                                    <option value="promocao" <?= ($_POST['category'] ?? '') === 'promocao' ? 'selected' : '' ?>>
                                        Promoção
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Imagem Destacada -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Imagem Destacada</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="image-upload-area" id="imageUploadArea">
                                    <input 
                                        type="file" 
                                        id="image" 
                                        name="image" 
                                        accept="image/*"
                                        class="image-input"
                                    >
                                    <div class="upload-placeholder">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p>Clique ou arraste uma imagem</p>
                                        <small>JPG, PNG, GIF ou WebP (máx. 5MB)</small>
                                    </div>
                                    <img id="imagePreview" class="image-preview" style="display: none;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
// Auto-gerar slug do título
document.getElementById('title').addEventListener('blur', function() {
    const slugField = document.getElementById('slug');
    if (slugField.value === '') {
        const slug = this.value
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-');
        slugField.value = slug;
    }
});

// Preview da imagem
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            const placeholder = document.querySelector('.upload-placeholder');
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        }
        reader.readAsDataURL(file);
    }
});
</script>

<?php include 'includes/footer.php'; ?>
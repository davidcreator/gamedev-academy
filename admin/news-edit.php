<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

require_once '../config/database.php';

$page_title = "Editar Notícia";
$edit_mode = false;
$news = [
    'title' => '',
    'slug' => '',
    'content' => '',
    'excerpt' => '',
    'category' => 'geral',
    'tags' => '',
    'status' => 'draft',
    'featured' => 0
];

// Se está editando
if (isset($_GET['id'])) {
    $edit_mode = true;
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $news = $stmt->fetch();
    
    if (!$news) {
        header('Location: news.php');
        exit();
    }
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $slug = $_POST['slug'] ?: preg_replace('/[^a-z0-9-]/', '-', strtolower($title));
    $content = $_POST['content'];
    $excerpt = $_POST['excerpt'];
    $category = $_POST['category'];
    $tags = $_POST['tags'];
    $status = $_POST['status'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // Upload de imagem
    $image = $news['image'] ?? null;
    $thumbnail = $news['thumbnail'] ?? null;
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = '../uploads/news/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['image']['name']);
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image = 'uploads/news/' . $file_name;
            $thumbnail = $image; // Por enquanto, usar a mesma imagem
        }
    }
    
    if ($edit_mode) {
        // Atualizar
        $sql = "UPDATE news SET 
                title = ?, slug = ?, content = ?, excerpt = ?, 
                category = ?, tags = ?, image = ?, thumbnail = ?,
                status = ?, featured = ?, updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $title, $slug, $content, $excerpt, 
            $category, $tags, $image, $thumbnail,
            $status, $featured, $_GET['id']
        ]);
        
        $_SESSION['success'] = "Notícia atualizada com sucesso!";
    } else {
        // Criar nova
        $sql = "INSERT INTO news 
                (title, slug, content, excerpt, category, tags, 
                 image, thumbnail, status, featured, author_id, published_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $published_at = ($status == 'published') ? date('Y-m-d H:i:s') : null;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $title, $slug, $content, $excerpt, 
            $category, $tags, $image, $thumbnail,
            $status, $featured, $_SESSION['user_id'], $published_at
        ]);
        
        $_SESSION['success'] = "Notícia criada com sucesso!";
    }
    
    header('Location: news.php');
    exit();
}

$categories = ['geral', 'anuncios', 'cursos', 'tutoriais', 'dicas', 'eventos'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $edit_mode ? 'Editar' : 'Nova' ?> Notícia - Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- TinyMCE Editor -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#content',
            height: 400,
            plugins: 'advlist autolink lists link image charmap preview anchor pagebreak code fullscreen media table',
            toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | removeformat code',
            content_css: 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css'
        });
    </script>
    
    <style>
        body { background-color: #f8f9fa; }
        .form-card { background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">🎮 GameDev Academy - Admin</span>
            <a href="news.php" class="btn btn-outline-light">← Voltar</a>
        </div>
    </nav>

    <div class="container">
        <div class="form-card">
            <h2><?= $edit_mode ? 'Editar' : 'Nova' ?> Notícia</h2>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="title" class="form-label">Título *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?= htmlspecialchars($news['title']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug (URL)</label>
                            <input type="text" class="form-control" id="slug" name="slug" 
                                   value="<?= htmlspecialchars($news['slug']) ?>"
                                   placeholder="Deixe em branco para gerar automaticamente">
                        </div>
                        
                        <div class="mb-3">
                            <label for="excerpt" class="form-label">Resumo</label>
                            <textarea class="form-control" id="excerpt" name="excerpt" rows="3"><?= htmlspecialchars($news['excerpt']) ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Conteúdo *</label>
                            <textarea id="content" name="content" required><?= htmlspecialchars($news['content']) ?></textarea>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="category" class="form-label">Categoria</label>
                            <select class="form-select" id="category" name="category">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat ?>" <?= $news['category'] == $cat ? 'selected' : '' ?>>
                                        <?= ucfirst($cat) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="draft" <?= $news['status'] == 'draft' ? 'selected' : '' ?>>Rascunho</option>
                                <option value="published" <?= $news['status'] == 'published' ? 'selected' : '' ?>>Publicado</option>
                                <option value="archived" <?= $news['status'] == 'archived' ? 'selected' : '' ?>>Arquivado</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tags" class="form-label">Tags</label>
                            <input type="text" class="form-control" id="tags" name="tags" 
                                   value="<?= htmlspecialchars($news['tags']) ?>"
                                   placeholder="Separadas por vírgula">
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Imagem de Capa</label>
                            <?php if ($news['image']): ?>
                                <img src="../<?= $news['image'] ?>" class="img-thumbnail mb-2" style="max-height: 150px;">
                            <?php endif; ?>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="featured" name="featured" 
                                   <?= $news['featured'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="featured">
                                <i class="fas fa-star text-warning"></i> Notícia em Destaque
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> <?= $edit_mode ? 'Atualizar' : 'Criar' ?> Notícia
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
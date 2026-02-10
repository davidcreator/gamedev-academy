<?php
session_start();

// Configuração de erro para desenvolvimento
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============ CONEXÃO COM BANCO ============
// Ajuste estas configurações conforme necessário
$db_host = 'localhost';
$db_name = 'gamedev_academy';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

// ============ VERIFICAÇÃO DE LOGIN ============
if (!isset($_SESSION['user_id'])) {
    // Comentado para testes - descomente em produção
    // header('Location: login.php');
    // exit;
    
    // Usuario temporário para testes - remova em produção
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'Admin';
}

// ============ FUNÇÕES AUXILIARES ============
function uploadImage($file, $old_image_url = null) {
    $upload_dir = '../uploads/news/';
    
    // Criar diretório se não existir
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    // Validar tipo de arquivo
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception('Tipo de arquivo não permitido. Use: JPG, PNG, GIF ou WebP');
    }
    
    // Validar tamanho
    if ($file['size'] > $max_size) {
        throw new Exception('Arquivo muito grande. Máximo: 5MB');
    }
    
    // Gerar nome único
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'news_' . uniqid() . '_' . time() . '.' . $extension;
    $upload_path = $upload_dir . $filename;
    
    // Fazer upload
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        // Deletar imagem antiga se existir
        if ($old_image_url && file_exists('../' . $old_image_url)) {
            unlink('../' . $old_image_url);
        }
        
        return 'uploads/news/' . $filename;
    } else {
        throw new Exception('Erro ao fazer upload da imagem');
    }
}

function generateSlug($title) {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

// ============ INICIALIZAÇÃO DE VARIÁVEIS ============
$news = null;
$error = '';
$success = '';
$authors = [];
$categories = [];
$all_tags = [];

// ============ BUSCAR DADOS AUXILIARES ============
try {
    // Buscar autores
    $stmt = $pdo->query("SELECT id, username, email FROM users WHERE role IN ('admin', 'instructor', 'moderator') ORDER BY username");
    $authors = $stmt->fetchAll();
    
    // Buscar categorias únicas
    $stmt = $pdo->query("SELECT DISTINCT category FROM news WHERE category IS NOT NULL AND category != '' ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Buscar todas as tags existentes
    $stmt = $pdo->query("SELECT DISTINCT tags FROM news WHERE tags IS NOT NULL AND tags != ''");
    $tags_rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tags_rows as $tags_str) {
        $tags = explode(',', $tags_str);
        foreach ($tags as $tag) {
            $tag = trim($tag);
            if ($tag && !in_array($tag, $all_tags)) {
                $all_tags[] = $tag;
            }
        }
    }
    sort($all_tags);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar dados auxiliares: " . $e->getMessage());
}

// ============ BUSCAR NOTÍCIA PARA EDIÇÃO ============
if (isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    
    if ($id) {
        try {
            $sql = "SELECT n.*, u.username as author_name 
                    FROM news n 
                    LEFT JOIN users u ON n.author_id = u.id 
                    WHERE n.id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $news = $stmt->fetch();
            
            if (!$news) {
                header('Location: news.php?error=not_found');
                exit;
            }
        } catch (PDOException $e) {
            $error = "Erro ao buscar notícia: " . $e->getMessage();
        }
    } else {
        header('Location: news.php?error=invalid_id');
        exit;
    }
} else {
    header('Location: news.php?error=no_id');
    exit;
}

// ============ PROCESSAR FORMULÁRIO DE ATUALIZAÇÃO ============
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $news) {
    
    // Receber dados do formulário
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $author_id = filter_input(INPUT_POST, 'author_id', FILTER_VALIDATE_INT) ?: $_SESSION['user_id'];
    $status = $_POST['status'] ?? 'draft';
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // Validações básicas
    $errors = [];
    
    if (empty($title)) {
        $errors[] = "O título é obrigatório";
    } elseif (strlen($title) > 255) {
        $errors[] = "O título não pode ter mais de 255 caracteres";
    }
    
    if (empty($content)) {
        $errors[] = "O conteúdo é obrigatório";
    }
    
    if (empty($slug)) {
        $slug = generateSlug($title);
    }
    
    // Verificar se o slug já existe (exceto para a notícia atual)
    try {
        $stmt = $pdo->prepare("SELECT id FROM news WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $id]);
        if ($stmt->fetch()) {
            $slug = $slug . '-' . uniqid();
        }
    } catch (PDOException $e) {
        $errors[] = "Erro ao verificar slug";
    }
    
    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    } else {
        try {
            $pdo->beginTransaction();
            
            // Processar upload de imagem
            $image_url = $news['image_url'];
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                try {
                    $image_url = uploadImage($_FILES['image'], $news['image_url']);
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
            
            // Determinar published_at
            $published_at = $news['published_at'];
            if ($status == 'published' && !$published_at) {
                $published_at = date('Y-m-d H:i:s');
            }
            
            // Atualizar notícia
            $sql = "UPDATE news SET 
                    title = :title,
                    slug = :slug,
                    summary = :summary,
                    content = :content,
                    image_url = :image_url,
                    category = :category,
                    tags = :tags,
                    author_id = :author_id,
                    status = :status,
                    featured = :featured,
                    published_at = :published_at,
                    updated_at = NOW()
                    WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                ':title' => $title,
                ':slug' => $slug,
                ':summary' => $summary,
                ':content' => $content,
                ':image_url' => $image_url,
                ':category' => $category,
                ':tags' => $tags,
                ':author_id' => $author_id,
                ':status' => $status,
                ':featured' => $featured,
                ':published_at' => $published_at,
                ':id' => $id
            ]);
            
            if ($result) {
                // Registrar atividade (se a tabela activity_logs existir)
                try {
                    $activity_sql = "INSERT INTO activity_logs (user_id, action, target_type, target_id, details, created_at) 
                                     VALUES (?, 'update', 'news', ?, ?, NOW())";
                    $activity_stmt = $pdo->prepare($activity_sql);
                    $activity_stmt->execute([
                        $_SESSION['user_id'],
                        $id,
                        json_encode(['title' => $title, 'status' => $status])
                    ]);
                } catch (PDOException $e) {
                    // Ignorar erro se a tabela não existir
                }
                
                $pdo->commit();
                $success = "Notícia atualizada com sucesso!";
                
                // Recarregar dados atualizados
                $stmt = $pdo->prepare("SELECT n.*, u.username as author_name 
                                        FROM news n 
                                        LEFT JOIN users u ON n.author_id = u.id 
                                        WHERE n.id = ?");
                $stmt->execute([$id]);
                $news = $stmt->fetch();
                
            } else {
                throw new Exception("Erro ao atualizar notícia");
            }
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Erro ao salvar: " . $e->getMessage();
        }
    }
}

// ============ HTML DA PÁGINA ============
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Notícia - GameDev Academy</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Editor de Texto Rico -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    
    <!-- Tags Input -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
    
    <style>
        :root {
            --primary-color: #6f42c1;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #0dcaf0;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
        .main-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #8e24aa 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .main-header h1 {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 600;
        }
        
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 0;
        }
        
        .breadcrumb-item + .breadcrumb-item::before {
            color: rgba(255,255,255,0.7);
        }
        
        .breadcrumb-item a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
        }
        
        .breadcrumb-item.active {
            color: rgba(255,255,255,0.7);
        }
        
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background-color: rgba(111, 66, 193, 0.05);
            border-bottom: 1px solid rgba(111, 66, 193, 0.125);
            font-weight: 600;
            padding: 1rem 1.25rem;
        }
        
        .required::after {
            content: " *";
            color: var(--danger-color);
        }
        
        .preview-image {
            max-width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 0.375rem;
            border: 1px solid #dee2e6;
        }
        
        .current-image-container {
            position: relative;
            display: inline-block;
        }
        
        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
            border-radius: 0.375rem;
        }
        
        .current-image-container:hover .image-overlay {
            opacity: 1;
        }
        
        .status-badge {
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 600;
            border-radius: 0.375rem;
        }
        
        .status-draft {
            background-color: var(--warning-color);
            color: #000;
        }
        
        .status-published {
            background-color: var(--success-color);
            color: white;
        }
        
        .status-archived {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .tagify {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            min-height: calc(1.5em + 0.75rem + 2px);
        }
        
        .tagify--focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        
        .info-item {
            display: flex;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-item i {
            width: 20px;
            color: var(--primary-color);
            margin-right: 0.5rem;
        }
        
        .btn-action {
            padding: 0.625rem 1.25rem;
            font-weight: 500;
            border-radius: 0.375rem;
            transition: all 0.3s ease;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .alert {
            border: none;
            border-radius: 0.5rem;
        }
        
        .note-editor {
            border-radius: 0.375rem;
        }
        
        @media (max-width: 768px) {
            .main-header h1 {
                font-size: 1.5rem;
            }
            
            .btn-group-action {
                flex-direction: column;
            }
            
            .btn-group-action .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="main-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1>Editar Notícia</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="news.php">Notícias</a></li>
                            <li class="breadcrumb-item active">Editar</li>
                        </ol>
                    </nav>
                </div>
                <div class="btn-group-action">
                    <a href="news.php" class="btn btn-light me-2">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                    <?php if ($news && $news['status'] == 'published'): ?>
                    <a href="../news/<?php echo $news['slug']; ?>" target="_blank" class="btn btn-info">
                        <i class="bi bi-eye"></i> Visualizar
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Conteúdo Principal -->
    <div class="container-fluid">
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if ($news): ?>
        <form method="POST" enctype="multipart/form-data" id="newsEditForm">
            <div class="row">
                <!-- Coluna Principal -->
                <div class="col-lg-8">
                    <!-- Informações Básicas -->
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-newspaper me-2"></i>
                            Informações da Notícia
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="title" class="form-label required">Título</label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="title" 
                                       name="title" 
                                       value="<?php echo htmlspecialchars($news['title']); ?>" 
                                       required 
                                       maxlength="255"
                                       placeholder="Digite o título da notícia">
                                <div class="form-text">Máximo de 255 caracteres</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="slug" class="form-label">Slug (URL)</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-link-45deg"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control" 
                                           id="slug" 
                                           name="slug" 
                                           value="<?php echo htmlspecialchars($news['slug']); ?>"
                                           pattern="[a-z0-9-]+"
                                           placeholder="url-amigavel">
                                </div>
                                <div class="form-text">Deixe em branco para gerar automaticamente a partir do título</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="summary" class="form-label">Resumo</label>
                                <textarea class="form-control" 
                                          id="summary" 
                                          name="summary" 
                                          rows="3"
                                          placeholder="Breve descrição da notícia (aparece nas listagens)"><?php echo htmlspecialchars($news['summary'] ?? ''); ?></textarea>
                                <div class="form-text">
                                    <span id="summaryCount">0</span> caracteres
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="content" class="form-label required">Conteúdo</label>
                                <textarea class="form-control" 
                                          id="content" 
                                          name="content" 
                                          required><?php echo htmlspecialchars($news['content']); ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Categorização -->
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-tags me-2"></i>
                            Categorização
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="category" class="form-label">Categoria</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="category" 
                                               name="category" 
                                               list="categoriesList"
                                               value="<?php echo htmlspecialchars($news['category'] ?? ''); ?>"
                                               placeholder="Ex: Tutoriais, Novidades, etc">
                                        <datalist id="categoriesList">
                                            <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo htmlspecialchars($cat); ?>">
                                            <?php endforeach; ?>
                                        </datalist>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tags" class="form-label">Tags</label>
                                        <input type="text" 
                                               id="tags" 
                                               name="tags" 
                                               value="<?php echo htmlspecialchars($news['tags'] ?? ''); ?>"
                                               placeholder="Digite as tags...">
                                        <div class="form-text">Separe as tags com vírgulas</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Coluna Lateral -->
                <div class="col-lg-4">
                    <!-- Status e Publicação -->
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-gear me-2"></i>
                            Configurações
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="draft" <?php echo $news['status'] == 'draft' ? 'selected' : ''; ?>>
                                        📝 Rascunho
                                    </option>
                                    <option value="published" <?php echo $news['status'] == 'published' ? 'selected' : ''; ?>>
                                        ✅ Publicado
                                    </option>
                                    <option value="archived" <?php echo $news['status'] == 'archived' ? 'selected' : ''; ?>>
                                        📦 Arquivado
                                    </option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="author_id" class="form-label">Autor</label>
                                <select class="form-select" id="author_id" name="author_id">
                                    <option value="<?php echo $_SESSION['user_id']; ?>">
                                        <?php echo $_SESSION['username']; ?> (Você)
                                    </option>
                                    <?php foreach ($authors as $author): ?>
                                        <?php if ($author['id'] != $_SESSION['user_id']): ?>
                                        <option value="<?php echo $author['id']; ?>" 
                                                <?php echo $news['author_id'] == $author['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($author['username']); ?>
                                        </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="featured" 
                                       name="featured" 
                                       <?php echo $news['featured'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="featured">
                                    ⭐ Notícia em Destaque
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Imagem -->
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-image me-2"></i>
                            Imagem de Capa
                        </div>
                        <div class="card-body">
                            <?php if ($news['image_url']): ?>
                            <div class="mb-3">
                                <label class="form-label">Imagem Atual:</label>
                                <div class="current-image-container">
                                    <img src="../<?php echo htmlspecialchars($news['image_url']); ?>" 
                                         alt="Imagem atual" 
                                         class="preview-image">
                                    <div class="image-overlay">
                                        <span>Clique em "Escolher arquivo" para substituir</span>
                                    </div>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                Nenhuma imagem definida
                            </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="image" class="form-label">Nova Imagem</label>
                                <input type="file" 
                                       class="form-control" 
                                       id="image" 
                                       name="image" 
                                       accept="image/*">
                                <div class="form-text">
                                    JPG, PNG, GIF ou WebP (Máx: 5MB)
                                </div>
                            </div>
                            
                            <div id="imagePreview" style="display: none;">
                                <label class="form-label">Preview:</label>
                                <img id="preview" class="preview-image" alt="Preview">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informações -->
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-info-circle me-2"></i>
                            Informações
                        </div>
                        <div class="card-body p-0">
                            <div class="info-item px-3">
                                <i class="bi bi-calendar-plus"></i>
                                <div>
                                    <small class="text-muted">Criado em:</small><br>
                                    <strong><?php echo date('d/m/Y H:i', strtotime($news['created_at'])); ?></strong>
                                </div>
                            </div>
                            
                            <?php if ($news['updated_at']): ?>
                            <div class="info-item px-3">
                                <i class="bi bi-calendar-check"></i>
                                <div>
                                    <small class="text-muted">Atualizado em:</small><br>
                                    <strong><?php echo date('d/m/Y H:i', strtotime($news['updated_at'])); ?></strong>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($news['published_at']): ?>
                            <div class="info-item px-3">
                                <i class="bi bi-calendar2-check"></i>
                                <div>
                                    <small class="text-muted">Publicado em:</small><br>
                                    <strong><?php echo date('d/m/Y H:i', strtotime($news['published_at'])); ?></strong>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="info-item px-3">
                                <i class="bi bi-eye"></i>
                                <div>
                                    <small class="text-muted">Visualizações:</small><br>
                                    <strong><?php echo number_format($news['views_count'], 0, ',', '.'); ?></strong>
                                </div>
                            </div>
                            
                            <div class="info-item px-3">
                                <i class="bi bi-person"></i>
                                <div>
                                    <small class="text-muted">Autor:</small><br>
                                    <strong><?php echo htmlspecialchars($news['author_name'] ?? 'Desconhecido'); ?></strong>
                                </div>
                            </div>
                            
                            <div class="info-item px-3">
                                <i class="bi bi-hash"></i>
                                <div>
                                    <small class="text-muted">ID:</small><br>
                                    <strong>#<?php echo $news['id']; ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Ações -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-action">
                            <i class="bi bi-save me-2"></i>
                            Salvar Alterações
                        </button>
                        
                        <a href="news-preview.php?id=<?php echo $news['id']; ?>" 
                           class="btn btn-secondary btn-action"
                           target="_blank">
                            <i class="bi bi-eye me-2"></i>
                            Preview
                        </a>
                        
                        <button type="button" 
                                class="btn btn-danger btn-action" 
                                onclick="confirmDelete()">
                            <i class="bi bi-trash me-2"></i>
                            Excluir Notícia
                        </button>
                    </div>
                </div>
            </div>
        </form>
        
        <!-- Form oculto para deletar -->
        <form id="deleteForm" method="POST" action="news-delete.php" style="display: none;">
            <input type="hidden" name="id" value="<?php echo $news['id']; ?>">
            <input type="hidden" name="confirm" value="1">
        </form>
        <?php endif; ?>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/lang/summernote-pt-BR.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    
    <script>
        $(document).ready(function() {
            // Inicializar Summernote
            $('#content').summernote({
                height: 400,
                lang: 'pt-BR',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video', 'hr']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                callbacks: {
                    onImageUpload: function(files) {
                        // Implementar upload de imagem via AJAX se necessário
                    }
                }
            });
            
            // Inicializar Tagify
            var input = document.querySelector('#tags');
            new Tagify(input, {
                whitelist: <?php echo json_encode($all_tags); ?>,
                dropdown: {
                    maxItems: 20,
                    classname: "tags-look",
                    enabled: 0,
                    closeOnSelect: false
                }
            });
            
            // Contador de caracteres do resumo
            $('#summary').on('input', function() {
                $('#summaryCount').text($(this).val().length);
            }).trigger('input');
            
            // Preview de imagem
            $('#image').on('change', function(e) {
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#preview').attr('src', e.target.result);
                        $('#imagePreview').show();
                    }
                    reader.readAsDataURL(file);
                } else {
                    $('#imagePreview').hide();
                }
            });
            
            // Auto-gerar slug
            $('#title').on('blur', function() {
                const slugField = $('#slug');
                if (!slugField.val().trim()) {
                    let slug = $(this).val()
                        .toLowerCase()
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '')
                        .replace(/[^\w\s-]/g, '')
                        .replace(/\s+/g, '-')
                        .replace(/-+/g, '-')
                        .trim();
                    slugField.val(slug);
                }
            });
            
            // Validação do formulário
            $('#newsEditForm').on('submit', function(e) {
                const title = $('#title').val().trim();
                const content = $('#content').summernote('code').trim();
                
                if (!title) {
                    e.preventDefault();
                    alert('Por favor, preencha o título da notícia.');
                    $('#title').focus();
                    return false;
                }
                
                if (!content || content === '<p><br></p>') {
                    e.preventDefault();
                    alert('Por favor, preencha o conteúdo da notícia.');
                    return false;
                }
                
                // Mostrar loading
                $(this).find('button[type="submit"]').prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm me-2"></span>Salvando...');
            });
        });
        
        // Confirmação de exclusão
        function confirmDelete() {
            if (confirm('⚠️ ATENÇÃO!\n\nTem certeza que deseja excluir esta notícia?\n\nEsta ação não pode ser desfeita!')) {
                if (confirm('Esta é a última confirmação. Deseja realmente excluir?')) {
                    document.getElementById('deleteForm').submit();
                }
            }
        }
        
        // Auto-save como rascunho (opcional)
        let autoSaveTimer;
        function autoSave() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(function() {
                // Implementar auto-save via AJAX se necessário
                console.log('Auto-save would trigger here');
            }, 30000); // 30 segundos
        }
        
        // Ativar auto-save nos campos principais
        $('#title, #summary').on('input', autoSave);
        $('#content').on('summernote.change', autoSave);
    </script>
</body>
</html>
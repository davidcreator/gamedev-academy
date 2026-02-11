<?php
// admin/lessons/edit.php

require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/editor-config.php';

// Verificar autenticação admin
checkAdminAuth();

$lesson_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$lesson = null;
$modules = [];
$errors = [];
$success = '';

// Buscar módulos para o select
try {
    $stmt = $pdo->query("
        SELECT m.id, m.title, c.title as course_title 
        FROM modules m 
        JOIN courses c ON m.course_id = c.id 
        ORDER BY c.title, m.order_number
    ");
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = "Erro ao carregar módulos: " . $e->getMessage();
}

// Se editando, buscar lição existente
if ($lesson_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ?");
        $stmt->execute([$lesson_id]);
        $lesson = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$lesson) {
            header('Location: index.php?error=lesson_not_found');
            exit;
        }
    } catch (PDOException $e) {
        $errors[] = "Erro ao carregar lição: " . $e->getMessage();
    }
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $module_id = (int)$_POST['module_id'];
    $title = trim($_POST['title']);
    $content = $_POST['content']; // Conteúdo do editor
    $video_url = trim($_POST['video_url'] ?? '');
    $duration = (int)($_POST['duration'] ?? 0);
    $order_number = (int)($_POST['order_number'] ?? 0);
    $is_free = isset($_POST['is_free']) ? 1 : 0;
    $status = $_POST['status'] ?? 'draft';
    
    // Validações
    if (empty($title)) {
        $errors[] = "O título é obrigatório.";
    }
    if ($module_id <= 0) {
        $errors[] = "Selecione um módulo.";
    }
    if (empty($content)) {
        $errors[] = "O conteúdo da lição é obrigatório.";
    }
    
    // Sanitizar conteúdo HTML (importante para segurança!)
    $content = sanitizeHtmlContent($content);
    
    if (empty($errors)) {
        try {
            if ($lesson_id > 0) {
                // Atualizar lição existente
                $stmt = $pdo->prepare("
                    UPDATE lessons SET 
                        module_id = ?,
                        title = ?,
                        content = ?,
                        video_url = ?,
                        duration = ?,
                        order_number = ?,
                        is_free = ?,
                        status = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([
                    $module_id, $title, $content, $video_url,
                    $duration, $order_number, $is_free, $status, $lesson_id
                ]);
                $success = "Lição atualizada com sucesso!";
            } else {
                // Criar nova lição
                $stmt = $pdo->prepare("
                    INSERT INTO lessons 
                    (module_id, title, content, video_url, duration, order_number, is_free, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $module_id, $title, $content, $video_url,
                    $duration, $order_number, $is_free, $status
                ]);
                $lesson_id = $pdo->lastInsertId();
                $success = "Lição criada com sucesso!";
            }
            
            // Recarregar dados
            $stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ?");
            $stmt->execute([$lesson_id]);
            $lesson = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            $errors[] = "Erro ao salvar: " . $e->getMessage();
        }
    }
}

$pageTitle = $lesson_id > 0 ? 'Editar Lição' : 'Nova Lição';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - GameDev Academy Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/editor.css">
    
    <!-- TinyMCE -->
    <script src="<?php echo EditorConfig::getScriptUrl(); ?>" referrerpolicy="origin"></script>
</head>
<body>
    <?php include '../includes/admin-header.php'; ?>
    
    <div class="admin-container">
        <div class="admin-content">
            <div class="page-header">
                <h1><?php echo $pageTitle; ?></h1>
                <a href="index.php" class="btn btn-secondary">← Voltar</a>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="lesson-form">
                <div class="form-grid">
                    <!-- Coluna Principal -->
                    <div class="form-main">
                        <div class="form-group">
                            <label for="title">Título da Lição *</label>
                            <input 
                                type="text" 
                                id="title" 
                                name="title" 
                                value="<?php echo htmlspecialchars($lesson['title'] ?? ''); ?>"
                                required
                                placeholder="Ex: Introdução ao Unity"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="lesson-content">Conteúdo da Lição *</label>
                            <textarea 
                                id="lesson-content" 
                                name="content"
                                placeholder="Escreva o conteúdo da lição aqui..."
                            ><?php echo htmlspecialchars($lesson['content'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Sidebar -->
                    <div class="form-sidebar">
                        <div class="sidebar-box">
                            <h3>Publicação</h3>
                            
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status">
                                    <option value="draft" <?php echo ($lesson['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>
                                        Rascunho
                                    </option>
                                    <option value="published" <?php echo ($lesson['status'] ?? '') === 'published' ? 'selected' : ''; ?>>
                                        Publicado
                                    </option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input 
                                        type="checkbox" 
                                        name="is_free" 
                                        value="1"
                                        <?php echo ($lesson['is_free'] ?? 0) ? 'checked' : ''; ?>
                                    >
                                    Lição gratuita (preview)
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block">
                                💾 Salvar Lição
                            </button>
                        </div>
                        
                        <div class="sidebar-box">
                            <h3>Módulo</h3>
                            
                            <div class="form-group">
                                <label for="module_id">Módulo *</label>
                                <select id="module_id" name="module_id" required>
                                    <option value="">Selecione...</option>
                                    <?php foreach ($modules as $module): ?>
                                        <option 
                                            value="<?php echo $module['id']; ?>"
                                            <?php echo ($lesson['module_id'] ?? 0) == $module['id'] ? 'selected' : ''; ?>
                                        >
                                            <?php echo htmlspecialchars($module['course_title'] . ' → ' . $module['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="order_number">Ordem</label>
                                <input 
                                    type="number" 
                                    id="order_number" 
                                    name="order_number"
                                    value="<?php echo $lesson['order_number'] ?? 0; ?>"
                                    min="0"
                                >
                            </div>
                        </div>
                        
                        <div class="sidebar-box">
                            <h3>Mídia</h3>
                            
                            <div class="form-group">
                                <label for="video_url">URL do Vídeo</label>
                                <input 
                                    type="url" 
                                    id="video_url" 
                                    name="video_url"
                                    value="<?php echo htmlspecialchars($lesson['video_url'] ?? ''); ?>"
                                    placeholder="https://youtube.com/watch?v=..."
                                >
                                <small>YouTube, Vimeo ou MP4 direto</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="duration">Duração (minutos)</label>
                                <input 
                                    type="number" 
                                    id="duration" 
                                    name="duration"
                                    value="<?php echo $lesson['duration'] ?? 0; ?>"
                                    min="0"
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <?php 
    // Inicializa o editor TinyMCE
    echo EditorConfig::renderInitScript('lesson'); 
    ?>
</body>
</html>
<?php
// Debug: verificar caminho
$db_path = '../../config/database.php';
echo "Caminho: " . realpath($db_path) . "<br>";
echo "Arquivo existe: SIM<br><br>";

echo "<strong>Conteúdo do database.php:</strong><br>";
echo "<pre>";
echo htmlspecialchars(file_get_contents($db_path));
echo "</pre>";

echo "<hr>";
echo "<strong>Tentando incluir...</strong><br>";

require_once $db_path;

echo "Variável \$pdo existe? " . (isset($pdo) ? 'SIM' : 'NÃO') . "<br>";

if (isset($pdo)) {
    echo "Tipo: " . get_class($pdo) . "<br>";
    echo "<strong style='color:green'>CONEXÃO OK!</strong>";
} else {
    echo "<strong style='color:red'>CONEXÃO FALHOU!</strong><br>";
    echo "Constantes definidas:<br>";
    echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'NÃO DEFINIDA') . "<br>";
    echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'NÃO DEFINIDA') . "<br>";
    echo "DB_USER: " . (defined('DB_USER') ? DB_USER : 'NÃO DEFINIDA') . "<br>";
}

exit;

// Obter ID da aula
$lesson_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($lesson_id <= 0) {
    $_SESSION['error_message'] = "ID da aula inválido.";
    header('Location: index.php');
    exit;
}

try {
    // Buscar dados da aula
    $stmt = $pdo->prepare("
        SELECT l.*, c.title as course_title
        FROM lessons l 
        LEFT JOIN courses c ON l.course_id = c.id 
        WHERE l.id = ?
    ");
    $stmt->execute([$lesson_id]);
    $lesson = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lesson) {
        $_SESSION['error_message'] = "Aula não encontrada.";
        header('Location: index.php');
        exit;
    }
} catch(PDOException $e) {
    $_SESSION['error_message'] = "Erro ao buscar aula: " . $e->getMessage();
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($lesson['title']); ?> - GameDev Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="../../assets/css/lesson-content.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
</head>
<body>
    <?php include '../../includes/admin-header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/admin-sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Visualizar Aula</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="edit.php?id=<?php echo $lesson_id; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i> Editar
                        </a>
                        <a href="index.php" class="btn btn-sm btn-outline-secondary ms-2">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h2 class="card-title mb-3"><?php echo htmlspecialchars($lesson['title']); ?></h2>
                                
                                <?php if (!empty($lesson['description'])): ?>
                                <p class="lead text-muted mb-4">
                                    <?php echo htmlspecialchars($lesson['description']); ?>
                                </p>
                                <?php endif; ?>
                                
                                <div class="mb-4">
                                    <?php if (!empty($lesson['course_title'])): ?>
                                    <span class="badge bg-primary me-2">
                                        <i class="bi bi-book"></i> <?php echo htmlspecialchars($lesson['course_title']); ?>
                                    </span>
                                    <?php endif; ?>
                                    
                                    <span class="badge bg-<?php echo $lesson['status'] == 'published' ? 'success' : 'warning'; ?> me-2">
                                        <?php echo $lesson['status'] == 'published' ? 'Publicado' : 'Rascunho'; ?>
                                    </span>
                                    
                                    <?php if (!empty($lesson['duration']) && $lesson['duration'] > 0): ?>
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-clock"></i> <?php echo $lesson['duration']; ?> min
                                    </span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($lesson['video_url'])): ?>
                                <div class="ratio ratio-16x9 mb-4">
                                    <?php
                                    $video_url = $lesson['video_url'];
                                    
                                    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $video_url, $matches)) {
                                        echo '<iframe src="https://www.youtube.com/embed/' . $matches[1] . '" allowfullscreen></iframe>';
                                    } elseif (preg_match('/vimeo\.com\/(\d+)/', $video_url, $matches)) {
                                        echo '<iframe src="https://player.vimeo.com/video/' . $matches[1] . '" allowfullscreen></iframe>';
                                    }
                                    ?>
                                </div>
                                <?php endif; ?>
                                
                                <hr class="my-4">
                                
                                <div class="lesson-content">
                                    <?php echo $lesson['content']; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Informações</h5>
                            </div>
                            <div class="card-body">
                                <dl class="row mb-0">
                                    <?php if (!empty($lesson['course_title'])): ?>
                                    <dt class="col-sm-5">Curso:</dt>
                                    <dd class="col-sm-7"><?php echo htmlspecialchars($lesson['course_title']); ?></dd>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($lesson['order_index'])): ?>
                                    <dt class="col-sm-5">Ordem:</dt>
                                    <dd class="col-sm-7"><?php echo $lesson['order_index']; ?></dd>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($lesson['duration'])): ?>
                                    <dt class="col-sm-5">Duração:</dt>
                                    <dd class="col-sm-7"><?php echo $lesson['duration']; ?> min</dd>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($lesson['created_at'])): ?>
                                    <dt class="col-sm-5">Criado:</dt>
                                    <dd class="col-sm-7"><?php echo date('d/m/Y H:i', strtotime($lesson['created_at'])); ?></dd>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($lesson['updated_at'])): ?>
                                    <dt class="col-sm-5">Atualizado:</dt>
                                    <dd class="col-sm-7"><?php echo date('d/m/Y H:i', strtotime($lesson['updated_at'])); ?></dd>
                                    <?php endif; ?>
                                </dl>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="edit.php?id=<?php echo $lesson_id; ?>" class="btn btn-primary">
                                        <i class="bi bi-pencil-square"></i> Editar Aula
                                    </a>
                                    <a href="delete.php?id=<?php echo $lesson_id; ?>" 
                                       class="btn btn-outline-danger"
                                       onclick="return confirm('Tem certeza que deseja excluir esta aula?');">
                                        <i class="bi bi-trash"></i> Excluir Aula
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-csharp.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-python.min.js"></script>
</body>
</html>
<?php
session_start();
require_once '../../config/database.php';

$lesson_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($lesson_id <= 0) {
    die("ID da aula inválido.");
}

try {
    $stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ?");
    $stmt->execute([$lesson_id]);
    $lesson = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lesson) {
        die("Aula não encontrada.");
    }
} catch(PDOException $e) {
    die("Erro ao buscar aula: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($lesson['title'] ?? 'Aula'); ?> - GameDev Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Visualizar Aula</h1>
            <div>
                <a href="edit.php?id=<?php echo $lesson_id; ?>" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Editar
                </a>
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h2><?php echo htmlspecialchars($lesson['title'] ?? ''); ?></h2>
                        
                        <?php if (!empty($lesson['description'])): ?>
                        <p class="lead text-muted">
                            <?php echo htmlspecialchars($lesson['description']); ?>
                        </p>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <!-- Conteúdo do TinyMCE -->
                        <div class="lesson-content">
                            <?php echo $lesson['content'] ?? ''; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Informações</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-5">ID:</dt>
                            <dd class="col-sm-7">#<?php echo $lesson['id']; ?></dd>
                        </dl>
                        
                        <hr>
                        
                        <div class="d-grid gap-2">
                            <a href="edit.php?id=<?php echo $lesson_id; ?>" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                        </div>
                    </div>
                </div>              
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
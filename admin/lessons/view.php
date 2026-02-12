<?php
// lesson-view.php ou similar
session_start();
require_once '../config/database.php';

$lesson_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ? AND status = 'published'");
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($lesson['title']); ?> - GameDev Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .lesson-content {
            font-size: 16px;
            line-height: 1.6;
        }
        .lesson-content img {
            max-width: 100%;
            height: auto;
            margin: 1em 0;
        }
        .lesson-content pre {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .lesson-content code {
            background: #f4f4f4;
            padding: 2px 5px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1><?php echo htmlspecialchars($lesson['title']); ?></h1>
        <p class="text-muted"><?php echo htmlspecialchars($lesson['description']); ?></p>
        
        <hr>
        
        <!-- IMPORTANTE: Não usar htmlspecialchars aqui -->
        <div class="lesson-content">
            <?php echo $lesson['content']; ?>
        </div>
        
        <!-- Se tiver vídeo -->
        <?php if (!empty($lesson['video_url'])): ?>
            <div class="mt-4">
                <h3>Vídeo da Aula</h3>
                <!-- Processar URL do vídeo se necessário -->
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Se usar código com syntax highlighting -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script>
        // Aplicar syntax highlighting se houver código
        if (typeof Prism !== 'undefined') {
            Prism.highlightAll();
        }
    </script>
</body>
</html>
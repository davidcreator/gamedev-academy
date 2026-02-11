<?php
// lessons/view.php (exemplo de exibição)

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$lesson_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Buscar lição
$stmt = $pdo->prepare("
    SELECT l.*, m.title as module_title, c.title as course_title
    FROM lessons l
    JOIN modules m ON l.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE l.id = ? AND l.status = 'published'
");
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lesson) {
    header('HTTP/1.0 404 Not Found');
    include '../404.php';
    exit;
}

// Processar conteúdo
$content = processLessonContent($lesson['content']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($lesson['title']); ?> - GameDev Academy</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/editor.css">
    
    <!-- Syntax Highlighting -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="lesson-page">
        <div class="container">
            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <a href="<?php echo BASE_URL; ?>">Home</a> /
                <a href="<?php echo BASE_URL; ?>/courses/<?php echo $lesson['course_id']; ?>">
                    <?php echo htmlspecialchars($lesson['course_title']); ?>
                </a> /
                <span><?php echo htmlspecialchars($lesson['title']); ?></span>
            </nav>
            
            <article class="lesson-article">
                <header class="lesson-header">
                    <h1><?php echo htmlspecialchars($lesson['title']); ?></h1>
                    <div class="lesson-meta">
                        <span>📚 <?php echo htmlspecialchars($lesson['module_title']); ?></span>
                        <?php if ($lesson['duration'] > 0): ?>
                            <span>⏱️ <?php echo $lesson['duration']; ?> min</span>
                        <?php endif; ?>
                    </div>
                </header>
                
                <?php if (!empty($lesson['video_url'])): ?>
                    <div class="lesson-video">
                        <?php echo embedVideo($lesson['video_url']); ?>
                    </div>
                <?php endif; ?>
                
                <div class="lesson-content">
                    <?php echo $content; ?>
                </div>
            </article>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <!-- Syntax Highlighting -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-csharp.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-python.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
</body>
</html>
<?php
session_start();
require_once '../../config/database.php';

$lesson_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($lesson_id <= 0) {
    die("ID da aula inválido.");
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $content = $_POST['content'] ?? '';
    
    try {
        $stmt = $pdo->prepare("
            UPDATE lessons SET 
                title = ?, 
                description = ?, 
                content = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$title, $description, $content, $lesson_id]);
        
        $success = "Aula atualizada com sucesso!";
    } catch(PDOException $e) {
        $error = "Erro ao atualizar: " . $e->getMessage();
    }
}

// Buscar dados da aula
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
    <title>Editar: <?php echo htmlspecialchars($lesson['title'] ?? 'Aula'); ?> - GameDev Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Editar Aula</h1>
            <div>
                <a href="view.php?id=<?php echo $lesson_id; ?>" class="btn btn-info">
                    <i class="bi bi-eye"></i> Visualizar
                </a>
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
        
        <?php if (isset($success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="title" class="form-label">Título da Aula</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo htmlspecialchars($lesson['title'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Descrição</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($lesson['description'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="content" class="form-label">Conteúdo</label>
                                <textarea class="form-control" id="content" name="content"><?php echo htmlspecialchars($lesson['content'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Ações</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Salvar Alterações
                                </button>
                                <a href="view.php?id=<?php echo $lesson_id; ?>" class="btn btn-outline-info">
                                    <i class="bi bi-eye"></i> Visualizar
                                </a>
                                <button type="button" class="btn btn-outline-danger" 
                                        onclick="if(confirm('Tem certeza que deseja excluir esta aula?')) location.href='delete.php?id=<?php echo $lesson_id; ?>'">
                                    <i class="bi bi-trash"></i> Excluir
                                </button>
                            </div>
                            
                            <?php if (!empty($lesson['created_at'])): ?>
                            <hr>
                            <small class="text-muted">
                                Criado: <?php echo date('d/m/Y H:i', strtotime($lesson['created_at'])); ?>
                            </small>
                            <?php endif; ?>
                            
                            <?php if (!empty($lesson['updated_at'])): ?>
                            <br>
                            <small class="text-muted">
                                Atualizado: <?php echo date('d/m/Y H:i', strtotime($lesson['updated_at'])); ?>
                            </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/tinymce/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: '#content',
            license_key: 'gpl',
            height: 500,
            language: 'pt_BR',
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount codesample',
            toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | codesample code image link | help',
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
                { text: 'Python', value: 'python' }
            ],
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save();
                });
            }
        });

        document.querySelector('form').addEventListener('submit', function() {
            if (tinymce.get('content')) {
                tinymce.get('content').save();
            }
        });
    </script>
</body>
</html>
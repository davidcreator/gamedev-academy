<?php
session_start();
require_once '../../config/database.php';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $content = $_POST['content'] ?? '';
    $duration = $_POST['duration'] ?? null;
    $video_url = $_POST['video_url'] ?? null;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO lessons (title, description, content, duration, video_url, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$title, $description, $content, $duration, $video_url]);
        
        $lesson_id = $pdo->lastInsertId();
        $_SESSION['success_message'] = "Aula criada com sucesso!";
        header("Location: view.php?id=$lesson_id");
        exit;
    } catch(PDOException $e) {
        $error = "Erro ao criar aula: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Nova Aula - GameDev Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                <i class="bi bi-plus-circle"></i> 
                Criar Nova Aula
            </h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
        
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
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-file-text"></i> 
                                Conteúdo da Aula
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="title" class="form-label">
                                    Título da Aula <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="title" 
                                       name="title" 
                                       placeholder="Ex: Introdução ao Unity 3D"
                                       required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">
                                    Descrição Breve
                                </label>
                                <textarea class="form-control" 
                                          id="description" 
                                          name="description" 
                                          rows="3"
                                          placeholder="Uma breve descrição do que será ensinado nesta aula..."></textarea>
                                <small class="text-muted">
                                    Esta descrição aparecerá na listagem de aulas
                                </small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="content" class="form-label">
                                    Conteúdo Completo <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" 
                                          id="content" 
                                          name="content"
                                          placeholder="Digite o conteúdo completo da aula aqui..."
                                          required></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-gear"></i> 
                                Configurações
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="duration" class="form-label">
                                    <i class="bi bi-clock"></i> 
                                    Duração (minutos)
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="duration" 
                                       name="duration" 
                                       min="1"
                                       placeholder="Ex: 45">
                                <small class="text-muted">
                                    Tempo estimado para completar a aula
                                </small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="video_url" class="form-label">
                                    <i class="bi bi-play-circle"></i> 
                                    URL do Vídeo
                                </label>
                                <input type="url" 
                                       class="form-control" 
                                       id="video_url" 
                                       name="video_url" 
                                       placeholder="https://youtube.com/watch?v=...">
                                <small class="text-muted">
                                    YouTube, Vimeo ou outro serviço
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-check-circle"></i> 
                                    Criar Aula
                                </button>
                                <button type="reset" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> 
                                    Limpar Formulário
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <h6 class="alert-heading">
                            <i class="bi bi-info-circle"></i> Dicas
                        </h6>
                        <small>
                            <ul class="mb-0 ps-3">
                                <li>Use títulos descritivos e claros</li>
                                <li>O conteúdo aceita formatação HTML</li>
                                <li>Você pode editar a aula depois</li>
                            </ul>
                        </small>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/tinymce/tinymce.min.js"></script>
    <script>
        // Inicializar TinyMCE
        tinymce.init({
            selector: '#content',
            license_key: 'gpl',
            height: 500,
            language: 'pt_BR',
            placeholder: 'Digite o conteúdo da aula aqui...',
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
                { text: 'Python', value: 'python' },
                { text: 'GDScript', value: 'gdscript' },
                { text: 'Lua', value: 'lua' }
            ],
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save();
                });
            }
        });

        // Sincronizar TinyMCE antes do envio
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Sincronizar TinyMCE
            if (tinymce.get('content')) {
                tinymce.get('content').save();
            }
            
            // Validar campos obrigatórios
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();
            
            if (!title) {
                alert('Por favor, insira o título da aula');
                document.getElementById('title').focus();
                return false;
            }
            
            if (!content || content === '<p></p>') {
                alert('Por favor, insira o conteúdo da aula');
                return false;
            }
            
            // Enviar formulário
            this.submit();
        });
        
        // Preview do título em tempo real
        document.getElementById('title').addEventListener('input', function() {
            const value = this.value || 'Nova Aula';
            document.title = value + ' - GameDev Academy';
        });
    </script>
</body>
</html>
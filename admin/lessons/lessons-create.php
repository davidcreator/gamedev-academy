<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit();
}

// Buscar módulos para o select
$modules = $conn->query("SELECT * FROM modules ORDER BY order_index");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Lição - GameDev Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    
    <!-- CSS do Editor.js -->
    <style>
        .codex-editor__redactor {
            padding-bottom: 100px !important;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            min-height: 400px;
        }
        .ce-block__content, .ce-toolbar__content {
            max-width: 100%;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/sidebar.php'; ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Nova Lição</h1>
                    <a href="lessons.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>

                <form id="lessonForm" method="POST" action="lessons-process.php?action=create">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="title" class="form-label">Título da Lição *</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Descrição Curta *</label>
                                <textarea class="form-control" id="description" name="description" rows="2" required></textarea>
                                <small class="text-muted">Aparecerá na listagem de lições</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Conteúdo da Lição *</label>
                                <div id="editorjs" class="border rounded"></div>
                                <!-- Input hidden que vai receber o JSON -->
                                <input type="hidden" name="content" id="content_json">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="module_id" class="form-label">Módulo *</label>
                                <select class="form-select" id="module_id" name="module_id" required>
                                    <option value="">Selecione...</option>
                                    <?php while($module = $modules->fetch_assoc()): ?>
                                        <option value="<?= $module['id'] ?>">
                                            <?= htmlspecialchars($module['title']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="order_index" class="form-label">Ordem *</label>
                                <input type="number" class="form-control" id="order_index" name="order_index" min="1" value="1" required>
                                <small class="text-muted">Ordem de exibição no módulo</small>
                            </div>

                            <div class="mb-3">
                                <label for="video_url" class="form-label">URL do Vídeo</label>
                                <input type="url" class="form-control" id="video_url" name="video_url" placeholder="https://youtube.com/...">
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Criar Lição
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <!-- Scripts do Editor.js -->
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/header@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/list@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/code@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/embed@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/image@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/quote@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/checklist@latest"></script>

    <script>
        // Inicializa o Editor.js
        const editor = new EditorJS({
            holder: 'editorjs',
            placeholder: 'Comece a escrever o conteúdo da lição...',
            tools: {
                header: {
                    class: Header,
                    inlineToolbar: true,
                    config: {
                        levels: [2, 3, 4],
                        defaultLevel: 2
                    }
                },
                list: {
                    class: List,
                    inlineToolbar: true
                },
                code: {
                    class: CodeTool,
                    config: {
                        placeholder: 'Cole seu código aqui (GDScript, C#, etc.)'
                    }
                },
                embed: {
                    class: Embed,
                    config: {
                        services: {
                            youtube: true,
                            coub: true,
                            codepen: true
                        }
                    }
                },
                image: {
                    class: ImageTool,
                    config: {
                        endpoints: {
                            byFile: 'upload-image.php', // Você vai precisar criar esse arquivo
                        }
                    }
                },
                quote: Quote,
                checklist: Checklist
            },
            onReady: () => {
                console.log('Editor.js está pronto!');
            }
        });

        // Intercepta o submit do formulário
        document.getElementById('lessonForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            try {
                // Salva os dados do editor
                const outputData = await editor.save();
                
                // Verifica se tem conteúdo
                if (!outputData.blocks || outputData.blocks.length === 0) {
                    alert('Por favor, adicione conteúdo à lição!');
                    return;
                }
                
                // Coloca o JSON no input hidden
                document.getElementById('content_json').value = JSON.stringify(outputData);
                
                // Envia o formulário
                this.submit();
                
            } catch (error) {
                console.error('Erro ao salvar:', error);
                alert('Erro ao processar o conteúdo. Verifique o console.');
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
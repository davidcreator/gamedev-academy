<?php
session_start();
require_once '../../config/database.php';

// Buscar todas as aulas
try {
    $stmt = $pdo->query("SELECT * FROM lessons ORDER BY id DESC");
    $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total = count($lessons);
} catch(PDOException $e) {
    die("Erro ao buscar aulas: " . $e->getMessage());
}

// Mensagens de sessão
$success = $_SESSION['success_message'] ?? null;
$error = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Aulas - GameDev Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>
                        <i class="bi bi-mortarboard"></i> 
                        Aulas <span class="badge bg-secondary"><?php echo $total; ?></span>
                    </h1>
                    <div>
                        <a href="create.php" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Nova Aula
                        </a>
                        <a href="../lesson-edit.php" class="btn btn-outline-secondary">
                            <i class="bi bi-gear"></i> Editor Principal
                        </a>
                    </div>
                </div>
                
                <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Lista de Aulas</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($lessons)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 3rem; color: #dee2e6;"></i>
                                <p class="mt-3 text-muted">Nenhuma aula cadastrada ainda.</p>
                                <a href="create.php" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Criar Primeira Aula
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="60">#ID</th>
                                            <th>Título</th>
                                            <th width="300">Descrição</th>
                                            <th width="100">Duração</th>
                                            <th width="130">Criado em</th>
                                            <th width="140" class="text-center">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($lessons as $lesson): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary">#<?php echo $lesson['id']; ?></span>
                                            </td>
                                            <td>
                                                <strong>
                                                    <?php 
                                                    $title = $lesson['title'] ?? 'Sem título';
                                                    echo htmlspecialchars($title); 
                                                    ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php 
                                                    $desc = $lesson['description'] ?? 'Sem descrição';
                                                    echo htmlspecialchars(substr($desc, 0, 100));
                                                    if (strlen($desc) > 100) echo '...';
                                                    ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if (!empty($lesson['duration'])): ?>
                                                    <span class="badge bg-info">
                                                        <i class="bi bi-clock"></i> 
                                                        <?php echo $lesson['duration']; ?> min
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                if (!empty($lesson['created_at'])) {
                                                    echo date('d/m/Y', strtotime($lesson['created_at']));
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="view.php?id=<?php echo $lesson['id']; ?>" 
                                                       class="btn btn-outline-info" 
                                                       title="Visualizar">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="edit.php?id=<?php echo $lesson['id']; ?>" 
                                                       class="btn btn-outline-primary" 
                                                       title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="delete.php?id=<?php echo $lesson['id']; ?>" 
                                                       class="btn btn-outline-danger" 
                                                       title="Excluir"
                                                       onclick="return confirm('Tem certeza que deseja excluir esta aula?')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($lessons)): ?>
                    <div class="card-footer text-muted">
                        Total de <?php echo $total; ?> aula(s) cadastrada(s)
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Links Rápidos -->
                <div class="mt-4">
                    <div class="btn-group">
                        <a href="../" class="btn btn-outline-secondary">
                            <i class="bi bi-house"></i> Painel Admin
                        </a>
                        <a href="create.php" class="btn btn-outline-success">
                            <i class="bi bi-plus"></i> Nova Aula
                        </a>
                        <a href="../lesson-edit.php" class="btn btn-outline-primary">
                            <i class="bi bi-gear"></i> Editor Principal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
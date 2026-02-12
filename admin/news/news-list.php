<?php
session_start();
require_once '../config/database.php';

// Buscar todas as notícias
try {
    $stmt = $pdo->query("SELECT * FROM news ORDER BY created_at DESC");
    $newsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total = count($newsList);
} catch(PDOException $e) {
    die("Erro ao buscar notícias: " . $e->getMessage());
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
    <title>Gerenciar Notícias - GameDev Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include '../includes/admin-header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/admin-sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-newspaper"></i> 
                        Notícias <span class="badge bg-secondary"><?php echo $total; ?></span>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="news-create.php" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Nova Notícia
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
                    <div class="card-body">
                        <?php if (empty($newsList)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 3rem; color: #dee2e6;"></i>
                                <p class="mt-3 text-muted">Nenhuma notícia cadastrada ainda.</p>
                                <a href="news-create.php" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Criar Primeira Notícia
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th width="60">#</th>
                                            <th>Título</th>
                                            <th width="120">Categoria</th>
                                            <th width="100">Status</th>
                                            <th width="130">Data</th>
                                            <th width="150" class="text-center">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($newsList as $item): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary">#<?php echo $item['id']; ?></span>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($item['title'] ?? 'Sem título'); ?></strong>
                                                <?php if (!empty($item['excerpt'])): ?>
                                                <br><small class="text-muted">
                                                    <?php 
                                                    $excerpt = $item['excerpt'];
                                                    echo htmlspecialchars(substr($excerpt, 0, 80));
                                                    if (strlen($excerpt) > 80) echo '...';
                                                    ?>
                                                </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($item['category'])): ?>
                                                <span class="badge bg-info">
                                                    <?php echo htmlspecialchars($item['category']); ?>
                                                </span>
                                                <?php else: ?>
                                                <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (($item['status'] ?? 'draft') == 'published'): ?>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> Publicado
                                                </span>
                                                <?php else: ?>
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-clock"></i> Rascunho
                                                </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                if (!empty($item['created_at'])) {
                                                    echo date('d/m/Y', strtotime($item['created_at']));
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="news.php?id=<?php echo $item['id']; ?>" 
                                                       class="btn btn-outline-info" title="Visualizar">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="news-edit.php?id=<?php echo $item['id']; ?>" 
                                                       class="btn btn-outline-primary" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="news-delete.php?id=<?php echo $item['id']; ?>" 
                                                       class="btn btn-outline-danger" title="Excluir"
                                                       onclick="return confirm('Tem certeza que deseja excluir esta notícia?')">
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
                    <?php if (!empty($newsList)): ?>
                    <div class="card-footer text-muted">
                        Total de <?php echo $total; ?> notícia(s) cadastrada(s)
                    </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
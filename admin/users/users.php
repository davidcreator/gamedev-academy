<?php
// admin/users.php - Gerenciar Usuários

$pageTitle = 'Gerenciar Usuários';
include '../includes/header.php';

$userModel = new User();
$db = Database::getInstance();

// Buscar usuários
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

if ($search) {
    $users = $db->fetchAll(
        "SELECT * FROM users 
         WHERE username LIKE ? OR email LIKE ? OR full_name LIKE ?
         ORDER BY created_at DESC 
         LIMIT ? OFFSET ?",
        ["%$search%", "%$search%", "%$search%", $perPage, $offset]
    );
    $total = $db->fetch(
        "SELECT COUNT(*) as total FROM users 
         WHERE username LIKE ? OR email LIKE ? OR full_name LIKE ?",
        ["%$search%", "%$search%", "%$search%"]
    )['total'];
} else {
    $users = $userModel->getAll($perPage, $offset);
    $total = $userModel->count();
}

$totalPages = ceil($total / $perPage);

// Ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = intval($_POST['user_id'] ?? 0);
    
    if ($action === 'toggle_status' && $userId) {
        $user = $userModel->find($userId);
        if ($user) {
            $userModel->update($userId, ['is_active' => !$user['is_active']]);
            flash('success', 'Status do usuário atualizado!');
            redirect(url('admin/users.php'));
        }
    }
    
    if ($action === 'change_role' && $userId) {
        $newRole = $_POST['role'] ?? 'student';
        $userModel->update($userId, ['role' => $newRole]);
        flash('success', 'Cargo do usuário atualizado!');
        redirect(url('admin/users.php'));
    }

    if ($action === 'delete_user' && $userId) {
        // Impedir deletar o próprio usuário logado
        if ($userId == $currentUser['id']) {
            flash('error', 'Você não pode deletar sua própria conta!');
        } else {
            $userModel->delete($userId);
            flash('success', 'Usuário removido com sucesso!');
        }
        redirect(url('admin/users.php'));
    }
}
?>

<div class="d-flex justify-between align-center mb-4">
    <div>
        <p class="text-muted">Total de <?= $total ?> usuários</p>
    </div>
    
    <!-- Busca -->
    <form method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control input-search-width" 
               placeholder="Buscar usuários..." 
               value="<?= escape($search) ?>">
        <button type="submit" class="btn btn-primary">Buscar</button>
        <?php if ($search): ?>
            <a href="<?= url('admin/users/users.php') ?>" class="btn btn-secondary">Limpar</a>
        <?php endif; ?>
    </form>
</div>

<?= showFlashMessages() ?>

<div class="admin-table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuário</th>
                <th>E-mail</th>
                <th>Cargo</th>
                <th>Nível</th>
                <th>XP</th>
                <th>Status</th>
                <th>Cadastro</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td>#<?= $user['id'] ?></td>
                <td>
                    <div class="d-flex align-center gap-2">
                        <img src="<?= getAvatar($user['avatar']) ?>" alt="" class="avatar">
                        <div>
                            <div><?= escape($user['username']) ?></div>
                            <div style="font-size: 0.8rem; color: var(--gray-500);">
                                <?= escape($user['full_name']) ?>
                            </div>
                        </div>
                    </div>
                </td>
                <td><?= escape($user['email']) ?></td>
                <td>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="action" value="change_role">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <select name="role" onchange="this.form.submit()" class="form-control form-select-sm">
                            <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Estudante</option>
                            <option value="instructor" <?= $user['role'] === 'instructor' ? 'selected' : '' ?>>Instrutor</option>
                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </form>
                </td>
                <td>
                    <span class="badge badge-primary">Nível <?= $user['level'] ?></span>
                </td>
                <td><?= number_format($user['xp_total']) ?></td>
                <td>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="action" value="toggle_status">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <?php if ($user['is_active']): ?>
                            <button type="submit" class="badge badge-success btn-unstyled">
                                Ativo
                            </button>
                        <?php else: ?>
                            <button type="submit" class="badge badge-danger btn-unstyled">
                                Inativo
                            </button>
                        <?php endif; ?>
                    </form>
                </td>
                <td><?= formatDate($user['created_at']) ?></td>
                <td>
                    <div class="admin-actions">
                        <a href="<?= url('admin/users/user-edit.php?id=' . $user['id']) ?>" 
                           class="btn-action edit" title="Editar">✏️</a>
                        
                        <form method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.');">
                            <input type="hidden" name="action" value="delete_user">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <button type="submit" class="btn-action delete" title="Deletar">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Paginação -->
<?php if ($totalPages > 1): ?>
<div class="d-flex justify-center gap-1 mt-4">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($i == $page): ?>
            <span class="btn btn-primary btn-sm"><?= $i ?></span>
        <?php else: ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" 
               class="btn btn-secondary btn-sm"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
<?php
$pageTitle = 'Níveis';
include '../includes/header.php';

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $data = [
            'level_number' => intval($_POST['level_number'] ?? 1),
            'title' => trim($_POST['title'] ?? ''),
            'xp_required' => intval($_POST['xp_required'] ?? 0),
            'badge_icon' => trim($_POST['badge_icon'] ?? ''),
            'color' => trim($_POST['color'] ?? '#6366f1'),
        ];
        if ($data['title']) {
            $db->insert('levels', $data);
            flash('success', 'Nível criado!');
        } else {
            flash('error', 'Informe o título do nível.');
        }
        redirect(url('admin/levels/levels.php'));
    }

    if ($action === 'update') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $data = [
                'level_number' => intval($_POST['level_number'] ?? 1),
                'title' => trim($_POST['title'] ?? ''),
                'xp_required' => intval($_POST['xp_required'] ?? 0),
                'badge_icon' => trim($_POST['badge_icon'] ?? ''),
                'color' => trim($_POST['color'] ?? '#6366f1'),
            ];
            $db->update('levels', $data, 'id = :id', ['id' => $id]);
            flash('success', 'Nível atualizado!');
        }
        redirect(url('admin/levels/levels.php'));
    }

    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $db->delete('levels', 'id = :id', ['id' => $id]);
            flash('success', 'Nível removido!');
        }
        redirect(url('admin/levels/levels.php'));
    }
}

$levels = $db->fetchAll("
    SELECT l.*, (SELECT COUNT(*) FROM users WHERE level = l.level_number) as user_count 
    FROM levels l 
    ORDER BY l.level_number ASC
");
?>

<?= showFlashMessages() ?>

<div class="d-flex justify-between align-center mb-4">
    <p class="text-muted">Total de <?= count($levels) ?> níveis</p>
    <button class="btn btn-success" onclick="document.getElementById('create-level').style.display='block'">Novo Nível</button>
</div>

<!-- Criar Nível -->
<div id="create-level" class="card mb-4" hidden>
    <div class="card-body">
        <h3 class="card-title">Criar Nível</h3>
        <form method="POST" class="grid-cols-2 gap-2">
            <input type="hidden" name="action" value="create">
            <label>Número
                <input type="number" name="level_number" class="form-control" value="<?= count($levels) + 1 ?>">
            </label>
            <label>Título
                <input type="text" name="title" class="form-control" required>
            </label>
            <label>XP Requerido
                <input type="number" name="xp_required" class="form-control" value="0">
            </label>
            <label>Ícone
                <input type="text" name="badge_icon" class="form-control" placeholder="Ex.: 🌱">
            </label>
            <label>Cor
                <input type="text" name="color" class="form-control" value="#6366f1">
            </label>
            <div class="mt-2">
                <button class="btn btn-success" type="submit">Salvar</button>
                <button class="btn btn-secondary" type="button" onclick="this.closest('#create-level').style.display='none'">Cancelar</button>
            </div>
        </form>
    </div>
    </div>

<!-- Lista de Níveis -->
<div class="admin-table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nível</th>
                <th>Título</th>
                <th>Usuários</th>
                <th>XP</th>
                <th>Ícone</th>
                <th>Cor</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($levels as $level): ?>
            <tr>
                <td>#<?= $level['id'] ?></td>
                <td><span class="badge badge-primary"><?= $level['level_number'] ?></span></td>
                <td><?= escape($level['title']) ?></td>
                <td><?= number_format($level['user_count']) ?></td>
                <td><?= number_format($level['xp_required']) ?></td>
                <td><?= escape($level['badge_icon']) ?></td>
                <td><span class="badge badge-secondary"><?= escape($level['color']) ?></span></td>
                <td>
                    <div class="admin-actions">
                        <button class="btn-action edit" title="Editar" onclick="toggleEdit(<?= $level['id'] ?>)">✏️</button>
                        <form method="POST" onsubmit="return confirm('Remover este nível?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $level['id'] ?>">
                            <button class="btn-action delete" title="Deletar">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
            <tr id="edit-<?= $level['id'] ?>" hidden>
                <td colspan="7">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Editar Nível #<?= $level['id'] ?></h4>
                            <form method="POST" class="grid-cols-2 gap-2">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?= $level['id'] ?>">
                                <label>Número
                                    <input type="number" name="level_number" class="form-control" value="<?= $level['level_number'] ?>">
                                </label>
                                <label>Título
                                    <input type="text" name="title" class="form-control" value="<?= escape($level['title']) ?>" required>
                                </label>
                                <label>XP Requerido
                                    <input type="number" name="xp_required" class="form-control" value="<?= $level['xp_required'] ?>">
                                </label>
                                <label>Ícone
                                    <input type="text" name="badge_icon" class="form-control" value="<?= escape($level['badge_icon']) ?>">
                                </label>
                                <label>Cor
                                    <input type="text" name="color" class="form-control" value="<?= escape($level['color']) ?>">
                                </label>
                                <div class="mt-2">
                                    <button class="btn btn-success" type="submit">Salvar</button>
                                    <button class="btn btn-secondary" type="button" onclick="toggleEdit(<?= $level['id'] ?>)">Cancelar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function toggleEdit(id) {
    const tr = document.getElementById('edit-' + id);
    if (tr.hasAttribute('hidden')) {
        tr.removeAttribute('hidden');
    } else {
        tr.setAttribute('hidden', '');
    }
}
</script>

<?php include '../includes/footer.php'; ?>

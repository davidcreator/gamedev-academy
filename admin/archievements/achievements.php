<?php
$pageTitle = 'Conquistas';
include 'includes/header.php';

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'icon' => trim($_POST['icon'] ?? ''),
            'xp_reward' => intval($_POST['xp_reward'] ?? 0),
            'coin_reward' => intval($_POST['coin_reward'] ?? 0),
            'requirement_type' => $_POST['requirement_type'] ?? 'lessons_completed',
            'requirement_value' => intval($_POST['requirement_value'] ?? 1),
            'is_secret' => isset($_POST['is_secret']) ? 1 : 0,
        ];
        if ($data['name']) {
            $db->insert('achievements', $data);
            flash('success', 'Conquista criada com sucesso!');
        } else {
            flash('error', 'Informe ao menos o nome da conquista.');
        }
        redirect(url('admin/achievements.php'));
    }

    if ($action === 'update') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'icon' => trim($_POST['icon'] ?? ''),
                'xp_reward' => intval($_POST['xp_reward'] ?? 0),
                'coin_reward' => intval($_POST['coin_reward'] ?? 0),
                'requirement_type' => $_POST['requirement_type'] ?? 'lessons_completed',
                'requirement_value' => intval($_POST['requirement_value'] ?? 1),
                'is_secret' => isset($_POST['is_secret']) ? 1 : 0,
            ];
            $db->update('achievements', $data, 'id = :id', ['id' => $id]);
            flash('success', 'Conquista atualizada!');
        }
        redirect(url('admin/achievements.php'));
    }

    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $db->delete('achievements', 'id = :id', ['id' => $id]);
            flash('success', 'Conquista removida!');
        }
        redirect(url('admin/achievements.php'));
    }

    if ($action === 'unlock_for_user') {
        $userId = intval($_POST['user_id'] ?? 0);
        $achievementId = intval($_POST['achievement_id'] ?? 0);
        if ($userId > 0 && $achievementId > 0) {
            $exists = $db->fetch("SELECT id FROM user_achievements WHERE user_id = ? AND achievement_id = ?", [$userId, $achievementId]);
            if (!$exists) {
                $db->insert('user_achievements', [
                    'user_id' => $userId,
                    'achievement_id' => $achievementId
                ]);
                flash('success', 'Conquista atribuída ao usuário!');
            } else {
                flash('info', 'Usuário já possui esta conquista.');
            }
        } else {
            flash('error', 'Selecione usuário e conquista.');
        }
        redirect(url('admin/achievements.php'));
    }
}

$search = trim($_GET['search'] ?? '');
$filterType = $_GET['type'] ?? '';
$perPage = 20;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];
if ($search) {
    $where[] = "(name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($filterType) {
    $where[] = "requirement_type = ?";
    $params[] = $filterType;
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$total = $db->fetch("SELECT COUNT(*) AS total FROM achievements {$whereSql}", $params)['total'] ?? 0;
$achievements = $db->fetchAll("SELECT * FROM achievements {$whereSql} ORDER BY id DESC LIMIT ? OFFSET ?", array_merge($params, [$perPage, $offset]));
$totalPages = max(1, ceil($total / $perPage));

$users = $db->fetchAll("SELECT id, username, full_name FROM users WHERE is_active = 1 ORDER BY created_at DESC LIMIT 50");
?>

<?= showFlashMessages() ?>

<div class="d-flex justify-between align-center mb-4">
    <form method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control" placeholder="Buscar conquistas..." value="<?= escape($search) ?>">
        <select name="type" class="form-control">
            <option value="">Tipo de requisito</option>
            <option value="lessons_completed" <?= $filterType === 'lessons_completed' ? 'selected' : '' ?>>Lições completadas</option>
            <option value="courses_completed" <?= $filterType === 'courses_completed' ? 'selected' : '' ?>>Cursos completados</option>
            <option value="streak" <?= $filterType === 'streak' ? 'selected' : '' ?>>Streak</option>
            <option value="xp_earned" <?= $filterType === 'xp_earned' ? 'selected' : '' ?>>XP acumulado</option>
            <option value="special" <?= $filterType === 'special' ? 'selected' : '' ?>>Especial</option>
        </select>
        <button class="btn btn-primary" type="submit">Filtrar</button>
        <?php if ($search || $filterType): ?>
            <a href="<?= url('admin/achievements.php') ?>" class="btn btn-secondary">Limpar</a>
        <?php endif; ?>
    </form>
    <button class="btn btn-success" onclick="toggleCreate()">Nova Conquista</button>
</div>

<!-- Criar Conquista -->
<div id="create-achievement" class="card mb-4" hidden>
    <div class="card-body">
        <h3 class="card-title">Criar Conquista</h3>
        <form method="POST" class="grid-cols-2 gap-2">
            <input type="hidden" name="action" value="create">
            <label>Nome
                <input type="text" name="name" class="form-control" required>
            </label>
            <label>Ícone
                <input type="text" name="icon" class="form-control" placeholder="Ex.: 🎯">
            </label>
            <label>Descrição
                <textarea name="description" class="form-control" rows="2"></textarea>
            </label>
            <div class="d-flex gap-2">
                <label>XP
                    <input type="number" name="xp_reward" class="form-control" value="0">
                </label>
                <label>Moedas
                    <input type="number" name="coin_reward" class="form-control" value="0">
                </label>
            </div>
            <div class="d-flex gap-2">
                <label>Tipo de requisito
                    <select name="requirement_type" class="form-control">
                        <option value="lessons_completed">Lições completadas</option>
                        <option value="courses_completed">Cursos completados</option>
                        <option value="streak">Streak</option>
                        <option value="xp_earned">XP acumulado</option>
                        <option value="special">Especial</option>
                    </select>
                </label>
                <label>Valor requerido
                    <input type="number" name="requirement_value" class="form-control" value="1">
                </label>
            </div>
            <label class="d-flex align-center gap-1">
                <input type="checkbox" name="is_secret"> Secreta
            </label>
            <div class="mt-2">
                <button class="btn btn-success" type="submit">Salvar</button>
                <button class="btn btn-secondary" type="button" onclick="toggleCreate()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Conquistas -->
<div class="admin-table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Ícone</th>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Requisito</th>
                <th>Recompensas</th>
                <th>Secreta</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($achievements as $a): ?>
            <tr>
                <td>#<?= $a['id'] ?></td>
                <td><?= escape($a['icon']) ?></td>
                <td><?= escape($a['name']) ?></td>
                <td><?= escape($a['description']) ?></td>
                <td>
                    <span class="badge badge-primary">
                        <?= escape($a['requirement_type']) ?>: <?= intval($a['requirement_value']) ?>
                    </span>
                </td>
                <td>
                    <?php if (($a['xp_reward'] ?? 0) > 0): ?>
                        <span class="badge badge-warning">⚡ <?= intval($a['xp_reward']) ?> XP</span>
                    <?php endif; ?>
                    <?php if (($a['coin_reward'] ?? 0) > 0): ?>
                        <span class="badge badge-success">🪙 <?= intval($a['coin_reward']) ?></span>
                    <?php endif; ?>
                </td>
                <td><?= !empty($a['is_secret']) ? 'Sim' : 'Não' ?></td>
                <td>
                    <div class="admin-actions">
                        <button class="btn-action edit" title="Editar" onclick="toggleEdit(<?= $a['id'] ?>)">✏️</button>
                        <form method="POST" onsubmit="return confirm('Remover esta conquista?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <button class="btn-action delete" title="Deletar">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
            <tr id="edit-<?= $a['id'] ?>" hidden>
                <td colspan="8">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Editar Conquista #<?= $a['id'] ?></h4>
                            <form method="POST" class="grid-cols-2 gap-2">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                <label>Nome
                                    <input type="text" name="name" class="form-control" value="<?= escape($a['name']) ?>" required>
                                </label>
                                <label>Ícone
                                    <input type="text" name="icon" class="form-control" value="<?= escape($a['icon']) ?>">
                                </label>
                                <label>Descrição
                                    <textarea name="description" class="form-control" rows="2"><?= escape($a['description']) ?></textarea>
                                </label>
                                <div class="d-flex gap-2">
                                    <label>XP
                                        <input type="number" name="xp_reward" class="form-control" value="<?= intval($a['xp_reward'] ?? 0) ?>">
                                    </label>
                                    <label>Moedas
                                        <input type="number" name="coin_reward" class="form-control" value="<?= intval($a['coin_reward'] ?? 0) ?>">
                                    </label>
                                </div>
                                <div class="d-flex gap-2">
                                    <label>Tipo de requisito
                                        <select name="requirement_type" class="form-control">
                                            <?php
                                            $types = ['lessons_completed','courses_completed','streak','xp_earned','special'];
                                            foreach ($types as $t):
                                            ?>
                                                <option value="<?= $t ?>" <?= $a['requirement_type'] === $t ? 'selected' : '' ?>><?= $t ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </label>
                                    <label>Valor requerido
                                        <input type="number" name="requirement_value" class="form-control" value="<?= intval($a['requirement_value'] ?? 1) ?>">
                                    </label>
                                </div>
                                <label class="d-flex align-center gap-1">
                                    <input type="checkbox" name="is_secret" <?= !empty($a['is_secret']) ? 'checked' : '' ?>> Secreta
                                </label>
                                <div class="mt-2">
                                    <button class="btn btn-success" type="submit">Salvar</button>
                                    <button class="btn btn-secondary" type="button" onclick="toggleEdit(<?= $a['id'] ?>)">Cancelar</button>
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

<!-- Atribuir Conquista para Usuário -->
<div class="card mt-4">
    <div class="card-body">
        <h3 class="card-title">Atribuir Conquista para Usuário</h3>
        <form method="POST" class="d-flex gap-2 align-center">
            <input type="hidden" name="action" value="unlock_for_user">
            <select name="user_id" class="form-control">
                <option value="">Selecione um usuário...</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>">#<?= $u['id'] ?> - <?= escape($u['username']) ?> (<?= escape($u['full_name']) ?>)</option>
                <?php endforeach; ?>
            </select>
            <select name="achievement_id" class="form-control">
                <option value="">Selecione uma conquista...</option>
                <?php foreach ($achievements as $a): ?>
                    <option value="<?= $a['id'] ?>">#<?= $a['id'] ?> - <?= escape($a['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary" type="submit">Atribuir</button>
        </form>
    </div>
</div>

<!-- Paginação -->
<?php if ($totalPages > 1): ?>
<div class="d-flex justify-center gap-1 mt-4">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($i == $page): ?>
            <span class="btn btn-primary btn-sm"><?= $i ?></span>
        <?php else: ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($filterType) ?>" class="btn btn-secondary btn-sm"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
</div>
<?php endif; ?>

<script>
function toggleCreate() {
    const el = document.getElementById('create-achievement');
    if (el.hasAttribute('hidden')) {
        el.removeAttribute('hidden');
    } else {
        el.setAttribute('hidden', '');
    }
}

function toggleEdit(id) {
    const tr = document.getElementById('edit-' + id);
    if (tr.hasAttribute('hidden')) {
        tr.removeAttribute('hidden');
    } else {
        tr.setAttribute('hidden', '');
    }
}
</script>

<?php include 'includes/footer.php'; ?>


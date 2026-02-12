<?php
// admin/user-edit.php - Editar Usuário

$pageTitle = 'Editar Usuário';
include '../includes/header.php';

$id = intval($_GET['id'] ?? 0);
$userModel = new User();
$user = $userModel->find($id);

if (!$user) {
    flash('error', 'Usuário não encontrado!');
    redirect(url('admin/users.php'));
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'username' => trim($_POST['username'] ?? ''),
        'role' => $_POST['role'] ?? 'student',
        'bio' => trim($_POST['bio'] ?? ''),
        'xp_total' => intval($_POST['xp_total'] ?? 0),
        'level' => intval($_POST['level'] ?? 1),
        'coins' => intval($_POST['coins'] ?? 0),
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];

    // Alterar senha se fornecida
    if (!empty($_POST['password'])) {
        $data['password'] = $_POST['password'];
    }

    // Validação básica
    if (empty($data['full_name']) || empty($data['email']) || empty($data['username'])) {
        flash('error', 'Preencha os campos obrigatórios!');
    } else {
        // Verificar duplicidade de email/username (exceto o próprio usuário)
        $existingEmail = $userModel->findByEmail($data['email']);
        $existingUsername = $userModel->findByUsername($data['username']);

        if (($existingEmail && $existingEmail['id'] != $id) || ($existingUsername && $existingUsername['id'] != $id)) {
            flash('error', 'Email ou Username já está em uso!');
        } else {
            if ($userModel->update($id, $data)) {
                flash('success', 'Usuário atualizado com sucesso!');
                // Recarregar dados
                $user = $userModel->find($id);
            } else {
                flash('error', 'Erro ao atualizar usuário.');
            }
        }
    }
}
?>

<div class="mb-4">
    <a href="<?= url('admin/users.php') ?>" class="btn btn-secondary">← Voltar para Lista</a>
</div>

<?= showFlashMessages() ?>

<div class="card p-4" style="max-width: 800px; margin: 0 auto;">
    <h2 class="mb-4">Editar Usuário: <?= escape($user['username']) ?></h2>
    
    <form method="POST">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Nome Completo <span class="text-danger">*</span></label>
                <input type="text" name="full_name" class="form-control" 
                       value="<?= escape($user['full_name']) ?>" required>
            </div>
            
            <div class="col-md-6 mb-3">
                <label class="form-label">Username <span class="text-danger">*</span></label>
                <input type="text" name="username" class="form-control" 
                       value="<?= escape($user['username']) ?>" required>
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" 
                   value="<?= escape($user['email']) ?>" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Senha (Deixe em branco para manter a atual)</label>
            <input type="password" name="password" class="form-control" 
                   placeholder="Nova senha...">
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Cargo</label>
                <select name="role" class="form-control">
                    <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Estudante</option>
                    <option value="instructor" <?= $user['role'] === 'instructor' ? 'selected' : '' ?>>Instrutor</option>
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                </select>
            </div>
            
            <div class="col-md-6 mb-3">
                <label class="form-label">Status</label>
                <div class="form-check mt-2">
                    <input type="checkbox" name="is_active" id="is_active" class="form-check-input" 
                           <?= $user['is_active'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_active">Conta Ativa</label>
                </div>
            </div>
        </div>
        
        <hr class="my-4">
        <h3 class="mb-3">Gamificação</h3>
        
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Nível</label>
                <input type="number" name="level" class="form-control" min="1" 
                       value="<?= $user['level'] ?>">
            </div>
            
            <div class="col-md-4 mb-3">
                <label class="form-label">XP Total</label>
                <input type="number" name="xp_total" class="form-control" min="0" 
                       value="<?= $user['xp_total'] ?>">
            </div>
            
            <div class="col-md-4 mb-3">
                <label class="form-label">Moedas</label>
                <input type="number" name="coins" class="form-control" min="0" 
                       value="<?= $user['coins'] ?>">
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Biografia</label>
            <textarea name="bio" class="form-control" rows="4"><?= escape($user['bio'] ?? '') ?></textarea>
        </div>
        
        <div class="d-flex justify-end gap-2 mt-4">
            <a href="<?= url('admin/users.php') ?>" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
<?php
// user/profile.php - Perfil do Usuário

$pageTitle = 'Meu Perfil';
include 'includes/header.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'bio' => trim($_POST['bio'] ?? ''),
        'github_url' => trim($_POST['github_url'] ?? ''),
        'linkedin_url' => trim($_POST['linkedin_url'] ?? '')
    ];
    
    // Upload de avatar
    if (!empty($_FILES['avatar']['name'])) {
        $uploadDir = __DIR__ . '/../../uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $filename = $currentUser['id'] . '_' . time() . '.' . $extension;
        
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $filename)) {
            $data['avatar'] = $filename;
        }
    }
    
    if ($userModel->update($currentUser['id'], $data)) {
        $message = 'Perfil atualizado com sucesso!';
        $currentUser = $auth->getCurrentUser(); // Recarregar dados
    } else {
        $error = 'Erro ao atualizar perfil.';
    }
}
?>

<h1>👤 Meu Perfil</h1>

<?php if ($message): ?>
    <div class="alert alert-success"><?= $message ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="widget">
    <div class="widget-header">
        <h3 class="widget-title">Informações do Perfil</h3>
    </div>
    <div class="widget-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label class="form-label">Avatar</label>
                <div class="d-flex align-center gap-3">
                    <img src="<?= getAvatar($currentUser['avatar']) ?>" alt="Avatar" class="avatar avatar-xl">
                    <div>
                        <input type="file" name="avatar" accept="image/*" class="form-control">
                        <small class="text-muted">JPG, PNG ou GIF. Máximo 2MB.</small>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Nome completo</label>
                <input type="text" name="full_name" class="form-control" 
                       value="<?= escape($currentUser['full_name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Nome de usuário</label>
                <input type="text" class="form-control" value="<?= escape($currentUser['username']) ?>" disabled>
                <small class="text-muted">O nome de usuário não pode ser alterado.</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">E-mail</label>
                <input type="email" class="form-control" value="<?= escape($currentUser['email']) ?>" disabled>
                <small class="text-muted">Para alterar o e-mail, entre em contato com o suporte.</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Bio</label>
                <textarea name="bio" class="form-control" rows="4" 
                          placeholder="Conte um pouco sobre você..."><?= escape($currentUser['bio'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">GitHub</label>
                <input type="url" name="github_url" class="form-control" 
                       placeholder="https://github.com/seuusuario"
                       value="<?= escape($currentUser['github_url'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">LinkedIn</label>
                <input type="url" name="linkedin_url" class="form-control" 
                       placeholder="https://linkedin.com/in/seuusuario"
                       value="<?= escape($currentUser['linkedin_url'] ?? '') ?>">
            </div>
            
            <button type="submit" class="btn btn-primary">
                Salvar Alterações
            </button>
        </form>
    </div>
</div>

<!-- Estatísticas do Perfil -->
<div class="widget mt-4">
    <div class="widget-header">
        <h3 class="widget-title">📊 Estatísticas</h3>
    </div>
    <div class="widget-body">
        <div class="stats-grid">
            <div>
                <div class="text-muted mb-1">Membro desde</div>
                <div class="font-weight-bold"><?= formatDate($currentUser['created_at'], 'd/m/Y') ?></div>
            </div>
            <div>
                <div class="text-muted mb-1">Última atividade</div>
                <div class="font-weight-bold"><?= timeAgo($currentUser['last_activity'] ?? $currentUser['created_at']) ?></div>
            </div>
            <div>
                <div class="text-muted mb-1">Total de XP</div>
                <div class="font-weight-bold"><?= number_format($currentUser['xp_total']) ?> XP</div>
            </div>
            <div>
                <div class="text-muted mb-1">Nível atual</div>
                <div class="font-weight-bold">Nível <?= $currentUser['level'] ?></div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<?php
// install/steps/step4.php
?>

<h2 class="step-title">👨‍💼 Conta do Administrador</h2>
<p class="step-description">
    Crie a conta de administrador principal que terá acesso total ao sistema.
</p>

<div class="form-row">
    <div class="form-group">
        <label class="form-label">👤 Nome Completo</label>
        <input type="text" name="admin_name" class="form-control" 
               value="<?= htmlspecialchars($config['admin_name'] ?? '') ?>" 
               placeholder="Seu Nome Completo" required>
    </div>
    
    <div class="form-group">
        <label class="form-label">🏷️ Nome de Usuário</label>
        <input type="text" name="admin_username" class="form-control" 
               value="<?= htmlspecialchars($config['admin_username'] ?? 'admin') ?>" 
               placeholder="admin" 
               pattern="[a-zA-Z0-9_]{3,20}" required>
        <div class="form-help">3-20 caracteres, apenas letras e números</div>
    </div>
</div>

<div class="form-group">
    <label class="form-label">📧 E-mail do Administrador</label>
    <input type="email" name="admin_email" class="form-control" 
           value="<?= htmlspecialchars($config['admin_email'] ?? '') ?>" 
           placeholder="admin@seusite.com" required>
    <div class="form-help">Use um e-mail válido para recuperação de senha</div>
</div>

<div class="form-row">
    <div class="form-group">
        <label class="form-label">🔐 Senha</label>
        <input type="password" name="admin_password" class="form-control" 
               placeholder="Mínimo 8 caracteres" 
               minlength="8" required>
    </div>
    
    <div class="form-group">
        <label class="form-label">🔐 Confirmar Senha</label>
        <input type="password" name="admin_password_confirm" class="form-control" 
               placeholder="Repita a senha" required>
    </div>
</div>

<div class="alert alert-warning" style="margin-top: 2rem;">
    <span class="alert-icon">⚠️</span>
    <div class="alert-content">
        <strong>Importante!</strong> Guarde estas credenciais em um local seguro. 
        Você precisará delas para acessar o painel administrativo.
    </div>
</div>

<div class="info-box">
    <h4><span>📌</span> Resumo da Instalação</h4>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Banco de Dados:</span>
            <span class="info-value"><?= htmlspecialchars($config['db_name'] ?? '') ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">URL do Site:</span>
            <span class="info-value"><?= htmlspecialchars($config['app_url'] ?? '') ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Ambiente:</span>
            <span class="info-value"><?= htmlspecialchars($config['app_env'] ?? 'local') ?></span>
        </div>
    </div>
</div>
<?php
// install/steps/step5.php
?>

<div class="success-animation">
    <div class="success-icon">🎉</div>
    <h2 class="success-title">Instalação Concluída!</h2>
    <p class="success-message">
        O GameDev Academy foi instalado com sucesso no seu servidor.
    </p>
</div>

<div class="info-box">
    <h4><span>📋</span> Resumo da Instalação</h4>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Site:</span>
            <span class="info-value"><?= htmlspecialchars($config['app_name'] ?? 'GameDev Academy') ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">URL:</span>
            <span class="info-value"><?= htmlspecialchars($config['app_url'] ?? '') ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Banco de Dados:</span>
            <span class="info-value"><?= htmlspecialchars($config['db_name'] ?? '') ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Admin:</span>
            <span class="info-value"><?= htmlspecialchars($config['admin_email'] ?? '') ?></span>
        </div>
    </div>
</div>

<div class="info-box" style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success);">
    <h4 style="color: var(--success);"><span>✅</span> Dados de Acesso</h4>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">URL do Site:</span>
            <span class="info-value"><?= htmlspecialchars($config['app_url'] ?? '') ?>/public/</span>
        </div>
        <div class="info-item">
            <span class="info-label">Painel Admin:</span>
            <span class="info-value"><?= htmlspecialchars($config['app_url'] ?? '') ?>/public/admin</span>
        </div>
        <div class="info-item">
            <span class="info-label">Usuário:</span>
            <span class="info-value"><?= htmlspecialchars($config['admin_username'] ?? 'admin') ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Senha:</span>
            <span class="info-value">A senha que você definiu</span>
        </div>
    </div>
</div>

<div class="alert alert-danger" style="margin-top: 2rem;">
    <span class="alert-icon">🔒</span>
    <div class="alert-content">
        <strong>Segurança!</strong> 
        Por favor, delete a pasta <code>/install</code> do servidor após concluir a instalação.
    </div>
</div>

<div style="margin-top: 2rem;">
    <h4 style="color: var(--white); margin-bottom: 1rem;">📚 Próximos Passos</h4>
    <ol style="color: var(--gray-400); line-height: 2;">
        <li>Delete a pasta <code>/install</code> por segurança</li>
        <li>Acesse o painel administrativo e configure o sistema</li>
        <li>Crie as categorias e cursos</li>
        <li>Configure as conquistas e sistema de gamificação</li>
        <li>Personalize o visual e conteúdo do site</li>
    </ol>
</div>
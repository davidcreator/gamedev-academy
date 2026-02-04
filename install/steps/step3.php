<?php
// install/steps/step3.php
?>

<h2 class="step-title">⚙️ Configuração do Site</h2>
<p class="step-description">
    Configure as informações básicas da sua plataforma de ensino.
</p>

<div class="form-group">
    <label class="form-label">🎨 Nome do Site</label>
    <input type="text" name="app_name" class="form-control" 
           value="<?= htmlspecialchars($config['app_name'] ?? 'GameDev Academy') ?>" 
           placeholder="GameDev Academy" required>
    <div class="form-help">Nome que aparecerá em todo o site</div>
</div>

<div class="form-group">
    <label class="form-label">🌐 URL do Site</label>
    <input type="url" name="app_url" class="form-control" 
           value="<?= htmlspecialchars($config['app_url'] ?? 'http://localhost/gamedev-academy') ?>" 
           placeholder="https://seu-site.com" required>
    <div class="form-help">URL completa sem barra no final</div>
</div>

<div class="form-row">
    <div class="form-group">
        <label class="form-label">🚀 Ambiente</label>
        <select name="app_env" class="form-control">
            <option value="local" <?= ($config['app_env'] ?? 'local') === 'local' ? 'selected' : '' ?>>
                Local (Desenvolvimento)
            </option>
            <option value="production" <?= ($config['app_env'] ?? '') === 'production' ? 'selected' : '' ?>>
                Produção
            </option>
        </select>
        <div class="form-help">Use "Local" para desenvolvimento</div>
    </div>
    
    <div class="form-group">
        <label class="form-label">🐛 Modo Debug</label>
        <select name="app_debug" class="form-control">
            <option value="true" <?= ($config['app_debug'] ?? 'true') === 'true' ? 'selected' : '' ?>>
                Ativado
            </option>
            <option value="false" <?= ($config['app_debug'] ?? '') === 'false' ? 'selected' : '' ?>>
                Desativado
            </option>
        </select>
        <div class="form-help">Desative em produção</div>
    </div>
</div>

<h4 style="margin: 2rem 0 1rem; color: var(--white);">📧 Configurações de E-mail</h4>

<div class="form-row">
    <div class="form-group">
        <label class="form-label">E-mail do Sistema</label>
        <input type="email" name="mail_from" class="form-control" 
               value="<?= htmlspecialchars($config['mail_from'] ?? 'noreply@gamedev.academy') ?>" 
               placeholder="noreply@seusite.com">
    </div>
    
    <div class="form-group">
        <label class="form-label">Nome do Remetente</label>
        <input type="text" name="mail_from_name" class="form-control" 
               value="<?= htmlspecialchars($config['mail_from_name'] ?? 'GameDev Academy') ?>" 
               placeholder="GameDev Academy">
    </div>
</div>

<h4 style="margin: 2rem 0 1rem; color: var(--white);">🎮 Configurações de Gamificação</h4>

<div class="form-row">
    <div class="form-group">
        <label class="form-label">XP por Lição Completa</label>
        <input type="number" name="xp_lesson" class="form-control" 
               value="<?= htmlspecialchars($config['xp_lesson'] ?? '10') ?>" 
               min="1" required>
    </div>
    
    <div class="form-group">
        <label class="form-label">XP por Curso Completo</label>
        <input type="number" name="xp_course" class="form-control" 
               value="<?= htmlspecialchars($config['xp_course'] ?? '100') ?>" 
               min="1" required>
    </div>
</div>

<div class="form-group">
    <label class="form-label">⏰ Timezone</label>
    <select name="timezone" class="form-control">
        <option value="America/Sao_Paulo" <?= ($config['timezone'] ?? 'America/Sao_Paulo') === 'America/Sao_Paulo' ? 'selected' : '' ?>>
            America/São Paulo (Brasil)
        </option>
        <option value="America/Fortaleza" <?= ($config['timezone'] ?? '') === 'America/Fortaleza' ? 'selected' : '' ?>>
            America/Fortaleza (Brasil - Nordeste)
        </option>
        <option value="America/Manaus" <?= ($config['timezone'] ?? '') === 'America/Manaus' ? 'selected' : '' ?>>
            America/Manaus (Brasil - Amazonas)
        </option>
        <option value="UTC" <?= ($config['timezone'] ?? '') === 'UTC' ? 'selected' : '' ?>>
            UTC (Horário Universal)
        </option>
    </select>
</div>
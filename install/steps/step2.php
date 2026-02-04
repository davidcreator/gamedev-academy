<?php
// install/steps/step2.php
?>

<h2 class="step-title">🗄️ Configuração do Banco de Dados</h2>
<p class="step-description">
    Configure a conexão com o banco de dados MySQL. O banco será criado automaticamente se não existir.
</p>

<div class="alert alert-info">
    <span class="alert-icon">💡</span>
    <div class="alert-content">
        <strong>Dica:</strong> No WAMP, geralmente o usuário é "root" e a senha fica em branco.
    </div>
</div>

<div class="form-group">
    <label class="form-label">🖥️ Host do Banco de Dados</label>
    <input type="text" name="db_host" class="form-control" 
           value="<?= htmlspecialchars($config['db_host'] ?? 'localhost') ?>" 
           placeholder="localhost" required>
    <div class="form-help">Geralmente "localhost" ou "127.0.0.1"</div>
</div>

<div class="form-row">
    <div class="form-group">
        <label class="form-label">🔌 Porta</label>
        <input type="text" name="db_port" class="form-control" 
               value="<?= htmlspecialchars($config['db_port'] ?? '3306') ?>" 
               placeholder="3306" required>
        <div class="form-help">Porta padrão: 3306</div>
    </div>
    
    <div class="form-group">
        <label class="form-label">📦 Nome do Banco de Dados</label>
        <input type="text" name="db_name" class="form-control" 
               value="<?= htmlspecialchars($config['db_name'] ?? 'gamedev_academy') ?>" 
               placeholder="gamedev_academy" 
               pattern="[a-zA-Z0-9_]+" required>
        <div class="form-help">Será criado automaticamente</div>
    </div>
</div>

<div class="form-row">
    <div class="form-group">
        <label class="form-label">👤 Usuário do MySQL</label>
        <input type="text" name="db_user" class="form-control" 
               value="<?= htmlspecialchars($config['db_user'] ?? 'root') ?>" 
               placeholder="root" required>
    </div>
    
    <div class="form-group">
        <label class="form-label">🔐 Senha do MySQL</label>
        <input type="password" name="db_pass" class="form-control" 
               value="<?= htmlspecialchars($config['db_pass'] ?? '') ?>" 
               placeholder="••••••••">
        <div class="form-help">Deixe em branco se não houver</div>
    </div>
</div>

<div class="form-group">
    <label class="form-label">🌍 Charset</label>
    <select name="db_charset" class="form-control">
        <option value="utf8mb4" <?= ($config['db_charset'] ?? 'utf8mb4') === 'utf8mb4' ? 'selected' : '' ?>>
            UTF8MB4 (Recomendado - Suporta emojis)
        </option>
        <option value="utf8" <?= ($config['db_charset'] ?? '') === 'utf8' ? 'selected' : '' ?>>
            UTF8
        </option>
    </select>
</div>

<div class="form-group">
    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
        <input type="checkbox" name="test_connection" value="1" checked>
        <span>Testar conexão antes de continuar</span>
    </label>
</div>
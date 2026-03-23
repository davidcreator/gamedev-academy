<?php
// views/auth/login.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GameDev Academy</title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <a href="<?= url('/') ?>">
                    <h1>🎮 GameDev Academy</h1>
                </a>
                <p>Faça login para continuar</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>E-mail ou nome de usuário</label>
                    <input type="text" name="identifier" class="form-control" autocomplete="username" required>
                </div>
                
                <div class="form-group">
                    <label>Senha</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    Entrar
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Não tem uma conta? <a href="<?= url('register') ?>">Criar conta</a></p>
            </div>
        </div>
    </div>
</body>
</html>

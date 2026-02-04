<?php
// views/auth/register.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta - GameDev Academy</title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <a href="<?= url('/') ?>">
                    <h1>🎮 GameDev Academy</h1>
                </a>
                <p>Crie sua conta gratuita</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Nome Completo</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Nome de Usuário</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Senha</label>
                    <input type="password" name="password" class="form-control" minlength="8" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    Criar Conta
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Já tem uma conta? <a href="<?= url('login') ?>">Fazer login</a></p>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// login.php

require_once 'config/database.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Auth.php';
require_once 'includes/functions.php';

$auth = new Auth();

// Já logado? Redirecionar
if ($auth->isLoggedIn()) {
    redirect($auth->isAdmin() ? url('admin/') : url('user/'));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? ($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    $result = $auth->login($identifier, $password, $remember);
    
    if ($result['success']) {
        $user = $result['user'];
        redirect($user['role'] === 'admin' ? url('admin/') : url('user/'));
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <a href="<?= url() ?>" class="auth-logo">🎮</a>
                <h1 class="auth-title">Bem-vindo de volta!</h1>
                <p class="auth-subtitle">Entre para continuar sua jornada</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= escape($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">E-mail ou nome de usuário</label>
                    <input type="text" name="identifier" class="form-control"
                           placeholder="seu@email.com ou seuusuario" value="<?= old('identifier', $_POST['email'] ?? '') ?>"
                           autocomplete="username" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Senha</label>
                    <input type="password" name="password" class="form-control" 
                           placeholder="••••••••" required>
                </div>
                
                <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="remember"> 
                        <span style="color: var(--gray-400);">Lembrar de mim</span>
                    </label>
                    <a href="<?= url('forgot-password.php') ?>" style="font-size: 0.9rem;">Esqueci a senha</a>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 btn-lg">
                    Entrar
                </button>
            </form>
            
            <div class="auth-footer">
                Não tem uma conta? <a href="<?= url('register.php') ?>">Cadastre-se grátis</a>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <a href="<?= url() ?>" style="color: var(--gray-400);">← Voltar para o site</a>
        </div>
    </div>
</body>
</html>

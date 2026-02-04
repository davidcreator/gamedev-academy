<?php
// register.php

require_once 'config/database.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Auth.php';
require_once 'includes/functions.php';

$auth = new Auth();

if ($auth->isLoggedIn()) {
    redirect(url('user/'));
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'username' => trim($_POST['username'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? ''
    ];
    
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    
    if ($data['password'] !== $passwordConfirm) {
        $error = 'As senhas não coincidem.';
    } else {
        $result = $auth->register($data);
        
        if ($result['success']) {
            redirect(url('user/'));
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta - <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <a href="<?= url() ?>" class="auth-logo">🎮</a>
                <h1 class="auth-title">Criar sua conta</h1>
                <p class="auth-subtitle">Comece sua jornada no GameDev</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= escape($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Nome completo</label>
                    <input type="text" name="full_name" class="form-control" 
                           placeholder="Seu nome" value="<?= old('full_name') ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Nome de usuário</label>
                    <input type="text" name="username" class="form-control" 
                           placeholder="seu_username" value="<?= old('username') ?>" 
                           pattern="[a-zA-Z0-9_]+" required>
                    <small style="color: var(--gray-500);">Apenas letras, números e underscore</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" 
                           placeholder="seu@email.com" value="<?= old('email') ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Senha</label>
                    <input type="password" name="password" class="form-control" 
                           placeholder="Mínimo 6 caracteres" minlength="6" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Confirmar senha</label>
                    <input type="password" name="password_confirm" class="form-control" 
                           placeholder="Digite a senha novamente" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 btn-lg">
                    🚀 Criar Conta
                </button>
            </form>
            
            <div class="auth-footer">
                Já tem uma conta? <a href="<?= url('login.php') ?>">Entrar</a>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <a href="<?= url() ?>" style="color: var(--gray-400);">← Voltar para o site</a>
        </div>
    </div>
</body>
</html>
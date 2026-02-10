<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/mail/Mailer.php';

$error = '';
$success = '';
$valid_token = false;
$token = $_GET['token'] ?? $_POST['token'] ?? '';

if ($token) {
    $token_hash = hash('sha256', $token);
    
    // Verificar token
    $stmt = $pdo->prepare("
        SELECT email, expires_at 
        FROM password_resets 
        WHERE token = ? 
        AND expires_at > NOW()
        AND used = 0
    ");
    $stmt->execute([$token_hash]);
    $reset = $stmt->fetch();
    
    if ($reset) {
        $valid_token = true;
        $email = $reset['email'];
        
        // Calcular tempo restante
        $expires_time = strtotime($reset['expires_at']);
        $time_left = $expires_time - time();
    } else {
        $error = "Link inválido ou expirado. Solicite um novo link de recuperação.";
    }
}

// Processar nova senha
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $valid_token) {
    // Verificar CSRF
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Erro de segurança. Recarregue a página e tente novamente.";
    } else {
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validações
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = "A senha deve ter no mínimo 8 caracteres";
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "A senha deve conter pelo menos uma letra maiúscula";
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "A senha deve conter pelo menos uma letra minúscula";
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "A senha deve conter pelo menos um número";
        }
        
        if ($password !== $confirm_password) {
            $errors[] = "As senhas não coincidem";
        }
        
        if (empty($errors)) {
            $token_hash = hash('sha256', $token);
            
            // Buscar informações do reset
            $stmt = $pdo->prepare("
                SELECT email FROM password_resets 
                WHERE token = ? AND expires_at > NOW() AND used = 0
            ");
            $stmt->execute([$token_hash]);
            $reset = $stmt->fetch();
            
            if ($reset) {
                // Atualizar senha
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE email = ?");
                $stmt->execute([$hashed_password, $reset['email']]);
                
                // Marcar token como usado
                $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
                $stmt->execute([$token_hash]);
                
                // Buscar nome do usuário para notificação
                $stmt = $pdo->prepare("SELECT name FROM users WHERE email = ?");
                $stmt->execute([$reset['email']]);
                $user = $stmt->fetch();
                
                // Enviar notificação
                $mailer = new Mailer();
                $mailer->sendPasswordChangedNotification($reset['email'], $user['name']);
                
                // Log de segurança
                logSecurityEvent('password_reset_success', $reset['email']);
                
                $success = "Senha alterada com sucesso! Redirecionando para o login...";
                header("refresh:3;url=login.php");
            } else {
                $error = "Token inválido ou expirado.";
            }
        } else {
            $error = implode("<br>", $errors);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Crie sua nova senha da GameDev Academy">
    <title>Nova Senha - GameDev Academy</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="auth-container">
        <div class="auth-box">
            <h2>🔑 Criar Nova Senha</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($valid_token && !$success): ?>
                <p class="text-muted">
                    Crie uma senha forte para sua conta
                    <?php if (isset($time_left)): ?>
                        <br><small>Tempo restante: <span id="token-timer"><?php echo gmdate("i:s", $time_left); ?></span></small>
                    <?php endif; ?>
                </p>
                
                <form id="reset-password-form" method="POST" action="">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label for="password">Nova Senha</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control" 
                            required 
                            minlength="8"
                            autocomplete="new-password"
                            placeholder="Mínimo 8 caracteres"
                        >
                        <div class="password-strength">
                            <div class="password-strength-bar">
                                <div class="password-strength-fill"></div>
                            </div>
                        </div>
                        <ul class="password-requirements">
                            <li>Mínimo 8 caracteres</li>
                            <li>Uma letra minúscula</li>
                            <li>Uma letra maiúscula</li>
                            <li>Um número</li>
                            <li>Um caractere especial (recomendado)</li>
                        </ul>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmar Senha</label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            class="form-control" 
                            required
                            autocomplete="new-password"
                            placeholder="Digite a senha novamente"
                        >
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        Alterar Senha
                    </button>
                </form>
            <?php else: ?>
                <?php if (!$success): ?>
                    <div class="text-center">
                        <p>Este link de recuperação é inválido ou expirou.</p>
                        <a href="forgot-password.php" class="btn btn-primary">
                            Solicitar Novo Link
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="auth-links">
                <a href="login.php">← Voltar ao Login</a>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <!-- JavaScript -->
    <script src="assets/js/auth.js"></script>
</body>
</html>
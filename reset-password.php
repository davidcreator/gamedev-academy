<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mail/Mailer.php';

$error = '';
$success = '';
$valid_token = false;
$token = $_GET['token'] ?? $_POST['token'] ?? '';

if ($token) {
    $token_hash = hash('sha256', $token);
    
    try {
        // Verificar se o token é válido
        $stmt = $conn->prepare("
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
            $expires_time = strtotime($reset['expires_at']);
            $time_left = $expires_time - time();
        } else {
            $error = "Link inválido ou expirado. Solicite um novo link de recuperação.";
        }
    } catch (Exception $e) {
        error_log("Erro ao verificar token: " . $e->getMessage());
        $error = "Erro ao processar solicitação.";
    }
}

// Processar nova senha
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $valid_token) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validações
    if (strlen($password) < 8) {
        $error = "A senha deve ter no mínimo 8 caracteres.";
    } elseif ($password !== $confirm_password) {
        $error = "As senhas não coincidem.";
    } else {
        try {
            $token_hash = hash('sha256', $token);
            
            // Buscar email do token
            $stmt = $conn->prepare("
                SELECT email FROM password_resets 
                WHERE token = ? AND expires_at > NOW() AND used = 0
            ");
            $stmt->execute([$token_hash]);
            $reset = $stmt->fetch();
            
            if ($reset) {
                // Atualizar senha
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->execute([$hashed_password, $reset['email']]);
                
                // Marcar token como usado
                $stmt = $conn->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
                $stmt->execute([$token_hash]);
                
                // Buscar nome do usuário
                $stmt = $conn->prepare("SELECT name FROM users WHERE email = ?");
                $stmt->execute([$reset['email']]);
                $user = $stmt->fetch();
                
                // Enviar notificação
                $mailer = new Mailer();
                $mailer->sendPasswordChangedNotification($reset['email'], $user['name']);
                
                $success = "Senha alterada com sucesso! Redirecionando para o login...";
                header("refresh:3;url=login.php");
            } else {
                $error = "Token inválido ou expirado.";
            }
        } catch (Exception $e) {
            error_log("Erro ao resetar senha: " . $e->getMessage());
            $error = "Erro ao processar solicitação.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Crie sua nova senha">
    <title>Nova Senha - <?php echo SITE_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="auth-page">
    
    <!-- Main Container -->
    <div class="auth-wrapper">
        <div class="auth-container">
            <!-- Logo Section -->
            <div class="auth-logo">
                <a href="index.php">
                    <img src="assets/img/logo.png" alt="GameDev Academy">
                    <h2>GameDev Academy</h2>
                </a>
            </div>
            
            <!-- Form Card -->
            <div class="auth-card">
                <div class="auth-card-header">
                    <h3 class="auth-title">Criar Nova Senha</h3>
                    <?php if ($valid_token): ?>
                        <p class="auth-subtitle">
                            Escolha uma senha forte para sua conta
                            <?php if (isset($time_left)): ?>
                                <br>
                                <small class="text-warning">
                                    <i class="fas fa-clock"></i> 
                                    Tempo restante: <span id="countdown"><?php echo gmdate("i:s", $time_left); ?></span>
                                </small>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>
                
                <div class="auth-card-body">
                    <!-- Alerts -->
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i>
                            <?php echo htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($valid_token && !$success): ?>
                        <!-- Password Reset Form -->
                        <form method="POST" action="" class="auth-form" id="resetPasswordForm">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            
                            <!-- Password Field -->
                            <div class="form-group mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i> Nova Senha
                                </label>
                                <div class="input-group">
                                    <input 
                                        type="password" 
                                        class="form-control form-control-lg" 
                                        id="password" 
                                        name="password" 
                                        placeholder="Mínimo 8 caracteres"
                                        required
                                        minlength="8"
                                    >
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                
                                <!-- Password Strength Indicator -->
                                <div class="password-strength mt-2">
                                    <div class="strength-bar">
                                        <div class="strength-bar-fill" id="strengthBar"></div>
                                    </div>
                                    <small class="strength-text" id="strengthText">Digite uma senha</small>
                                </div>
                                
                                <!-- Password Requirements -->
                                <div class="password-requirements mt-2">
                                    <small class="text-muted">A senha deve conter:</small>
                                    <ul class="requirements-list">
                                        <li id="length" class="requirement">
                                            <i class="fas fa-circle"></i> Mínimo 8 caracteres
                                        </li>
                                        <li id="uppercase" class="requirement">
                                            <i class="fas fa-circle"></i> Uma letra maiúscula
                                        </li>
                                        <li id="lowercase" class="requirement">
                                            <i class="fas fa-circle"></i> Uma letra minúscula
                                        </li>
                                        <li id="number" class="requirement">
                                            <i class="fas fa-circle"></i> Um número
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            
                            <!-- Confirm Password Field -->
                            <div class="form-group mb-4">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-check-double"></i> Confirmar Senha
                                </label>
                                <div class="input-group">
                                    <input 
                                        type="password" 
                                        class="form-control form-control-lg" 
                                        id="confirm_password" 
                                        name="confirm_password" 
                                        placeholder="Digite a senha novamente"
                                        required
                                    >
                                    <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback" id="confirmError">
                                    As senhas não coincidem
                                </div>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="form-group mb-3">
                                <button type="submit" class="btn btn-primary btn-lg btn-block w-100" id="submitBtn">
                                    <i class="fas fa-save"></i> Alterar Senha
                                </button>
                            </div>
                        </form>
                        
                    <?php else: ?>
                        <?php if (!$success): ?>
                            <!-- Invalid Token Message -->
                            <div class="text-center py-4">
                                <div class="mb-4">
                                    <i class="fas fa-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                                </div>
                                <h4>Link Inválido ou Expirado</h4>
                                <p class="text-muted">
                                    Este link de recuperação não é válido ou já expirou.
                                    <br>Por favor, solicite um novo link.
                                </p>
                                <a href="forgot-password.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-redo"></i> Solicitar Novo Link
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <!-- Links -->
                    <div class="auth-links text-center mt-4">
                        <a href="login.php" class="auth-link">
                            <i class="fas fa-arrow-left"></i> Voltar ao Login
                        </a>
                    </div>
                </div>
                
                <!-- Card Footer -->
                <div class="auth-card-footer">
                    <p class="text-muted text-center small mb-0">
                        <i class="fas fa-shield-alt"></i> Sua senha será criptografada
                    </p>
                </div>
            </div>
            
            <!-- Footer Links -->
            <div class="auth-footer">
                <div class="auth-footer-links">
                    <a href="index.php">Home</a>
                    <span class="separator">•</span>
                    <a href="about.php">Sobre</a>
                    <span class="separator">•</span>
                    <a href="contact.php">Contato</a>
                    <span class="separator">•</span>
                    <a href="privacy.php">Privacidade</a>
                </div>
                <p class="copyright">
                    © <?php echo date('Y'); ?> GameDev Academy. Todos os direitos reservados.
                </p>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/password-reset.js"></script>
</body>
</html>
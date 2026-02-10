<?php
/**
 * GameDev Academy - Forgot Password Page
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth/PasswordReset.php';
require_once __DIR__ . '/../includes/mail/Mailer.php';

use GameDev\Auth\PasswordReset;
use GameDev\Mail\Mailer;

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success = false;
$error = '';
$message = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Token de segurança inválido. Recarregue a página.';
    } else {
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Por favor, insira um email válido.';
        } else {
            try {
                // Conectar ao banco
                $pdo = new PDO(
                    sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME),
                    DB_USER,
                    DB_PASS
                );
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Solicitar reset
                $passwordReset = new PasswordReset($pdo, DB_PREFIX);
                $result = $passwordReset->requestReset($email);
                
                if ($result && $result['success'] && $result['user']) {
                    // Gerar link de recuperação
                    $resetLink = SITE_URL . '/auth/reset-password.php?token=' . $result['token'];
                    
                    // Enviar email
                    $mailer = Mailer::fromDatabase($pdo, DB_PREFIX);
                    $sent = $mailer->sendPasswordReset(
                        $email,
                        $resetLink,
                        $result['user']['name'] ?? $result['user']['username'] ?? 'Usuário'
                    );
                    
                    if ($sent) {
                        $success = true;
                        $message = 'Enviamos um email com instruções para recuperar sua senha.';
                    } else {
                        $error = 'Erro ao enviar email. Tente novamente mais tarde.';
                    }
                } else {
                    // Mensagem genérica por segurança
                    $success = true;
                    $message = 'Se o email estiver cadastrado, você receberá as instruções de recuperação.';
                }
                
            } catch (Exception $e) {
                $error = 'Erro no sistema. Tente novamente mais tarde.';
                error_log('Password reset error: ' . $e->getMessage());
            }
        }
    }
}

$pageTitle = 'Recuperar Senha';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME ?? 'GameDev Academy'; ?></title>
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <a href="../" class="auth-logo">
                        <img src="../assets/images/logo.png" alt="Logo" onerror="this.style.display='none'">
                        <h1><?php echo SITE_NAME ?? 'GameDev Academy'; ?></h1>
                    </a>
                </div>
                
                <div class="auth-body">
                    <div class="auth-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    
                    <h2>Recuperar Senha</h2>
                    <p class="auth-subtitle">Digite seu email para receber as instruções de recuperação.</p>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                        <div class="auth-links">
                            <a href="login.php" class="btn btn-primary btn-block">
                                <i class="fas fa-arrow-left"></i>
                                Voltar ao Login
                            </a>
                        </div>
                    <?php else: ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" class="auth-form" id="forgotForm">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            
                            <div class="form-group">
                                <label for="email">
                                    <i class="fas fa-envelope"></i>
                                    Email
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       placeholder="Seu email cadastrado"
                                       required
                                       autofocus>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-paper-plane"></i>
                                Enviar Instruções
                            </button>
                        </form>
                        
                        <div class="auth-links">
                            <p>Lembrou sua senha? <a href="login.php">Fazer login</a></p>
                            <p>Não tem conta? <a href="register.php">Cadastre-se</a></p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="auth-footer">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME ?? 'GameDev Academy'; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/auth.js"></script>
</body>
</html>
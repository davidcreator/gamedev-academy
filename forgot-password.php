<?php
/**
 * GameDev Academy - Forgot Password Page
 * Localização: /forgot-password.php (raiz do projeto)
 */

// Incluir configuração
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
} else {
    die('Erro: Arquivo config.php não encontrado. Execute a instalação primeiro.');
}

// Verificar se as classes necessárias existem
if (!file_exists(__DIR__ . '/includes/auth/PasswordReset.php')) {
    die('Erro: Classe PasswordReset não encontrada em /includes/auth/');
}

if (!file_exists(__DIR__ . '/includes/mail/Mailer.php')) {
    die('Erro: Classe Mailer não encontrada em /includes/mail/');
}

require_once __DIR__ . '/includes/auth/PasswordReset.php';
require_once __DIR__ . '/includes/mail/Mailer.php';

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
                $dsn = sprintf(
                    'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                    defined('DB_HOST') ? DB_HOST : 'localhost',
                    defined('DB_PORT') ? DB_PORT : 3306,
                    defined('DB_NAME') ? DB_NAME : ''
                );
                
                $pdo = new PDO(
                    $dsn,
                    defined('DB_USER') ? DB_USER : '',
                    defined('DB_PASS') ? DB_PASS : ''
                );
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Solicitar reset
                $passwordReset = new PasswordReset($pdo, defined('DB_PREFIX') ? DB_PREFIX : '');
                $result = $passwordReset->requestReset($email);
                
                if ($result && $result['success'] && isset($result['user']) && $result['user']) {
                    // Gerar link de recuperação
                    $siteUrl = defined('SITE_URL') ? SITE_URL : 
                              (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . 
                              $_SERVER['HTTP_HOST'] . 
                              rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
                    
                    $resetLink = $siteUrl . '/reset-password.php?token=' . $result['token'];
                    
                    // Configuração do Mailer
                    $mailerConfig = [
                        'smtp_host' => defined('SMTP_HOST') ? SMTP_HOST : '',
                        'smtp_port' => defined('SMTP_PORT') ? SMTP_PORT : 587,
                        'smtp_user' => defined('SMTP_USER') ? SMTP_USER : '',
                        'smtp_pass' => defined('SMTP_PASS') ? SMTP_PASS : '',
                        'smtp_security' => defined('SMTP_SECURITY') ? SMTP_SECURITY : 'tls',
                        'from_email' => defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'noreply@localhost',
                        'from_name' => defined('SITE_NAME') ? SITE_NAME : 'GameDev Academy',
                        'site_url' => $siteUrl
                    ];
                    
                    // Enviar email
                    $mailer = new Mailer($mailerConfig);
                    $sent = $mailer->sendPasswordReset(
                        $email,
                        $resetLink,
                        $result['user']['name'] ?? $result['user']['username'] ?? 'Usuário',
                        '1 hora'
                    );
                    
                    if ($sent) {
                        $success = true;
                        $message = 'Enviamos um email com instruções para recuperar sua senha. Verifique sua caixa de entrada.';
                    } else {
                        $lastError = $mailer->getLastError();
                        $error = 'Erro ao enviar email: ' . ($lastError ?: 'Verifique as configurações SMTP.');
                        error_log('Mailer error: ' . $lastError);
                    }
                } else {
                    // Mensagem genérica por segurança (não revelar se email existe)
                    $success = true;
                    $message = 'Se o email estiver cadastrado, você receberá as instruções de recuperação em alguns instantes.';
                }
                
            } catch (PDOException $e) {
                $error = 'Erro de conexão com banco de dados. Tente novamente mais tarde.';
                error_log('Database error in forgot-password: ' . $e->getMessage());
            } catch (Exception $e) {
                $error = 'Erro no sistema. Tente novamente mais tarde.';
                error_log('Password reset error: ' . $e->getMessage());
            }
        }
    }
}

$pageTitle = 'Recuperar Senha';
$siteName = defined('SITE_NAME') ? SITE_NAME : 'GameDev Academy';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle . ' - ' . $siteName); ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <a href="index.php" class="auth-logo">
                        <?php if (file_exists('assets/images/logo.png')): ?>
                        <img src="assets/images/logo.png" alt="Logo">
                        <?php endif; ?>
                        <h1><?php echo htmlspecialchars($siteName); ?></h1>
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
                            <div><?php echo htmlspecialchars($message); ?></div>
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
                                <div><?php echo htmlspecialchars($error); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" class="auth-form" id="forgotForm">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            
                            <div class="form-group">
                                <label for="email">
                                    <i class="fas fa-envelope"></i>
                                    Email
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       placeholder="seuemail@exemplo.com"
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
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
                            <?php if (file_exists('register.php')): ?>
                            <p>Não tem conta? <a href="register.php">Cadastre-se</a></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="auth-footer">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($siteName); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/auth.js"></script>
</body>
</html>
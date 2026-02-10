<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mail/Mailer.php';

// Se já estiver logado, redirecionar
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    
    if ($email) {
        try {
            global $conn;
            
            // Verificar se o email existe
            $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Gerar token único
                $token = bin2hex(random_bytes(32));
                $token_hash = hash('sha256', $token);
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Limpar tokens antigos
                $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
                $stmt->execute([$email]);
                
                // Inserir novo token
                $stmt = $conn->prepare("
                    INSERT INTO password_resets (email, token, expires_at, created_at) 
                    VALUES (?, ?, ?, NOW())
                ");
                $stmt->execute([$email, $token_hash, $expires]);
                
                // Enviar email
                $mailer = new Mailer();
                if ($mailer->sendPasswordResetEmail($email, $user['name'], $token)) {
                    $success = "Um link de recuperação foi enviado para seu email.";
                } else {
                    $success = "Se o email estiver cadastrado, você receberá as instruções.";
                }
            } else {
                // Por segurança, mostrar mensagem genérica
                $success = "Se o email estiver cadastrado, você receberá as instruções de recuperação.";
            }
            
        } catch (Exception $e) {
            error_log("Erro na recuperação de senha: " . $e->getMessage());
            $error = "Ocorreu um erro ao processar sua solicitação. Tente novamente.";
        }
    } else {
        $error = "Por favor, insira um email válido.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Recupere sua senha da GameDev Academy">
    <title>Recuperar Senha - <?php echo SITE_NAME; ?></title>
    
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
                    <h3 class="auth-title">Recuperar Senha</h3>
                    <p class="auth-subtitle">Digite seu email para receber o link de recuperação</p>
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
                    
                    <!-- Form -->
                    <form method="POST" action="" class="auth-form" id="forgotPasswordForm">
                        <div class="form-group mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i> Email
                            </label>
                            <input 
                                type="email" 
                                class="form-control form-control-lg" 
                                id="email" 
                                name="email" 
                                placeholder="seu@email.com"
                                required
                                autofocus
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                            >
                            <div class="form-text">
                                Digite o email associado à sua conta
                            </div>
                        </div>
                        
                        <div class="form-group mb-4">
                            <button type="submit" class="btn btn-primary btn-lg btn-block w-100">
                                <i class="fas fa-paper-plane"></i> Enviar Link de Recuperação
                            </button>
                        </div>
                    </form>
                    
                    <!-- Divider -->
                    <div class="auth-divider">
                        <span>ou</span>
                    </div>
                    
                    <!-- Links -->
                    <div class="auth-links text-center">
                        <p class="mb-2">
                            Lembrou a senha? 
                            <a href="login.php" class="auth-link">
                                <i class="fas fa-sign-in-alt"></i> Fazer Login
                            </a>
                        </p>
                        <p>
                            Não tem uma conta? 
                            <a href="register.php" class="auth-link">
                                <i class="fas fa-user-plus"></i> Cadastrar-se
                            </a>
                        </p>
                    </div>
                </div>
                
                <!-- Card Footer -->
                <div class="auth-card-footer">
                    <p class="text-muted text-center small mb-0">
                        <i class="fas fa-lock"></i> Conexão segura e criptografada
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
    <script src="assets/js/auth-validation.js"></script>
</body>
</html>
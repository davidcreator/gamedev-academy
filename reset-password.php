<?php
session_start();

// Includes necessários
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mail/Mailer.php';

// Verificar conexão
if (!isset($pdo) && !isset($conn)) {
    die("Erro: Conexão com banco de dados não estabelecida.");
}

// Usar a variável de conexão disponível
$db = isset($pdo) ? $pdo : $conn;

$error = '';
$success = '';
$valid_token = false;
$token = $_GET['token'] ?? $_POST['token'] ?? '';

if ($token) {
    $token_hash = hash('sha256', $token);
    
    try {
        // Verificar se o token é válido
        $stmt = $db->prepare("
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
            $stmt = $db->prepare("
                SELECT email FROM password_resets 
                WHERE token = ? AND expires_at > NOW() AND used = 0
            ");
            $stmt->execute([$token_hash]);
            $reset = $stmt->fetch();
            
            if ($reset) {
                // Atualizar senha
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->execute([$hashed_password, $reset['email']]);
                
                // Marcar token como usado
                $stmt = $db->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
                $stmt->execute([$token_hash]);
                
                // Buscar nome do usuário
                $stmt = $db->prepare("SELECT name FROM users WHERE email = ?");
                $stmt->execute([$reset['email']]);
                $user = $stmt->fetch();
                
                // Enviar notificação
                try {
                    $mailer = new Mailer();
                    $mailer->sendPasswordChangedNotification($reset['email'], $user['name']);
                } catch (Exception $mailError) {
                    error_log("Erro ao enviar notificação: " . $mailError->getMessage());
                }
                
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
    <title>Nova Senha - GameDev Academy</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="auth-bg">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="login-container">
                    <!-- Header -->
                    <div class="login-header">
                        <h2><i class="fas fa-gamepad"></i> GameDev Academy</h2>
                        <p>Criar Nova Senha</p>
                        <?php if ($valid_token && isset($time_left) && $time_left > 0): ?>
                            <small class="countdown">
                                <i class="fas fa-clock"></i> 
                                Tempo restante: <span id="countdown"><?php echo gmdate("i:s", $time_left); ?></span>
                            </small>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Body -->
                    <div class="login-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($valid_token && !$success): ?>
                            <form method="POST" action="" id="resetPasswordForm">
                                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                                
                                <div class="mb-3">
                                    <div class="form-floating position-relative">
                                        <input 
                                            type="password" 
                                            class="form-control" 
                                            id="password" 
                                            name="password" 
                                            placeholder="Nova senha"
                                            required
                                            minlength="8"
                                        >
                                        <label for="password">
                                            <i class="fas fa-lock"></i> Nova Senha
                                        </label>
                                        <span class="password-toggle" onclick="togglePassword('password')">
                                            <i class="fas fa-eye" id="togglePasswordIcon"></i>
                                        </span>
                                    </div>
                                    <div class="password-strength">
                                        <div class="password-strength-bar" id="strengthBar"></div>
                                    </div>
                                    <small class="form-text">
                                        Mínimo de 8 caracteres
                                    </small>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-floating position-relative">
                                        <input 
                                            type="password" 
                                            class="form-control" 
                                            id="confirm_password" 
                                            name="confirm_password" 
                                            placeholder="Confirmar senha"
                                            required
                                        >
                                        <label for="confirm_password">
                                            <i class="fas fa-check-double"></i> Confirmar Senha
                                        </label>
                                        <span class="password-toggle" onclick="togglePassword('confirm_password')">
                                            <i class="fas fa-eye" id="toggleConfirmPasswordIcon"></i>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save"></i> Alterar Senha
                                    </button>
                                </div>
                            </form>
                        <?php elseif (!$success): ?>
                            <div class="text-center py-4">
                                <div class="mb-3">
                                    <i class="fas fa-exclamation-triangle text-warning icon-lg"></i>
                                </div>
                                <h5>Link Inválido ou Expirado</h5>
                                <p class="text-muted">
                                    Este link de recuperação não é mais válido.
                                </p>
                                <a href="forgot-password.php" class="btn btn-primary">
                                    <i class="fas fa-redo"></i> Solicitar Novo Link
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="auth-links">
                            <a href="login.php">
                                <i class="fas fa-arrow-left"></i> Voltar ao Login
                            </a>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="login-footer">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt"></i> Sua senha será criptografada
                        </small>
                    </div>
                </div>
                
                <!-- Links do rodapé -->
                <div class="footer-links">
                    <small>
                        <a href="index.php">Home</a> | 
                        <a href="about.php">Sobre</a> | 
                        <a href="contact.php">Contato</a>
                    </small>
                    <div class="footer-copyright">
                        © <?php echo date('Y'); ?> GameDev Academy. Todos os direitos reservados.
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="assets/js/main.js"></script>
    <script src="assets/js/auth.js"></script>
</body>
</html>
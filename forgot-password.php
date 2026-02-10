<?php
session_start();
require_once 'includes/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/mail/Mailer.php';

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
        // Rate limiting check
        if (!checkRateLimit($email, 'password_reset', 3, 900)) {
            $error = "Muitas tentativas. Tente novamente em 15 minutos.";
        } else {
            // Verificar se o email existe
            $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ? AND active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Gerar token
                $token = bin2hex(random_bytes(32));
                $token_hash = hash('sha256', $token);
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Limpar tokens antigos
                $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
                $stmt->execute([$email]);
                
                // Inserir novo token
                $stmt = $pdo->prepare("
                    INSERT INTO password_resets (email, token, expires_at) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$email, $token_hash, $expires]);
                
                // Enviar email
                $mailer = new Mailer();
                $mailer->sendPasswordResetEmail($email, $user['name'], $token);
            }
            
            // Sempre mostrar mensagem de sucesso por segurança
            $success = "Se o email estiver cadastrado, você receberá as instruções de recuperação.";
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
    <title>Recuperar Senha - GameDev Academy</title>
    
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
            <h2>🔐 Recuperar Senha</h2>
            <p class="text-muted">Digite seu email cadastrado para receber o link de recuperação</p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form id="forgot-password-form" method="POST" action="">
                <?php // CSRF Token ?>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="email">
                        Email
                        <span class="help-tooltip">
                            <span class="tooltip-content">
                                Use o email cadastrado em sua conta
                            </span>
                        </span>
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control" 
                        required 
                        autocomplete="email"
                        placeholder="seu@email.com"
                        autofocus
                    >
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    Enviar Link de Recuperação
                </button>
            </form>
            
            <div class="divider">
                <span>ou</span>
            </div>
            
            <div class="auth-links">
                <a href="login.php">← Voltar ao Login</a>
                <span class="mx-2">|</span>
                <a href="register.php">Criar Conta</a>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <!-- JavaScript -->
    <script src="assets/js/auth.js"></script>
</body>
</html>
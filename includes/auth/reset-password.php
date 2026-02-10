<?php
/**
 * GameDev Academy - Reset Password Page
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth/PasswordReset.php';

use GameDev\Auth\PasswordReset;

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$token = $_GET['token'] ?? '';
$success = false;
$error = '';
$message = '';
$tokenValid = false;
$userEmail = '';

// Verificar token
if (!empty($token)) {
    try {
        $pdo = new PDO(
            sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME),
            DB_USER,
            DB_PASS
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $passwordReset = new PasswordReset($pdo, DB_PREFIX);
        $tokenData = $passwordReset->verifyToken($token);
        
        if ($tokenData && $tokenData['valid']) {
            $tokenValid = true;
            $userEmail = $tokenData['email'];
        } else {
            $error = 'Link inválido ou expirado. Solicite uma nova recuperação de senha.';
        }
        
    } catch (Exception $e) {
        $error = 'Erro no sistema. Tente novamente.';
        error_log('Token verification error: ' . $e->getMessage());
    }
} else {
    $error = 'Token não fornecido.';
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Token de segurança inválido.';
    } else {
        $newPassword = $_POST['password'] ?? '';
        $confirmPassword = $_POST['password_confirm'] ?? '';
        
        try {
            $pdo = new PDO(
                sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME),
                DB_USER,
                DB_PASS
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $passwordReset = new PasswordReset($pdo, DB_PREFIX);
            $result = $passwordReset->resetPassword($token, $newPassword, $confirmPassword);
            
            if ($result && $result['success']) {
                $success = true;
                $message = 'Senha alterada com sucesso! Você já pode fazer login.';
                $tokenValid = false; // Esconder formulário
            } else {
                $errors = $passwordReset->getErrors();
                $error = implode('<br>', $errors);
            }
            
        } catch (Exception $e) {
            $error = 'Erro ao alterar senha. Tente novamente.';
            error_log('Password reset error: ' . $e->getMessage());
        }
    }
}

$pageTitle = 'Redefinir Senha';
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
                        <i class="fas fa-lock"></i>
                    </div>
                    
                    <h2>Redefinir Senha</h2>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo $message; ?>
                        </div>
                        <div class="auth-links">
                            <a href="login.php" class="btn btn-primary btn-block">
                                <i class="fas fa-sign-in-alt"></i>
                                Ir para Login
                            </a>
                        </div>
                        
                    <?php elseif ($error && !$tokenValid): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $error; ?>
                        </div>
                        <div class="auth-links">
                            <a href="forgot-password.php" class="btn btn-primary btn-block">
                                <i class="fas fa-redo"></i>
                                Solicitar Nova Recuperação
                            </a>
                        </div>
                        
                    <?php elseif ($tokenValid): ?>
                        <p class="auth-subtitle">
                            Crie uma nova senha para <strong><?php echo htmlspecialchars($userEmail); ?></strong>
                        </p>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" class="auth-form" id="resetForm">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            
                            <div class="form-group">
                                <label for="password">
                                    <i class="fas fa-lock"></i>
                                    Nova Senha
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Mínimo 8 caracteres"
                                           required
                                           minlength="8">
                                    <button type="button" class="btn-toggle-password" data-target="password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                
                                <!-- Barra de força -->
                                <div class="password-strength-container">
                                    <div class="password-strength-bar">
                                        <div id="passwordStrengthFill" class="password-strength-fill"></div>
                                    </div>
                                    <div id="passwordStrengthText" class="password-strength-text">
                                        <i class="fas fa-shield-alt"></i>
                                        <span>Digite uma senha</span>
                                    </div>
                                </div>
                                
                                <!-- Requisitos -->
                                <div class="password-requirements">
                                    <div class="password-requirements-title">A senha deve ter:</div>
                                    <ul>
                                        <li id="req-length" class="invalid">
                                            <i class="fas fa-times"></i> Mínimo 8 caracteres
                                        </li>
                                        <li id="req-uppercase" class="invalid">
                                            <i class="fas fa-times"></i> Uma letra maiúscula
                                        </li>
                                        <li id="req-lowercase" class="invalid">
                                            <i class="fas fa-times"></i> Uma letra minúscula
                                        </li>
                                        <li id="req-number" class="invalid">
                                            <i class="fas fa-times"></i> Um número
                                        </li>
                                        <li id="req-special" class="invalid">
                                            <i class="fas fa-times"></i> Um caractere especial
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="password_confirm">
                                    <i class="fas fa-lock"></i>
                                    Confirmar Senha
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control" 
                                           id="password_confirm" 
                                           name="password_confirm" 
                                           placeholder="Digite novamente"
                                           required
                                           minlength="8">
                                    <button type="button" class="btn-toggle-password" data-target="password_confirm">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div id="passwordMatchStatus" class="validation-status"></div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block" id="submitBtn">
                                <i class="fas fa-save"></i>
                                Salvar Nova Senha
                            </button>
                        </form>
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
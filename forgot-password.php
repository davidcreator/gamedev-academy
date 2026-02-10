<?php
session_start();

// Includes necessários
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mail/Mailer.php';

// Verificar se a conexão existe
if (!isset($pdo) && !isset($conn)) {
    die("Erro: Conexão com banco de dados não estabelecida.");
}

// Usar a variável de conexão disponível
$db = isset($pdo) ? $pdo : $conn;

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
            // Verificar se o email existe
            $stmt = $db->prepare("SELECT id, name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Gerar token único
                $token = bin2hex(random_bytes(32));
                $token_hash = hash('sha256', $token);
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Verificar se a tabela password_resets existe
                $checkTable = $db->query("SHOW TABLES LIKE 'password_resets'");
                if ($checkTable->rowCount() == 0) {
                    // Criar tabela se não existir
                    $createTable = "
                        CREATE TABLE password_resets (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            email VARCHAR(255) NOT NULL,
                            token VARCHAR(255) NOT NULL,
                            expires_at DATETIME NOT NULL,
                            used BOOLEAN DEFAULT 0,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            INDEX idx_token (token),
                            INDEX idx_email (email)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                    ";
                    $db->exec($createTable);
                }
                
                // Limpar tokens antigos
                $stmt = $db->prepare("DELETE FROM password_resets WHERE email = ?");
                $stmt->execute([$email]);
                
                // Inserir novo token
                $stmt = $db->prepare("
                    INSERT INTO password_resets (email, token, expires_at) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$email, $token_hash, $expires]);
                
                // Enviar email
                try {
                    $mailer = new Mailer();
                    $emailSent = $mailer->sendPasswordResetEmail($email, $user['name'], $token);
                    
                    if ($emailSent) {
                        $success = "Um link de recuperação foi enviado para seu email.";
                    } else {
                        $success = "Se o email estiver cadastrado, você receberá as instruções em breve.";
                    }
                } catch (Exception $mailError) {
                    error_log("Erro no envio de email: " . $mailError->getMessage());
                    $success = "Se o email estiver cadastrado, você receberá as instruções em breve.";
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
    <title>Recuperar Senha - GameDev Academy</title>
    
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
                        <p>Recuperação de Senha</p>
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
                        
                        <form method="POST" action="" id="forgotPasswordForm">
                            <div class="mb-3">
                                <div class="form-floating">
                                    <input 
                                        type="email" 
                                        class="form-control" 
                                        id="email" 
                                        name="email" 
                                        placeholder="nome@exemplo.com"
                                        required
                                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                    >
                                    <label for="email">
                                        <i class="fas fa-envelope"></i> Email
                                    </label>
                                </div>
                                <div class="form-text">
                                    Digite o email associado à sua conta para receber o link de recuperação.
                                </div>
                            </div>
                            
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane"></i> Enviar Link de Recuperação
                                </button>
                            </div>
                        </form>
                        
                        <div class="divider">
                            <span>ou</span>
                        </div>
                        
                        <div class="auth-links">
                            <p class="mb-2">
                                Lembrou a senha? 
                                <a href="login.php">Fazer Login</a>
                            </p>
                            <p>
                                Ainda não tem conta? 
                                <a href="register.php">Cadastre-se</a>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="login-footer">
                        <small class="text-muted">
                            <i class="fas fa-lock"></i> Conexão segura e criptografada
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
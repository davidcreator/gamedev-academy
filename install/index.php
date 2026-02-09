<?php
/**
 * GameDev Academy - Sistema de Instalação
 * Arquivo principal do instalador
 * @version 2.0
 */

// Configurações de erro para desenvolvimento (remover em produção)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Não mostrar erros direto na tela
ini_set('log_errors', 1);

// Definir constante de segurança
define('INSTALLER', true);
define('INSTALL_PATH', __DIR__);
define('ROOT_PATH', dirname(__DIR__));

// Iniciar sessão com configurações seguras
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'use_strict_mode' => true
    ]);
}

// Gerar token CSRF se não existir
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Regenerar ID da sessão para evitar fixação
if (!isset($_SESSION['installer_started'])) {
    session_regenerate_id(true);
    $_SESSION['installer_started'] = true;
}

// Incluir arquivos necessários com verificação
$required_files = [
    'includes/functions.php',
    'includes/requirements.php',
    'includes/database.php'
];

foreach ($required_files as $file) {
    $file_path = INSTALL_PATH . '/' . $file;
    if (!file_exists($file_path)) {
        die("Erro: Arquivo necessário não encontrado: {$file}");
    }
    require_once $file_path;
}

// Verificar se o sistema já está instalado
if (file_exists(ROOT_PATH . '/config.php') && !isset($_GET['force'])) {
    // Verificar se o config.php tem conteúdo válido
    $config_content = @file_get_contents(ROOT_PATH . '/config.php');
    if (strlen($config_content) > 100 && strpos($config_content, 'DB_HOST') !== false) {
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Sistema já instalado</title>
            <!-- Bootstrap CSS -->
            <link rel="stylesheet" href="assets/css/bootstrap.min.css">
            <!-- Custom CSS -->
            <link rel="stylesheet" href="assets/css/installer.css">
            <link rel="stylesheet" href="assets/css/step3-tables.css">
            <!-- Font Awesome -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        </head>
        <body>
            <div class="container">
                <div class="alert alert-warning mt-5">
                    <h2>⚠️ Sistema já instalado</h2>
                    <p>O sistema já foi instalado anteriormente.</p>
                    <p>Por segurança, remova a pasta <code>/install</code> do servidor.</p>
                    <hr>
                    <p><a href="../" class="btn btn-primary">Ir para o Sistema</a></p>
                    <p><small>Para reinstalar, remova o arquivo config.php ou <a href="?force=1">force a reinstalação</a></small></p>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Determinar etapa atual
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$step = max(1, min(5, $step)); // Limitar entre 1 e 5

// Processar formulários POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Erro de segurança: Token CSRF inválido');
    }
    
    // Incluir processador com verificação
    $processor_file = INSTALL_PATH . '/includes/process.php';
    if (file_exists($processor_file)) {
        require_once $processor_file;
        
        // Processar baseado na etapa
        $process_function = 'process_step_' . $step;
        if (function_exists($process_function)) {
            $result = $process_function($_POST);
            
            if ($result['success']) {
                // Avançar para próxima etapa
                header('Location: index.php?step=' . ($step + 1));
                exit;
            } else {
                // Armazenar erro para exibir
                $_SESSION['error'] = $result['message'];
            }
        }
    }
}

// Array com informações das etapas
$steps_info = [
    1 => [
        'title' => 'Verificação de Requisitos',
        'description' => 'Verificando se o servidor atende aos requisitos mínimos',
        'file' => 'steps/step1_requirements.php'
    ],
    2 => [
        'title' => 'Configuração do Banco de Dados',
        'description' => 'Configure a conexão com o banco de dados MySQL',
        'file' => 'steps/step2_database.php'
    ],
    3 => [
        'title' => 'Criação das Tabelas',
        'description' => 'Criando estrutura do banco de dados',
        'file' => 'steps/step3_tables.php'
    ],
    4 => [
        'title' => 'Configuração do Administrador',
        'description' => 'Configure a conta de administrador do sistema',
        'file' => 'steps/step4_admin.php'
    ],
    5 => [
        'title' => 'Instalação Concluída',
        'description' => 'Sistema instalado com sucesso',
        'file' => 'steps/step5_complete.php'
    ]
];

// Verificar se o arquivo da etapa existe
$step_file = INSTALL_PATH . '/' . $steps_info[$step]['file'];
if (!file_exists($step_file)) {
    die("Erro: Arquivo da etapa {$step} não encontrado: {$steps_info[$step]['file']}");
}
?>

<!-- Step-specific CSS -->
<?php if ($step == 3): ?>
    <link rel="stylesheet" href="assets/css/step3-tables.css">
<?php endif; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Instalação - <?php echo htmlspecialchars($steps_info[$step]['title']); ?></title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/installer.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
</head>
<body>
    <div class="installer-wrapper">
        <!-- Header -->
        <header class="installer-header">
            <div class="container">
                <div class="header-content">
                    <img src="assets/images/logo.png" alt="GameDev Academy" class="installer-logo">
                    <h1>GameDev Academy</h1>
                    <p>Assistente de Instalação v2.0</p>
                </div>
            </div>
        </header>

        <!-- Progress Bar -->
        <div class="progress-wrapper">
            <div class="container">
                <div class="progress installer-progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" 
                         style="width: <?php echo ($step * 20); ?>%;" 
                         aria-valuenow="<?php echo $step; ?>" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                        Etapa <?php echo $step; ?> de 5
                    </div>
                </div>
                
                <!-- Steps Navigation -->
                <div class="steps-nav">
                    <?php foreach ($steps_info as $num => $info): ?>
                        <div class="step-item <?php echo $num == $step ? 'active' : ($num < $step ? 'completed' : ''); ?>">
                            <span class="step-number"><?php echo $num; ?></span>
                            <span class="step-name"><?php echo htmlspecialchars($info['title']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="installer-content">
            <div class="container">
                <div class="card installer-card">
                    <div class="card-header">
                        <h2><?php echo htmlspecialchars($steps_info[$step]['title']); ?></h2>
                        <p class="mb-0"><?php echo htmlspecialchars($steps_info[$step]['description']); ?></p>
                    </div>
                    
                    <div class="card-body">
                        <?php
                        // Exibir mensagens de erro se houver
                        if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php 
                                echo htmlspecialchars($_SESSION['error']); 
                                unset($_SESSION['error']);
                                ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>
                        
                        <?php
                        // Exibir mensagens de sucesso se houver
                        if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i>
                                <?php 
                                echo htmlspecialchars($_SESSION['success']); 
                                unset($_SESSION['success']);
                                ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>
                        
                        <?php
                        // Incluir arquivo da etapa
                        include $step_file;
                        ?>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="installer-footer">
            <div class="container">
                <div class="footer-content">
                    <p>&copy; <?php echo date('Y'); ?> GameDev Academy. Todos os direitos reservados.</p>
                    <p class="footer-info">
                        <small>
                            PHP <?php echo PHP_VERSION; ?> | 
                            MySQL <?php echo isset($_SESSION['mysql_version']) ? $_SESSION['mysql_version'] : 'N/A'; ?> | 
                            Servidor: <?php echo $_SERVER['SERVER_SOFTWARE']; ?>
                        </small>
                    </p>
                </div>
            </div>
        </footer>
    </div>

    <!-- jQuery -->
    <script src="assets/js/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap Bundle JS (includes Popper) -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Installer JS -->
    <script src="assets/js/installer.js"></script>
    
    <!-- Step-specific JS -->
    <?php if ($step == 2): ?>
        <script src="assets/js/database.js"></script>
    <?php elseif ($step == 3): ?>
        <script src="assets/js/tables-installer.js"></script>
    <?php elseif ($step == 4): ?>
        <script src="assets/js/admin.js"></script>
    <?php endif; ?>
    
    <script>
    // Proteção contra resubmissão de formulário
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
    </script>
</body>
</html>
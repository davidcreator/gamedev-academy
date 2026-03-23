<?php
/**
 * GameDev Academy - Sistema de Instalacao
 * Arquivo principal do instalador
 * @version 2.0
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

define('INSTALLER', true);
define('INSTALL_PATH', __DIR__);
define('ROOT_PATH', dirname(__DIR__));

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'use_strict_mode' => true
    ]);
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['installer_started'])) {
    session_regenerate_id(true);
    $_SESSION['installer_started'] = true;
}

$required_files = [
    '../includes/install-state.php',
    'includes/functions.php',
    'includes/requirements.php',
    'includes/database.php'
];

foreach ($required_files as $file) {
    $file_path = INSTALL_PATH . '/' . $file;
    if (!file_exists($file_path)) {
        die("Erro: Arquivo necessario nao encontrado: {$file}");
    }
    require_once $file_path;
}

if (!isset($_GET['force']) && gamedevHasValidInstallConfig()) {
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sistema ja instalado</title>
        <link rel="stylesheet" href="assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="assets/css/installer.css">
        <link rel="stylesheet" href="assets/css/step3-tables.css">
        <link rel="stylesheet" href="assets/css/step4-admin.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>
    <body>
        <div class="container">
            <div class="alert alert-warning mt-5">
                <h2>Sistema ja instalado</h2>
                <p>O sistema ja foi instalado anteriormente.</p>
                <p>Por seguranca, remova a pasta <code>/install</code> do servidor.</p>
                <hr>
                <p><a href="../" class="btn btn-primary">Ir para o Sistema</a></p>
                <p><small>Para reinstalar, remova ou limpe o arquivo config.php da raiz, ou <a href="?force=1">force a reinstalacao</a></small></p>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$step = max(1, min(5, $step));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Erro de seguranca: Token CSRF invalido');
    }

    $processor_file = INSTALL_PATH . '/includes/process.php';
    if (file_exists($processor_file)) {
        require_once $processor_file;

        $process_function = 'process_step_' . $step;
        if (function_exists($process_function)) {
            $result = $process_function($_POST);

            if ($result['success']) {
                header('Location: index.php?step=' . ($step + 1));
                exit;
            }

            $_SESSION['error'] = $result['message'];
        }
    }
}

$steps_info = [
    1 => [
        'title' => 'Verificacao de Requisitos',
        'description' => 'Verificando se o servidor atende aos requisitos minimos',
        'file' => 'steps/step1_requirements.php'
    ],
    2 => [
        'title' => 'Configuracao do Banco de Dados',
        'description' => 'Configure a conexao com o banco de dados MySQL',
        'file' => 'steps/step2_database.php'
    ],
    3 => [
        'title' => 'Criacao das Tabelas',
        'description' => 'Criando estrutura do banco de dados',
        'file' => 'steps/step3_tables.php'
    ],
    4 => [
        'title' => 'Configuracao do Administrador',
        'description' => 'Configure a conta de administrador do sistema',
        'file' => 'steps/step4_admin.php'
    ],
    5 => [
        'title' => 'Instalacao Concluida',
        'description' => 'Sistema instalado com sucesso',
        'file' => 'steps/step5.php'
    ]
];

$step_file = INSTALL_PATH . '/' . $steps_info[$step]['file'];
if (!file_exists($step_file)) {
    die("Erro: Arquivo da etapa {$step} nao encontrado: {$steps_info[$step]['file']}");
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Instalacao - <?php echo htmlspecialchars($steps_info[$step]['title']); ?></title>

    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/installer.css">
    <?php if ($step == 3): ?>
        <link rel="stylesheet" href="assets/css/step3-tables.css">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
</head>
<body>
    <div class="installer-wrapper">
        <header class="installer-header">
            <div class="container">
                <div class="header-content">
                    <img src="assets/images/logo.png" alt="GameDev Academy" class="installer-logo">
                    <h1>GameDev Academy</h1>
                    <p>Assistente de Instalacao v2.0</p>
                </div>
            </div>
        </header>

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

        <main class="installer-content">
            <div class="container">
                <div class="card installer-card">
                    <div class="card-header">
                        <h2><?php echo htmlspecialchars($steps_info[$step]['title']); ?></h2>
                        <p class="mb-0"><?php echo htmlspecialchars($steps_info[$step]['description']); ?></p>
                    </div>

                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
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

                        <?php if (isset($_SESSION['success'])): ?>
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

                        <?php include $step_file; ?>
                    </div>
                </div>
            </div>
        </main>

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

    <script src="assets/js/install.js"></script>

    <?php if ($step == 2): ?>
        <script src="assets/js/database.js"></script>
    <?php elseif ($step == 3): ?>
        <script src="assets/js/tables-installer.js"></script>
    <?php elseif ($step == 4): ?>
        <script src="assets/js/admin.js"></script>
    <?php endif; ?>

    <script>
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
    </script>
</body>
</html>

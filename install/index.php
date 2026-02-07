<?php
/**
 * GameDev Academy - Instalador
 * 
 * @package    GameDevAcademy
 * @version    2.0.0
 * @author     David Creator
 * @license    MIT
 */

// Definir constantes
define('GDA_INSTALL', true);
define('GDA_VERSION', '2.0.0');
define('GDA_MIN_PHP', '7.4.0');
define('GDA_ROOT', dirname(__DIR__));

// Configurações de segurança
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Prevenir execução múltipla
$lockFile = GDA_ROOT . '/storage/installed.lock';
if (file_exists($lockFile)) {
    die('
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <title>Instalação Concluída</title>
            <style>
                body { font-family: system-ui; max-width: 600px; margin: 100px auto; text-align: center; }
                .warning { background: #fff3cd; border: 2px solid #ffc107; padding: 2rem; border-radius: 8px; }
            </style>
        </head>
        <body>
            <div class="warning">
                <h1>⚠️ Instalação já concluída</h1>
                <p>O sistema já foi instalado. Por segurança, remova a pasta <code>/install</code>.</p>
                <p><a href="../">Ir para página inicial</a></p>
            </div>
        </body>
        </html>
    ');
}

// Incluir dependências
require_once 'functions.php';

// Iniciar sessão segura
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true
]);

// CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Processar instalação
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$errors = [];
$success = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('Token CSRF inválido');
    }
    
    switch ($step) {
        case 1:
            // Não bloquear avanço por requisitos; apenas coletar erros (se houver)
            $errors = validateRequirements();
            $step = 2;
            $success[] = 'Prosseguindo para configuração do banco.';
            break;
        case 2:
            $errors = validateRequirements();
            if (empty($errors)) {
                $step = 3;
                $success[] = 'Requisitos verificados com sucesso!';
            }
            break;
            
        case 3:
            $result = setupDatabase($_POST);
            if ($result['success']) {
                $step = 4;
                $_SESSION['db_config'] = $result['config'];
                $success[] = 'Banco de dados configurado com sucesso!';
            } else {
                $errors = $result['errors'];
            }
            break;
            
        case 4:
            $result = createAdminUser($_POST, $_SESSION['db_config']);
            if ($result['success']) {
                $step = 5;
                $success[] = 'Administrador criado com sucesso!';
            } else {
                $errors = $result['errors'];
            }
            break;
            
        case 5:
            $result = finalizeInstallation($_SESSION['db_config'], $_POST);
            if ($result['success']) {
                // Criar arquivo de lock
                @mkdir(GDA_ROOT . '/storage', 0755, true);
                file_put_contents($lockFile, date('Y-m-d H:i:s'));
                
                // Limpar sessão
                session_destroy();
                
                // Redirecionar
                header('Location: ../admin/login.php');
                exit;
            } else {
                $errors = $result['errors'];
            }
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Instalação - GameDev Academy</title>
    <link rel="stylesheet" href="assets/install.css">
</head>
<body>
    <div class="installer">
        <header class="installer-header">
            <div class="logo">
                <svg width="48" height="48" viewBox="0 0 48 48">
                    <rect width="48" height="48" rx="8" fill="url(#gradient)"/>
                    <path d="M24 12l8 8-8 8-8-8z" fill="white"/>
                    <defs>
                        <linearGradient id="gradient">
                            <stop offset="0%" stop-color="#667eea"/>
                            <stop offset="100%" stop-color="#764ba2"/>
                        </linearGradient>
                    </defs>
                </svg>
                <h1>GameDev Academy</h1>
            </div>
            <div class="version">v<?= GDA_VERSION ?></div>
        </header>

        <div class="progress-bar">
            <div class="progress-step <?= $step >= 1 ? 'active' : '' ?>">
                <div class="step-number">1</div>
                <div class="step-label">Bem-vindo</div>
            </div>
            <div class="progress-step <?= $step >= 2 ? 'active' : '' ?>">
                <div class="step-number">2</div>
                <div class="step-label">Requisitos</div>
            </div>
            <div class="progress-step <?= $step >= 3 ? 'active' : '' ?>">
                <div class="step-number">3</div>
                <div class="step-label">Banco de Dados</div>
            </div>
            <div class="progress-step <?= $step >= 4 ? 'active' : '' ?>">
                <div class="step-number">4</div>
                <div class="step-label">Admin</div>
            </div>
            <div class="progress-step <?= $step >= 5 ? 'active' : '' ?>">
                <div class="step-number">5</div>
                <div class="step-label">Finalizar</div>
            </div>
        </div>

        <main class="installer-content">
            <?php $config = $_SESSION['install_config'] ?? []; ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <strong>❌ Erros encontrados:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <strong>✅ Sucesso:</strong>
                    <ul>
                        <?php foreach ($success as $msg): ?>
                            <li><?= htmlspecialchars($msg) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="index.php?step=<?= (int)$step ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <?php if ($step === 5): ?>
                    <input type="hidden" name="app_name" value="<?= htmlspecialchars($config['app_name'] ?? 'GameDev Academy') ?>">
                    <input type="hidden" name="app_url" value="<?= htmlspecialchars($config['app_url'] ?? '') ?>">
                    <input type="hidden" name="timezone" value="<?= htmlspecialchars($config['timezone'] ?? 'America/Sao_Paulo') ?>">
                <?php endif; ?>
                <?php
                switch ($step) {
                    case 1:
                        include 'steps/step1.php';
                        break;
                    case 2:
                        include 'steps/step2.php';
                        break;
                    case 3:
                        include 'steps/step3.php';
                        break;
                    case 4:
                        include 'steps/step4.php';
                        break;
                    case 5:
                        include 'steps/step5.php';
                        break;
                }
                ?>
                <div style="display:flex; justify-content: space-between; gap: 1rem; margin-top: 2rem;">
                    <a class="btn btn-secondary" href="index.php?step=<?= max(1, $step - 1) ?>" <?= $step === 1 ? 'style="pointer-events:none;opacity:0.5;"' : '' ?>>
                        Voltar
                    </a>
                    <?php if ($step < 5): ?>
                        <button type="submit" id="next-btn" class="btn btn-primary">
                            Continuar
                        </button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-success">
                            Finalizar Instalação
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </main>

        <footer class="installer-footer">
            <p>&copy; <?= date('Y') ?> GameDev Academy. Todos os direitos reservados.</p>
            <p>
                <a href="https://github.com/davidcreator/gamedev-academy" target="_blank">GitHub</a> |
                <a href="https://gamedevacademy.com.br/docs" target="_blank">Documentação</a>
            </p>
        </footer>
    </div>

    <!-- Script opcional removido: assets/install.js não encontrado -->
</body>
</html>

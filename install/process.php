<?php
// install/process.php

$step = intval($_POST['step'] ?? 1);
$errors = [];
$warnings = [];

// Função auxiliar para testar conexão
function testDatabaseConnection($host, $user, $pass, $dbname = null, $port = '3306') {
    try {
        if ($dbname) {
            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
        } else {
            $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
        }
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return $pdo;
    } catch (PDOException $e) {
        return false;
    }
}

switch ($step) {
    case 1:
        // Verificar requisitos
        if (!($_SESSION['requirements_passed'] ?? false)) {
            $errors[] = 'Por favor, resolva todos os requisitos antes de continuar.';
        }
        break;
        
    case 2:
        // Configuração do banco de dados
        $config = [
            'db_host' => trim($_POST['db_host'] ?? 'localhost'),
            'db_port' => trim($_POST['db_port'] ?? '3306'),
            'db_name' => trim($_POST['db_name'] ?? ''),
            'db_user' => trim($_POST['db_user'] ?? ''),
            'db_pass' => $_POST['db_pass'] ?? '',
            'db_charset' => $_POST['db_charset'] ?? 'utf8mb4'
        ];
        
        // Validações
        if (empty($config['db_name'])) {
            $errors[] = 'O nome do banco de dados é obrigatório.';
        }
        
        if (empty($config['db_user'])) {
            $errors[] = 'O usuário do banco de dados é obrigatório.';
        }
        
        if (empty($errors) && isset($_POST['test_connection'])) {
            // Primeiro testa conexão sem especificar banco
            $pdo = testDatabaseConnection($config['db_host'], $config['db_user'], $config['db_pass'], null, $config['db_port']);
            
            if ($pdo === false) {
                $errors[] = 'Não foi possível conectar ao servidor MySQL. Verifique host, porta, usuário e senha.';
            } else {
                try {
                    // Criar banco se não existir
                    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['db_name']}` CHARACTER SET {$config['db_charset']} COLLATE {$config['db_charset']}_unicode_ci");
                    
                    // Testar conexão com o banco criado
                    $pdo = testDatabaseConnection($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name'], $config['db_port']);
                    
                    if ($pdo === false) {
                        $errors[] = 'Banco criado mas não foi possível conectar. Verifique as permissões.';
                    } else {
                        $_SESSION['db_connection_tested'] = true;
                    }
                } catch (PDOException $e) {
                    $errors[] = 'Erro ao criar banco de dados: ' . $e->getMessage();
                }
            }
        }
        
        $_SESSION['install_config'] = array_merge($_SESSION['install_config'] ?? [], $config);
        break;
        
    case 3:
        // Configuração do site
        $config = [
            'app_name' => trim($_POST['app_name'] ?? ''),
            'app_url' => rtrim(trim($_POST['app_url'] ?? ''), '/'),
            'app_env' => $_POST['app_env'] ?? 'local',
            'app_debug' => $_POST['app_debug'] ?? 'true',
            'mail_from' => trim($_POST['mail_from'] ?? ''),
            'mail_from_name' => trim($_POST['mail_from_name'] ?? ''),
            'timezone' => $_POST['timezone'] ?? 'America/Sao_Paulo',
            'xp_lesson' => intval($_POST['xp_lesson'] ?? 10),
            'xp_course' => intval($_POST['xp_course'] ?? 100)
        ];
        
        if (empty($config['app_name'])) {
            $errors[] = 'O nome do site é obrigatório.';
        }
        
        if (empty($config['app_url'])) {
            $errors[] = 'A URL do site é obrigatória.';
        }
        
        if (!filter_var($config['app_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'A URL do site não é válida.';
        }
        
        $_SESSION['install_config'] = array_merge($_SESSION['install_config'] ?? [], $config);
        break;
        
    case 4:
        // Criar conta do admin e executar instalação
        $config = [
            'admin_name' => trim($_POST['admin_name'] ?? ''),
            'admin_username' => trim($_POST['admin_username'] ?? ''),
            'admin_email' => trim($_POST['admin_email'] ?? ''),
            'admin_password' => $_POST['admin_password'] ?? ''
        ];
        
        // Validações
        if (empty($config['admin_name'])) {
            $errors[] = 'O nome completo é obrigatório.';
        }
        
        if (empty($config['admin_username'])) {
            $errors[] = 'O nome de usuário é obrigatório.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $config['admin_username'])) {
            $errors[] = 'Nome de usuário inválido. Use 3-20 caracteres alfanuméricos.';
        }
        
        if (empty($config['admin_email'])) {
            $errors[] = 'O e-mail é obrigatório.';
        } elseif (!filter_var($config['admin_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'E-mail inválido.';
        }
        
        if (empty($config['admin_password'])) {
            $errors[] = 'A senha é obrigatória.';
        } elseif (strlen($config['admin_password']) < 8) {
            $errors[] = 'A senha deve ter pelo menos 8 caracteres.';
        }
        
        if ($config['admin_password'] !== ($_POST['admin_password_confirm'] ?? '')) {
            $errors[] = 'As senhas não coincidem.';
        }
        
        $_SESSION['install_config'] = array_merge($_SESSION['install_config'] ?? [], $config);
        
        // Se não houver erros, executar a instalação
        if (empty($errors)) {
            $allConfig = $_SESSION['install_config'];
            
            try {
                // Conectar ao banco
                $pdo = new PDO(
                    "mysql:host={$allConfig['db_host']};port={$allConfig['db_port']};dbname={$allConfig['db_name']};charset={$allConfig['db_charset']}",
                    $allConfig['db_user'],
                    $allConfig['db_pass'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                // Executar SQL de criação das tabelas
                $sqlFile = __DIR__ . '/../database/schema.sql';
                if (file_exists($sqlFile)) {
                    $sql = file_get_contents($sqlFile);
                    // Dividir por ponto e vírgula e executar cada query
                    $queries = array_filter(array_map('trim', explode(';', $sql)));
                    foreach ($queries as $query) {
                        if (!empty($query)) {
                            $pdo->exec($query);
                        }
                    }
                } else {
                    // SQL inline se o arquivo não existir
                    require __DIR__ . '/sql/create_tables.php';
                    executeDatabaseSetup($pdo);
                }
                
                // Inserir dados iniciais
                $passwordHash = password_hash($allConfig['admin_password'], PASSWORD_DEFAULT);
                
                // Inserir admin
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role, is_active, email_verified) VALUES (?, ?, ?, ?, 'admin', 1, 1)");
                $stmt->execute([
                    $allConfig['admin_username'],
                    $allConfig['admin_email'],
                    $passwordHash,
                    $allConfig['admin_name']
                ]);
                
                // Gerar chaves de segurança
                $appKey = bin2hex(random_bytes(32));
                $jwtSecret = bin2hex(random_bytes(32));
                
                // Criar arquivo .env
                $envContent = "# GameDev Academy Configuration
# Generated at " . date('Y-m-d H:i:s') . "

#===========================================
# APPLICATION
#===========================================
APP_NAME=\"{$allConfig['app_name']}\"
APP_ENV={$allConfig['app_env']}
APP_DEBUG={$allConfig['app_debug']}
APP_URL={$allConfig['app_url']}
APP_KEY={$appKey}

#===========================================
# DATABASE
#===========================================
DB_HOST={$allConfig['db_host']}
DB_PORT={$allConfig['db_port']}
DB_DATABASE={$allConfig['db_name']}
DB_USERNAME={$allConfig['db_user']}
DB_PASSWORD={$allConfig['db_pass']}
DB_CHARSET={$allConfig['db_charset']}

#===========================================
# SESSION
#===========================================
SESSION_DRIVER=file
SESSION_LIFETIME=120

#===========================================
# MAIL
#===========================================
MAIL_FROM_ADDRESS={$allConfig['mail_from']}
MAIL_FROM_NAME=\"{$allConfig['mail_from_name']}\"

#===========================================
# SECURITY
#===========================================
JWT_SECRET={$jwtSecret}

#===========================================
# GAMIFICATION
#===========================================
XP_LESSON_COMPLETE={$allConfig['xp_lesson']}
XP_COURSE_COMPLETE={$allConfig['xp_course']}

#===========================================
# TIMEZONE
#===========================================
TIMEZONE={$allConfig['timezone']}
";
                
                file_put_contents(__DIR__ . '/../.env', $envContent);
                
                // Criar arquivo de lock
                file_put_contents(__DIR__ . '/../storage/installed.lock', date('Y-m-d H:i:s'));
                
                // Limpar sessão de instalação (mantém apenas config para mostrar no step 5)
                unset($_SESSION['requirements_passed']);
                unset($_SESSION['db_connection_tested']);
                
            } catch (Exception $e) {
                $errors[] = 'Erro durante a instalação: ' . $e->getMessage();
            }
        }
        break;
}

// Salvar erros e avisos na sessão
if (!empty($errors)) {
    $_SESSION['install_errors'] = $errors;
    header("Location: index.php?step={$step}");
    exit;
}

if (!empty($warnings)) {
    $_SESSION['install_warnings'] = $warnings;
}

// Avançar para o próximo passo
$nextStep = $step + 1;
header("Location: index.php?step={$nextStep}");
exit;
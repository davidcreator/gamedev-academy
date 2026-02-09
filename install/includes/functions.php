<?php
/**
 * Funções auxiliares do instalador
 */

if (!defined('INSTALLER')) {
    die('Acesso negado');
}

/**
 * Verifica requisitos do sistema
 */
function check_requirements() {
    $requirements = [
        'php_version' => [
            'required' => '7.4.0',
            'current' => PHP_VERSION,
            'passed' => version_compare(PHP_VERSION, '7.4.0', '>='),
            'name' => 'Versão do PHP'
        ],
        'mysqli' => [
            'required' => 'Habilitado',
            'current' => extension_loaded('mysqli') ? 'Habilitado' : 'Desabilitado',
            'passed' => extension_loaded('mysqli'),
            'name' => 'Extensão MySQLi'
        ],
        'pdo' => [
            'required' => 'Habilitado',
            'current' => extension_loaded('pdo') ? 'Habilitado' : 'Desabilitado',
            'passed' => extension_loaded('pdo'),
            'name' => 'Extensão PDO'
        ],
        'json' => [
            'required' => 'Habilitado',
            'current' => function_exists('json_encode') ? 'Habilitado' : 'Desabilitado',
            'passed' => function_exists('json_encode'),
            'name' => 'Suporte JSON'
        ],
        'session' => [
            'required' => 'Habilitado',
            'current' => function_exists('session_start') ? 'Habilitado' : 'Desabilitado',
            'passed' => function_exists('session_start'),
            'name' => 'Suporte a Sessões'
        ],
        'writable_config' => [
            'required' => 'Gravável',
            'current' => is_writable(ROOT_PATH) ? 'Gravável' : 'Somente leitura',
            'passed' => is_writable(ROOT_PATH),
            'name' => 'Pasta raiz (para config.php)'
        ]
    ];
    
    return $requirements;
}

/**
 * Testa conexão com banco de dados
 */
function test_database_connection($host, $user, $pass, $name = null, $port = 3306) {
    try {
        $mysqli = @new mysqli($host, $user, $pass, $name, $port);
        
        if ($mysqli->connect_error) {
            return [
                'success' => false,
                'message' => 'Erro de conexão: ' . $mysqli->connect_error
            ];
        }
        
        // Definir charset
        $mysqli->set_charset('utf8mb4');
        
        // Testar se o banco existe (se nome fornecido)
        if ($name) {
            $result = $mysqli->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$name}'");
            if ($result->num_rows == 0) {
                // Tentar criar o banco
                if ($mysqli->query("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
                    $mysqli->select_db($name);
                } else {
                    return [
                        'success' => false,
                        'message' => 'Banco de dados não existe e não foi possível criar'
                    ];
                }
            }
        }
        
        $mysqli->close();
        
        return [
            'success' => true,
            'message' => 'Conexão estabelecida com sucesso'
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Erro: ' . $e->getMessage()
        ];
    }
}

/**
 * Cria arquivo de configuração
 */
function create_config_file($data) {
    $config_template = <<<'CONFIG'
<?php
/**
 * Arquivo de configuração do sistema
 * Gerado automaticamente pelo instalador
 * Data: %s
 */

// Configurações do Banco de Dados
define('DB_HOST', '%s');
define('DB_USER', '%s');
define('DB_PASS', '%s');
define('DB_NAME', '%s');
define('DB_PORT', %d);
define('DB_PREFIX', '%s');
define('DB_CHARSET', 'utf8mb4');

// Configurações do Sistema
define('SITE_URL', '%s');
define('SITE_NAME', '%s');
define('SITE_EMAIL', '%s');
define('TIMEZONE', '%s');

// Configurações de Segurança
define('SECURITY_SALT', '%s');
define('SESSION_NAME', 'gamedev_session');
define('COOKIE_SECURE', %s);
define('COOKIE_HTTPONLY', true);

// Configurações de Debug (desativar em produção)
define('DEBUG_MODE', false);
define('LOG_ERRORS', true);
define('ERROR_LOG_FILE', __DIR__ . '/logs/error.log');

// Configurações de Upload
define('UPLOAD_PATH', __DIR__ . '/uploads/');
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'zip']);

// Configurações de Cache
define('CACHE_ENABLED', true);
define('CACHE_TIME', 3600); // 1 hora

// Versão do Sistema
define('SYSTEM_VERSION', '2.0.0');
define('INSTALL_DATE', '%s');

// Prevenir acesso direto
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

// Autoload e inicialização
require_once ROOT_PATH . '/includes/init.php';
CONFIG;

    $config_content = sprintf(
        $config_template,
        date('Y-m-d H:i:s'),
        addslashes($data['db_host']),
        addslashes($data['db_user']),
        addslashes($data['db_pass']),
        addslashes($data['db_name']),
        (int)($data['db_port'] ?? 3306),
        addslashes($data['db_prefix'] ?? ''),
        addslashes($data['site_url'] ?? ''),
        addslashes($data['site_name'] ?? 'GameDev Academy'),
        addslashes($data['admin_email'] ?? ''),
        date_default_timezone_get(),
        bin2hex(random_bytes(32)),
        isset($_SERVER['HTTPS']) ? 'true' : 'false',
        date('Y-m-d H:i:s')
    );
    
    $config_file = ROOT_PATH . '/config.php';
    
    // Tentar gravar o arquivo
    if (@file_put_contents($config_file, $config_content) === false) {
        return [
            'success' => false,
            'message' => 'Não foi possível criar o arquivo config.php. Verifique as permissões.',
            'content' => $config_content
        ];
    }
    
    // Tentar ajustar permissões
    @chmod($config_file, 0644);
    
    return [
        'success' => true,
        'message' => 'Arquivo de configuração criado com sucesso'
    ];
}

/**
 * Executa arquivo SQL
 */
function execute_sql_file($mysqli, $file_path, $prefix = '') {
    if (!file_exists($file_path)) {
        return [
            'success' => false,
            'message' => 'Arquivo SQL não encontrado: ' . $file_path
        ];
    }
    
    $sql = file_get_contents($file_path);
    
    // Substituir prefixo se fornecido
    if ($prefix) {
        $sql = str_replace('prefix_', $prefix, $sql);
    }
    
    // Remover comentários
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    
    // Dividir em comandos individuais
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    $errors = [];
    $success_count = 0;
    
    foreach ($queries as $query) {
        if (empty($query)) continue;
        
        if ($mysqli->query($query)) {
            $success_count++;
        } else {
            $errors[] = $mysqli->error;
        }
    }
    
    if (count($errors) > 0) {
        return [
            'success' => false,
            'message' => 'Erros durante execução SQL',
            'errors' => $errors,
            'executed' => $success_count
        ];
    }
    
    return [
        'success' => true,
        'message' => "{$success_count} queries executadas com sucesso"
    ];
}

/**
 * Remove pasta recursivamente
 */
function remove_directory($dir) {
    if (!is_dir($dir)) return false;
    
    $files = array_diff(scandir($dir), ['.', '..']);
    
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? remove_directory($path) : unlink($path);
    }
    
    return rmdir($dir);
}

/**
 * Gera senha aleatória segura
 */
function generate_password($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $password = '';
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    
    return $password;
}

/**
 * Valida email
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Sanitiza entrada
 */
function sanitize_input($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}
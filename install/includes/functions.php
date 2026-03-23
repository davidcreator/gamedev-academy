<?php
/**
 * Funcoes auxiliares do instalador.
 */

if (!defined('INSTALLER')) {
    die('Acesso negado');
}

/**
 * Verifica requisitos do sistema.
 */
function check_requirements() {
    $requirements = [
        'php_version' => [
            'required' => '7.4.0',
            'current' => PHP_VERSION,
            'passed' => version_compare(PHP_VERSION, '7.4.0', '>='),
            'name' => 'Versao do PHP'
        ],
        'mysqli' => [
            'required' => 'Habilitado',
            'current' => extension_loaded('mysqli') ? 'Habilitado' : 'Desabilitado',
            'passed' => extension_loaded('mysqli'),
            'name' => 'Extensao MySQLi'
        ],
        'pdo' => [
            'required' => 'Habilitado',
            'current' => extension_loaded('pdo') ? 'Habilitado' : 'Desabilitado',
            'passed' => extension_loaded('pdo'),
            'name' => 'Extensao PDO'
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
            'name' => 'Suporte a Sessoes'
        ],
        'writable_config' => [
            'required' => 'Gravavel',
            'current' => is_writable(ROOT_PATH) ? 'Gravavel' : 'Somente leitura',
            'passed' => is_writable(ROOT_PATH),
            'name' => 'Pasta raiz (para config.php)'
        ]
    ];

    return $requirements;
}

/**
 * Testa conexao com banco de dados.
 */
function test_database_connection($host, $user, $pass, $name = null, $port = 3306) {
    try {
        $mysqli = @new mysqli($host, $user, $pass, $name, $port);

        if ($mysqli->connect_error) {
            return [
                'success' => false,
                'message' => 'Erro de conexao: ' . $mysqli->connect_error
            ];
        }

        $mysqli->set_charset('utf8mb4');

        if ($name) {
            $result = $mysqli->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$name}'");
            if ($result->num_rows == 0) {
                if ($mysqli->query("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
                    $mysqli->select_db($name);
                } else {
                    return [
                        'success' => false,
                        'message' => 'Banco de dados nao existe e nao foi possivel criar'
                    ];
                }
            }
        }

        $mysqli->close();

        return [
            'success' => true,
            'message' => 'Conexao estabelecida com sucesso'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Erro: ' . $e->getMessage()
        ];
    }
}

/**
 * Cria arquivo de configuracao.
 */
function create_config_file($data) {
    $dbHost = $data['db_host'] ?? $data['host'] ?? 'localhost';
    $dbUser = $data['db_user'] ?? $data['user'] ?? 'root';
    $dbPass = $data['db_pass'] ?? $data['pass'] ?? '';
    $dbName = $data['db_name'] ?? $data['name'] ?? '';
    $dbPort = (int)($data['db_port'] ?? $data['port'] ?? 3306);
    $dbPrefix = $data['db_prefix'] ?? $data['prefix'] ?? '';
    $siteUrl = $data['site_url'] ?? '';
    $siteName = $data['site_name'] ?? 'GameDev Academy';
    $siteEmail = $data['site_email'] ?? $data['admin_email'] ?? '';
    $timezone = $data['timezone'] ?? 'America/Sao_Paulo';
    $debugMode = !empty($data['debug_mode']) || !empty($data['enable_debug']);

    $configTemplate = <<<'CONFIG'
<?php
/**
 * Arquivo de configuracao do sistema
 * Gerado automaticamente pelo instalador
 * Data: %s
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', rtrim(__DIR__, '/\\') . DIRECTORY_SEPARATOR);
}

if (!defined('BASE_PATH')) {
    define('BASE_PATH', ROOT_PATH);
}

// Configuracoes do Banco de Dados
define('DB_HOST', '%s');
define('DB_USER', '%s');
define('DB_PASS', '%s');
define('DB_NAME', '%s');
define('DB_PORT', %d);
define('DB_PREFIX', '%s');
define('DB_CHARSET', 'utf8mb4');

// Configuracoes do Sistema
define('SITE_URL', '%s');
define('SITE_NAME', '%s');
define('SITE_EMAIL', '%s');
define('TIMEZONE', '%s');
define('BASE_URL', rtrim(SITE_URL, '/') . '/');
define('ASSETS_URL', BASE_URL . 'assets/');
define('UPLOADS_URL', BASE_URL . 'uploads/');
define('ADMIN_URL', BASE_URL . 'admin/');
define('USER_URL', BASE_URL . 'user/');
define('INSTALL_URL', BASE_URL . 'install/');

// Configuracoes de Seguranca
define('SECURITY_SALT', '%s');
define('SESSION_NAME', 'gamedev_session');
define('COOKIE_SECURE', %s);
define('COOKIE_HTTPONLY', true);

// Configuracoes de Debug
define('DEBUG_MODE', %s);
define('LOG_ERRORS', true);
define('ERROR_LOG_FILE', ROOT_PATH . 'logs/error.log');

// Configuracoes de Upload
define('UPLOAD_PATH', ROOT_PATH . 'uploads/');
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'zip']);

// Configuracoes de Cache
define('CACHE_ENABLED', true);
define('CACHE_TIME', 3600); // 1 hora

// Versao do Sistema
define('SYSTEM_VERSION', '2.0.0');
define('INSTALL_DATE', '%s');
CONFIG;

    $configContent = sprintf(
        $configTemplate,
        date('Y-m-d H:i:s'),
        addslashes($dbHost),
        addslashes($dbUser),
        addslashes($dbPass),
        addslashes($dbName),
        $dbPort,
        addslashes($dbPrefix),
        addslashes($siteUrl),
        addslashes($siteName),
        addslashes($siteEmail),
        addslashes($timezone),
        bin2hex(random_bytes(32)),
        isset($_SERVER['HTTPS']) ? 'true' : 'false',
        $debugMode ? 'true' : 'false',
        date('Y-m-d H:i:s')
    );

    $configFile = ROOT_PATH . '/config.php';

    if (@file_put_contents($configFile, $configContent) === false) {
        return [
            'success' => false,
            'message' => 'Nao foi possivel criar o arquivo config.php. Verifique as permissoes.',
            'content' => $configContent
        ];
    }

    @chmod($configFile, 0644);

    return [
        'success' => true,
        'message' => 'Arquivo de configuracao criado com sucesso'
    ];
}

/**
 * Executa arquivo SQL.
 */
function execute_sql_file($mysqli, $file_path, $prefix = '') {
    if (!file_exists($file_path)) {
        return [
            'success' => false,
            'message' => 'Arquivo SQL nao encontrado: ' . $file_path
        ];
    }

    $sql = file_get_contents($file_path);

    if ($prefix) {
        $sql = str_replace('prefix_', $prefix, $sql);
    }

    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

    $queries = array_filter(array_map('trim', explode(';', $sql)));

    $errors = [];
    $success_count = 0;

    foreach ($queries as $query) {
        if (empty($query)) {
            continue;
        }

        if ($mysqli->query($query)) {
            $success_count++;
        } else {
            $errors[] = $mysqli->error;
        }
    }

    if (count($errors) > 0) {
        return [
            'success' => false,
            'message' => 'Erros durante execucao SQL',
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
 * Remove pasta recursivamente.
 */
function remove_directory($dir) {
    if (!is_dir($dir)) {
        return false;
    }

    $files = array_diff(scandir($dir), ['.', '..']);

    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? remove_directory($path) : unlink($path);
    }

    return rmdir($dir);
}

/**
 * Gera senha aleatoria segura.
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
 * Valida email.
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Sanitiza entrada.
 */
function sanitize_input($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

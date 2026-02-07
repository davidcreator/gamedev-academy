<?php
/**
 * Funções auxiliares do instalador
 */

if (!defined('GDA_INSTALL')) {
    die('Acesso negado');
}

/**
 * Valida requisitos do sistema
 */
function validateRequirements(): array {
    $errors = [];
    
    // PHP Version
    if (version_compare(PHP_VERSION, GDA_MIN_PHP, '<')) {
        $errors[] = sprintf(
            'PHP %s ou superior é necessário. Você está usando %s',
            GDA_MIN_PHP,
            PHP_VERSION
        );
    }
    
    // Extensões necessárias
    $required_extensions = [
        'pdo',
        'pdo_mysql',
        'mbstring',
        'json',
        'curl',
        'gd',
        'fileinfo',
        'zip'
    ];
    
    foreach ($required_extensions as $ext) {
        if (!extension_loaded($ext)) {
            $errors[] = "Extensão PHP '{$ext}' não está instalada";
        }
    }
    
    // Permissões de pastas
    $writable_dirs = [
        GDA_ROOT . '/config',
        GDA_ROOT . '/uploads',
        GDA_ROOT . '/cache',
        GDA_ROOT . '/logs',
        GDA_ROOT . '/storage'
    ];
    
    foreach ($writable_dirs as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        
        if (!is_writable($dir)) {
            $errors[] = "Diretório '{$dir}' não tem permissão de escrita";
        }
    }
    
    // Removido: verificação obrigatória de .htaccess para não bloquear instalação
    
    // Verificar memória
    $memory_limit = ini_get('memory_limit');
    $memory_bytes = return_bytes($memory_limit);
    if ($memory_bytes < 64 * 1024 * 1024) { // 64MB
        $errors[] = "Memória PHP deve ser no mínimo 64MB. Atual: {$memory_limit}";
    }
    
    // Verificar upload
    $max_upload = ini_get('upload_max_filesize');
    $upload_bytes = return_bytes($max_upload);
    if ($upload_bytes < 8 * 1024 * 1024) { // 8MB
        $errors[] = "upload_max_filesize deve ser no mínimo 8MB. Atual: {$max_upload}";
    }
    
    return $errors;
}

/**
 * Converte tamanho para bytes
 */
function return_bytes(string $val): int {
    $val = trim($val);
    $last = strtolower($val[strlen($val) - 1]);
    $val = (int)$val;
    
    switch ($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    
    return $val;
}

/**
 * Configura o banco de dados
 */
function setupDatabase(array $data): array {
    $errors = [];
    
    // Validar dados
    $required = ['db_host', 'db_name', 'db_user', 'db_pass'];
    foreach ($required as $field) {
        if (empty($data[$field]) && $field !== 'db_pass') {
            $errors[] = "Campo '{$field}' é obrigatório";
        }
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    // Sanitizar dados
    $config = [
        'host' => filter_var($data['db_host'], FILTER_SANITIZE_STRING),
        'name' => filter_var($data['db_name'], FILTER_SANITIZE_STRING),
        'user' => filter_var($data['db_user'], FILTER_SANITIZE_STRING),
        'pass' => $data['db_pass'],
        'port' => filter_var($data['db_port'] ?? 3306, FILTER_SANITIZE_NUMBER_INT),
        'charset' => filter_var($data['db_charset'] ?? 'utf8mb4', FILTER_SANITIZE_STRING),
        'prefix' => filter_var($data['db_prefix'] ?? 'gda_', FILTER_SANITIZE_STRING)
    ];
    
    // Testar conexão
    try {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;charset=%s',
            $config['host'],
            $config['port'],
            $config['charset']
        );
        
        $pdo = new PDO(
            $dsn,
            $config['user'],
            $config['pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        
        // Criar banco se não existir
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['name']}` 
                    CHARACTER SET {$config['charset']} 
                    COLLATE {$config['charset']}_unicode_ci");
        
        $pdo->exec("USE `{$config['name']}`");
        
        // Importar schema
        $sql = file_get_contents(__DIR__ . '/database/database.sql');
        
        // Substituir prefixo
        $sql = str_replace('gda_', $config['prefix'], $sql);
        
        // Executar queries
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            'strlen'
        );
        
        foreach ($statements as $statement) {
            $pdo->exec($statement);
        }
        
        // Salvar configuração
        $config_file = GDA_ROOT . '/config/database.php';
        $config_content = "<?php\n/**\n * Configuração do Banco de Dados\n * Gerado automaticamente em " . date('Y-m-d H:i:s') . "\n */\n\n";
        $config_content .= "return [\n";
        $config_content .= "    'host' => '" . addslashes($config['host']) . "',\n";
        $config_content .= "    'port' => " . $config['port'] . ",\n";
        $config_content .= "    'name' => '" . addslashes($config['name']) . "',\n";
        $config_content .= "    'user' => '" . addslashes($config['user']) . "',\n";
        $config_content .= "    'pass' => '" . addslashes($config['pass']) . "',\n";
        $config_content .= "    'charset' => '" . $config['charset'] . "',\n";
        $config_content .= "    'prefix' => '" . addslashes($config['prefix']) . "',\n";
        $config_content .= "    'options' => [\n";
        $config_content .= "        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,\n";
        $config_content .= "        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n";
        $config_content .= "        PDO::ATTR_EMULATE_PREPARES => false,\n";
        $config_content .= "        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES {$config['charset']}'\n";
        $config_content .= "    ]\n";
        $config_content .= "];\n";
        
        file_put_contents($config_file, $config_content);
        chmod($config_file, 0640);
        
        return ['success' => true, 'config' => $config];
        
    } catch (PDOException $e) {
        $errors[] = 'Erro ao conectar ao banco de dados: ' . $e->getMessage();
        return ['success' => false, 'errors' => $errors];
    }
}

/**
 * Cria usuário administrador
 */
function createAdminUser(array $data, array $dbConfig): array {
    $errors = [];
    
    // Validações
    if (empty($data['admin_name']) || strlen($data['admin_name']) < 3) {
        $errors[] = 'Nome do administrador deve ter no mínimo 3 caracteres';
    }
    
    if (empty($data['admin_email']) || !filter_var($data['admin_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido';
    }
    
    if (empty($data['admin_username']) || strlen($data['admin_username']) < 4) {
        $errors[] = 'Usuário deve ter no mínimo 4 caracteres';
    }
    
    if (empty($data['admin_password']) || strlen($data['admin_password']) < 8) {
        $errors[] = 'Senha deve ter no mínimo 8 caracteres';
    }
    
    if (($data['admin_password'] ?? '') !== ($data['admin_password_confirm'] ?? '')) {
        $errors[] = 'As senhas não coincidem';
    }
    
    // Verificar força da senha
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $data['admin_password'])) {
        $errors[] = 'Senha deve conter letras maiúsculas, minúsculas e números';
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    try {
        // Conectar ao banco
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $dbConfig['host'],
            $dbConfig['port'],
            $dbConfig['name'],
            $dbConfig['charset']
        );
        
        $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        // Gerar hash seguro
        $passwordHash = password_hash($data['admin_password'], PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
        
        // Inserir usuário
        $stmt = $pdo->prepare("
            INSERT INTO {$dbConfig['prefix']}users 
            (name, username, email, password, role, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, 'admin', 'active', NOW(), NOW())
        ");
        
        $stmt->execute([
            $data['admin_name'],
            $data['admin_username'],
            $data['admin_email'],
            $passwordHash
        ]);
        
        $userId = $pdo->lastInsertId();
        
        // Criar perfil do admin
        $stmt = $pdo->prepare("
            INSERT INTO {$dbConfig['prefix']}user_profiles
            (user_id, bio, avatar, created_at)
            VALUES (?, 'Administrador do sistema', NULL, NOW())
        ");
        $stmt->execute([$userId]);
        
        return ['success' => true, 'user_id' => $userId];
        
    } catch (PDOException $e) {
        $errors[] = 'Erro ao criar administrador: ' . $e->getMessage();
        return ['success' => false, 'errors' => $errors];
    }
}

/**
 * Finaliza a instalação
 */
function finalizeInstallation(array $dbConfig, array $data): array {
    try {
        // Criar arquivo de configuração principal
        $app_config = [
            'app_name' => $data['app_name'] ?? 'GameDev Academy',
            'app_url' => rtrim($data['app_url'] ?? '', '/'),
            'app_env' => 'production',
            'app_debug' => false,
            'app_timezone' => $data['timezone'] ?? 'America/Sao_Paulo',
            'app_locale' => 'pt_BR',
            'app_key' => bin2hex(random_bytes(32)),
            'session_lifetime' => 120,
            'session_secure' => isset($_SERVER['HTTPS']),
            'cookie_domain' => parse_url($data['app_url'] ?? '', PHP_URL_HOST)
        ];
        
        $config_content = "<?php\n/**\n * Configuração da Aplicação\n */\n\nreturn " . var_export($app_config, true) . ";\n";
        
        file_put_contents(GDA_ROOT . '/config/app.php', $config_content);
        chmod(GDA_ROOT . '/config/app.php', 0640);
        
        // Criar .env
        $env_content = "# Gerado em " . date('Y-m-d H:i:s') . "\n\n";
        $env_content .= "APP_NAME=\"{$app_config['app_name']}\"\n";
        $env_content .= "APP_URL=\"{$app_config['app_url']}\"\n";
        $env_content .= "APP_ENV={$app_config['app_env']}\n";
        $env_content .= "APP_DEBUG=false\n";
        $env_content .= "APP_KEY={$app_config['app_key']}\n\n";
        
        $env_content .= "DB_HOST={$dbConfig['host']}\n";
        $env_content .= "DB_PORT={$dbConfig['port']}\n";
        $env_content .= "DB_NAME={$dbConfig['name']}\n";
        $env_content .= "DB_USER={$dbConfig['user']}\n";
        $env_content .= "DB_PASS={$dbConfig['pass']}\n";
        $env_content .= "DB_PREFIX={$dbConfig['prefix']}\n";
        
        file_put_contents(GDA_ROOT . '/.env', $env_content);
        chmod(GDA_ROOT . '/.env', 0640);
        
        // Criar .htaccess de segurança
        createSecurityFiles();
        
        // Criar estrutura de pastas
        createDirectoryStructure();
        
        return ['success' => true];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'errors' => ['Erro ao finalizar instalação: ' . $e->getMessage()]
        ];
    }
}

/**
 * Cria arquivos de segurança
 */
function createSecurityFiles(): void {
    // .htaccess raiz (mínimo e não restritivo)
    $htaccess = <<<'HTX'
# GameDev Academy - Minimal Security

# Disable directory browsing
Options -Indexes

# Prevent access to sensitive dotfiles and common secrets
<FilesMatch "^\.">
    Require all denied
</FilesMatch>

<FilesMatch "\.(env|sql|log|lock|yml|yaml)$">
    Require all denied
</FilesMatch>

# Basic security headers (safe defaults)
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
    Header unset Server
    Header unset X-Powered-By
</IfModule>
HTX;
    
    file_put_contents(GDA_ROOT . '/.htaccess', $htaccess);
    
    // Proteger pastas sensíveis
    $protected_dirs = ['config', 'logs', 'storage', 'cache'];
    foreach ($protected_dirs as $dir) {
        $path = GDA_ROOT . '/' . $dir . '/.htaccess';
        file_put_contents($path, "Require all denied\n");
    }
    
    // robots.txt
    $robots = <<<'ROBOTS'
User-agent: *
Disallow: /admin/
Disallow: /config/
Disallow: /install/
Disallow: /api/*/private/
Allow: /

Sitemap: {APP_URL}/sitemap.xml
ROBOTS;
    
    file_put_contents(GDA_ROOT . '/robots.txt', $robots);
}

/**
 * Cria estrutura de diretórios
 */
function createDirectoryStructure(): void {
    $directories = [
        'uploads/courses',
        'uploads/users',
        'uploads/images',
        'uploads/videos',
        'uploads/documents',
        'cache/views',
        'cache/data',
        'logs/app',
        'logs/security',
        'storage/sessions',
        'storage/temp',
        'backups'
    ];
    
    foreach ($directories as $dir) {
        $path = GDA_ROOT . '/' . $dir;
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        
        // Adicionar index.html vazio para segurança
        file_put_contents($path . '/index.html', '');
    }
}

/**
 * Sanitiza entrada
 */
function sanitize(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Valida email
 */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Gera token seguro
 */
function generateToken(int $length = 32): string {
    return bin2hex(random_bytes($length));
}

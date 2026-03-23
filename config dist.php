<?php
/**
 * Arquivo modelo de configuracao do sistema.
 * Copie para config.php ou deixe o instalador gerar automaticamente.
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', rtrim(__DIR__, '/\\') . DIRECTORY_SEPARATOR);
}

if (!defined('BASE_PATH')) {
    define('BASE_PATH', ROOT_PATH);
}

// Configuracoes do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gamedev_academy');
define('DB_PORT', 3306);
define('DB_PREFIX', '');
define('DB_CHARSET', 'utf8mb4');

// Configuracoes do Sistema
define('SITE_URL', 'http://localhost/gamedev-academy');
define('SITE_NAME', 'GameDev Academy');
define('SITE_EMAIL', 'contato@gamedev.com');
define('TIMEZONE', 'America/Sao_Paulo');
define('BASE_URL', rtrim(SITE_URL, '/') . '/');
define('ASSETS_URL', BASE_URL . 'assets/');
define('UPLOADS_URL', BASE_URL . 'uploads/');
define('ADMIN_URL', BASE_URL . 'admin/');
define('USER_URL', BASE_URL . 'user/');
define('INSTALL_URL', BASE_URL . 'install/');

// Configuracoes de Seguranca
define('SECURITY_SALT', 'troque-este-salt-por-um-valor-unico');
define('SESSION_NAME', 'gamedev_session');
define('COOKIE_SECURE', false);
define('COOKIE_HTTPONLY', true);

// Configuracoes de Debug
define('DEBUG_MODE', false);
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
define('INSTALL_DATE', date('Y-m-d H:i:s'));

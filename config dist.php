<?php
/**
 * Arquivo de configuração do sistema
 * Gerado automaticamente pelo instalador
 * Data: 2026-02-09 19:34:28
 */

// Configurações de Email SMTP (configurar após instalação)
/*if (!defined('SMTP_HOST')) define('SMTP_HOST', '');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 587);
if (!defined('SMTP_USER')) define('SMTP_USER', '');
if (!defined('SMTP_PASS')) define('SMTP_PASS', '');
if (!defined('SMTP_SECURITY')) define('SMTP_SECURITY', 'tls');
if (!defined('SMTP_FROM_EMAIL')) define('SMTP_FROM_EMAIL', 'noreply@localhost');
if (!defined('SMTP_FROM_NAME')) define('SMTP_FROM_NAME', SITE_NAME);
*/

// Configurações do Banco de Dados
define('DB_HOST', '');
define('DB_USER', '');
define('DB_PASS', '');
define('DB_NAME', '');
define('DB_PORT', 3306);
define('DB_PREFIX', '');
define('DB_CHARSET', 'utf8mb4');


// Configurações do Sistema
define('SITE_URL', 'http://localhost/gamedev-academy');
define('SITE_NAME', 'GameDev Academy');
define('SITE_EMAIL', 'contato@davidalmeida.xyz');
define('TIMEZONE', 'UTC');

// Configurações de Segurança
define('SECURITY_SALT', 'ee1a43a1d9af20c655b3655a358515c9520a0cc52e875fbdff4ad57d771ea19d');
define('SESSION_NAME', 'gamedev_session');
define('COOKIE_SECURE', false);
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
define('INSTALL_DATE', '2026-02-09 19:34:28');

// Prevenir acesso direto
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

// Autoload e inicialização
require_once ROOT_PATH . '/includes/init.php';
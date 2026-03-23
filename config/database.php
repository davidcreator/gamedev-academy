<?php
// config/database.php

require_once dirname(__DIR__) . '/includes/install-state.php';

gamedevRequireInstalledSystem();

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', rtrim(dirname(__DIR__), '/\\') . DIRECTORY_SEPARATOR);
}

if (!defined('BASE_PATH')) {
    define('BASE_PATH', ROOT_PATH);
}

if (!defined('DB_PORT')) {
    define('DB_PORT', 3306);
}

if (!defined('DB_PREFIX')) {
    define('DB_PREFIX', '');
}

if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', 'utf8mb4');
}

if (!defined('BASE_URL') && defined('SITE_URL')) {
    define('BASE_URL', rtrim(SITE_URL, '/') . '/');
}

if (!defined('ASSETS_URL') && defined('BASE_URL')) {
    define('ASSETS_URL', BASE_URL . 'assets/');
}

if (!defined('UPLOADS_URL') && defined('BASE_URL')) {
    define('UPLOADS_URL', BASE_URL . 'uploads/');
}

if (!defined('ADMIN_URL') && defined('BASE_URL')) {
    define('ADMIN_URL', BASE_URL . 'admin/');
}

if (!defined('USER_URL') && defined('BASE_URL')) {
    define('USER_URL', BASE_URL . 'user/');
}

if (!defined('INSTALL_URL') && defined('BASE_URL')) {
    define('INSTALL_URL', BASE_URL . 'install/');
}

if (!defined('SESSION_LIFETIME')) {
    define('SESSION_LIFETIME', 86400 * 7);
}

if (!defined('UPLOAD_PATH')) {
    define('UPLOAD_PATH', ROOT_PATH . 'uploads/');
}

if (!defined('MAX_UPLOAD_SIZE')) {
    define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024);
}

date_default_timezone_set(defined('TIMEZONE') && TIMEZONE !== '' ? TIMEZONE : 'America/Sao_Paulo');

$debugEnabled = defined('DEBUG_MODE') && DEBUG_MODE;
error_reporting($debugEnabled ? E_ALL : 0);
ini_set('display_errors', $debugEnabled ? '1' : '0');
ini_set('log_errors', '1');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    $GLOBALS['pdo'] = $pdo;
} catch (PDOException $e) {
    die('Erro de conexao com o banco de dados: ' . $e->getMessage());
}

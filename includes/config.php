<?php
/**
 * Bootstrap de compatibilidade para a estrutura legada.
 */

if (defined('GAMEDEV_LEGACY_CONFIG_LOADED')) {
    return;
}

define('GAMEDEV_LEGACY_CONFIG_LOADED', true);

require_once __DIR__ . '/install-state.php';
gamedevRequireInstalledSystem();
require_once __DIR__ . '/../config/database.php';

if (!defined('ALLOW_CONFIG')) {
    define('ALLOW_CONFIG', true);
}

if (!defined('INCLUDES_PATH')) {
    define('INCLUDES_PATH', ROOT_PATH . 'includes/');
}

if (!defined('ASSETS_PATH')) {
    define('ASSETS_PATH', ROOT_PATH . 'assets/');
}

if (!defined('UPLOADS_PATH')) {
    define('UPLOADS_PATH', ROOT_PATH . 'uploads/');
}

if (!defined('ADMIN_PATH')) {
    define('ADMIN_PATH', ROOT_PATH . 'admin/');
}

if (!defined('USER_PATH')) {
    define('USER_PATH', ROOT_PATH . 'user/');
}

if (!defined('COURSES_PATH')) {
    define('COURSES_PATH', ROOT_PATH . 'courses/');
}

if (!defined('COURSES_URL') && defined('BASE_URL')) {
    define('COURSES_URL', BASE_URL . 'courses/');
}

if (!defined('PDO_OPTIONS')) {
    define('PDO_OPTIONS', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

if (!defined('SITE_DESCRIPTION')) {
    define('SITE_DESCRIPTION', 'Aprenda desenvolvimento de jogos com os melhores cursos online');
}

if (!defined('SITE_KEYWORDS')) {
    define('SITE_KEYWORDS', 'gamedev, desenvolvimento de jogos, unity, unreal, godot, programacao');
}

if (!defined('SITE_AUTHOR')) {
    define('SITE_AUTHOR', 'GameDev Academy');
}

if (!defined('SITE_VERSION') && defined('SYSTEM_VERSION')) {
    define('SITE_VERSION', SYSTEM_VERSION);
}

if (!defined('ALLOWED_IMAGE_TYPES')) {
    define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
}

if (!defined('ALLOWED_VIDEO_TYPES')) {
    define('ALLOWED_VIDEO_TYPES', ['mp4', 'webm', 'ogg']);
}

if (!defined('ALLOWED_DOCUMENT_TYPES')) {
    define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'txt']);
}

if (!defined('ITEMS_PER_PAGE')) {
    define('ITEMS_PER_PAGE', 12);
}

if (!defined('PAGINATION_RANGE')) {
    define('PAGINATION_RANGE', 5);
}

if (!defined('USER_STATUS_ACTIVE')) {
    define('USER_STATUS_ACTIVE', 1);
}

if (!defined('USER_STATUS_INACTIVE')) {
    define('USER_STATUS_INACTIVE', 0);
}

if (!defined('USER_STATUS_BANNED')) {
    define('USER_STATUS_BANNED', -1);
}

if (!defined('COURSE_STATUS_DRAFT')) {
    define('COURSE_STATUS_DRAFT', 0);
}

if (!defined('COURSE_STATUS_PUBLISHED')) {
    define('COURSE_STATUS_PUBLISHED', 1);
}

if (!defined('COURSE_STATUS_ARCHIVED')) {
    define('COURSE_STATUS_ARCHIVED', 2);
}

if (!defined('ACCESS_PUBLIC')) {
    define('ACCESS_PUBLIC', 0);
}

if (!defined('ACCESS_REGISTERED')) {
    define('ACCESS_REGISTERED', 1);
}

if (!defined('ACCESS_PREMIUM')) {
    define('ACCESS_PREMIUM', 2);
}

if (!defined('ACCESS_ADMIN')) {
    define('ACCESS_ADMIN', 9);
}

date_default_timezone_set(defined('TIMEZONE') && TIMEZONE !== '' ? TIMEZONE : 'America/Sao_Paulo');
setlocale(LC_ALL, 'pt_BR.utf8', 'pt_BR', 'portuguese');
setlocale(LC_MONETARY, 'pt_BR.utf8', 'pt_BR', 'portuguese');

if (session_status() === PHP_SESSION_NONE) {
    if (defined('SESSION_NAME') && SESSION_NAME !== '') {
        session_name(SESSION_NAME);
    }

    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', (string) SESSION_LIFETIME);
    ini_set('session.cookie_lifetime', '0');

    if (defined('COOKIE_SECURE') && COOKIE_SECURE) {
        ini_set('session.cookie_secure', '1');
    }

    session_start();
}

if (!isset($_SESSION['flash_messages'])) {
    $_SESSION['flash_messages'] = [];
}

if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} elseif (time() - $_SESSION['created'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

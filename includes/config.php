<?php
/**
 * GameDev Academy - Arquivo de Configuração Principal
 * 
 * Este arquivo contém todas as configurações essenciais do sistema
 * @author David Creator
 * @version 2.0.0
 */

// ====================================================================
// CONFIGURAÇÕES DE SEGURANÇA
// ====================================================================

// Previne acesso direto ao arquivo
if (!defined('ALLOW_CONFIG')) {
    define('ALLOW_CONFIG', true);
}

// Configurações de erro (desenvolvimento vs produção)
$isProduction = false; // Mude para true em produção

if ($isProduction) {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// ====================================================================
// DEFINIÇÕES DE CAMINHOS
// ====================================================================

// Caminhos absolutos do sistema
define('ROOT_PATH', dirname(__DIR__) . '/');
define('BASE_PATH', dirname(__DIR__) . '/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('ASSETS_PATH', ROOT_PATH . 'assets/');
define('UPLOADS_PATH', ROOT_PATH . 'uploads/');
define('ADMIN_PATH', ROOT_PATH . 'admin/');
define('USER_PATH', ROOT_PATH . 'user/');
define('COURSES_PATH', ROOT_PATH . 'courses/');

// URLs do sistema (ajuste conforme seu ambiente)
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$folder = '/gamedev-academy/'; // Ajuste se necessário

define('BASE_URL', $protocol . $host . $folder);
define('ASSETS_URL', BASE_URL . 'assets/');
define('UPLOADS_URL', BASE_URL . 'uploads/');
define('ADMIN_URL', BASE_URL . 'admin/');
define('USER_URL', BASE_URL . 'user/');
define('COURSES_URL', BASE_URL . 'courses/');

// ====================================================================
// CONFIGURAÇÕES DO BANCO DE DADOS
// ====================================================================

// Credenciais do banco de dados
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'gamedev_academy');
define('DB_USER', 'root');
define('DB_PASS', ''); // Em produção, use senha forte!
define('DB_CHARSET', 'utf8mb4');

// Opções PDO
define('PDO_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    PDO::ATTR_PERSISTENT => false
]);

// ====================================================================
// CONEXÃO COM O BANCO DE DADOS
// ====================================================================

try {
    $dsn = sprintf(
        "mysql:host=%s;port=%s;dbname=%s;charset=%s",
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_CHARSET
    );
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, PDO_OPTIONS);
    
    // Define o timezone do MySQL (opcional)
    $pdo->exec("SET time_zone = '-03:00'"); // Ajuste para seu timezone
    
} catch (PDOException $e) {
    if (!$isProduction) {
        die("
            <div style='padding:20px; background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; border-radius:5px; margin:20px; font-family:Arial;'>
                <h3>⚠️ Erro de Conexão com o Banco de Dados</h3>
                <p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>
                <p><strong>Código:</strong> " . $e->getCode() . "</p>
                <hr>
                <p><strong>Verifique:</strong></p>
                <ul>
                    <li>O MySQL/MariaDB está rodando?</li>
                    <li>O banco de dados '" . DB_NAME . "' existe?</li>
                    <li>As credenciais estão corretas?</li>
                    <li>A porta " . DB_PORT . " está correta?</li>
                </ul>
            </div>
        ");
    } else {
        // Em produção, loga o erro e mostra mensagem genérica
        error_log("Database Connection Error: " . $e->getMessage());
        die("Desculpe, estamos com problemas técnicos. Por favor, tente novamente mais tarde.");
    }
}

// ====================================================================
// CONFIGURAÇÕES DE SESSÃO
// ====================================================================

// Configurações de sessão (aplicadas somente se a sessão não estiver ativa)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    if ($protocol === 'https://') {
        ini_set('session.cookie_secure', 1);
    }
    ini_set('session.gc_maxlifetime', 3600);
    ini_set('session.cookie_lifetime', 0);
    session_start();
}

// Regenera o ID da sessão periodicamente (segurança)
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 1800) {
    // Sessão iniciada há mais de 30 minutos
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// ====================================================================
// CONFIGURAÇÕES DO SISTEMA
// ====================================================================

// Informações do site
define('SITE_NAME', 'GameDev Academy');
define('SITE_DESCRIPTION', 'Aprenda desenvolvimento de jogos com os melhores cursos online');
define('SITE_KEYWORDS', 'gamedev, desenvolvimento de jogos, unity, unreal, godot, programação');
define('SITE_AUTHOR', 'David Creator');
define('SITE_VERSION', '1.0.0');

// Configurações de email
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'seu-email@gmail.com');
define('SMTP_PASS', 'sua-senha-de-app'); // Use senha de app, não a senha normal
define('SMTP_FROM_EMAIL', 'noreply@gamedev-academy.com');
define('SMTP_FROM_NAME', 'GameDev Academy');

// Configurações de upload
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB em bytes
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_VIDEO_TYPES', ['mp4', 'webm', 'ogg']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'txt']);

// Configurações de paginação
define('ITEMS_PER_PAGE', 12);
define('PAGINATION_RANGE', 5);

// Configurações de cache
define('CACHE_ENABLED', false); // Ative em produção
define('CACHE_TIME', 3600); // 1 hora

// ====================================================================
// TIMEZONE E LOCALIZAÇÃO
// ====================================================================

// Define o timezone padrão
date_default_timezone_set('America/Sao_Paulo');

// Define a localização para português brasileiro
setlocale(LC_ALL, 'pt_BR.utf8', 'pt_BR', 'portuguese');
setlocale(LC_MONETARY, 'pt_BR.utf8', 'pt_BR', 'portuguese');

// ====================================================================
// FUNÇÕES AUXILIARES GLOBAIS
// ====================================================================

/**
 * Função para debug (remover em produção)
 */
if (!function_exists('dd')) {
    function dd($data, $die = true) {
        echo '<pre style="background:#222; color:#0f0; padding:10px; margin:10px; border-radius:5px;">';
        var_dump($data);
        echo '</pre>';
        if ($die) die();
    }
}

/**
 * Função para sanitizar entrada
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Função para verificar se é requisição AJAX
 */
function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * Função para redirecionar
 */
function redirect($url, $statusCode = 302) {
    header('Location: ' . $url, true, $statusCode);
    exit();
}

/**
 * Função para gerar token CSRF
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Função para verificar token CSRF
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Função para verificar se usuário está logado
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Função para verificar se é admin
 */
function isAdmin() {
    return isLoggedIn() && 
           isset($_SESSION['user_role']) && 
           $_SESSION['user_role'] === 'admin';
}

/**
 * Função para obter URL completa atual
 */
function getCurrentUrl() {
    return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Função para formatar data em português
 */
function formatDate($date, $format = 'd/m/Y H:i') {
    if (!$date) return '';
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    return date($format, $timestamp);
}

/**
 * Função para gerar slug de URL
 */
function createSlug($text) {
    // Remove acentos
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    // Converte para minúsculas
    $text = strtolower($text);
    // Remove caracteres especiais
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    // Remove múltiplos hífens
    $text = preg_replace('/-+/', '-', $text);
    // Remove hífens do início e fim
    return trim($text, '-');
}

// ====================================================================
// AUTOLOAD DE CLASSES (Opcional)
// ====================================================================

spl_autoload_register(function ($class) {
    $paths = [
        ROOT_PATH . 'classes/',
        ROOT_PATH . 'models/',
        ROOT_PATH . 'controllers/',
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    return false;
});

// ====================================================================
// CONSTANTES DE STATUS
// ====================================================================

// Status de usuário
define('USER_STATUS_ACTIVE', 1);
define('USER_STATUS_INACTIVE', 0);
define('USER_STATUS_BANNED', -1);

// Status de curso
define('COURSE_STATUS_DRAFT', 0);
define('COURSE_STATUS_PUBLISHED', 1);
define('COURSE_STATUS_ARCHIVED', 2);

// Níveis de acesso
define('ACCESS_PUBLIC', 0);
define('ACCESS_REGISTERED', 1);
define('ACCESS_PREMIUM', 2);
define('ACCESS_ADMIN', 9);

// ====================================================================
// INCLUSÃO DE ARQUIVOS ESSENCIAIS
// ====================================================================

// Inclui funções adicionais se existir
if (file_exists(INCLUDES_PATH . 'functions.php')) {
    require_once INCLUDES_PATH . 'functions.php';
}

// Inclui helpers se existir
if (file_exists(INCLUDES_PATH . 'helpers.php')) {
    require_once INCLUDES_PATH . 'helpers.php';
}

// ====================================================================
// VARIÁVEIS GLOBAIS
// ====================================================================

// Array global para mensagens flash
if (!isset($_SESSION['flash_messages'])) {
    $_SESSION['flash_messages'] = [];
}

// Informações do usuário logado
$currentUser = null;
if (isLoggedIn()) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        $currentUser = $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching user: " . $e->getMessage());
    }
}

// ====================================================================
// FIM DO ARQUIVO DE CONFIGURAÇÃO
// ====================================================================

// Debug - Remova em produção
if (!$isProduction && isset($_GET['debug'])) {
    echo "<div style='background:#333; color:#fff; padding:20px; margin:20px; border-radius:5px;'>";
    echo "<h3>🐛 Debug Mode</h3>";
    echo "<p><strong>Root Path:</strong> " . ROOT_PATH . "</p>";
    echo "<p><strong>Base URL:</strong> " . BASE_URL . "</p>";
    echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
    echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
    echo "<p><strong>User Logged:</strong> " . (isLoggedIn() ? 'Yes' : 'No') . "</p>";
    echo "</div>";
}
?>

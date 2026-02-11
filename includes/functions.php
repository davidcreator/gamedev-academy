<?php
/**
 * GameDev Academy - Funções Auxiliares
 * 
 * Biblioteca de funções utilitárias do sistema
 * @author David Creator
 * @version 3.1.0 - CORRIGIDO
 */

// ====================================================================
// PREVENÇÃO DE REDECLARAÇÃO
// ====================================================================

if (defined('FUNCTIONS_LOADED')) {
    return;
}
define('FUNCTIONS_LOADED', true);

// ====================================================================
// FUNÇÕES DE NAVEGAÇÃO E URLS - CORRIGIDAS
// ====================================================================

/**
 * Gera URL completa do site
 */
if (!function_exists('url')) {
    function url(string $path = ''): string {
        $baseUrl = defined('BASE_URL') ? BASE_URL : 'http://localhost/gamedev-academy/';
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }
}

/**
 * Gera URL para assets - VERSÃO ÚNICA E CORRIGIDA
 */
if (!function_exists('asset')) {
    function asset(string $path): string {
        // Remove barras extras
        $path = ltrim($path, '/');
        
        // 1. Tenta usar ASSETS_URL (melhor opção)
        if (defined('ASSETS_URL')) {
            return rtrim(ASSETS_URL, '/') . '/' . $path;
        }
        
        // 2. Tenta usar BASE_URL + assets/
        if (defined('BASE_URL')) {
            return rtrim(BASE_URL, '/') . '/assets/' . $path;
        }
        
        // 3. Fallback: detecção automática
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Detecta a pasta do projeto baseado no SCRIPT_NAME
        $scriptName = $_SERVER['SCRIPT_NAME']; // Ex: /gamedev-academy/user/profile.php
        $pathParts = explode('/', trim($scriptName, '/'));
        
        // Pega a primeira parte que é o nome da pasta do projeto
        $projectFolder = $pathParts[0] ?? '';
        
        // Se detectou a pasta, usa ela
        if (!empty($projectFolder) && $projectFolder !== 'index.php') {
            return $protocol . $host . '/' . $projectFolder . '/assets/' . $path;
        }
        
        // Última opção: assets na raiz
        return $protocol . $host . '/assets/' . $path;
    }
}

/**
 * Redireciona para uma URL
 */
if (!function_exists('redirect')) {
    function redirect(string $url, int $statusCode = 302): void {
        $url = trim($url);
        
        // Se for URL relativa, adiciona base URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $url = url($url);
        }
        
        // Limpa buffer de saída
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header("Location: {$url}", true, $statusCode);
        exit;
    }
}

// ====================================================================
// FUNÇÕES DE SEGURANÇA E SANITIZAÇÃO
// ====================================================================

if (!function_exists('escape')) {
    function escape(?string $string): string {
        if ($string === null) {
            return '';
        }
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('e')) {
    function e(?string $string): string {
        return escape($string);
    }
}

if (!function_exists('esc')) {
    function esc(?string $string): string {
        return escape($string);
    }
}

if (!function_exists('sanitize')) {
    function sanitize($input) {
        if (is_array($input)) {
            return array_map('sanitize', $input);
        }
        return htmlspecialchars(trim($input ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('old')) {
    function old(string $key, string $default = ''): string {
        if (isset($_SESSION['old_input'][$key])) {
            $value = $_SESSION['old_input'][$key];
            unset($_SESSION['old_input'][$key]);
            return escape($value);
        }
        return escape($_POST[$key] ?? $_GET[$key] ?? $default);
    }
}

// ====================================================================
// FUNÇÕES CSRF
// ====================================================================

if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken(): string {
        return csrf_token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('verify_csrf')) {
    function verify_csrf(?string $token): bool {
        if ($token === null) {
            return false;
        }
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('verifyCSRFToken')) {
    function verifyCSRFToken(?string $token): bool {
        return verify_csrf($token);
    }
}

// ====================================================================
// FUNÇÕES DE DATA E HORA
// ====================================================================

if (!function_exists('formatDate')) {
    function formatDate(?string $date, string $format = 'd/m/Y'): string {
        if (empty($date)) {
            return '';
        }
        $timestamp = is_numeric($date) ? $date : strtotime($date);
        if ($timestamp === false) {
            return '';
        }
        return date($format, $timestamp);
    }
}

if (!function_exists('formatDateTime')) {
    function formatDateTime(?string $date, string $format = 'd/m/Y H:i'): string {
        return formatDate($date, $format);
    }
}

if (!function_exists('timeAgo')) {
    function timeAgo(?string $datetime, bool $full = false): string {
        if (empty($datetime)) {
            return 'nunca';
        }
        
        $now = new DateTime();
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);
        
        if ($diff->days > 30) {
            return formatDate($datetime);
        }
        
        $units = [
            'y' => ['ano', 'anos'],
            'm' => ['mês', 'meses'],
            'd' => ['dia', 'dias'],
            'h' => ['hora', 'horas'],
            'i' => ['minuto', 'minutos'],
            's' => ['segundo', 'segundos']
        ];
        
        foreach ($units as $unit => $labels) {
            $value = $diff->$unit;
            if ($value > 0) {
                $label = $value == 1 ? $labels[0] : $labels[1];
                if ($unit == 's' && $value < 30) {
                    return 'agora mesmo';
                }
                return "há {$value} {$label}";
            }
        }
        
        return 'agora mesmo';
    }
}

// ====================================================================
// FUNÇÕES DE BANCO DE DADOS
// ====================================================================

/**
 * Funções auxiliares do GameDev Academy
 */

/**
 * Obtém conexão com o banco de dados
 */
function getDBConnection() {
    if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
        return $GLOBALS['pdo'];
    }
    
    static $pdo = null;
    if ($pdo === null) {
        $host = defined('DB_HOST') ? DB_HOST : 'localhost';
        $port = defined('DB_PORT') ? DB_PORT : '3306';
        $dbname = defined('DB_NAME') ? DB_NAME : 'gamedev_academy';
        $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';
        $user = defined('DB_USER') ? DB_USER : (defined('DB_USERNAME') ? DB_USERNAME : 'root');
        $pass = defined('DB_PASS') ? DB_PASS : (defined('DB_PASSWORD') ? DB_PASSWORD : '');
        
        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
        
        $options = defined('PDO_OPTIONS') ? PDO_OPTIONS : [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        
        $pdo = new PDO($dsn, $user, $pass, $options);
        $GLOBALS['pdo'] = $pdo;
    }
    
    return $pdo;
}

/**
 * Enviar email de recuperação de senha
 */
function sendPasswordResetEmail($email, $name, $reset_link) {
    $subject = "Recuperação de Senha - GameDev Academy";
    
    // Template HTML do email
    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🎮 GameDev Academy</h1>
                <p>Recuperação de Senha</p>
            </div>
            <div class="content">
                <h2>Olá, ' . htmlspecialchars($name) . '!</h2>
                <p>Recebemos uma solicitação para redefinir sua senha.</p>
                <p>Clique no botão abaixo para criar uma nova senha:</p>
                <center>
                    <a href="' . $reset_link . '" class="button">Redefinir Senha</a>
                </center>
                <p>Ou copie e cole este link no seu navegador:</p>
                <p style="word-break: break-all; background: #fff; padding: 10px; border: 1px solid #ddd;">
                    ' . $reset_link . '
                </p>
                <div class="footer">
                    <p><strong>Importante:</strong> Este link expira em 1 hora.</p>
                    <p>Se você não solicitou esta recuperação, ignore este email.</p>
                    <p>© ' . date('Y') . ' GameDev Academy. Todos os direitos reservados.</p>
                </div>
            </div>
        </div>
    </body>
    </html>';
    
    // Headers para HTML
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: GameDev Academy <noreply@gamedevacademy.com>" . "\r\n";
    $headers .= "Reply-To: support@gamedevacademy.com" . "\r\n";
    
    // Enviar email
    return mail($email, $subject, $message, $headers);
}

/**
 * Gera URL amigável
 */
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return $text ?: 'n-a';
}

/**
 * Formata número de forma amigável
 */
function formatNumber($number) {
    if ($number >= 1000000) {
        return round($number / 1000000, 1) . 'M';
    }
    if ($number >= 1000) {
        return round($number / 1000, 1) . 'K';
    }
    return number_format($number);
}



/**
 * Obtém usuário atual
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}


/**
 * Define mensagem flash
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Obtém e limpa mensagem flash
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ====================================================================
// FUNÇÕES DE USUÁRIO E AUTENTICAÇÃO
// ====================================================================

if (!function_exists('isLoggedIn')) {
    function isLoggedIn(): bool {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin(): bool {
        return isLoggedIn() && 
               isset($_SESSION['user_role']) && 
               $_SESSION['user_role'] === 'admin';
    }
}

if (!function_exists('getCurrentUserId')) {
    function getCurrentUserId(): ?int {
        return $_SESSION['user_id'] ?? null;
    }
}

if (!function_exists('getUserAvatar')) {
    function getUserAvatar(?string $avatar = null, string $username = '', int $size = 200): string {
        // Avatar personalizado
        if (!empty($avatar) && $avatar !== 'default.png') {
            $avatarPath = 'uploads/avatars/' . $avatar;
            if (defined('ROOT_PATH') && file_exists(ROOT_PATH . $avatarPath)) {
                return url($avatarPath);
            }
        }
        
        // Gravatar
        if (isset($_SESSION['user_email'])) {
            $hash = md5(strtolower(trim($_SESSION['user_email'])));
            return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=mp";
        }
        
        // Avatar com iniciais
        if (!empty($username)) {
            $name = urlencode($username);
            return "https://ui-avatars.com/api/?name={$name}&background=6366f1&color=fff&size={$size}&bold=true";
        }
        
        // Avatar padrão
        return asset('images/avatars/default.png');
    }
}

if (!function_exists('getAvatar')) {
    function getAvatar(?string $avatar = null, string $username = ''): string {
        return getUserAvatar($avatar, $username);
    }
}

// ====================================================================
// FUNÇÕES DE CURSO
// ====================================================================

if (!function_exists('getDifficultyBadge')) {
    function getDifficultyBadge(?string $difficulty): string {
        $badges = [
            'beginner' => '<span class="badge bg-success">Iniciante</span>',
            'intermediate' => '<span class="badge bg-warning text-dark">Intermediário</span>',
            'advanced' => '<span class="badge bg-danger">Avançado</span>',
            'expert' => '<span class="badge bg-dark">Expert</span>'
        ];
        return $badges[$difficulty] ?? $badges['beginner'];
    }
}

if (!function_exists('getDifficultyText')) {
    function getDifficultyText(?string $difficulty): string {
        $texts = [
            'beginner' => 'Iniciante',
            'intermediate' => 'Intermediário',
            'advanced' => 'Avançado',
            'expert' => 'Expert'
        ];
        return $texts[$difficulty] ?? 'Iniciante';
    }
}

if (!function_exists('getDifficultyColor')) {
    function getDifficultyColor(?string $difficulty): string {
        $colors = [
            'beginner' => 'success',
            'intermediate' => 'warning',
            'advanced' => 'danger',
            'expert' => 'dark'
        ];
        return $colors[$difficulty] ?? 'success';
    }
}

// ====================================================================
// FUNÇÕES DE TEXTO
// ====================================================================

if (!function_exists('truncate')) {
    function truncate(?string $text, int $length = 100, string $suffix = '...'): string {
        if (empty($text)) {
            return '';
        }
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return mb_substr($text, 0, $length) . $suffix;
    }
}

if (!function_exists('limitWords')) {
    function limitWords(?string $text, int $limit = 20, string $suffix = '...'): string {
        if (empty($text)) {
            return '';
        }
        $words = explode(' ', $text);
        if (count($words) <= $limit) {
            return $text;
        }
        return implode(' ', array_slice($words, 0, $limit)) . $suffix;
    }
}

if (!function_exists('createSlug')) {
    function createSlug(?string $text): string {
        if (empty($text)) {
            return '';
        }
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }
}

// ====================================================================
// FUNÇÕES DE MENSAGEM FLASH
// ====================================================================

if (!function_exists('flash')) {
    function flash(string $key, ?string $message = null) {
        if ($message !== null) {
            if (!isset($_SESSION['flash'])) {
                $_SESSION['flash'] = [];
            }
            $_SESSION['flash'][$key] = $message;
            return null;
        }
        
        if (isset($_SESSION['flash'][$key])) {
            $msg = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $msg;
        }
        
        return null;
    }
}

if (!function_exists('addFlashMessage')) {
    function addFlashMessage(string $message, string $type = 'info'): void {
        flash($type, $message);
    }
}

if (!function_exists('showFlashMessages')) {
    function showFlashMessages(): string {
        $html = '';
        $types = [
            'success' => 'success',
            'error' => 'danger',
            'warning' => 'warning',
            'info' => 'info'
        ];
        
        foreach ($types as $type => $class) {
            if ($message = flash($type)) {
                $icon = match($type) {
                    'success' => 'check-circle',
                    'error' => 'exclamation-circle',
                    'warning' => 'exclamation-triangle',
                    default => 'info-circle'
                };
                
                $html .= "<div class='alert alert-{$class} alert-dismissible fade show' role='alert'>";
                $html .= "<i class='fas fa-{$icon} me-2'></i>{$message}";
                $html .= "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>";
                $html .= "</div>";
            }
        }
        
        if (isset($_SESSION['flash_messages']) && is_array($_SESSION['flash_messages'])) {
            foreach ($_SESSION['flash_messages'] as $msg) {
                $type = $msg['type'] ?? 'info';
                $text = $msg['message'] ?? '';
                $class = $types[$type] ?? 'info';
                
                $html .= "<div class='alert alert-{$class} alert-dismissible fade show' role='alert'>";
                $html .= "{$text}";
                $html .= "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>";
                $html .= "</div>";
            }
            $_SESSION['flash_messages'] = [];
        }
        
        return $html;
    }
}

// ====================================================================
// FUNÇÕES UTILITÁRIAS
// ====================================================================

if (!function_exists('dd')) {
    function dd($data, bool $die = true): void {
        echo '<pre style="background:#1a1a1a; color:#4fc3f7; padding:15px; margin:10px; border-radius:5px;">';
        var_dump($data);
        echo '</pre>';
        if ($die) exit;
    }
}

if (!function_exists('formatBytes')) {
    function formatBytes(int $bytes, int $precision = 2): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

if (!function_exists('generateUniqueCode')) {
    function generateUniqueCode(string $prefix = '', int $length = 8): string {
        $code = $prefix . strtoupper(bin2hex(random_bytes($length / 2)));
        return substr($code, 0, $prefix ? strlen($prefix) + $length : $length);
    }
}

if (!function_exists('isValidEmail')) {
    function isValidEmail(?string $email): bool {
        if (empty($email)) return false;
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('isValidUrl')) {
    function isValidUrl(?string $url): bool {
        if (empty($url)) return false;
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}

if (!function_exists('getUserIP')) {
    function getUserIP(): string {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                return trim($ip);
            }
        }
        return '0.0.0.0';
    }
}

if (!function_exists('isAjax')) {
    function isAjax(): bool {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}

if (!function_exists('formatNumber')) {
    function formatNumber($number, int $decimals = 0): string {
        return number_format($number, $decimals, ',', '.');
    }
}

if (!function_exists('formatCurrency')) {
    function formatCurrency($value): string {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }
}

if (!function_exists('logActivity')) {
    function logActivity(string $action, string $details = '', ?int $userId = null): bool {
        global $pdo;
        if (!isset($pdo)) return false;
        
        if ($userId === null && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
        }
        
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE 'activity_logs'");
            if ($stmt->rowCount() == 0) {
                $pdo->exec("
                    CREATE TABLE activity_logs (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT,
                        action VARCHAR(100),
                        details TEXT,
                        ip_address VARCHAR(45),
                        user_agent TEXT,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_user_id (user_id)
                    )
                ");
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $userId,
                $action,
                $details,
                getUserIP(),
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log("Activity log error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('isValidCPF')) {
    function isValidCPF(?string $cpf): bool {
        if (empty($cpf)) return false;
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpf) != 11) return false;
        if (preg_match('/(\d)\1{10}/', $cpf)) return false;
        
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) return false;
        }
        return true;
    }
}

if (!function_exists('formatCPF')) {
    function formatCPF(?string $cpf): string {
        if (empty($cpf)) return '';
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpf) != 11) return $cpf;
        
        return sprintf('%s.%s.%s-%s',
            substr($cpf, 0, 3),
            substr($cpf, 3, 3),
            substr($cpf, 6, 3),
            substr($cpf, 9, 2)
        );
    }
}

if (!function_exists('createPagination')) {
    function createPagination(int $totalItems, int $currentPage, int $itemsPerPage, string $url): string {
        $totalPages = ceil($totalItems / $itemsPerPage);
        if ($totalPages <= 1) return '';
        
        $currentPage = max(1, min($currentPage, $totalPages));
        $html = '<nav><ul class="pagination justify-content-center">';
        
        // Anterior
        $prevDisabled = $currentPage <= 1 ? 'disabled' : '';
        $prevUrl = $currentPage > 1 ? $url . '?page=' . ($currentPage - 1) : '#';
        $html .= "<li class='page-item {$prevDisabled}'><a class='page-link' href='{$prevUrl}'>&laquo;</a></li>";
        
        // Páginas
        $range = 2;
        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i == 1 || $i == $totalPages || abs($i - $currentPage) <= $range) {
                $active = $i == $currentPage ? 'active' : '';
                $pageUrl = $url . '?page=' . $i;
                $html .= "<li class='page-item {$active}'><a class='page-link' href='{$pageUrl}'>{$i}</a></li>";
            } elseif (abs($i - $currentPage) == $range + 1) {
                $html .= "<li class='page-item disabled'><span class='page-link'>...</span></li>";
            }
        }
        
        // Próximo
        $nextDisabled = $currentPage >= $totalPages ? 'disabled' : '';
        $nextUrl = $currentPage < $totalPages ? $url . '?page=' . ($currentPage + 1) : '#';
        $html .= "<li class='page-item {$nextDisabled}'><a class='page-link' href='{$nextUrl}'>&raquo;</a></li>";
        
        $html .= '</ul></nav>';
        return $html;
    }
}

if (!function_exists('isProduction')) {
    function isProduction(): bool {
        return (defined('ENVIRONMENT') && ENVIRONMENT === 'production') ||
               ($_SERVER['SERVER_NAME'] ?? 'localhost') !== 'localhost';
    }
}

/**
 * Sanitiza conteúdo HTML permitindo apenas tags seguras
 * Importante para prevenir XSS
 */
function sanitizeHtmlContent($html) {
    // Tags permitidas para conteúdo educacional
    $allowed_tags = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li',
        'a', 'img',
        'table', 'thead', 'tbody', 'tr', 'th', 'td',
        'blockquote', 'pre', 'code',
        'div', 'span',
        'iframe' // Para vídeos embedados
    ];
    
    // Atributos permitidos por tag
    $allowed_attributes = [
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'class'],
        'iframe' => ['src', 'width', 'height', 'frameborder', 'allowfullscreen'],
        'pre' => ['class', 'data-language'],
        'code' => ['class', 'data-language'],
        'div' => ['class', 'id'],
        'span' => ['class', 'style'],
        'table' => ['class', 'border'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan']
    ];
    
    // Usar HTMLPurifier se disponível (mais seguro)
    if (class_exists('HTMLPurifier')) {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', implode(',', $allowed_tags));
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%');
        
        $purifier = new HTMLPurifier($config);
        return $purifier->purify($html);
    }
    
    // Fallback: sanitização básica
    // Remove scripts e eventos maliciosos
    $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
    $html = preg_replace('/on\w+\s*=\s*(["\']).*?\1/i', '', $html);
    $html = preg_replace('/javascript:/i', '', $html);
    
    return $html;
}

/**
 * Processa conteúdo para exibição (syntax highlighting, etc)
 */
function processLessonContent($content) {
    // Adiciona classes para syntax highlighting com Prism.js
    $content = preg_replace(
        '/<pre><code class="language-(\w+)">/i',
        '<pre class="language-$1"><code class="language-$1">',
        $content
    );
    
    // Converte links do YouTube em embeds
    $content = preg_replace(
        '/https?:\/\/(www\.)?youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/i',
        '<div class="video-embed"><iframe src="https://www.youtube.com/embed/$2" frameborder="0" allowfullscreen></iframe></div>',
        $content
    );
    
    return $content;
}

// ====================================================================
// FIM DO ARQUIVO
// ====================================================================
?>

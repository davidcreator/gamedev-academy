<?php
/**
 * Database Configuration
 * GameDev Academy
 */

// Prevent multiple definitions
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'gamedev_academy');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_CHARSET', 'utf8mb4');
}

// Application settings
if (!defined('APP_NAME')) {
    define('APP_NAME', 'GameDev Academy');
    define('APP_URL', 'http://localhost/gamedev-academy');
    define('APP_ENV', 'development');
}

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include functions file (only once)
$functions_file = dirname(__DIR__) . '/includes/functions.php';
if (file_exists($functions_file)) {
    require_once $functions_file;
}

// Database connection function
if (!function_exists('getConnection')) {
    function getConnection() {
        static $pdo = null;
        
        if ($pdo === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ];
                
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                
            } catch (PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }
        
        return $pdo;
    }
}
?>
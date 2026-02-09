<?php
/**
 * GameDev Academy - Create Tables AJAX Handler
 * @version 2.2 - PDO Compatible Version
 */

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'tables_created' => 0,
    'data_inserted' => false,
    'errors' => []
];

try {
    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check CSRF token
    if (isset($_SESSION['csrf_token']) && isset($_POST['csrf_token'])) {
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            throw new Exception('Token de segurança inválido');
        }
    }
    
    // Check if database config exists
    if (!isset($_SESSION['db_config'])) {
        throw new Exception('Configuração do banco de dados não encontrada. Volte ao passo anterior.');
    }
    
    $config = $_SESSION['db_config'];
    
    // Check PDO extension
    if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')) {
        throw new Exception('Extensão PDO MySQL não está instalada');
    }
    
    // Create PDO connection
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $config['host'],
        $config['port'],
        $config['name']
    );
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];
    
    $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
    
    // Path to create_tables.php
    $createTablesPath = dirname(__DIR__) . '/sql/create_tables.php';
    
    if (!file_exists($createTablesPath)) {
        throw new Exception('Arquivo create_tables.php não encontrado');
    }
    
    // Include the file
    require_once $createTablesPath;
    
    // Check if function exists
    if (!function_exists('executeDatabaseSetup')) {
        throw new Exception('Função executeDatabaseSetup não encontrada');
    }
    
    // Execute database setup
    $result = executeDatabaseSetup($pdo);
    
    // Process result
    if ($result['success']) {
        $response['success'] = true;
        $response['message'] = 'Tabelas criadas com sucesso!';
        $response['tables_created'] = isset($result['stats']['tables_created']) 
            ? $result['stats']['tables_created'] 
            : 51;
        $response['data_inserted'] = isset($result['stats']['data_inserted']) 
            ? $result['stats']['data_inserted'] > 0 
            : true;
        
        // Add messages if available
        if (!empty($result['messages'])) {
            $response['log'] = $result['messages'];
        }
        
        // Add warnings if any
        if (!empty($result['warnings'])) {
            $response['warnings'] = $result['warnings'];
        }
        
        // Mark as completed in session
        $_SESSION['tables_created'] = true;
        
    } else {
        $response['message'] = 'Erro ao criar tabelas';
        $response['errors'] = isset($result['errors']) ? $result['errors'] : ['Erro desconhecido'];
        
        // Add messages for debugging
        if (!empty($result['messages'])) {
            $response['log'] = $result['messages'];
        }
    }
    
} catch (PDOException $e) {
    $response['success'] = false;
    $response['message'] = 'Erro de banco de dados: ' . $e->getMessage();
    $response['errors'][] = $e->getMessage();
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    $response['errors'][] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
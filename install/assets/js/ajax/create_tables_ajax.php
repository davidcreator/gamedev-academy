<?php
/**
 * AJAX handler for table creation
 */

// Prevent direct access
define('AJAX_REQUEST', true);

// Start session
session_start();

// Set JSON header
header('Content-Type: application/json; charset=utf-8');

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'tables_created' => 0,
    'errors' => []
];

try {
    // Security check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        throw new Exception('Token CSRF inválido');
    }

    if (!isset($_SESSION['db_config'])) {
        throw new Exception('Configuração do banco não encontrada');
    }

    // Get database configuration
    $config = $_SESSION['db_config'];
    
    // Include the table creator - adjust path
    $create_tables_path = dirname(dirname(__FILE__)) . '/sql/create_tables.php';
    
    if (!file_exists($create_tables_path)) {
        throw new Exception('Arquivo create_tables.php não encontrado');
    }
    
    require_once $create_tables_path;
    
    // Create connection
    $mysqli = new mysqli(
        $config['host'],
        $config['user'],
        $config['pass'],
        $config['name'],
        $config['port'] ?? 3306
    );
    
    if ($mysqli->connect_error) {
        throw new Exception("Falha na conexão: " . $mysqli->connect_error);
    }
    
    // Set charset
    if (!$mysqli->set_charset('utf8mb4')) {
        $mysqli->set_charset('utf8');
    }
    
    // Check if TableCreator class exists
    if (!class_exists('TableCreator')) {
        throw new Exception('Classe TableCreator não encontrada');
    }
    
    // Create TableCreator instance
    $tableCreator = new TableCreator($mysqli, $config['prefix'] ?? '');
    
    // Execute table creation
    $result = $tableCreator->createAllTables();
    
    if ($result['success']) {
        $response['success'] = true;
        $response['message'] = 'Tabelas criadas com sucesso!';
        $response['tables_created'] = $result['tables_created'] ?? 46;
        $response['data_inserted'] = $result['data_inserted'] ?? true;
        
        $_SESSION['tables_created'] = true;
    } else {
        $response['message'] = 'Erro ao criar tabelas';
        $response['errors'] = $result['errors'] ?? ['Erro desconhecido durante a criação'];
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log('Table creation error: ' . $e->getMessage());
}

// Send JSON response
echo json_encode($response);
exit;
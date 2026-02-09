<?php
/**
 * GameDev Academy - Test Database Connection
 * @version 2.0
 */

// Set headers first
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'server_info' => '',
    'database' => '',
    'privileges' => [],
    'warning' => null
];

try {
    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Get POST data
    $db_host = isset($_POST['db_host']) ? trim($_POST['db_host']) : 'localhost';
    $db_port = isset($_POST['db_port']) ? (int)$_POST['db_port'] : 3306;
    $db_user = isset($_POST['db_user']) ? trim($_POST['db_user']) : '';
    $db_pass = isset($_POST['db_pass']) ? $_POST['db_pass'] : '';
    $db_name = isset($_POST['db_name']) ? trim($_POST['db_name']) : '';
    $db_prefix = isset($_POST['db_prefix']) ? trim($_POST['db_prefix']) : '';
    
    // Validate port
    if ($db_port < 1 || $db_port > 65535) {
        $db_port = 3306;
    }
    
    // Validate required fields
    if (empty($db_host)) {
        throw new Exception('Servidor do banco de dados é obrigatório');
    }
    
    if (empty($db_user)) {
        throw new Exception('Usuário do banco de dados é obrigatório');
    }
    
    if (empty($db_name)) {
        throw new Exception('Nome do banco de dados é obrigatório');
    }
    
    // Check MySQLi extension
    if (!extension_loaded('mysqli')) {
        throw new Exception('Extensão MySQLi não está instalada no PHP');
    }
    
    // Attempt connection
    mysqli_report(MYSQLI_REPORT_OFF);
    $mysqli = @new mysqli($db_host, $db_user, $db_pass, '', $db_port);
    
    // Check connection
    if ($mysqli->connect_error) {
        $code = $mysqli->connect_errno;
        $msg = $mysqli->connect_error;
        
        switch ($code) {
            case 1045:
                throw new Exception('Acesso negado. Verifique o usuário e a senha.');
            case 2002:
                throw new Exception('Não foi possível conectar ao MySQL. Verifique se o servidor está rodando.');
            case 2003:
                throw new Exception("Não foi possível conectar em {$db_host}:{$db_port}.");
            case 2005:
                throw new Exception("Host '{$db_host}' não encontrado.");
            default:
                throw new Exception("Erro de conexão ({$code}): {$msg}");
        }
    }
    
    // Connection successful
    $response['server_info'] = $mysqli->server_info;
    
    // Set charset
    if (!$mysqli->set_charset('utf8mb4')) {
        $mysqli->set_charset('utf8');
    }
    
    // Check if database exists
    $db_escaped = $mysqli->real_escape_string($db_name);
    $result = $mysqli->query("SHOW DATABASES LIKE '{$db_escaped}'");
    
    if ($result && $result->num_rows > 0) {
        $response['database'] = $db_name . ' (existe)';
        $mysqli->select_db($db_name);
    } else {
        // Try to create database
        $createSQL = "CREATE DATABASE IF NOT EXISTS `{$db_escaped}` 
                      CHARACTER SET utf8mb4 
                      COLLATE utf8mb4_unicode_ci";
        
        if ($mysqli->query($createSQL)) {
            $response['database'] = $db_name . ' (criado com sucesso)';
            $mysqli->select_db($db_name);
        } else {
            $response['database'] = $db_name . ' (não existe)';
            $response['warning'] = 'O banco não existe e não foi possível criá-lo: ' . $mysqli->error;
        }
    }
    
    // Check privileges
    $privileges = [];
    $grantsResult = $mysqli->query("SHOW GRANTS FOR CURRENT_USER()");
    
    if ($grantsResult) {
        while ($row = $grantsResult->fetch_array(MYSQLI_NUM)) {
            $grant = strtoupper($row[0]);
            
            if (strpos($grant, 'ALL PRIVILEGES') !== false || strpos($grant, 'GRANT ALL') !== false) {
                $privileges = ['ALL PRIVILEGES'];
                break;
            }
            
            $checkPrivs = ['SELECT', 'INSERT', 'UPDATE', 'DELETE', 'CREATE', 'DROP', 'ALTER', 'INDEX'];
            foreach ($checkPrivs as $priv) {
                if (preg_match('/\b' . $priv . '\b/', $grant) && !in_array($priv, $privileges)) {
                    $privileges[] = $priv;
                }
            }
        }
        $grantsResult->free();
    }
    
    $response['privileges'] = $privileges;
    
    // Check for missing privileges
    if (!in_array('ALL PRIVILEGES', $privileges)) {
        $required = ['SELECT', 'INSERT', 'UPDATE', 'DELETE', 'CREATE'];
        $missing = array_diff($required, $privileges);
        
        if (!empty($missing)) {
            $existingWarning = $response['warning'] ?? '';
            $privWarning = 'Privilégios faltando: ' . implode(', ', $missing);
            $response['warning'] = $existingWarning ? $existingWarning . ' | ' . $privWarning : $privWarning;
        }
    }
    
    // Save config to session
    $_SESSION['db_config'] = [
        'host' => $db_host,
        'port' => $db_port,
        'user' => $db_user,
        'pass' => $db_pass,
        'name' => $db_name,
        'prefix' => $db_prefix
    ];
    
    $_SESSION['db_test_passed'] = true;
    
    // Close connection
    $mysqli->close();
    
    // Success!
    $response['success'] = true;
    $response['message'] = 'Conexão estabelecida com sucesso!';
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
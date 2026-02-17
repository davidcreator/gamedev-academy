<?php
/**
 * AJAX Handler - Criação das Tabelas (PDO)
 * GameDev Academy - Sistema de Instalação
 * 
 * Chamado via AJAX pelo tables-installer.js
 * Usa PDO para conexão com o banco de dados
 */

// ================================================================
// SEGURANÇA
// ================================================================

if (!defined('AJAX_REQUEST')) {
    define('AJAX_REQUEST', true);
}
if (!defined('INSTALLING')) {
    define('INSTALLING', true);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

// Capturar erros fatais
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// ================================================================
// PROCESSAMENTO
// ================================================================

try {
    // Verificar sessão
    if (!isset($_SESSION['db_config'])) {
        throw new Exception('Configuração do banco não encontrada na sessão');
    }

    $config = $_SESSION['db_config'];
    $host   = $config['host'] ?? 'localhost';
    $port   = isset($config['port']) ? (int) $config['port'] : 3306;
    $name   = $config['name'] ?? '';
    $user   = $config['user'] ?? 'root';
    $pass   = $config['pass'] ?? '';

    // Validar dados
    if (empty($name)) {
        throw new Exception('Nome do banco de dados não informado');
    }

    // Verificar extensão PDO
    if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')) {
        throw new Exception('Extensão PDO MySQL não está instalada');
    }

    // ============================================================
    // CONEXÃO PDO
    // ============================================================
    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]);

    // Testar conexão
    $pdo->query("SELECT 1");

    // ============================================================
    // INCLUIR E EXECUTAR CREATE_TABLES
    // ============================================================
    $create_tables_path = dirname(dirname(dirname(__DIR__))) . '/sql/create_tables.php';

    if (!file_exists($create_tables_path)) {
        throw new Exception('Arquivo create_tables.php não encontrado');
    }

    require_once $create_tables_path;

    if (!function_exists('executeDatabaseSetup')) {
        throw new Exception('Função executeDatabaseSetup não encontrada');
    }

    // Executar criação das tabelas
    $result = executeDatabaseSetup($pdo);

    // ============================================================
    // RESPOSTA
    // ============================================================
    if ($result['success']) {
        $_SESSION['tables_created'] = true;
        $_SESSION['install_step']   = 3;

        echo json_encode([
            'success' => true,
            'message' => "Tabelas criadas com sucesso! {$result['stats']['tables_created']} tabelas instaladas.",
            'stats'   => $result['stats'],
            'details' => isset($result['messages']) ? $result['messages'] : []
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao criar tabelas',
            'errors'  => $result['errors'],
            'stats'   => isset($result['stats']) ? $result['stats'] : null
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success'    => false,
        'message'    => 'Erro de banco de dados: ' . $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

restore_error_handler();
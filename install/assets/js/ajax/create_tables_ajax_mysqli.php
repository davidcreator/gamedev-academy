<?php
/**
 * AJAX Handler - Criação das Tabelas (MySQLi)
 * GameDev Academy - Sistema de Instalação
 * 
 * Chamado via AJAX pelo tables-installer.js
 * Usa MySQLi para conexão com o banco de dados
 * Converte para PDO internamente para o executeDatabaseSetup
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

$mysqli = null;

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

    // Verificar extensão MySQLi
    if (!extension_loaded('mysqli')) {
        throw new Exception('Extensão MySQLi não está instalada');
    }

    // ============================================================
    // CONEXÃO MySQLi
    // ============================================================
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $mysqli = new mysqli($host, $user, $pass, $name, $port);

    if ($mysqli->connect_error) {
        throw new Exception('Falha na conexão: ' . $mysqli->connect_error);
    }

    $mysqli->set_charset('utf8mb4');

    // Testar conexão
    $testResult = $mysqli->query("SELECT 1");
    if (!$testResult) {
        throw new Exception('Falha ao testar conexão com o banco');
    }
    $testResult->free();

    // ============================================================
    // CRIAR PDO PARA O executeDatabaseSetup
    // O create_tables.php usa PDO internamente
    // ============================================================
    $pdo = null;
    $usePdo = extension_loaded('pdo') && extension_loaded('pdo_mysql');

    if ($usePdo) {
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]);
    }

    // ============================================================
    // INCLUIR E EXECUTAR CREATE_TABLES
    // ============================================================
    $create_tables_path = dirname(dirname(dirname(__DIR__))) . '/sql/create_tables.php';

    if (!file_exists($create_tables_path)) {
        throw new Exception('Arquivo create_tables.php não encontrado');
    }

    require_once $create_tables_path;

    // ============================================================
    // EXECUTAR SETUP
    // ============================================================

    // Método 1: Usar executeDatabaseSetup com PDO (preferido)
    if ($usePdo && function_exists('executeDatabaseSetup')) {
        $result = executeDatabaseSetup($pdo);
    }
    // Método 2: Usar executeDatabaseSetupMysqli se existir
    elseif (function_exists('executeDatabaseSetupMysqli')) {
        $result = executeDatabaseSetupMysqli($mysqli);
    }
    // Método 3: Fallback - executar SQL direto via MySQLi
    else {
        $result = executeTablesWithMysqli($mysqli);
    }

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

} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode([
        'success'    => false,
        'message'    => 'Erro MySQLi: ' . $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success'    => false,
        'message'    => 'Erro PDO: ' . $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    // Fechar conexão MySQLi
    if ($mysqli !== null && $mysqli instanceof mysqli) {
        $mysqli->close();
    }
}

restore_error_handler();

// ================================================================
// FALLBACK: Executar SQL direto via MySQLi
// Usado quando PDO não está disponível e
// executeDatabaseSetupMysqli não existe
// ================================================================

function executeTablesWithMysqli(mysqli $mysqli): array
{
    $errors   = [];
    $messages = [];
    $created  = 0;

    // Carregar schema SQL
    $schemaPath = dirname(dirname(dirname(__DIR__))) . '/sql/schema.sql';

    if (!file_exists($schemaPath)) {
        return [
            'success' => false,
            'errors'  => ['Arquivo schema.sql não encontrado'],
            'stats'   => ['tables_created' => 0, 'tables_expected' => 54],
            'messages' => []
        ];
    }

    $sql = file_get_contents($schemaPath);

    if (empty($sql)) {
        return [
            'success' => false,
            'errors'  => ['Arquivo schema.sql está vazio'],
            'stats'   => ['tables_created' => 0, 'tables_expected' => 54],
            'messages' => []
        ];
    }

    // Desabilitar FK checks
    $mysqli->query("SET FOREIGN_KEY_CHECKS = 0");
    $mysqli->query("SET NAMES utf8mb4");

    // Executar cada statement
    $mysqli->multi_query($sql);

    do {
        $result = $mysqli->store_result();
        if ($result) {
            $result->free();
        }

        if ($mysqli->errno) {
            $errors[] = "Erro MySQL [{$mysqli->errno}]: {$mysqli->error}";
        }
    } while ($mysqli->next_result());

    // Reabilitar FK checks
    $mysqli->query("SET FOREIGN_KEY_CHECKS = 1");

    // Contar tabelas criadas
    $countResult = $mysqli->query("SHOW TABLES");
    if ($countResult) {
        $created = $countResult->num_rows;
        while ($row = $countResult->fetch_array()) {
            $messages[] = "✅ Tabela '{$row[0]}' criada";
        }
        $countResult->free();
    }

    return [
        'success'  => empty($errors) && $created > 0,
        'errors'   => $errors,
        'messages' => $messages,
        'stats'    => [
            'tables_created'  => $created,
            'tables_expected' => 54
        ]
    ];
}
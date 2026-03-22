<?php
/**
 * AJAX Handler - Criacao das Tabelas (MySQLi)
 */

if (!defined('AJAX_REQUEST')) { define('AJAX_REQUEST', true); }
if (!defined('INSTALLING')) { define('INSTALLING', true); }
@set_time_limit(900);
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_SESSION['db_config'])) {
        throw new Exception('Configuracao do banco nao encontrada na sessao');
    }
    $config = $_SESSION['db_config'];
    $host = $config['host'] ?? 'localhost';
    $port = isset($config['port']) ? (int)$config['port'] : 3306;
    $name = $config['name'] ?? '';
    $user = $config['user'] ?? 'root';
    $pass = $config['pass'] ?? '';
    if (empty($name)) { throw new Exception('Nome do banco de dados nao informado'); }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $mysqli = new mysqli($host, $user, $pass, $name, $port);
    $mysqli->set_charset('utf8mb4');

    $schemaPath = dirname(dirname(dirname(__DIR__))) . '/install/database/schema.sql';
    if (!file_exists($schemaPath)) { $schemaPath = dirname(dirname(dirname(__DIR__))) . '/sql/schema.sql'; }
    if (!file_exists($schemaPath)) { $schemaPath = dirname(dirname(dirname(dirname(__DIR__)))) . '/install/database/schema.sql'; }
    if (!file_exists($schemaPath)) { throw new Exception('Arquivo schema.sql nao encontrado'); }

    $sql = file_get_contents($schemaPath);
    if (empty($sql)) { throw new Exception('Arquivo schema.sql esta vazio'); }

    $mysqli->query('SET FOREIGN_KEY_CHECKS=0;');
    $mysqli->query('SET UNIQUE_CHECKS=0;');

    if (!$mysqli->multi_query($sql)) {
        throw new Exception('Erro ao executar schema: ' . $mysqli->error);
    }
    while ($mysqli->more_results()) { $mysqli->next_result(); }

    $mysqli->query('SET FOREIGN_KEY_CHECKS=1;');
    $mysqli->query('SET UNIQUE_CHECKS=1;');

    $res = $mysqli->query('SHOW TABLES');
    $totalTables = $res->num_rows;

    $_SESSION['tables_created'] = $totalTables > 0;
    $_SESSION['install_step'] = 3;

    echo json_encode([
        'success' => $totalTables > 0,
        'message' => "Tabelas criadas com sucesso! {$totalTables} tabelas instaladas.",
        'stats' => [
            'tables_created' => $totalTables,
            'errors' => []
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

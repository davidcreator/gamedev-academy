<?php
/**
 * AJAX Handler - Criacao das Tabelas (PDO)
 */

if (!defined('AJAX_REQUEST')) { define('AJAX_REQUEST', true); }
if (!defined('INSTALLING')) { define('INSTALLING', true); }
@set_time_limit(900);
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_SESSION['db_config'])) { throw new Exception('Configuracao do banco nao encontrada na sessao'); }
    $config = $_SESSION['db_config'];
    $port   = isset($config['port']) ? (int)$config['port'] : 3306;

    $pdo = new PDO(
        "mysql:host={$config['host']};port={$port};dbname={$config['name']};charset=utf8mb4",
        $config['user'],
        $config['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => true
        ]
    );

    $schemaPath = dirname(dirname(dirname(__DIR__))) . '/install/database/schema.sql';
    if (!file_exists($schemaPath)) { $schemaPath = dirname(dirname(dirname(__DIR__))) . '/sql/schema.sql'; }
    if (!file_exists($schemaPath)) { $schemaPath = dirname(dirname(dirname(dirname(__DIR__)))) . '/install/database/schema.sql'; }
    if (!file_exists($schemaPath)) { throw new Exception('Arquivo schema.sql nao encontrado'); }

    $sql = file_get_contents($schemaPath);
    if (!$sql) { throw new Exception('Arquivo schema.sql esta vazio'); }

    $pdo->exec('SET FOREIGN_KEY_CHECKS=0;');
    $pdo->exec('SET UNIQUE_CHECKS=0;');
    $pdo->exec($sql);
    $pdo->exec('SET FOREIGN_KEY_CHECKS=1;');
    $pdo->exec('SET UNIQUE_CHECKS=1;');

    $totalTables = $pdo->query('SHOW TABLES')->rowCount();

    $_SESSION['tables_created'] = $totalTables > 0;
    $_SESSION['install_step'] = 3;

    echo json_encode([
        'success' => $totalTables > 0,
        'message' => "Tabelas instaladas: {$totalTables}.",
        'tables_created' => $totalTables,
        'data_inserted' => false,
        'stats'   => [
            'tables_created'  => $totalTables,
            'errors'          => []
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

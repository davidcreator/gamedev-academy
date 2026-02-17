<?php
/**
 * AJAX Handler - Criação das Tabelas
 * Executa o schema.sql diretamente - rápido e leve
 */

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

try {
    // Verificar sessão
    if (!isset($_SESSION['db_config'])) {
        throw new Exception('Configuração do banco não encontrada na sessão');
    }

    $config = $_SESSION['db_config'];
    $port   = isset($config['port']) ? (int) $config['port'] : 3306;

    // Conectar via PDO
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$port};dbname={$config['name']};charset=utf8mb4",
        $config['user'],
        $config['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );

    // Carregar schema.sql
    $schemaPath = dirname(dirname(dirname(__DIR__))) . '/install/database/schema.sql';

    // Tentar caminhos alternativos
    if (!file_exists($schemaPath)) {
        $schemaPath = dirname(dirname(dirname(__DIR__))) . '/sql/schema.sql';
    }
    if (!file_exists($schemaPath)) {
        $schemaPath = dirname(dirname(dirname(dirname(__DIR__)))) . '/install/database/schema.sql';
    }

    if (!file_exists($schemaPath)) {
        throw new Exception('Arquivo schema.sql não encontrado');
    }

    $sql = file_get_contents($schemaPath);

    if (empty($sql)) {
        throw new Exception('Arquivo schema.sql está vazio');
    }

    // Executar schema.sql de uma vez
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("SET UNIQUE_CHECKS = 0");
    $pdo->exec("SET AUTOCOMMIT = 0");

    // Separar statements e executar
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function ($s) { return !empty($s) && $s !== "\n"; }
    );

    $errors = [];
    $created = 0;

    foreach ($statements as $statement) {
        // Pular comentários e linhas vazias
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }

        try {
            $pdo->exec($statement);
            // Contar CREATE TABLEs
            if (stripos($statement, 'CREATE TABLE') !== false) {
                $created++;
            }
        } catch (PDOException $e) {
            $errors[] = $e->getMessage();
        }
    }

    $pdo->exec("COMMIT");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    $pdo->exec("SET UNIQUE_CHECKS = 1");
    $pdo->exec("SET AUTOCOMMIT = 1");

    // Contar tabelas reais no banco
    $stmt = $pdo->query("SHOW TABLES");
    $totalTables = $stmt->rowCount();

    // Resultado
    if ($totalTables > 0 && empty($errors)) {
        $_SESSION['tables_created'] = true;
        $_SESSION['install_step'] = 3;

        echo json_encode([
            'success' => true,
            'message' => "Tabelas criadas com sucesso! {$totalTables} tabelas instaladas.",
            'stats'   => [
                'tables_created'  => $totalTables,
                'tables_expected' => 54
            ]
        ]);
    } elseif ($totalTables > 0 && !empty($errors)) {
        $_SESSION['tables_created'] = true;
        $_SESSION['install_step'] = 3;

        echo json_encode([
            'success' => true,
            'message' => "{$totalTables} tabelas criadas com alguns avisos.",
            'stats'   => [
                'tables_created'  => $totalTables,
                'tables_expected' => 54
            ],
            'warnings' => $errors
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Nenhuma tabela foi criada',
            'errors'  => $errors
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro de banco de dados: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
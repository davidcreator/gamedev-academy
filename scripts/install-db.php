<?php
// scripts/install-db.php
// Importa install/database/schema.sql de forma incremental (linha a linha) para evitar timeouts.

$schemaFile = __DIR__ . '/../install/database/schema.sql';
if (!file_exists($schemaFile)) {
    exit("schema.sql não encontrado em {$schemaFile}\n");
}

require __DIR__ . '/../config/database.php'; // usa $pdo do config

$pdo->exec("SET FOREIGN_KEY_CHECKS=0; SET UNIQUE_CHECKS=0; SET NAMES utf8mb4;");

$handle = fopen($schemaFile, 'r');
if (!$handle) {
    exit("Não foi possível abrir schema.sql\n");
}

$statement = '';
$count = 0;
$errors = [];

while (($line = fgets($handle)) !== false) {
    $trim = trim($line);
    // Ignorar comentários
    if ($trim === '' || str_starts_with($trim, '--') || str_starts_with($trim, '/*')) {
        continue;
    }
    $statement .= $line;
    if (str_ends_with(rtrim($line), ';')) {
        try {
            $pdo->exec($statement);
            $count++;
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
        $statement = '';
    }
}
fclose($handle);

$pdo->exec("SET FOREIGN_KEY_CHECKS=1; SET UNIQUE_CHECKS=1;");

echo "Statements executados: {$count}\n";
if ($errors) {
    echo "Erros:\n" . implode("\n", $errors) . "\n";
} else {
    echo "Importação concluída sem erros.\n";
}

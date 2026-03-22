<?php
$pdo = new PDO(
    "mysql:host=localhost;dbname=gda_test;charset=utf8mb4",
    "root",
    "",
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

$schema = __DIR__ . '/../install/database/schema.sql';
if (!file_exists($schema)) {
    die("schema not found\n");
}

echo "Starting import...\n";
$pdo->exec("SET FOREIGN_KEY_CHECKS=0; SET UNIQUE_CHECKS=0; SET NAMES utf8mb4;");

$handle = fopen($schema, 'r');
$buffer = '';
$count = 0;
$errors = [];
while (($line = fgets($handle)) !== false) {
    $trim = trim($line);
    if ($trim === '' || str_starts_with($trim, '--') || str_starts_with($trim, '/*')) {
        continue;
    }
    $buffer .= $line;
    if (str_ends_with(rtrim($line), ';')) {
        $stmt = trim($buffer);
        $preview = substr(preg_replace('/\s+/', ' ', $stmt), 0, 80);
        $next = $count + 1;
        echo "[{$next}] {$preview}\n";
        try {
            $pdo->exec($stmt);
            $count++;
        } catch (Exception $e) {
            $errors[] = "Stmt {$next}: " . $e->getMessage();
            echo "E\n";
        }
        $buffer = '';
    }
}
fclose($handle);
$pdo->exec("SET FOREIGN_KEY_CHECKS=1; SET UNIQUE_CHECKS=1;");

echo "\nExecuted: {$count} statements\n";
if ($errors) {
    echo "Errors (" . count($errors) . "):\n";
    echo implode("\n", array_slice($errors, 0, 5));
}

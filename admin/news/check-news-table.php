<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Conexão com banco
$host = 'localhost';
$dbname = 'gamedev_academy';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔍 Verificação da Tabela NEWS</h2>";
    
    // 1. Verificar se a tabela existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'news'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabela 'news' existe<br><br>";
        
        // 2. Mostrar estrutura da tabela
        echo "<h3>Estrutura Atual da Tabela:</h3>";
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        $stmt = $pdo->query("DESCRIBE news");
        $columns = $stmt->fetchAll();
        
        $hasStatus = false;
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . $column['Field'] . "</td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . $column['Default'] . "</td>";
            echo "<td>" . $column['Extra'] . "</td>";
            echo "</tr>";
            
            if ($column['Field'] == 'status') {
                $hasStatus = true;
            }
        }
        echo "</table>";
        
        if (!$hasStatus) {
            echo "<br><p style='color: red;'>❌ <strong>A coluna 'status' NÃO existe!</strong></p>";
            echo "<p>Vamos corrigir isso...</p>";
        } else {
            echo "<br><p style='color: green;'>✅ A coluna 'status' existe!</p>";
        }
        
        // 3. Contar registros
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM news");
        $result = $stmt->fetch();
        echo "<br><p>📊 Total de registros: " . $result['total'] . "</p>";
        
    } else {
        echo "❌ Tabela 'news' NÃO existe!<br>";
    }
    
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
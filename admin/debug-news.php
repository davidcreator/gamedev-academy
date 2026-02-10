<?php
// admin/debug-news.php - Diagnóstico completo

session_start();

echo "<h2>🔍 Diagnóstico do Sistema de Notícias</h2>";

// Conexão com banco
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=gamedev_academy;charset=utf8mb4",
        "root",
        "",
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );
    echo "✅ Conexão com banco OK<br><br>";
} catch (PDOException $e) {
    die("❌ Erro de conexão: " . $e->getMessage());
}

// Verificar se a tabela news existe
echo "<h3>1. Verificar se tabela 'news' existe:</h3>";
$result = $pdo->query("SHOW TABLES LIKE 'news'");
if ($result->rowCount() > 0) {
    echo "✅ Tabela 'news' existe<br><br>";
    
    // Mostrar estrutura
    echo "<h3>2. Estrutura atual da tabela 'news':</h3>";
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr style='background:#f0f0f0;'><th>Coluna</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    $columns = $pdo->query("DESCRIBE news")->fetchAll();
    $columnNames = [];
    
    foreach ($columns as $col) {
        $columnNames[] = $col['Field'];
        echo "<tr>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table><br>";
    
    // Verificar colunas necessárias
    echo "<h3>3. Verificar colunas necessárias:</h3>";
    $requiredColumns = ['id', 'title', 'slug', 'content', 'excerpt', 'category', 'tags', 'image', 'thumbnail', 'author_id', 'status', 'featured', 'views', 'published_at', 'created_at'];
    
    foreach ($requiredColumns as $col) {
        if (in_array($col, $columnNames)) {
            echo "✅ Coluna '$col' existe<br>";
        } else {
            echo "❌ <strong>Coluna '$col' FALTANDO!</strong><br>";
        }
    }
    
    // Contar registros
    echo "<br><h3>4. Dados na tabela:</h3>";
    $count = $pdo->query("SELECT COUNT(*) FROM news")->fetchColumn();
    echo "Total de registros: <strong>$count</strong><br>";
    
    if ($count > 0) {
        echo "<br><h3>5. Amostra de dados:</h3>";
        $sample = $pdo->query("SELECT * FROM news LIMIT 3")->fetchAll();
        echo "<pre>" . print_r($sample, true) . "</pre>";
    }
    
} else {
    echo "❌ <strong>Tabela 'news' NÃO EXISTE!</strong><br><br>";
    
    echo "<h3>Criando tabela news...</h3>";
    
    $sql = "CREATE TABLE `news` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `slug` VARCHAR(255),
        `content` LONGTEXT NOT NULL,
        `excerpt` TEXT,
        `category` VARCHAR(50) DEFAULT 'geral',
        `tags` TEXT,
        `image` VARCHAR(255),
        `thumbnail` VARCHAR(255),
        `author_id` INT DEFAULT 1,
        `status` ENUM('draft', 'published', 'archived') DEFAULT 'draft',
        `featured` TINYINT(1) DEFAULT 0,
        `views` INT DEFAULT 0,
        `published_at` DATETIME,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_featured (featured)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    try {
        $pdo->exec($sql);
        echo "✅ Tabela criada com sucesso!<br>";
    } catch (PDOException $e) {
        echo "❌ Erro ao criar tabela: " . $e->getMessage() . "<br>";
    }
}

echo "<br><hr><br>";
echo "<a href='news.php'>Tentar acessar news.php novamente</a>";
?>
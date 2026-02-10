<?php
// admin/fix-news-table.php - Corrigir tabela news

echo "<h2>🔧 Corrigindo Tabela de Notícias</h2>";

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=gamedev_academy;charset=utf8mb4",
        "root",
        "",
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );
    echo "✅ Conectado ao banco<br><br>";
} catch (PDOException $e) {
    die("❌ Erro: " . $e->getMessage());
}

// Verificar se tabela existe
$tableExists = $pdo->query("SHOW TABLES LIKE 'news'")->rowCount() > 0;

if ($tableExists) {
    echo "📋 Tabela 'news' existe. Verificando colunas...<br><br>";
    
    // Obter colunas atuais
    $columns = $pdo->query("DESCRIBE news")->fetchAll(PDO::FETCH_COLUMN);
    
    // Colunas que precisam existir
    $fixes = [
        'status' => "ADD COLUMN `status` ENUM('draft', 'published', 'archived') DEFAULT 'draft'",
        'featured' => "ADD COLUMN `featured` TINYINT(1) DEFAULT 0",
        'views' => "ADD COLUMN `views` INT DEFAULT 0",
        'category' => "ADD COLUMN `category` VARCHAR(50) DEFAULT 'geral'",
        'tags' => "ADD COLUMN `tags` TEXT",
        'image' => "ADD COLUMN `image` VARCHAR(255)",
        'thumbnail' => "ADD COLUMN `thumbnail` VARCHAR(255)",
        'slug' => "ADD COLUMN `slug` VARCHAR(255)",
        'excerpt' => "ADD COLUMN `excerpt` TEXT",
        'author_id' => "ADD COLUMN `author_id` INT DEFAULT 1",
        'published_at' => "ADD COLUMN `published_at` DATETIME",
        'updated_at' => "ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
    ];
    
    foreach ($fixes as $column => $sql) {
        if (!in_array($column, $columns)) {
            echo "➕ Adicionando coluna '$column'... ";
            try {
                $pdo->exec("ALTER TABLE news $sql");
                echo "✅ OK<br>";
            } catch (PDOException $e) {
                echo "❌ Erro: " . $e->getMessage() . "<br>";
            }
        } else {
            echo "✅ Coluna '$column' já existe<br>";
        }
    }
    
} else {
    echo "📋 Tabela 'news' não existe. Criando...<br><br>";
    
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
        echo "❌ Erro: " . $e->getMessage() . "<br>";
    }
}

// Atualizar registros existentes
echo "<br>📝 Atualizando registros existentes...<br>";
try {
    $pdo->exec("UPDATE news SET status = 'published' WHERE status IS NULL");
    $pdo->exec("UPDATE news SET featured = 0 WHERE featured IS NULL");
    $pdo->exec("UPDATE news SET views = 0 WHERE views IS NULL");
    $pdo->exec("UPDATE news SET category = 'geral' WHERE category IS NULL OR category = ''");
    echo "✅ Registros atualizados<br>";
} catch (PDOException $e) {
    echo "⚠️ " . $e->getMessage() . "<br>";
}

// Inserir notícia de exemplo se não houver
$count = $pdo->query("SELECT COUNT(*) FROM news")->fetchColumn();
if ($count == 0) {
    echo "<br>📝 Inserindo notícia de exemplo...<br>";
    
    $sql = "INSERT INTO news (title, slug, content, excerpt, category, status, featured, published_at, author_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'Bem-vindo ao GameDev Academy',
        'bem-vindo-gamedev-academy',
        '<p>Esta é a primeira notícia do sistema GameDev Academy.</p>',
        'Conheça a plataforma de ensino de desenvolvimento de jogos.',
        'anuncios',
        'published',
        1,
        1
    ]);
    echo "✅ Notícia de exemplo inserida<br>";
}

// Verificar estrutura final
echo "<br><h3>Estrutura Final da Tabela:</h3>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr style='background:#e0e0e0;'><th>Coluna</th><th>Tipo</th><th>Default</th></tr>";

$columns = $pdo->query("DESCRIBE news")->fetchAll();
foreach ($columns as $col) {
    echo "<tr>";
    echo "<td>{$col['Field']}</td>";
    echo "<td>{$col['Type']}</td>";
    echo "<td>{$col['Default']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><br>";
echo "<div style='background:#d4edda; padding:20px; border-radius:10px;'>";
echo "<h2>✅ Correção Concluída!</h2>";
echo "<p><a href='news.php'><strong>→ Ir para Gerenciar Notícias</strong></a></p>";
echo "</div>";
?>
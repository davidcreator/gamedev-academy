<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Conexão
$host = 'localhost';
$dbname = 'gamedev_academy';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>🔄 Migração da Tabela NEWS</h1>";
    
    // 1. Fazer backup
    echo "<h2>Passo 1: Criando Backup</h2>";
    
    // Remover backup antigo se existir
    $pdo->exec("DROP TABLE IF EXISTS news_old");
    
    // Renomear tabela atual para backup
    $pdo->exec("RENAME TABLE news TO news_old");
    echo "✅ Tabela atual renomeada para 'news_old'<br><br>";
    
    // 2. Criar nova tabela com estrutura correta
    echo "<h2>Passo 2: Criando Nova Estrutura</h2>";
    
    $sql = "CREATE TABLE `news` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `slug` varchar(255) NOT NULL,
        `summary` text,
        `content` longtext NOT NULL,
        `image_url` varchar(500) DEFAULT NULL,
        `category` varchar(100) DEFAULT NULL,
        `tags` text,
        `author_id` int(11) DEFAULT NULL,
        `status` enum('draft','published','archived') DEFAULT 'draft',
        `featured` tinyint(1) DEFAULT '0',
        `views_count` int(11) DEFAULT '0',
        `published_at` datetime DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `slug` (`slug`),
        KEY `author_id` (`author_id`),
        KEY `idx_news_status_published` (`status`,`published_at`),
        KEY `idx_news_featured` (`featured`),
        CONSTRAINT `news_author_fk` FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✅ Nova tabela criada com estrutura correta<br><br>";
    
    // 3. Migrar dados
    echo "<h2>Passo 3: Migrando Dados</h2>";
    
    $migration_sql = "INSERT INTO news (
        id,
        title,
        slug,
        summary,
        content,
        image_url,
        category,
        tags,
        author_id,
        status,
        featured,
        views_count,
        published_at,
        created_at,
        updated_at
    )
    SELECT 
        id,
        title,
        slug,
        excerpt as summary,
        content,
        thumbnail as image_url,
        CASE 
            WHEN category IN ('update', 'tutorial', 'news', 'event', 'announcement') 
            THEN category 
            ELSE 'news' 
        END as category,
        tags,
        author_id,
        CASE 
            WHEN is_published = 1 THEN 'published'
            ELSE 'draft'
        END as status,
        is_featured as featured,
        views as views_count,
        CASE 
            WHEN is_published = 1 AND published_at IS NOT NULL THEN published_at
            WHEN is_published = 1 THEN created_at
            ELSE NULL
        END as published_at,
        created_at,
        updated_at
    FROM news_old";
    
    $pdo->exec($migration_sql);
    $count = $pdo->query("SELECT COUNT(*) FROM news")->fetchColumn();
    echo "✅ {$count} registros migrados com sucesso!<br><br>";
    
    // 4. Verificar migração
    echo "<h2>Passo 4: Verificando Migração</h2>";
    
    echo "<h3>Estrutura Nova:</h3>";
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
    
    $stmt = $pdo->query("DESCRIBE news");
    while ($column = $stmt->fetch()) {
        $highlight = $column['Field'] == 'status' ? 'style="background: #d4f4dd;"' : '';
        echo "<tr $highlight>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "</tr>";
    }
    echo "</table><br>";
    
    // 5. Estatísticas
    echo "<h2>Passo 5: Estatísticas</h2>";
    
    $stats = $pdo->query("
        SELECT 
            status, 
            COUNT(*) as total 
        FROM news 
        GROUP BY status
    ")->fetchAll();
    
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>Status</th><th>Quantidade</th></tr>";
    foreach ($stats as $stat) {
        echo "<tr>";
        echo "<td>{$stat['status']}</td>";
        echo "<td>{$stat['total']}</td>";
        echo "</tr>";
    }
    echo "</table><br>";
    
    // 6. Amostra de dados
    echo "<h2>Passo 6: Amostra dos Dados Migrados</h2>";
    
    $sample = $pdo->query("SELECT id, title, status, featured, views_count FROM news LIMIT 5")->fetchAll();
    
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Título</th><th>Status</th><th>Destaque</th><th>Views</th></tr>";
    foreach ($sample as $row) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td><span class='badge status-{$row['status']}'>{$row['status']}</span></td>";
        echo "<td>" . ($row['featured'] ? '⭐' : '-') . "</td>";
        echo "<td>{$row['views_count']}</td>";
        echo "</tr>";
    }
    echo "</table><br>";
    
    echo "<div class='success-box'>";
    echo "<h2>✅ Migração Concluída com Sucesso!</h2>";
    echo "<p>A tabela 'news' foi migrada para a nova estrutura.</p>";
    echo "<p>A tabela antiga foi preservada como 'news_old' (pode ser removida após verificação).</p>";
    echo "<div class='buttons'>";
    echo "<a href='news.php' class='btn btn-primary'>Ir para Notícias</a> ";
    echo "<a href='check-news-table.php' class='btn btn-secondary'>Verificar Estrutura</a>";
    echo "</div>";
    echo "</div>";
    
    // Opcional: Adicionar notícia de teste se não houver nenhuma
    if ($count == 0) {
        echo "<br><h3>Adicionando Notícia de Exemplo...</h3>";
        
        $stmt = $pdo->prepare("INSERT INTO news (title, slug, summary, content, category, status, featured, published_at) 
                              VALUES (:title, :slug, :summary, :content, :category, 'published', 1, NOW())");
        
        $stmt->execute([
            ':title' => 'Bem-vindo ao GameDev Academy',
            ':slug' => 'bem-vindo-gamedev-academy',
            ':summary' => 'Primeira notícia do sistema após migração.',
            ':content' => '<p>Esta é uma notícia de teste criada após a migração do banco de dados.</p>',
            ':category' => 'news'
        ]);
        
        echo "✅ Notícia de exemplo adicionada!<br>";
    }
    
} catch (PDOException $e) {
    echo "<div class='error-box'>";
    echo "<h2>❌ Erro na Migração</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    
    // Tentar reverter se possível
    if ($e->getCode() && strpos($e->getMessage(), 'news_old') === false) {
        echo "<h3>Tentando reverter...</h3>";
        try {
            $pdo->exec("DROP TABLE IF EXISTS news");
            $pdo->exec("RENAME TABLE news_old TO news");
            echo "✅ Revertido para tabela original<br>";
        } catch (PDOException $e2) {
            echo "❌ Não foi possível reverter: " . $e2->getMessage() . "<br>";
        }
    }
    echo "</div>";
}
?>

<style>
body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
}

h1 {
    background: white;
    color: #764ba2;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

h2 {
    background: white;
    color: #495057;
    padding: 15px;
    border-radius: 8px;
    margin-top: 25px;
    margin-bottom: 15px;
    border-left: 4px solid #764ba2;
}

h3 {
    color: #495057;
    margin-top: 20px;
}

table {
    background: white;
    width: 100%;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
}

table th {
    background: #764ba2;
    color: white;
    padding: 10px;
    text-align: left;
}

table td {
    padding: 8px 10px;
    border-bottom: 1px solid #e9ecef;
}

.success-box {
    background: white;
    border: 2px solid #28a745;
    border-radius: 10px;
    padding: 20px;
    margin-top: 30px;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.success-box h2 {
    color: #28a745;
    border: none;
    background: none;
}

.error-box {
    background: white;
    border: 2px solid #dc3545;
    border-radius: 10px;
    padding: 20px;
    margin-top: 30px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.error-box h2 {
    color: #dc3545;
    border: none;
    background: none;
}

pre {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    overflow-x: auto;
    border: 1px solid #dee2e6;
}

.btn {
    display: inline-block;
    padding: 12px 24px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: 600;
    transition: all 0.3s ease;
    margin: 5px;
}

.btn-primary {
    background: #764ba2;
    color: white;
}

.btn-primary:hover {
    background: #5a3785;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.buttons {
    margin-top: 20px;
}

.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-published {
    background: #28a745;
    color: white;
}

.status-draft {
    background: #ffc107;
    color: #000;
}

.status-archived {
    background: #6c757d;
    color: white;
}
</style>
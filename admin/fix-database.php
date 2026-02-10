<?php
// admin/fix-database.php - Corrigir estrutura do banco de dados

require_once '../config/database.php';

echo "<h2>🔧 Corrigindo Estrutura do Banco de Dados</h2>";

try {
    // Verificar estrutura atual da tabela
    echo "<h3>Estrutura Atual da Tabela 'news':</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Coluna</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
    
    $columns = $pdo->query("DESCRIBE news")->fetchAll();
    $existing_columns = [];
    
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
        $existing_columns[] = $col['Field'];
    }
    echo "</table><br>";
    
    // Colunas necessárias
    $required_columns = [
        'id' => true,
        'title' => true,
        'slug' => false,
        'content' => true,
        'excerpt' => false,
        'category' => false,
        'tags' => false,
        'image' => false,
        'thumbnail' => false,
        'author_id' => false,
        'status' => false,
        'featured' => false,
        'views' => false,
        'published_at' => false,
        'created_at' => false,
        'updated_at' => false
    ];
    
    echo "<h3>Verificando e Adicionando Colunas Faltantes:</h3>";
    
    // Adicionar coluna 'status' se não existir
    if (!in_array('status', $existing_columns)) {
        echo "➕ Adicionando coluna 'status'...<br>";
        $pdo->exec("ALTER TABLE news ADD COLUMN status ENUM('draft', 'published', 'archived') DEFAULT 'draft' AFTER author_id");
        echo "✅ Coluna 'status' adicionada!<br>";
    } else {
        echo "✅ Coluna 'status' já existe<br>";
    }
    
    // Adicionar coluna 'featured' se não existir
    if (!in_array('featured', $existing_columns)) {
        echo "➕ Adicionando coluna 'featured'...<br>";
        $pdo->exec("ALTER TABLE news ADD COLUMN featured BOOLEAN DEFAULT FALSE AFTER status");
        echo "✅ Coluna 'featured' adicionada!<br>";
    } else {
        echo "✅ Coluna 'featured' já existe<br>";
    }
    
    // Adicionar coluna 'views' se não existir
    if (!in_array('views', $existing_columns)) {
        echo "➕ Adicionando coluna 'views'...<br>";
        $pdo->exec("ALTER TABLE news ADD COLUMN views INT DEFAULT 0 AFTER featured");
        echo "✅ Coluna 'views' adicionada!<br>";
    } else {
        echo "✅ Coluna 'views' já existe<br>";
    }
    
    // Adicionar coluna 'category' se não existir
    if (!in_array('category', $existing_columns)) {
        echo "➕ Adicionando coluna 'category'...<br>";
        $pdo->exec("ALTER TABLE news ADD COLUMN category VARCHAR(50) DEFAULT 'geral' AFTER excerpt");
        echo "✅ Coluna 'category' adicionada!<br>";
    } else {
        echo "✅ Coluna 'category' já existe<br>";
    }
    
    // Adicionar coluna 'tags' se não existir
    if (!in_array('tags', $existing_columns)) {
        echo "➕ Adicionando coluna 'tags'...<br>";
        $pdo->exec("ALTER TABLE news ADD COLUMN tags TEXT AFTER category");
        echo "✅ Coluna 'tags' adicionada!<br>";
    } else {
        echo "✅ Coluna 'tags' já existe<br>";
    }
    
    // Adicionar coluna 'image' se não existir
    if (!in_array('image', $existing_columns)) {
        echo "➕ Adicionando coluna 'image'...<br>";
        $pdo->exec("ALTER TABLE news ADD COLUMN image VARCHAR(255) AFTER tags");
        echo "✅ Coluna 'image' adicionada!<br>";
    } else {
        echo "✅ Coluna 'image' já existe<br>";
    }
    
    // Adicionar coluna 'thumbnail' se não existir
    if (!in_array('thumbnail', $existing_columns)) {
        echo "➕ Adicionando coluna 'thumbnail'...<br>";
        $pdo->exec("ALTER TABLE news ADD COLUMN thumbnail VARCHAR(255) AFTER image");
        echo "✅ Coluna 'thumbnail' adicionada!<br>";
    } else {
        echo "✅ Coluna 'thumbnail' já existe<br>";
    }
    
    // Adicionar coluna 'slug' se não existir
    if (!in_array('slug', $existing_columns)) {
        echo "➕ Adicionando coluna 'slug'...<br>";
        $pdo->exec("ALTER TABLE news ADD COLUMN slug VARCHAR(255) UNIQUE AFTER title");
        echo "✅ Coluna 'slug' adicionada!<br>";
        
        // Gerar slugs para registros existentes
        $news = $pdo->query("SELECT id, title FROM news WHERE slug IS NULL")->fetchAll();
        foreach ($news as $item) {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($item['title'])));
            $pdo->prepare("UPDATE news SET slug = ? WHERE id = ?")->execute([$slug, $item['id']]);
        }
        echo "✅ Slugs gerados para notícias existentes!<br>";
    } else {
        echo "✅ Coluna 'slug' já existe<br>";
    }
    
    // Adicionar coluna 'published_at' se não existir
    if (!in_array('published_at', $existing_columns)) {
        echo "➕ Adicionando coluna 'published_at'...<br>";
        $pdo->exec("ALTER TABLE news ADD COLUMN published_at DATETIME AFTER views");
        echo "✅ Coluna 'published_at' adicionada!<br>";
    } else {
        echo "✅ Coluna 'published_at' já existe<br>";
    }
    
    // Adicionar coluna 'updated_at' se não existir
    if (!in_array('updated_at', $existing_columns)) {
        echo "➕ Adicionando coluna 'updated_at'...<br>";
        $pdo->exec("ALTER TABLE news ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
        echo "✅ Coluna 'updated_at' adicionada!<br>";
    } else {
        echo "✅ Coluna 'updated_at' já existe<br>";
    }
    
    // Adicionar índices
    echo "<br><h3>Verificando Índices:</h3>";
    
    try {
        $pdo->exec("CREATE INDEX idx_status ON news(status)");
        echo "✅ Índice 'idx_status' criado<br>";
    } catch (PDOException $e) {
        echo "ℹ️ Índice 'idx_status' já existe<br>";
    }
    
    try {
        $pdo->exec("CREATE INDEX idx_slug ON news(slug)");
        echo "✅ Índice 'idx_slug' criado<br>";
    } catch (PDOException $e) {
        echo "ℹ️ Índice 'idx_slug' já existe<br>";
    }
    
    // Atualizar registros existentes que não têm status
    echo "<br><h3>Atualizando Dados Existentes:</h3>";
    $updated = $pdo->exec("UPDATE news SET status = 'published' WHERE status IS NULL");
    echo "✅ {$updated} registros atualizados com status 'published'<br>";
    
    // Inserir notícia de exemplo se não houver nenhuma
    $count = $pdo->query("SELECT COUNT(*) FROM news")->fetchColumn();
    
    if ($count == 0) {
        echo "<br><h3>Inserindo Notícias de Exemplo:</h3>";
        
        $examples = [
            [
                'title' => 'Bem-vindo ao GameDev Academy',
                'slug' => 'bem-vindo-gamedev-academy',
                'content' => '<h2>Bem-vindo!</h2><p>Esta é a primeira notícia do sistema GameDev Academy.</p><p>Aqui você encontrará as últimas novidades sobre desenvolvimento de jogos, tutoriais e muito mais!</p>',
                'excerpt' => 'Conheça a plataforma GameDev Academy',
                'category' => 'anuncios',
                'status' => 'published',
                'featured' => 1
            ],
            [
                'title' => 'Tutorial: Como Começar no Desenvolvimento de Jogos',
                'slug' => 'tutorial-comecar-desenvolvimento-jogos',
                'content' => '<h2>Primeiros Passos</h2><p>Neste tutorial você aprenderá os conceitos básicos para começar no desenvolvimento de jogos.</p>',
                'excerpt' => 'Guia completo para iniciantes',
                'category' => 'tutoriais',
                'status' => 'published',
                'featured' => 0
            ],
            [
                'title' => 'Novo Curso de Unity Disponível',
                'slug' => 'novo-curso-unity',
                'content' => '<h2>Aprenda Unity</h2><p>Acabamos de lançar um novo curso completo de Unity!</p>',
                'excerpt' => 'Curso completo de Unity do básico ao avançado',
                'category' => 'cursos',
                'status' => 'published',
                'featured' => 1
            ]
        ];
        
        $sql = "INSERT INTO news (title, slug, content, excerpt, category, status, featured, published_at, author_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 1)";
        
        $stmt = $pdo->prepare($sql);
        
        foreach ($examples as $example) {
            $stmt->execute([
                $example['title'],
                $example['slug'],
                $example['content'],
                $example['excerpt'],
                $example['category'],
                $example['status'],
                $example['featured']
            ]);
            echo "✅ Notícia inserida: {$example['title']}<br>";
        }
    } else {
        echo "<br>ℹ️ Já existem {$count} notícias no sistema<br>";
    }
    
    echo "<br><h3>Nova Estrutura da Tabela 'news':</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Coluna</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
    
    $columns = $pdo->query("DESCRIBE news")->fetchAll();
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><br>";
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; color: #155724;'>";
    echo "<h2>✅ Banco de dados corrigido com sucesso!</h2>";
    echo "<p><strong><a href='news.php' style='color: #155724;'>→ Ir para Gerenciar Notícias</a></strong></p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; color: #721c24;'>";
    echo "<h3>❌ Erro:</h3>";
    echo "<p>{$e->getMessage()}</p>";
    echo "</div>";
}
?>
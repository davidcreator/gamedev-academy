<?php
// classes/News.php - Modelo de Notícias

class News {
    private $db;
    private $table = 'news';
    
    public function __construct($database = null) {
        if ($database) {
            $this->db = $database;
        } else {
            // Tentar obter conexão de diferentes formas
            $this->initializeDatabase();
        }
    }
    
    /**
     * Inicializar conexão com banco de dados
     */
    private function initializeDatabase() {
        global $pdo, $conn, $db;
        
        // Tentar usar variável global existente
        if (isset($pdo) && $pdo instanceof PDO) {
            $this->db = $pdo;
        } elseif (isset($conn) && $conn instanceof PDO) {
            $this->db = $conn;
        } elseif (isset($db) && $db instanceof PDO) {
            $this->db = $db;
        } else {
            // Se não houver conexão global, criar uma nova
            $this->createConnection();
        }
    }
    
    /**
     * Criar nova conexão com banco de dados
     */
    private function createConnection() {
        // Verificar se o arquivo de configuração existe
        $configFile = dirname(__DIR__) . '/config/database.php';
        
        if (file_exists($configFile)) {
            require_once $configFile;
            
            // Após incluir o arquivo, verificar novamente as variáveis globais
            global $pdo, $conn;
            
            if (isset($pdo) && $pdo instanceof PDO) {
                $this->db = $pdo;
            } elseif (isset($conn) && $conn instanceof PDO) {
                $this->db = $conn;
            }
        }
        
        // Se ainda não tiver conexão, criar uma nova
        if (!$this->db) {
            try {
                // Usar constantes ou valores padrão
                $host = defined('DB_HOST') ? DB_HOST : 'localhost';
                $dbname = defined('DB_NAME') ? DB_NAME : 'gamedev_academy';
                $user = defined('DB_USER') ? DB_USER : 'root';
                $pass = defined('DB_PASS') ? DB_PASS : '';
                $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';
                
                $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
                
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                
                $this->db = new PDO($dsn, $user, $pass, $options);
                
            } catch (PDOException $e) {
                throw new Exception("Erro de conexão com banco de dados: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Verificar se a conexão está ativa
     */
    private function checkConnection() {
        if (!$this->db) {
            $this->initializeDatabase();
        }
        
        if (!$this->db) {
            throw new Exception("Não foi possível estabelecer conexão com o banco de dados");
        }
    }
    
    /**
     * Obter todas as notícias com paginação
     */
    public function getAll($page = 1, $perPage = 12, $category = null, $search = null) {
        $this->checkConnection();
        
        $offset = ($page - 1) * $perPage;
        $where = ['status = :status'];
        $params = ['status' => 'published'];
        
        if ($category) {
            $where[] = 'category = :category';
            $params['category'] = $category;
        }
        
        if ($search) {
            $where[] = '(title LIKE :search OR content LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Verificar se as tabelas relacionadas existem
        $sql = "SELECT n.*";
        
        // Adicionar joins apenas se as tabelas existirem
        if ($this->tableExists('users')) {
            $sql .= ", u.username as author_name, u.avatar as author_avatar";
        }
        
        if ($this->tableExists('news_views')) {
            $sql .= ", (SELECT COUNT(*) FROM news_views WHERE news_id = n.id) as views_count";
        }
        
        if ($this->tableExists('news_comments')) {
            $sql .= ", (SELECT COUNT(*) FROM news_comments WHERE news_id = n.id AND status = 'approved') as comments_count";
        }
        
        $sql .= " FROM {$this->table} n";
        
        if ($this->tableExists('users')) {
            $sql .= " LEFT JOIN users u ON n.author_id = u.id";
        }
        
        $sql .= " WHERE {$whereClause}
                  ORDER BY n.published_at DESC
                  LIMIT :offset, :limit";
        
        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao obter notícias: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter notícias mais recentes
     */
    public function getLatest($limit = 6) {
        $this->checkConnection();
        
        try {
            $sql = "SELECT n.*";
            
            if ($this->tableExists('users')) {
                $sql .= ", u.username as author_name, u.avatar as author_avatar";
            }
            
            if ($this->tableExists('news_views')) {
                $sql .= ", (SELECT COUNT(*) FROM news_views WHERE news_id = n.id) as views_count";
            }
            
            $sql .= " FROM {$this->table} n";
            
            if ($this->tableExists('users')) {
                $sql .= " LEFT JOIN users u ON n.author_id = u.id";
            }
            
            $sql .= " WHERE n.status = 'published' 
                      AND (n.published_at IS NULL OR n.published_at <= NOW())
                      ORDER BY n.published_at DESC, n.created_at DESC
                      LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao obter notícias recentes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter notícias em destaque
     */
    public function getFeatured($limit = 3) {
        $this->checkConnection();
        
        try {
            // Verificar se a coluna 'featured' existe
            if (!$this->columnExists($this->table, 'featured')) {
                return $this->getLatest($limit); // Fallback para últimas notícias
            }
            
            $sql = "SELECT n.*";
            
            if ($this->tableExists('users')) {
                $sql .= ", u.username as author_name, u.avatar as author_avatar";
            }
            
            $sql .= " FROM {$this->table} n";
            
            if ($this->tableExists('users')) {
                $sql .= " LEFT JOIN users u ON n.author_id = u.id";
            }
            
            $sql .= " WHERE n.status = 'published' 
                      AND n.featured = 1
                      AND (n.published_at IS NULL OR n.published_at <= NOW())
                      ORDER BY n.published_at DESC, n.created_at DESC
                      LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao obter notícias em destaque: " . $e->getMessage());
            return $this->getLatest($limit); // Fallback
        }
    }
    
    /**
     * Obter notícias relacionadas
     */
    public function getRelated($newsId, $category, $limit = 4) {
        $this->checkConnection();
        
        try {
            $sql = "SELECT n.*";
            
            if ($this->tableExists('users')) {
                $sql .= ", u.username as author_name";
            }
            
            $sql .= " FROM {$this->table} n";
            
            if ($this->tableExists('users')) {
                $sql .= " LEFT JOIN users u ON n.author_id = u.id";
            }
            
            $sql .= " WHERE n.status = 'published' 
                      AND n.id != :news_id
                      AND n.category = :category
                      AND (n.published_at IS NULL OR n.published_at <= NOW())
                      ORDER BY n.published_at DESC, n.created_at DESC
                      LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue('news_id', $newsId, PDO::PARAM_INT);
            $stmt->bindValue('category', $category);
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao obter notícias relacionadas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter uma notícia pelo ID ou slug
     */
    public function getById($id) {
        $this->checkConnection();
        
        try {
            $sql = "SELECT n.*";
            
            if ($this->tableExists('users')) {
                $sql .= ", u.username as author_name, u.avatar as author_avatar, u.bio as author_bio";
            }
            
            if ($this->tableExists('news_views')) {
                $sql .= ", (SELECT COUNT(*) FROM news_views WHERE news_id = n.id) as views_count";
            }
            
            if ($this->tableExists('news_comments')) {
                $sql .= ", (SELECT COUNT(*) FROM news_comments WHERE news_id = n.id AND status = 'approved') as comments_count";
            }
            
            $sql .= " FROM {$this->table} n";
            
            if ($this->tableExists('users')) {
                $sql .= " LEFT JOIN users u ON n.author_id = u.id";
            }
            
            // Verificar se a coluna slug existe
            if ($this->columnExists($this->table, 'slug')) {
                $sql .= " WHERE n.id = :id OR n.slug = :slug";
            } else {
                $sql .= " WHERE n.id = :id";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue('id', is_numeric($id) ? $id : 0, PDO::PARAM_INT);
            
            if ($this->columnExists($this->table, 'slug')) {
                $stmt->bindValue('slug', $id);
            }
            
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao obter notícia: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Criar nova notícia
     */
    public function create($data) {
        $this->checkConnection();
        
        try {
            // Campos básicos que devem existir
            $fields = ['title', 'content'];
            $values = [':title', ':content'];
            $params = [
                'title' => $data['title'],
                'content' => $data['content']
            ];
            
            // Campos opcionais
            $optionalFields = [
                'slug', 'excerpt', 'category', 'tags', 'image', 'thumbnail',
                'author_id', 'status', 'featured', 'published_at', 
                'meta_title', 'meta_description'
            ];
            
            foreach ($optionalFields as $field) {
                if (isset($data[$field]) && $this->columnExists($this->table, $field)) {
                    $fields[] = $field;
                    $values[] = ':' . $field;
                    $params[$field] = $data[$field];
                }
            }
            
            // Adicionar created_at se existir
            if ($this->columnExists($this->table, 'created_at')) {
                $fields[] = 'created_at';
                $values[] = 'NOW()';
            }
            
            $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                    VALUES (" . implode(', ', $values) . ")";
            
            $stmt = $this->db->prepare($sql);
            
            // Gerar slug se não fornecido e a coluna existir
            if ($this->columnExists($this->table, 'slug') && empty($params['slug'])) {
                $params['slug'] = $this->generateSlug($params['title']);
            }
            
            // Definir data de publicação se necessário
            if (isset($params['status']) && $params['status'] == 'published' && 
                empty($params['published_at']) && $this->columnExists($this->table, 'published_at')) {
                $params['published_at'] = date('Y-m-d H:i:s');
            }
            
            return $stmt->execute($params);
            
        } catch (PDOException $e) {
            error_log("Erro ao criar notícia: " . $e->getMessage());
            throw new Exception("Erro ao criar notícia: " . $e->getMessage());
        }
    }
    
    /**
     * Atualizar notícia
     */
    public function update($id, $data) {
        $this->checkConnection();
        
        try {
            $fields = [];
            $params = ['id' => $id];
            
            foreach ($data as $key => $value) {
                if ($key != 'id' && $this->columnExists($this->table, $key)) {
                    $fields[] = "{$key} = :{$key}";
                    $params[$key] = $value;
                }
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $fields);
            
            // Adicionar updated_at se existir
            if ($this->columnExists($this->table, 'updated_at')) {
                $sql .= ", updated_at = NOW()";
            }
            
            $sql .= " WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
            
        } catch (PDOException $e) {
            error_log("Erro ao atualizar notícia: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Deletar notícia
     */
    public function delete($id) {
        $this->checkConnection();
        
        try {
            // Verificar se tem coluna de soft delete
            if ($this->columnExists($this->table, 'deleted_at')) {
                $sql = "UPDATE {$this->table} SET ";
                
                if ($this->columnExists($this->table, 'status')) {
                    $sql .= "status = 'deleted', ";
                }
                
                $sql .= "deleted_at = NOW() WHERE id = :id";
            } else {
                // Hard delete
                $sql = "DELETE FROM {$this->table} WHERE id = :id";
            }
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id' => $id]);
            
        } catch (PDOException $e) {
            error_log("Erro ao deletar notícia: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Registrar visualização
     */
    public function registerView($newsId, $userId = null, $ip = null) {
        $this->checkConnection();
        
        try {
            // Verificar se a tabela de views existe
            if ($this->tableExists('news_views')) {
                $sql = "INSERT INTO news_views (news_id, user_id, ip_address, viewed_at) 
                        VALUES (:news_id, :user_id, :ip, NOW())";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'news_id' => $newsId,
                    'user_id' => $userId,
                    'ip' => $ip ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
                ]);
            }
            
            // Atualizar contador na tabela principal se a coluna existir
            if ($this->columnExists($this->table, 'views')) {
                $sql = "UPDATE {$this->table} SET views = views + 1 WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['id' => $newsId]);
            }
            
        } catch (PDOException $e) {
            error_log("Erro ao registrar visualização: " . $e->getMessage());
        }
    }
    
    /**
     * Obter categorias disponíveis
     */
    public function getCategories() {
        return [
            'updates' => 'Atualizações',
            'tutorials' => 'Tutoriais',
            'industry' => 'Indústria',
            'events' => 'Eventos',
            'reviews' => 'Reviews',
            'interviews' => 'Entrevistas',
            'tips' => 'Dicas',
            'resources' => 'Recursos'
        ];
    }
    
    /**
     * Contar total de notícias
     */
    public function count($status = 'published') {
        $this->checkConnection();
        
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table}";
            
            if ($status !== 'all' && $this->columnExists($this->table, 'status')) {
                $sql .= " WHERE status = :status";
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['status' => $status]);
            } else {
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
            }
            
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erro ao contar notícias: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Gerar slug único
     */
    private function generateSlug($title) {
        // Remover acentos
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $title);
        // Converter para lowercase e substituir espaços por hífens
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $slug)));
        // Remover hífens múltiplos
        $slug = preg_replace('/-+/', '-', $slug);
        // Remover hífens do início e fim
        $slug = trim($slug, '-');
        
        try {
            // Verificar se já existe
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE slug = :slug";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['slug' => $slug]);
            
            if ($stmt->fetchColumn() > 0) {
                // Adicionar timestamp para tornar único
                $slug .= '-' . time();
            }
        } catch (PDOException $e) {
            // Em caso de erro, adicionar timestamp por segurança
            $slug .= '-' . time();
        }
        
        return $slug;
    }
    
    /**
     * Buscar notícias
     */
    public function search($query, $limit = 10) {
        $this->checkConnection();
        
        try {
            $sql = "SELECT n.*";
            
            if ($this->tableExists('users')) {
                $sql .= ", u.username as author_name";
            }
            
            $sql .= " FROM {$this->table} n";
            
            if ($this->tableExists('users')) {
                $sql .= " LEFT JOIN users u ON n.author_id = u.id";
            }
            
            $sql .= " WHERE n.status = 'published'
                      AND (n.title LIKE :query";
            
            if ($this->columnExists($this->table, 'content')) {
                $sql .= " OR n.content LIKE :query";
            }
            
            if ($this->columnExists($this->table, 'tags')) {
                $sql .= " OR n.tags LIKE :query";
            }
            
            $sql .= ") ORDER BY 
                        CASE 
                            WHEN n.title LIKE :exact THEN 1
                            WHEN n.title LIKE :start THEN 2
                            ELSE 3
                        END,
                        n.created_at DESC
                      LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue('query', '%' . $query . '%');
            $stmt->bindValue('exact', $query);
            $stmt->bindValue('start', $query . '%');
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar notícias: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Verificar se uma tabela existe
     */
    private function tableExists($tableName) {
        try {
            $result = $this->db->query("SHOW TABLES LIKE '{$tableName}'");
            return $result->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Verificar se uma coluna existe na tabela
     */
    private function columnExists($tableName, $columnName) {
        try {
            $result = $this->db->query("SHOW COLUMNS FROM {$tableName} LIKE '{$columnName}'");
            return $result->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
}
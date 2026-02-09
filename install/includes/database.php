<?php
/**
 * Funções de banco de dados
 */

if (!defined('INSTALLER')) {
    die('Acesso negado');
}

/**
 * Classe para gerenciar banco de dados
 */
class DatabaseManager {
    
    private $connection = null;
    private $host;
    private $user;
    private $pass;
    private $name;
    private $port;
    private $prefix;
    private $charset = 'utf8mb4';
    
    /**
     * Construtor
     */
    public function __construct($config = []) {
        if (!empty($config)) {
            $this->setConfig($config);
        }
    }
    
    /**
     * Define configurações
     */
    public function setConfig($config) {
        $this->host = $config['host'] ?? 'localhost';
        $this->user = $config['user'] ?? '';
        $this->pass = $config['pass'] ?? '';
        $this->name = $config['name'] ?? '';
        $this->port = $config['port'] ?? 3306;
        $this->prefix = $config['prefix'] ?? '';
    }
    
    /**
     * Testa conexão
     */
    public function testConnection($createDb = false) {
        try {
            // Tentar conectar sem selecionar banco
            $mysqli = @new mysqli(
                $this->host,
                $this->user,
                $this->pass,
                '', // Sem banco inicialmente
                $this->port
            );
            
            if ($mysqli->connect_error) {
                return [
                    'success' => false,
                    'message' => 'Erro de conexão: ' . $mysqli->connect_error,
                    'code' => $mysqli->connect_errno
                ];
            }
            
            // Definir charset
            $mysqli->set_charset($this->charset);
            
            // Verificar se o banco existe
            if ($this->name) {
                $result = $mysqli->query("SHOW DATABASES LIKE '{$this->name}'");
                $exists = $result && $result->num_rows > 0;
                
                if (!$exists && $createDb) {
                    // Tentar criar o banco
                    $sql = "CREATE DATABASE IF NOT EXISTS `{$this->name}` 
                            CHARACTER SET {$this->charset} 
                            COLLATE {$this->charset}_unicode_ci";
                    
                    if (!$mysqli->query($sql)) {
                        return [
                            'success' => false,
                            'message' => 'Não foi possível criar o banco de dados: ' . $mysqli->error
                        ];
                    }
                    
                    $exists = true;
                } elseif (!$exists) {
                    return [
                        'success' => false,
                        'message' => "Banco de dados '{$this->name}' não existe"
                    ];
                }
                
                // Selecionar o banco
                if (!$mysqli->select_db($this->name)) {
                    return [
                        'success' => false,
                        'message' => 'Não foi possível selecionar o banco de dados'
                    ];
                }
            }
            
            // Verificar privilégios
            $privileges = $this->checkPrivileges($mysqli);
            
            $mysqli->close();
            
            return [
                'success' => true,
                'message' => 'Conexão estabelecida com sucesso',
                'privileges' => $privileges
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Verifica privilégios do usuário
     */
    private function checkPrivileges($mysqli) {
        $required = ['SELECT', 'INSERT', 'UPDATE', 'DELETE', 'CREATE', 'DROP', 'ALTER', 'INDEX'];
        $privileges = [];
        
        $result = $mysqli->query("SHOW GRANTS FOR CURRENT_USER");
        if ($result) {
            while ($row = $result->fetch_array()) {
                $grant = $row[0];
                
                // Verificar se tem ALL PRIVILEGES
                if (stripos($grant, 'ALL PRIVILEGES') !== false) {
                    foreach ($required as $priv) {
                        $privileges[$priv] = true;
                    }
                    break;
                }
                
                // Verificar privilégios individuais
                foreach ($required as $priv) {
                    if (stripos($grant, $priv) !== false) {
                        $privileges[$priv] = true;
                    }
                }
            }
        }
        
        // Verificar se tem todos os privilégios necessários
        $missing = [];
        foreach ($required as $priv) {
            if (!isset($privileges[$priv])) {
                $missing[] = $priv;
            }
        }
        
        return [
            'has_all' => empty($missing),
            'missing' => $missing,
            'granted' => array_keys($privileges)
        ];
    }
    
    /**
     * Conecta ao banco
     */
    public function connect() {
        if ($this->connection !== null) {
            return $this->connection;
        }
        
        $this->connection = @new mysqli(
            $this->host,
            $this->user,
            $this->pass,
            $this->name,
            $this->port
        );
        
        if ($this->connection->connect_error) {
            throw new Exception('Conexão falhou: ' . $this->connection->connect_error);
        }
        
        $this->connection->set_charset($this->charset);
        
        return $this->connection;
    }
    
    /**
     * Executa arquivo SQL
     */
    public function executeFile($filepath, $replace_prefix = true) {
        if (!file_exists($filepath)) {
            return [
                'success' => false,
                'message' => 'Arquivo SQL não encontrado: ' . $filepath
            ];
        }
        
        $sql = file_get_contents($filepath);
        
        if ($replace_prefix && $this->prefix) {
            $sql = str_replace('prefix_', $this->prefix, $sql);
        }
        
        return $this->executeSQL($sql);
    }
    
    /**
     * Executa SQL
     */
    public function executeSQL($sql) {
        $mysqli = $this->connect();
        
        // Remove comentários SQL
        $sql = $this->removeComments($sql);
        
        // Divide em statements
        $statements = $this->splitSQL($sql);
        
        $errors = [];
        $executed = 0;
        $total = count($statements);
        
        // Desabilitar foreign keys temporariamente
        $mysqli->query('SET FOREIGN_KEY_CHECKS = 0');
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement)) continue;
            
            if ($mysqli->query($statement)) {
                $executed++;
            } else {
                $errors[] = [
                    'statement' => substr($statement, 0, 100) . '...',
                    'error' => $mysqli->error,
                    'errno' => $mysqli->errno
                ];
                
                // Se for erro crítico, parar
                if (in_array($mysqli->errno, [1044, 1045, 1049, 2002, 2003])) {
                    break;
                }
            }
        }
        
        // Reabilitar foreign keys
        $mysqli->query('SET FOREIGN_KEY_CHECKS = 1');
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Alguns comandos falharam',
                'executed' => $executed,
                'total' => $total,
                'errors' => $errors
            ];
        }
        
        return [
            'success' => true,
            'message' => "Executados {$executed} de {$total} comandos com sucesso",
            'executed' => $executed,
            'total' => $total
        ];
    }
    
    /**
     * Remove comentários SQL
     */
    private function removeComments($sql) {
        // Remove comentários de linha
        $sql = preg_replace('/^\s*--.*$/m', '', $sql);
        $sql = preg_replace('/^\s*#.*$/m', '', $sql);
        
        // Remove comentários de bloco
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        return $sql;
    }
    
    /**
     * Divide SQL em statements
     */
    private function splitSQL($sql) {
        $statements = [];
        $current = '';
        $in_string = false;
        $string_char = '';
        $escaped = false;
        
        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];
            $current .= $char;
            
            // Verificar strings
            if (!$escaped) {
                if (!$in_string && ($char === '"' || $char === "'")) {
                    $in_string = true;
                    $string_char = $char;
                } elseif ($in_string && $char === $string_char) {
                    $in_string = false;
                }
            }
            
            // Verificar escape
            $escaped = !$escaped && $char === '\\';
            
            // Verificar fim de statement
            if (!$in_string && $char === ';') {
                $statements[] = trim($current);
                $current = '';
            }
        }
        
        if (!empty(trim($current))) {
            $statements[] = trim($current);
        }
        
        return array_filter($statements);
    }
    
    /**
     * Verifica se tabela existe
     */
    public function tableExists($table) {
        $mysqli = $this->connect();
        $table = $this->prefix . $table;
        
        $result = $mysqli->query("SHOW TABLES LIKE '{$table}'");
        return $result && $result->num_rows > 0;
    }
    
    /**
     * Conta registros em tabela
     */
    public function countRecords($table) {
        $mysqli = $this->connect();
        $table = $this->prefix . $table;
        
        $result = $mysqli->query("SELECT COUNT(*) as count FROM `{$table}`");
        if ($result) {
            $row = $result->fetch_assoc();
            return (int)$row['count'];
        }
        
        return 0;
    }
    
    /**
     * Insere dados
     */
    public function insert($table, $data) {
        $mysqli = $this->connect();
        $table = $this->prefix . $table;
        
        $fields = array_keys($data);
        $values = array_values($data);
        
        $fields_str = '`' . implode('`, `', $fields) . '`';
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        
        $sql = "INSERT INTO `{$table}` ({$fields_str}) VALUES ({$placeholders})";
        
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            return [
                'success' => false,
                'message' => 'Erro ao preparar query: ' . $mysqli->error
            ];
        }
        
        // Bind parameters dinamicamente
        $types = '';
        foreach ($values as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        
        $stmt->bind_param($types, ...$values);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'id' => $mysqli->insert_id
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Erro ao inserir: ' . $stmt->error
        ];
    }
    
    /**
     * Atualiza dados
     */
    public function update($table, $data, $where) {
        $mysqli = $this->connect();
        $table = $this->prefix . $table;
        
        $set_parts = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $set_parts[] = "`{$field}` = ?";
            $values[] = $value;
        }
        
        $set_str = implode(', ', $set_parts);
        
        // Construir WHERE
        $where_parts = [];
        foreach ($where as $field => $value) {
            $where_parts[] = "`{$field}` = ?";
            $values[] = $value;
        }
        
        $where_str = implode(' AND ', $where_parts);
        
        $sql = "UPDATE `{$table}` SET {$set_str} WHERE {$where_str}";
        
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            return [
                'success' => false,
                'message' => 'Erro ao preparar query: ' . $mysqli->error
            ];
        }
        
        // Bind parameters
        $types = str_repeat('s', count($values));
        $stmt->bind_param($types, ...$values);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'affected' => $stmt->affected_rows
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Erro ao atualizar: ' . $stmt->error
        ];
    }
    
    /**
     * Fecha conexão
     */
    public function close() {
        if ($this->connection !== null) {
            $this->connection->close();
            $this->connection = null;
        }
    }
    
    /**
     * Destrutor
     */
    public function __destruct() {
        $this->close();
    }
}
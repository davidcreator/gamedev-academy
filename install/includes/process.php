<?php
/**
 * Processamento de formulários do instalador
 */

if (!defined('INSTALLER')) {
    die('Acesso negado');
}

// ... (outras funções permanecem iguais) ...

/**
 * Processa Step 4 - Configuração do Admin (CORRIGIDO)
 */
function process_step_4($data) {
    // Verificar se passou pelos steps anteriores
    if (!isset($_SESSION['db_config']) || !isset($_SESSION['tables_created'])) {
        return [
            'success' => false,
            'message' => 'Complete as etapas anteriores primeiro'
        ];
    }
    
    $errors = [];
    
    // Validar campos obrigatórios
    $required = ['admin_username', 'admin_email', 'admin_password', 'admin_password_confirm'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            $errors[] = "Campo obrigatório: " . str_replace('admin_', '', str_replace('_', ' ', $field));
        }
    }
    
    // Validar formato do email
    if (!empty($data['admin_email']) && !filter_var($data['admin_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido";
    }
    
    // Validar senhas
    if (!empty($data['admin_password'])) {
        if (strlen($data['admin_password']) < 8) {
            $errors[] = "A senha deve ter no mínimo 8 caracteres";
        }
        
        if ($data['admin_password'] !== $data['admin_password_confirm']) {
            $errors[] = "As senhas não coincidem";
        }
    }
    
    // Validar username
    if (!empty($data['admin_username'])) {
        if (strlen($data['admin_username']) < 3) {
            $errors[] = "Nome de usuário deve ter no mínimo 3 caracteres";
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $data['admin_username'])) {
            $errors[] = "Nome de usuário pode conter apenas letras, números e underscore";
        }
    }
    
    if (!empty($errors)) {
        return [
            'success' => false,
            'message' => implode(', ', $errors)
        ];
    }
    
    // Conectar ao banco usando PDO
    $config = $_SESSION['db_config'];
    
    try {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $config['host'],
            $config['port'],
            $config['name']
        );
        
        $pdo = new PDO($dsn, $config['user'], $config['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Nome da tabela com prefixo
        $table = $config['prefix'] . 'users';
        
        // Primeiro vamos descobrir quais colunas existem na tabela
        $columns_query = $pdo->query("SHOW COLUMNS FROM `{$table}`");
        $existing_columns = [];
        while ($row = $columns_query->fetch(PDO::FETCH_ASSOC)) {
            $existing_columns[] = $row['Field'];
        }
        
        // Verificar se já existe admin
        $check = $pdo->prepare("SELECT id FROM `{$table}` WHERE username = :username OR email = :email LIMIT 1");
        $check->execute([
            ':username' => $data['admin_username'],
            ':email' => $data['admin_email']
        ]);
        
        // Preparar dados básicos que devem existir em qualquer sistema
        $admin_data = [
            'username' => $data['admin_username'],
            'email' => $data['admin_email'],
            'password' => password_hash($data['admin_password'], PASSWORD_DEFAULT),
            'name' => $data['admin_name'] ?? $data['admin_username'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Adicionar campos opcionais se existirem na tabela
        if (in_array('display_name', $existing_columns)) {
            $admin_data['display_name'] = $data['admin_name'] ?? $data['admin_username'];
        }
        
        if (in_array('role', $existing_columns)) {
            $admin_data['role'] = 'admin';
        } elseif (in_array('user_role', $existing_columns)) {
            $admin_data['user_role'] = 'admin';
        } elseif (in_array('role_id', $existing_columns)) {
            // Buscar ID da role admin
            $role_table = $config['prefix'] . 'roles';
            $role_stmt = $pdo->query("SELECT id FROM `{$role_table}` WHERE name = 'admin' OR slug = 'admin' LIMIT 1");
            $role = $role_stmt->fetch();
            $admin_data['role_id'] = $role ? $role['id'] : 1;
        }
        
        if (in_array('is_active', $existing_columns)) {
            $admin_data['is_active'] = 1;
        } elseif (in_array('active', $existing_columns)) {
            $admin_data['active'] = 1;
        } elseif (in_array('status', $existing_columns)) {
            $admin_data['status'] = 'active';
        }
        
        if (in_array('updated_at', $existing_columns)) {
            $admin_data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        if (in_array('is_admin', $existing_columns)) {
            $admin_data['is_admin'] = 1;
        }
        
        if (in_array('email_verified_at', $existing_columns)) {
            $admin_data['email_verified_at'] = date('Y-m-d H:i:s');
        }
        
        if ($check->rowCount() > 0) {
            // Atualizar admin existente
            $update_fields = [];
            $update_values = [];
            
            foreach ($admin_data as $key => $value) {
                if ($key !== 'created_at' && $key !== 'username' && $key !== 'email') {
                    $update_fields[] = "`{$key}` = :{$key}";
                    $update_values[$key] = $value;
                }
            }
            
            $update_values['username'] = $data['admin_username'];
            $update_values['email'] = $data['admin_email'];
            
            $sql = "UPDATE `{$table}` SET " . implode(', ', $update_fields) . 
                   " WHERE username = :username OR email = :email";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($update_values);
        } else {
            // Inserir novo admin
            $fields = array_keys($admin_data);
            $placeholders = array_map(function($f) { return ':' . $f; }, $fields);
            
            $sql = "INSERT INTO `{$table}` (`" . implode('`, `', $fields) . "`) 
                    VALUES (" . implode(', ', $placeholders) . ")";
            
            $stmt = $pdo->prepare($sql);
            foreach ($admin_data as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->execute();
            
            $user_id = $pdo->lastInsertId();
            
            // Se houver tabela user_roles separada, inserir relação
            if (!in_array('role', $existing_columns) && !in_array('role_id', $existing_columns)) {
                $user_roles_table = $config['prefix'] . 'user_roles';
                try {
                    // Verificar se a tabela user_roles existe
                    $check_table = $pdo->query("SHOW TABLES LIKE '{$user_roles_table}'");
                    if ($check_table->rowCount() > 0) {
                        // Buscar ID da role admin
                        $role_table = $config['prefix'] . 'roles';
                        $role_stmt = $pdo->query("SELECT id FROM `{$role_table}` WHERE name = 'admin' OR slug = 'admin' LIMIT 1");
                        $role = $role_stmt->fetch();
                        
                        if ($role) {
                            $pdo->exec("INSERT INTO `{$user_roles_table}` (user_id, role_id) VALUES ({$user_id}, {$role['id']})");
                        }
                    }
                } catch (Exception $e) {
                    // Ignorar erro se tabela não existir
                }
            }
        }
        
        // Configurar SMTP se fornecido
        if (!empty($data['configure_email']) && !empty($data['smtp_host'])) {
            $settings_table = $config['prefix'] . 'settings';
            
            try {
                // Verificar se a tabela settings existe
                $check_settings = $pdo->query("SHOW TABLES LIKE '{$settings_table}'");
                if ($check_settings->rowCount() > 0) {
                    $smtp_settings = [
                        'smtp_host' => $data['smtp_host'],
                        'smtp_port' => $data['smtp_port'] ?? 587,
                        'smtp_security' => $data['smtp_security'] ?? 'tls',
                        'smtp_user' => $data['smtp_user'] ?? '',
                        'smtp_pass' => $data['smtp_pass'] ?? '',
                        'smtp_from_email' => $data['smtp_from_email'] ?? $data['admin_email'],
                        'smtp_from_name' => $data['smtp_from_name'] ?? ($data['site_name'] ?? 'GameDev Academy')
                    ];
                    
                    foreach ($smtp_settings as $key => $value) {
                        $stmt = $pdo->prepare("INSERT INTO `{$settings_table}` (`key`, `value`, `type`, `group`) 
                                              VALUES (:key, :value, 'text', 'email') 
                                              ON DUPLICATE KEY UPDATE `value` = :value");
                        $stmt->execute([
                            ':key' => $key,
                            ':value' => $value
                        ]);
                    }
                }
            } catch (Exception $e) {
                // Ignorar erro se tabela não existir
            }
        }
        
        // Enviar email de boas-vindas se configurado
        if (!empty($data['send_welcome_email']) && !empty($data['smtp_host'])) {
            try {
                // Código para enviar email seria aqui
                // Por enquanto apenas registrar que deve enviar
                $_SESSION['send_welcome_email'] = true;
            } catch (Exception $e) {
                // Ignorar erro de email
            }
        }
        
        // Salvar configurações
        $_SESSION['admin_created'] = true;
        $_SESSION['site_config'] = [
            'site_name' => $data['site_name'] ?? 'GameDev Academy',
            'site_url' => $data['site_url'] ?? '',
            'site_description' => $data['site_description'] ?? '',
            'admin_email' => $data['admin_email'],
            'timezone' => $data['timezone'] ?? 'America/Sao_Paulo',
            'language' => $data['language'] ?? 'pt-BR',
            'debug_mode' => !empty($data['enable_debug'])
        ];
        
        // Criar arquivo de configuração
        $config_data = array_merge($config, $_SESSION['site_config']);
        $config_result = create_config_file($config_data);
        
        if ($config_result['success']) {
            $_SESSION['config_created'] = true;
        } else {
            $_SESSION['config_error'] = $config_result['message'];
            $_SESSION['config_content'] = $config_result['content'] ?? '';
        }
        
        return [
            'success' => true,
            'message' => 'Administrador criado com sucesso'
        ];
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Erro ao criar administrador: ' . $e->getMessage()
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Erro: ' . $e->getMessage()
        ];
    }
}

// ... (resto do arquivo permanece igual) ...
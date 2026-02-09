<?php
/**
 * Processamento de formulários do instalador
 */

if (!defined('INSTALLER')) {
    die('Acesso negado');
}

/**
 * Processa Step 1 - Requisitos
 */
function process_step_1($data) {
    // Step 1 é apenas verificação, não precisa processar dados
    // Apenas verificar se pode continuar
    
    $checker = new RequirementsChecker();
    $summary = $checker->getSummary();
    
    if (!$summary['can_continue']) {
        return [
            'success' => false,
            'message' => 'Corrija os erros antes de continuar'
        ];
    }
    
    $_SESSION['requirements_checked'] = true;
    
    return [
        'success' => true,
        'message' => 'Requisitos verificados com sucesso'
    ];
}

/**
 * Processa Step 2 - Banco de Dados
 */
function process_step_2($data) {
    $errors = [];
    
    // Validar campos obrigatórios
    $required = ['db_host', 'db_user', 'db_name'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            $errors[] = "Campo obrigatório: " . str_replace('db_', '', $field);
        }
    }
    
    if (!empty($errors)) {
        return [
            'success' => false,
            'message' => implode(', ', $errors)
        ];
    }
    
    // Sanitizar dados
    $config = [
        'host' => filter_var($data['db_host'], FILTER_SANITIZE_STRING),
        'user' => filter_var($data['db_user'], FILTER_SANITIZE_STRING),
        'pass' => $data['db_pass'] ?? '',
        'name' => filter_var($data['db_name'], FILTER_SANITIZE_STRING),
        'port' => isset($data['db_port']) ? (int)$data['db_port'] : 3306,
        'prefix' => isset($data['db_prefix']) ? preg_replace('/[^a-z0-9_]/', '', strtolower($data['db_prefix'])) : ''
    ];
    
    // Adicionar underscore ao prefixo se não tiver
    if ($config['prefix'] && substr($config['prefix'], -1) !== '_') {
        $config['prefix'] .= '_';
    }
    
    // Testar conexão
    $db = new DatabaseManager($config);
    $test = $db->testConnection(true); // true = criar banco se não existir
    
    if (!$test['success']) {
        return [
            'success' => false,
            'message' => $test['message']
        ];
    }
    
    // Verificar privilégios
    if (isset($test['privileges']) && !$test['privileges']['has_all']) {
        $missing = implode(', ', $test['privileges']['missing']);
        return [
            'success' => false,
            'message' => "Privilégios insuficientes. Faltando: {$missing}"
        ];
    }
    
    // Salvar configurações na sessão (temporário)
    $_SESSION['db_config'] = $config;
    $_SESSION['db_tested'] = true;
    
    return [
        'success' => true,
        'message' => 'Conexão com banco de dados estabelecida'
    ];
}

/**
 * Processa Step 3 - Criação de Tabelas
 */
function process_step_3($data) {
    // Verificar se passou pelo step 2
    if (!isset($_SESSION['db_config']) || !isset($_SESSION['db_tested'])) {
        return [
            'success' => false,
            'message' => 'Configure o banco de dados primeiro'
        ];
    }
    
    $config = $_SESSION['db_config'];
    $db = new DatabaseManager($config);
    
    // Arquivos SQL para executar
    $sql_files = [
        'structure.sql' => 'Estrutura das tabelas',
        'data.sql' => 'Dados iniciais'
    ];
    
    $all_success = true;
    $messages = [];
    
    foreach ($sql_files as $file => $description) {
        $filepath = INSTALL_PATH . '/sql/' . $file;
        
        if (!file_exists($filepath)) {
            // Não é erro se data.sql não existir
            if ($file === 'data.sql') {
                continue;
            }
            
            $messages[] = "❌ Arquivo {$file} não encontrado";
            $all_success = false;
            continue;
        }
        
        $result = $db->executeFile($filepath, true);
        
        if ($result['success']) {
            $messages[] = "✅ {$description}: {$result['executed']} comandos executados";
        } else {
            $messages[] = "❌ {$description}: " . $result['message'];
            if (isset($result['errors'])) {
                foreach ($result['errors'] as $error) {
                    $messages[] = "   → " . $error['error'];
                }
            }
            $all_success = false;
        }
    }
    
    // Verificar se as principais tabelas foram criadas
    $required_tables = ['users', 'settings', 'sessions'];
    $missing_tables = [];
    
    foreach ($required_tables as $table) {
        if (!$db->tableExists($table)) {
            $missing_tables[] = $config['prefix'] . $table;
        }
    }
    
    if (!empty($missing_tables)) {
        $messages[] = "❌ Tabelas não criadas: " . implode(', ', $missing_tables);
        $all_success = false;
    }
    
    $db->close();
    
    if (!$all_success) {
        return [
            'success' => false,
            'message' => implode("\n", $messages)
        ];
    }
    
    $_SESSION['tables_created'] = true;
    
    return [
        'success' => true,
        'message' => implode("\n", $messages)
    ];
}

/**
 * Processa Step 4 - Configuração do Admin
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
    
    // Conectar ao banco
    $config = $_SESSION['db_config'];
    $db = new DatabaseManager($config);
    
    // Preparar dados do admin
    $admin_data = [
        'username' => $data['admin_username'],
        'email' => $data['admin_email'],
        'password' => password_hash($data['admin_password'], PASSWORD_DEFAULT),
        'name' => $data['admin_name'] ?? $data['admin_username'],
        'role' => 'admin',
        'status' => 'active',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Verificar se já existe admin
    $mysqli = $db->connect();
    $table = $config['prefix'] . 'users';
    $check = $mysqli->query("SELECT id FROM `{$table}` WHERE role = 'admin' LIMIT 1");
    
    if ($check && $check->num_rows > 0) {
        // Atualizar admin existente
        $row = $check->fetch_assoc();
        $result = $db->update('users', $admin_data, ['id' => $row['id']]);
    } else {
        // Inserir novo admin
        $result = $db->insert('users', $admin_data);
    }
    
    if (!$result['success']) {
        return [
            'success' => false,
            'message' => 'Erro ao criar administrador: ' . ($result['message'] ?? 'Erro desconhecido')
        ];
    }
    
    // Configurações do site
    $site_data = [
        'site_name' => $data['site_name'] ?? 'GameDev Academy',
        'site_url' => $data['site_url'] ?? '',
        'site_description' => $data['site_description'] ?? '',
        'admin_email' => $data['admin_email']
    ];
    
    // Salvar configurações
    $_SESSION['admin_created'] = true;
    $_SESSION['site_config'] = $site_data;
    
    // Criar arquivo de configuração
    $config_data = array_merge($config, $site_data, [
        'admin_email' => $data['admin_email']
    ]);
    
    $config_result = create_config_file($config_data);
    
    if (!$config_result['success']) {
        // Não é erro fatal, pode ser criado manualmente
        $_SESSION['config_content'] = $config_result['content'] ?? '';
        $_SESSION['config_error'] = $config_result['message'];
    } else {
        $_SESSION['config_created'] = true;
    }
    
    $db->close();
    
    return [
        'success' => true,
        'message' => 'Administrador criado com sucesso'
    ];
}

/**
 * Processa Step 5 - Finalização
 */
function process_step_5($data) {
    // Limpar dados sensíveis da sessão
    $keep_keys = ['csrf_token', 'installer_started'];
    
    foreach ($_SESSION as $key => $value) {
        if (!in_array($key, $keep_keys)) {
            unset($_SESSION[$key]);
        }
    }
    
    // Tentar criar arquivo .htaccess na pasta install para bloquear acesso
    $htaccess_content = "Order Deny,Allow\nDeny from all";
    @file_put_contents(INSTALL_PATH . '/.htaccess', $htaccess_content);
    
    // Criar arquivo de flag de instalação completa
    @file_put_contents(INSTALL_PATH . '/installed.lock', date('Y-m-d H:i:s'));
    
    return [
        'success' => true,
        'message' => 'Instalação concluída'
    ];
}

/**
 * Função auxiliar para validar dados
 */
function validate_input($input, $type = 'string', $options = []) {
    switch ($type) {
        case 'email':
            return filter_var($input, FILTER_VALIDATE_EMAIL) !== false;
            
        case 'url':
            return filter_var($input, FILTER_VALIDATE_URL) !== false;
            
        case 'int':
            return filter_var($input, FILTER_VALIDATE_INT, $options) !== false;
            
        case 'username':
            return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $input);
            
        case 'password':
            $min = $options['min'] ?? 8;
            return strlen($input) >= $min;
            
        default:
            return !empty(trim($input));
    }
}
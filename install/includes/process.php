<?php
/**
 * Installer form processing helpers.
 */

if (!defined('INSTALLER')) {
    die('Acesso negado');
}

function installer_open_pdo(array $config): PDO
{
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $config['host'],
        $config['port'],
        $config['name']
    );

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ];

    if (defined('PDO::MYSQL_ATTR_MULTI_STATEMENTS')) {
        $options[PDO::MYSQL_ATTR_MULTI_STATEMENTS] = true;
    }

    return new PDO($dsn, $config['user'], $config['pass'], $options);
}

function installer_table_exists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare('SHOW TABLES LIKE :table');
    $stmt->execute([':table' => $table]);

    return $stmt->rowCount() > 0;
}

function installer_upsert_settings(PDO $pdo, string $table, array $settings): void
{
    if (!installer_table_exists($pdo, $table)) {
        return;
    }

    $sql = "INSERT INTO `{$table}` (
                `setting_key`,
                `setting_label`,
                `setting_value`,
                `setting_type`,
                `setting_group`,
                `description`,
                `is_public`
            ) VALUES (
                :setting_key,
                :setting_label,
                :setting_value,
                :setting_type,
                :setting_group,
                :description,
                :is_public
            )
            ON DUPLICATE KEY UPDATE
                `setting_label` = VALUES(`setting_label`),
                `setting_value` = VALUES(`setting_value`),
                `setting_type` = VALUES(`setting_type`),
                `setting_group` = VALUES(`setting_group`),
                `description` = VALUES(`description`),
                `is_public` = VALUES(`is_public`)";

    $stmt = $pdo->prepare($sql);

    foreach ($settings as $setting) {
        $stmt->execute([
            ':setting_key' => $setting['key'],
            ':setting_label' => $setting['label'],
            ':setting_value' => $setting['value'],
            ':setting_type' => $setting['type'],
            ':setting_group' => $setting['group'],
            ':description' => $setting['description'],
            ':is_public' => $setting['is_public'],
        ]);
    }
}

function installer_import_sql_file(PDO $pdo, string $filePath): void
{
    if (!is_file($filePath)) {
        throw new RuntimeException('Arquivo SQL demonstrativo nao encontrado.');
    }

    $sql = (string) file_get_contents($filePath);
    $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql);

    if (trim($sql) === '') {
        throw new RuntimeException('Arquivo SQL demonstrativo esta vazio.');
    }

    $startedTransaction = false;
    if (!$pdo->inTransaction()) {
        $pdo->beginTransaction();
        $startedTransaction = true;
    }

    try {
        $pdo->exec($sql);

        if ($startedTransaction) {
            $pdo->commit();
        }
    } catch (Throwable $e) {
        if ($startedTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}

/**
 * Process Step 4 - admin setup.
 */
function process_step_4($data)
{
    if (!isset($_SESSION['db_config']) || !isset($_SESSION['tables_created'])) {
        return [
            'success' => false,
            'message' => 'Complete as etapas anteriores primeiro'
        ];
    }

    $errors = [];

    $required = ['admin_username', 'admin_email', 'admin_password', 'admin_password_confirm'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            $errors[] = 'Campo obrigatorio: ' . str_replace('admin_', '', str_replace('_', ' ', $field));
        }
    }

    if (!empty($data['admin_email']) && !filter_var($data['admin_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email invalido';
    }

    if (!empty($data['admin_password'])) {
        if (strlen($data['admin_password']) < 8) {
            $errors[] = 'A senha deve ter no minimo 8 caracteres';
        }

        if (($data['admin_password'] ?? '') !== ($data['admin_password_confirm'] ?? '')) {
            $errors[] = 'As senhas nao coincidem';
        }
    }

    if (!empty($data['admin_username'])) {
        if (strlen($data['admin_username']) < 3) {
            $errors[] = 'Nome de usuario deve ter no minimo 3 caracteres';
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $data['admin_username'])) {
            $errors[] = 'Nome de usuario pode conter apenas letras, numeros e underscore';
        }
    }

    if (!empty($errors)) {
        return [
            'success' => false,
            'message' => implode(', ', $errors)
        ];
    }

    $config = $_SESSION['db_config'];

    try {
        $pdo = installer_open_pdo($config);
        $pdo->beginTransaction();

        $usersTable = $config['prefix'] . 'users';
        $settingsTable = $config['prefix'] . 'settings';

        $columnsQuery = $pdo->query("SHOW COLUMNS FROM `{$usersTable}`");
        $existingColumns = [];
        while ($row = $columnsQuery->fetch(PDO::FETCH_ASSOC)) {
            $existingColumns[] = $row['Field'];
        }

        $check = $pdo->prepare("SELECT id FROM `{$usersTable}` WHERE username = :username OR email = :email LIMIT 1");
        $check->execute([
            ':username' => $data['admin_username'],
            ':email' => $data['admin_email']
        ]);

        $adminData = [
            'username' => $data['admin_username'],
            'email' => $data['admin_email'],
            'password' => password_hash($data['admin_password'], PASSWORD_DEFAULT),
            'name' => $data['admin_name'] ?? $data['admin_username'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        if (in_array('full_name', $existingColumns, true)) {
            $adminData['full_name'] = $data['admin_name'] ?? $data['admin_username'];
        }

        if (in_array('display_name', $existingColumns, true)) {
            $adminData['display_name'] = $data['admin_name'] ?? $data['admin_username'];
        }

        if (in_array('role', $existingColumns, true)) {
            $adminData['role'] = 'admin';
        } elseif (in_array('user_role', $existingColumns, true)) {
            $adminData['user_role'] = 'admin';
        } elseif (in_array('role_id', $existingColumns, true)) {
            $roleTable = $config['prefix'] . 'roles';
            $roleStmt = $pdo->query("SELECT id FROM `{$roleTable}` WHERE name = 'admin' OR slug = 'admin' LIMIT 1");
            $role = $roleStmt->fetch(PDO::FETCH_ASSOC);
            $adminData['role_id'] = $role ? $role['id'] : 1;
        }

        if (in_array('is_active', $existingColumns, true)) {
            $adminData['is_active'] = 1;
        }

        if (in_array('status', $existingColumns, true)) {
            $adminData['status'] = 'active';
        }

        if (in_array('updated_at', $existingColumns, true)) {
            $adminData['updated_at'] = date('Y-m-d H:i:s');
        }

        if (in_array('is_admin', $existingColumns, true)) {
            $adminData['is_admin'] = 1;
        }

        if (in_array('email_verified_at', $existingColumns, true)) {
            $adminData['email_verified_at'] = date('Y-m-d H:i:s');
        }

        if ($check->rowCount() > 0) {
            $updateFields = [];
            $updateValues = [];

            foreach ($adminData as $key => $value) {
                if ($key !== 'created_at' && $key !== 'username' && $key !== 'email') {
                    $updateFields[] = "`{$key}` = :{$key}";
                    $updateValues[$key] = $value;
                }
            }

            $updateValues['username'] = $data['admin_username'];
            $updateValues['email'] = $data['admin_email'];

            $sql = "UPDATE `{$usersTable}` SET " . implode(', ', $updateFields)
                . " WHERE username = :username OR email = :email";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($updateValues);
        } else {
            $fields = array_keys($adminData);
            $placeholders = array_map(static function ($field) {
                return ':' . $field;
            }, $fields);

            $sql = "INSERT INTO `{$usersTable}` (`" . implode('`, `', $fields) . "`)"
                . " VALUES (" . implode(', ', $placeholders) . ")";

            $stmt = $pdo->prepare($sql);
            foreach ($adminData as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->execute();

            $userId = (int) $pdo->lastInsertId();

            if (!in_array('role', $existingColumns, true) && !in_array('role_id', $existingColumns, true)) {
                $userRolesTable = $config['prefix'] . 'user_roles';
                if (installer_table_exists($pdo, $userRolesTable)) {
                    $roleTable = $config['prefix'] . 'roles';
                    $roleStmt = $pdo->query("SELECT id FROM `{$roleTable}` WHERE name = 'admin' OR slug = 'admin' LIMIT 1");
                    $role = $roleStmt->fetch(PDO::FETCH_ASSOC);

                    if ($role) {
                        $pdo->exec("INSERT INTO `{$userRolesTable}` (user_id, role_id) VALUES ({$userId}, {$role['id']})");
                    }
                }
            }
        }

        $siteSettings = [
            [
                'key' => 'site_name',
                'label' => 'Nome do Site',
                'value' => $data['site_name'] ?? 'GameDev Academy',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Nome exibido no site',
                'is_public' => 1,
            ],
            [
                'key' => 'site_url',
                'label' => 'URL do Site',
                'value' => $data['site_url'] ?? '',
                'type' => 'string',
                'group' => 'general',
                'description' => 'URL principal da plataforma',
                'is_public' => 1,
            ],
            [
                'key' => 'site_description',
                'label' => 'Descricao do Site',
                'value' => $data['site_description'] ?? '',
                'type' => 'text',
                'group' => 'general',
                'description' => 'Descricao institucional da plataforma',
                'is_public' => 1,
            ],
            [
                'key' => 'contact_email',
                'label' => 'Email de Contato',
                'value' => $data['admin_email'],
                'type' => 'string',
                'group' => 'general',
                'description' => 'Email principal de contato',
                'is_public' => 1,
            ],
            [
                'key' => 'timezone',
                'label' => 'Fuso Horario',
                'value' => $data['timezone'] ?? 'America/Sao_Paulo',
                'type' => 'string',
                'group' => 'system',
                'description' => 'Fuso horario padrao do sistema',
                'is_public' => 0,
            ],
            [
                'key' => 'default_language',
                'label' => 'Idioma Padrao',
                'value' => $data['language'] ?? 'pt-BR',
                'type' => 'string',
                'group' => 'appearance',
                'description' => 'Idioma padrao da plataforma',
                'is_public' => 0,
            ],
            [
                'key' => 'debug_mode',
                'label' => 'Modo Debug',
                'value' => !empty($data['enable_debug']) ? '1' : '0',
                'type' => 'boolean',
                'group' => 'system',
                'description' => 'Ativa logs e mensagens detalhadas para desenvolvimento',
                'is_public' => 0,
            ],
        ];

        installer_upsert_settings($pdo, $settingsTable, $siteSettings);

        if (!empty($data['configure_email']) && !empty($data['smtp_host'])) {
            $smtpSettings = [
                [
                    'key' => 'smtp_host',
                    'label' => 'Servidor SMTP',
                    'value' => $data['smtp_host'],
                    'type' => 'string',
                    'group' => 'email',
                    'description' => 'Servidor SMTP para envio de emails',
                    'is_public' => 0,
                ],
                [
                    'key' => 'smtp_port',
                    'label' => 'Porta SMTP',
                    'value' => (string) ($data['smtp_port'] ?? 587),
                    'type' => 'number',
                    'group' => 'email',
                    'description' => 'Porta do servidor SMTP',
                    'is_public' => 0,
                ],
                [
                    'key' => 'smtp_security',
                    'label' => 'Seguranca SMTP',
                    'value' => $data['smtp_security'] ?? 'tls',
                    'type' => 'string',
                    'group' => 'email',
                    'description' => 'Tipo de seguranca da conexao SMTP',
                    'is_public' => 0,
                ],
                [
                    'key' => 'smtp_user',
                    'label' => 'Usuario SMTP',
                    'value' => $data['smtp_user'] ?? '',
                    'type' => 'string',
                    'group' => 'email',
                    'description' => 'Usuario autenticado do SMTP',
                    'is_public' => 0,
                ],
                [
                    'key' => 'smtp_pass',
                    'label' => 'Senha SMTP',
                    'value' => $data['smtp_pass'] ?? '',
                    'type' => 'string',
                    'group' => 'email',
                    'description' => 'Senha do SMTP',
                    'is_public' => 0,
                ],
                [
                    'key' => 'smtp_from_email',
                    'label' => 'Email do Remetente',
                    'value' => $data['smtp_from_email'] ?? $data['admin_email'],
                    'type' => 'string',
                    'group' => 'email',
                    'description' => 'Email padrao de envio',
                    'is_public' => 0,
                ],
                [
                    'key' => 'smtp_from_name',
                    'label' => 'Nome do Remetente',
                    'value' => $data['smtp_from_name'] ?? ($data['site_name'] ?? 'GameDev Academy'),
                    'type' => 'string',
                    'group' => 'email',
                    'description' => 'Nome padrao do remetente',
                    'is_public' => 0,
                ],
            ];

            installer_upsert_settings($pdo, $settingsTable, $smtpSettings);
        }

        $demoImported = false;
        if (!empty($data['install_demo_content'])) {
            installer_import_sql_file($pdo, dirname(__DIR__) . '/database/gamedev-demo.sql');
            $demoImported = true;
        }

        if (!empty($data['send_welcome_email']) && !empty($data['smtp_host'])) {
            $_SESSION['send_welcome_email'] = true;
        }

        $pdo->commit();

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
        $_SESSION['demo_content_installed'] = $demoImported;

        $configData = array_merge($config, $_SESSION['site_config']);
        $configResult = create_config_file($configData);

        if ($configResult['success']) {
            $_SESSION['config_created'] = true;
        } else {
            $_SESSION['config_error'] = $configResult['message'];
            $_SESSION['config_content'] = $configResult['content'] ?? '';
        }

        return [
            'success' => true,
            'message' => $demoImported
                ? 'Administrador criado com sucesso e conteudo demonstrativo importado.'
                : 'Administrador criado com sucesso'
        ];
    } catch (PDOException $e) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'success' => false,
            'message' => 'Erro ao criar administrador: ' . $e->getMessage()
        ];
    } catch (Throwable $e) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'success' => false,
            'message' => 'Erro: ' . $e->getMessage()
        ];
    }
}

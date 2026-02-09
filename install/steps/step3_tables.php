<?php
/**
 * Step 3 - Criação das Tabelas
 */

if (!defined('INSTALLER')) {
    die('Acesso negado');
}

// Verificar se tem configuração do banco
if (!isset($_SESSION['db_config'])) {
    header('Location: ?step=2');
    exit;
}

// Processar criação das tabelas se POST
$installResult = null;
$installError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_tables') {
    
    try {
        $config = $_SESSION['db_config'];
        
        // Check PDO extension
        if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')) {
            throw new Exception('Extensão PDO MySQL não está instalada');
        }
        
        // Create PDO connection
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $config['host'],
            $config['port'],
            $config['name']
        );
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
        
        // Include create_tables.php
        $createTablesPath = INSTALL_PATH . '/sql/create_tables.php';
        
        if (!file_exists($createTablesPath)) {
            throw new Exception('Arquivo create_tables.php não encontrado');
        }
        
        require_once $createTablesPath;
        
        // Check if function exists
        if (!function_exists('executeDatabaseSetup')) {
            throw new Exception('Função executeDatabaseSetup não encontrada');
        }
        
        // Execute database setup
        $result = executeDatabaseSetup($pdo);
        
        if ($result['success']) {
            $_SESSION['tables_created'] = true;
            $_SESSION['success'] = "Tabelas criadas com sucesso! {$result['stats']['tables_created']} tabelas instaladas.";
            header('Location: ?step=4');
            exit;
        } else {
            $installError = implode("\n", $result['errors']);
        }
        
    } catch (PDOException $e) {
        $installError = 'Erro de banco de dados: ' . $e->getMessage();
    } catch (Exception $e) {
        $installError = $e->getMessage();
    }
}

// Lista de tabelas (51 tabelas conforme create_tables.php)
$tables = [
    'users' => 'Usuários do sistema',
    'user_profiles' => 'Perfis de usuários',
    'user_settings' => 'Configurações de usuários',
    'roles' => 'Funções/Papéis',
    'permissions' => 'Permissões',
    'role_permissions' => 'Relação roles-permissions',
    'user_roles' => 'Relação users-roles',
    'sessions' => 'Sessões de usuários',
    'password_resets' => 'Recuperação de senhas',
    'email_verifications' => 'Verificação de emails',
    'activity_logs' => 'Logs de atividades',
    'login_attempts' => 'Tentativas de login',
    'categories' => 'Categorias',
    'posts' => 'Posts/Artigos',
    'post_meta' => 'Meta dados de posts',
    'pages' => 'Páginas',
    'page_meta' => 'Meta dados de páginas',
    'comments' => 'Comentários',
    'comment_meta' => 'Meta dados de comentários',
    'tags' => 'Tags',
    'post_tags' => 'Relação posts-tags',
    'media' => 'Arquivos de mídia',
    'media_meta' => 'Meta dados de mídia',
    'galleries' => 'Galerias',
    'gallery_items' => 'Itens de galerias',
    'menus' => 'Menus',
    'menu_items' => 'Itens de menus',
    'widgets' => 'Widgets',
    'widget_areas' => 'Áreas de widgets',
    'settings' => 'Configurações',
    'options' => 'Opções do sistema',
    'notifications' => 'Notificações',
    'user_notifications' => 'Notificações de usuários',
    'email_templates' => 'Templates de email',
    'email_queue' => 'Fila de emails',
    'forms' => 'Formulários',
    'form_fields' => 'Campos de formulários',
    'form_submissions' => 'Envios de formulários',
    'languages' => 'Idiomas',
    'translations' => 'Traduções',
    'seo_meta' => 'Meta dados SEO',
    'redirects' => 'Redirecionamentos',
    'analytics' => 'Analytics',
    'cache' => 'Cache',
    'backups' => 'Backups',
    'migrations' => 'Migrações',
    'api_keys' => 'Chaves de API',
    'webhooks' => 'Webhooks',
    'cron_jobs' => 'Tarefas cron',
    'error_logs' => 'Logs de erros',
    'audit_logs' => 'Logs de auditoria'
];
?>

<div class="tables-setup">
    
    <?php if ($installError): ?>
    <div class="alert alert-danger">
        <i class="fas fa-times-circle"></i>
        <div class="alert-content">
            <h4>Erro na Instalação</h4>
            <p><?php echo nl2br(htmlspecialchars($installError)); ?></p>
        </div>
    </div>
    <?php endif; ?>

    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        <div class="alert-content">
            <h4>Criação da Estrutura do Banco de Dados</h4>
            <p class="mb-0">
                Agora vamos criar as tabelas necessárias para o funcionamento do sistema.
                Serão criadas 51 tabelas com toda a estrutura necessária.
            </p>
        </div>
    </div>

    <!-- Informações da Conexão -->
    <div class="connection-info mb-4">
        <h5 class="section-title">
            <i class="fas fa-database"></i>
            Informações da Conexão
        </h5>
        <table class="table table-sm">
            <tbody>
                <tr>
                    <td width="30%"><strong>Servidor:</strong></td>
                    <td><?php echo htmlspecialchars($_SESSION['db_config']['host']); ?>:<?php echo $_SESSION['db_config']['port']; ?></td>
                </tr>
                <tr>
                    <td><strong>Banco de Dados:</strong></td>
                    <td><?php echo htmlspecialchars($_SESSION['db_config']['name']); ?></td>
                </tr>
                <tr>
                    <td><strong>Usuário:</strong></td>
                    <td><?php echo htmlspecialchars($_SESSION['db_config']['user']); ?></td>
                </tr>
                <tr>
                    <td><strong>Prefixo das Tabelas:</strong></td>
                    <td><?php echo htmlspecialchars($_SESSION['db_config']['prefix'] ?: 'Sem prefixo'); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Lista de Tabelas -->
    <div class="tables-list" id="tablesList">
        <h5 class="section-title">
            <i class="fas fa-list"></i>
            Tabelas que serão criadas (51 tabelas)
        </h5>
        <div class="table-grid">
            <?php foreach ($tables as $table => $description): 
                $fullTableName = ($_SESSION['db_config']['prefix'] ?? '') . $table;
            ?>
            <div class="table-item" data-table="<?php echo $table; ?>">
                <div class="table-item-icon">
                    <i class="fas fa-table"></i>
                </div>
                <div class="table-item-content">
                    <span class="table-name"><?php echo $fullTableName; ?></span>
                    <small class="table-description"><?php echo $description; ?></small>
                </div>
                <div class="table-item-status">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Progress Container -->
    <div id="installProgress" class="install-progress-container d-none">
        <h5 class="section-title">
            <i class="fas fa-tasks"></i>
            Progresso da Instalação
        </h5>
        
        <div class="progress installer-progress mb-3">
            <div id="progressBar" 
                 class="progress-bar progress-bar-striped progress-bar-animated" 
                 role="progressbar"
                 style="width: 0%">
                0%
            </div>
        </div>

        <div id="installLog" class="install-log"></div>
    </div>

    <!-- Resultado -->
    <div id="installResult" class="install-result d-none"></div>

    <!-- Formulário -->
    <form method="POST" action="" id="tablesForm">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="action" value="create_tables">
        
        <div class="form-actions mt-4">
            <div class="d-flex justify-content-between">
                <a href="?step=2" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> 
                    Voltar
                </a>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary" id="startInstallBtn">
                        <i class="fas fa-database"></i> 
                        Criar Tabelas
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        Continuar <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
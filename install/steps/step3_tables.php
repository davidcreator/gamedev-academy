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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_tables') {
    // Incluir o script de criação
    require_once INSTALL_PATH . '/sql/create_tables.php';
    
    // Configurar conexão
    $config = $_SESSION['db_config'];
    
    try {
        $mysqli = new mysqli(
            $config['host'],
            $config['user'],
            $config['pass'],
            $config['name'],
            $config['port']
        );
        
        if ($mysqli->connect_error) {
            throw new Exception("Conexão falhou: " . $mysqli->connect_error);
        }
        
        $mysqli->set_charset('utf8mb4');
        
        // Criar instância do TableCreator
        $tableCreator = new TableCreator($mysqli, $config['prefix'] ?? '');
        
        // Executar criação
        $result = $tableCreator->createAllTables();
        
        if ($result['success']) {
            $_SESSION['tables_created'] = true;
            $_SESSION['success'] = "Tabelas criadas com sucesso! {$result['tables_created']} tabelas instaladas.";
            header('Location: ?step=4');
            exit;
        } else {
            $_SESSION['error'] = "Erro ao criar tabelas: " . implode(', ', $result['errors']);
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Erro: " . $e->getMessage();
    }
}

// Lista de todas as 46 tabelas do create_tables.php
$tables = [
    'users' => 'Usuários do sistema',
    'user_profiles' => 'Perfis de usuários',
    'roles' => 'Funções/Papéis',
    'permissions' => 'Permissões',
    'role_permissions' => 'Relação roles-permissions',
    'user_sessions' => 'Sessões de usuários',
    'password_resets' => 'Recuperação de senhas',
    'email_verifications' => 'Verificação de emails',
    'activity_logs' => 'Logs de atividades',
    'login_attempts' => 'Tentativas de login',
    'categories' => 'Categorias',
    'posts' => 'Posts/Artigos',
    'pages' => 'Páginas',
    'comments' => 'Comentários',
    'tags' => 'Tags',
    'post_tags' => 'Relação posts-tags',
    'media' => 'Arquivos de mídia',
    'galleries' => 'Galerias',
    'gallery_items' => 'Itens de galerias',
    'menus' => 'Menus',
    'menu_items' => 'Itens de menus',
    'widgets' => 'Widgets',
    'widget_positions' => 'Posições de widgets',
    'settings' => 'Configurações',
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
    'audit_logs' => 'Logs de auditoria',
    'subscriptions' => 'Assinaturas',
    'newsletters' => 'Newsletters'
];
?>

<div class="tables-setup">
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        <div class="alert-content">
            <h4>Criação da Estrutura do Banco de Dados</h4>
            <p class="mb-0">
                Agora vamos criar as tabelas necessárias para o funcionamento do sistema.
                Serão criadas 46 tabelas com toda a estrutura necessária.
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

    <!-- Lista de Tabelas que serão criadas -->
    <div class="tables-list" id="tablesList">
        <h5 class="section-title">
            <i class="fas fa-list"></i>
            Tabelas que serão criadas
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

    <!-- Progress Container (Hidden initially) -->
    <div id="installProgress" class="install-progress-container d-none">
        <h5 class="section-title">
            <i class="fas fa-tasks"></i>
            Progresso da Instalação
        </h5>
        
        <div class="progress installer-progress mb-3">
            <div id="progressBar" 
                 class="progress-bar progress-bar-striped progress-bar-animated" 
                 role="progressbar" 
                 aria-valuenow="0"
                 aria-valuemin="0"
                 aria-valuemax="100">
                0%
            </div>
        </div>

        <div id="installLog" class="install-log">
            <!-- Logs aparecerão aqui via JavaScript -->
        </div>
    </div>

    <!-- Resultado (Hidden initially) -->
    <div id="installResult" class="install-result d-none">
        <!-- Resultado será inserido aqui via JavaScript -->
    </div>

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
                    <button type="button" class="btn btn-warning" id="startInstallBtn">
                        <i class="fas fa-database"></i> 
                        Criar Tabelas
                    </button>
                    
                    <button type="submit" class="btn btn-primary d-none" id="continueBtn">
                        Continuar 
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
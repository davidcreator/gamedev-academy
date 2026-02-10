<?php
/**
 * Step 2 - Configuração do Banco de Dados
 */

if (!defined('INSTALLER')) {
    die('Acesso negado');
}

// Valores padrão ou da sessão
$db_host = $_SESSION['db_config']['host'] ?? 'localhost';
$db_port = $_SESSION['db_config']['port'] ?? '3306';
$db_user = $_SESSION['db_config']['user'] ?? '';
$db_name = $_SESSION['db_config']['name'] ?? '';
$db_prefix = $_SESSION['db_config']['prefix'] ?? '';
?>

<div class="database-config">
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        <strong>Informações do Banco de Dados</strong>
        <p class="mb-0 mt-2">
            Entre com as informações de conexão do banco de dados MySQL/MariaDB. 
            Se você não tem certeza sobre essas informações, entre em contato com seu provedor de hospedagem.
        </p>
    </div>

    <form method="POST" action="" id="databaseForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <div class="form-section">
            <h5 class="section-title">Conexão com o Banco de Dados</h5>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="db_host">
                            <i class="fas fa-server"></i> Servidor do Banco de Dados
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="db_host" 
                               name="db_host" 
                               value="<?php echo htmlspecialchars($db_host); ?>"
                               placeholder="localhost ou IP do servidor"
                               required>
                        <small class="form-text text-muted">
                            Geralmente "localhost". Para conexões remotas, use o IP ou domínio do servidor.
                        </small>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="db_port">
                            <i class="fas fa-plug"></i> Porta
                        </label>
                        <input type="number" 
                               class="form-control" 
                               id="db_port" 
                               name="db_port" 
                               value="<?php echo htmlspecialchars($db_port); ?>"
                               placeholder="3306"
                               min="1" 
                               max="65535">
                        <small class="form-text text-muted">
                            Padrão: 3306
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="db_user">
                            <i class="fas fa-user"></i> Usuário do Banco
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="db_user" 
                               name="db_user" 
                               value="<?php echo htmlspecialchars($db_user); ?>"
                               placeholder="Nome de usuário MySQL"
                               required
                               autocomplete="off">
                        <small class="form-text text-muted">
                            Usuário com privilégios para criar/modificar tabelas.
                        </small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="db_pass">
                            <i class="fas fa-lock"></i> Senha do Banco
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control" 
                                   id="db_pass" 
                                   name="db_pass" 
                                   placeholder="Senha do usuário MySQL"
                                   autocomplete="new-password">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary btn-toggle-password" data-target="db_pass">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            Deixe em branco se não houver senha.
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="db_name">
                            <i class="fas fa-database"></i> Nome do Banco de Dados
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="db_name" 
                               name="db_name" 
                               value="<?php echo htmlspecialchars($db_name); ?>"
                               placeholder="Nome do banco de dados"
                               required
                               pattern="[a-zA-Z0-9_]+"
                               title="Use apenas letras, números e underscore">
                        <small class="form-text text-muted">
                            Se não existir, tentaremos criar automaticamente.
                        </small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="db_prefix">
                            <i class="fas fa-tag"></i> Prefixo das Tabelas
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="db_prefix" 
                               name="db_prefix" 
                               value="<?php echo htmlspecialchars($db_prefix); ?>"
                               placeholder="gda_"
                               pattern="[a-z0-9_]*"
                               title="Use apenas letras minúsculas, números e underscore">
                        <small class="form-text text-muted">
                            Útil se você compartilha o banco com outras aplicações.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Teste de Conexão -->
        <div class="form-section mt-4">
            <h5 class="section-title">Teste de Conexão</h5>
            
            <div id="testResults" class="test-results" style="display: none;">
                <!-- Resultados do teste aparecerão aqui via JS -->
            </div>
            
            <button type="button" class="btn btn-info" id="testConnectionBtn">
                <i class="fas fa-plug"></i> Testar Conexão
            </button>
        </div>

        <!-- Botões de Ação -->
        <div class="form-actions mt-4">
            <div class="d-flex justify-content-between">
                <a href="?step=1" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                
                <button type="button" class="btn btn-primary" id="submitBtn" onclick="goToStep(3)">
                    Continuar <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
    </form>
</div>
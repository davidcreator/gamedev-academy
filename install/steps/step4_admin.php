<?php
/**
 * Step 4 - Configuração do Administrador
 */

if (!defined('INSTALLER')) {
    die('Acesso negado');
}

// Verificar se passou pelas etapas anteriores
if (!isset($_SESSION['db_config']) || !isset($_SESSION['tables_created'])) {
    header('Location: ?step=1');
    exit;
}
?>

<div class="admin-setup">
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        <strong>Configuração do Administrador</strong>
        <p class="mb-0 mt-2">
            Configure a conta de administrador principal do sistema e as informações básicas do site.
        </p>
    </div>

    <form method="POST" action="" id="adminForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <!-- Informações do Administrador -->
        <div class="form-section">
            <h5 class="section-title">
                <i class="fas fa-user-shield"></i> Conta de Administrador
            </h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="admin_username">
                            Nome de Usuário
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="admin_username" 
                               name="admin_username" 
                               placeholder="admin"
                               required
                               pattern="[a-zA-Z0-9_]{3,20}"
                               title="3-20 caracteres, apenas letras, números e underscore">
                        <small class="form-text text-muted">
                            Use para fazer login no sistema.
                        </small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="admin_email">
                            Email
                            <span class="text-danger">*</span>
                        </label>
                        <input type="email" 
                               class="form-control" 
                               id="admin_email" 
                               name="admin_email" 
                               placeholder="admin@exemplo.com"
                               required>
                        <small class="form-text text-muted">
                            Usado para notificações e recuperação de senha.
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="admin_password">
                            Senha
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control password-field" 
                                   id="admin_password" 
                                   name="admin_password" 
                                   required
                                   minlength="8">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary btn-toggle-password" data-target="admin_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <small class="form-text">
                            <span id="passwordStrength" class="badge badge-secondary">Digite uma senha</span>
                        </small>
                        <div class="password-requirements mt-2">
                            <small class="text-muted">A senha deve ter:</small>
                            <ul class="small text-muted mb-0">
                                <li id="req-length"><i class="fas fa-times text-danger"></i> Mínimo 8 caracteres</li>
                                <li id="req-upper"><i class="fas fa-times text-danger"></i> Uma letra maiúscula</li>
                                <li id="req-lower"><i class="fas fa-times text-danger"></i> Uma letra minúscula</li>
                                <li id="req-number"><i class="fas fa-times text-danger"></i> Um número</li>
                                <li id="req-special"><i class="fas fa-times text-danger"></i> Um caractere especial</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="admin_password_confirm">
                            Confirmar Senha
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control" 
                                   id="admin_password_confirm" 
                                   name="admin_password_confirm" 
                                   required
                                   minlength="8">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary btn-toggle-password" data-target="admin_password_confirm">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            Digite a mesma senha para confirmar.
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="admin_name">
                            Nome Completo
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="admin_name" 
                               name="admin_name" 
                               placeholder="João da Silva">
                        <small class="form-text text-muted">
                            Nome de exibição no sistema.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informações do Site -->
        <div class="form-section mt-4">
            <h5 class="section-title">
                <i class="fas fa-globe"></i> Informações do Site
            </h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="site_name">
                            Nome do Site
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="site_name" 
                               name="site_name" 
                               value="GameDev Academy"
                               required>
                        <small class="form-text text-muted">
                            Aparecerá no título e cabeçalho do site.
                        </small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="site_url">
                            URL do Site
                        </label>
                        <input type="url" 
                               class="form-control" 
                               id="site_url" 
                               name="site_url" 
                               value="<?php echo 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['REQUEST_URI'])); ?>"
                               placeholder="https://exemplo.com">
                        <small class="form-text text-muted">
                            URL completa onde o site será acessado.
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="site_description">
                            Descrição do Site
                        </label>
                        <textarea class="form-control" 
                                  id="site_description" 
                                  name="site_description" 
                                  rows="3" 
                                  placeholder="Um site sobre desenvolvimento de jogos..."></textarea>
                        <small class="form-text text-muted">
                            Breve descrição do site (meta description).
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configurações Adicionais -->
        <div class="form-section mt-4">
            <h5 class="section-title">
                <i class="fas fa-cog"></i> Configurações Adicionais
            </h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="timezone">Fuso Horário</label>
                        <select class="form-control" id="timezone" name="timezone">
                            <option value="America/Sao_Paulo" selected>América/São Paulo (GMT-3)</option>
                            <option value="America/Manaus">América/Manaus (GMT-4)</option>
                            <option value="America/Fortaleza">América/Fortaleza (GMT-3)</option>
                            <option value="America/Belem">América/Belém (GMT-3)</option>
                            <option value="America/Recife">América/Recife (GMT-3)</option>
                            <option value="UTC">UTC (GMT+0)</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="language">Idioma Padrão</label>
                        <select class="form-control" id="language" name="language">
                            <option value="pt-BR" selected>Português (Brasil)</option>
                            <option value="en-US">English (US)</option>
                            <option value="es-ES">Español</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="send_test_email" 
                               name="send_test_email" 
                               value="1">
                        <label class="form-check-label" for="send_test_email">
                            Enviar email de teste após instalação
                        </label>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="enable_debug" 
                               name="enable_debug" 
                               value="1">
                        <label class="form-check-label" for="enable_debug">
                            Habilitar modo debug (desenvolvimento)
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="form-actions mt-4">
            <div class="d-flex justify-content-between">
                <a href="?step=3" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    Instalar Sistema <i class="fas fa-check"></i>
                </button>
            </div>
        </div>
    </form>
</div>
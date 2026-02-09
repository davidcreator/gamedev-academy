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

// Detectar URL do site automaticamente
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$path = dirname(dirname($_SERVER['REQUEST_URI']));
$site_url = rtrim($protocol . $host . $path, '/');
?>

<div class="admin-setup">
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        <div class="alert-content">
            <h4>Configuração do Administrador</h4>
            <p class="mb-0">
                Configure a conta de administrador principal do sistema e as informações básicas do site.
                Esta será a conta com acesso total ao sistema.
            </p>
        </div>
    </div>

    <form method="POST" action="" id="adminForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <!-- Informações do Administrador -->
        <div class="form-section">
            <h5 class="section-title">
                <i class="fas fa-user-shield"></i>
                Conta de Administrador
            </h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="admin_name">
                            <i class="fas fa-user"></i>
                            Nome Completo
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="admin_name" 
                               name="admin_name" 
                               placeholder="Ex: João da Silva"
                               required
                               minlength="3"
                               maxlength="100">
                        <div class="invalid-feedback"></div>
                        <small class="form-text text-muted">
                            Nome que será exibido no sistema.
                        </small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="admin_username">
                            <i class="fas fa-at"></i>
                            Nome de Usuário
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="admin_username" 
                               name="admin_username" 
                               placeholder="Ex: admin"
                               required
                               pattern="[a-zA-Z0-9_]{3,20}"
                               minlength="3"
                               maxlength="20"
                               autocomplete="username">
                        <div class="invalid-feedback"></div>
                        <small class="form-text text-muted">
                            3-20 caracteres. Apenas letras, números e underscore.
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="admin_email">
                            <i class="fas fa-envelope"></i>
                            Email
                            <span class="text-danger">*</span>
                        </label>
                        <input type="email" 
                               class="form-control" 
                               id="admin_email" 
                               name="admin_email" 
                               placeholder="admin@exemplo.com"
                               required
                               autocomplete="email">
                        <div class="invalid-feedback"></div>
                        <div id="emailStatus" class="validation-status"></div>
                        <small class="form-text text-muted">
                            Usado para login, notificações e recuperação de senha.
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="admin_password">
                            <i class="fas fa-lock"></i>
                            Senha
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control" 
                                   id="admin_password" 
                                   name="admin_password" 
                                   placeholder="Mínimo 8 caracteres"
                                   required
                                   minlength="8"
                                   autocomplete="new-password">
                            <div class="input-group-append">
                                <button type="button" 
                                        class="btn btn-outline-secondary btn-toggle-password" 
                                        data-target="admin_password"
                                        title="Mostrar/Ocultar senha">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="invalid-feedback"></div>
                        
                        <!-- Barra de força da senha -->
                        <div class="password-strength-container mt-2">
                            <div class="password-strength-bar">
                                <div id="passwordStrengthFill" class="password-strength-fill"></div>
                            </div>
                            <div id="passwordStrengthText" class="password-strength-text">
                                <i class="fas fa-shield-alt"></i>
                                <span>Digite uma senha</span>
                            </div>
                        </div>
                        
                        <!-- Requisitos da senha -->
                        <div class="password-requirements mt-2">
                            <div class="password-requirements-title">A senha deve ter:</div>
                            <ul>
                                <li id="req-length" class="invalid">
                                    <i class="fas fa-times"></i>
                                    <span>Mínimo 8 caracteres</span>
                                </li>
                                <li id="req-uppercase" class="invalid">
                                    <i class="fas fa-times"></i>
                                    <span>Uma letra maiúscula</span>
                                </li>
                                <li id="req-lowercase" class="invalid">
                                    <i class="fas fa-times"></i>
                                    <span>Uma letra minúscula</span>
                                </li>
                                <li id="req-number" class="invalid">
                                    <i class="fas fa-times"></i>
                                    <span>Um número</span>
                                </li>
                                <li id="req-special" class="invalid">
                                    <i class="fas fa-times"></i>
                                    <span>Um caractere especial (!@#$%^&*)</span>
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Gerador de senha -->
                        <div class="password-generator mt-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="generatePasswordBtn">
                                <i class="fas fa-magic"></i>
                                Gerar Senha Forte
                            </button>
                            <div id="generatedPasswordContainer" class="generated-password-container d-none">
                                <div class="generated-password-box">
                                    <code id="generatedPassword"></code>
                                    <button type="button" class="btn btn-sm btn-success" id="copyPasswordBtn" title="Copiar senha">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-primary" id="usePasswordBtn" title="Usar esta senha">
                                        <i class="fas fa-check"></i>
                                        Usar
                                    </button>
                                </div>
                                <small class="text-success" id="copySuccess" style="display: none;">
                                    <i class="fas fa-check"></i> Copiado!
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="admin_password_confirm">
                            <i class="fas fa-lock"></i>
                            Confirmar Senha
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control" 
                                   id="admin_password_confirm" 
                                   name="admin_password_confirm" 
                                   placeholder="Digite a senha novamente"
                                   required
                                   minlength="8"
                                   autocomplete="new-password">
                            <div class="input-group-append">
                                <button type="button" 
                                        class="btn btn-outline-secondary btn-toggle-password" 
                                        data-target="admin_password_confirm"
                                        title="Mostrar/Ocultar senha">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="invalid-feedback"></div>
                        <div id="passwordMatchStatus" class="validation-status mt-2"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informações do Site -->
        <div class="form-section mt-4">
            <h5 class="section-title">
                <i class="fas fa-globe"></i>
                Informações do Site
            </h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="site_name">
                            <i class="fas fa-heading"></i>
                            Nome do Site
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="site_name" 
                               name="site_name" 
                               value="GameDev Academy"
                               required
                               maxlength="100">
                        <div class="invalid-feedback"></div>
                        <small class="form-text text-muted">
                            Aparecerá no título e cabeçalho do site.
                        </small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="site_url">
                            <i class="fas fa-link"></i>
                            URL do Site
                        </label>
                        <input type="url" 
                               class="form-control" 
                               id="site_url" 
                               name="site_url" 
                               value="<?php echo htmlspecialchars($site_url); ?>"
                               placeholder="https://exemplo.com">
                        <div class="invalid-feedback"></div>
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
                            <i class="fas fa-align-left"></i>
                            Descrição do Site
                        </label>
                        <textarea class="form-control" 
                                  id="site_description" 
                                  name="site_description" 
                                  rows="3" 
                                  maxlength="255"
                                  placeholder="Uma breve descrição do seu site..."></textarea>
                        <small class="form-text text-muted">
                            <span id="descriptionCount">0</span>/255 caracteres. Usado em meta description para SEO.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configurações de Email -->
        <div class="form-section mt-4">
            <h5 class="section-title">
                <i class="fas fa-envelope-open-text"></i>
                Configurações de Email (Opcional)
            </h5>
            
            <div class="alert alert-warning mb-3">
                <i class="fas fa-info-circle"></i>
                <small>
                    Configure o servidor SMTP para envio de emails. Se não configurar agora, poderá fazer depois no painel administrativo.
                </small>
            </div>
            
            <div class="form-check mb-3">
                <input type="checkbox" 
                       class="form-check-input" 
                       id="configure_email" 
                       name="configure_email" 
                       value="1">
                <label class="form-check-label" for="configure_email">
                    Configurar servidor de email agora
                </label>
            </div>
            
            <div id="emailConfigSection" style="display: none;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="smtp_host">Servidor SMTP</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="smtp_host" 
                                   name="smtp_host" 
                                   placeholder="smtp.gmail.com">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="smtp_port">Porta</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="smtp_port" 
                                   name="smtp_port" 
                                   value="587"
                                   placeholder="587">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="smtp_security">Segurança</label>
                            <select class="form-control" id="smtp_security" name="smtp_security">
                                <option value="tls">TLS</option>
                                <option value="ssl">SSL</option>
                                <option value="none">Nenhuma</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="smtp_user">Usuário SMTP</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="smtp_user" 
                                   name="smtp_user" 
                                   placeholder="seu-email@gmail.com">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="smtp_pass">Senha SMTP</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="smtp_pass" 
                                   name="smtp_pass" 
                                   placeholder="Senha do email">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="smtp_from_email">Email do Remetente</label>
                            <input type="email" 
                                   class="form-control" 
                                   id="smtp_from_email" 
                                   name="smtp_from_email" 
                                   placeholder="noreply@seusite.com">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="smtp_from_name">Nome do Remetente</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="smtp_from_name" 
                                   name="smtp_from_name" 
                                   placeholder="GameDev Academy">
                        </div>
                    </div>
                </div>
                
                <button type="button" class="btn btn-info btn-sm" id="testEmailBtn">
                    <i class="fas fa-paper-plane"></i>
                    Enviar Email de Teste
                </button>
                <div id="emailTestResult" class="mt-2"></div>
            </div>
        </div>

        <!-- Configurações Adicionais -->
        <div class="form-section mt-4">
            <h5 class="section-title">
                <i class="fas fa-cog"></i>
                Configurações Adicionais
            </h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="timezone">
                            <i class="fas fa-clock"></i>
                            Fuso Horário
                        </label>
                        <select class="form-control" id="timezone" name="timezone">
                            <option value="America/Sao_Paulo" selected>América/São Paulo (GMT-3)</option>
                            <option value="America/Fortaleza">América/Fortaleza (GMT-3)</option>
                            <option value="America/Manaus">América/Manaus (GMT-4)</option>
                            <option value="America/Rio_Branco">América/Rio Branco (GMT-5)</option>
                            <option value="America/Noronha">Fernando de Noronha (GMT-2)</option>
                            <option value="UTC">UTC (GMT+0)</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="language">
                            <i class="fas fa-language"></i>
                            Idioma Padrão
                        </label>
                        <select class="form-control" id="language" name="language">
                            <option value="pt-BR" selected>Português (Brasil)</option>
                            <option value="en-US">English (US)</option>
                            <option value="es-ES">Español</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="send_welcome_email" 
                               name="send_welcome_email" 
                               value="1"
                               checked>
                        <label class="form-check-label" for="send_welcome_email">
                            <i class="fas fa-envelope"></i>
                            Enviar email de boas-vindas após instalação
                        </label>
                    </div>
                    
                    <div class="form-check mt-2">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="enable_debug" 
                               name="enable_debug" 
                               value="1">
                        <label class="form-check-label" for="enable_debug">
                            <i class="fas fa-bug"></i>
                            Habilitar modo debug (apenas para desenvolvimento)
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="form-actions mt-4">
            <div class="d-flex justify-content-between">
                <a href="?step=3" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> 
                    Voltar
                </a>
                
                <button type="submit" class="btn btn-success" id="submitBtn">
                    <i class="fas fa-user-plus"></i>
                    Criar Administrador e Finalizar
                </button>
            </div>
        </div>
    </form>
</div>
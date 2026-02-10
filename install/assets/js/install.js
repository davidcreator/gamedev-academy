/**
 * GameDev Academy - Installation Script
 * Sistema de instalação multi-step com navegação por URL
 * 
 * @author GameDev Academy
 * @version 2.0
 */

(function() {
    'use strict';

    // Configurações globais
    const CONFIG = {
        totalSteps: 5,
        baseUrl: 'index.php',
        requirementsUrl: 'check-requirements.php',
        testDbUrl: 'test-database.php',
        installUrl: 'install.php'
    };

    // Estado atual
    let currentStep = 1;
    let installationData = {};

    /**
     * Inicialização
     */
    function init() {
        console.log('🎮 GameDev Academy Installer v2.0');
        
        // Obter step atual da URL
        currentStep = getCurrentStepFromUrl();
        console.log('📍 Step atual:', currentStep);
        
        // Inicializar step atual
        initializeCurrentStep();
        
        // Configurar navegação
        setupNavigation();
        
        // Configurar validações
        setupValidations();
    }

    /**
     * Obter step atual da URL
     */
    function getCurrentStepFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        const step = parseInt(urlParams.get('step')) || 1;
        return Math.min(Math.max(step, 1), CONFIG.totalSteps);
    }

    /**
     * Inicializar step atual
     */
    function initializeCurrentStep() {
        console.log('🔧 Inicializando Step', currentStep);
        
        switch(currentStep) {
            case 1:
                initStep1();
                break;
            case 2:
                initStep2();
                break;
            case 3:
                initStep3();
                break;
            case 4:
                initStep4();
                break;
            case 5:
                initStep5();
                break;
        }
        
        updateProgressBar();
    }

    /**
     * Step 1: Verificação de Requisitos
     */
    function initStep1() {
        console.log('📋 Verificando requisitos...');
        checkRequirements();
    }

    /**
     * Step 2: Configuração do Banco de Dados
     */
    function initStep2() {
        console.log('💾 Configurando banco de dados...');
        
        // Configurar botão de teste
        const testBtn = document.getElementById('testDbBtn');
        if (testBtn) {
            testBtn.addEventListener('click', function(e) {
                e.preventDefault();
                testDatabaseConnection();
            });
        }
        
        // Carregar dados salvos se existirem
        loadSavedData(2);
    }

    /**
     * Step 3: Conta de Administrador
     */
    function initStep3() {
        console.log('👤 Configurando administrador...');
        
        setupPasswordValidation();
        loadSavedData(3);
    }

    /**
     * Step 4: Configurações Gerais
     */
    function initStep4() {
        console.log('⚙️ Configurações gerais...');
        
        // Auto-detectar URL do site
        autoDetectSiteUrl();
        loadSavedData(4);
    }

    /**
     * Step 5: Instalação Final
     */
    function initStep5() {
        console.log('🚀 Pronto para instalar...');
        
        displayInstallationSummary();
        
        const installBtn = document.getElementById('installBtn');
        if (installBtn) {
            installBtn.addEventListener('click', function(e) {
                e.preventDefault();
                startInstallation();
            });
        }
    }

    /**
     * Configurar navegação entre steps
     */
    function setupNavigation() {
        // Botões "Próximo"
        document.querySelectorAll('[data-action="next"]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const step = parseInt(this.getAttribute('data-step')) || currentStep;
                handleNextStep(step);
            });
        });
        
        // Botões "Anterior"
        document.querySelectorAll('[data-action="prev"]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const step = parseInt(this.getAttribute('data-step')) || currentStep;
                goToStep(step - 1);
            });
        });
        
        // Criar funções globais para compatibilidade
        window.nextStep = handleNextStep;
        window.previousStep = function(step) {
            goToStep((step || currentStep) - 1);
        };
        window.goToStep = goToStep;
        
        console.log('✅ Navegação configurada');
    }

    /**
     * Avançar para próximo step
     */
    function handleNextStep(step) {
        const stepToValidate = step || currentStep;
        
        console.log('➡️ Tentando avançar do step', stepToValidate);
        
        // Validar step atual
        if (!validateStep(stepToValidate)) {
            console.log('❌ Validação falhou');
            return false;
        }
        
        // Salvar dados
        saveStepData(stepToValidate);
        
        // Avançar
        goToStep(stepToValidate + 1);
    }

    /**
     * Ir para step específico
     */
    function goToStep(step) {
        if (step < 1 || step > CONFIG.totalSteps) {
            console.warn('⚠️ Step inválido:', step);
            return;
        }
        
        console.log('🔄 Navegando para step', step);
        window.location.href = CONFIG.baseUrl + '?step=' + step;
    }

    /**
     * Validar step atual
     */
    function validateStep(step) {
        console.log('✓ Validando step', step);
        
        switch(step) {
            case 1:
                return validateStep1();
            case 2:
                return validateStep2();
            case 3:
                return validateStep3();
            case 4:
                return validateStep4();
            default:
                return true;
        }
    }

    /**
     * Validar Step 1 (Requisitos)
     */
    function validateStep1() {
        const passed = sessionStorage.getItem('requirements_passed');
        
        if (passed !== 'true') {
            showAlert('Por favor, aguarde a verificação dos requisitos.', 'warning');
            return false;
        }
        
        return true;
    }

    /**
     * Validar Step 2 (Banco de Dados)
     */
    function validateStep2() {
        const form = document.getElementById('databaseForm');
        if (!form) return true;
        
        const host = document.getElementById('dbHost');
        const name = document.getElementById('dbName');
        const user = document.getElementById('dbUser');
        
        if (!host || !host.value || !name || !name.value || !user || !user.value) {
            showAlert('Preencha todos os campos obrigatórios do banco de dados.', 'error');
            return false;
        }
        
        // Verificar se testou a conexão
        const tested = sessionStorage.getItem('db_connection_tested');
        if (tested !== 'true') {
            showAlert('Por favor, teste a conexão com o banco de dados primeiro.', 'warning');
            return false;
        }
        
        return true;
    }

    /**
     * Validar Step 3 (Admin)
     */
    function validateStep3() {
        const form = document.getElementById('adminForm');
        if (!form) return true;
        
        const username = document.getElementById('adminUsername');
        const email = document.getElementById('adminEmail');
        const password = document.getElementById('adminPassword');
        const confirm = document.getElementById('adminPasswordConfirm');
        
        // Validações básicas
        if (!username || !username.value) {
            showAlert('Digite o nome de usuário do administrador.', 'error');
            return false;
        }
        
        if (!email || !email.value || !isValidEmail(email.value)) {
            showAlert('Digite um email válido.', 'error');
            return false;
        }
        
        if (!password || !password.value || password.value.length < 8) {
            showAlert('A senha deve ter no mínimo 8 caracteres.', 'error');
            return false;
        }
        
        if (!confirm || password.value !== confirm.value) {
            showAlert('As senhas não coincidem.', 'error');
            return false;
        }
        
        return true;
    }

    /**
     * Validar Step 4 (Configurações)
     */
    function validateStep4() {
        const form = document.getElementById('configForm');
        if (!form) return true;
        
        const siteName = document.getElementById('siteName');
        const siteUrl = document.getElementById('siteUrl');
        
        if (!siteName || !siteName.value) {
            showAlert('Digite o nome do site.', 'error');
            return false;
        }
        
        if (!siteUrl || !siteUrl.value) {
            showAlert('Digite a URL do site.', 'error');
            return false;
        }
        
        return true;
    }

    /**
     * Salvar dados do step
     */
    function saveStepData(step) {
        let formId = '';
        
        switch(step) {
            case 2: formId = 'databaseForm'; break;
            case 3: formId = 'adminForm'; break;
            case 4: formId = 'configForm'; break;
        }
        
        if (!formId) return;
        
        const form = document.getElementById(formId);
        if (!form) return;
        
        const formData = new FormData(form);
        const data = {};
        
        formData.forEach((value, key) => {
            // Não salvar senhas no sessionStorage
            if (!key.includes('password')) {
                data[key] = value;
            }
            // Guardar em memória para instalação
            installationData[key] = value;
        });
        
        sessionStorage.setItem('step' + step + '_data', JSON.stringify(data));
        console.log('💾 Dados do step', step, 'salvos');
    }

    /**
     * Carregar dados salvos
     */
    function loadSavedData(step) {
        const savedData = sessionStorage.getItem('step' + step + '_data');
        if (!savedData) return;
        
        try {
            const data = JSON.parse(savedData);
            
            for (const [key, value] of Object.entries(data)) {
                const input = document.querySelector(`[name="${key}"]`);
                if (input && !key.includes('password')) {
                    input.value = value;
                }
            }
            
            console.log('📥 Dados do step', step, 'carregados');
        } catch (e) {
            console.error('Erro ao carregar dados:', e);
        }
    }

    /**
     * Verificar requisitos do sistema
     */
    function checkRequirements() {
        const listContainer = document.getElementById('requirementsList');
        if (!listContainer) return;
        
        // Mostrar loading
        listContainer.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Verificando requisitos...</div>';
        
        fetch(CONFIG.requirementsUrl)
            .then(response => response.json())
            .then(data => {
                displayRequirements(data);
                
                // Habilitar botão se passou
                if (data.all_passed) {
                    sessionStorage.setItem('requirements_passed', 'true');
                    enableNextButton();
                } else {
                    sessionStorage.setItem('requirements_passed', 'false');
                    disableNextButton();
                }
            })
            .catch(error => {
                console.error('Erro ao verificar requisitos:', error);
                listContainer.innerHTML = '<div class="alert alert-danger">Erro ao verificar requisitos.</div>';
                // Mesmo com erro, permitir continuar
                enableNextButton();
            });
    }

    /**
     * Exibir requisitos
     */
    function displayRequirements(data) {
        const container = document.getElementById('requirementsList');
        if (!container) return;
        
        let html = '';
        
        // PHP Version
        if (data.php_version) {
            const icon = data.php_version.passed ? 'check-circle' : 'times-circle';
            const cssClass = data.php_version.passed ? 'success' : 'danger';
            
            html += `
                <div class="requirement-item ${cssClass}">
                    <i class="fas fa-${icon}"></i>
                    <div class="requirement-details">
                        <strong>PHP ${data.php_version.current}</strong>
                        <small>${data.php_version.passed ? '✓ Versão adequada' : '✗ Versão ' + data.php_version.required + ' ou superior necessária'}</small>
                    </div>
                </div>
            `;
        }
        
        // Extensions
        if (data.extensions) {
            for (const [ext, loaded] of Object.entries(data.extensions)) {
                const icon = loaded ? 'check-circle' : 'times-circle';
                const cssClass = loaded ? 'success' : 'danger';
                
                html += `
                    <div class="requirement-item ${cssClass}">
                        <i class="fas fa-${icon}"></i>
                        <div class="requirement-details">
                            <strong>${ext.toUpperCase()}</strong>
                            <small>${loaded ? '✓ Instalada' : '✗ Não encontrada'}</small>
                        </div>
                    </div>
                `;
            }
        }
        
        // Permissions
        if (data.permissions) {
            for (const [dir, writable] of Object.entries(data.permissions)) {
                const icon = writable ? 'check-circle' : 'exclamation-triangle';
                const cssClass = writable ? 'success' : 'warning';
                
                html += `
                    <div class="requirement-item ${cssClass}">
                        <i class="fas fa-${icon}"></i>
                        <div class="requirement-details">
                            <strong>/${dir}</strong>
                            <small>${writable ? '✓ Gravável' : '⚠ Sem permissão de escrita'}</small>
                        </div>
                    </div>
                `;
            }
        }
        
        container.innerHTML = html;
    }

    /**
     * Testar conexão com banco de dados
     */
    function testDatabaseConnection() {
        const form = document.getElementById('databaseForm');
        if (!form) return;
        
        const resultDiv = document.getElementById('dbTestResult');
        const testBtn = document.getElementById('testDbBtn');
        
        // Desabilitar botão
        if (testBtn) {
            testBtn.disabled = true;
            testBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testando...';
        }
        
        // Mostrar loading
        if (resultDiv) {
            resultDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Testando conexão...</div>';
        }
        
        const formData = new FormData(form);
        
        fetch(CONFIG.testDbUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                sessionStorage.setItem('db_connection_tested', 'true');
                if (resultDiv) {
                    resultDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' + (data.message || 'Conexão estabelecida!') + '</div>';
                }
            } else {
                sessionStorage.setItem('db_connection_tested', 'false');
                if (resultDiv) {
                    resultDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> ' + (data.message || 'Erro na conexão') + '</div>';
                }
            }
        })
        .catch(error => {
            sessionStorage.setItem('db_connection_tested', 'false');
            if (resultDiv) {
                resultDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Erro: ' + error.message + '</div>';
            }
        })
        .finally(() => {
            if (testBtn) {
                testBtn.disabled = false;
                testBtn.innerHTML = '<i class="fas fa-vial"></i> Testar Conexão';
            }
        });
    }

    /**
     * Configurar validação de senha
     */
    function setupPasswordValidation() {
        const password = document.getElementById('adminPassword');
        const confirm = document.getElementById('adminPasswordConfirm');
        
        if (password) {
            password.addEventListener('input', function() {
                validatePasswordStrength(this.value);
            });
        }
        
        if (confirm) {
            confirm.addEventListener('input', function() {
                if (password && this.value !== password.value) {
                    this.setCustomValidity('As senhas não coincidem');
                    this.classList.add('is-invalid');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('is-invalid');
                }
            });
        }
    }

    /**
     * Validar força da senha
     */
    function validatePasswordStrength(password) {
        const strengthDiv = document.getElementById('passwordStrength');
        if (!strengthDiv) return;
        
        let strength = 0;
        
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/)) strength++;
        if (password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;
        
        let level = 'weak';
        let text = 'Fraca';
        
        if (strength >= 4) {
            level = 'strong';
            text = 'Forte';
        } else if (strength >= 3) {
            level = 'medium';
            text = 'Média';
        }
        
        strengthDiv.innerHTML = `
            <div class="strength-bar ${level}">
                <div class="strength-fill" style="width: ${strength * 20}%"></div>
            </div>
            <small class="strength-text">Força: ${text}</small>
        `;
        
        // Atualizar requisitos visuais
        updatePasswordRequirements(password);
    }

    /**
     * Atualizar requisitos de senha
     */
    function updatePasswordRequirements(password) {
        const requirements = {
            'req-length': password.length >= 8,
            'req-lowercase': /[a-z]/.test(password),
            'req-uppercase': /[A-Z]/.test(password),
            'req-number': /[0-9]/.test(password)
        };
        
        for (const [id, valid] of Object.entries(requirements)) {
            const element = document.getElementById(id);
            if (element) {
                element.className = valid ? 'valid' : 'invalid';
                const icon = element.querySelector('i');
                if (icon) {
                    icon.className = valid ? 'fas fa-check' : 'fas fa-times';
                }
            }
        }
    }

    /**
     * Auto-detectar URL do site
     */
    function autoDetectSiteUrl() {
        const siteUrl = document.getElementById('siteUrl');
        if (siteUrl && !siteUrl.value) {
            const url = window.location.href;
            const baseUrl = url.substring(0, url.lastIndexOf('/install'));
            siteUrl.value = baseUrl;
        }
    }

    /**
     * Exibir resumo da instalação
     */
    function displayInstallationSummary() {
        const summaryDiv = document.getElementById('installationSummary');
        if (!summaryDiv) return;
        
        // Coletar todos os dados
        for (let i = 2; i <= 4; i++) {
            const saved = sessionStorage.getItem('step' + i + '_data');
            if (saved) {
                Object.assign(installationData, JSON.parse(saved));
            }
        }
        
        const html = `
            <div class="summary-section">
                <h5><i class="fas fa-database"></i> Banco de Dados</h5>
                <p><strong>Host:</strong> ${installationData.db_host || 'localhost'}</p>
                <p><strong>Banco:</strong> ${installationData.db_name || 'gamedev_academy'}</p>
                <p><strong>Usuário:</strong> ${installationData.db_user || 'root'}</p>
            </div>
            
            <div class="summary-section">
                <h5><i class="fas fa-user-shield"></i> Administrador</h5>
                <p><strong>Usuário:</strong> ${installationData.admin_username || 'N/A'}</p>
                <p><strong>Email:</strong> ${installationData.admin_email || 'N/A'}</p>
            </div>
            
            <div class="summary-section">
                <h5><i class="fas fa-cog"></i> Configurações</h5>
                <p><strong>Nome:</strong> ${installationData.site_name || 'GameDev Academy'}</p>
                <p><strong>URL:</strong> ${installationData.site_url || 'N/A'}</p>
            </div>
        `;
        
        summaryDiv.innerHTML = html;
    }

    /**
     * Iniciar instalação
     */
    function startInstallation() {
        const installBtn = document.getElementById('installBtn');
        const progressDiv = document.getElementById('installationProgress');
        const resultDiv = document.getElementById('installationResult');
        
        if (installBtn) installBtn.disabled = true;
        if (progressDiv) progressDiv.style.display = 'block';
        
        // Simular progresso
        simulateInstallProgress();
        
        // Enviar dados
        fetch(CONFIG.installUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(installationData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showInstallSuccess(resultDiv);
            } else {
                showInstallError(resultDiv, data.message);
            }
        })
        .catch(error => {
            showInstallError(resultDiv, error.message);
        });
    }

    /**
     * Simular progresso da instalação
     */
    function simulateInstallProgress() {
        const bar = document.getElementById('installProgressBar');
        const status = document.getElementById('installStatus');
        
        const steps = [
            { p: 20, t: 'Criando configuração...' },
            { p: 40, t: 'Conectando ao banco...' },
            { p: 60, t: 'Criando tabelas...' },
            { p: 80, t: 'Inserindo dados...' },
            { p: 100, t: 'Finalizando...' }
        ];
        
        steps.forEach((step, i) => {
            setTimeout(() => {
                if (bar) {
                    bar.style.width = step.p + '%';
                    bar.textContent = step.p + '%';
                }
                if (status) status.textContent = step.t;
            }, i * 1500);
        });
    }

    /**
     * Mostrar sucesso
     */
    function showInstallSuccess(container) {
        if (!container) return;
        
        container.innerHTML = `
            <div class="alert alert-success">
                <h4><i class="fas fa-check-circle"></i> Instalação Concluída!</h4>
                <p>O GameDev Academy foi instalado com sucesso.</p>
                <hr>
                <a href="../login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Fazer Login
                </a>
                <a href="../index.php" class="btn btn-outline-primary">
                    <i class="fas fa-home"></i> Ir para o Site
                </a>
            </div>
            <div class="alert alert-warning mt-3">
                <strong>Importante:</strong> Delete a pasta /install por segurança.
            </div>
        `;
        
        document.getElementById('installationProgress').style.display = 'none';
    }

    /**
     * Mostrar erro
     */
    function showInstallError(container, message) {
        if (!container) return;
        
        container.innerHTML = `
            <div class="alert alert-danger">
                <h4><i class="fas fa-times-circle"></i> Erro na Instalação</h4>
                <p>${message || 'Erro desconhecido'}</p>
            </div>
        `;
        
        const installBtn = document.getElementById('installBtn');
        if (installBtn) {
            installBtn.disabled = false;
            installBtn.textContent = 'Tentar Novamente';
        }
    }

    /**
     * Atualizar barra de progresso
     */
    function updateProgressBar() {
        const bar = document.querySelector('.progress-bar');
        if (!bar) return;
        
        const progress = ((currentStep - 1) / (CONFIG.totalSteps - 1)) * 100;
        bar.style.width = progress + '%';
    }

    /**
     * Habilitar botão próximo
     */
    function enableNextButton() {
        const btn = document.querySelector('[data-action="next"]') || 
                    document.getElementById('step' + currentStep + 'Next');
        if (btn) {
            btn.disabled = false;
            btn.classList.remove('disabled');
        }
    }

    /**
     * Desabilitar botão próximo
     */
    function disableNextButton() {
        const btn = document.querySelector('[data-action="next"]') || 
                    document.getElementById('step' + currentStep + 'Next');
        if (btn) {
            btn.disabled = true;
            btn.classList.add('disabled');
        }
    }

    /**
     * Mostrar alerta
     */
    function showAlert(message, type = 'info') {
        // Criar elemento de alerta
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Inserir no topo da página
        const container = document.querySelector('.installer-content') || document.body;
        container.insertBefore(alert, container.firstChild);
        
        // Auto-remover após 5 segundos
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }

    /**
     * Validar email
     */
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    /**
     * Configurar validações gerais
     */
    function setupValidations() {
        // Adicionar validações conforme necessário
    }

    // Inicializar quando DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expor funções globais
    window.GameDevInstaller = {
        goToStep,
        nextStep: handleNextStep,
        previousStep: function(s) { goToStep((s || currentStep) - 1); },
        getCurrentStep: function() { return currentStep; }
    };

})();
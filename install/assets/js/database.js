/**
 * GameDev Academy - Database Configuration Handler
 * Step 2 - Database Connection Testing and Validation
 * @version 2.0
 */

(function() {
    'use strict';

    const DatabaseHandler = {
        
        // Configuration
        config: {
            ajaxTimeout: 30000, // 30 seconds
            debugMode: true, // Set to false in production
            retryAttempts: 0,
            maxRetries: 2
        },

        /**
         * Initialize all handlers
         */
        init: function() {
            console.log('🔧 Database Handler initialized');
            
            this.initTestConnection();
            this.initPasswordToggle();
            this.initFormValidation();
            this.initAutoFillDetection();
            this.bindCloseButtons();
        },

        /**
         * Initialize test connection functionality
         */
        initTestConnection: function() {
            const testBtn = document.getElementById('testConnectionBtn');
            if (!testBtn) {
                this.debug('Test connection button not found');
                return;
            }

            testBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.config.retryAttempts = 0;
                this.testConnection();
            });

            // Reset test results when any field changes
            const fields = ['db_host', 'db_port', 'db_user', 'db_pass', 'db_name'];
            fields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.addEventListener('input', () => {
                        this.resetTestResults();
                    });
                }
            });

            this.debug('Test connection initialized');
        },

        /**
         * Reset test results
         */
        resetTestResults: function() {
            const resultsDiv = document.getElementById('testResults');
            const submitBtn = document.getElementById('submitBtn');
            
            if (resultsDiv && resultsDiv.style.display !== 'none') {
                resultsDiv.style.display = 'none';
                resultsDiv.className = 'test-results';
                resultsDiv.innerHTML = '';
            }
            
            if (submitBtn) {
                submitBtn.disabled = true;
            }
        },

        /**
         * Test database connection
         */
        testConnection: function() {
            const form = document.getElementById('databaseForm');
            if (!form) {
                this.debug('Database form not found', 'error');
                return;
            }

            // Get UI elements
            const testBtn = document.getElementById('testConnectionBtn');
            const resultsDiv = document.getElementById('testResults');
            const submitBtn = document.getElementById('submitBtn');
            
            // Validate required fields first
            const validation = this.validateRequiredFields();
            if (!validation.isValid) {
                this.showAlert(validation.message, 'danger');
                return;
            }

            // Update button state
            this.setButtonLoading(testBtn, true, 'Testando...');
            
            // Show loading in results
            if (resultsDiv) {
                resultsDiv.style.display = 'block';
                resultsDiv.className = 'test-results';
                resultsDiv.innerHTML = `
                    <div class="result-item">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span>Testando conexão com o banco de dados...</span>
                    </div>
                `;
            }

            // Prepare form data
            const formData = new FormData(form);
            
            this.debug('Testing connection with data:', {
                host: formData.get('db_host'),
                port: formData.get('db_port'),
                user: formData.get('db_user'),
                database: formData.get('db_name')
            });

            // Create abort controller for timeout
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), this.config.ajaxTimeout);

            // Make AJAX request
            fetch('ajax/test_connection.php', {
                method: 'POST',
                body: formData,
                signal: controller.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                clearTimeout(timeoutId);
                
                this.debug('Response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                return response.text();
            })
            .then(text => {
                this.debug('Raw response:', text);
                
                // Check if response is HTML/PHP error
                if (this.isPHPError(text)) {
                    this.logPHPError(text);
                    throw new Error('Erro PHP no servidor. Verifique o arquivo de log ou console do navegador.');
                }
                
                // Try to parse JSON
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    this.debug('JSON parse error:', e);
                    this.debug('Response text:', text);
                    throw new Error('Resposta inválida do servidor (não é JSON válido)');
                }
                
                this.debug('Parsed data:', data);
                
                // Handle response
                if (data.success) {
                    this.handleTestSuccess(data, resultsDiv, submitBtn);
                } else {
                    this.handleTestError(data, resultsDiv, submitBtn);
                }
            })
            .catch(error => {
                clearTimeout(timeoutId);
                
                this.debug('Fetch error:', error);
                
                // Handle specific errors
                let errorMessage = error.message;
                
                if (error.name === 'AbortError') {
                    errorMessage = 'Tempo limite excedido. Verifique se o servidor MySQL está respondendo.';
                } else if (error.message.includes('Failed to fetch')) {
                    errorMessage = 'Não foi possível conectar ao servidor. Verifique sua conexão.';
                } else if (error.message.includes('HTTP 500')) {
                    errorMessage = 'Erro interno do servidor. Verifique os logs do PHP.';
                } else if (error.message.includes('HTTP 404')) {
                    errorMessage = 'Arquivo de teste não encontrado. Verifique se ajax/test_connection.php existe.';
                }
                
                this.handleTestError(
                    { 
                        message: errorMessage,
                        technical: error.message 
                    }, 
                    resultsDiv, 
                    submitBtn
                );
                
                // Auto retry on certain errors
                if (this.shouldRetry(error) && this.config.retryAttempts < this.config.maxRetries) {
                    this.config.retryAttempts++;
                    this.debug(`Retrying... Attempt ${this.config.retryAttempts} of ${this.config.maxRetries}`);
                    setTimeout(() => this.testConnection(), 2000);
                }
            })
            .finally(() => {
                this.setButtonLoading(testBtn, false, 'Testar Conexão');
            });
        },

        /**
         * Check if response is PHP error
         */
        isPHPError: function(text) {
            const errorIndicators = [
                '<?php',
                '<br',
                'Fatal error',
                'Parse error',
                'Warning:',
                'Notice:',
                'Undefined',
                '<html',
                '<!DOCTYPE'
            ];
            
            const lowerText = text.toLowerCase();
            return errorIndicators.some(indicator => lowerText.includes(indicator.toLowerCase()));
        },

        /**
         * Log PHP error to console
         */
        logPHPError: function(text) {
            console.group('🔴 PHP Error Detected');
            console.error('The server returned HTML/PHP error instead of JSON:');
            console.log(text);
            console.groupEnd();
            
            // Show in alert for debugging
            if (this.config.debugMode) {
                this.showAlert('Erro PHP detectado. Verifique o console (F12) para detalhes.', 'danger');
            }
        },

        /**
         * Validate required fields
         */
        validateRequiredFields: function() {
            const requiredFields = [
                { id: 'db_host', name: 'Servidor' },
                { id: 'db_user', name: 'Usuário' },
                { id: 'db_name', name: 'Nome do Banco' }
            ];
            
            const missingFields = [];
            
            requiredFields.forEach(field => {
                const input = document.getElementById(field.id);
                if (!input || !input.value.trim()) {
                    input?.classList.add('is-invalid');
                    missingFields.push(field.name);
                } else {
                    input?.classList.remove('is-invalid');
                }
            });

            if (missingFields.length > 0) {
                return {
                    isValid: false,
                    message: `Campos obrigatórios não preenchidos: ${missingFields.join(', ')}`
                };
            }

            return { isValid: true };
        },

        /**
         * Handle successful test result
         */
        handleTestSuccess: function(data, resultsDiv, submitBtn) {
            this.debug('Connection test successful', data);
            
            if (!resultsDiv) return;
            
            resultsDiv.className = 'test-results success';
            
            let html = `
                <div class="result-item">
                    <i class="fas fa-check-circle"></i>
                    <strong>Conexão estabelecida com sucesso!</strong>
                </div>
            `;
            
            if (data.server_info) {
                html += `
                    <div class="result-item">
                        <i class="fas fa-server"></i>
                        <span>Servidor: ${this.escapeHtml(data.server_info)}</span>
                    </div>
                `;
            }
            
            if (data.database) {
                html += `
                    <div class="result-item">
                        <i class="fas fa-database"></i>
                        <span>Banco de dados: ${this.escapeHtml(data.database)}</span>
                    </div>
                `;
            }
            
            if (data.privileges && data.privileges.length > 0) {
                const privText = data.privileges.join(', ');
                html += `
                    <div class="result-item">
                        <i class="fas fa-key"></i>
                        <span>Privilégios: ${this.escapeHtml(privText)}</span>
                    </div>
                `;
            }
            
            if (data.warning) {
                html += `
                    <div class="result-item" style="color: #f59e0b;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>${this.escapeHtml(data.warning)}</span>
                    </div>
                `;
            }
            
            // Show debug info if available and debug mode is on
            if (this.config.debugMode && data.debug) {
                html += `
                    <details style="margin-top: 10px;">
                        <summary style="cursor: pointer; font-size: 0.85rem; color: #64748b;">
                            Debug Info (apenas desenvolvimento)
                        </summary>
                        <pre style="font-size: 0.75rem; background: #f1f5f9; padding: 10px; border-radius: 5px; margin-top: 5px;">
${JSON.stringify(data.debug, null, 2)}
                        </pre>
                    </details>
                `;
            }
            
            resultsDiv.innerHTML = html;
            
            // Enable submit button
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('btn-secondary');
                submitBtn.classList.add('btn-primary');
            }
            
            // Mark fields as valid
            ['db_host', 'db_user', 'db_name'].forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field && field.value) {
                    field.classList.add('is-valid');
                    field.classList.remove('is-invalid');
                }
            });
            
            // Show success alert
            this.showAlert('✓ Conexão testada com sucesso! Você pode continuar.', 'success');
        },

        /**
         * Handle test error
         */
        handleTestError: function(data, resultsDiv, submitBtn) {
            this.debug('Connection test failed', data);
            
            if (!resultsDiv) return;
            
            resultsDiv.className = 'test-results error';
            
            let html = `
                <div class="result-item">
                    <i class="fas fa-times-circle"></i>
                    <strong>Falha na conexão</strong>
                </div>
                <div class="result-item">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>${this.escapeHtml(data.message || 'Erro desconhecido')}</span>
                </div>
            `;
            
            // Add technical details if available
            if (data.technical && this.config.debugMode) {
                html += `
                    <div class="result-item" style="margin-top: 10px;">
                        <small style="color: #64748b;">
                            <strong>Detalhes técnicos:</strong><br>
                            ${this.escapeHtml(data.technical)}
                        </small>
                    </div>
                `;
            }
            
            // Add helpful suggestions
            html += `
                <div class="result-item" style="margin-top: 10px; border-top: 1px solid rgba(0,0,0,0.1); padding-top: 10px;">
                    <small><strong>Dicas:</strong></small>
                    <ul style="margin: 5px 0 0 20px; font-size: 0.85rem;">
                        <li>Verifique se o MySQL está rodando</li>
                        <li>Confirme o host (geralmente 'localhost' ou '127.0.0.1')</li>
                        <li>Verifique usuário e senha</li>
                        <li>Certifique-se que o usuário tem permissões necessárias</li>
                    </ul>
                </div>
            `;
            
            // Show debug info if available
            if (this.config.debugMode && data.debug) {
                html += `
                    <details style="margin-top: 10px;">
                        <summary style="cursor: pointer; font-size: 0.85rem; color: #64748b;">
                            Debug Info
                        </summary>
                        <pre style="font-size: 0.75rem; background: #fef2f2; padding: 10px; border-radius: 5px; margin-top: 5px;">
${JSON.stringify(data.debug, null, 2)}
                        </pre>
                    </details>
                `;
            }
            
            resultsDiv.innerHTML = html;
            
            // Disable submit button
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.remove('btn-primary');
                submitBtn.classList.add('btn-secondary');
            }
            
            // Mark fields as invalid
            ['db_host', 'db_user', 'db_name'].forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.classList.remove('is-valid');
                }
            });
        },

        /**
         * Check if error should trigger retry
         */
        shouldRetry: function(error) {
            const retryableErrors = [
                'Failed to fetch',
                'NetworkError',
                'timeout'
            ];
            
            return retryableErrors.some(err => 
                error.message.toLowerCase().includes(err.toLowerCase())
            );
        },

        /**
         * Set button loading state
         */
        setButtonLoading: function(button, isLoading, text) {
            if (!button) return;
            
            if (isLoading) {
                button.disabled = true;
                button.dataset.originalText = button.innerHTML;
                button.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${text}`;
            } else {
                button.disabled = false;
                button.innerHTML = button.dataset.originalText || text;
            }
        },

        /**
         * Initialize password toggle
         */
        initPasswordToggle: function() {
            document.querySelectorAll('.btn-toggle-password').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const targetId = this.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    const icon = this.querySelector('i');
                    
                    if (input && icon) {
                        if (input.type === 'password') {
                            input.type = 'text';
                            icon.classList.remove('fa-eye');
                            icon.classList.add('fa-eye-slash');
                            this.setAttribute('aria-label', 'Ocultar senha');
                        } else {
                            input.type = 'password';
                            icon.classList.remove('fa-eye-slash');
                            icon.classList.add('fa-eye');
                            this.setAttribute('aria-label', 'Mostrar senha');
                        }
                    }
                });
            });
            
            this.debug('Password toggle initialized');
        },

        /**
         * Initialize form validation
         */
        initFormValidation: function() {
            const form = document.getElementById('databaseForm');
            if (!form) return;

            // Real-time validation
            const inputs = form.querySelectorAll('input[required]');
            inputs.forEach(input => {
                input.addEventListener('blur', () => {
                    this.validateField(input);
                });

                input.addEventListener('input', () => {
                    if (input.classList.contains('is-invalid')) {
                        this.validateField(input);
                    }
                });
            });

            // Form submit validation
            form.addEventListener('submit', (e) => {
                let isValid = true;
                
                inputs.forEach(input => {
                    if (!this.validateField(input)) {
                        isValid = false;
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    this.showAlert('Por favor, corrija os erros antes de continuar', 'danger');
                    
                    // Focus first invalid field
                    const firstInvalid = form.querySelector('.is-invalid');
                    if (firstInvalid) {
                        firstInvalid.focus();
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
            
            this.debug('Form validation initialized');
        },

        /**
         * Validate a single field
         */
        validateField: function(input) {
            const value = input.value.trim();
            const name = input.name;
            let isValid = true;
            let errorMessage = '';

            // Remove previous validation
            input.classList.remove('is-valid', 'is-invalid');

            if (input.hasAttribute('required') && !value) {
                isValid = false;
                errorMessage = 'Este campo é obrigatório';
            } else if (name === 'db_port' && value) {
                const port = parseInt(value);
                if (isNaN(port) || port < 1 || port > 65535) {
                    isValid = false;
                    errorMessage = 'Porta deve ser entre 1 e 65535';
                }
            } else if (name === 'db_name' && value) {
                if (!/^[a-zA-Z0-9_]+$/.test(value)) {
                    isValid = false;
                    errorMessage = 'Use apenas letras, números e underscore';
                } else if (value.length > 64) {
                    isValid = false;
                    errorMessage = 'Nome muito longo (máximo 64 caracteres)';
                }
            } else if (name === 'db_prefix' && value) {
                if (!/^[a-z0-9_]*$/.test(value)) {
                    isValid = false;
                    errorMessage = 'Use apenas letras minúsculas, números e underscore';
                } else if (value.length > 20) {
                    isValid = false;
                    errorMessage = 'Prefixo muito longo (máximo 20 caracteres)';
                }
            } else if (name === 'db_user' && value) {
                if (value.length > 32) {
                    isValid = false;
                    errorMessage = 'Nome de usuário muito longo';
                }
            }

            // Apply validation classes
            if (value) {
                input.classList.add(isValid ? 'is-valid' : 'is-invalid');
            }

            // Update error message
            this.updateFieldError(input, isValid ? '' : errorMessage);

            return isValid;
        },

        /**
         * Update field error message
         */
        updateFieldError: function(input, message) {
            const parent = input.closest('.input-group') || input.parentElement;
            let feedback = parent.querySelector('.invalid-feedback');
            
            if (!feedback && message) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                parent.appendChild(feedback);
            }

            if (feedback) {
                feedback.textContent = message;
                feedback.style.display = message ? 'block' : 'none';
            }
        },

        /**
         * Auto-detect and fill default values
         */
        initAutoFillDetection: function() {
            const hostField = document.getElementById('db_host');
            const portField = document.getElementById('db_port');
            
            // Auto-fill host if empty
            if (hostField && !hostField.value) {
                if (window.location.hostname === 'localhost' || 
                    window.location.hostname === '127.0.0.1') {
                    hostField.value = 'localhost';
                    this.debug('Auto-filled host: localhost');
                }
            }
            
            // Auto-fill port if empty
            if (portField && !portField.value) {
                portField.value = '3306';
                this.debug('Auto-filled port: 3306');
            }
        },

        /**
         * Show alert message
         */
        showAlert: function(message, type = 'info') {
            const container = document.querySelector('.database-config') || 
                            document.querySelector('.card-body');
            if (!container) return;

            // Remove existing custom alerts
            const existingAlert = container.querySelector('.alert-custom');
            if (existingAlert) {
                existingAlert.remove();
            }

            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show alert-custom`;
            alertDiv.style.marginBottom = '1rem';
            alertDiv.innerHTML = `
                <i class="fas fa-${this.getAlertIcon(type)}"></i>
                <span>${this.escapeHtml(message)}</span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            `;

            container.insertBefore(alertDiv, container.firstChild);

            // Auto dismiss after 8 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.classList.remove('show');
                    setTimeout(() => alertDiv.remove(), 150);
                }
            }, 8000);
        },

        /**
         * Bind close buttons for alerts
         */
        bindCloseButtons: function() {
            document.addEventListener('click', (e) => {
                if (e.target.matches('[data-dismiss="alert"]') || 
                    e.target.closest('[data-dismiss="alert"]')) {
                    const alert = e.target.closest('.alert');
                    if (alert) {
                        alert.classList.remove('show');
                        setTimeout(() => alert.remove(), 150);
                    }
                }
            });
        },

        /**
         * Get alert icon based on type
         */
        getAlertIcon: function(type) {
            const icons = {
                success: 'check-circle',
                danger: 'exclamation-circle',
                warning: 'exclamation-triangle',
                info: 'info-circle'
            };
            return icons[type] || 'info-circle';
        },

        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml: function(text) {
            if (typeof text !== 'string') {
                text = String(text);
            }
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        /**
         * Debug logger
         */
        debug: function(...args) {
            if (this.config.debugMode) {
                console.log('[Database Handler]', ...args);
            }
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            DatabaseHandler.init();
        });
    } else {
        DatabaseHandler.init();
    }

    // Expose to global scope for debugging
    window.DatabaseHandler = DatabaseHandler;

})();
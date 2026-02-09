/**
 * GameDev Academy - Admin Setup Handler
 * Step 4 - Password validation, email validation, and password generator
 * @version 2.0
 */

(function() {
    'use strict';

    const AdminHandler = {
        
        // Configuration
        config: {
            minPasswordLength: 8,
            passwordChars: {
                lowercase: 'abcdefghijklmnopqrstuvwxyz',
                uppercase: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                numbers: '0123456789',
                special: '!@#$%^&*()_+-=[]{}|;:,.<>?'
            }
        },

        /**
         * Initialize all handlers
         */
        init: function() {
            console.log('🔧 Admin Handler initialized');
            
            this.initPasswordValidation();
            this.initPasswordGenerator();
            this.initPasswordToggle();
            this.initEmailValidation();
            this.initFormValidation();
            this.initEmailConfig();
            this.initCharacterCount();
        },

        // ========================================
        // PASSWORD VALIDATION
        // ========================================

        /**
         * Initialize password strength checker
         */
        initPasswordValidation: function() {
            const passwordField = document.getElementById('admin_password');
            const confirmField = document.getElementById('admin_password_confirm');
            
            if (!passwordField) return;

            // Password input handler
            passwordField.addEventListener('input', () => {
                this.checkPasswordStrength(passwordField.value);
                this.checkPasswordMatch();
            });

            passwordField.addEventListener('focus', () => {
                const requirements = document.querySelector('.password-requirements');
                if (requirements) requirements.style.display = 'block';
            });

            // Confirm password handler
            if (confirmField) {
                confirmField.addEventListener('input', () => {
                    this.checkPasswordMatch();
                });
            }
        },

        /**
         * Check password strength and update UI
         */
        checkPasswordStrength: function(password) {
            const requirements = {
                length: password.length >= this.config.minPasswordLength,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(password)
            };

            // Update requirement indicators
            this.updateRequirement('req-length', requirements.length);
            this.updateRequirement('req-uppercase', requirements.uppercase);
            this.updateRequirement('req-lowercase', requirements.lowercase);
            this.updateRequirement('req-number', requirements.number);
            this.updateRequirement('req-special', requirements.special);

            // Calculate strength score
            const passedCount = Object.values(requirements).filter(v => v).length;
            const strength = this.calculateStrength(password, passedCount);

            // Update strength bar
            this.updateStrengthBar(strength);

            // Update field validation state
            const field = document.getElementById('admin_password');
            if (passedCount === 5) {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
            } else if (password.length > 0) {
                field.classList.remove('is-valid');
                field.classList.add('is-invalid');
            } else {
                field.classList.remove('is-valid', 'is-invalid');
            }

            return passedCount === 5;
        },

        /**
         * Update requirement indicator
         */
        updateRequirement: function(id, passed) {
            const element = document.getElementById(id);
            if (!element) return;

            const icon = element.querySelector('i');
            
            if (passed) {
                element.classList.remove('invalid');
                element.classList.add('valid');
                if (icon) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-check');
                }
            } else {
                element.classList.remove('valid');
                element.classList.add('invalid');
                if (icon) {
                    icon.classList.remove('fa-check');
                    icon.classList.add('fa-times');
                }
            }
        },

        /**
         * Calculate overall password strength
         */
        calculateStrength: function(password, passedCount) {
            if (password.length === 0) {
                return { level: 'none', text: 'Digite uma senha', class: '' };
            }
            
            if (passedCount <= 1) {
                return { level: 'weak', text: 'Muito fraca', class: 'weak' };
            }
            
            if (passedCount === 2) {
                return { level: 'fair', text: 'Fraca', class: 'weak' };
            }
            
            if (passedCount === 3) {
                return { level: 'medium', text: 'Média', class: 'fair' };
            }
            
            if (passedCount === 4) {
                return { level: 'good', text: 'Boa', class: 'good' };
            }
            
            // All 5 requirements met
            if (password.length >= 12) {
                return { level: 'excellent', text: 'Excelente', class: 'strong' };
            }
            
            return { level: 'strong', text: 'Forte', class: 'strong' };
        },

        /**
         * Update strength bar UI
         */
        updateStrengthBar: function(strength) {
            const fill = document.getElementById('passwordStrengthFill');
            const text = document.getElementById('passwordStrengthText');
            
            if (!fill || !text) return;

            // Remove all classes
            fill.className = 'password-strength-fill';
            text.className = 'password-strength-text';

            // Add new class
            if (strength.class) {
                fill.classList.add(strength.class);
                text.classList.add(strength.class);
            }

            // Update text
            text.innerHTML = `<i class="fas fa-shield-alt"></i> <span>${strength.text}</span>`;
        },

        /**
         * Check if passwords match
         */
        checkPasswordMatch: function() {
            const password = document.getElementById('admin_password');
            const confirm = document.getElementById('admin_password_confirm');
            const status = document.getElementById('passwordMatchStatus');
            
            if (!password || !confirm || !status) return false;

            const passwordVal = password.value;
            const confirmVal = confirm.value;

            if (confirmVal.length === 0) {
                status.innerHTML = '';
                confirm.classList.remove('is-valid', 'is-invalid');
                return false;
            }

            if (passwordVal === confirmVal) {
                status.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> Senhas coincidem</span>';
                confirm.classList.remove('is-invalid');
                confirm.classList.add('is-valid');
                return true;
            } else {
                status.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle"></i> Senhas não coincidem</span>';
                confirm.classList.remove('is-valid');
                confirm.classList.add('is-invalid');
                return false;
            }
        },

        // ========================================
        // PASSWORD GENERATOR
        // ========================================

        /**
         * Initialize password generator
         */
        initPasswordGenerator: function() {
            const generateBtn = document.getElementById('generatePasswordBtn');
            const copyBtn = document.getElementById('copyPasswordBtn');
            const useBtn = document.getElementById('usePasswordBtn');
            
            if (generateBtn) {
                generateBtn.addEventListener('click', () => this.generatePassword());
            }
            
            if (copyBtn) {
                copyBtn.addEventListener('click', () => this.copyGeneratedPassword());
            }
            
            if (useBtn) {
                useBtn.addEventListener('click', () => this.useGeneratedPassword());
            }
        },

        /**
         * Generate a strong password
         */
        generatePassword: function(length = 16) {
            const { lowercase, uppercase, numbers, special } = this.config.passwordChars;
            const allChars = lowercase + uppercase + numbers + special;
            
            let password = '';
            
            // Ensure at least one of each required type
            password += this.getRandomChar(lowercase);
            password += this.getRandomChar(uppercase);
            password += this.getRandomChar(numbers);
            password += this.getRandomChar(special);
            
            // Fill the rest randomly
            for (let i = password.length; i < length; i++) {
                password += this.getRandomChar(allChars);
            }
            
            // Shuffle the password
            password = this.shuffleString(password);
            
            // Display the generated password
            const container = document.getElementById('generatedPasswordContainer');
            const display = document.getElementById('generatedPassword');
            
            if (container && display) {
                display.textContent = password;
                container.classList.remove('d-none');
            }
            
            // Hide copy success message
            const copySuccess = document.getElementById('copySuccess');
            if (copySuccess) copySuccess.style.display = 'none';
            
            return password;
        },

        /**
         * Get random character from string
         */
        getRandomChar: function(str) {
            const array = new Uint32Array(1);
            window.crypto.getRandomValues(array);
            return str[array[0] % str.length];
        },

        /**
         * Shuffle string characters
         */
        shuffleString: function(str) {
            const arr = str.split('');
            for (let i = arr.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [arr[i], arr[j]] = [arr[j], arr[i]];
            }
            return arr.join('');
        },

        /**
         * Copy generated password to clipboard
         */
        copyGeneratedPassword: function() {
            const display = document.getElementById('generatedPassword');
            const copySuccess = document.getElementById('copySuccess');
            
            if (!display) return;
            
            const password = display.textContent;
            
            // Copy to clipboard
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(password).then(() => {
                    this.showCopySuccess(copySuccess);
                });
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = password;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                textArea.remove();
                this.showCopySuccess(copySuccess);
            }
        },

        /**
         * Show copy success message
         */
        showCopySuccess: function(element) {
            if (element) {
                element.style.display = 'inline';
                setTimeout(() => {
                    element.style.display = 'none';
                }, 2000);
            }
        },

        /**
         * Use generated password in form fields
         */
        useGeneratedPassword: function() {
            const display = document.getElementById('generatedPassword');
            const passwordField = document.getElementById('admin_password');
            const confirmField = document.getElementById('admin_password_confirm');
            
            if (!display || !passwordField) return;
            
            const password = display.textContent;
            
            // Set values
            passwordField.value = password;
            if (confirmField) confirmField.value = password;
            
            // Temporarily show password
            passwordField.type = 'text';
            if (confirmField) confirmField.type = 'text';
            
            // Update validation
            this.checkPasswordStrength(password);
            this.checkPasswordMatch();
            
            // Hide password after 3 seconds
            setTimeout(() => {
                passwordField.type = 'password';
                if (confirmField) confirmField.type = 'password';
            }, 3000);
            
            // Show success message
            this.showAlert('Senha aplicada com sucesso! A senha será ocultada em 3 segundos.', 'success');
        },

        // ========================================
        // PASSWORD TOGGLE
        // ========================================

        /**
         * Initialize password visibility toggle
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
                        } else {
                            input.type = 'password';
                            icon.classList.remove('fa-eye-slash');
                            icon.classList.add('fa-eye');
                        }
                    }
                });
            });
        },

        // ========================================
        // EMAIL VALIDATION
        // ========================================

        /**
         * Initialize email validation
         */
        initEmailValidation: function() {
            const emailField = document.getElementById('admin_email');
            if (!emailField) return;

            emailField.addEventListener('input', () => {
                this.debounce(() => this.validateEmail(emailField), 500)();
            });

            emailField.addEventListener('blur', () => {
                this.validateEmail(emailField);
            });
        },

        /**
         * Validate email format
         */
        validateEmail: function(field) {
            const email = field.value.trim();
            const status = document.getElementById('emailStatus');
            
            if (!email) {
                field.classList.remove('is-valid', 'is-invalid');
                if (status) status.innerHTML = '';
                return false;
            }

            // Email regex pattern
            const emailPattern = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
            
            // Additional validations
            const isValidFormat = emailPattern.test(email);
            const hasValidLength = email.length <= 254;
            const hasValidLocal = email.split('@')[0]?.length <= 64;
            const hasValidDomain = email.includes('.') && email.split('@')[1]?.includes('.');
            
            const isValid = isValidFormat && hasValidLength && hasValidLocal && hasValidDomain;

            if (isValid) {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
                if (status) {
                    status.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> Email válido</span>';
                }
            } else {
                field.classList.remove('is-valid');
                field.classList.add('is-invalid');
                if (status) {
                    let message = 'Email inválido';
                    if (!email.includes('@')) {
                        message = 'Email deve conter @';
                    } else if (!hasValidDomain) {
                        message = 'Domínio inválido';
                    } else if (!hasValidLength) {
                        message = 'Email muito longo';
                    }
                    status.innerHTML = `<span class="text-danger"><i class="fas fa-times-circle"></i> ${message}</span>`;
                }
            }

            return isValid;
        },

        // ========================================
        // FORM VALIDATION
        // ========================================

        /**
         * Initialize form validation
         */
        initFormValidation: function() {
            const form = document.getElementById('adminForm');
            if (!form) return;

            form.addEventListener('submit', (e) => {
                if (!this.validateForm()) {
                    e.preventDefault();
                }
            });

            // Real-time validation for required fields
            form.querySelectorAll('input[required]').forEach(input => {
                input.addEventListener('blur', () => this.validateField(input));
            });
        },

        /**
         * Validate entire form
         */
        validateForm: function() {
            const form = document.getElementById('adminForm');
            if (!form) return false;

            let isValid = true;
            const errors = [];

            // Check required fields
            form.querySelectorAll('input[required]').forEach(input => {
                if (!this.validateField(input)) {
                    isValid = false;
                    errors.push(this.getFieldLabel(input) + ' é obrigatório');
                }
            });

            // Check password strength
            const password = document.getElementById('admin_password');
            if (password && !this.checkPasswordStrength(password.value)) {
                isValid = false;
                errors.push('A senha não atende todos os requisitos');
            }

            // Check password match
            if (!this.checkPasswordMatch()) {
                isValid = false;
                errors.push('As senhas não coincidem');
            }

            // Check email
            const email = document.getElementById('admin_email');
            if (email && !this.validateEmail(email)) {
                isValid = false;
                errors.push('Email inválido');
            }

            if (!isValid) {
                this.showAlert('Por favor, corrija os seguintes erros:\n• ' + errors.join('\n• '), 'danger');
                
                // Focus first invalid field
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }

            return isValid;
        },

        /**
         * Validate single field
         */
        validateField: function(input) {
            const value = input.value.trim();
            
            if (input.hasAttribute('required') && !value) {
                input.classList.add('is-invalid');
                input.classList.remove('is-valid');
                return false;
            }

            if (value) {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
            }

            return true;
        },

        /**
         * Get field label text
         */
        getFieldLabel: function(input) {
            const label = document.querySelector(`label[for="${input.id}"]`);
            if (label) {
                return label.textContent.replace('*', '').trim();
            }
            return input.name;
        },

        // ========================================
        // EMAIL CONFIGURATION
        // ========================================

        /**
         * Initialize email config toggle
         */
        initEmailConfig: function() {
            const checkbox = document.getElementById('configure_email');
            const section = document.getElementById('emailConfigSection');
            const testBtn = document.getElementById('testEmailBtn');
            
            if (checkbox && section) {
                checkbox.addEventListener('change', function() {
                    section.style.display = this.checked ? 'block' : 'none';
                });
            }

            if (testBtn) {
                testBtn.addEventListener('click', () => this.sendTestEmail());
            }
        },

        /**
         * Send test email
         */
        sendTestEmail: function() {
            const resultDiv = document.getElementById('emailTestResult');
            const testBtn = document.getElementById('testEmailBtn');
            
            if (!resultDiv || !testBtn) return;

            // Get SMTP config
            const smtpConfig = {
                host: document.getElementById('smtp_host')?.value || '',
                port: document.getElementById('smtp_port')?.value || '587',
                security: document.getElementById('smtp_security')?.value || 'tls',
                user: document.getElementById('smtp_user')?.value || '',
                pass: document.getElementById('smtp_pass')?.value || '',
                from_email: document.getElementById('smtp_from_email')?.value || '',
                from_name: document.getElementById('smtp_from_name')?.value || '',
                to_email: document.getElementById('admin_email')?.value || ''
            };

            // Validate
            if (!smtpConfig.host || !smtpConfig.user || !smtpConfig.to_email) {
                resultDiv.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Preencha os campos obrigatórios do SMTP e o email do administrador.</div>';
                return;
            }

            // Show loading
            testBtn.disabled = true;
            testBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            resultDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Enviando email de teste...</div>';

            // Make request
            const formData = new FormData();
            formData.append('action', 'test_email');
            formData.append('csrf_token', document.querySelector('[name="csrf_token"]')?.value || '');
            
            for (const [key, value] of Object.entries(smtpConfig)) {
                formData.append(key, value);
            }

            fetch('ajax/test_email.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Email de teste enviado com sucesso! Verifique sua caixa de entrada.</div>';
                } else {
                    resultDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Falha ao enviar: ${data.message}</div>`;
                }
            })
            .catch(error => {
                resultDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Erro: ${error.message}</div>`;
            })
            .finally(() => {
                testBtn.disabled = false;
                testBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar Email de Teste';
            });
        },

        // ========================================
        // CHARACTER COUNT
        // ========================================

        /**
         * Initialize character counter
         */
        initCharacterCount: function() {
            const textarea = document.getElementById('site_description');
            const counter = document.getElementById('descriptionCount');
            
            if (textarea && counter) {
                textarea.addEventListener('input', function() {
                    counter.textContent = this.value.length;
                });
            }
        },

        // ========================================
        // UTILITIES
        // ========================================

        /**
         * Debounce function
         */
        debounce: function(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        },

        /**
         * Show alert message
         */
        showAlert: function(message, type = 'info') {
            const container = document.querySelector('.admin-setup');
            if (!container) return;

            // Remove existing alerts
            const existing = container.querySelector('.alert-custom');
            if (existing) existing.remove();

            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible alert-custom`;
            alertDiv.innerHTML = `
                <i class="fas fa-${this.getAlertIcon(type)}"></i>
                <div class="alert-content">${message.replace(/\n/g, '<br>')}</div>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            `;

            container.insertBefore(alertDiv, container.firstChild);

            // Bind close button
            alertDiv.querySelector('.close').addEventListener('click', () => {
                alertDiv.remove();
            });

            // Auto dismiss
            setTimeout(() => {
                if (alertDiv.parentNode) alertDiv.remove();
            }, 8000);

            // Scroll to alert
            alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        },

        /**
         * Get alert icon
         */
        getAlertIcon: function(type) {
            const icons = {
                success: 'check-circle',
                danger: 'exclamation-circle',
                warning: 'exclamation-triangle',
                info: 'info-circle'
            };
            return icons[type] || 'info-circle';
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            AdminHandler.init();
        });
    } else {
        AdminHandler.init();
    }

    // Expose to global scope
    window.AdminHandler = AdminHandler;

})();
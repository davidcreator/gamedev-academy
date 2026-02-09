/**
 * GameDev Academy - Installation Wizard JavaScript
 * Handles form validation, password strength, email validation, etc.
 */

(function() {
    'use strict';

    // ========================================
    // Configuration
    // ========================================
    const Config = {
        passwordMinLength: 8,
        passwordMaxLength: 128,
        usernameMinLength: 3,
        usernameMaxLength: 20,
        animationDuration: 300,
        debounceDelay: 300
    };

    // ========================================
    // Utility Functions
    // ========================================
    const Utils = {
        /**
         * Debounce function to limit execution rate
         */
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        /**
         * Show element with animation
         */
        show: function(element) {
            if (!element) return;
            element.style.display = 'block';
            element.classList.add('show');
        },

        /**
         * Hide element with animation
         */
        hide: function(element) {
            if (!element) return;
            element.classList.remove('show');
            setTimeout(() => {
                element.style.display = 'none';
            }, Config.animationDuration);
        },

        /**
         * Add class to element
         */
        addClass: function(element, className) {
            if (element) element.classList.add(className);
        },

        /**
         * Remove class from element
         */
        removeClass: function(element, className) {
            if (element) element.classList.remove(className);
        },

        /**
         * Toggle class on element
         */
        toggleClass: function(element, className, condition) {
            if (element) {
                if (condition) {
                    element.classList.add(className);
                } else {
                    element.classList.remove(className);
                }
            }
        },

        /**
         * Copy text to clipboard
         */
        copyToClipboard: function(text) {
            if (navigator.clipboard && window.isSecureContext) {
                return navigator.clipboard.writeText(text);
            } else {
                const textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                
                return new Promise((resolve, reject) => {
                    document.execCommand('copy') ? resolve() : reject();
                    textArea.remove();
                });
            }
        }
    };

    // ========================================
    // Form Validator Class
    // ========================================
    class FormValidator {
        constructor(form) {
            this.form = form;
            this.errors = [];
            this.init();
        }

        init() {
            if (!this.form) return;

            // Add novalidate to use custom validation
            this.form.setAttribute('novalidate', true);

            // Bind submit event
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));

            // Bind real-time validation
            const inputs = this.form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('blur', () => this.validateField(input));
                input.addEventListener('input', Utils.debounce(() => {
                    if (input.classList.contains('is-invalid')) {
                        this.validateField(input);
                    }
                }, Config.debounceDelay));
            });
        }

        handleSubmit(e) {
            this.errors = [];
            const inputs = this.form.querySelectorAll('input[required], select[required], textarea[required]');
            let isValid = true;

            inputs.forEach(input => {
                if (!this.validateField(input)) {
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
                this.showErrors();
                
                // Focus first invalid field
                const firstInvalid = this.form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        }

        validateField(input) {
            const value = input.value.trim();
            const type = input.type;
            const name = input.name;
            let isValid = true;
            let errorMessage = '';

            // Remove previous validation classes
            Utils.removeClass(input, 'is-valid');
            Utils.removeClass(input, 'is-invalid');

            // Check if required and empty
            if (input.hasAttribute('required') && !value) {
                isValid = false;
                errorMessage = this.getFieldLabel(input) + ' é obrigatório';
            }
            // Validate email
            else if (type === 'email' && value) {
                if (!EmailValidator.isValid(value)) {
                    isValid = false;
                    errorMessage = 'Por favor, insira um email válido';
                }
            }
            // Validate password
            else if (type === 'password' && value && name.includes('password') && !name.includes('confirm')) {
                if (value.length < Config.passwordMinLength) {
                    isValid = false;
                    errorMessage = `A senha deve ter no mínimo ${Config.passwordMinLength} caracteres`;
                }
            }
            // Validate password confirmation
            else if (name.includes('password_confirm') || name.includes('confirm_password')) {
                const passwordField = this.form.querySelector('input[name*="password"]:not([name*="confirm"])');
                if (passwordField && value !== passwordField.value) {
                    isValid = false;
                    errorMessage = 'As senhas não coincidem';
                }
            }
            // Validate username
            else if (name.includes('username') && value) {
                if (value.length < Config.usernameMinLength) {
                    isValid = false;
                    errorMessage = `O nome de usuário deve ter no mínimo ${Config.usernameMinLength} caracteres`;
                } else if (!/^[a-zA-Z0-9_]+$/.test(value)) {
                    isValid = false;
                    errorMessage = 'Use apenas letras, números e underscore';
                }
            }
            // Validate URL
            else if (type === 'url' && value) {
                if (!this.isValidURL(value)) {
                    isValid = false;
                    errorMessage = 'Por favor, insira uma URL válida';
                }
            }
            // Validate pattern
            else if (input.hasAttribute('pattern') && value) {
                const pattern = new RegExp(input.getAttribute('pattern'));
                if (!pattern.test(value)) {
                    isValid = false;
                    errorMessage = input.getAttribute('title') || 'Formato inválido';
                }
            }
            // Validate minlength
            else if (input.hasAttribute('minlength') && value) {
                const minLength = parseInt(input.getAttribute('minlength'));
                if (value.length < minLength) {
                    isValid = false;
                    errorMessage = `Mínimo de ${minLength} caracteres`;
                }
            }

            // Apply validation classes
            if (value) {
                Utils.addClass(input, isValid ? 'is-valid' : 'is-invalid');
            }

            // Show/hide error message
            this.updateFieldError(input, isValid ? '' : errorMessage);

            if (!isValid && errorMessage) {
                this.errors.push({ field: input, message: errorMessage });
            }

            return isValid;
        }

        getFieldLabel(input) {
            const label = this.form.querySelector(`label[for="${input.id}"]`);
            if (label) {
                return label.textContent.replace('*', '').trim();
            }
            return input.name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        }

        updateFieldError(input, message) {
            let feedback = input.parentElement.querySelector('.invalid-feedback');
            
            if (!feedback && message) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                
                // Insert after input or input-group
                const parent = input.closest('.input-group') || input;
                parent.parentNode.insertBefore(feedback, parent.nextSibling);
            }

            if (feedback) {
                feedback.textContent = message;
                feedback.style.display = message ? 'block' : 'none';
            }
        }

        isValidURL(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }

        showErrors() {
            if (this.errors.length > 0) {
                console.log('Validation errors:', this.errors);
            }
        }
    }

    // ========================================
    // Email Validator
    // ========================================
    const EmailValidator = {
        // RFC 5322 compliant email regex (simplified)
        pattern: /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/,

        /**
         * Validate email format
         */
        isValid: function(email) {
            if (!email || typeof email !== 'string') return false;
            
            email = email.trim().toLowerCase();
            
            // Check basic format
            if (!this.pattern.test(email)) return false;
            
            // Check length
            if (email.length > 254) return false;
            
            // Check parts
            const parts = email.split('@');
            if (parts[0].length > 64) return false;
            
            // Check domain parts
            const domainParts = parts[1].split('.');
            if (domainParts.some(part => part.length > 63)) return false;
            
            // Check for valid TLD (at least 2 characters)
            const tld = domainParts[domainParts.length - 1];
            if (tld.length < 2) return false;
            
            return true;
        },

        /**
         * Get validation message
         */
        getMessage: function(email) {
            if (!email) return 'Email é obrigatório';
            if (!email.includes('@')) return 'Email deve conter @';
            if (!this.isValid(email)) return 'Formato de email inválido';
            return '';
        },

        /**
         * Initialize email field validation
         */
        init: function(input) {
            if (!input) return;

            const validate = () => {
                const value = input.value.trim();
                const isValid = !value || this.isValid(value);
                
                Utils.removeClass(input, 'is-valid');
                Utils.removeClass(input, 'is-invalid');
                
                if (value) {
                    Utils.addClass(input, isValid ? 'is-valid' : 'is-invalid');
                }

                // Update feedback
                let feedback = input.parentElement.querySelector('.email-feedback');
                if (!feedback) {
                    feedback = document.createElement('div');
                    feedback.className = 'email-feedback invalid-feedback';
                    input.parentNode.insertBefore(feedback, input.nextSibling);
                }
                feedback.textContent = isValid ? '' : this.getMessage(value);
                feedback.style.display = isValid ? 'none' : 'block';
            };

            input.addEventListener('blur', validate);
            input.addEventListener('input', Utils.debounce(validate, Config.debounceDelay));
        }
    };

    // ========================================
    // Password Strength Checker
    // ========================================
    const PasswordStrength = {
        levels: {
            0: { label: 'Muito fraca', class: 'weak', color: '#ef4444' },
            1: { label: 'Fraca', class: 'weak', color: '#ef4444' },
            2: { label: 'Razoável', class: 'fair', color: '#f59e0b' },
            3: { label: 'Boa', class: 'good', color: '#0ea5e9' },
            4: { label: 'Forte', class: 'strong', color: '#10b981' },
            5: { label: 'Muito forte', class: 'strong', color: '#10b981' }
        },

        requirements: {
            minLength: { test: (p) => p.length >= 8, label: 'Mínimo 8 caracteres' },
            hasUppercase: { test: (p) => /[A-Z]/.test(p), label: 'Uma letra maiúscula' },
            hasLowercase: { test: (p) => /[a-z]/.test(p), label: 'Uma letra minúscula' },
            hasNumber: { test: (p) => /[0-9]/.test(p), label: 'Um número' },
            hasSpecial: { test: (p) => /[!@#$%^&*(),.?":{}|<>]/.test(p), label: 'Um caractere especial' },
            noSpaces: { test: (p) => !/\s/.test(p), label: 'Sem espaços' }
        },

        /**
         * Calculate password strength score (0-5)
         */
        getScore: function(password) {
            if (!password) return 0;
            
            let score = 0;
            
            // Length scoring
            if (password.length >= 8) score++;
            if (password.length >= 12) score++;
            if (password.length >= 16) score++;
            
            // Character variety
            if (/[a-z]/.test(password)) score += 0.5;
            if (/[A-Z]/.test(password)) score += 0.5;
            if (/[0-9]/.test(password)) score += 0.5;
            if (/[^a-zA-Z0-9]/.test(password)) score += 0.5;
            
            // Bonus for mixing
            const hasLower = /[a-z]/.test(password);
            const hasUpper = /[A-Z]/.test(password);
            const hasDigit = /[0-9]/.test(password);
            const hasSpecial = /[^a-zA-Z0-9]/.test(password);
            
            if (hasLower && hasUpper && hasDigit) score += 0.5;
            if (hasLower && hasUpper && hasDigit && hasSpecial) score += 0.5;
            
            // Penalty for common patterns
            if (/^[a-zA-Z]+$/.test(password)) score -= 0.5;
            if (/^[0-9]+$/.test(password)) score -= 1;
            if (/(.)\1{2,}/.test(password)) score -= 0.5; // Repeated chars
            if (/^(123|abc|qwerty|password|admin)/i.test(password)) score -= 1;
            
            return Math.max(0, Math.min(5, Math.round(score)));
        },

        /**
         * Get strength level info
         */
        getLevel: function(password) {
            const score = this.getScore(password);
            return this.levels[score];
        },

        /**
         * Check requirements
         */
        checkRequirements: function(password) {
            const results = {};
            for (const [key, req] of Object.entries(this.requirements)) {
                results[key] = {
                    passed: req.test(password || ''),
                    label: req.label
                };
            }
            return results;
        },

        /**
         * Initialize password strength UI
         */
        init: function(passwordInput, options = {}) {
            if (!passwordInput) return;

            const container = options.container || passwordInput.parentElement;
            
            // Create strength UI
            this.createStrengthUI(container, passwordInput);
            
            // Create requirements UI if enabled
            if (options.showRequirements !== false) {
                this.createRequirementsUI(container);
            }

            // Bind events
            passwordInput.addEventListener('input', () => {
                this.updateStrength(passwordInput.value);
                this.updateRequirements(passwordInput.value);
            });

            passwordInput.addEventListener('focus', () => {
                const reqContainer = container.querySelector('.password-requirements');
                if (reqContainer) Utils.show(reqContainer);
            });
        },

        createStrengthUI: function(container, input) {
            // Check if already exists
            if (container.querySelector('.password-strength-container')) return;

            const html = `
                <div class="password-strength-container">
                    <div class="password-strength-bar">
                        <div class="password-strength-fill"></div>
                    </div>
                    <div class="password-strength-text">
                        <i class="fas fa-shield-alt"></i>
                        <span class="strength-label">Digite uma senha</span>
                    </div>
                </div>
            `;
            
            // Insert after input or input-group
            const insertAfter = input.closest('.input-group') || input;
            insertAfter.insertAdjacentHTML('afterend', html);
        },

        createRequirementsUI: function(container) {
            // Check if already exists
            if (container.querySelector('.password-requirements')) return;

            let html = `
                <div class="password-requirements" style="display: none;">
                    <div class="password-requirements-title">A senha deve conter:</div>
                    <ul>
            `;
            
            for (const [key, req] of Object.entries(this.requirements)) {
                html += `
                    <li data-requirement="${key}" class="invalid">
                        <i class="fas fa-times"></i>
                        <span>${req.label}</span>
                    </li>
                `;
            }
            
            html += `
                    </ul>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', html);
        },

        updateStrength: function(password) {
            const container = document.querySelector('.password-strength-container');
            if (!container) return;

            const fill = container.querySelector('.password-strength-fill');
            const label = container.querySelector('.strength-label');
            const textContainer = container.querySelector('.password-strength-text');
            
            if (!password) {
                fill.className = 'password-strength-fill';
                fill.style.width = '0%';
                label.textContent = 'Digite uma senha';
                textContainer.className = 'password-strength-text';
                return;
            }

            const level = this.getLevel(password);
            const score = this.getScore(password);
            const width = Math.max(5, (score / 5) * 100);

            fill.className = `password-strength-fill ${level.class}`;
            fill.style.width = `${width}%`;
            label.textContent = level.label;
            textContainer.className = `password-strength-text ${level.class}`;
        },

        updateRequirements: function(password) {
            const results = this.checkRequirements(password);
            
            for (const [key, result] of Object.entries(results)) {
                const li = document.querySelector(`[data-requirement="${key}"]`);
                if (li) {
                    const icon = li.querySelector('i');
                    
                    if (result.passed) {
                        li.classList.remove('invalid');
                        li.classList.add('valid');
                        icon.className = 'fas fa-check';
                    } else {
                        li.classList.remove('valid');
                        li.classList.add('invalid');
                        icon.className = 'fas fa-times';
                    }
                }
            }
        }
    };

    // ========================================
    // Password Generator
    // ========================================
    const PasswordGenerator = {
        charsets: {
            lowercase: 'abcdefghijklmnopqrstuvwxyz',
            uppercase: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            numbers: '0123456789',
            symbols: '!@#$%^&*()_+-=[]{}|;:,.<>?'
        },

        /**
         * Generate a strong password
         */
        generate: function(length = 16, options = {}) {
            const opts = {
                lowercase: true,
                uppercase: true,
                numbers: true,
                symbols: true,
                ...options
            };

            let charset = '';
            let password = '';
            const required = [];

            // Build charset and required characters
            if (opts.lowercase) {
                charset += this.charsets.lowercase;
                required.push(this.getRandomChar(this.charsets.lowercase));
            }
            if (opts.uppercase) {
                charset += this.charsets.uppercase;
                required.push(this.getRandomChar(this.charsets.uppercase));
            }
            if (opts.numbers) {
                charset += this.charsets.numbers;
                required.push(this.getRandomChar(this.charsets.numbers));
            }
            if (opts.symbols) {
                charset += this.charsets.symbols;
                required.push(this.getRandomChar(this.charsets.symbols));
            }

            if (!charset) {
                charset = this.charsets.lowercase + this.charsets.numbers;
            }

            // Generate password
            const remainingLength = length - required.length;
            for (let i = 0; i < remainingLength; i++) {
                password += this.getRandomChar(charset);
            }

            // Add required characters
            password += required.join('');

            // Shuffle password
            return this.shuffle(password);
        },

        /**
         * Get cryptographically secure random character
         */
        getRandomChar: function(charset) {
            const array = new Uint32Array(1);
            window.crypto.getRandomValues(array);
            return charset[array[0] % charset.length];
        },

        /**
         * Shuffle string
         */
        shuffle: function(str) {
            const array = str.split('');
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
            return array.join('');
        },

        /**
         * Create generator button
         */
        createButton: function(passwordInput, confirmInput = null) {
            const container = passwordInput.closest('.form-group');
            if (!container || container.querySelector('.password-generator')) return;

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn-generate-password';
            button.innerHTML = '<i class="fas fa-key"></i> Gerar senha forte';
            
            const wrapper = document.createElement('div');
            wrapper.className = 'password-generator';
            wrapper.appendChild(button);

            container.appendChild(wrapper);

            button.addEventListener('click', () => {
                const newPassword = this.generate(16);
                passwordInput.value = newPassword;
                passwordInput.type = 'text';
                
                // Update confirm field if exists
                if (confirmInput) {
                    confirmInput.value = newPassword;
                }

                // Trigger input event to update strength meter
                passwordInput.dispatchEvent(new Event('input'));

                // Show copy notification
                this.showCopyNotification(container, newPassword);

                // Revert to password type after 3 seconds
                setTimeout(() => {
                    passwordInput.type = 'password';
                }, 3000);
            });
        },

        showCopyNotification: function(container, password) {
            // Remove existing notification
            const existing = container.querySelector('.password-notification');
            if (existing) existing.remove();

            const notification = document.createElement('div');
            notification.className = 'password-notification';
            notification.innerHTML = `
                <span>Senha gerada! </span>
                <button type="button" class="btn-copy-password">
                    <i class="fas fa-copy"></i> Copiar
                </button>
            `;
            notification.style.cssText = `
                margin-top: 0.5rem;
                padding: 0.5rem 0.75rem;
                background: #ecfdf5;
                border: 1px solid #10b981;
                border-radius: 0.375rem;
                font-size: 0.875rem;
                color: #065f46;
                display: flex;
                align-items: center;
                justify-content: space-between;
                animation: fadeIn 0.3s ease;
            `;

            const copyBtn = notification.querySelector('.btn-copy-password');
            copyBtn.style.cssText = `
                background: #10b981;
                color: white;
                border: none;
                padding: 0.25rem 0.5rem;
                border-radius: 0.25rem;
                cursor: pointer;
                font-size: 0.75rem;
            `;

            copyBtn.addEventListener('click', async () => {
                try {
                    await Utils.copyToClipboard(password);
                    copyBtn.innerHTML = '<i class="fas fa-check"></i> Copiado!';
                    setTimeout(() => {
                        notification.remove();
                    }, 2000);
                } catch (err) {
                    copyBtn.innerHTML = '<i class="fas fa-times"></i> Erro';
                }
            });

            container.appendChild(notification);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }
    };

    // ========================================
    // Toggle Password Visibility
    // ========================================
    const PasswordToggle = {
        init: function() {
            document.querySelectorAll('.btn-toggle-password').forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    const icon = this.querySelector('i');
                    
                    if (input) {
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
        }
    };

    // ========================================
    // Alert Handler
    // ========================================
    const AlertHandler = {
        init: function() {
            // Auto dismiss alerts after 5 seconds
            document.querySelectorAll('.alert:not(.alert-permanent)').forEach(alert => {
                setTimeout(() => {
                    this.dismiss(alert);
                }, 5000);
            });

            // Dismiss button handler
            document.querySelectorAll('.alert .close').forEach(button => {
                button.addEventListener('click', function() {
                    const alert = this.closest('.alert');
                    AlertHandler.dismiss(alert);
                });
            });
        },

        dismiss: function(alert) {
            if (!alert) return;
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                alert.remove();
            }, 300);
        },

        show: function(message, type = 'info', container = null) {
            const alertHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="fas fa-${this.getIcon(type)}"></i>
                    <div class="alert-content">${message}</div>
                    <button type="button" class="close" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;

            const targetContainer = container || document.querySelector('.card-body');
            if (targetContainer) {
                targetContainer.insertAdjacentHTML('afterbegin', alertHTML);
                const newAlert = targetContainer.querySelector('.alert');
                
                // Bind close button
                newAlert.querySelector('.close').addEventListener('click', () => {
                    this.dismiss(newAlert);
                });

                // Auto dismiss
                setTimeout(() => {
                    this.dismiss(newAlert);
                }, 5000);
            }
        },

        getIcon: function(type) {
            const icons = {
                success: 'check-circle',
                danger: 'exclamation-circle',
                warning: 'exclamation-triangle',
                info: 'info-circle'
            };
            return icons[type] || 'info-circle';
        }
    };

    // ========================================
    // Database Connection Tester
    // ========================================
    const DatabaseTester = {
        init: function() {
            const testBtn = document.getElementById('testConnectionBtn');
            if (!testBtn) return;

            testBtn.addEventListener('click', () => this.test());
        },

        test: function() {
            const form = document.getElementById('databaseForm');
            if (!form) return;

            const resultsDiv = document.getElementById('testResults');
            const testBtn = document.getElementById('testConnectionBtn');
            
            // Show loading state
            testBtn.disabled = true;
            testBtn.innerHTML = '<i class="fas fa-spinner spinner"></i> Testando...';
            
            Utils.show(resultsDiv);
            resultsDiv.className = 'test-results';
            resultsDiv.innerHTML = '<i class="fas fa-spinner spinner"></i> Testando conexão com o banco de dados...';

            // Collect form data
            const formData = new FormData(form);
            formData.append('action', 'test_connection');

            // Make AJAX request
            fetch('ajax/test_connection.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultsDiv.className = 'test-results success';
                    resultsDiv.innerHTML = `
                        <div class="result-item">
                            <i class="fas fa-check-circle"></i>
                            <strong>Conexão estabelecida com sucesso!</strong>
                        </div>
                        ${data.server_info ? `
                        <div class="result-item">
                            <i class="fas fa-server"></i>
                            <span>Servidor: ${data.server_info}</span>
                        </div>
                        ` : ''}
                        ${data.database ? `
                        <div class="result-item">
                            <i class="fas fa-database"></i>
                            <span>Banco de dados: ${data.database}</span>
                        </div>
                        ` : ''}
                    `;
                    
                    document.getElementById('submitBtn').disabled = false;
                } else {
                    resultsDiv.className = 'test-results error';
                    resultsDiv.innerHTML = `
                        <div class="result-item">
                            <i class="fas fa-times-circle"></i>
                            <strong>Falha na conexão</strong>
                        </div>
                        <div class="result-item">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>${data.message || 'Erro desconhecido'}</span>
                        </div>
                    `;
                }
            })
            .catch(error => {
                resultsDiv.className = 'test-results error';
                resultsDiv.innerHTML = `
                    <div class="result-item">
                        <i class="fas fa-times-circle"></i>
                        <strong>Erro ao testar conexão</strong>
                    </div>
                    <div class="result-item">
                        <span>${error.message}</span>
                    </div>
                `;
            })
            .finally(() => {
                testBtn.disabled = false;
                testBtn.innerHTML = '<i class="fas fa-plug"></i> Testar Conexão';
            });
        }
    };

    // ========================================
    // Tables Installation
    // ========================================
    const TablesInstaller = {
        tables: [],
        currentIndex: 0,
        isRunning: false,

        init: function() {
            const startBtn = document.getElementById('startInstallBtn');
            if (!startBtn) return;

            const tablesData = document.getElementById('tablesData');
            if (tablesData) {
                try {
                    this.tables = JSON.parse(tablesData.value);
                } catch (e) {
                    console.error('Error parsing tables data:', e);
                }
            }

            startBtn.addEventListener('click', () => this.start());
        },

        start: function() {
            if (this.isRunning) return;
            this.isRunning = true;
            this.currentIndex = 0;

            // Show progress, hide list
            document.getElementById('tablesList').style.display = 'none';
            document.getElementById('installProgress').style.display = 'block';
            
            // Clear log
            document.getElementById('installLog').innerHTML = '';
            
            this.log('Iniciando instalação do banco de dados...', 'info');
            this.log('─'.repeat(50), 'info');
            
            this.processNext();
        },

        processNext: function() {
            if (this.currentIndex >= this.tables.length) {
                this.complete();
                return;
            }

            const table = this.tables[this.currentIndex];
            const progress = Math.round((this.currentIndex / this.tables.length) * 100);
            
            this.updateProgress(progress);
            this.updateTableStatus(table, 'processing');
            this.log(`Criando tabela: ${table}...`, 'info');

            // Make AJAX request to create table
            const formData = new FormData();
            formData.append('action', 'create_table');
            formData.append('table', table);
            formData.append('csrf_token', document.querySelector('[name="csrf_token"]').value);

            fetch('ajax/create_table.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.updateTableStatus(table, 'success');
                    this.log(`✓ Tabela ${table} criada com sucesso`, 'success');
                } else {
                    this.updateTableStatus(table, 'error');
                    this.log(`✗ Erro ao criar tabela ${table}: ${data.message}`, 'error');
                }
            })
            .catch(error => {
                this.updateTableStatus(table, 'error');
                this.log(`✗ Erro ao criar tabela ${table}: ${error.message}`, 'error');
            })
            .finally(() => {
                this.currentIndex++;
                setTimeout(() => this.processNext(), 300);
            });
        },

        updateProgress: function(percent) {
            const bar = document.getElementById('progressBar');
            if (bar) {
                bar.style.width = `${percent}%`;
                bar.setAttribute('aria-valuenow', percent);
                bar.textContent = `${percent}%`;
            }
        },

        updateTableStatus: function(table, status) {
            const item = document.querySelector(`[data-table="${table}"]`);
            if (item) {
                item.className = `table-item ${status}`;
                const icon = item.querySelector('.status-icon i');
                if (icon) {
                    icon.className = status === 'processing' ? 'fas fa-spinner spinner' :
                                    status === 'success' ? 'fas fa-check-circle text-success' :
                                    'fas fa-times-circle text-danger';
                }
            }
        },

        log: function(message, type = 'info') {
            const logContainer = document.getElementById('installLog');
            if (!logContainer) return;

            const entry = document.createElement('div');
            entry.className = `log-entry log-${type}`;
            
            const timestamp = new Date().toLocaleTimeString();
            entry.textContent = `[${timestamp}] ${message}`;
            
            logContainer.appendChild(entry);
            logContainer.scrollTop = logContainer.scrollHeight;
        },

        complete: function() {
            this.updateProgress(100);
            this.log('─'.repeat(50), 'info');
            this.log('Instalação concluída com sucesso!', 'success');

            const resultDiv = document.getElementById('installResult');
            if (resultDiv) {
                resultDiv.className = 'install-result success';
                resultDiv.innerHTML = `
                    <h5><i class="fas fa-check-circle"></i> Instalação Concluída!</h5>
                    <p>Todas as tabelas foram criadas com sucesso.</p>
                    <ul>
                        <li>${this.tables.length} tabelas criadas</li>
                        <li>Estrutura do banco de dados pronta</li>
                    </ul>
                `;
                resultDiv.style.display = 'block';
            }

            // Show continue button
            const continueBtn = document.getElementById('continueBtn');
            const startBtn = document.getElementById('startInstallBtn');
            if (continueBtn) continueBtn.style.display = 'inline-flex';
            if (startBtn) startBtn.style.display = 'none';

            this.isRunning = false;
        }
    };

    // ========================================
    // Initialize Everything
    // ========================================
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize form validation on all forms
        document.querySelectorAll('form').forEach(form => {
            new FormValidator(form);
        });

        // Initialize email validation
        document.querySelectorAll('input[type="email"]').forEach(input => {
            EmailValidator.init(input);
        });

        // Initialize password strength
        const passwordField = document.getElementById('admin_password');
        const confirmField = document.getElementById('admin_password_confirm');
        
        if (passwordField) {
            const container = passwordField.closest('.form-group');
            PasswordStrength.init(passwordField, {
                container: container,
                showRequirements: true
            });
            
            // Add password generator
            PasswordGenerator.createButton(passwordField, confirmField);
        }

        // Initialize password toggle
        PasswordToggle.init();

        // Initialize alerts
        AlertHandler.init();

        // Initialize database tester
        DatabaseTester.init();

        // Initialize tables installer
        TablesInstaller.init();

        // Prevent form resubmission
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        // Warn before leaving during installation
        window.addEventListener('beforeunload', function(e) {
            if (TablesInstaller.isRunning) {
                e.preventDefault();
                e.returnValue = 'A instalação está em progresso. Tem certeza que deseja sair?';
            }
        });

        console.log('🎮 GameDev Academy Installer initialized');
    });

    // ========================================
    // Expose to global scope if needed
    // ========================================
    window.GDAInstaller = {
        Utils,
        FormValidator,
        EmailValidator,
        PasswordStrength,
        PasswordGenerator,
        AlertHandler,
        DatabaseTester,
        TablesInstaller
    };

})();
/**
 * GameDev Academy - Authentication Pages JavaScript
 */

(function() {
    'use strict';

    const AuthHandler = {
        
        init: function() {
            this.initPasswordValidation();
            this.initPasswordToggle();
            this.initFormValidation();
        },

        initPasswordValidation: function() {
            const passwordField = document.getElementById('password');
            const confirmField = document.getElementById('password_confirm');
            
            if (!passwordField) return;

            passwordField.addEventListener('input', () => {
                this.checkPasswordStrength(passwordField.value);
                if (confirmField && confirmField.value) {
                    this.checkPasswordMatch();
                }
            });

            if (confirmField) {
                confirmField.addEventListener('input', () => {
                    this.checkPasswordMatch();
                });
            }
        },

        checkPasswordStrength: function(password) {
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(password)
            };

            // Update indicators
            this.updateRequirement('req-length', requirements.length);
            this.updateRequirement('req-uppercase', requirements.uppercase);
            this.updateRequirement('req-lowercase', requirements.lowercase);
            this.updateRequirement('req-number', requirements.number);
            this.updateRequirement('req-special', requirements.special);

            // Calculate strength
            const passedCount = Object.values(requirements).filter(v => v).length;
            const strength = this.getStrength(password, passedCount);
            this.updateStrengthBar(strength);

            return passedCount === 5;
        },

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

        getStrength: function(password, passedCount) {
            if (!password) return { level: 'none', text: 'Digite uma senha', class: '' };
            if (passedCount <= 2) return { level: 'weak', text: 'Fraca', class: 'weak' };
            if (passedCount <= 3) return { level: 'fair', text: 'Média', class: 'fair' };
            if (passedCount <= 4) return { level: 'good', text: 'Boa', class: 'good' };
            return { level: 'strong', text: 'Forte', class: 'strong' };
        },

        updateStrengthBar: function(strength) {
            const fill = document.getElementById('passwordStrengthFill');
            const text = document.getElementById('passwordStrengthText');
            
            if (!fill || !text) return;

            fill.className = 'password-strength-fill';
            text.className = 'password-strength-text';

            if (strength.class) {
                fill.classList.add(strength.class);
                text.classList.add(strength.class);
            }

            text.innerHTML = `<i class="fas fa-shield-alt"></i> <span>${strength.text}</span>`;
        },

        checkPasswordMatch: function() {
            const password = document.getElementById('password');
            const confirm = document.getElementById('password_confirm');
            const status = document.getElementById('passwordMatchStatus');
            
            if (!password || !confirm) return false;

            if (!confirm.value) {
                if (status) status.innerHTML = '';
                confirm.classList.remove('is-valid', 'is-invalid');
                return false;
            }

            if (password.value === confirm.value) {
                if (status) status.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> Senhas coincidem</span>';
                confirm.classList.remove('is-invalid');
                confirm.classList.add('is-valid');
                return true;
            } else {
                if (status) status.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle"></i> Senhas não coincidem</span>';
                confirm.classList.remove('is-valid');
                confirm.classList.add('is-invalid');
                return false;
            }
        },

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

        initFormValidation: function() {
            const form = document.querySelector('.auth-form');
            if (!form) return;

            form.addEventListener('submit', (e) => {
                const password = document.getElementById('password');
                const confirm = document.getElementById('password_confirm');
                
                if (password && confirm) {
                    if (!this.checkPasswordStrength(password.value)) {
                        e.preventDefault();
                        alert('A senha não atende todos os requisitos.');
                        password.focus();
                        return;
                    }
                    
                    if (!this.checkPasswordMatch()) {
                        e.preventDefault();
                        alert('As senhas não coincidem.');
                        confirm.focus();
                        return;
                    }
                }
            });
        }
    };

    // Initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => AuthHandler.init());
    } else {
        AuthHandler.init();
    }

    window.AuthHandler = AuthHandler;
})();
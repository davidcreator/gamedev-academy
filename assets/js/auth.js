/**
 * GameDev Academy - Authentication JavaScript
 * Handles all authentication related functionality
 */

(function() {
    'use strict';

    // Configurações
    const CONFIG = {
        passwordMinLength: 8,
        tokenExpiryTime: 3600000, // 1 hora em ms
        messages: {
            passwordMismatch: 'As senhas não coincidem',
            passwordTooShort: 'A senha deve ter no mínimo 8 caracteres',
            invalidEmail: 'Por favor, insira um email válido',
            requestSuccess: 'Se o email existir, você receberá um link de recuperação',
            requestError: 'Erro ao processar solicitação. Tente novamente.'
        }
    };

    /**
     * Validação de Email
     */
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    /**
     * Validação de Força da Senha
     */
    function checkPasswordStrength(password) {
        let strength = 0;
        
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;
        
        return {
            score: strength,
            level: strength <= 2 ? 'weak' : strength <= 4 ? 'medium' : 'strong'
        };
    }

    /**
     * Atualizar Indicador de Força da Senha
     */
    function updatePasswordStrength(password) {
        const strengthBar = document.querySelector('.password-strength-fill');
        const requirements = document.querySelectorAll('.password-requirements li');
        
        if (!strengthBar) return;
        
        const strength = checkPasswordStrength(password);
        
        // Atualizar barra
        strengthBar.className = 'password-strength-fill ' + strength.level;
        
        // Atualizar requisitos
        if (requirements.length > 0) {
            requirements[0].className = password.length >= 8 ? 'valid' : 'invalid';
            requirements[1].className = /[a-z]/.test(password) ? 'valid' : 'invalid';
            requirements[2].className = /[A-Z]/.test(password) ? 'valid' : 'invalid';
            requirements[3].className = /[0-9]/.test(password) ? 'valid' : 'invalid';
            requirements[4].className = /[^a-zA-Z0-9]/.test(password) ? 'valid' : 'invalid';
        }
    }

    /**
     * Validar Confirmação de Senha
     */
    function validatePasswordMatch() {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        if (!password || !confirmPassword) return true;
        
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity(CONFIG.messages.passwordMismatch);
            confirmPassword.classList.add('is-invalid');
            return false;
        } else {
            confirmPassword.setCustomValidity('');
            confirmPassword.classList.remove('is-invalid');
            confirmPassword.classList.add('is-valid');
            return true;
        }
    }

    /**
     * Manipulador do Formulário de Recuperação de Senha
     */
    function handleForgotPasswordForm(form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = form.querySelector('#email').value;
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            // Validar email
            if (!validateEmail(email)) {
                showAlert('error', CONFIG.messages.invalidEmail);
                return;
            }
            
            // Desabilitar botão e mostrar loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Enviando...';
            
            try {
                const formData = new FormData(form);
                const response = await fetch(form.action || '', {
                    method: 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    showAlert('success', CONFIG.messages.requestSuccess);
                    form.reset();
                } else {
                    showAlert('error', CONFIG.messages.requestError);
                }
            } catch (error) {
                console.error('Erro:', error);
                showAlert('error', CONFIG.messages.requestError);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }

    /**
     * Manipulador do Formulário de Reset de Senha
     */
    function handleResetPasswordForm(form) {
        const passwordInput = form.querySelector('#password');
        const confirmPasswordInput = form.querySelector('#confirm_password');
        
        // Monitorar mudanças na senha
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                updatePasswordStrength(this.value);
                if (confirmPasswordInput.value) {
                    validatePasswordMatch();
                }
            });
        }
        
        // Monitorar confirmação de senha
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', validatePasswordMatch);
        }
        
        // Submit do formulário
        form.addEventListener('submit', function(e) {
            if (!validatePasswordMatch()) {
                e.preventDefault();
                showAlert('error', CONFIG.messages.passwordMismatch);
                return false;
            }
            
            if (passwordInput.value.length < CONFIG.passwordMinLength) {
                e.preventDefault();
                showAlert('error', CONFIG.messages.passwordTooShort);
                return false;
            }
        });
    }

    /**
     * Mostrar Alerta
     */
    function showAlert(type, message) {
        // Remover alertas existentes
        const existingAlerts = document.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());
        
        // Criar novo alerta
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type}`;
        alertDiv.textContent = message;
        alertDiv.style.animation = 'fadeIn 0.3s ease';
        
        // Inserir alerta
        const form = document.querySelector('form');
        form.parentNode.insertBefore(alertDiv, form);
        
        // Auto remover após 5 segundos
        setTimeout(() => {
            alertDiv.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => alertDiv.remove(), 300);
        }, 5000);
    }

    /**
     * Verificar Expiração do Token
     */
    function checkTokenExpiry() {
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');
        
        if (token) {
            // Adicionar timer visual se necessário
            const timerElement = document.getElementById('token-timer');
            if (timerElement) {
                let timeLeft = 3600; // 1 hora em segundos
                
                const interval = setInterval(() => {
                    timeLeft--;
                    const minutes = Math.floor(timeLeft / 60);
                    const seconds = timeLeft % 60;
                    
                    timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                    
                    if (timeLeft <= 0) {
                        clearInterval(interval);
                        showAlert('error', 'Token expirado. Solicite um novo link.');
                        setTimeout(() => {
                            window.location.href = 'forgot-password.php';
                        }, 3000);
                    }
                }, 1000);
            }
        }
    }

    /**
     * Inicialização
     */
    document.addEventListener('DOMContentLoaded', function() {
        // Formulário de Esqueceu a Senha
        const forgotForm = document.querySelector('#forgot-password-form');
        if (forgotForm) {
            handleForgotPasswordForm(forgotForm);
        }
        
        // Formulário de Reset de Senha
        const resetForm = document.querySelector('#reset-password-form');
        if (resetForm) {
            handleResetPasswordForm(resetForm);
            checkTokenExpiry();
        }
        
        // Adicionar animações aos inputs
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.classList.remove('focused');
                }
            });
        });
    });

})();
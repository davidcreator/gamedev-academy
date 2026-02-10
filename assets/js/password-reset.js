/**
 * GameDev Academy - Password Reset JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Password visibility toggle
    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    }
    
    if (toggleConfirmPassword) {
        toggleConfirmPassword.addEventListener('click', function() {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    }
    
    // Password strength checker
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
            if (confirmPasswordInput.value) {
                checkPasswordMatch();
            }
        });
    }
    
    // Confirm password validation
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    }
    
    // Check password strength
    function checkPasswordStrength(password) {
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        const requirements = {
            length: document.getElementById('length'),
            uppercase: document.getElementById('uppercase'),
            lowercase: document.getElementById('lowercase'),
            number: document.getElementById('number')
        };
        
        let strength = 0;
        
        // Check length
        if (password.length >= 8) {
            strength++;
            requirements.length.classList.add('valid');
        } else {
            requirements.length.classList.remove('valid');
        }
        
        // Check uppercase
        if (/[A-Z]/.test(password)) {
            strength++;
            requirements.uppercase.classList.add('valid');
        } else {
            requirements.uppercase.classList.remove('valid');
        }
        
        // Check lowercase
        if (/[a-z]/.test(password)) {
            strength++;
            requirements.lowercase.classList.add('valid');
        } else {
            requirements.lowercase.classList.remove('valid');
        }
        
        // Check number
        if (/[0-9]/.test(password)) {
            strength++;
            requirements.number.classList.add('valid');
        } else {
            requirements.number.classList.remove('valid');
        }
        
        // Update strength bar
        if (strengthBar) {
            strengthBar.className = 'strength-bar-fill';
            if (password.length === 0) {
                strengthBar.style.width = '0';
                strengthText.textContent = 'Digite uma senha';
                strengthText.style.color = '#6c757d';
            } else if (strength <= 2) {
                strengthBar.classList.add('weak');
                strengthText.textContent = 'Senha fraca';
                strengthText.style.color = '#dc3545';
            } else if (strength === 3) {
                strengthBar.classList.add('medium');
                strengthText.textContent = 'Senha média';
                strengthText.style.color = '#ffc107';
            } else {
                strengthBar.classList.add('strong');
                strengthText.textContent = 'Senha forte';
                strengthText.style.color = '#28a745';
            }
        }
        
        return strength;
    }
    
    // Check password match
    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        const confirmError = document.getElementById('confirmError');
        
        if (confirmPassword.length > 0) {
            if (password === confirmPassword) {
                confirmPasswordInput.classList.remove('is-invalid');
                confirmPasswordInput.classList.add('is-valid');
                if (confirmError) {
                    confirmError.style.display = 'none';
                }
                return true;
            } else {
                confirmPasswordInput.classList.remove('is-valid');
                confirmPasswordInput.classList.add('is-invalid');
                if (confirmError) {
                    confirmError.style.display = 'block';
                }
                return false;
            }
        }
        return true;
    }
    
    // Form submission
    const resetForm = document.getElementById('resetPasswordForm');
    if (resetForm) {
        resetForm.addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const strength = checkPasswordStrength(password);
            
            if (strength < 4) {
                e.preventDefault();
                alert('Por favor, crie uma senha mais forte seguindo os requisitos.');
                return false;
            }
            
            if (!checkPasswordMatch()) {
                e.preventDefault();
                alert('As senhas não coincidem!');
                return false;
            }
            
            // Add loading state to button
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.classList.add('btn-loading');
            submitBtn.disabled = true;
        });
    }
    
    // Countdown timer
    const countdownElement = document.getElementById('countdown');
    if (countdownElement) {
        const timeString = countdownElement.textContent;
        const timeParts = timeString.split(':');
        let totalSeconds = parseInt(timeParts[0]) * 60 + parseInt(timeParts[1]);
        
        const timer = setInterval(function() {
            totalSeconds--;
            
            if (totalSeconds <= 0) {
                clearInterval(timer);
                countdownElement.textContent = '00:00';
                countdownElement.style.color = '#dc3545';
                
                // Disable form
                if (resetForm) {
                    const inputs = resetForm.querySelectorAll('input, button');
                    inputs.forEach(input => input.disabled = true);
                }
                
                // Show expired message
                setTimeout(function() {
                    alert('O tempo expirou! Solicite um novo link de recuperação.');
                    window.location.href = 'forgot-password.php';
                }, 1000);
            } else {
                const minutes = Math.floor(totalSeconds / 60);
                const seconds = totalSeconds % 60;
                countdownElement.textContent = 
                    (minutes < 10 ? '0' : '') + minutes + ':' + 
                    (seconds < 10 ? '0' : '') + seconds;
                
                // Change color when time is running out
                if (totalSeconds <= 60) {
                    countdownElement.style.color = '#dc3545';
                } else if (totalSeconds <= 300) {
                    countdownElement.style.color = '#ffc107';
                }
            }
        }, 1000);
    }
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
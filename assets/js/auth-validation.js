/**
 * GameDev Academy - Auth Validation
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Forgot Password Form
    const forgotForm = document.getElementById('forgotPasswordForm');
    if (forgotForm) {
        forgotForm.addEventListener('submit', function(e) {
            const emailInput = document.getElementById('email');
            const submitBtn = this.querySelector('button[type="submit"]');
            
            // Validate email
            if (!validateEmail(emailInput.value)) {
                e.preventDefault();
                emailInput.classList.add('is-invalid');
                return false;
            }
            
            // Add loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            submitBtn.disabled = true;
        });
        
        // Remove invalid class on input
        const emailInput = document.getElementById('email');
        if (emailInput) {
            emailInput.addEventListener('input', function() {
                if (validateEmail(this.value)) {
                    this.classList.remove('is-invalid');
                }
            });
        }
    }
    
    // Email validation function
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    // Auto-hide alerts
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            if (!alert.classList.contains('alert-permanent')) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        });
    }, 5000);
});
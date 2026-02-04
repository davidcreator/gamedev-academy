// assets/js/main.js

// Utilitários gerais
const GameDevAcademy = {
    // Inicialização
    init() {
        this.initMobileMenu();
        this.initTooltips();
        this.initProgressBars();
        this.initNotifications();
        this.initForms();
    },

    // Menu Mobile
    initMobileMenu() {
        const toggle = document.querySelector('.navbar-toggle');
        const menu = document.querySelector('.navbar-nav');
        
        if (toggle && menu) {
            toggle.addEventListener('click', () => {
                menu.classList.toggle('active');
            });
        }
    },

    // Tooltips
    initTooltips() {
        const tooltips = document.querySelectorAll('[title]');
        tooltips.forEach(element => {
            element.addEventListener('mouseenter', function() {
                // Implementar tooltip customizado se necessário
            });
        });
    },

    // Barras de Progresso Animadas
    initProgressBars() {
        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const progressBar = entry.target.querySelector('.progress-bar');
                    if (progressBar) {
                        const targetWidth = progressBar.getAttribute('data-width') || 
                                          progressBar.style.width;
                        progressBar.style.width = targetWidth;
                    }
                }
            });
        }, observerOptions);

        document.querySelectorAll('.progress').forEach(progress => {
            observer.observe(progress);
        });
    },

    // Sistema de Notificações
    initNotifications() {
        this.checkForLevelUp();
        this.checkForAchievements();
    },

    checkForLevelUp() {
        // Verificar se houve level up (implementar com AJAX)
        const levelUpData = localStorage.getItem('levelUp');
        if (levelUpData) {
            const data = JSON.parse(levelUpData);
            this.showNotification('🎉 Level Up!', `Você alcançou o nível ${data.level}!`, 'success');
            localStorage.removeItem('levelUp');
        }
    },

    checkForAchievements() {
        // Verificar novas conquistas (implementar com AJAX)
        const achievementData = localStorage.getItem('newAchievement');
        if (achievementData) {
            const data = JSON.parse(achievementData);
            this.showNotification('🏆 Nova Conquista!', data.name, 'success');
            localStorage.removeItem('newAchievement');
        }
    },

    showNotification(title, message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <h4>${title}</h4>
                <p>${message}</p>
            </div>
            <button class="notification-close">&times;</button>
        `;

        document.body.appendChild(notification);

        // Animação de entrada
        setTimeout(() => notification.classList.add('show'), 100);

        // Auto-remover após 5 segundos
        setTimeout(() => this.removeNotification(notification), 5000);

        // Botão de fechar
        notification.querySelector('.notification-close').addEventListener('click', () => {
            this.removeNotification(notification);
        });
    },

    removeNotification(notification) {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    },

    // Validação de Formulários
    initForms() {
        const forms = document.querySelectorAll('form[data-validate]');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
        });
    },

    validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('[required]');

        inputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                this.showFieldError(input, 'Este campo é obrigatório');
            } else {
                this.clearFieldError(input);
            }

            // Validação de e-mail
            if (input.type === 'email' && !this.isValidEmail(input.value)) {
                isValid = false;
                this.showFieldError(input, 'E-mail inválido');
            }
        });

        return isValid;
    },

    showFieldError(field, message) {
        field.classList.add('error');
        let errorElement = field.nextElementSibling;
        
        if (!errorElement || !errorElement.classList.contains('field-error')) {
            errorElement = document.createElement('div');
            errorElement.className = 'field-error';
            field.parentNode.insertBefore(errorElement, field.nextSibling);
        }
        
        errorElement.textContent = message;
    },

    clearFieldError(field) {
        field.classList.remove('error');
        const errorElement = field.nextElementSibling;
        if (errorElement && errorElement.classList.contains('field-error')) {
            errorElement.remove();
        }
    },

    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },

    // Funções de XP e Gamificação
    animateXPGain(amount) {
        const xpDisplay = document.querySelector('.xp-display');
        if (!xpDisplay) return;

        const animation = document.createElement('div');
        animation.className = 'xp-animation';
        animation.textContent = `+${amount} XP`;
        xpDisplay.appendChild(animation);

        setTimeout(() => animation.remove(), 2000);
    },

    // Countdown Timer para Desafios
    startCountdown(elementId, endTime) {
        const element = document.getElementById(elementId);
        if (!element) return;

        const timer = setInterval(() => {
            const now = new Date().getTime();
            const distance = endTime - now;

            if (distance < 0) {
                clearInterval(timer);
                element.innerHTML = "EXPIRADO";
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            element.innerHTML = `${days}d ${hours}h ${minutes}m ${seconds}s`;
        }, 1000);
    }
};

// CSS para notificações
const style = document.createElement('style');
style.textContent = `
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    max-width: 350px;
    background: var(--gray-800);
    border-radius: var(--radius-lg);
    padding: 1rem;
    box-shadow: var(--shadow-lg);
    transform: translateX(400px);
    transition: transform 0.3s ease;
    z-index: 9999;
    border-left: 4px solid;
}

.notification.show {
    transform: translateX(0);
}

.notification-success { border-color: var(--success); }
.notification-error { border-color: var(--danger); }
.notification-warning { border-color: var(--warning); }
.notification-info { border-color: var(--primary); }

.notification-content h4 {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
}

.notification-content p {
    margin: 0;
    font-size: 0.9rem;
    color: var(--gray-400);
}

.notification-close {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: none;
    border: none;
    color: var(--gray-500);
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.notification-close:hover {
    color: var(--white);
}

.xp-animation {
    position: absolute;
    top: -20px;
    right: 0;
    color: var(--accent);
    font-weight: bold;
    font-size: 1.25rem;
    animation: floatUp 2s ease-out forwards;
}

@keyframes floatUp {
    0% {
        opacity: 1;
        transform: translateY(0);
    }
    100% {
        opacity: 0;
        transform: translateY(-50px);
    }
}

.field-error {
    color: var(--danger);
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

.form-control.error {
    border-color: var(--danger);
}
`;
document.head.appendChild(style);

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    GameDevAcademy.init();
});

// Exportar para uso global
window.GameDevAcademy = GameDevAcademy;
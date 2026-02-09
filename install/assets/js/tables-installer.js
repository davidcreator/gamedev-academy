/**
 * Tables Installation Handler
 */

(function() {
    'use strict';

    const TablesInstaller = {
        isRunning: false,
        progressInterval: null,

        init: function() {
            const startBtn = document.getElementById('startInstallBtn');
            if (!startBtn) return;

            startBtn.addEventListener('click', () => this.start());
        },

        start: function() {
            if (this.isRunning) return;
            
            if (!confirm('Deseja iniciar a criação das tabelas do banco de dados?')) {
                return;
            }

            this.isRunning = true;
            
            const startBtn = document.getElementById('startInstallBtn');
            const progressDiv = document.getElementById('installProgress');
            
            // Update UI
            startBtn.disabled = true;
            startBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Criando tabelas...';
            
            // Show progress
            if (progressDiv) {
                progressDiv.classList.remove('d-none');
                progressDiv.style.display = 'block';
            }
            
            // Update all table items to processing
            document.querySelectorAll('.table-item').forEach(item => {
                item.classList.add('processing');
            });
            
            // Log inicial
            this.log('Iniciando criação das tabelas...', 'info');
            this.log('Conectando ao banco de dados...', 'info');
            
            // Simular progresso visual
            this.animateProgress();
            
            // Execute creation
            setTimeout(() => {
                this.log('Executando script create_tables.php...', 'info');
                this.executeCreation();
            }, 1000);
        },

        executeCreation: function() {
            const form = document.getElementById('tablesForm');
            if (!form) {
                this.handleError({ message: 'Formulário não encontrado' });
                return;
            }

            const formData = new FormData(form);
            
            // Log request
            this.log('Enviando requisição para criar tabelas...', 'info');
            
            // Make AJAX request
            fetch('ajax/create_tables_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Check if response is ok
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // Get response text first to debug
                return response.text();
            })
            .then(text => {
                // Try to parse JSON
                try {
                    const data = JSON.parse(text);
                    
                    if (data.success) {
                        this.handleSuccess(data);
                    } else {
                        this.handleError(data);
                    }
                } catch (e) {
                    console.error('Response text:', text);
                    this.handleError({ 
                        message: 'Resposta inválida do servidor. Verifique o console para mais detalhes.',
                        rawResponse: text 
                    });
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                this.handleError({ 
                    message: `Erro de conexão: ${error.message}` 
                });
            });
        },

        animateProgress: function() {
            let progress = 0;
            const progressBar = document.getElementById('progressBar');
            
            if (!progressBar) return;
            
            this.progressInterval = setInterval(() => {
                progress += Math.random() * 10;
                if (progress > 90) {
                    progress = 90;
                    clearInterval(this.progressInterval);
                }
                
                progressBar.style.width = progress + '%';
                progressBar.textContent = Math.round(progress) + '%';
                progressBar.setAttribute('aria-valuenow', Math.round(progress));
            }, 500);
        },

        handleSuccess: function(data) {
            // Clear interval
            if (this.progressInterval) {
                clearInterval(this.progressInterval);
            }
            
            const progressBar = document.getElementById('progressBar');
            if (progressBar) {
                progressBar.style.width = '100%';
                progressBar.textContent = '100%';
                progressBar.setAttribute('aria-valuenow', 100);
                progressBar.classList.remove('progress-bar-animated');
                progressBar.classList.add('bg-success');
            }
            
            // Update all tables to success
            document.querySelectorAll('.table-item').forEach(item => {
                item.classList.remove('processing');
                item.classList.add('success');
            });
            
            // Log success
            this.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'info');
            this.log('✓ Todas as tabelas foram criadas com sucesso!', 'success');
            this.log(`✓ Total: ${data.tables_created || 46} tabelas criadas`, 'success');
            
            if (data.data_inserted) {
                this.log('✓ Dados iniciais inseridos com sucesso', 'success');
            }
            
            this.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'info');
            
            // Show result
            const resultDiv = document.getElementById('installResult');
            if (resultDiv) {
                resultDiv.classList.remove('d-none');
                resultDiv.style.display = 'block';
                resultDiv.className = 'install-result success';
                resultDiv.innerHTML = `
                    <h5><i class="fas fa-check-circle"></i> Instalação Concluída!</h5>
                    <p>O banco de dados foi configurado com sucesso.</p>
                    <ul>
                        <li>${data.tables_created || 46} tabelas criadas</li>
                        <li>Estrutura completa instalada</li>
                        <li>Dados iniciais inseridos</li>
                        <li>Sistema pronto para configuração do administrador</li>
                    </ul>
                `;
            }
            
            // Show continue button, hide start button
            const continueBtn = document.getElementById('continueBtn');
            const startBtn = document.getElementById('startInstallBtn');
            
            if (continueBtn) {
                continueBtn.classList.remove('d-none');
                continueBtn.style.display = 'inline-block';
            }
            
            if (startBtn) {
                startBtn.style.display = 'none';
            }
            
            // Auto submit form after 3 seconds
            this.log('Redirecionando em 3 segundos...', 'info');
            setTimeout(() => {
                const form = document.getElementById('tablesForm');
                if (form) {
                    form.submit();
                }
            }, 3000);
        },

        handleError: function(data) {
            // Clear interval
            if (this.progressInterval) {
                clearInterval(this.progressInterval);
            }
            
            const progressBar = document.getElementById('progressBar');
            if (progressBar) {
                progressBar.classList.remove('progress-bar-animated');
                progressBar.classList.add('bg-danger');
            }
            
            // Update tables to show error
            document.querySelectorAll('.table-item').forEach(item => {
                item.classList.remove('processing');
                item.classList.add('error');
            });
            
            // Log errors
            this.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'error');
            this.log('✗ Erro ao criar tabelas', 'error');
            this.log(data.message || 'Erro desconhecido', 'error');
            
            if (data.errors && Array.isArray(data.errors)) {
                data.errors.forEach(error => {
                    this.log(`  → ${error}`, 'error');
                });
            }
            
            if (data.rawResponse) {
                console.error('Raw server response:', data.rawResponse);
                this.log('Verifique o console do navegador para detalhes', 'error');
            }
            
            this.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'error');
            
            // Show error result
            const resultDiv = document.getElementById('installResult');
            if (resultDiv) {
                resultDiv.classList.remove('d-none');
                resultDiv.style.display = 'block';
                resultDiv.className = 'install-result error';
                resultDiv.innerHTML = `
                    <h5><i class="fas fa-times-circle"></i> Erro na Instalação</h5>
                    <p>${data.message || 'Ocorreu um erro ao criar as tabelas.'}</p>
                    ${data.errors && data.errors.length > 0 ? 
                        '<p>Erros encontrados:</p><ul>' + 
                        data.errors.map(e => `<li>${e}</li>`).join('') + 
                        '</ul>' : ''}
                    <p>Por favor, verifique:</p>
                    <ul>
                        <li>As configurações do banco de dados estão corretas</li>
                        <li>O usuário tem permissões para criar tabelas</li>
                        <li>O arquivo create_tables.php existe em install/sql/</li>
                    </ul>
                `;
            }
            
            // Re-enable button
            const startBtn = document.getElementById('startInstallBtn');
            if (startBtn) {
                startBtn.disabled = false;
                startBtn.innerHTML = '<i class="fas fa-database"></i> Tentar Novamente';
            }
            
            this.isRunning = false;
        },

        log: function(message, type = 'info') {
            const logContainer = document.getElementById('installLog');
            if (!logContainer) return;
            
            const entry = document.createElement('div');
            entry.className = `log-entry log-${type}`;
            
            const timestamp = new Date().toLocaleTimeString('pt-BR');
            entry.textContent = `[${timestamp}] ${message}`;
            
            logContainer.appendChild(entry);
            
            // Auto scroll to bottom
            logContainer.scrollTop = logContainer.scrollHeight;
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            TablesInstaller.init();
        });
    } else {
        TablesInstaller.init();
    }

    // Prevent leaving page during installation
    window.addEventListener('beforeunload', function(e) {
        if (TablesInstaller.isRunning) {
            e.preventDefault();
            e.returnValue = 'A instalação está em progresso. Tem certeza que deseja sair?';
            return e.returnValue;
        }
    });

    // Expose to global scope for debugging
    window.TablesInstaller = TablesInstaller;

})();
/**
 * Editor.js Initializer
 * GameDev Academy
 */

class EditorJSManager {
    constructor(config) {
        this.holderId = config.holderId || 'editorjs';
        this.textareaId = config.textareaId || 'content';
        this.formId = config.formId || 'lessonForm';
        this.data = config.data || { blocks: [] };
        this.editor = null;
        
        this.waitForDependencies();
    }

    waitForDependencies() {
        let attempts = 0;
        const maxAttempts = 40;
        
        const check = () => {
            attempts++;
            
            if (typeof EditorJS !== 'undefined' && 
                typeof Header !== 'undefined' && 
                typeof EditorJSTools !== 'undefined') {
                
                console.log('✅ Dependências carregadas');
                this.init();
                
            } else if (attempts >= maxAttempts) {
                console.error('❌ Timeout');
                this.showError('Erro ao carregar editor');
                
            } else {
                setTimeout(check, 500);
            }
        };
        
        check();
    }

    init() {
        const holder = document.getElementById(this.holderId);
        if (!holder) {
            console.error('Elemento #' + this.holderId + ' não encontrado');
            return;
        }

        holder.innerHTML = '';

        try {
            this.editor = new EditorJS({
                holder: this.holderId,
                placeholder: 'Comece a escrever...',
                data: this.data,
                tools: EditorJSTools,
                
                onReady: () => {
                    console.log('✅ Editor pronto');
                },
                
                onChange: async () => {
                    await this.syncData();
                }
            });

            this.attachFormListener();

        } catch (error) {
            console.error('Erro ao criar editor:', error);
            this.showError(error.message);
        }
    }

    async syncData() {
        try {
            const data = await this.editor.save();
            const textarea = document.getElementById(this.textareaId);
            if (textarea) {
                textarea.value = JSON.stringify(data);
            }
        } catch (error) {
            console.error('Erro ao sincronizar:', error);
        }
    }

    attachFormListener() {
        const form = document.getElementById(this.formId);
        if (!form) {
            console.warn('Form #' + this.formId + ' não encontrado');
            return;
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            try {
                const data = await this.editor.save();

                if (!data.blocks || data.blocks.length === 0) {
                    alert('Adicione conteúdo antes de salvar');
                    return false;
                }

                document.getElementById(this.textareaId).value = JSON.stringify(data);
                form.submit();

            } catch (error) {
                console.error('Erro ao salvar:', error);
                alert('Erro ao processar conteúdo');
                return false;
            }
        });
    }

    showError(message) {
        const holder = document.getElementById(this.holderId);
        if (holder) {
            holder.innerHTML = `
                <div class="alert alert-danger">
                    <strong>Erro:</strong> ${message}
                    <button class="btn btn-sm btn-primary ms-2" onclick="location.reload()">
                        Recarregar
                    </button>
                </div>
            `;
        }
    }
}
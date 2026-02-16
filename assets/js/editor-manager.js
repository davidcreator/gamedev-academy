/**
 * Editor.js Manager
 * GameDev Academy
 */

class EditorManager {
    constructor(config) {
        this.holderId = config.holderId || 'editorjs';
        this.textareaId = config.textareaId || 'content';
        this.formId = config.formId || 'lessonForm';
        this.data = config.data || { blocks: [] };
        this.editor = null;
        
        this.waitForDependencies();
    }

    /**
     * Aguarda todas as dependências carregarem
     */
    waitForDependencies() {
        let attempts = 0;
        const maxAttempts = 40; // 20 segundos
        
        const check = () => {
            attempts++;
            
            if (typeof EditorJS !== 'undefined' && 
                typeof Header !== 'undefined' && 
                typeof List !== 'undefined') {
                
                console.log('✅ Dependências carregadas');
                this.init();
                
            } else if (attempts >= maxAttempts) {
                console.error('❌ Timeout ao carregar dependências');
                this.showError('Erro ao carregar o editor. Recarregue a página.');
                
            } else {
                setTimeout(check, 500);
            }
        };
        
        check();
    }

    /**
     * Inicializa o editor
     */
    init() {
        const holder = document.getElementById(this.holderId);
        if (!holder) {
            console.error('Elemento #' + this.holderId + ' não encontrado');
            return;
        }

        // Remove loading
        holder.innerHTML = '';

        // Configuração das ferramentas
        const tools = this.getTools();

        try {
            this.editor = new EditorJS({
                holder: this.holderId,
                placeholder: 'Comece a escrever o conteúdo...',
                data: this.data,
                tools: tools,
                
                onReady: () => {
                    console.log('✅ Editor.js pronto');
                },
                
                onChange: async () => {
                    await this.syncData();
                }
            });

            this.attachFormListener();

        } catch (error) {
            console.error('Erro ao inicializar editor:', error);
            this.showError(error.message);
        }
    }

    /**
     * Retorna configuração das ferramentas
     */
    getTools() {
        const tools = {};

        if (typeof Header !== 'undefined') {
            tools.header = {
                class: Header,
                inlineToolbar: true,
                config: { levels: [2, 3, 4], defaultLevel: 2 }
            };
        }

        if (typeof Paragraph !== 'undefined') {
            tools.paragraph = { class: Paragraph, inlineToolbar: true };
        }

        if (typeof List !== 'undefined') {
            tools.list = { class: List, inlineToolbar: true };
        }

        if (typeof Checklist !== 'undefined') {
            tools.checklist = { class: Checklist, inlineToolbar: true };
        }

        if (typeof Quote !== 'undefined') {
            tools.quote = { class: Quote, inlineToolbar: true };
        }

        if (typeof Warning !== 'undefined') {
            tools.warning = { class: Warning, inlineToolbar: true };
        }

        if (typeof CodeTool !== 'undefined') {
            tools.code = { class: CodeTool };
        }

        if (typeof Delimiter !== 'undefined') {
            tools.delimiter = Delimiter;
        }

        if (typeof Table !== 'undefined') {
            tools.table = { class: Table, inlineToolbar: true };
        }

        if (typeof Marker !== 'undefined') {
            tools.marker = { class: Marker };
        }

        if (typeof Underline !== 'undefined') {
            tools.underline = { class: Underline };
        }

        if (typeof ImageTool !== 'undefined') {
            tools.image = {
                class: ImageTool,
                config: {
                    endpoints: { byFile: 'upload-image.php' }
                }
            };
        }

        if (typeof Embed !== 'undefined') {
            tools.embed = {
                class: Embed,
                config: {
                    services: {
                        youtube: true,
                        vimeo: true,
                        codepen: true
                    }
                }
            };
        }

        return tools;
    }

    /**
     * Sincroniza dados com textarea
     */
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

    /**
     * Adiciona listener ao formulário
     */
    attachFormListener() {
        const form = document.getElementById(this.formId);
        if (!form) {
            console.warn('Formulário #' + this.formId + ' não encontrado');
            return;
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            try {
                const data = await this.editor.save();

                if (!data.blocks || data.blocks.length === 0) {
                    alert('Por favor, adicione conteúdo antes de salvar.');
                    return false;
                }

                document.getElementById(this.textareaId).value = JSON.stringify(data);
                form.submit();

            } catch (error) {
                console.error('Erro ao salvar:', error);
                alert('Erro ao processar o conteúdo.');
                return false;
            }
        });
    }

    /**
     * Exibe mensagem de erro
     */
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
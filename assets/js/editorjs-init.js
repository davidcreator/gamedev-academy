/**
 * Editor.js Initializer
 * Função reutilizável para inicializar o editor
 */

class EditorJSManager {
    constructor(config) {
        this.holderId = config.holderId || 'editorjs';
        this.textareaId = config.textareaId || 'content';
        this.formId = config.formId || null;
        this.placeholder = config.placeholder || 'Comece a escrever...';
        this.data = config.data || { blocks: [] };
        this.uploadEndpoint = config.uploadEndpoint || 'upload-image.php';
        this.editor = null;
        
        this.init();
    }

    /**
     * Valida os dados carregados
     */
    validateData(data) {
        if (!data || typeof data !== 'object') {
            return false;
        }
        if (!Array.isArray(data.blocks)) {
            return false;
        }
        return true;
    }

    /**
     * Inicializa o editor
     */
    init() {
        if (!this.validateData(this.data)) {
            console.warn('Dados inválidos, usando estrutura vazia');
            this.data = {
                time: new Date().getTime(),
                blocks: [],
                version: '2.28.0'
            };
        }

        // Atualiza endpoint de upload nas tools
        if (typeof EditorJSTools.image !== 'undefined') {
            EditorJSTools.image.config.endpoints.byFile = this.uploadEndpoint;
        }

        // Inicializa o Editor.js
        this.editor = new EditorJS({
            holder: this.holderId,
            placeholder: this.placeholder,
            data: this.data,
            tools: EditorJSTools,
            
            onReady: () => {
                console.log('✅ Editor.js está pronto!');
            },
            
            onChange: async (api, event) => {
                try {
                    const data = await this.editor.save();
                    this.syncToTextarea(data);
                } catch (error) {
                    console.error('Erro ao sincronizar:', error);
                }
            }
        });

        // Adiciona listener ao formulário se especificado
        if (this.formId) {
            this.attachFormListener();
        }
    }

    /**
     * Sincroniza dados com textarea
     */
    syncToTextarea(data) {
        const textarea = document.getElementById(this.textareaId);
        if (textarea) {
            textarea.value = JSON.stringify(data);
        }
    }

    /**
     * Adiciona listener ao formulário
     */
    attachFormListener() {
        const form = document.getElementById(this.formId);
        if (!form) {
            console.warn(`Formulário #${this.formId} não encontrado`);
            return;
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            try {
                const savedData = await this.editor.save();
                
                if (!savedData.blocks || savedData.blocks.length === 0) {
                    alert('⚠️ Por favor, adicione conteúdo antes de salvar.');
                    return false;
                }
                
                this.syncToTextarea(savedData);
                
                console.log('💾 Dados salvos:', savedData);
                
                // Submete o formulário
                form.submit();
                
            } catch (error) {
                console.error('❌ Erro ao salvar:', error);
                alert('Ocorreu um erro ao processar o conteúdo.');
                return false;
            }
        });
    }

    /**
     * Retorna instância do editor
     */
    getInstance() {
        return this.editor;
    }
}
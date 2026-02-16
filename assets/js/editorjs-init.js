/**
 * Editor.js Initializer - VERSÃO COMPLETA
 * Com Undo/Redo, Drag & Drop e Auto-save
 */

class EditorJSManager {
    constructor(config) {
        this.holderId = config.holderId || 'editorjs';
        this.textareaId = config.textareaId || 'content';
        this.formId = config.formId || null;
        this.placeholder = config.placeholder || 'Comece a escrever...';
        this.data = config.data || { blocks: [] };
        this.uploadEndpoint = config.uploadEndpoint || 'upload-image.php';
        this.autosave = config.autosave !== false;
        this.autosaveInterval = config.autosaveInterval || 15000; // 15 segundos
        this.editor = null;
        this.autosaveTimer = null;
        
        this.init();
    }

    validateData(data) {
        if (!data || typeof data !== 'object') {
            return false;
        }
        if (!Array.isArray(data.blocks)) {
            return false;
        }
        return true;
    }

    init() {
        if (!this.validateData(this.data)) {
            console.warn('⚠️ Dados inválidos, usando estrutura vazia');
            this.data = {
                time: new Date().getTime(),
                blocks: [],
                version: '2.28.0'
            };
        }

        // Atualiza endpoint nas ferramentas
        if (typeof EditorJSTools.image !== 'undefined') {
            EditorJSTools.image.config.endpoints.byFile = this.uploadEndpoint;
        }

        if (typeof EditorJSTools.attaches !== 'undefined') {
            EditorJSTools.attaches.config.endpoint = this.uploadEndpoint.replace('image', 'file');
        }

        // Inicializa o Editor.js
        this.editor = new EditorJS({
            holder: this.holderId,
            placeholder: this.placeholder,
            data: this.data,
            tools: EditorJSTools,
            
            // ========== PLUGINS EXTRAS ==========
            onReady: () => {
                console.log('✅ Editor.js está pronto!');
                
                // Ativa Drag & Drop
                if (typeof DragDrop !== 'undefined') {
                    new DragDrop(this.editor);
                }
                
                // Ativa Undo/Redo
                if (typeof Undo !== 'undefined') {
                    new Undo({ editor: this.editor });
                }
                
                this.showReadyNotification();
            },
            
            onChange: async (api, event) => {
                try {
                    const data = await this.editor.save();
                    this.syncToTextarea(data);
                    
                    // Auto-save
                    if (this.autosave) {
                        this.scheduleAutosave();
                    }
                } catch (error) {
                    console.error('❌ Erro ao sincronizar:', error);
                }
            },

            // ========== ATALHOS CUSTOMIZADOS ==========
            defaultBlock: 'paragraph',
            
            /**
             * Configuração de inline toolbar
             */
            inlineToolbar: ['bold', 'italic', 'link', 'marker', 'underline'],
            
            /**
             * Logs habilitados em desenvolvimento
             */
            logLevel: 'ERROR'
        });

        // Listener do formulário
        if (this.formId) {
            this.attachFormListener();
        }

        // Listener de atalhos customizados
        this.attachKeyboardShortcuts();
    }

    /**
     * Sincroniza com textarea
     */
    syncToTextarea(data) {
        const textarea = document.getElementById(this.textareaId);
        if (textarea) {
            textarea.value = JSON.stringify(data);
        }
    }

    /**
     * Auto-save agendado
     */
    scheduleAutosave() {
        if (this.autosaveTimer) {
            clearTimeout(this.autosaveTimer);
        }

        this.autosaveTimer = setTimeout(() => {
            this.performAutosave();
        }, this.autosaveInterval);
    }

    /**
     * Executa auto-save
     */
    async performAutosave() {
        try {
            const savedData = await this.editor.save();
            
            // Salva no localStorage
            localStorage.setItem(`editorjs_autosave_${this.holderId}`, JSON.stringify(savedData));
            
            console.log('💾 Auto-save realizado');
            
            // Notificação visual (opcional)
            this.showAutosaveNotification();
            
        } catch (error) {
            console.error('❌ Erro no auto-save:', error);
        }
    }

    /**
     * Listener do formulário
     */
    attachFormListener() {
        const form = document.getElementById(this.formId);
        if (!form) {
            console.warn(`⚠️ Formulário #${this.formId} não encontrado`);
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
                
                // Limpa auto-save
                localStorage.removeItem(`editorjs_autosave_${this.holderId}`);
                
                // Submete
                form.submit();
                
            } catch (error) {
                console.error('❌ Erro ao salvar:', error);
                alert('Ocorreu um erro ao processar o conteúdo.');
                return false;
            }
        });
    }

    /**
     * Atalhos de teclado customizados
     */
    attachKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl+S para salvar
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                const form = document.getElementById(this.formId);
                if (form) {
                    form.dispatchEvent(new Event('submit'));
                }
            }

            // Ctrl+Shift+P para preview
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'P') {
                e.preventDefault();
                this.showPreview();
            }
        });
    }

    /**
     * Preview do conteúdo
     */
    async showPreview() {
        try {
            const data = await this.editor.save();
            console.log('👁️ Preview:', data);
            // Implementar modal de preview aqui
        } catch (error) {
            console.error('Erro ao gerar preview:', error);
        }
    }

    /**
     * Notificações visuais
     */
    showReadyNotification() {
        this.showNotification('✅ Editor carregado com sucesso!', 'success');
    }

    showAutosaveNotification() {
        this.showNotification('💾 Rascunho salvo automaticamente', 'info', 2000);
    }

    showNotification(message, type = 'info', duration = 3000) {
        // Implementação simples de notificação
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} position-fixed top-0 end-0 m-3`;
        notification.style.zIndex = '9999';
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, duration);
    }

    /**
     * Retorna instância do editor
     */
    getInstance() {
        return this.editor;
    }

    /**
     * Destrói o editor
     */
    destroy() {
        if (this.editor && typeof this.editor.destroy === 'function') {
            this.editor.destroy();
        }
        if (this.autosaveTimer) {
            clearTimeout(this.autosaveTimer);
        }
    }
}
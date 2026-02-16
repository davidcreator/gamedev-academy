/**
 * Editor.js Manager
 * GameDev Academy
 */

function EditorJSManager(config) {
    this.holderId = config.holderId || 'editorjs';
    this.textareaId = config.textareaId || 'content';
    this.formId = config.formId || 'lessonForm';
    this.data = config.data || { blocks: [] };
    this.editor = null;

    this.waitForDependencies();
}

EditorJSManager.prototype.waitForDependencies = function() {
    var self = this;
    var attempts = 0;
    var maxAttempts = 40;

    function check() {
        attempts++;

        if (typeof EditorJS !== 'undefined' && typeof EditorJSTools !== 'undefined') {
            console.log('✅ Dependências prontas');
            self.createEditor();
        } else if (attempts >= maxAttempts) {
            console.error('❌ Timeout ao carregar dependências');
        } else {
            setTimeout(check, 500);
        }
    }

    check();
};

EditorJSManager.prototype.createEditor = function() {
    var self = this;
    var holder = document.getElementById(this.holderId);

    if (!holder) {
        console.error('Elemento #' + this.holderId + ' não encontrado');
        return;
    }

    // Limpa conteúdo do holder
    holder.innerHTML = '';

    try {
        this.editor = new EditorJS({
            holder: this.holderId,
            placeholder: 'Comece a escrever...',
            data: this.data,
            tools: EditorJSTools,

            onReady: function() {
                console.log('✅ Editor.js pronto!');
            },

            onChange: function() {
                self.syncData();
            }
        });

        this.attachFormListener();

    } catch (error) {
        console.error('Erro ao criar editor:', error);
        holder.innerHTML = '<div class="alert alert-danger">Erro ao inicializar o editor.</div>';
    }
};

EditorJSManager.prototype.syncData = function() {
    var self = this;
    
    if (!this.editor) return;

    this.editor.save().then(function(data) {
        var textarea = document.getElementById(self.textareaId);
        if (textarea) {
            textarea.value = JSON.stringify(data);
        }
    }).catch(function(error) {
        console.error('Erro ao sincronizar:', error);
    });
};

EditorJSManager.prototype.attachFormListener = function() {
    var self = this;
    var form = document.getElementById(this.formId);

    if (!form) {
        console.warn('Form #' + this.formId + ' não encontrado');
        return;
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!self.editor) {
            alert('Aguarde o editor carregar');
            return;
        }

        self.editor.save().then(function(data) {
            if (!data.blocks || data.blocks.length === 0) {
                alert('Adicione conteúdo antes de salvar');
                return;
            }

            document.getElementById(self.textareaId).value = JSON.stringify(data);
            form.submit();

        }).catch(function(error) {
            console.error('Erro ao salvar:', error);
            alert('Erro ao processar conteúdo');
        });
    });
};
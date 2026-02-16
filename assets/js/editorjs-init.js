/**
 * EditorJS Initialization Script
 * Script para inicializar e configurar o EditorJS com Toolbar Fixa
 */

/**
 * Cria a estrutura de wrapper e toolbar fixa para o editor
 */
function createEditorWrapper(holderId) {
    const holderElement = document.getElementById(holderId);
    
    // Verificar se já foi criado
    if (holderElement.parentElement.classList.contains('editorjs-wrapper')) {
        return;
    }
    
    // Criar wrapper
    const wrapper = document.createElement('div');
    wrapper.className = 'editorjs-wrapper';
    
    // Criar toolbar
    const toolbar = document.createElement('div');
    toolbar.className = 'editorjs-toolbar';
    toolbar.id = 'editorjs-custom-toolbar';
    
    // HTML da toolbar
    toolbar.innerHTML = `
        <!-- Grupo de Formatação de Texto -->
        <div class="editorjs-toolbar-group">
            <select class="editorjs-heading-select" id="heading-selector" title="Estilo de parágrafo">
                <option value="paragraph">Parágrafo</option>
                <option value="header-1">Título 1</option>
                <option value="header-2">Título 2</option>
                <option value="header-3">Título 3</option>
                <option value="header-4">Título 4</option>
            </select>
        </div>
        
        <!-- Grupo de Listas -->
        <div class="editorjs-toolbar-group">
            <button class="editorjs-toolbar-btn" data-tool="list-unordered" title="Lista não ordenada">
                <i class="fas fa-list-ul"></i>
                <span>Lista</span>
            </button>
            <button class="editorjs-toolbar-btn" data-tool="list-ordered" title="Lista numerada">
                <i class="fas fa-list-ol"></i>
                <span>Numerada</span>
            </button>
            <button class="editorjs-toolbar-btn" data-tool="checklist" title="Lista de tarefas">
                <i class="fas fa-check-square"></i>
                <span>Tarefas</span>
            </button>
        </div>
        
        <!-- Grupo de Blocos -->
        <div class="editorjs-toolbar-group">
            <button class="editorjs-toolbar-btn" data-tool="quote" title="Citação">
                <i class="fas fa-quote-right"></i>
                <span>Citação</span>
            </button>
            <button class="editorjs-toolbar-btn" data-tool="code" title="Código">
                <i class="fas fa-code"></i>
                <span>Código</span>
            </button>
            <button class="editorjs-toolbar-btn" data-tool="delimiter" title="Divisor">
                <i class="fas fa-minus"></i>
                <span>Divisor</span>
            </button>
        </div>
        
        <!-- Grupo de Mídia -->
        <div class="editorjs-toolbar-group">
            <button class="editorjs-toolbar-btn" data-tool="image" title="Inserir imagem">
                <i class="fas fa-image"></i>
                <span>Imagem</span>
            </button>
            <button class="editorjs-toolbar-btn" data-tool="table" title="Inserir tabela">
                <i class="fas fa-table"></i>
                <span>Tabela</span>
            </button>
            <button class="editorjs-toolbar-btn" data-tool="embed" title="Incorporar vídeo">
                <i class="fas fa-video"></i>
                <span>Vídeo</span>
            </button>
        </div>
        
        <!-- Grupo de Outros -->
        <div class="editorjs-toolbar-group">
            <button class="editorjs-toolbar-btn" data-tool="warning" title="Aviso">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Aviso</span>
            </button>
            <button class="editorjs-toolbar-btn" data-tool="raw" title="HTML">
                <i class="fas fa-file-code"></i>
                <span>HTML</span>
            </button>
        </div>
    `;
    
    // Inserir wrapper ao redor do editor
    holderElement.parentNode.insertBefore(wrapper, holderElement);
    wrapper.appendChild(toolbar);
    wrapper.appendChild(holderElement);
    
    // Adicionar event listeners nos botões após o editor ser criado
    setTimeout(() => {
        attachToolbarEvents();
    }, 500);
}

/**
 * Adiciona eventos aos botões da toolbar
 */
function attachToolbarEvents() {
    const editor = window.editorInstance;
    if (!editor) return;
    
    // Selector de headings
    const headingSelector = document.getElementById('heading-selector');
    if (headingSelector) {
        headingSelector.addEventListener('change', function() {
            const value = this.value;
            
            if (value === 'paragraph') {
                // Inserir parágrafo
                editor.blocks.insert('paragraph', {
                    text: ''
                });
            } else if (value.startsWith('header-')) {
                // Inserir header
                const level = parseInt(value.split('-')[1]);
                editor.blocks.insert('header', {
                    text: '',
                    level: level
                });
            }
            
            // Resetar selector
            this.value = 'paragraph';
            
            // Focar no último bloco
            const blocksCount = editor.blocks.getBlocksCount();
            if (blocksCount > 0) {
                editor.caret.setToBlock(blocksCount - 1);
            }
        });
    }
    
    // Botões de ferramentas
    const toolButtons = document.querySelectorAll('.editorjs-toolbar-btn[data-tool]');
    toolButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tool = this.dataset.tool;
            
            switch(tool) {
                case 'list-unordered':
                    editor.blocks.insert('list', {
                        style: 'unordered',
                        items: ['']
                    });
                    break;
                    
                case 'list-ordered':
                    editor.blocks.insert('list', {
                        style: 'ordered',
                        items: ['']
                    });
                    break;
                    
                case 'checklist':
                    editor.blocks.insert('checklist', {
                        items: [
                            { text: '', checked: false }
                        ]
                    });
                    break;
                    
                case 'quote':
                    editor.blocks.insert('quote', {
                        text: '',
                        caption: ''
                    });
                    break;
                    
                case 'code':
                    editor.blocks.insert('code', {
                        code: ''
                    });
                    break;
                    
                case 'delimiter':
                    editor.blocks.insert('delimiter', {});
                    break;
                    
                case 'image':
                    editor.blocks.insert('image', {
                        file: {
                            url: ''
                        },
                        caption: ''
                    });
                    break;
                    
                case 'table':
                    editor.blocks.insert('table', {
                        content: [
                            ['', ''],
                            ['', '']
                        ]
                    });
                    break;
                    
                case 'embed':
                    editor.blocks.insert('embed', {
                        service: 'youtube',
                        source: '',
                        embed: '',
                        width: 580,
                        height: 320,
                        caption: ''
                    });
                    break;
                    
                case 'warning':
                    editor.blocks.insert('warning', {
                        title: '',
                        message: ''
                    });
                    break;
                    
                case 'raw':
                    editor.blocks.insert('raw', {
                        html: ''
                    });
                    break;
            }
            
            // Focar no último bloco inserido
            const blocksCount = editor.blocks.getBlocksCount();
            if (blocksCount > 0) {
                editor.caret.setToBlock(blocksCount - 1, 'end');
            }
        });
    });
}

/**
 * Função principal de inicialização do EditorJS
 * 
 * @param {string} holderId - ID do elemento que receberá o editor
 * @param {string} outputId - ID do textarea que receberá o JSON
 * @param {object|null} initialData - Dados iniciais do editor (opcional)
 */
function initializeEditorJS(holderId = 'editorjs', outputId = 'content', initialData = null) {
    // Verificar se os elementos existem
    const holderElement = document.getElementById(holderId);
    const outputElement = document.getElementById(outputId);

    if (!holderElement) {
        console.error(`Elemento com ID "${holderId}" não encontrado`);
        return null;
    }

    if (!outputElement) {
        console.error(`Elemento com ID "${outputId}" não encontrado`);
        return null;
    }

    // Verificar se EditorJS está carregado
    if (typeof EditorJS === 'undefined') {
        console.error('EditorJS não está carregado');
        return null;
    }

    // Criar wrapper e toolbar antes de inicializar o editor
    createEditorWrapper(holderId);

    // Configuração dos tools
    const tools = getEditorTools();

    // Configuração do editor
    const editorConfig = {
        holder: holderId,
        
        // Dados iniciais (se houver)
        data: initialData || {
            blocks: []
        },

        // Tools disponíveis
        tools: tools,

        // Configurações gerais
        autofocus: false,
        placeholder: 'Comece a escrever aqui...',
        
        // Callbacks
        onChange: async function(api, event) {
            // Salvar conteúdo no textarea toda vez que houver mudança
            try {
                const savedData = await api.saver.save();
                outputElement.value = JSON.stringify(savedData);
                
                // Disparar evento personalizado para integração com formulários
                const changeEvent = new CustomEvent('editorjs-change', {
                    detail: { data: savedData }
                });
                outputElement.dispatchEvent(changeEvent);
            } catch (error) {
                console.error('Erro ao salvar conteúdo:', error);
            }
        },

        onReady: function() {
            console.log('EditorJS está pronto!');
            
            // Adicionar classe ao container para estilização adicional
            holderElement.classList.add('editorjs-ready');
            
            // Disparar evento personalizado
            const readyEvent = new CustomEvent('editorjs-ready');
            document.dispatchEvent(readyEvent);
        },

        // Configurações i18n (português)
        i18n: {
            messages: {
                ui: {
                    "blockTunes": {
                        "toggler": {
                            "Click to tune": "Clique para configurar",
                            "or drag to move": "ou arraste para mover"
                        },
                    },
                    "inlineToolbar": {
                        "converter": {
                            "Convert to": "Converter para"
                        }
                    },
                    "toolbar": {
                        "toolbox": {
                            "Add": "Adicionar"
                        }
                    }
                },
                toolNames: {
                    "Text": "Texto",
                    "Heading": "Título",
                    "List": "Lista",
                    "Checklist": "Lista de tarefas",
                    "Quote": "Citação",
                    "Code": "Código",
                    "Delimiter": "Divisor",
                    "Table": "Tabela",
                    "Warning": "Aviso",
                    "Image": "Imagem",
                    "Embed": "Incorporar",
                    "Raw HTML": "HTML",
                    "Link": "Link",
                    "Marker": "Marcador",
                    "Bold": "Negrito",
                    "Italic": "Itálico",
                    "InlineCode": "Código inline",
                    "Underline": "Sublinhado"
                },
                tools: {
                    "warning": {
                        "Title": "Título",
                        "Message": "Mensagem",
                    },
                    "link": {
                        "Add a link": "Adicionar link"
                    },
                    "stub": {
                        'The block can not be displayed correctly.': 'O bloco não pode ser exibido corretamente.'
                    }
                },
                blockTunes: {
                    "delete": {
                        "Delete": "Deletar"
                    },
                    "moveUp": {
                        "Move up": "Mover para cima"
                    },
                    "moveDown": {
                        "Move down": "Mover para baixo"
                    }
                },
            }
        }
    };

    // Criar instância do editor
    let editor;
    try {
        editor = new EditorJS(editorConfig);
        
        // Salvar referência global para debug (remover em produção se necessário)
        window.editorInstance = editor;
        
        return editor;
    } catch (error) {
        console.error('Erro ao inicializar EditorJS:', error);
        return null;
    }
}

/**
 * Retorna a configuração dos tools do EditorJS
 */
function getEditorTools() {
    const tools = {};

    // Header (Títulos)
    if (typeof Header !== 'undefined') {
        tools.header = {
            class: Header,
            config: {
                placeholder: 'Digite um título...',
                levels: [1, 2, 3, 4, 5, 6],
                defaultLevel: 2
            },
            inlineToolbar: true,
            shortcut: 'CMD+SHIFT+H'
        };
    }

    // List (Listas)
    if (typeof List !== 'undefined') {
        tools.list = {
            class: List,
            inlineToolbar: true,
            config: {
                defaultStyle: 'unordered'
            }
        };
    }

    // Checklist
    if (typeof Checklist !== 'undefined') {
        tools.checklist = {
            class: Checklist,
            inlineToolbar: true
        };
    }

    // Quote (Citações)
    if (typeof Quote !== 'undefined') {
        tools.quote = {
            class: Quote,
            inlineToolbar: true,
            config: {
                quotePlaceholder: 'Digite uma citação',
                captionPlaceholder: 'Autor da citação',
            },
            shortcut: 'CMD+SHIFT+O'
        };
    }

    // Code (Blocos de código)
    if (typeof CodeTool !== 'undefined') {
        tools.code = {
            class: CodeTool,
            shortcut: 'CMD+SHIFT+C'
        };
    }

    // Delimiter (Separador)
    if (typeof Delimiter !== 'undefined') {
        tools.delimiter = Delimiter;
    }

    // Table (Tabelas)
    if (typeof Table !== 'undefined') {
        tools.table = {
            class: Table,
            inlineToolbar: true,
            config: {
                rows: 2,
                cols: 3,
            }
        };
    }

    // Warning (Avisos)
    if (typeof Warning !== 'undefined') {
        tools.warning = {
            class: Warning,
            inlineToolbar: true,
            config: {
                titlePlaceholder: 'Título',
                messagePlaceholder: 'Mensagem',
            }
        };
    }

    // Image (Imagens)
    if (typeof ImageTool !== 'undefined') {
        tools.image = {
            class: ImageTool,
            config: {
                endpoints: {
                    byFile: '/admin/upload-image.php', // Endpoint para upload
                    byUrl: '/admin/upload-image-url.php', // Endpoint para URL
                },
                field: 'image',
                types: 'image/*',
                additionalRequestHeaders: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                captionPlaceholder: 'Legenda da imagem',
                buttonContent: 'Selecionar imagem',
                uploader: {
                    uploadByFile: function(file) {
                        // Implementação customizada de upload
                        return uploadImageFile(file);
                    },
                    uploadByUrl: function(url) {
                        // Implementação customizada de upload por URL
                        return uploadImageByUrl(url);
                    }
                }
            }
        };
    }

    // Embed (Incorporar vídeos, etc)
    if (typeof Embed !== 'undefined') {
        tools.embed = {
            class: Embed,
            config: {
                services: {
                    youtube: true,
                    vimeo: true,
                    codepen: true,
                    coub: true,
                    twitter: true,
                    instagram: true,
                    facebook: true,
                    github: true,
                }
            }
        };
    }

    // Raw HTML
    if (typeof RawTool !== 'undefined') {
        tools.raw = {
            class: RawTool,
            config: {
                placeholder: 'Digite HTML...'
            }
        };
    }

    // Inline Tools

    // Marker (Destacar texto)
    if (typeof Marker !== 'undefined') {
        tools.marker = {
            class: Marker,
            shortcut: 'CMD+SHIFT+M'
        };
    }

    // InlineCode
    if (typeof InlineCode !== 'undefined') {
        tools.inlineCode = {
            class: InlineCode,
            shortcut: 'CMD+SHIFT+K'
        };
    }

    // Underline
    if (typeof Underline !== 'undefined') {
        tools.underline = Underline;
    }

    return tools;
}

/**
 * Função auxiliar para upload de imagem por arquivo
 */
async function uploadImageFile(file) {
    const formData = new FormData();
    formData.append('image', file);

    try {
        const response = await fetch('/admin/upload-image.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const result = await response.json();

        if (result.success === 1) {
            return {
                success: 1,
                file: {
                    url: result.file.url,
                }
            };
        } else {
            throw new Error(result.message || 'Erro ao fazer upload');
        }
    } catch (error) {
        console.error('Erro no upload:', error);
        return {
            success: 0,
            file: {
                url: ''
            }
        };
    }
}

/**
 * Função auxiliar para upload de imagem por URL
 */
async function uploadImageByUrl(url) {
    try {
        const response = await fetch('/admin/upload-image-url.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ url: url })
        });

        const result = await response.json();

        if (result.success === 1) {
            return {
                success: 1,
                file: {
                    url: result.file.url,
                }
            };
        } else {
            throw new Error(result.message || 'Erro ao fazer upload');
        }
    } catch (error) {
        console.error('Erro no upload por URL:', error);
        return {
            success: 0,
            file: {
                url: url // Fallback para URL original
            }
        };
    }
}

/**
 * Função auxiliar para validar JSON do EditorJS
 */
function validateEditorJSData(data) {
    try {
        const parsed = typeof data === 'string' ? JSON.parse(data) : data;
        return parsed && parsed.blocks && Array.isArray(parsed.blocks);
    } catch (error) {
        return false;
    }
}

/**
 * Função para limpar o editor
 */
function clearEditor(editor) {
    if (editor && editor.clear) {
        editor.clear();
    }
}

/**
 * Função para obter o conteúdo atual do editor
 */
async function getEditorData(editor) {
    if (editor && editor.save) {
        try {
            return await editor.save();
        } catch (error) {
            console.error('Erro ao obter dados:', error);
            return null;
        }
    }
    return null;
}

/**
 * Função para renderizar conteúdo do EditorJS em HTML (cliente)
 * Útil para preview
 */
function renderEditorJSContent(data) {
    if (!data || !data.blocks) {
        return '';
    }

    let html = '';
    
    data.blocks.forEach(block => {
        switch(block.type) {
            case 'header':
                html += `<h${block.data.level}>${block.data.text}</h${block.data.level}>`;
                break;
            case 'paragraph':
                html += `<p>${block.data.text}</p>`;
                break;
            case 'list':
                const tag = block.data.style === 'ordered' ? 'ol' : 'ul';
                html += `<${tag}>`;
                block.data.items.forEach(item => {
                    html += `<li>${item}</li>`;
                });
                html += `</${tag}>`;
                break;
            case 'quote':
                html += `<blockquote>${block.data.text}`;
                if (block.data.caption) {
                    html += `<cite>${block.data.caption}</cite>`;
                }
                html += `</blockquote>`;
                break;
            case 'code':
                html += `<pre><code>${escapeHtml(block.data.code)}</code></pre>`;
                break;
            case 'delimiter':
                html += `<hr>`;
                break;
            case 'image':
                html += `<figure>`;
                html += `<img src="${block.data.file.url}" alt="${block.data.caption || ''}">`;
                if (block.data.caption) {
                    html += `<figcaption>${block.data.caption}</figcaption>`;
                }
                html += `</figure>`;
                break;
        }
    });

    return html;
}

/**
 * Função auxiliar para escapar HTML
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Event Listeners globais

// Prevenir perda de dados ao sair da página
window.addEventListener('beforeunload', function(e) {
    if (window.editorInstance) {
        const outputElement = document.getElementById('content');
        if (outputElement && outputElement.value) {
            // Verificar se houve mudanças não salvas
            // Isso pode ser melhorado com um sistema de tracking de mudanças
        }
    }
});

// Log para debug (remover em produção)
console.log('EditorJS Init Script carregado');
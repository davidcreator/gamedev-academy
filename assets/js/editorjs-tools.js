/**
 * Editor.js Tools Configuration
 * GameDev Academy
 *
 * Apenas ferramentas com CDN carregado no editorjs-loader.php
 */

var EditorJSTools = {};

// Adiciona cada ferramenta SOMENTE se estiver disponível

if (typeof Header !== 'undefined') {
    EditorJSTools.header = {
        class: Header,
        inlineToolbar: true,
        config: {
            levels: [2, 3, 4],
            defaultLevel: 2
        }
    };
}

if (typeof Paragraph !== 'undefined') {
    EditorJSTools.paragraph = {
        class: Paragraph,
        inlineToolbar: true
    };
}

if (typeof List !== 'undefined') {
    EditorJSTools.list = {
        class: List,
        inlineToolbar: true
    };
}

if (typeof Checklist !== 'undefined') {
    EditorJSTools.checklist = {
        class: Checklist,
        inlineToolbar: true
    };
}

if (typeof Quote !== 'undefined') {
    EditorJSTools.quote = {
        class: Quote,
        inlineToolbar: true,
        config: {
            quotePlaceholder: 'Insira a citação',
            captionPlaceholder: 'Autor'
        }
    };
}

if (typeof Warning !== 'undefined') {
    EditorJSTools.warning = {
        class: Warning,
        inlineToolbar: true,
        config: {
            titlePlaceholder: 'Título',
            messagePlaceholder: 'Mensagem'
        }
    };
}

if (typeof CodeTool !== 'undefined') {
    EditorJSTools.code = {
        class: CodeTool
    };
}

if (typeof Delimiter !== 'undefined') {
    EditorJSTools.delimiter = Delimiter;
}

if (typeof Table !== 'undefined') {
    EditorJSTools.table = {
        class: Table,
        inlineToolbar: true,
        config: {
            rows: 2,
            cols: 3
        }
    };
}

if (typeof Marker !== 'undefined') {
    EditorJSTools.marker = {
        class: Marker
    };
}

if (typeof Underline !== 'undefined') {
    EditorJSTools.underline = {
        class: Underline
    };
}

if (typeof ImageTool !== 'undefined') {
    EditorJSTools.image = {
        class: ImageTool,
        config: {
            endpoints: {
                byFile: 'upload-image.php'
            }
        }
    };
}

if (typeof Embed !== 'undefined') {
    EditorJSTools.embed = {
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

console.log('🛠️ EditorJS Tools carregadas:', Object.keys(EditorJSTools));
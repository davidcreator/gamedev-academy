/**
 * Editor.js Tools Configuration
 * Configuração centralizada de ferramentas
 */

const EditorJSTools = {
    header: {
        class: Header,
        inlineToolbar: ['bold', 'italic', 'link'],
        config: {
            levels: [2, 3, 4],
            defaultLevel: 2
        }
    },
    list: {
        class: List,
        inlineToolbar: true
    },
    code: {
        class: CodeTool,
        config: {
            placeholder: 'Cole seu código aqui (GDScript, C#, JavaScript...)'
        }
    },
    embed: {
        class: Embed,
        config: {
            services: {
                youtube: true,
                vimeo: true,
                codepen: {
                    regex: /https?:\/\/codepen\.io\/([^\/\?\&]*)\/pen\/([^\/\?\&]*)/,
                    embedUrl: 'https://codepen.io/<%= remote_id %>?height=300&theme-id=0&default-tab=result&embed-version=2',
                    html: "<iframe height='300' scrolling='no' frameborder='no' allowtransparency='true' allowfullscreen='true' style='width: 100%;'></iframe>",
                    height: 300,
                    width: 600,
                    id: (groups) => groups.join('/embed/')
                }
            }
        }
    },
    image: {
        class: ImageTool,
        config: {
            endpoints: {
                byFile: 'upload-image.php'
            }
        }
    },
    quote: {
        class: Quote,
        inlineToolbar: true
    },
    checklist: {
        class: Checklist,
        inlineToolbar: true
    },
    marker: {
        class: Marker
    },
    delimiter: Delimiter,
    table: {
        class: Table,
        inlineToolbar: true
    }
};
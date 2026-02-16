/**
 * Editor.js Tools Configuration
 * GameDev Academy
 */

const EditorJSTools = {
    header: {
        class: Header,
        inlineToolbar: true,
        config: {
            levels: [2, 3, 4],
            defaultLevel: 2
        }
    },
    paragraph: {
        class: Paragraph,
        inlineToolbar: true
    },
    list: {
        class: List,
        inlineToolbar: true
    },
    checklist: {
        class: Checklist,
        inlineToolbar: true
    },
    quote: {
        class: Quote,
        inlineToolbar: true
    },
    warning: {
        class: Warning,
        inlineToolbar: true
    },
    code: {
        class: CodeTool
    },
    delimiter: Delimiter,
    table: {
        class: Table,
        inlineToolbar: true
    },
    marker: {
        class: Marker
    },
    underline: {
        class: Underline
    },
    image: {
        class: ImageTool,
        config: {
            endpoints: {
                byFile: 'upload-image.php'
            }
        }
    },
    embed: {
        class: Embed,
        config: {
            services: {
                youtube: true,
                vimeo: true,
                codepen: true
            }
        }
    }
};
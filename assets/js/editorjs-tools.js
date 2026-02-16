/**
 * Editor.js Tools Configuration - VERSÃO COMPLETA
 * Recursos avançados de edição de texto
 */

const EditorJSTools = {
    // ========== CABEÇALHOS ==========
    header: {
        class: Header,
        inlineToolbar: ['bold', 'italic', 'link'],
        config: {
            levels: [1, 2, 3, 4, 5, 6],
            defaultLevel: 2
        },
        shortcut: 'CMD+SHIFT+H'
    },

    // ========== PARÁGRAFO (com inline tools) ==========
    paragraph: {
        class: Paragraph,
        inlineToolbar: true
    },

    // ========== LISTAS ==========
    list: {
        class: List,
        inlineToolbar: true,
        config: {
            defaultStyle: 'unordered'
        },
        shortcut: 'CMD+SHIFT+L'
    },

    nestedList: {
        class: NestedList,
        inlineToolbar: true,
        shortcut: 'CMD+SHIFT+N'
    },

    // ========== CHECKLIST / TO-DO ==========
    checklist: {
        class: Checklist,
        inlineToolbar: true,
        shortcut: 'CMD+SHIFT+C'
    },

    // ========== TABELAS ==========
    table: {
        class: Table,
        inlineToolbar: true,
        config: {
            rows: 2,
            cols: 3,
            withHeadings: true
        },
        shortcut: 'CMD+ALT+T'
    },

    // ========== CÓDIGO ==========
    code: {
        class: CodeTool,
        config: {
            placeholder: 'Digite seu código aqui...'
        },
        shortcut: 'CMD+SHIFT+D'
    },

    // ========== CITAÇÕES ==========
    quote: {
        class: Quote,
        inlineToolbar: true,
        config: {
            quotePlaceholder: 'Insira a citação',
            captionPlaceholder: 'Autor da citação'
        },
        shortcut: 'CMD+SHIFT+Q'
    },

    // ========== AVISOS / ALERTAS ==========
    warning: {
        class: Warning,
        inlineToolbar: true,
        config: {
            titlePlaceholder: 'Título do aviso',
            messagePlaceholder: 'Mensagem do aviso'
        },
        shortcut: 'CMD+SHIFT+W'
    },

    alert: {
        class: Alert,
        inlineToolbar: true,
        config: {
            defaultType: 'primary',
            messagePlaceholder: 'Digite sua mensagem'
        }
    },

    // ========== DELIMITADORES ==========
    delimiter: Delimiter,

    // ========== TEXTO INLINE ==========
    // Negrito, Itálico, Sublinhado, etc.
    bold: {
        class: InlineCode // Ativa automaticamente com Ctrl+B
    },

    italic: {
        class: InlineCode // Ativa automaticamente com Ctrl+I
    },

    underline: Underline,

    strikethrough: Strikethrough,

    // ========== MARCADOR DE TEXTO ==========
    marker: {
        class: Marker,
        shortcut: 'CMD+SHIFT+M'
    },

    // ========== COR DO TEXTO ==========
    textColor: {
        class: ColorPlugin,
        config: {
            colorCollections: [
                '#FF1300', '#EC7878', '#9C27B0', '#673AB7',
                '#3F51B5', '#0070FF', '#03A9F4', '#00BCD4',
                '#4CAF50', '#8BC34A', '#CDDC39', '#FFF'
            ],
            defaultColor: '#000000',
            type: 'text'
        }
    },

    backgroundColor: {
        class: ColorPlugin,
        config: {
            colorCollections: [
                '#FFE5B4', '#FFEBCD', '#FFDAB9', '#FFE4C4',
                '#D3D3D3', '#E0E0E0', '#F5F5F5', '#FFFFFF'
            ],
            defaultColor: '#FFFFFF',
            type: 'background'
        }
    },

    // ========== ALINHAMENTO ==========
    alignment: {
        class: AlignmentTuneTool,
        config: {
            default: 'left',
            blocks: {
                header: 'center',
                list: 'left'
            }
        }
    },

    // ========== IMAGENS ==========
    image: {
        class: ImageTool,
        config: {
            endpoints: {
                byFile: 'upload-image.php',
                byUrl: 'fetch-url-image.php'
            },
            field: 'image',
            types: 'image/*',
            captionPlaceholder: 'Legenda da imagem',
            buttonContent: 'Selecionar imagem',
            uploader: {
                uploadByFile(file) {
                    const formData = new FormData();
                    formData.append('image', file);

                    return fetch('upload-image.php', {
                        method: 'POST',
                        body: formData
                    }).then(res => res.json());
                }
            },
            additionalRequestHeaders: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }
    },

    // ========== GALERIA DE IMAGENS ==========
    gallery: {
        class: ImageGallery,
        config: {
            endpoints: {
                byFile: 'upload-image.php'
            }
        }
    },

    // ========== ANEXOS / ARQUIVOS ==========
    attaches: {
        class: AttachesTool,
        config: {
            endpoint: 'upload-file.php',
            field: 'file',
            types: '*',
            buttonText: 'Selecionar arquivo',
            errorMessage: 'Erro ao fazer upload'
        }
    },

    // ========== EMBEDS (YouTube, Vimeo, etc.) ==========
    embed: {
        class: Embed,
        config: {
            services: {
                youtube: true,
                vimeo: true,
                twitter: true,
                instagram: true,
                facebook: true,
                codepen: {
                    regex: /https?:\/\/codepen\.io\/([^\/\?\&]*)\/pen\/([^\/\?\&]*)/,
                    embedUrl: 'https://codepen.io/<%= remote_id %>?height=300&theme-id=0&default-tab=result&embed-version=2',
                    html: "<iframe height='300' scrolling='no' frameborder='no' allowtransparency='true' allowfullscreen='true' style='width: 100%;'></iframe>",
                    height: 300,
                    width: 600,
                    id: (groups) => groups.join('/embed/')
                },
                github: {
                    regex: /https?:\/\/gist\.github\.com\/([^\/]+)\/([^\/]+)/,
                    embedUrl: 'https://gist.github.com/<%= remote_id %>.js',
                    html: "<script src='https://gist.github.com/<%= remote_id %>.js'></script>",
                    height: 300,
                    width: 600
                }
            }
        },
        shortcut: 'CMD+SHIFT+E'
    },

    // ========== LINKS ==========
    linkTool: {
        class: LinkTool,
        config: {
            endpoint: 'fetch-link-metadata.php'
        }
    },

    // ========== SEPARADORES / ESPAÇAMENTOS ==========
    raw: RawTool,

    // ========== EQUAÇÕES MATEMÁTICAS ==========
    math: {
        class: MathTex,
        inlineToolbar: true,
        config: {
            placeholder: 'Digite a equação LaTeX'
        }
    },

    // ========== BOTÕES / CALL TO ACTION ==========
    button: {
        class: Button,
        inlineToolbar: false,
        config: {
            css: {
                btnColor: 'btn--primary'
            }
        }
    },

    // ========== PERSONALIDADE (Emojis) ==========
    personality: {
        class: Personality,
        config: {
            data: [
                {
                    name: 'happy',
                    icon: '😊'
                },
                {
                    name: 'sad',
                    icon: '😢'
                },
                {
                    name: 'angry',
                    icon: '😠'
                }
            ]
        }
    },

    // ========== TOOLTIP / DICA ==========
    tooltip: {
        class: Tooltip
    },

    // ========== LAYOUT EM COLUNAS ==========
    columns: {
        class: EditorJSColumns,
        config: {
            EditorJsLibrary: EditorJS,
            tools: {
                header: Header,
                list: List,
                paragraph: Paragraph
            }
        }
    },

    // ========== ÍNDICE / TABLE OF CONTENTS ==========
    tableOfContents: {
        class: TOC,
        config: {
            placeholder: 'Índice será gerado automaticamente'
        }
    },

    // ========== ÂNCORAS / HYPERLINKS ==========
    anchor: {
        class: AnchorTool,
        config: {
            shortcut: 'CMD+SHIFT+A'
        }
    },

    // ========== NOTAS DE RODAPÉ ==========
    footnotes: {
        class: Footnotes,
        inlineToolbar: true
    },

    // ========== QUEBRA DE PÁGINA ==========
    pageBreak: {
        class: PageBreak
    },

    // ========== INDENTAÇÃO ==========
    indent: {
        class: Indent,
        config: {
            indentSize: 20,
            maxIndent: 5
        }
    }
};
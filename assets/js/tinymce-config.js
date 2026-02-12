// assets/js/tinymce-config.js
function initTinyMCE(selector, height = 400) {
    tinymce.init({
        selector: selector,
        height: height,
        language: 'pt_BR',
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount', 'codesample'
        ],
        toolbar: 'undo redo | blocks | ' +
            'bold italic forecolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'removeformat | codesample code | help',
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px }',
        
        // Configurações para upload de imagem
        images_upload_url: '../includes/upload-handler.php',
        automatic_uploads: true,
        images_upload_base_path: '../uploads/content/',
        images_upload_credentials: true,
        
        // Configuração do codesample para desenvolvimento de jogos
        codesample_languages: [
            { text: 'HTML/XML', value: 'markup' },
            { text: 'JavaScript', value: 'javascript' },
            { text: 'CSS', value: 'css' },
            { text: 'PHP', value: 'php' },
            { text: 'C#', value: 'csharp' },
            { text: 'C++', value: 'cpp' },
            { text: 'Python', value: 'python' },
            { text: 'Java', value: 'java' },
            { text: 'JSON', value: 'json' }
        ],
        
        // Configurações avançadas
        menubar: 'file edit view insert format tools table help',
        branding: false,
        promotion: false,
        
        // Validação de conteúdo
        valid_elements: '*[*]',
        extended_valid_elements: 'script[src|async|defer|type|charset]',
        
        // Setup callback
        setup: function(editor) {
            editor.on('change', function() {
                tinymce.triggerSave();
            });
        }
    });
}
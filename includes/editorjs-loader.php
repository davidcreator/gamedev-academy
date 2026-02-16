<?php
/**
 * Editor.js Loader
 * Carrega scripts e prepara dados para o Editor.js
 */

class EditorJSLoader {
    private $content;
    private $editorData;

    public function __construct($content = '') {
        $this->content = trim($content ?? '');
        $this->prepareData();
    }

    /**
     * Prepara os dados para o Editor.js
     */
    private function prepareData() {
        $defaultData = [
            'time' => time(),
            'blocks' => [],
            'version' => '2.28.0'
        ];

        if (empty($this->content)) {
            $this->editorData = $defaultData;
            return;
        }

        $decoded = json_decode($this->content, true);
        
        if (json_last_error() === JSON_ERROR_NONE && 
            isset($decoded['blocks']) && 
            is_array($decoded['blocks'])) {
            $this->editorData = $decoded;
        } else {
            // Converte texto antigo para formato Editor.js
            $this->editorData = [
                'time' => time(),
                'blocks' => [
                    [
                        'type' => 'paragraph',
                        'data' => [
                            'text' => htmlspecialchars($this->content, ENT_QUOTES, 'UTF-8')
                        ]
                    ]
                ],
                'version' => '2.28.0'
            ];
        }
    }

    /**
     * Retorna os dados preparados
     */
    public function getData() {
        return $this->editorData;
    }

    /**
     * Retorna JSON seguro para JavaScript
     */
    public function getJsonData() {
        return json_encode(
            $this->editorData, 
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * Renderiza os links dos scripts CDN
     */
    public static function renderScripts() {
        ?>
<!-- Editor.js Core -->
<script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@2.28.2"></script>

<!-- Editor.js Tools -->
<script src="https://cdn.jsdelivr.net/npm/@editorjs/header@2.8.1"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/paragraph@2.11.3"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/list@1.9.0"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/checklist@1.6.0"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/quote@2.6.0"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/code@2.9.0"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/delimiter@1.4.0"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/table@2.3.0"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/warning@1.4.0"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/marker@1.4.0"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/underline@1.1.0"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/image@2.9.0"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/embed@2.7.0"></script>

<!-- Editor Tools -->
<script src="../assets/js/editorjs-tools.js"></script>
        <?php
    }

    /**
     * Renderiza o CSS
     */
    public static function renderStyles() {
        echo '<link rel="stylesheet" href="../assets/css/editorjs-custom.css">' . PHP_EOL;
    }

    /**
     * Inicializa o editor
     */
    public static function init($editorData, $holderId = 'editorjs', $textareaId = 'content', $formId = 'lessonForm') {
        ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new EditorTools({
        holderId: '<?= $holderId ?>',
        textareaId: '<?= $textareaId ?>',
        formId: '<?= $formId ?>',
        data: <?= $editorData ?>
    });
});
</script>
        <?php
    }
}
?>
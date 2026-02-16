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
<script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest"></script>

<!-- Editor.js Tools -->
<script src="https://cdn.jsdelivr.net/npm/@editorjs/header@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/list@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/code@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/embed@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/image@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/quote@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/checklist@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/marker@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/delimiter@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/table@latest"></script>

<!-- Editor.js Config -->
<script src="../../assets/js/editorjs-tools.js"></script>
<script src="../../assets/js/editorjs-init.js"></script>
        <?php
    }

    /**
     * Renderiza o CSS
     */
    public static function renderStyles() {
        echo '<link rel="stylesheet" href="../../assets/css/editorjs-custom.css">';
    }
}
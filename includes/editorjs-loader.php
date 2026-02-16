<?php
/**
 * Editor.js Loader
 * Localização: gamedev-academy/includes/editorjs-loader.php
 */

class EditorJSLoader {
    private $content;
    private $editorData;

    public function __construct($content = '') {
        $this->content = trim($content ?? '');
        $this->prepareData();
    }

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

        if (
            json_last_error() === JSON_ERROR_NONE &&
            isset($decoded['blocks']) &&
            is_array($decoded['blocks'])
        ) {
            $this->editorData = $decoded;
        } else {
            $cleanContent = strip_tags($this->content);
            $cleanContent = html_entity_decode($cleanContent, ENT_QUOTES, 'UTF-8');
            $cleanContent = trim($cleanContent);

            if (empty($cleanContent)) {
                $this->editorData = $defaultData;
            } else {
                $this->editorData = [
                    'time' => time(),
                    'blocks' => [
                        [
                            'type' => 'paragraph',
                            'data' => [
                                'text' => $cleanContent
                            ]
                        ]
                    ],
                    'version' => '2.28.0'
                ];
            }
        }
    }

    public function getData() {
        return $this->editorData;
    }

    public function getJsonData() {
        $json = json_encode(
            $this->editorData,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
        );
        
        // Fallback se json_encode falhar
        if ($json === false) {
            $json = json_encode([
                'time' => time(),
                'blocks' => [],
                'version' => '2.28.0'
            ]);
        }
        
        return $json;
    }

    public static function renderStyles() {
        echo '<link rel="stylesheet" href="/gamedev-academy/assets/css/editorjs-custom.css">' . PHP_EOL;
    }

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

<!-- Editor.js Custom -->
<script src="/gamedev-academy/assets/js/editorjs-tools.js"></script>
<script src="/gamedev-academy/assets/js/editorjs-init.js"></script>
        <?php
    }

    public static function init($data, $formId = 'lessonForm') {
        $safeJson = is_string($data) ? $data : json_encode($data);
        ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var editorData;
    try {
        editorData = JSON.parse('<?= addslashes($safeJson) ?>');
    } catch(e) {
        console.warn('Dados do editor inválidos, usando vazio:', e);
        editorData = { time: Date.now(), blocks: [], version: '2.28.0' };
    }

    if (typeof EditorJSManager !== 'undefined') {
        new EditorJSManager({
            holderId: 'editorjs',
            textareaId: 'content',
            formId: '<?= $formId ?>',
            data: editorData
        });
    } else {
        console.error('EditorJSManager não carregado');
    }
});
</script>
        <?php
    }
}
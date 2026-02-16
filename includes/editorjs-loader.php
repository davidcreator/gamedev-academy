<?php
/**
 * Editor.js Loader
 * GameDev Academy
 * 
 * Localização: gamedev-academy/includes/editorjs-loader.php
 * 
 * Uso:
 * require_once __DIR__ . '/../includes/editorjs-loader.php';
 * $loader = new EditorJSLoader($content);
 * EditorJSLoader::renderStyles();
 * EditorJSLoader::renderScripts();
 * EditorJSLoader::init($loader->getJsonData());
 */

class EditorJSLoader {
    private $content;
    private $editorData;
    private static $basePath = '/gamedev-academy'; // ← Altere se o projeto mudar de pasta

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
     * Define o caminho base do projeto
     * Útil se o projeto mudar de pasta
     */
    public static function setBasePath($path) {
        self::$basePath = rtrim($path, '/');
    }

    /**
     * Retorna URL completa para assets
     */
    private static function getAssetUrl($path) {
        return self::$basePath . '/' . ltrim($path, '/');
    }

    /**
     * Renderiza os estilos CSS do Editor.js
     */
    public static function renderStyles() {
        $cssUrl = self::getAssetUrl('assets/css/editorjs-custom.css');
        echo '<link rel="stylesheet" href="' . $cssUrl . '">' . PHP_EOL;
    }

    /**
     * Renderiza os scripts do Editor.js
     */
    public static function renderScripts() {
        $jsToolsUrl = self::getAssetUrl('assets/js/editorjs-tools.js');
        $jsInitUrl = self::getAssetUrl('assets/js/editorjs-init.js');
        ?>
<!-- Editor.js Core -->
<script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@2.28.2"></script>

<!-- Editor.js Tools (CDN) -->
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

<!-- Editor.js Custom Config -->
<script src="<?= $jsToolsUrl ?>"></script>
<script src="<?= $jsInitUrl ?>"></script>
        <?php
    }

    /**
     * Inicializa o Editor.js
     * 
     * @param string $editorData JSON dos dados do editor
     * @param string $holderId ID do elemento HTML que receberá o editor
     * @param string $textareaId ID do textarea que armazenará o JSON
     * @param string $formId ID do formulário
     */
    public static function init($editorData, $holderId = 'editorjs', $textareaId = 'content', $formId = 'lessonForm') {
        ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Inicializando Editor.js...');
    
    // Verifica se EditorJSManager foi carregado
    if (typeof EditorJSManager === 'undefined') {
        console.error('❌ EditorJSManager não foi carregado!');
        document.getElementById('<?= $holderId ?>').innerHTML = 
            '<div class="alert alert-danger">Erro ao carregar o editor. Verifique a conexão.</div>';
        return;
    }
    
    new EditorJSManager({
        holderId: '<?= $holderId ?>',
        textareaId: '<?= $textareaId ?>',
        formId: '<?= $formId ?>',
        data: <?= $editorData ?>
    });
});
</script>
        <?php
    }

    /**
     * Renderiza tudo (estilos + scripts + init) de uma vez
     * Útil para simplificar o uso
     */
    public static function renderAll($editorData, $holderId = 'editorjs', $textareaId = 'content', $formId = 'lessonForm') {
        self::renderStyles();
        self::renderScripts();
        self::init($editorData, $holderId, $textareaId, $formId);
    }
}
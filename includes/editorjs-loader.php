<?php
/**
 * EditorJS Loader
 * Classe para gerenciar o carregamento e inicialização do EditorJS
 * 
 * Uso:
 * 1. No início da página: EditorJSLoader::renderStyles();
 * 2. Antes do </body>: EditorJSLoader::renderScripts();
 * 3. Para inicializar: EditorJSLoader::init($existingContent);
 */

class EditorJSLoader {
    
    /**
     * Versões dos plugins (facilita atualização centralizada)
     */
    private static $versions = [
        'editorjs' => '2.28.2',
        'header' => '2.7.0',
        'list' => '1.8.0',
        'checklist' => '1.5.0',
        'quote' => '2.5.0',
        'code' => '2.8.0',
        'delimiter' => '1.3.0',
        'table' => '2.2.2',
        'warning' => '1.3.0',
        'image' => '2.8.1',
        'embed' => '2.5.3',
        'inline-code' => '1.4.0',
        'marker' => '1.3.0',
        'underline' => '1.1.0',
        'raw' => '2.4.0',
    ];

    /**
     * Plugins habilitados (configurável)
     */
    private static $enabledPlugins = [
        'header',
        'list',
        'checklist',
        'quote',
        'code',
        'delimiter',
        'table',
        'warning',
        'image',
        'embed',
        'inline-code',
        'marker',
        'underline',
        'raw',
    ];

    /**
     * Renderiza os estilos CSS necessários
     */
    public static function renderStyles() {
        $baseUrl = self::getBaseUrl();
        echo <<<HTML
        <!-- EditorJS Styles -->
        <link rel="stylesheet" href="{$baseUrl}/assets/css/editorjs-custom.css">
        
HTML;
    }

    /**
     * Renderiza os scripts necessários
     */
    public static function renderScripts() {
        $baseUrl = self::getBaseUrl();
        $versions = self::$versions;
        
        echo <<<HTML
        <!-- EditorJS Core -->
        <script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@{$versions['editorjs']}"></script>
        
HTML;

        // Carregar plugins habilitados
        foreach (self::$enabledPlugins as $plugin) {
            if (isset($versions[$plugin])) {
                $pluginUrl = self::getPluginUrl($plugin, $versions[$plugin]);
                echo "        <script src=\"{$pluginUrl}\"></script>\n";
            }
        }

        echo <<<HTML
        
        <!-- EditorJS Initialization Script -->
        <script src="{$baseUrl}/assets/js/editorjs-init.js"></script>
        
HTML;
    }

    /**
     * Inicializa o EditorJS com conteúdo existente (se houver)
     * 
     * @param string $content Conteúdo JSON do EditorJS
     * @param string $holderId ID do elemento que receberá o editor (padrão: editorjs)
     * @param string $outputId ID do textarea que receberá o JSON (padrão: content)
     */
    public static function init($content = '', $holderId = 'editorjs', $outputId = 'content') {
        // Escapa o conteúdo para JavaScript
        $contentJson = !empty($content) ? $content : '{}';
        $contentEscaped = htmlspecialchars($contentJson, ENT_QUOTES, 'UTF-8');
        
        echo <<<HTML
        <script>
            // Inicializar EditorJS quando o DOM estiver pronto
            document.addEventListener('DOMContentLoaded', function() {
                let existingContent = '{$contentEscaped}';
                
                // Parse do conteúdo existente
                let parsedContent = null;
                if (existingContent && existingContent !== '{}') {
                    try {
                        parsedContent = JSON.parse(existingContent);
                    } catch(e) {
                        console.error('Erro ao fazer parse do conteúdo:', e);
                        parsedContent = null;
                    }
                }
                
                // Inicializar editor
                initializeEditorJS('{$holderId}', '{$outputId}', parsedContent);
            });
        </script>
        
HTML;
    }

    /**
     * Retorna a URL base do projeto
     */
    private static function getBaseUrl() {
        // Se a função url() existir (helper do projeto)
        if (function_exists('url')) {
            return rtrim(url(''), '/');
        }
        
        // Fallback: detectar automaticamente
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        
        // Remove /admin ou outros subdiretórios para chegar na raiz
        $basePath = preg_replace('#/(admin|public|includes).*$#', '', $scriptName);
        
        return $protocol . '://' . $host . $basePath;
    }

    /**
     * Retorna a URL do CDN para um plugin específico
     */
    private static function getPluginUrl($plugin, $version) {
        $pluginMap = [
            'header' => '@editorjs/header',
            'list' => '@editorjs/list',
            'checklist' => '@editorjs/checklist',
            'quote' => '@editorjs/quote',
            'code' => '@editorjs/code',
            'delimiter' => '@editorjs/delimiter',
            'table' => '@editorjs/table',
            'warning' => '@editorjs/warning',
            'image' => '@editorjs/image',
            'embed' => '@editorjs/embed',
            'inline-code' => '@editorjs/inline-code',
            'marker' => '@editorjs/marker',
            'underline' => '@editorjs/underline',
            'raw' => '@editorjs/raw',
        ];

        $packageName = $pluginMap[$plugin] ?? "@editorjs/{$plugin}";
        return "https://cdn.jsdelivr.net/npm/{$packageName}@{$version}";
    }

    /**
     * Habilita ou desabilita um plugin
     */
    public static function enablePlugin($plugin) {
        if (!in_array($plugin, self::$enabledPlugins)) {
            self::$enabledPlugins[] = $plugin;
        }
    }

    public static function disablePlugin($plugin) {
        self::$enabledPlugins = array_filter(self::$enabledPlugins, function($p) use ($plugin) {
            return $p !== $plugin;
        });
    }

    /**
     * Retorna lista de plugins habilitados
     */
    public static function getEnabledPlugins() {
        return self::$enabledPlugins;
    }

    /**
     * Converte conteúdo HTML para formato EditorJS (básico)
     * Útil para migração de conteúdo antigo
     */
    public static function htmlToEditorJS($html) {
        $blocks = [];
        
        // Implementação básica - pode ser expandida
        if (!empty($html)) {
            $blocks[] = [
                'type' => 'paragraph',
                'data' => [
                    'text' => $html
                ]
            ];
        }
        
        return json_encode([
            'time' => time() * 1000,
            'blocks' => $blocks,
            'version' => self::$versions['editorjs']
        ]);
    }

    /**
     * Converte conteúdo EditorJS para HTML (renderização)
     */
    public static function editorJSToHtml($jsonContent) {
        if (empty($jsonContent)) {
            return '';
        }

        $data = json_decode($jsonContent, true);
        if (!isset($data['blocks'])) {
            return '';
        }

        $html = '';
        foreach ($data['blocks'] as $block) {
            $html .= self::renderBlock($block);
        }

        return $html;
    }

    /**
     * Renderiza um bloco individual do EditorJS
     */
    private static function renderBlock($block) {
        $type = $block['type'] ?? 'paragraph';
        $data = $block['data'] ?? [];

        switch ($type) {
            case 'header':
                $level = $data['level'] ?? 2;
                $text = $data['text'] ?? '';
                return "<h{$level}>{$text}</h{$level}>\n";

            case 'paragraph':
                $text = $data['text'] ?? '';
                return "<p>{$text}</p>\n";

            case 'list':
                $style = $data['style'] ?? 'unordered';
                $items = $data['items'] ?? [];
                $tag = $style === 'ordered' ? 'ol' : 'ul';
                $html = "<{$tag}>\n";
                foreach ($items as $item) {
                    $html .= "  <li>{$item}</li>\n";
                }
                $html .= "</{$tag}>\n";
                return $html;

            case 'quote':
                $text = $data['text'] ?? '';
                $caption = $data['caption'] ?? '';
                $html = "<blockquote>{$text}";
                if ($caption) {
                    $html .= "<cite>{$caption}</cite>";
                }
                $html .= "</blockquote>\n";
                return $html;

            case 'code':
                $code = $data['code'] ?? '';
                return "<pre><code>{$code}</code></pre>\n";

            case 'delimiter':
                return "<hr>\n";

            case 'image':
                $url = $data['file']['url'] ?? '';
                $caption = $data['caption'] ?? '';
                $html = "<figure>";
                $html .= "<img src=\"{$url}\" alt=\"{$caption}\">";
                if ($caption) {
                    $html .= "<figcaption>{$caption}</figcaption>";
                }
                $html .= "</figure>\n";
                return $html;

            default:
                return "<!-- Tipo de bloco não suportado: {$type} -->\n";
        }
    }
}
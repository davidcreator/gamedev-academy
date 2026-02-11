<?php
// includes/editor-config.php

/**
 * Configuração do Editor TinyMCE para GameDev Academy
 * Editor WYSIWYG gratuito e opensource
 */

class EditorConfig {
    
    // Usar CDN ou local
    private static $useCDN = true;
    private static $cdnUrl = 'https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js';
    private static $localPath = '/assets/js/tinymce/tinymce.min.js';
    
    /**
     * Retorna a URL do script do editor
     */
    public static function getScriptUrl() {
        if (self::$useCDN) {
            return self::$cdnUrl;
        }
        return BASE_URL . self::$localPath;
    }
    
    /**
     * Configuração padrão para lições (conteúdo educacional)
     */
    public static function getLessonConfig() {
        return [
            'selector' => '#lesson-content',
            'height' => 500,
            'plugins' => [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
                'preview', 'anchor', 'searchreplace', 'visualblocks', 'code',
                'fullscreen', 'insertdatetime', 'media', 'table', 'help',
                'wordcount', 'codesample'
            ],
            'toolbar' => 'undo redo | blocks | bold italic forecolor | ' .
                        'alignleft aligncenter alignright alignjustify | ' .
                        'bullist numlist outdent indent | codesample | ' .
                        'link image media | removeformat | help',
            'codesample_languages' => [
                ['text' => 'C#', 'value' => 'csharp'],
                ['text' => 'JavaScript', 'value' => 'javascript'],
                ['text' => 'Python', 'value' => 'python'],
                ['text' => 'GDScript', 'value' => 'python'],
                ['text' => 'HTML/XML', 'value' => 'markup'],
                ['text' => 'CSS', 'value' => 'css'],
                ['text' => 'PHP', 'value' => 'php']
            ],
            'content_style' => 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 16px; line-height: 1.6; }',
            'language' => 'pt_BR',
            'branding' => false,
            'promotion' => false
        ];
    }
    
    /**
     * Configuração simplificada para descrições curtas
     */
    public static function getSimpleConfig() {
        return [
            'selector' => '.simple-editor',
            'height' => 200,
            'menubar' => false,
            'plugins' => ['lists', 'link', 'autolink'],
            'toolbar' => 'bold italic | bullist numlist | link | removeformat',
            'branding' => false
        ];
    }
    
    /**
     * Gera o JavaScript de inicialização
     */
    public static function renderInitScript($config = 'lesson') {
        $configData = ($config === 'simple') 
            ? self::getSimpleConfig() 
            : self::getLessonConfig();
        
        $jsonConfig = json_encode($configData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        return "<script>
            document.addEventListener('DOMContentLoaded', function() {
                tinymce.init({$jsonConfig});
            });
        </script>";
    }
}
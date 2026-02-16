<?php
// app/Helpers/AssetHelper.php

class AssetHelper
{
    private static $baseUrl;
    
    public static function init()
    {
        self::$baseUrl = rtrim(BASE_URL, '/');
    }
    
    /**
     * Retorna URL do asset baseado no contexto
     * @param string $path - Caminho do arquivo
     * @param string $context - 'admin', 'user' ou 'public'
     */
    public static function url(string $path, string $context = 'public'): string
    {
        $basePath = match($context) {
            'admin' => '/admin/assets/',
            'user'  => '/user/assets/',
            default => '/assets/'
        };
        
        $fullPath = self::$baseUrl . $basePath . ltrim($path, '/');
        
        // Adiciona versioning para cache busting
        $filePath = ROOT_PATH . $basePath . ltrim($path, '/');
        if (file_exists($filePath)) {
            $fullPath .= '?v=' . filemtime($filePath);
        }
        
        return $fullPath;
    }
    
    /**
     * Atalhos para tipos específicos
     */
    public static function css(string $file, string $context = 'public'): string
    {
        return self::url("css/{$file}", $context);
    }
    
    public static function js(string $file, string $context = 'public'): string
    {
        return self::url("js/{$file}", $context);
    }
    
    public static function img(string $file, string $context = 'public'): string
    {
        return self::url("img/{$file}", $context);
    }
    
    /**
     * Gera tag link CSS
     */
    public static function cssTag(string $file, string $context = 'public'): string
    {
        $url = self::css($file, $context);
        return "<link rel=\"stylesheet\" href=\"{$url}\">";
    }
    
    /**
     * Gera tag script JS
     */
    public static function jsTag(string $file, string $context = 'public', bool $defer = true): string
    {
        $url = self::js($file, $context);
        $deferAttr = $defer ? ' defer' : '';
        return "<script src=\"{$url}\"{$deferAttr}></script>";
    }
}

// Função global para facilitar uso nas views
function asset(string $path, string $context = 'public'): string
{
    return AssetHelper::url($path, $context);
}

function asset_css(string $file, string $context = 'public'): string
{
    return AssetHelper::cssTag($file, $context);
}

function asset_js(string $file, string $context = 'public', bool $defer = true): string
{
    return AssetHelper::jsTag($file, $context, $defer);
}
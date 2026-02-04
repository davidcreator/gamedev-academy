<?php
// core/View.php

namespace Core;

use League\Plates\Engine;

class View
{
    private static ?Engine $engine = null;

    private static function getEngine(): Engine
    {
        if (self::$engine === null) {
            self::$engine = new Engine(VIEWS_PATH);
            
            // Registrar funções auxiliares
            self::$engine->registerFunction('url', fn($path = '') => url($path));
            self::$engine->registerFunction('asset', fn($path) => asset($path));
            self::$engine->registerFunction('old', fn($key, $default = '') => old($key, $default));
            self::$engine->registerFunction('csrf', fn() => csrf_field());
            self::$engine->registerFunction('auth', fn() => auth());
            self::$engine->registerFunction('config', fn($key, $default = null) => config($key, $default));
        }
        
        return self::$engine;
    }

    public static function render(string $template, array $data = []): void
    {
        // Adicionar dados globais
        $data['app'] = [
            'name' => env('APP_NAME', 'GameDev Academy'),
            'url' => env('APP_URL', ''),
            'debug' => env('APP_DEBUG', false),
        ];
        
        $data['flash'] = Session::getFlash();
        
        echo self::getEngine()->render($template, $data);
    }

    public static function exists(string $template): bool
    {
        return self::getEngine()->exists($template);
    }

    public static function share(string $key, $value): void
    {
        self::getEngine()->addData([$key => $value]);
    }
}
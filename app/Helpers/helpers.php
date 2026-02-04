<?php
// app/Helpers/helpers.php

use Core\Application;
use Core\Session;
use Core\View;

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
            case 'empty':
            case '(empty)':
                return '';
        }
        
        return $value;
    }
}

if (!function_exists('config')) {
    function config(string $key, $default = null)
    {
        return Application::getInstance()->config($key, $default);
    }
}

if (!function_exists('app')) {
    function app(): Application
    {
        return Application::getInstance();
    }
}

if (!function_exists('db')) {
    function db(): \Core\Database
    {
        return \Core\Database::getInstance();
    }
}

if (!function_exists('view')) {
    function view(string $template, array $data = []): void
    {
        View::render($template, $data);
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $baseUrl = rtrim(env('APP_URL', ''), '/');
        $path = ltrim($path, '/');
        return $path ? "{$baseUrl}/{$path}" : $baseUrl;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }
}

if (!function_exists('back')) {
    function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
        redirect($referer);
    }
}

if (!function_exists('session')) {
    function session(?string $key = null, $default = null)
    {
        if ($key === null) {
            return Session::class;
        }
        return Session::get($key, $default);
    }
}

if (!function_exists('flash')) {
    function flash(string $key, $value = null)
    {
        if ($value !== null) {
            Session::flash($key, $value);
            return null;
        }
        return Session::getFlash($key);
    }
}

if (!function_exists('old')) {
    function old(string $key, $default = '')
    {
        return Session::getOld($key, $default);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Session::csrf();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('method_field')) {
    function method_field(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . strtoupper($method) . '">';
    }
}

if (!function_exists('auth')) {
    function auth(): ?\App\Models\User
    {
        $userId = Session::get('user_id');
        if (!$userId) {
            return null;
        }
        return \App\Models\User::find($userId);
    }
}

if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool
    {
        return Session::has('user_id');
    }
}

if (!function_exists('is_admin')) {
    function is_admin(): bool
    {
        $user = auth();
        return $user && $user->role === 'admin';
    }
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('escape')) {
    function escape(?string $value): string
    {
        return e($value);
    }
}

if (!function_exists('dd')) {
    function dd(...$vars): void
    {
        foreach ($vars as $var) {
            dump($var);
        }
        exit;
    }
}

if (!function_exists('now')) {
    function now(): \Carbon\Carbon
    {
        return \Carbon\Carbon::now();
    }
}

if (!function_exists('slug')) {
    function slug(string $text): string
    {
        $slugify = new \Cocur\Slugify\Slugify();
        return $slugify->slugify($text);
    }
}

if (!function_exists('format_date')) {
    function format_date(string $date, string $format = 'd/m/Y'): string
    {
        return \Carbon\Carbon::parse($date)->format($format);
    }
}

if (!function_exists('time_ago')) {
    function time_ago(string $datetime): string
    {
        return \Carbon\Carbon::parse($datetime)->diffForHumans();
    }
}

if (!function_exists('format_bytes')) {
    function format_bytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        return round($bytes / (1024 ** $pow), $precision) . ' ' . $units[$pow];
    }
}

if (!function_exists('str_limit')) {
    function str_limit(string $value, int $limit = 100, string $end = '...'): string
    {
        if (mb_strlen($value) <= $limit) {
            return $value;
        }
        return mb_substr($value, 0, $limit) . $end;
    }
}

if (!function_exists('generate_uuid')) {
    function generate_uuid(): string
    {
        return \Ramsey\Uuid\Uuid::uuid4()->toString();
    }
}

if (!function_exists('get_avatar')) {
    function get_avatar(?string $avatar): string
    {
        if (empty($avatar) || $avatar === 'default.png') {
            return asset('images/default-avatar.png');
        }
        return url('storage/uploads/avatars/' . $avatar);
    }
}

if (!function_exists('difficulty_badge')) {
    function difficulty_badge(string $difficulty): string
    {
        $badges = [
            'beginner' => '<span class="badge badge-success">Iniciante</span>',
            'intermediate' => '<span class="badge badge-warning">Intermediário</span>',
            'advanced' => '<span class="badge badge-danger">Avançado</span>'
        ];
        return $badges[$difficulty] ?? $badges['beginner'];
    }
}

if (!function_exists('log_info')) {
    function log_info(string $message, array $context = []): void
    {
        app()->getLogger()->info($message, $context);
    }
}

if (!function_exists('log_error')) {
    function log_error(string $message, array $context = []): void
    {
        app()->getLogger()->error($message, $context);
    }
}
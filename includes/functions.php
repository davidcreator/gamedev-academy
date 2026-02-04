<?php
// includes/functions.php

function redirect(string $url): void {
    header("Location: {$url}");
    exit;
}

function asset(string $path): string {
    return SITE_URL . '/assets/' . ltrim($path, '/');
}

function url(string $path = ''): string {
    return SITE_URL . '/' . ltrim($path, '/');
}

function escape(string $string): string {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function old(string $key, string $default = ''): string {
    return escape($_POST[$key] ?? $_GET[$key] ?? $default);
}

function formatDate(string $date, string $format = 'd/m/Y'): string {
    return date($format, strtotime($date));
}

function formatDateTime(string $date): string {
    return date('d/m/Y H:i', strtotime($date));
}

function timeAgo(string $datetime): string {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) {
        return 'agora mesmo';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return "{$mins} min atrás";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return "{$hours}h atrás";
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return "{$days}d atrás";
    } else {
        return formatDate($datetime);
    }
}

function getDifficultyBadge(string $difficulty): string {
    $badges = [
        'beginner' => '<span class="badge badge-success">Iniciante</span>',
        'intermediate' => '<span class="badge badge-warning">Intermediário</span>',
        'advanced' => '<span class="badge badge-danger">Avançado</span>'
    ];
    return $badges[$difficulty] ?? $badges['beginner'];
}

function getDifficultyText(string $difficulty): string {
    $texts = [
        'beginner' => 'Iniciante',
        'intermediate' => 'Intermediário',
        'advanced' => 'Avançado'
    ];
    return $texts[$difficulty] ?? 'Iniciante';
}

function getAvatar(string $avatar): string {
    if (empty($avatar) || $avatar === 'default.png') {
        return asset('images/default-avatar.png');
    }
    return SITE_URL . '/uploads/avatars/' . $avatar;
}

function truncate(string $text, int $length = 100): string {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

function csrf_token(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function flash(string $key, ?string $message = null): ?string {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    
    return null;
}

function showFlashMessages(): string {
    $html = '';
    
    if ($success = flash('success')) {
        $html .= "<div class='alert alert-success'>{$success}</div>";
    }
    
    if ($error = flash('error')) {
        $html .= "<div class='alert alert-danger'>{$error}</div>";
    }
    
    if ($warning = flash('warning')) {
        $html .= "<div class='alert alert-warning'>{$warning}</div>";
    }
    
    return $html;
}
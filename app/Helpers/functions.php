<?php
// app/Helpers/functions.php

function url($path = '')
{
    $base = getenv('APP_URL') ?: 'http://localhost/gamedev-academy';
    $path = ltrim($path, '/');
    return $path ? "{$base}/public/{$path}" : "{$base}/public";
}

function asset($path)
{
    return url($path);
}

function redirect($url)
{
    header("Location: " . url($url));
    exit;
}

function old($key, $default = '')
{
    return $_POST[$key] ?? $default;
}

function escape($string)
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
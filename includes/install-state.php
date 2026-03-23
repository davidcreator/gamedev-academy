<?php
/**
 * Validacao central do estado de instalacao.
 */

if (defined('GAMEDEV_INSTALL_STATE_LOADED')) {
    return;
}

define('GAMEDEV_INSTALL_STATE_LOADED', true);

if (!function_exists('gamedevProjectRoot')) {
    function gamedevProjectRoot(): string
    {
        return dirname(__DIR__);
    }
}

if (!function_exists('gamedevConfigPath')) {
    function gamedevConfigPath(): string
    {
        return gamedevProjectRoot() . DIRECTORY_SEPARATOR . 'config.php';
    }
}

if (!function_exists('gamedevBasePathFromRequest')) {
    function gamedevBasePathFromRequest(): string
    {
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $projectDir = trim(basename(gamedevProjectRoot()), '/');

        if ($projectDir !== '') {
            $needle = '/' . $projectDir;
            $position = strpos($scriptName, $needle);

            if ($position !== false) {
                return substr($scriptName, 0, $position + strlen($needle));
            }
        }

        return '';
    }
}

if (!function_exists('gamedevInstallUrl')) {
    function gamedevInstallUrl(): string
    {
        $basePath = rtrim(gamedevBasePathFromRequest(), '/');

        return ($basePath === '' ? '' : $basePath) . '/install/';
    }
}

if (!function_exists('gamedevLoadRootConfig')) {
    function gamedevLoadRootConfig(): bool
    {
        static $loaded = null;

        if ($loaded !== null) {
            return $loaded;
        }

        $configPath = gamedevConfigPath();
        if (!is_file($configPath) || filesize($configPath) === 0) {
            $loaded = false;
            return false;
        }

        try {
            require_once $configPath;
            $loaded = true;
        } catch (Throwable $exception) {
            error_log('GameDev Academy config.php invalido: ' . $exception->getMessage());
            $loaded = false;
        }

        return $loaded;
    }
}

if (!function_exists('gamedevHasValidInstallConfig')) {
    function gamedevHasValidInstallConfig(): bool
    {
        if (!gamedevLoadRootConfig()) {
            return false;
        }

        $requiredConstants = [
            'DB_HOST',
            'DB_NAME',
            'DB_USER',
            'SITE_URL',
            'SITE_NAME',
        ];

        foreach ($requiredConstants as $constant) {
            if (!defined($constant)) {
                return false;
            }

            $value = constant($constant);
            if (is_string($value) && trim($value) === '') {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('gamedevRedirectToInstaller')) {
    function gamedevRedirectToInstaller(): void
    {
        $currentScript = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        if (strpos($currentScript, '/install/') !== false) {
            return;
        }

        if (!headers_sent()) {
            header('Location: ' . gamedevInstallUrl());
        }

        exit;
    }
}

if (!function_exists('gamedevRequireInstalledSystem')) {
    function gamedevRequireInstalledSystem(): void
    {
        if (!gamedevHasValidInstallConfig()) {
            gamedevRedirectToInstaller();
        }
    }
}

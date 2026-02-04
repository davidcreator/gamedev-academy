<?php
// config/database.php

define('DB_HOST', 'localhost');
define('DB_NAME', 'gamedev_academy');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'GameDev Academy');
define('SITE_URL', 'http://localhost/gamedev-academy');
define('SITE_EMAIL', 'contato@gamedev.com');

// Configurações de sessão
define('SESSION_LIFETIME', 86400 * 7); // 7 dias

// Configurações de upload
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Error reporting (desabilitar em produção)
error_reporting(E_ALL);
ini_set('display_errors', 1);
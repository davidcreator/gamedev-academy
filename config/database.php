<?php
// config/database.php

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'gamedev_academy');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

if (!defined('SITE_NAME')) define('SITE_NAME', 'GameDev Academy');
if (!defined('SITE_URL')) define('SITE_URL', 'http://localhost/gamedev-academy');
if (!defined('SITE_EMAIL')) define('SITE_EMAIL', 'contato@gamedev.com');

if (!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME', 86400 * 7);

if (!defined('UPLOAD_PATH')) define('UPLOAD_PATH', __DIR__ . '/../uploads/');
if (!defined('MAX_UPLOAD_SIZE')) define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024);

date_default_timezone_set('America/Sao_Paulo');

error_reporting(E_ALL);
ini_set('display_errors', 1);

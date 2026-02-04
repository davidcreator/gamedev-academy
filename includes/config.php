<?php
/**
 * Configurações do Banco de Dados
 */

// Configurações do banco
define('DB_HOST', 'localhost');
define('DB_NAME', 'gamedev_academy');
define('DB_USER', 'root');
define('DB_PASS', ''); // Sua senha do MySQL

// Configurações do site
define('SITE_NAME', 'GameDev Academy');
define('SITE_DESCRIPTION', 'Aprenda desenvolvimento de jogos com Phaser e React');

// Configurações de upload
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
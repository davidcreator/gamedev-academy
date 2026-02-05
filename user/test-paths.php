<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Teste de Caminhos</title>
</head>
<body>
    <h1>🔍 Diagnóstico de Caminhos</h1>
    
    <h3>Constantes Definidas:</h3>
    <pre>
ROOT_PATH: <?= defined('ROOT_PATH') ? ROOT_PATH : '❌ NÃO DEFINIDO' ?>

BASE_URL: <?= defined('BASE_URL') ? BASE_URL : '❌ NÃO DEFINIDO' ?>

ASSETS_URL: <?= defined('ASSETS_URL') ? ASSETS_URL : '❌ NÃO DEFINIDO' ?>

SITE_URL: <?= defined('SITE_URL') ? SITE_URL : '❌ NÃO DEFINIDO' ?>
    </pre>
    
    <h3>Funções de URL:</h3>
    <pre>
url(): <?= function_exists('url') ? url() : '❌ FUNÇÃO NÃO EXISTE' ?>

url('test'): <?= function_exists('url') ? url('test') : '❌ FUNÇÃO NÃO EXISTE' ?>

asset('css/style.css'): <?= function_exists('asset') ? asset('css/style.css') : '❌ FUNÇÃO NÃO EXISTE' ?>
    </pre>
    
    <h3>Informações do Servidor:</h3>
    <pre>
DOCUMENT_ROOT: <?= $_SERVER['DOCUMENT_ROOT'] ?>

SCRIPT_FILENAME: <?= $_SERVER['SCRIPT_FILENAME'] ?>

SCRIPT_NAME: <?= $_SERVER['SCRIPT_NAME'] ?>

REQUEST_URI: <?= $_SERVER['REQUEST_URI'] ?>

__DIR__: <?= __DIR__ ?>

dirname(__DIR__): <?= dirname(__DIR__) ?>
    </pre>
    
    <h3>Verificação de Arquivos CSS:</h3>
    <pre>
<?php
$cssFiles = [
    dirname(__DIR__) . '/assets/css/style.css',
    dirname(__DIR__) . '/assets/css/main.css',
    dirname(__DIR__) . '/assets/css/bootstrap.min.css',
];

foreach ($cssFiles as $file) {
    $exists = file_exists($file) ? '✅ EXISTE' : '❌ NÃO EXISTE';
    echo basename($file) . ": $exists\n";
}
?>
    </pre>
    
    <h3>Teste de Carregamento CSS:</h3>
    <?php 
    $cssUrl = function_exists('asset') ? asset('css/style.css') : '/assets/css/style.css';
    ?>
    <link rel="stylesheet" href="<?= $cssUrl ?>">
    <p>URL do CSS: <code><?= $cssUrl ?></code></p>
    <p class="btn btn-primary">Se este botão estiver azul, o Bootstrap carregou!</p>
    
    <h3>Header.php existe?</h3>
    <pre>
<?php
$headerPath = dirname(__DIR__) . '/includes/header.php';
if (file_exists($headerPath)) {
    echo "✅ header.php existe em: $headerPath\n\n";
    echo "Primeiras 50 linhas do header.php:\n";
    echo "================================\n";
    $lines = file($headerPath);
    for ($i = 0; $i < min(50, count($lines)); $i++) {
        echo htmlspecialchars($lines[$i]);
    }
} else {
    echo "❌ header.php NÃO EXISTE em: $headerPath";
}
?>
    </pre>
</body>
</html>
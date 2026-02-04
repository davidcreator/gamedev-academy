<?php
/**
 * Script para corrigir caminhos de inclusão
 * Execute uma vez para verificar todos os arquivos
 */

echo "<h2>🔧 Verificador de Caminhos - GameDev Academy</h2>";

// Arquivos que precisam de correção
$files_to_check = [
    'fix_missing_tables.php',
    'login.php',
    'register.php',
    'profile.php',
    'logout.php'
];

foreach ($files_to_check as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        // Verifica se tem require incorreto
        if (strpos($content, "require_once('init.php')") !== false ||
            strpos($content, 'require_once("init.php")') !== false) {
            echo "❌ <b>$file</b> - Caminho incorreto para init.php<br>";
        } else {
            echo "✅ <b>$file</b> - OK<br>";
        }
    } else {
        echo "⚠️ <b>$file</b> - Arquivo não encontrado<br>";
    }
}

echo "<hr>";
echo "<h3>📋 Correção Manual Necessária:</h3>";
echo "<pre>
// Substitua em TODOS os arquivos da raiz:

// ❌ INCORRETO:
require_once('init.php');
require_once('includes/config.php');

// ✅ CORRETO:
require_once(__DIR__ . '/includes/init.php');
require_once(__DIR__ . '/includes/config.php');
</pre>";
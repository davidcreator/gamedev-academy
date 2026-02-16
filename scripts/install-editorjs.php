<?php
/**
 * Download direto do Editor.js (alternativa ao Composer)
 */

$baseUrl = 'https://cdn.jsdelivr.net/npm/';
$basePath = __DIR__ . '/../assets/js/editorjs/';

// Criar diretórios
@mkdir($basePath, 0755, true);
@mkdir($basePath . 'plugins', 0755, true);

$packages = [
    [
        'url' => '@editorjs/editorjs@latest/dist/editorjs.umd.js',
        'file' => 'editor.js'
    ],
    [
        'url' => '@editorjs/header@latest/dist/bundle.js',
        'file' => 'plugins/header.js'
    ],
    [
        'url' => '@editorjs/paragraph@latest/dist/bundle.js',
        'file' => 'plugins/paragraph.js'
    ],
    [
        'url' => '@editorjs/code@latest/dist/bundle.js',
        'file' => 'plugins/code.js'
    ],
    [
        'url' => '@editorjs/list@latest/dist/bundle.js',
        'file' => 'plugins/list.js'
    ],
    [
        'url' => '@editorjs/image@latest/dist/bundle.js',
        'file' => 'plugins/image.js'
    ],
    [
        'url' => '@editorjs/embed@latest/dist/bundle.js',
        'file' => 'plugins/embed.js'
    ],
    [
        'url' => '@editorjs/table@latest/dist/bundle.js',
        'file' => 'plugins/table.js'
    ],
    [
        'url' => '@editorjs/checklist@latest/dist/bundle.js',
        'file' => 'plugins/checklist.js'
    ]
];

echo "🚀 Baixando Editor.js...\n\n";

foreach ($packages as $package) {
    echo "📦 " . basename($package['file']) . "... ";
    $content = @file_get_contents($baseUrl . $package['url']);
    
    if ($content) {
        file_put_contents($basePath . $package['file'], $content);
        echo "✅\n";
    } else {
        echo "❌ Erro\n";
    }
}

echo "\n✨ Concluído!\n";
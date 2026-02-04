<?php
// install/steps/step1.php

// Verificar requisitos do sistema
$requirements = [
    'php_version' => [
        'name' => 'Versão do PHP',
        'required' => '7.4.0',
        'current' => PHP_VERSION,
        'pass' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'description' => 'PHP 7.4 ou superior é necessário'
    ],
    'pdo' => [
        'name' => 'Extensão PDO',
        'required' => 'Habilitado',
        'current' => extension_loaded('pdo') ? 'Habilitado' : 'Desabilitado',
        'pass' => extension_loaded('pdo'),
        'description' => 'PDO é necessário para conexão com banco de dados'
    ],
    'pdo_mysql' => [
        'name' => 'PDO MySQL Driver',
        'required' => 'Habilitado',
        'current' => extension_loaded('pdo_mysql') ? 'Habilitado' : 'Desabilitado',
        'pass' => extension_loaded('pdo_mysql'),
        'description' => 'Driver MySQL para PDO'
    ],
    'mbstring' => [
        'name' => 'Extensão Mbstring',
        'required' => 'Habilitado',
        'current' => extension_loaded('mbstring') ? 'Habilitado' : 'Desabilitado',
        'pass' => extension_loaded('mbstring'),
        'description' => 'Necessário para manipulação de strings UTF-8'
    ],
    'openssl' => [
        'name' => 'Extensão OpenSSL',
        'required' => 'Habilitado',
        'current' => extension_loaded('openssl') ? 'Habilitado' : 'Desabilitado',
        'pass' => extension_loaded('openssl'),
        'description' => 'Necessário para criptografia'
    ],
    'json' => [
        'name' => 'Extensão JSON',
        'required' => 'Habilitado',
        'current' => extension_loaded('json') ? 'Habilitado' : 'Desabilitado',
        'pass' => extension_loaded('json'),
        'description' => 'Necessário para manipulação de dados JSON'
    ],
    'session' => [
        'name' => 'Extensão Session',
        'required' => 'Habilitado',
        'current' => extension_loaded('session') ? 'Habilitado' : 'Desabilitado',
        'pass' => extension_loaded('session'),
        'description' => 'Necessário para gerenciamento de sessões'
    ],
];

// Verificar permissões de diretórios
$directories = [
    '../storage' => 'Diretório Storage',
    '../storage/cache' => 'Diretório Cache',
    '../storage/logs' => 'Diretório Logs',
    '../storage/sessions' => 'Diretório Sessions',
    '../storage/uploads' => 'Diretório Uploads',
    '../public/assets' => 'Diretório Assets'
];

$dirPermissions = [];
foreach ($directories as $path => $name) {
    $fullPath = __DIR__ . '/' . $path;
    $exists = file_exists($fullPath);
    $writable = false;
    
    if (!$exists) {
        // Tentar criar o diretório
        @mkdir($fullPath, 0777, true);
        $exists = file_exists($fullPath);
    }
    
    if ($exists) {
        $writable = is_writable($fullPath);
    }
    
    $dirPermissions[] = [
        'name' => $name,
        'path' => $path,
        'exists' => $exists,
        'writable' => $writable,
        'pass' => $exists && $writable
    ];
}

// Verificar se todos os requisitos foram atendidos
$allPassed = true;
foreach ($requirements as $req) {
    if (!$req['pass']) $allPassed = false;
}
foreach ($dirPermissions as $dir) {
    if (!$dir['pass']) $allPassed = false;
}

$_SESSION['requirements_passed'] = $allPassed;
?>

<h2 class="step-title">📋 Verificação de Requisitos</h2>
<p class="step-description">
    Verificando se seu servidor atende aos requisitos mínimos para executar o GameDev Academy.
</p>

<h4 style="margin-bottom: 1rem; color: var(--white);">🔧 Requisitos do PHP</h4>
<ul class="requirement-list">
    <?php foreach ($requirements as $req): ?>
    <li class="requirement-item">
        <span class="requirement-status <?= $req['pass'] ? 'pass' : 'fail' ?>">
            <?= $req['pass'] ? '✓' : '✗' ?>
        </span>
        <div class="requirement-info">
            <div class="requirement-name"><?= $req['name'] ?></div>
            <div class="requirement-details">
                <span>Requerido: <?= $req['required'] ?></span>
                <span>•</span>
                <span>Atual: <?= $req['current'] ?></span>
            </div>
        </div>
        <span class="requirement-value <?= $req['pass'] ? 'pass' : 'fail' ?>">
            <?= $req['pass'] ? 'OK' : 'ERRO' ?>
        </span>
    </li>
    <?php endforeach; ?>
</ul>

<h4 style="margin: 2rem 0 1rem; color: var(--white);">📁 Permissões de Diretórios</h4>
<ul class="requirement-list">
    <?php foreach ($dirPermissions as $dir): ?>
    <li class="requirement-item">
        <span class="requirement-status <?= $dir['pass'] ? 'pass' : ($dir['exists'] ? 'warning' : 'fail') ?>">
            <?= $dir['pass'] ? '✓' : ($dir['exists'] ? '⚠' : '✗') ?>
        </span>
        <div class="requirement-info">
            <div class="requirement-name"><?= $dir['name'] ?></div>
            <div class="requirement-details">
                <span><?= $dir['path'] ?></span>
                <?php if (!$dir['exists']): ?>
                    <span style="color: var(--danger);">• Não existe</span>
                <?php elseif (!$dir['writable']): ?>
                    <span style="color: var(--warning);">• Sem permissão de escrita</span>
                <?php endif; ?>
            </div>
        </div>
        <span class="requirement-value <?= $dir['pass'] ? 'pass' : 'fail' ?>">
            <?= $dir['pass'] ? 'OK' : 'ERRO' ?>
        </span>
    </li>
    <?php endforeach; ?>
</ul>

<?php if (!$allPassed): ?>
<div class="alert alert-danger" style="margin-top: 2rem;">
    <span class="alert-icon">⚠️</span>
    <div class="alert-content">
        <strong>Atenção!</strong> 
        Alguns requisitos não foram atendidos. Por favor, corrija os problemas antes de continuar.
    </div>
</div>
<?php else: ?>
<div class="alert alert-success" style="margin-top: 2rem;">
    <span class="alert-icon">✅</span>
    <div class="alert-content">
        <strong>Tudo certo!</strong> 
        Todos os requisitos foram atendidos. Você pode continuar com a instalação.
    </div>
</div>
<?php endif; ?>

<script>
// Desabilitar botão se não passou nos requisitos
<?php if (!$allPassed): ?>
document.getElementById('next-btn').disabled = true;
document.getElementById('next-btn').innerHTML = 'Corrija os erros para continuar';
<?php endif; ?>
</script>
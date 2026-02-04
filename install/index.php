<?php
// install/index.php

session_start();

// Verificar se já está instalado
if (file_exists(__DIR__ . '/../storage/installed.lock')) {
    header('Location: ../public/');
    exit;
}

// Passo atual
$step = intval($_GET['step'] ?? 1);
$totalSteps = 5;

// Processar formulários
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/process.php';
}

// Dados da sessão
$config = $_SESSION['install_config'] ?? [];
$errors = $_SESSION['install_errors'] ?? [];
$warnings = $_SESSION['install_warnings'] ?? [];
unset($_SESSION['install_errors']);
unset($_SESSION['install_warnings']);

// Definir timezone temporário
date_default_timezone_set('America/Sao_Paulo');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - GameDev Academy</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/install.css">
</head>
<body>
    <div class="installer">
        <div class="installer-header">
            <div class="installer-logo">🎮</div>
            <h1 class="installer-title">GameDev Academy</h1>
            <p class="installer-subtitle">Assistente de Instalação - v1.0.0</p>
        </div>
        
        <div class="steps">
            <?php
            $stepNames = ['Requisitos', 'Banco de Dados', 'Configuração', 'Admin', 'Finalizar'];
            for ($i = 1; $i <= $totalSteps; $i++):
                $class = $i < $step ? 'completed' : ($i === $step ? 'active' : '');
                $icon = $i < $step ? '✓' : $i;
            ?>
            <div class="step <?= $class ?>">
                <span class="step-number"><?= $icon ?></span>
                <span><?= $stepNames[$i-1] ?></span>
            </div>
            <?php endfor; ?>
        </div>
        
        <form method="POST" action="?step=<?= $step ?>" id="install-form">
            <input type="hidden" name="step" value="<?= $step ?>">
            
            <div class="installer-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <span class="alert-icon">⚠️</span>
                        <div class="alert-content">
                            <strong>Erros encontrados:</strong>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($warnings)): ?>
                    <div class="alert alert-warning">
                        <span class="alert-icon">⚠️</span>
                        <div class="alert-content">
                            <strong>Avisos:</strong>
                            <ul>
                                <?php foreach ($warnings as $warning): ?>
                                    <li><?= htmlspecialchars($warning) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= ($step / $totalSteps) * 100 ?>%"></div>
                </div>
                
                <?php include __DIR__ . "/steps/step{$step}.php"; ?>
            </div>
            
            <div class="installer-footer">
                <?php if ($step > 1 && $step < 5): ?>
                    <a href="?step=<?= $step - 1 ?>" class="btn btn-secondary">
                        ← Voltar
                    </a>
                <?php else: ?>
                    <div></div>
                <?php endif; ?>
                
                <?php if ($step < 5): ?>
                    <button type="submit" class="btn btn-primary" id="next-btn">
                        <?= $step === 4 ? 'Instalar Sistema' : 'Continuar' ?> →
                    </button>
                <?php else: ?>
                    <div class="btn-group">
                        <a href="../public/" class="btn btn-primary">
                            🏠 Acessar Site
                        </a>
                        <a href="../public/admin" class="btn btn-success">
                            ⚙️ Painel Admin
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <script>
    // Adicionar loading ao botão ao enviar
    document.getElementById('install-form')?.addEventListener('submit', function(e) {
        const btn = document.getElementById('next-btn');
        if (btn) {
            btn.innerHTML = '<span class="loading"></span> Processando...';
            btn.disabled = true;
        }
    });
    </script>
</body>
</html>
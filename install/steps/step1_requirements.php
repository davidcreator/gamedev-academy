<?php
/**
 * Step 1 - Verificação de Requisitos
 */

if (!defined('INSTALLER')) {
    die('Acesso negado');
}

// Verificar requisitos
$checker = new RequirementsChecker();
$requirements = $checker->getRequirements();
$errors = $checker->getErrors();
$warnings = $checker->getWarnings();
$summary = $checker->getSummary();
?>

<div class="requirements-check">
    <!-- Resumo -->
    <div class="summary-box mb-4">
        <div class="row">
            <div class="col-md-3 text-center">
                <div class="summary-item <?php echo $summary['passed'] == $summary['total'] ? 'success' : ''; ?>">
                    <i class="fas fa-check-circle fa-2x"></i>
                    <h3><?php echo $summary['passed']; ?></h3>
                    <p>Aprovados</p>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="summary-item <?php echo $summary['warnings'] > 0 ? 'warning' : ''; ?>">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                    <h3><?php echo $summary['warnings']; ?></h3>
                    <p>Avisos</p>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="summary-item <?php echo $summary['errors'] > 0 ? 'error' : ''; ?>">
                    <i class="fas fa-times-circle fa-2x"></i>
                    <h3><?php echo $summary['errors']; ?></h3>
                    <p>Erros</p>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="summary-item">
                    <i class="fas fa-server fa-2x"></i>
                    <h3><?php echo $summary['total']; ?></h3>
                    <p>Total de Verificações</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensagens de Erro -->
    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <h4><i class="fas fa-times-circle"></i> Erros Encontrados</h4>
        <p>Os seguintes erros precisam ser corrigidos antes de continuar:</p>
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
            <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Mensagens de Aviso -->
    <?php if (!empty($warnings)): ?>
    <div class="alert alert-warning">
        <h4><i class="fas fa-exclamation-triangle"></i> Avisos</h4>
        <p>Os seguintes avisos não impedem a instalação, mas podem afetar o funcionamento:</p>
        <ul class="mb-0">
            <?php foreach ($warnings as $warning): ?>
            <li><?php echo htmlspecialchars($warning); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Tabela de Requisitos -->
    <div class="requirements-table">
        <h4 class="mb-3">Detalhes da Verificação</h4>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th width="40%">Requisito</th>
                    <th width="20%">Necessário</th>
                    <th width="20%">Atual</th>
                    <th width="20%" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requirements as $key => $req): ?>
                <tr class="requirement-row <?php echo $req['status']; ?>">
                    <td>
                        <strong><?php echo htmlspecialchars($req['name']); ?></strong>
                        <?php if (isset($req['description'])): ?>
                        <br><small class="text-muted"><?php echo htmlspecialchars($req['description']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge badge-secondary">
                            <?php echo htmlspecialchars($req['required']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-<?php echo $req['status'] === 'success' ? 'success' : ($req['status'] === 'warning' ? 'warning' : 'danger'); ?>">
                            <?php echo htmlspecialchars($req['current']); ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <?php if ($req['status'] === 'success'): ?>
                            <i class="fas fa-check-circle text-success fa-lg"></i>
                        <?php elseif ($req['status'] === 'warning'): ?>
                            <i class="fas fa-exclamation-triangle text-warning fa-lg"></i>
                        <?php else: ?>
                            <i class="fas fa-times-circle text-danger fa-lg"></i>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Informações do Servidor -->
    <div class="server-info mt-4">
        <h4 class="mb-3">Informações do Servidor</h4>
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Sistema Operacional:</strong></td>
                        <td><?php echo PHP_OS; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Servidor Web:</strong></td>
                        <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Não identificado'; ?></td>
                    </tr>
                    <tr>
                        <td><strong>PHP SAPI:</strong></td>
                        <td><?php echo PHP_SAPI; ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Diretório Raiz:</strong></td>
                        <td><code><?php echo ROOT_PATH; ?></code></td>
                    </tr>
                    <tr>
                        <td><strong>Diretório de Instalação:</strong></td>
                        <td><code><?php echo INSTALL_PATH; ?></code></td>
                    </tr>
                    <tr>
                        <td><strong>Timezone:</strong></td>
                        <td><?php echo date_default_timezone_get(); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Botões de Ação -->
    <div class="form-actions mt-4">
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> Verificar Novamente
                </button>
                
                <?php if ($summary['can_continue']): ?>
                <button type="button" class="btn btn-primary" onclick="goToStep(2)">
                    Continuar <i class="fas fa-arrow-right"></i>
                </button>
                <?php else: ?>
                <button type="button" class="btn btn-danger" disabled>
                    <i class="fas fa-times"></i> Corrija os erros para continuar
                </button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
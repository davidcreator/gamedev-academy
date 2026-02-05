<?php
require_once __DIR__ . '/includes/header.php';

$pdo = getDBConnection();
$success_message = '';
$error_message = '';
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    redirect('login.php');
}

// Alterar email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_email'])) {
    try {
        $new_email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $_POST['current_password'] ?? '';

        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $userRow = $stmt->fetch();

        if (!$userRow || !password_verify($password, $userRow['password'])) {
            $error_message = 'Senha atual incorreta.';
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$new_email, $user_id]);
            if ($stmt->fetch()) {
                $error_message = 'Este email já está em uso.';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                if ($stmt->execute([$new_email, $user_id])) {
                    $success_message = 'Email atualizado com sucesso!';
                    $_SESSION['email'] = $new_email;
                }
            }
        }
    } catch (Exception $e) {
        $error_message = 'Erro ao atualizar email.';
    }
}

// Alterar senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    try {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($new_password !== $confirm_password) {
            $error_message = 'As senhas não coincidem.';
        } elseif (strlen($new_password) < 6) {
            $error_message = 'A nova senha deve ter pelo menos 6 caracteres.';
        } else {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $userRow = $stmt->fetch();

            if (!$userRow || !password_verify($current_password, $userRow['password'])) {
                $error_message = 'Senha atual incorreta.';
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($stmt->execute([$hashed_password, $user_id])) {
                    $success_message = 'Senha alterada com sucesso!';
                }
            }
        }
    } catch (Exception $e) {
        $error_message = 'Erro ao alterar senha.';
    }
}

// Preferências
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_preferences'])) {
    $success_message = 'Preferências atualizadas!';
}

// Excluir conta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    try {
        $password = $_POST['delete_password'] ?? '';
        $stmt = $pdo->prepare("SELECT password, avatar FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $userRow = $stmt->fetch();

        if (!$userRow || !password_verify($password, $userRow['password'])) {
            $error_message = 'Senha incorreta. A conta não foi excluída.';
        } else {
            if (!empty($userRow['avatar']) && $userRow['avatar'] !== 'default.png') {
                $avatar_path = __DIR__ . '/uploads/avatars/' . $userRow['avatar'];
                if (file_exists($avatar_path)) {
                    @unlink($avatar_path);
                }
            }
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            session_destroy();
            redirect('index.php?account_deleted=1');
        }
    } catch (Exception $e) {
        $error_message = 'Erro ao excluir conta.';
    }
}

// Dados do usuário
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$pageTitle = 'Configurações';
?>

<main class="main-content">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0"><i class="fas fa-cog text-primary me-2"></i>Configurações</h1>
        </div>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i><?= esc($success_message) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?= esc($error_message) ?>
            </div>
        <?php endif; ?>

        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary" id="tabBtnAccount">Conta</button>
                    <button type="button" class="btn btn-secondary" id="tabBtnSecurity">Segurança</button>
                    <button type="button" class="btn btn-secondary" id="tabBtnPreferences">Preferências</button>
                    <button type="button" class="btn btn-secondary" id="tabBtnDanger">Zona de Perigo</button>
                </div>
            </div>
        </div>

        <div id="tabAccount">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Informações da Conta</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Nome de Usuário</label>
                                <input type="text" value="<?= esc($user['username'] ?? '') ?>" class="form-control" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Membro desde</label>
                                <input type="text" value="<?= formatDate($user['created_at'] ?? '', 'd/m/Y') ?>" class="form-control" disabled>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="mb-3"><i class="fas fa-envelope text-muted me-2"></i>Alterar Email</h6>
                    <form method="POST">
                        <input type="hidden" name="update_email" value="1">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">Novo Email</label>
                                    <input type="email" id="email" name="email" value="<?= esc($user['email'] ?? '') ?>" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="current_password_email" class="form-label">Senha Atual</label>
                                    <input type="password" id="current_password_email" name="current_password" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Atualizar Email</button>
                    </form>
                </div>
            </div>
        </div>

        <div id="tabSecurity" hidden>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Segurança</h5>
                    <form method="POST">
                        <input type="hidden" name="update_password" value="1">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="current_password" class="form-label">Senha Atual</label>
                                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="new_password" class="form-label">Nova Senha</label>
                                    <input type="password" id="new_password" name="new_password" class="form-control" minlength="6" required>
                                    <small class="text-muted">Mínimo 6 caracteres.</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="confirm_password" class="form-label">Confirmar Nova Senha</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" minlength="6" required>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-danger"><i class="fas fa-key me-2"></i>Alterar Senha</button>
                    </form>

                    <hr class="my-4">
                    <h6 class="mb-3"><i class="fas fa-info-circle text-muted me-2"></i>Sessão Atual</h6>
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>IP:</strong> <?= esc($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0') ?><br>
                                    <strong>Último acesso:</strong> <?= date('d/m/Y H:i') ?>
                                </div>
                                <span class="badge badge-success">Ativa</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="tabPreferences" hidden>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Preferências</h5>
                    <form method="POST">
                        <input type="hidden" name="update_preferences" value="1">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-2">Notificações por Email</h6>
                                <div class="form-group">
                                    <label><input type="checkbox" checked> Novos cursos e atualizações</label>
                                </div>
                                <div class="form-group">
                                    <label><input type="checkbox" checked> Conquistas desbloqueadas</label>
                                </div>
                                <div class="form-group">
                                    <label><input type="checkbox"> Newsletter semanal</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-2">Privacidade</h6>
                                <div class="form-group">
                                    <label><input type="checkbox" checked> Mostrar perfil publicamente</label>
                                </div>
                                <div class="form-group">
                                    <label><input type="checkbox" checked> Mostrar conquistas no perfil</label>
                                </div>
                                <div class="form-group">
                                    <label><input type="checkbox"> Aparecer no ranking</label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="form-group">
                            <label class="form-label">Tema</label>
                            <select class="form-control">
                                <option>Claro</option>
                                <option>Escuro</option>
                                <option>Automático (Sistema)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Salvar Preferências</button>
                    </form>
                </div>
            </div>
        </div>

        <div id="tabDanger" hidden>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Zona de Perigo</h5>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6>Exportar Dados</h6>
                            <p class="text-muted">Baixe todos os seus dados em formato JSON</p>
                            <button class="btn btn-secondary"><i class="fas fa-download me-2"></i>Exportar Dados</button>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-danger">Excluir Conta Permanentemente</h6>
                            <p class="text-muted">Atenção: Esta ação é irreversível. Todos os seus dados, progresso, conquistas e certificados serão permanentemente excluídos.</p>
                            <form method="POST">
                                <input type="hidden" name="delete_account" value="1">
                                <div class="form-group">
                                    <label class="form-label">Confirme sua senha</label>
                                    <input type="password" name="delete_password" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-2"></i>Excluir Minha Conta</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    const tabs = {
        account: document.getElementById('tabAccount'),
        security: document.getElementById('tabSecurity'),
        preferences: document.getElementById('tabPreferences'),
        danger: document.getElementById('tabDanger')
    };
    const btns = {
        account: document.getElementById('tabBtnAccount'),
        security: document.getElementById('tabBtnSecurity'),
        preferences: document.getElementById('tabBtnPreferences'),
        danger: document.getElementById('tabBtnDanger')
    };
    function show(name) {
        Object.keys(tabs).forEach(key => {
            tabs[key].hidden = key !== name;
        });
        Object.keys(btns).forEach(key => {
            if (key === name) {
                btns[key].classList.add('btn-primary');
                btns[key].classList.remove('btn-secondary');
            } else {
                btns[key].classList.add('btn-secondary');
                btns[key].classList.remove('btn-primary');
            }
        });
    }
    btns.account.addEventListener('click', () => show('account'));
    btns.security.addEventListener('click', () => show('security'));
    btns.preferences.addEventListener('click', () => show('preferences'));
    btns.danger.addEventListener('click', () => show('danger'));
    show('account');
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

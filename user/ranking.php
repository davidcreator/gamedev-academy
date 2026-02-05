<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$pdo = getDBConnection();
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id, username, full_name, avatar, xp_total, level, is_active FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || (int)($user['is_active'] ?? 0) !== 1) {
    session_destroy();
    redirect('login.php');
}

$userXP = (int)($user['xp_total'] ?? 0);

$rankAlunoStmt = $pdo->prepare("SELECT COUNT(*) + 1 FROM users WHERE role = 'student' AND is_active = 1 AND xp_total > ?");
$rankAlunoStmt->execute([$userXP]);
$rankAluno = (int)$rankAlunoStmt->fetchColumn();

$rankGeralStmt = $pdo->prepare("SELECT COUNT(*) + 1 FROM users WHERE is_active = 1 AND xp_total > ?");
$rankGeralStmt->execute([$userXP]);
$rankGeral = (int)$rankGeralStmt->fetchColumn();

$topAlunosStmt = $pdo->query("SELECT id, username, full_name, avatar, xp_total, level FROM users WHERE role = 'student' AND is_active = 1 ORDER BY xp_total DESC LIMIT 20");
$topAlunos = $topAlunosStmt->fetchAll(PDO::FETCH_ASSOC);

$topGeralStmt = $pdo->query("SELECT id, username, full_name, avatar, xp_total, level FROM users WHERE is_active = 1 ORDER BY xp_total DESC LIMIT 20");
$topGeral = $topGeralStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Ranking';
?>

<main class="main-content">
    <div class="container">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Seu Ranking</h5>
                <div class="d-flex" style="gap:1rem; align-items:center;">
                    <img src="<?= getUserAvatar($user['avatar'] ?? null, $user['username'] ?? '') ?>" alt="Avatar" class="avatar avatar-lg">
                    <div>
                        <div><strong><?= esc($user['full_name'] ?? ($user['username'] ?? '')) ?></strong></div>
                        <div>Nível <?= (int)($user['level'] ?? 1) ?> • <?= number_format($userXP) ?> XP</div>
                        <div class="mt-2">
                            <span class="badge badge-success">Alunos: #<?= $rankAluno ?></span>
                            <span class="badge badge-primary" style="margin-left:0.5rem;">Geral: #<?= $rankGeral ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Ranking de Alunos</h5>
                <?php if (count($topAlunos) === 0): ?>
                    <div class="text-muted">Nenhum aluno encontrado.</div>
                <?php else: ?>
                    <?php $pos = 1; foreach ($topAlunos as $row): ?>
                        <div class="d-flex" style="gap:0.75rem; align-items:center; padding:0.5rem 0; border-bottom:1px solid var(--gray-700);">
                            <div style="width:2rem; text-align:right; font-weight:700;">#<?= $pos ?></div>
                            <img src="<?= getUserAvatar($row['avatar'] ?? null, $row['username'] ?? '') ?>" alt="Avatar" class="avatar">
                            <div style="flex:1;">
                                <div><strong><?= esc($row['full_name'] ?? ($row['username'] ?? '')) ?></strong></div>
                                <div class="text-muted">Nível <?= (int)($row['level'] ?? 1) ?> • <?= number_format((int)($row['xp_total'] ?? 0)) ?> XP</div>
                            </div>
                            <?php if ((int)$row['id'] === (int)$userId): ?>
                                <span class="badge badge-warning">Você</span>
                            <?php endif; ?>
                        </div>
                        <?php $pos++; endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Ranking Geral</h5>
                <?php if (count($topGeral) === 0): ?>
                    <div class="text-muted">Nenhum usuário encontrado.</div>
                <?php else: ?>
                    <?php $pos = 1; foreach ($topGeral as $row): ?>
                        <div class="d-flex" style="gap:0.75rem; align-items:center; padding:0.5rem 0; border-bottom:1px solid var(--gray-700);">
                            <div style="width:2rem; text-align:right; font-weight:700;">#<?= $pos ?></div>
                            <img src="<?= getUserAvatar($row['avatar'] ?? null, $row['username'] ?? '') ?>" alt="Avatar" class="avatar">
                            <div style="flex:1;">
                                <div><strong><?= esc($row['full_name'] ?? ($row['username'] ?? '')) ?></strong></div>
                                <div class="text-muted">Nível <?= (int)($row['level'] ?? 1) ?> • <?= number_format((int)($row['xp_total'] ?? 0)) ?> XP</div>
                            </div>
                            <?php if ((int)$row['id'] === (int)$userId): ?>
                                <span class="badge badge-warning">Você</span>
                            <?php endif; ?>
                        </div>
                        <?php $pos++; endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

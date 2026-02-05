<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$pdo = getDBConnection();
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id, username, full_name, avatar, xp_total, level, role, is_active FROM users WHERE id = ? LIMIT 1");
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

$weekStart = date('Y-m-d', strtotime('monday this week'));
$weeklyStmt = $pdo->prepare("
    SELECT u.id, u.username, u.full_name, u.avatar, u.level, wl.xp_earned 
    FROM weekly_leaderboard wl 
    JOIN users u ON wl.user_id = u.id 
    WHERE wl.week_start = ? 
    ORDER BY wl.xp_earned DESC 
    LIMIT 20
");
$weeklyStmt->execute([$weekStart]);
$topSemanal = $weeklyStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Ranking';
?>

<main class="main-content">
    <div class="container">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Seu Ranking</h5>
                <div>
                    <img src="<?= getUserAvatar($user['avatar'] ?? null, $user['username'] ?? '') ?>" alt="Avatar" class="avatar avatar-lg">
                    <div>
                        <div><strong><?= esc($user['full_name'] ?? ($user['username'] ?? '')) ?></strong></div>
                        <div>Nível <?= (int)($user['level'] ?? 1) ?> • <?= number_format($userXP) ?> XP</div>
                        <div class="mt-2">
                            <span class="badge badge-success">Alunos: #<?= $rankAluno ?></span>
                            <span class="badge badge-primary">Geral: #<?= $rankGeral ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" id="tabButtons">
            <div class="card-body">
                <button type="button" class="btn btn-primary" id="btnGeral">Geral</button>
                <button type="button" class="btn btn-secondary" id="btnAlunos">Alunos</button>
                <button type="button" class="btn btn-secondary" id="btnSemanal">Semanal</button>
            </div>
        </div>

        <div id="contentGeral">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Ranking Geral</h5>
                    <?php if (count($topGeral) === 0): ?>
                        <div class="text-muted">Nenhum usuário encontrado.</div>
                    <?php else: ?>
                        <?php $pos = 1; foreach ($topGeral as $row): ?>
                            <div>
                                <div>#<?= $pos ?></div>
                                <img src="<?= getUserAvatar($row['avatar'] ?? null, $row['username'] ?? '') ?>" alt="Avatar" class="avatar">
                                <div>
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

        <div id="contentAlunos" hidden>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Ranking de Alunos</h5>
                    <?php if (count($topAlunos) === 0): ?>
                        <div class="text-muted">Nenhum aluno encontrado.</div>
                    <?php else: ?>
                        <?php $pos = 1; foreach ($topAlunos as $row): ?>
                            <div>
                                <div>#<?= $pos ?></div>
                                <img src="<?= getUserAvatar($row['avatar'] ?? null, $row['username'] ?? '') ?>" alt="Avatar" class="avatar">
                                <div>
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

        <div id="contentSemanal" hidden>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Ranking Semanal</h5>
                    <?php if (count($topSemanal) === 0): ?>
                        <div class="text-muted">Nenhum dado semanal encontrado.</div>
                    <?php else: ?>
                        <?php $pos = 1; foreach ($topSemanal as $row): ?>
                            <div>
                                <div>#<?= $pos ?></div>
                                <img src="<?= getUserAvatar($row['avatar'] ?? null, $row['username'] ?? '') ?>" alt="Avatar" class="avatar">
                                <div>
                                    <div><strong><?= esc($row['full_name'] ?? ($row['username'] ?? '')) ?></strong></div>
                                    <div class="text-muted">Nível <?= (int)($row['level'] ?? 1) ?> • <?= number_format((int)($row['xp_earned'] ?? 0)) ?> XP na semana</div>
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
    </div>
</main>

<script>
    const btnGeral = document.getElementById('btnGeral');
    const btnAlunos = document.getElementById('btnAlunos');
    const btnSemanal = document.getElementById('btnSemanal');
    const contentGeral = document.getElementById('contentGeral');
    const contentAlunos = document.getElementById('contentAlunos');
    const contentSemanal = document.getElementById('contentSemanal');
    function showTab(name) {
        contentGeral.hidden = name !== 'geral';
        contentAlunos.hidden = name !== 'alunos';
        contentSemanal.hidden = name !== 'semanal';
        btnGeral.classList.toggle('btn-primary', name === 'geral');
        btnGeral.classList.toggle('btn-secondary', name !== 'geral');
        btnAlunos.classList.toggle('btn-primary', name === 'alunos');
        btnAlunos.classList.toggle('btn-secondary', name !== 'alunos');
        btnSemanal.classList.toggle('btn-primary', name === 'semanal');
        btnSemanal.classList.toggle('btn-secondary', name !== 'semanal');
    }
    btnGeral.addEventListener('click', () => showTab('geral'));
    btnAlunos.addEventListener('click', () => showTab('alunos'));
    btnSemanal.addEventListener('click', () => showTab('semanal'));
    showTab('geral');
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

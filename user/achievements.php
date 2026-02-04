<?php
// user/achievements.php - Conquistas

$pageTitle = 'Conquistas';
include 'includes/header.php';

$achievements = $gamification->getUserAchievements($currentUser['id']);
$unlockedCount = count(array_filter($achievements, fn($a) => $a['unlocked_at'] !== null));
$totalCount = count($achievements);
?>

<div class="mb-4">
    <h1>🏆 Conquistas</h1>
    <p class="text-muted">Desbloqueie conquistas e ganhe recompensas especiais!</p>
</div>

<!-- Stats -->
<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon">🏆</div>
        <div class="stat-value"><?= $unlockedCount ?>/<?= $totalCount ?></div>
        <div class="stat-label">Conquistas Desbloqueadas</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">📈</div>
        <div class="stat-value"><?= round(($unlockedCount / max($totalCount, 1)) * 100) ?>%</div>
        <div class="stat-label">Progresso Total</div>
    </div>
</div>

<!-- Achievements Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem;">
    <?php foreach ($achievements as $achievement): ?>
    <div class="achievement-card <?= $achievement['unlocked_at'] ? 'unlocked' : 'locked' ?>">
        <div class="achievement-icon"><?= $achievement['icon'] ?></div>
        <div class="achievement-info">
            <div class="achievement-name">
                <?= escape($achievement['name']) ?>
                <?php if ($achievement['is_secret']): ?>
                    <span class="badge badge-primary">Secreta</span>
                <?php endif; ?>
            </div>
            <div class="achievement-description">
                <?php if ($achievement['unlocked_at'] || !$achievement['is_secret']): ?>
                    <?= escape($achievement['description']) ?>
                <?php else: ?>
                    <i>Conquista secreta - continue jogando para descobrir!</i>
                <?php endif; ?>
            </div>
            <?php if ($achievement['unlocked_at']): ?>
                <div style="margin-top: 0.5rem; font-size: 0.8rem; color: var(--gray-500);">
                    Desbloqueada em <?= formatDate($achievement['unlocked_at']) ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="achievement-reward">
            <?php if ($achievement['xp_reward'] > 0): ?>
                <div>⚡ <?= $achievement['xp_reward'] ?> XP</div>
            <?php endif; ?>
            <?php if ($achievement['coin_reward'] > 0): ?>
                <div>🪙 <?= $achievement['coin_reward'] ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>
<?php
// views/admin/dashboard.php
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-value"><?= number_format($stats['users']) ?></div>
        <div class="stat-label">Usuários</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">📚</div>
        <div class="stat-value"><?= $stats['courses'] ?></div>
        <div class="stat-label">Cursos</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">📰</div>
        <div class="stat-value"><?= $stats['news'] ?></div>
        <div class="stat-label">Notícias</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">⚡</div>
        <div class="stat-value"><?= number_format($stats['xp_total']) ?></div>
        <div class="stat-label">XP Total</div>
    </div>
</div>

<div class="admin-panel">
    <h2>Últimos Usuários</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Tipo</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($latestUsers as $user): ?>
            <tr>
                <td>#<?= $user['id'] ?></td>
                <td><?= $user['full_name'] ?></td>
                <td><?= $user['email'] ?></td>
                <td>
                    <span class="badge badge-<?= $user['role'] === 'admin' ? 'danger' : 'primary' ?>">
                        <?= $user['role'] ?>
                    </span>
                </td>
                <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
// admin/index.php - Dashboard Administrativo

$pageTitle = 'Dashboard';
include 'includes/header.php';

$db = Database::getInstance();
$userModel = new User();
$courseModel = new Course();
$newsModel = new News();

// Estatísticas
$totalUsers = $userModel->count();
$totalCourses = $courseModel->count();
$totalNews = $newsModel->count();

// Novos usuários (últimos 7 dias)
$newUsers = $db->fetch(
    "SELECT COUNT(*) as total FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
)['total'];

// Últimos usuários
$latestUsers = $userModel->getAll(5);

// Cursos mais populares
$popularCourses = $db->fetchAll(
    "SELECT c.*, COUNT(e.id) as enrollments 
     FROM courses c 
     LEFT JOIN enrollments e ON c.id = e.course_id 
     GROUP BY c.id 
     ORDER BY enrollments DESC 
     LIMIT 5"
);

// Atividade recente
$recentActivity = $db->fetchAll(
    "SELECT xh.*, u.username, u.avatar 
     FROM xp_history xh 
     JOIN users u ON xh.user_id = u.id 
     ORDER BY xh.created_at DESC 
     LIMIT 10"
);
?>

<!-- Estatísticas -->
<div class="admin-stats">
    <div class="admin-stat-card">
        <div class="admin-stat-header">
            <div>
                <div class="admin-stat-value"><?= number_format($totalUsers) ?></div>
                <div class="admin-stat-label">Total de Usuários</div>
            </div>
            <div class="admin-stat-icon primary">👥</div>
        </div>
        <div class="admin-stat-change positive">
            +<?= $newUsers ?> esta semana
        </div>
    </div>
    
    <div class="admin-stat-card">
        <div class="admin-stat-header">
            <div>
                <div class="admin-stat-value"><?= $totalCourses ?></div>
                <div class="admin-stat-label">Cursos Publicados</div>
            </div>
            <div class="admin-stat-icon success">📚</div>
        </div>
    </div>
    
    <div class="admin-stat-card">
        <div class="admin-stat-header">
            <div>
                <div class="admin-stat-value"><?= $totalNews ?></div>
                <div class="admin-stat-label">Notícias</div>
            </div>
            <div class="admin-stat-icon warning">📰</div>
        </div>
    </div>
    
    <div class="admin-stat-card">
        <div class="admin-stat-header">
            <div>
                <?php 
                $totalXP = $db->fetch("SELECT SUM(xp_total) as total FROM users")['total'];
                ?>
                <div class="admin-stat-value"><?= number_format($totalXP ?? 0) ?></div>
                <div class="admin-stat-label">XP Total Distribuído</div>
            </div>
            <div class="admin-stat-icon danger">⚡</div>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Últimos Usuários -->
    <div class="admin-table-container">
        <div class="admin-table-header">
            <h3>👥 Últimos Usuários</h3>
            <a href="<?= url('admin/users/users.php') ?>" class="btn btn-sm btn-primary">Ver Todos</a>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Usuário</th>
                    <th>E-mail</th>
                    <th>Nível</th>
                    <th>Cadastro</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($latestUsers as $user): ?>
                <tr>
                    <td>
                        <div class="d-flex align-center gap-2">
                            <img src="<?= getAvatar($user['avatar']) ?>" alt="" class="avatar">
                            <div>
                                <div><?= escape($user['username']) ?></div>
                                <div style="font-size: 0.8rem; color: var(--gray-500);">
                                    <?= escape($user['full_name']) ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td><?= escape($user['email']) ?></td>
                    <td>
                        <span class="badge badge-primary">Nível <?= $user['level'] ?></span>
                    </td>
                    <td><?= formatDate($user['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Cursos Populares -->
    <div class="admin-table-container">
        <div class="admin-table-header">
            <h3>📚 Cursos Populares</h3>
            <a href="<?= url('admin/courses/courses.php') ?>" class="btn btn-sm btn-primary">Ver Todos</a>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Curso</th>
                    <th>Alunos</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($popularCourses as $course): ?>
                <tr>
                    <td>
                        <div>
                            <div><?= escape($course['title']) ?></div>
                            <div style="font-size: 0.8rem; color: var(--gray-500);">
                                <?= getDifficultyText($course['difficulty']) ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-success"><?= $course['enrollments'] ?> alunos</span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Atividade Recente -->
<div class="admin-table-container mt-4">
    <div class="admin-table-header">
        <h3>⚡ Atividade Recente</h3>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Usuário</th>
                <th>Ação</th>
                <th>XP</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentActivity as $activity): ?>
            <tr>
                <td>
                    <div class="d-flex align-center gap-2">
                        <img src="<?= getAvatar($activity['avatar']) ?>" alt="" class="avatar" style="width: 32px; height: 32px;">
                        <?= escape($activity['username']) ?>
                    </div>
                </td>
                <td><?= escape($activity['description'] ?: $activity['action_type']) ?></td>
                <td>
                    <span class="badge badge-warning">+<?= $activity['xp_amount'] ?> XP</span>
                </td>
                <td><?= timeAgo($activity['created_at']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
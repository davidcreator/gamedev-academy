<?php
// user/courses.php - Meus Cursos

$pageTitle = 'Meus Cursos';
include 'includes/header.php';

$courseModel = new Course();
$userCourses = $courseModel->getUserCourses($currentUser['id']);
$activeCourses = array_filter($userCourses, fn($c) => $c['completed_at'] === null);
$completedCourses = array_filter($userCourses, fn($c) => $c['completed_at'] !== null);
?>

<div class="d-flex justify-between align-center mb-4">
    <h1>📚 Meus Cursos</h1>
    <a href="<?= url('courses.php') ?>" class="btn btn-primary">
        Explorar Novos Cursos
    </a>
</div>

<!-- Tabs -->
<div class="mb-3" style="border-bottom: 1px solid var(--gray-700);">
    <button class="btn btn-secondary" onclick="showTab('active')" id="btn-active" style="border-radius: 0; border-bottom: 2px solid var(--primary);">
        Em Andamento (<?= count($activeCourses) ?>)
    </button>
    <button class="btn btn-secondary" onclick="showTab('completed')" id="btn-completed" style="border-radius: 0;">
        Concluídos (<?= count($completedCourses) ?>)
    </button>
</div>

<!-- Cursos em Andamento -->
<div id="tab-active" class="courses-grid">
    <?php if (empty($activeCourses)): ?>
        <div style="grid-column: 1/-1; text-align: center; padding: 3rem;">
            <p style="font-size: 4rem; margin-bottom: 1rem;">📚</p>
            <p style="color: var(--gray-400); margin-bottom: 1rem;">Você ainda não está matriculado em nenhum curso.</p>
            <a href="<?= url('courses.php') ?>" class="btn btn-primary">Explorar Cursos</a>
        </div>
    <?php else: ?>
        <?php foreach ($activeCourses as $course): ?>
        <div class="course-card">
            <a href="<?= url('learn.php?course=' . $course['slug']) ?>">
                <div class="course-thumbnail">
                    <div style="background: var(--gradient-primary); height: 100%; display: flex; align-items: center; justify-content: center; font-size: 4rem;">
                        🎮
                    </div>
                    <div style="position: absolute; top: 1rem; right: 1rem;">
                        <span class="badge badge-primary"><?= round($course['progress_percentage']) ?>%</span>
                    </div>
                </div>
            </a>
            <div class="course-content">
                <div class="course-category"><?= escape($course['category_name'] ?? 'Geral') ?></div>
                <h3 class="course-title"><?= escape($course['title']) ?></h3>
                
                <div class="mb-2">
                    <div class="d-flex justify-between mb-1">
                        <small>Progresso</small>
                        <small><?= round($course['progress_percentage']) ?>%</small>
                    </div>
                    <div class="progress progress-lg">
                        <div class="progress-bar" style="width: <?= $course['progress_percentage'] ?>%"></div>
                    </div>
                </div>
                
                <div class="course-meta">
                    <span>📅 <?= formatDate($course['started_at']) ?></span>
                    <span>🕐 <?= timeAgo($course['last_accessed']) ?></span>
                </div>
                
                <a href="<?= url('learn.php?course=' . $course['slug']) ?>" class="btn btn-primary btn-sm w-100 mt-3">
                    Continuar Aprendendo
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Cursos Concluídos -->
<div id="tab-completed" class="courses-grid" style="display: none;">
    <?php if (empty($completedCourses)): ?>
        <div style="grid-column: 1/-1; text-align: center; padding: 3rem;">
            <p style="font-size: 4rem; margin-bottom: 1rem;">🎯</p>
            <p style="color: var(--gray-400);">Você ainda não concluiu nenhum curso.</p>
        </div>
    <?php else: ?>
        <?php foreach ($completedCourses as $course): ?>
        <div class="course-card">
            <div class="course-thumbnail">
                <div style="background: var(--gradient-secondary); height: 100%; display: flex; align-items: center; justify-content: center; font-size: 4rem;">
                    ✅
                </div>
                <div style="position: absolute; top: 1rem; right: 1rem;">
                    <span class="badge badge-success">Completo</span>
                </div>
            </div>
            <div class="course-content">
                <div class="course-category"><?= escape($course['category_name'] ?? 'Geral') ?></div>
                <h3 class="course-title"><?= escape($course['title']) ?></h3>
                
                <div class="course-meta">
                    <span>✅ <?= formatDate($course['completed_at']) ?></span>
                    <span class="course-xp">⚡ <?= $course['xp_reward'] ?> XP</span>
                </div>
                
                <a href="<?= url('certificate.php?course=' . $course['id']) ?>" class="btn btn-success btn-sm w-100 mt-3">
                    📜 Ver Certificado
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function showTab(tab) {
    document.getElementById('tab-active').style.display = tab === 'active' ? 'grid' : 'none';
    document.getElementById('tab-completed').style.display = tab === 'completed' ? 'grid' : 'none';
    
    document.getElementById('btn-active').style.borderBottom = tab === 'active' ? '2px solid var(--primary)' : 'none';
    document.getElementById('btn-completed').style.borderBottom = tab === 'completed' ? '2px solid var(--primary)' : 'none';
}
</script>

<?php include 'includes/footer.php'; ?>
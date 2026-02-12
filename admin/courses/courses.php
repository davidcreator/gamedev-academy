<?php
// admin/courses.php - Gerenciar Cursos

$pageTitle = 'Gerenciar Cursos';
include '../includes/header.php';

$courseModel = new Course();
$courses = $courseModel->getAll(false); // Incluir não publicados

// Ação de publicar/despublicar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $courseId = intval($_POST['course_id'] ?? 0);
    
    if ($action === 'toggle_publish' && $courseId) {
        $course = $courseModel->find($courseId);
        if ($course) {
            $courseModel->update($courseId, ['is_published' => !$course['is_published']]);
            flash('success', 'Status do curso atualizado!');
            redirect(url('admin/courses/courses.php'));
        }
    }
    
    if ($action === 'delete' && $courseId) {
        if ($courseModel->delete($courseId)) {
            flash('success', 'Curso excluído com sucesso!');
        } else {
            flash('error', 'Erro ao excluir curso.');
        }
        redirect(url('admin/courses/courses.php'));
    }
}
?>

<div class="d-flex justify-between align-center mb-4">
    <div>
        <p class="text-muted">Total de <?= count($courses) ?> cursos</p>
    </div>
    
    <a href="<?= url('admin/courses/course-edit.php') ?>" class="btn btn-primary">
        + Novo Curso
    </a>
</div>

<?= showFlashMessages() ?>

<div class="admin-table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Categoria</th>
                <th>Dificuldade</th>
                <th>Módulos</th>
                <th>Alunos</th>
                <th>Status</th>
                <th>Criado em</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($courses as $course): ?>
            <tr>
                <td>#<?= $course['id'] ?></td>
                <td>
                    <div>
                        <div><?= escape($course['title']) ?></div>
                        <div class="text-muted">
                            <?= escape(truncate($course['description'], 50)) ?>
                        </div>
                    </div>
                </td>
                <td><?= escape($course['category_name'] ?? 'Sem categoria') ?></td>
                <td><?= getDifficultyBadge($course['difficulty']) ?></td>
                <td><?= $course['total_modules'] ?? 0 ?></td>
                <td>
                    <span class="badge badge-primary"><?= $course['total_students'] ?></span>
                </td>
                <td>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="action" value="toggle_publish">
                        <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                        <?php if ($course['is_published']): ?>
                            <button type="submit" class="badge badge-success">
                                Publicado
                            </button>
                        <?php else: ?>
                            <button type="submit" class="badge badge-warning">
                                Rascunho
                            </button>
                        <?php endif; ?>
                    </form>
                </td>
                <td><?= formatDate($course['created_at']) ?></td>
                <td>
                    <div class="admin-actions">
                        <a href="<?= url('admin/courses/course-edit.php?id=' . $course['id']) ?>" 
                           class="btn-action edit" title="Editar">✏️</a>
                        <a href="<?= url('admin/modules/modules.php?course_id=' . $course['id']) ?>" 
                           class="btn-action" title="Módulos">📚</a>
                        <form method="POST" class="d-inline" 
                              onsubmit="return confirm('Tem certeza que deseja excluir este curso?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                            <button type="submit" class="btn-action delete" title="Deletar">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>

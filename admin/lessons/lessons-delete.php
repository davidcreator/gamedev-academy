<?php
/**
 * Delete Lesson
 * Script para excluir lições
 */

require_once '../../includes/init.php';

// Verificar autenticação
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'Acesso negado.');
    redirect(url('admin/login.php'));
}

$db = Database::getInstance();
$id = intval($_GET['id'] ?? 0);
$moduleId = intval($_GET['module_id'] ?? 0);
$courseId = intval($_GET['course_id'] ?? 0);

// Validar ID
if ($id <= 0) {
    flash('error', 'ID da lição inválido.');
    redirect(url('admin/lessons/lessons-list.php'));
}

// Buscar lição
$lesson = $db->fetch("
    SELECT l.*, 
           m.title as module_title,
           c.title as course_title
    FROM course_lessons l
     LEFT JOIN course_modules m ON l.module_id = m.id
    LEFT JOIN courses c ON m.course_id = c.id
    WHERE l.id = ?
", [$id]);

if (!$lesson) {
    flash('error', 'Lição não encontrada.');
    redirect(url('admin/lessons/lessons-list.php?module_id=' . $moduleId . '&course_id=' . $courseId));
}

// Se for requisição POST, executar exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Opcional: Excluir arquivos associados
    // if (!empty($lesson['attachment_url'])) {
    //     // Lógica para excluir anexos
    // }
    
    // Excluir progresso dos alunos desta lição
    $db->delete('lesson_progress', 'lesson_id = ?', [$id]);
    
    // Excluir a lição
    $deleted = $db->delete('course_lessons', 'id = ?', [$id]);
    
    if ($deleted) {
        // Reordenar lições restantes no módulo
        $remainingLessons = $db->fetchAll(
            "SELECT id FROM course_lessons WHERE module_id = ? ORDER BY sort_order ASC",
            [$moduleId]
        );
        
        foreach ($remainingLessons as $order => $remaining) {
            $db->update('course_lessons', [
                'sort_order' => $order + 1
            ], 'id = ?', [$remaining['id']]);
        }
    }
        
        flash('success', 'Lição excluída com sucesso!');
    } else {
        flash('error', 'Erro ao excluir lição.');
    }
    
    redirect(url('admin/lessons/lessons-list.php?module_id=' . $moduleId . '&course_id=' . $courseId));
}

// Mostrar página de confirmação
$pageTitle = 'Excluir Lição';
include '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0">
                    <i class="fas fa-exclamation-triangle"></i>
                    Confirmar Exclusão
                </h4>
            </div>
            <div class="card-body">
                <div class="alert alert-danger">
                    <strong>Atenção!</strong> Esta ação não pode ser desfeita.
                </div>

                <h5>Você está prestes a excluir a seguinte lição:</h5>
                
                <div class="card mt-3">
                    <div class="card-body">
                        <h4><?= escape($lesson['title']) ?></h4>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <strong>Curso:</strong> 
                                    <?= escape($lesson['course_title']) ?>
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <strong>Módulo:</strong> 
                                    <?= escape($lesson['module_title']) ?>
                                </small>
                            </div>
                        </div>
                        
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <small class="text-muted">
                                    <strong>Tipo:</strong> 
                                    <?= ucfirst($lesson['content_type']) ?>
                                </small>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">
                                    <strong>Duração:</strong> 
                                    <?= $lesson['duration_minutes'] ?> minutos
                                </small>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">
                                    <strong>Ordem:</strong> 
                                    #<?= $lesson['order_position'] ?>
                                </small>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <strong>Status:</strong> 
                                    <?= $lesson['is_published'] ? 'Publicada' : 'Rascunho' ?>
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <strong>Prévia Gratuita:</strong> 
                                    <?= $lesson['is_free_preview'] ? 'Sim' : 'Não' ?>
                                </small>
                            </div>
                        </div>

                        <?php if (!empty($lesson['summary'])): ?>
                            <p class="mt-3 mb-0">
                                <small><?= escape($lesson['summary']) ?></small>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($lesson['video_url'])): ?>
                            <div class="mt-2">
                                <small class="text-info">
                                    <i class="fas fa-video"></i> 
                                    Possui vídeo associado
                                </small>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($lesson['attachment_url'])): ?>
                            <div class="mt-2">
                                <small class="text-info">
                                    <i class="fas fa-paperclip"></i> 
                                    Possui materiais complementares
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-4">
                    <h6>O que será excluído:</h6>
                    <ul>
                        <li>Todos os dados da lição</li>
                        <li>O progresso dos alunos nesta lição</li>
                        <?php if (!empty($lesson['attachment_url'])): ?>
                            <li>Links para materiais complementares</li>
                        <?php endif; ?>
                        <?php if ($lesson['is_published']): ?>
                            <li class="text-danger">
                                <strong>A lição está PUBLICADA - alunos podem perder acesso</strong>
                            </li>
                        <?php endif; ?>
                    </ul>
                    
                    <div class="alert alert-warning">
                        <strong>Importante:</strong> As lições restantes serão reordenadas automaticamente.
                    </div>
                </div>

                <form method="POST" class="mt-4">
                    <div class="d-flex gap-3 justify-content-end">
                        <a href="<?= url('admin/lessons/list.php?module_id=' . $moduleId . '&course_id=' . $courseId) ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Sim, Excluir Definitivamente
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Opções Alternativas -->
        <div class="alert alert-info mt-4">
            <h6><i class="fas fa-lightbulb"></i> Alternativas à exclusão:</h6>
            <p class="mb-2">Em vez de excluir, você pode:</p>
            <ul class="mb-0">
                <li>
                    <strong>Despublicar:</strong> 
                    <a href="<?= url('admin/lessons/edit.php?id=' . $id . '&module_id=' . $moduleId . '&course_id=' . $courseId) ?>">Editar a lição</a> 
                    e desmarcar "Publicado" para ocultá-la sem perder os dados
                </li>
                <li>
                    <strong>Mover:</strong> 
                    Transferir a lição para outro módulo em vez de excluir
                </li>
            </ul>
        </div>

        <!-- Estatísticas (se implementado) -->
        <?php
        $stats = $db->fetch("
            SELECT 
                COUNT(DISTINCT lp.user_id) as students_count,
                COUNT(CASE WHEN lp.is_completed = 1 THEN 1 END) as completed_count
            FROM lesson_progress lp
            WHERE lp.lesson_id = ?
        ", [$id]);
        
        if ($stats && $stats['students_count'] > 0):
        ?>
        <div class="alert alert-danger mt-4">
            <h6><i class="fas fa-users"></i> Impacto nos Alunos:</h6>
            <ul class="mb-0">
                <li><strong><?= $stats['students_count'] ?></strong> aluno(s) iniciaram esta lição</li>
                <li><strong><?= $stats['completed_count'] ?></strong> aluno(s) completaram esta lição</li>
            </ul>
            <p class="mt-2 mb-0">
                <small>Todo o progresso destes alunos nesta lição será perdido.</small>
            </p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<?php
// learn.php - Ambiente de Estudo do Curso

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Course.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/Gamification.php';
require_once __DIR__ . '/includes/functions.php';

$auth = new Auth();
$auth->requireLogin();
$db = Database::getInstance();
$courseModel = new Course();
$gamification = new Gamification();

$slug = trim($_GET['course'] ?? '');
$lessonId = intval($_GET['lesson'] ?? 0);
$user = $auth->getCurrentUser();

$course = $courseModel->findBySlug($slug);
if (!$course || !$course['is_published']) {
    http_response_code(404);
    echo "Curso não encontrado.";
    exit;
}

// Garantir matrícula (se o curso for grátis, matricula automática ao acessar)
if (!$courseModel->isEnrolled($user['id'], $course['id'])) {
    if (!empty($course['is_free'])) {
        $courseModel->enroll($user['id'], $course['id']);
    } else {
        flash('error', 'Você não está matriculado neste curso.');
        redirect(url('course.php?slug=' . $course['slug']));
    }
}

// Processar conclusão de lição
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'complete_lesson') {
    $lid = intval($_POST['lesson_id'] ?? 0);
    if ($lid > 0) {
        $lesson = $db->fetch("SELECT * FROM lessons WHERE id = ?", [$lid]);
        if ($lesson) {
            // Inserir/Atualizar progresso
            $existing = $db->fetch("SELECT id FROM lesson_progress WHERE user_id = ? AND lesson_id = ?", [$user['id'], $lid]);
            if ($existing) {
                $db->query("UPDATE lesson_progress SET is_completed = 1, progress_percentage = 100, time_spent = time_spent WHERE id = ?", [$existing['id']]);
            } else {
                $db->insert('lesson_progress', [
                    'user_id' => $user['id'],
                    'lesson_id' => $lid,
                    'is_completed' => 1,
                    'progress_percentage' => 100,
                    'time_spent' => 0
                ]);
            }
            
            // Atualizar matrícula
            $enrollment = $db->fetch("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?", [$user['id'], $course['id']]);
            if ($enrollment) {
                $totalLessons = $db->fetch("SELECT COUNT(*) as total FROM lessons l JOIN modules m ON l.module_id = m.id WHERE m.course_id = ?", [$course['id']])['total'];
                $completedLessons = $db->fetch("SELECT COUNT(*) as total FROM lesson_progress lp JOIN lessons l ON lp.lesson_id = l.id JOIN modules m ON l.module_id = m.id WHERE lp.user_id = ? AND lp.is_completed = 1 AND m.course_id = ?", [$user['id'], $course['id']])['total'];
                $progress = $totalLessons > 0 ? min(100, round(($completedLessons / $totalLessons) * 100, 2)) : 0;
                
                $db->query("UPDATE enrollments SET completed_lessons = ?, total_lessons = ?, progress_percentage = ?, current_lesson_id = ?, last_accessed = NOW() WHERE id = ?", [
                    $completedLessons, $totalLessons, $progress, $lid, $enrollment['id']
                ]);
                
                // Recompensas
                $xp = intval($lesson['xp_reward'] ?? 10);
                $coins = intval($lesson['coin_reward'] ?? 1);
                if ($xp > 0) $gamification->addXP($user['id'], $xp, 'lesson_complete', "Lição concluída: {$lesson['title']}", $lid, 'lesson');
                if ($coins > 0) $gamification->addCoins($user['id'], $coins);
                
                // Conclusão de curso (garante XP do curso apenas uma vez)
                if ($progress >= 100 && !$enrollment['completed_at']) {
                    $courseXp = intval($course['xp_reward'] ?? 0);
                    $courseCoins = intval($course['coin_reward'] ?? 0);
                    if ($courseXp > 0) {
                        $gamification->addXP($user['id'], $courseXp, 'course_complete', "Curso concluído: {$course['title']}", $course['id'], 'course');
                    }
                    if ($courseCoins > 0) {
                        $gamification->addCoins($user['id'], $courseCoins);
                    }
                    $db->query("UPDATE enrollments SET status = 'completed', completed_at = NOW() WHERE id = ?", [$enrollment['id']]);
                }
            }
            
            flash('success', 'Lição marcada como concluída!');
            redirect(url('learn.php?course=' . $course['slug'] . '&lesson=' . $lid));
        }
    }
}

// Submissão de quiz básico
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit_quiz') {
    $lid = intval($_POST['lesson_id'] ?? 0);
    $answers = $_POST['answers'] ?? [];
    if ($lid > 0) {
        $lesson = $db->fetch("SELECT * FROM lessons WHERE id = ?", [$lid]);
        if ($lesson && $lesson['content_type'] === 'quiz' && !empty($lesson['content'])) {
            $quiz = json_decode($lesson['content'], true);
            $correct = 0;
            $total = 0;
            if (is_array($quiz) && !empty($quiz['questions'])) {
                foreach ($quiz['questions'] as $qi => $q) {
                    $total++;
                    $correctIndex = $q['answer'] ?? null;
                    $given = isset($answers[$qi]) ? intval($answers[$qi]) : null;
                    if ($correctIndex !== null && $given === intval($correctIndex)) {
                        $correct++;
                    }
                }
            }
            // Atualiza progresso e recompensa simples com base no acerto
            $existing = $db->fetch("SELECT id FROM lesson_progress WHERE user_id = ? AND lesson_id = ?", [$user['id'], $lid]);
            if ($existing) {
                $db->query("UPDATE lesson_progress SET is_completed = 1, progress_percentage = 100, time_spent = time_spent, score = ?, max_score = ? WHERE id = ?", [$correct, $total, $existing['id']]);
            } else {
                $db->insert('lesson_progress', [
                    'user_id' => $user['id'],
                    'lesson_id' => $lid,
                    'is_completed' => 1,
                    'progress_percentage' => 100,
                    'time_spent' => 0,
                    'score' => $correct,
                    'max_score' => $total
                ]);
            }
            // Recompensa proporcional: até o xp_reward
            $xpBase = intval($lesson['xp_reward'] ?? 10);
            $xpEarned = $total > 0 ? intval(round($xpBase * ($correct / $total))) : 0;
            if ($xpEarned > 0) {
                $gamification->addXP($user['id'], $xpEarned, 'quiz_complete', "Quiz: {$lesson['title']} ({$correct}/{$total})", $lid, 'lesson');
            }
            // Atualiza matrícula
            $enrollment = $db->fetch("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?", [$user['id'], $course['id']]);
            if ($enrollment) {
                $totalLessons = $db->fetch("SELECT COUNT(*) as total FROM lessons l JOIN modules m ON l.module_id = m.id WHERE m.course_id = ?", [$course['id']])['total'];
                $completedLessons = $db->fetch("SELECT COUNT(*) as total FROM lesson_progress lp JOIN lessons l ON lp.lesson_id = l.id JOIN modules m ON l.module_id = m.id WHERE lp.user_id = ? AND lp.is_completed = 1 AND m.course_id = ?", [$user['id'], $course['id']])['total'];
                $progress = $totalLessons > 0 ? min(100, round(($completedLessons / $totalLessons) * 100, 2)) : 0;
                $db->query("UPDATE enrollments SET completed_lessons = ?, total_lessons = ?, progress_percentage = ?, current_lesson_id = ?, last_accessed = NOW() WHERE id = ?", [
                    $completedLessons, $totalLessons, $progress, $lid, $enrollment['id']
                ]);
                if ($progress >= 100 && !$enrollment['completed_at']) {
                    $courseXp = intval($course['xp_reward'] ?? 0);
                    $courseCoins = intval($course['coin_reward'] ?? 0);
                    if ($courseXp > 0) $gamification->addXP($user['id'], $courseXp, 'course_complete', "Curso concluído: {$course['title']}", $course['id'], 'course');
                    if ($courseCoins > 0) $gamification->addCoins($user['id'], $courseCoins);
                    $db->query("UPDATE enrollments SET status = 'completed', completed_at = NOW() WHERE id = ?", [$enrollment['id']]);
                }
            }
            flash('success', "Quiz enviado: {$correct}/{$total} corretas.");
            redirect(url('learn.php?course=' . $course['slug'] . '&lesson=' . $lid));
        }
    }
}
// Navegação: módulos e lições
$modules = $courseModel->getModules($course['id']);
if ($lessonId <= 0) {
    // Selecionar primeira lição disponível
    foreach ($modules as $m) {
        $firstLesson = $db->fetch("SELECT id FROM lessons WHERE module_id = ? AND is_published = 1 ORDER BY order_index LIMIT 1", [$m['id']]);
        if ($firstLesson) { $lessonId = $firstLesson['id']; break; }
    }
}
$currentLesson = $lessonId ? $db->fetch("SELECT * FROM lessons WHERE id = ?", [$lessonId]) : null;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estudar: <?= escape($course['title']) ?> - <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/user.css') ?>">
</head>
<body>
    <?php include __DIR__ . '/user/includes/header.php'; ?>
    
    <?= showFlashMessages() ?>
    
    <div class="d-flex">
        <!-- Sidebar do curso -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <div class="admin-sidebar-brand">
                    <span>📚</span>
                    <span><?= escape($course['title']) ?></span>
                </div>
            </div>
            <nav class="admin-nav">
                <?php foreach ($modules as $m): ?>
                <div class="admin-nav-item">
                    <span>📦</span>
                    <span><?= escape($m['title']) ?></span>
                </div>
                <?php
                    $lessons = $courseModel->getLessons($m['id']);
                    foreach ($lessons as $l):
                        $active = ($currentLesson && $currentLesson['id'] == $l['id']);
                ?>
                <a href="<?= url('learn.php?course=' . $course['slug'] . '&lesson=' . $l['id']) ?>" class="admin-nav-item <?= $active ? 'active' : '' ?>">
                    <span>📖</span>
                    <span><?= escape($l['title']) ?></span>
                </a>
                <?php endforeach; ?>
                <?php endforeach; ?>
            </nav>
        </aside>
        
        <!-- Player/Conteúdo -->
        <main class="admin-main">
            <div class="admin-header">
                <h1><?= escape($currentLesson['title'] ?? 'Selecione uma lição') ?></h1>
                <div class="d-flex align-center gap-2">
                    <a href="<?= url('course.php?slug=' . $course['slug']) ?>" class="btn btn-sm btn-outline">Ver Curso</a>
                </div>
            </div>
            
            <div class="admin-content">
                <?php if ($currentLesson): ?>
                    <?php if ($currentLesson['content_type'] === 'video' && !empty($currentLesson['video_url'])): ?>
                        <div class="card p-4">
                            <?php
                            $url = $currentLesson['video_url'];
                            $provider = $currentLesson['video_provider'] ?? 'youtube';
                            if ($provider === 'youtube') {
                                // Embed simples
                                $embed = $url;
                                if (strpos($url, 'watch?v=') !== false) {
                                    $embed = str_replace('watch?v=', 'embed/', $url);
                                }
                                echo '<iframe width="100%" height="480" src="' . escape($embed) . '" frameborder="0" allowfullscreen></iframe>';
                            } elseif ($provider === 'vimeo') {
                                echo '<iframe width="100%" height="480" src="' . escape($url) . '" frameborder="0" allowfullscreen></iframe>';
                            } else {
                                echo '<a class="btn btn-primary" href="' . escape($url) . '" target="_blank">Assistir Vídeo</a>';
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($currentLesson['content_type'] === 'text'): ?>
                        <div class="card p-4">
                            <div class="prose">
                                <?= $currentLesson['content'] ?? '<p>Conteúdo não disponível.</p>' ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($currentLesson['content_type'] === 'quiz'): ?>
                        <?php
                        $quizData = [];
                        if (!empty($currentLesson['content'])) {
                            $decoded = json_decode($currentLesson['content'], true);
                            if (is_array($decoded)) { $quizData = $decoded; }
                        }
                        ?>
                        <div class="card p-4">
                            <form method="POST">
                                <input type="hidden" name="action" value="submit_quiz">
                                <input type="hidden" name="lesson_id" value="<?= $currentLesson['id'] ?>">
                                <?php if (!empty($quizData['questions'])): ?>
                                    <?php foreach ($quizData['questions'] as $qi => $q): ?>
                                        <div class="mb-3">
                                            <div><strong>Q<?= $qi+1 ?>.</strong> <?= escape($q['text'] ?? '') ?></div>
                                            <?php if (!empty($q['options'])): ?>
                                                <?php foreach ($q['options'] as $oi => $opt): ?>
                                                    <label class="d-flex align-center gap-1 mt-1">
                                                        <input type="radio" name="answers[<?= $qi ?>]" value="<?= $oi ?>">
                                                        <span><?= escape($opt) ?></span>
                                                    </label>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                    <button type="submit" class="btn btn-primary">Enviar respostas</button>
                                <?php else: ?>
                                    <p>Quiz indisponível.</p>
                                <?php endif; ?>
                            </form>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (in_array($currentLesson['content_type'], ['exercise','project','live','download'])): ?>
                        <div class="card p-4">
                            <p>Conteúdo: <?= escape($currentLesson['content_type']) ?></p>
                            <div class="prose">
                                <?= $currentLesson['content'] ?? '' ?>
                            </div>
                            <?php if (!empty($currentLesson['attachment_url'])): ?>
                                <a href="<?= escape($currentLesson['attachment_url']) ?>" class="btn btn-secondary" target="_blank">Baixar recurso</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="mt-3">
                        <input type="hidden" name="action" value="complete_lesson">
                        <input type="hidden" name="lesson_id" value="<?= $currentLesson['id'] ?>">
                        <button type="submit" class="btn btn-success">Marcar como concluída</button>
                    </form>
                <?php else: ?>
                    <div class="card p-4">
                        <p>Nenhuma lição disponível. Volte mais tarde.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

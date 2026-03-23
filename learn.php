<?php
// learn.php - Ambiente de Estudo do Curso

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Course.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/CertificateService.php';
require_once __DIR__ . '/classes/Gamification.php';
require_once __DIR__ . '/includes/functions.php';

$auth = new Auth();
$auth->requireLogin();
$db = Database::getInstance();
$courseModel = new Course();
$certificateService = new CertificateService();
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
        $lesson = $db->fetch("SELECT * FROM course_lessons WHERE id = ?", [$lid]);
        if ($lesson) {
            // Inserir/Atualizar progresso
            $existing = $db->fetch("SELECT id FROM lesson_progress WHERE user_id = ? AND lesson_id = ?", [$user['id'], $lid]);
            if ($existing) {
                $db->query("UPDATE lesson_progress SET is_completed = 1, progress_percent = 100 WHERE id = ?", [$existing['id']]);
            } else {
                $db->insert('lesson_progress', [
                    'user_id' => $user['id'],
                    'lesson_id' => $lid,
                    'is_completed' => 1,
                    'progress_percent' => 100,
                    'watch_time' => 0
                ]);
            }
            
            // Atualizar matrícula
            $enrollment = $db->fetch("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?", [$user['id'], $course['id']]);
            $successMessage = 'Lição marcada como concluída!';
            if ($enrollment) {
                $totalLessons = $db->fetch("SELECT COUNT(*) as total FROM course_lessons l JOIN course_modules m ON l.module_id = m.id WHERE m.course_id = ?", [$course['id']])['total'];
                $completedLessons = $db->fetch("SELECT COUNT(*) as total FROM lesson_progress lp JOIN course_lessons l ON lp.lesson_id = l.id JOIN course_modules m ON l.module_id = m.id WHERE lp.user_id = ? AND lp.is_completed = 1 AND m.course_id = ?", [$user['id'], $course['id']])['total'];
                $progress = $totalLessons > 0 ? min(100, round(($completedLessons / $totalLessons) * 100, 2)) : 0;
                
                $db->query("UPDATE enrollments SET lessons_completed = ?, progress_percent = ?, last_lesson_id = ?, last_accessed_at = NOW() WHERE id = ?", [
                    $completedLessons, $progress, $lid, $enrollment['id']
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

                $certificateResult = $certificateService->issueForEnrollment((int) $enrollment['id']);
                if (!empty($certificateResult['issued'])) {
                    $successMessage .= ' Certificado emitido com sucesso!';
                }
            }
            
            flash('success', 'Lição marcada como concluída!');
            flash('success', $successMessage);
            redirect(url('learn.php?course=' . $course['slug'] . '&lesson=' . $lid));
        }
    }
}

// Submissão de quiz básico
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit_quiz') {
    $lid = intval($_POST['lesson_id'] ?? 0);
    $answers = $_POST['answers'] ?? [];
    if ($lid > 0) {
        $lesson = $db->fetch("SELECT * FROM course_lessons WHERE id = ?", [$lid]);
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
                $db->query("UPDATE lesson_progress SET is_completed = 1, progress_percent = 100, watch_time = watch_time, score = ?, max_score = ? WHERE id = ?", [$correct, $total, $existing['id']]);
            } else {
                $db->insert('lesson_progress', [
                    'user_id' => $user['id'],
                    'lesson_id' => $lid,
                    'is_completed' => 1,
                    'progress_percent' => 100,
                    'watch_time' => 0,
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
            $successMessage = "Quiz enviado: {$correct}/{$total} corretas.";
            if ($enrollment) {
                $totalLessons = $db->fetch("SELECT COUNT(*) as total FROM course_lessons l JOIN course_modules m ON l.module_id = m.id WHERE m.course_id = ?", [$course['id']])['total'];
                $completedLessons = $db->fetch("SELECT COUNT(*) as total FROM lesson_progress lp JOIN course_lessons l ON lp.lesson_id = l.id JOIN course_modules m ON l.module_id = m.id WHERE lp.user_id = ? AND lp.is_completed = 1 AND m.course_id = ?", [$user['id'], $course['id']])['total'];
                $progress = $totalLessons > 0 ? min(100, round(($completedLessons / $totalLessons) * 100, 2)) : 0;
                $db->query("UPDATE enrollments SET lessons_completed = ?, progress_percent = ?, last_lesson_id = ?, last_accessed_at = NOW() WHERE id = ?", [
                    $completedLessons, $progress, $lid, $enrollment['id']
                ]);
                if ($progress >= 100 && !$enrollment['completed_at']) {
                    $courseXp = intval($course['xp_reward'] ?? 0);
                    $courseCoins = intval($course['coin_reward'] ?? 0);
                    if ($courseXp > 0) $gamification->addXP($user['id'], $courseXp, 'course_complete', "Curso concluído: {$course['title']}", $course['id'], 'course');
                    if ($courseCoins > 0) $gamification->addCoins($user['id'], $courseCoins);
                    $db->query("UPDATE enrollments SET status = 'completed', completed_at = NOW() WHERE id = ?", [$enrollment['id']]);
                }

                $certificateResult = $certificateService->issueForEnrollment((int) $enrollment['id']);
                if (!empty($certificateResult['issued'])) {
                    $successMessage .= ' Certificado emitido com sucesso!';
                }
            }
            flash('success', $successMessage);
            redirect(url('learn.php?course=' . $course['slug'] . '&lesson=' . $lid));
        }
    }
}
// Navegação: módulos e lições
$modules = $courseModel->getModules($course['id']);
if ($lessonId <= 0) {
    // Selecionar primeira lição disponível
    foreach ($modules as $m) {
        $firstLesson = $db->fetch("SELECT id FROM course_lessons WHERE module_id = ? AND is_published = 1 ORDER BY sort_order LIMIT 1", [$m['id']]);
        if ($firstLesson) { $lessonId = $firstLesson['id']; break; }
    }
}
$currentLesson = $lessonId ? $db->fetch("SELECT * FROM course_lessons WHERE id = ?", [$lessonId]) : null;

if (!$currentLesson) {
    echo "<div style='padding:2rem; font-family:Inter,Arial; color:#e11d48;'>Nenhuma liÃ§Ã£o publicada encontrada para este curso. Crie mÃ³dulos e liÃ§Ãµes ou rode o seed de demo em <code>scripts/seed-demo-data.php</code>.</div>";
    exit;
}

// Título para o header do painel do aluno
$pageTitle = 'Estudar: ' . ($course['title'] ?? '');

include __DIR__ . '/user/includes/header.php';

echo showFlashMessages();

?>
    <style>
        .learn-layout { display: grid; grid-template-columns: 320px 1fr; gap: 1.5rem; }
        .learn-sidebar { background: var(--gray-800); border: 1px solid var(--gray-700); border-radius: 12px; padding: 1rem; max-height: calc(100vh - 180px); overflow-y: auto; }
        .learn-sidebar h3 { margin: 0 0 .5rem; font-size: 1.05rem; }
        .learn-module { margin-top: 1rem; }
        .learn-module-title { font-weight: 600; color: var(--gray-100); display: flex; align-items: center; gap: .5rem; }
        .learn-lesson { display: flex; align-items: center; gap: .5rem; padding: .45rem .5rem; border-radius: 8px; color: var(--gray-200); text-decoration: none; }
        .learn-lesson:hover { background: var(--gray-700); }
        .learn-lesson.active { background: var(--primary-900, #1f2937); color: #fff; }
        .learn-lesson small { color: var(--gray-400); }
        .learn-content { background: var(--gray-800); border: 1px solid var(--gray-700); border-radius: 12px; padding: 1.25rem; }
        .learn-header { display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:1rem; }
        @media (max-width: 900px) { .learn-layout { grid-template-columns: 1fr; } .learn-sidebar { max-height:none; } }
    </style>

    <div class="learn-layout">
        <!-- Sidebar do curso -->
        <aside class="learn-sidebar">
            <h3><?= escape($course['title']) ?></h3>
            <?php foreach ($modules as $m): ?>
            <div class="learn-module">
                <div class="learn-module-title">📦 <?= escape($m['title']) ?></div>
                <?php
                    $lessons = $courseModel->getLessons($m['id']);
                    foreach ($lessons as $l):
                        $active = ($currentLesson && $currentLesson['id'] == $l['id']);
                ?>
                <a href="<?= url('learn.php?course=' . $course['slug'] . '&lesson=' . $l['id']) ?>" class="learn-lesson <?= $active ? 'active' : '' ?>">
                    <span>📖</span>
                    <div>
                        <div><?= escape($l['title']) ?></div>
                        <small><?= ucfirst($l['content_type']) ?> • <?= intval($l['xp_reward'] ?? 0) ?> XP</small>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </aside>
        
        <!-- Player/Conteúdo -->
        <div class="learn-content">
            <div class="learn-header">
                <div>
                    <div style="color:var(--gray-400); font-size:.9rem;"><?= escape($course['title']) ?></div>
                    <h1 style="margin:.1rem 0 0; font-size:1.5rem;"><?= escape($currentLesson['title'] ?? 'Selecione uma lição') ?></h1>
                </div>
                <a href="<?= url('course.php?slug=' . $course['slug']) ?>" class="btn btn-sm btn-outline">Ver Curso</a>
            </div>
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
    <?php include __DIR__ . '/user/includes/footer.php'; ?>

<?php
/**
 * Seeder de dados demo para GameDev Academy.
 *
 * Cria:
 * - Usuário admin e aluno de testes.
 * - Categorias, cursos, módulos, lições (com vídeos, textos, quizzes e tarefas).
 * - Matrículas do aluno em todos os cursos.
 * - 1.000 conquistas (XP + moedas) para cobrir lições, cursos, streaks e XP.
 * - Níveis (1–50) se a tabela estiver vazia.
 * - Views de compatibilidade se o banco estiver no padrão antigo (modules/lessons).
 *
 * Uso: php scripts/seed-demo-data.php
 * Nunca rode em produção: dados apenas para desenvolvimento/teste.
 */

declare(strict_types=1);

// Carregar apenas configuração de banco sem iniciar sessão
require __DIR__ . '/../config/database.php'; // define constantes DB_* e cria $pdo
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// -------------------------------------------------------------------------
// Helpers
// -------------------------------------------------------------------------

function tableExists(PDO $pdo, string $table): bool {
    $stmt = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($table));
    return (bool) $stmt->fetchColumn();
}

function ensureView(PDO $pdo, string $view, string $source): void {
    if (!tableExists($pdo, $view) && tableExists($pdo, $source)) {
        $pdo->exec("CREATE VIEW `{$view}` AS SELECT * FROM `{$source}`");
    }
}

function slug(string $text): string {
    if (function_exists('slugify')) {
        return slugify($text);
    }
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('/[^a-zA-Z0-9]+/', '-', $text);
    return strtolower(trim($text, '-')) ?: 'item';
}

// -------------------------------------------------------------------------
// Compatibilidade: cria views se o banco estiver com nomes antigos
// -------------------------------------------------------------------------
ensureView($pdo, 'course_modules', 'modules');
ensureView($pdo, 'course_lessons', 'lessons');

// -------------------------------------------------------------------------
// Usuários base
// -------------------------------------------------------------------------
$users = [
    [
        'name' => 'Admin',
        'username' => 'admin',
        'email' => 'admin@gamedev.test',
        'role' => 'admin',
        'password' => password_hash('admin123', PASSWORD_BCRYPT),
        'is_active' => 1,
    ],
    [
        'name' => 'Aluno Demo',
        'username' => 'aluno',
        'email' => 'aluno@gamedev.test',
        'role' => 'student',
        'password' => password_hash('123456', PASSWORD_BCRYPT),
        'is_active' => 1,
    ],
];

foreach ($users as $u) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$u['email'], $u['username']]);
    $id = $stmt->fetchColumn();
    if (!$id) {
        $pdo->prepare("
            INSERT INTO users (name, full_name, username, email, password, role, is_active, xp_total, coins, level)
            VALUES (:name, :full, :username, :email, :password, :role, :active, 0, 0, 1)
        ")->execute([
            ':name' => $u['name'],
            ':full' => $u['name'],
            ':username' => $u['username'],
            ':email' => $u['email'],
            ':password' => $u['password'],
            ':role' => $u['role'],
            ':active' => $u['is_active'],
        ]);
        echo "Criado usuário {$u['email']}\n";
    }
}

// Buscar ids para reuso
$adminId = (int) ($pdo->query("SELECT id FROM users WHERE email='admin@gamedev.test' OR username='admin' OR role='admin' LIMIT 1")->fetchColumn() ?: 0);
$studentId = (int) ($pdo->query("SELECT id FROM users WHERE email='aluno@gamedev.test' OR username='aluno' LIMIT 1")->fetchColumn() ?: 0);

// -------------------------------------------------------------------------
// Categorias
// -------------------------------------------------------------------------
$categories = [
    ['Game Design', 'Conceitos de design e teoria de jogos'],
    ['Programação', 'Códigos e engines'],
    ['Arte 2D', 'Pixel art e ilustração'],
    ['Arte 3D', 'Modelagem e animação'],
    ['Áudio', 'Música e efeitos'],
    ['Publicação', 'Builds, marketing e monetização'],
];

foreach ($categories as [$name, $desc]) {
    $slug = slug($name);
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
    $stmt->execute([$slug]);
    if (!$stmt->fetchColumn()) {
        $pdo->prepare("
            INSERT INTO categories (name, slug, description, is_active)
            VALUES (?, ?, ?, 1)
        ")->execute([$name, $slug, $desc]);
    }
}
$categoryIds = $pdo->query("SELECT id FROM categories")->fetchAll(PDO::FETCH_COLUMN);

// -------------------------------------------------------------------------
// Níveis (se tabela vazia)
// -------------------------------------------------------------------------
$levelsCount = (int) ($pdo->query("SELECT COUNT(*) FROM levels")->fetchColumn() ?: 0);
if ($levelsCount === 0) {
    $stmt = $pdo->prepare("
        INSERT INTO levels (level_number, title, badge_icon, xp_required, color)
        VALUES (:num, :title, :icon, :xp, :color)
    ");
    $colors = ['#6366f1','#10b981','#f59e0b','#ef4444','#8b5cf6','#0ea5e9'];
    for ($i = 1; $i <= 50; $i++) {
        $stmt->execute([
            ':num' => $i,
            ':title' => "Nível {$i}",
            ':icon' => '🏅',
            ':xp' => ($i - 1) * ($i - 1) * 100,
            ':color' => $colors[$i % count($colors)],
        ]);
    }
    echo "Seed de níveis (1-50) criado.\n";
}

// -------------------------------------------------------------------------
// Cursos, módulos, lições
// -------------------------------------------------------------------------
$courseBase = [
    ['Phaser Web', 'beginner'],
    ['Godot 4', 'beginner'],
    ['Unity 3D', 'intermediate'],
    ['Unreal 5', 'advanced'],
    ['Game Design', 'all_levels'],
    ['Pixel Art', 'beginner'],
    ['AI para Jogos', 'advanced'],
    ['Multiplayer', 'advanced'],
    ['Monetização', 'intermediate'],
    ['Narrativa', 'intermediate'],
];

$courseStmt = $pdo->prepare("
    INSERT INTO courses 
    (title, slug, short_description, description, instructor_id, category_id, level, duration_hours, xp_reward, coin_reward, is_published, is_featured, is_free, price, original_price, currency, total_modules, total_lessons, status)
    VALUES (:title, :slug, :short, :desc, :instr, :cat, :lvl, :dur, :xp, :coin, 1, 0, 1, 0, 0, 'BRL', :mods, :less, 'published')
    ON DUPLICATE KEY UPDATE title=VALUES(title), short_description=VALUES(short_description), description=VALUES(description),
        level=VALUES(level), duration_hours=VALUES(duration_hours), xp_reward=VALUES(xp_reward),
        coin_reward=VALUES(coin_reward), is_published=1, is_free=1, total_modules=VALUES(total_modules), total_lessons=VALUES(total_lessons), status='published'
");

$moduleStmt = $pdo->prepare("
    INSERT INTO course_modules (course_id, title, description, sort_order, is_published, duration_minutes, xp_reward, is_free_preview)
    VALUES (:course, :title, :desc, :sort, 1, :dur, :xp, :free)
");

$lessonStmt = $pdo->prepare("
    INSERT INTO course_lessons 
    (module_id, course_id, title, summary, slug, content_type, content, video_url, video_duration, xp_reward, coin_reward, is_free_preview, sort_order, is_published)
    VALUES (:module, :course, :title, :summary, :slug, :ctype, :content, :video, :dur, :xp, :coin, :free, :sort, 1)
");

$courseIds = [];
$courseIndex = 1;
foreach ($courseBase as [$topic, $level]) {
    $title = "{$topic} Masterclass {$courseIndex}";
    $slugCourse = slug($title);
    $short = "Aprenda {$topic} do básico ao avançado.";
    $desc = "{$topic}: conteúdos práticos, desafios e projeto final.";
    $cat = $categoryIds[array_rand($categoryIds)];
    $modulesCount = 4;
    $lessonsPerModule = 6;
    $courseStmt->execute([
        ':title' => $title,
        ':slug' => $slugCourse,
        ':short' => $short,
        ':desc' => $desc,
        ':instr' => $adminId,
        ':cat' => $cat,
        ':lvl' => $level,
        ':dur' => 6 + $courseIndex * 0.5,
        ':xp' => 300 + $courseIndex * 20,
        ':coin' => 30 + $courseIndex * 2,
        ':mods' => $modulesCount,
        ':less' => $modulesCount * $lessonsPerModule,
    ]);

    $courseId = (int) $pdo->lastInsertId();
    if ($courseId === 0) {
        $courseId = (int) $pdo->query("SELECT id FROM courses WHERE slug = " . $pdo->quote($slugCourse))->fetchColumn();
    }
    $courseIds[] = $courseId;

    // Módulos
    $moduleTitles = ['Fundamentos', 'Intermediário', 'Avançado', 'Projeto Final'];
    foreach ($moduleTitles as $mIndex => $mTitle) {
        $moduleStmt->execute([
            ':course' => $courseId,
            ':title' => $mTitle,
            ':desc' => "{$mTitle} do curso {$title}",
            ':sort' => $mIndex + 1,
            ':dur' => 45 + ($mIndex * 15),
            ':xp' => 100 + ($mIndex * 25),
            ':free' => $mIndex === 0 ? 1 : 0,
        ]);
        $moduleId = (int) $pdo->lastInsertId();

        // Lição por módulo
        $types = ['video','text','video','quiz','assignment','video'];
        for ($l = 0; $l < $lessonsPerModule; $l++) {
            $type = $types[$l % count($types)];
            $lessonTitle = "{$mTitle} - Aula " . ($l + 1);
            $lessonSlug = slug("{$slugCourse}-{$mTitle}-{$l}");
            $content = $type === 'quiz'
                ? json_encode(['questions' => [
                    ['q' => 'Pergunta exemplo?', 'options' => ['Opção A','Opção B','Opção C'], 'answer' => 1]
                ]], JSON_UNESCAPED_UNICODE)
                : "<p>Conteúdo {$lessonTitle} ({$type}).</p>";
            $lessonStmt->execute([
                ':module' => $moduleId,
                ':course' => $courseId,
                ':title' => $lessonTitle,
                ':summary' => "Resumo de {$lessonTitle}",
                ':slug' => $lessonSlug,
                ':ctype' => $type,
                ':content' => $content,
                ':video' => $type === 'video' ? 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' : null,
            ':dur' => 8 + $l * 2,
            ':xp' => 20 + ($mIndex * 5) + ($l * 2),
            ':coin' => 2 + $l,
            ':free' => ($mIndex === 0 && $l < 2) ? 1 : 0,
            ':sort' => $l + 1,
        ]);
    }
    }
    $courseIndex++;
}

// -------------------------------------------------------------------------
// Matrículas do aluno demo
// -------------------------------------------------------------------------
$enrollStmt = $pdo->prepare("INSERT IGNORE INTO enrollments (user_id, course_id, status, progress_percent) VALUES (?, ?, 'active', 0)");
foreach ($courseIds as $cid) {
    $enrollStmt->execute([$studentId, $cid]);
}

// -------------------------------------------------------------------------
// Conquistas automáticas (1000)
// -------------------------------------------------------------------------
$achCount = (int) ($pdo->query("SELECT COUNT(*) FROM achievements")->fetchColumn() ?: 0);
$toCreate = max(0, 1000 - $achCount);

if ($toCreate > 0) {
    $achStmt = $pdo->prepare("
        INSERT INTO achievements (name, slug, description, icon, type, requirement_type, requirement_value, xp_reward, coin_reward, sort_order, is_active)
        VALUES (:name, :slug, :desc, :icon, :type, :req_type, :req_value, :xp, :coin, :sort, 1)
        ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description), requirement_value=VALUES(requirement_value),
            xp_reward=VALUES(xp_reward), coin_reward=VALUES(coin_reward), is_active=1
    ");

    $icons = ['🎯','🔥','🏅','🚀','📚','🧠','⚡','💎','🥇','🎖️'];
    $sort = 1;

    // Lessons completed
    for ($i = 1; $i <= 400; $i++, $sort++) {
        $achStmt->execute([
            ':name' => "Lições Concluídas {$i}",
            ':slug' => "auto-lessons-{$i}",
            ':desc' => "Complete {$i} lições.",
            ':icon' => $icons[$i % count($icons)],
            ':type' => 'lesson',
            ':req_type' => 'lessons_completed',
            ':req_value' => $i,
            ':xp' => 5 + $i,
            ':coin' => 1 + intdiv($i, 5),
            ':sort' => $sort,
        ]);
    }

    // Courses completed
    for ($i = 1; $i <= 200; $i++, $sort++) {
        $achStmt->execute([
            ':name' => "Cursos Finalizados {$i}",
            ':slug' => "auto-courses-{$i}",
            ':desc' => "Conclua {$i} cursos.",
            ':icon' => $icons[$i % count($icons)],
            ':type' => 'course',
            ':req_type' => 'courses_completed',
            ':req_value' => $i,
            ':xp' => 50 + ($i * 5),
            ':coin' => 10 + intdiv($i, 2),
            ':sort' => $sort,
        ]);
    }

    // Streak (aulas seguidas)
    for ($i = 1; $i <= 200; $i++, $sort++) {
        $streak = $i * 5; // 5,10,...1000
        $achStmt->execute([
            ':name' => "Streak de {$streak} dias",
            ':slug' => "auto-streak-{$streak}",
            ':desc' => "Mantenha {$streak} dias seguidos estudando.",
            ':icon' => '🔥',
            ':type' => 'streak',
            ':req_type' => 'streak',
            ':req_value' => $streak,
            ':xp' => 40 + ($i * 8),
            ':coin' => 20 + ($i * 2),
            ':sort' => $sort,
        ]);
    }

    // XP total
    for ($i = 1; $i <= 200; $i++, $sort++) {
        $xpTarget = $i * 1000;
        $achStmt->execute([
            ':name' => "XP Acumulado {$xpTarget}",
            ':slug' => "auto-xp-{$xpTarget}",
            ':desc' => "Alcance {$xpTarget} XP.",
            ':icon' => '⚡',
            ':type' => 'special',
            ':req_type' => 'xp_earned',
            ':req_value' => $xpTarget,
            ':xp' => 25 + intdiv($xpTarget, 100),
            ':coin' => 15 + intdiv($xpTarget, 200),
            ':sort' => $sort,
        ]);
    }

    echo "Criadas/atualizadas {$toCreate} conquistas (total agora: " . (1000) . ").\n";
} else {
    echo "Já existem {$achCount} conquistas. Nenhuma nova criada.\n";
}

echo "Seed concluído com sucesso.\n";

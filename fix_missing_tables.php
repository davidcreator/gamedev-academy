<?php
// fix_missing_tables.php - Script de Correção de Tabelas Faltantes

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir configurações
require_once 'init.php';

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Correção de Tabelas - GameDev Academy</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: #1a1a1a; 
            color: #fff; 
            padding: 20px; 
            line-height: 1.6;
        }
        .container { 
            max-width: 900px; 
            margin: 0 auto; 
            background: #2a2a2a; 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
        }
        h1 { 
            color: #6366f1; 
            text-align: center;
            margin-bottom: 30px;
        }
        h2 { 
            color: #818cf8; 
            margin-top: 30px;
        }
        .success { 
            color: #10b981; 
            background: rgba(16, 185, 129, 0.1);
            padding: 10px 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 3px solid #10b981;
        }
        .error { 
            color: #ef4444; 
            background: rgba(239, 68, 68, 0.1);
            padding: 10px 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 3px solid #ef4444;
        }
        .warning { 
            color: #f59e0b; 
            background: rgba(245, 158, 11, 0.1);
            padding: 10px 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 3px solid #f59e0b;
        }
        .info { 
            color: #3b82f6; 
            background: rgba(59, 130, 246, 0.1);
            padding: 10px 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 3px solid #3b82f6;
        }
        .table-status {
            background: #3a3a3a;
            padding: 8px 12px;
            border-radius: 5px;
            margin: 5px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .table-status.exists {
            border-left: 3px solid #10b981;
        }
        .table-status.missing {
            border-left: 3px solid #ef4444;
        }
        .table-status.created {
            border-left: 3px solid #f59e0b;
        }
        pre {
            background: #1a1a1a;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 12px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #6366f1;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #4f46e5;
            transform: translateY(-2px);
        }
        .btn-success {
            background: #10b981;
        }
        .btn-success:hover {
            background: #059669;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: #3a3a3a;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #6366f1;
        }
        .stat-label {
            color: #9ca3af;
            font-size: 14px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔧 GameDev Academy - Correção de Tabelas</h1>";

try {
    $db = Database::getInstance();
    $pdo = $db->getPdo();
    
    echo "<div class='info'>📊 Verificando estrutura do banco de dados...</div>";
    
    // Lista de todas as tabelas necessárias
    $requiredTables = [
        'users' => 'Usuários do sistema',
        'levels' => 'Níveis de experiência',
        'achievements' => 'Conquistas disponíveis',
        'user_achievements' => 'Conquistas dos usuários',
        'categories' => 'Categorias de cursos',
        'courses' => 'Cursos disponíveis',
        'modules' => 'Módulos dos cursos',
        'lessons' => 'Lições dos módulos',
        'enrollments' => 'Matrículas em cursos',
        'lesson_progress' => 'Progresso nas lições',
        'quizzes' => 'Quizzes das lições',
        'quiz_questions' => 'Questões dos quizzes',
        'quiz_attempts' => 'Tentativas de quiz',
        'news' => 'Notícias e atualizações',
        'comments' => 'Comentários',
        'xp_history' => 'Histórico de XP',
        'coin_transactions' => 'Transações de moedas',
        'weekly_leaderboard' => 'Ranking semanal',
        'global_leaderboard' => 'Ranking global',
        'notifications' => 'Notificações',
        'password_resets' => 'Recuperação de senha',
        'user_sessions' => 'Sessões de usuário',
        'settings' => 'Configurações do sistema',
        'activity_logs' => 'Logs de atividade',
        'course_reviews' => 'Avaliações de cursos'
    ];
    
    // Verificar quais tabelas existem
    $stmt = $pdo->query("SHOW TABLES");
    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $missingTables = [];
    $existingCount = 0;
    
    echo "<h2>📋 Status das Tabelas:</h2>";
    
    foreach ($requiredTables as $table => $description) {
        if (in_array($table, $existingTables)) {
            echo "<div class='table-status exists'>
                    <span>✅ {$table} - {$description}</span>
                    <span style='color: #10b981;'>Existe</span>
                  </div>";
            $existingCount++;
        } else {
            echo "<div class='table-status missing'>
                    <span>❌ {$table} - {$description}</span>
                    <span style='color: #ef4444;'>Faltando</span>
                  </div>";
            $missingTables[] = $table;
        }
    }
    
    // Estatísticas
    $totalRequired = count($requiredTables);
    $totalMissing = count($missingTables);
    
    echo "<div class='stats'>
            <div class='stat-card'>
                <div class='stat-value'>{$totalRequired}</div>
                <div class='stat-label'>Total Necessário</div>
            </div>
            <div class='stat-card'>
                <div class='stat-value'>{$existingCount}</div>
                <div class='stat-label'>Existentes</div>
            </div>
            <div class='stat-card'>
                <div class='stat-value'>{$totalMissing}</div>
                <div class='stat-label'>Faltando</div>
            </div>
          </div>";
    
    // Se houver tabelas faltando, criar elas
    if (!empty($missingTables)) {
        echo "<h2>🔨 Criando Tabelas Faltantes:</h2>";
        
        // Desabilitar verificação de foreign keys temporariamente
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        foreach ($missingTables as $table) {
            try {
                $created = createMissingTable($pdo, $table);
                if ($created) {
                    echo "<div class='success'>✅ Tabela '{$table}' criada com sucesso!</div>";
                } else {
                    echo "<div class='warning'>⚠️ Tabela '{$table}' - SQL não definido</div>";
                }
            } catch (PDOException $e) {
                echo "<div class='error'>❌ Erro ao criar tabela '{$table}': " . $e->getMessage() . "</div>";
            }
        }
        
        // Reabilitar verificação de foreign keys
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        
        // Inserir dados iniciais se necessário
        echo "<h2>📝 Verificando Dados Iniciais:</h2>";
        insertInitialDataIfNeeded($pdo);
        
    } else {
        echo "<div class='success'>
                <h3>✅ Todas as tabelas necessárias já existem!</h3>
                <p>O banco de dados está completo e pronto para uso.</p>
              </div>";
    }
    
    // Verificação final
    echo "<h2>📊 Verificação Final:</h2>";
    
    $stmt = $pdo->query("SHOW TABLES");
    $finalTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $finalCount = count($finalTables);
    
    echo "<div class='info'>
            Total de tabelas no banco: <strong>{$finalCount}</strong>
          </div>";
    
    // Verificar registros
    echo "<h3>📈 Contagem de Registros:</h3>";
    $importantTables = ['users', 'courses', 'categories', 'levels', 'achievements', 'news', 'settings'];
    
    foreach ($importantTables as $table) {
        if (in_array($table, $finalTables)) {
            try {
                $count = $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
                echo "<div class='table-status exists'>
                        <span>{$table}</span>
                        <span><strong>{$count}</strong> registros</span>
                      </div>";
            } catch (Exception $e) {
                echo "<div class='table-status missing'>
                        <span>{$table}</span>
                        <span>Erro ao contar</span>
                      </div>";
            }
        }
    }
    
    echo "<div class='success' style='margin-top: 30px; text-align: center;'>
            <h3>🎉 Correção Concluída!</h3>
            <p>O banco de dados foi verificado e corrigido.</p>
            <a href='public/' class='btn'>🏠 Ir para o Site</a>
            <a href='public/login' class='btn'>🔐 Fazer Login</a>
            <a href='public/register' class='btn btn-success'>📝 Criar Conta</a>
          </div>";
    
} catch (Exception $e) {
    echo "<div class='error'>
            <h3>❌ Erro Fatal:</h3>
            <pre>" . $e->getMessage() . "</pre>
            <pre>" . $e->getTraceAsString() . "</pre>
          </div>";
}

echo "</div></body></html>";

/**
 * Função para criar tabela específica que está faltando
 */
function createMissingTable($pdo, $tableName) {
    $sql = "";
    
    switch ($tableName) {
        case 'lesson_progress':
            $sql = "CREATE TABLE IF NOT EXISTS lesson_progress (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                lesson_id INT NOT NULL,
                is_completed TINYINT(1) DEFAULT 0,
                progress_percentage INT DEFAULT 0,
                time_spent INT DEFAULT 0,
                video_progress INT DEFAULT 0,
                attempts INT DEFAULT 1,
                score DECIMAL(5,2),
                completed_at TIMESTAMP NULL,
                xp_earned INT DEFAULT 0,
                coins_earned INT DEFAULT 0,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
                UNIQUE KEY unique_lesson_progress (user_id, lesson_id),
                INDEX idx_user (user_id),
                INDEX idx_lesson (lesson_id),
                INDEX idx_completed (is_completed)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            break;
            
        case 'enrollments':
            $sql = "CREATE TABLE IF NOT EXISTS enrollments (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                course_id INT NOT NULL,
                enrollment_type ENUM('free', 'paid', 'gifted', 'admin') DEFAULT 'free',
                progress_percentage DECIMAL(5,2) DEFAULT 0.00,
                completed_lessons INT DEFAULT 0,
                total_time_spent INT DEFAULT 0,
                current_lesson_id INT,
                certificate_issued TINYINT(1) DEFAULT 0,
                certificate_url VARCHAR(255),
                started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                completed_at TIMESTAMP NULL,
                last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                expiry_date DATE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
                UNIQUE KEY unique_enrollment (user_id, course_id),
                INDEX idx_user (user_id),
                INDEX idx_course (course_id),
                INDEX idx_progress (progress_percentage)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            break;
            
        case 'modules':
            $sql = "CREATE TABLE IF NOT EXISTS modules (
                id INT PRIMARY KEY AUTO_INCREMENT,
                course_id INT NOT NULL,
                title VARCHAR(200) NOT NULL,
                description TEXT,
                order_index INT DEFAULT 0,
                xp_reward INT DEFAULT 50,
                is_published TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
                INDEX idx_course (course_id),
                INDEX idx_order (order_index)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            break;
            
        case 'lessons':
            $sql = "CREATE TABLE IF NOT EXISTS lessons (
                id INT PRIMARY KEY AUTO_INCREMENT,
                module_id INT NOT NULL,
                title VARCHAR(200) NOT NULL,
                slug VARCHAR(200),
                content_type ENUM('video', 'text', 'quiz', 'exercise', 'project', 'live') DEFAULT 'text',
                content LONGTEXT,
                video_url VARCHAR(255),
                video_duration INT DEFAULT 0,
                attachment_url VARCHAR(255),
                duration_minutes INT DEFAULT 0,
                order_index INT DEFAULT 0,
                xp_reward INT DEFAULT 10,
                coin_reward INT DEFAULT 1,
                is_free_preview TINYINT(1) DEFAULT 0,
                is_published TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
                INDEX idx_module (module_id),
                INDEX idx_order (order_index),
                INDEX idx_content_type (content_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            break;
            
        case 'xp_history':
            $sql = "CREATE TABLE IF NOT EXISTS xp_history (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                xp_amount INT NOT NULL,
                action_type VARCHAR(50) NOT NULL,
                description VARCHAR(255),
                reference_id INT,
                reference_type VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_user (user_id),
                INDEX idx_action (action_type),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            break;
            
        case 'weekly_leaderboard':
            $sql = "CREATE TABLE IF NOT EXISTS weekly_leaderboard (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                week_start DATE NOT NULL,
                week_end DATE NOT NULL,
                xp_earned INT DEFAULT 0,
                lessons_completed INT DEFAULT 0,
                quizzes_passed INT DEFAULT 0,
                time_spent INT DEFAULT 0,
                rank INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE KEY unique_weekly (user_id, week_start),
                INDEX idx_user (user_id),
                INDEX idx_week (week_start),
                INDEX idx_xp (xp_earned),
                INDEX idx_rank (rank)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            break;
            
        case 'user_achievements':
            $sql = "CREATE TABLE IF NOT EXISTS user_achievements (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                achievement_id INT NOT NULL,
                progress INT DEFAULT 0,
                unlocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE,
                UNIQUE KEY unique_user_achievement (user_id, achievement_id),
                INDEX idx_user_id (user_id),
                INDEX idx_achievement_id (achievement_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            break;
            
        case 'notifications':
            $sql = "CREATE TABLE IF NOT EXISTS notifications (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                type VARCHAR(50) NOT NULL,
                title VARCHAR(200),
                message TEXT,
                data JSON,
                is_read TINYINT(1) DEFAULT 0,
                read_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_user (user_id),
                INDEX idx_read (is_read),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            break;
            
        case 'quizzes':
            $sql = "CREATE TABLE IF NOT EXISTS quizzes (
                id INT PRIMARY KEY AUTO_INCREMENT,
                lesson_id INT,
                title VARCHAR(200) NOT NULL,
                description TEXT,
                pass_percentage INT DEFAULT 70,
                max_attempts INT DEFAULT 3,
                time_limit INT,
                xp_reward INT DEFAULT 20,
                coin_reward INT DEFAULT 5,
                shuffle_questions TINYINT(1) DEFAULT 0,
                show_correct_answers TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
                INDEX idx_lesson (lesson_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            break;
            
        case 'quiz_questions':
            $sql = "CREATE TABLE IF NOT EXISTS quiz_questions (
                id INT PRIMARY KEY AUTO_INCREMENT,
                quiz_id INT NOT NULL,
                question_type ENUM('multiple_choice', 'true_false', 'short_answer', 'code') DEFAULT 'multiple_choice',
                question TEXT NOT NULL,
                options JSON,
                correct_answer TEXT,
                explanation TEXT,
                points INT DEFAULT 1,
                order_index INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
                INDEX idx_quiz (quiz_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            break;
            
        case 'quiz_attempts':
            $sql = "CREATE TABLE IF NOT EXISTS quiz_attempts (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                quiz_id INT NOT NULL,
                score DECIMAL(5,2),
                passed TINYINT(1) DEFAULT 0,
                time_spent INT,
                answers JSON,
                attempt_number INT DEFAULT 1,
                completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
                INDEX idx_user (user_id),
                INDEX idx_quiz (quiz_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            break;
            
        case 'coin_transactions':
            $sql = "CREATE TABLE IF NOT EXISTS coin_transactions (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                amount INT NOT NULL,
                type ENUM('earned', 'spent', 'gifted', 'refunded') DEFAULT 'earned',
                description VARCHAR(255),
                reference_id INT,
                reference_type VARCHAR(50),
                balance_after INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_user (user_id),
                INDEX idx_type (type),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            break;
            
        case 'global_leaderboard':
            $sql = "CREATE TABLE IF NOT EXISTS global_leaderboard (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL UNIQUE,
                total_xp INT DEFAULT 0,
                total_courses INT DEFAULT 0,
                total_lessons INT DEFAULT 0,
                total_achievements INT DEFAULT 0,
                total_time_spent INT DEFAULT 0,
                rank INT,
                previous_rank INT,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_user (user_id),
                INDEX idx_xp (total_xp),
                INDEX idx_rank (rank)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            break;
            
        case 'comments':
            $sql = "CREATE TABLE IF NOT EXISTS comments (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                commentable_type VARCHAR(50) NOT NULL,
                commentable_id INT NOT NULL,
                parent_id INT,
                content TEXT NOT NULL,
                likes INT DEFAULT 0,
                is_approved TINYINT(1) DEFAULT 1,
                is_pinned TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE,
                INDEX idx_user (user_id),
                INDEX idx_commentable (commentable_type, commentable_id),
                INDEX idx_parent (parent_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            break;
            
        case 'password_resets':
            $sql = "CREATE TABLE IF NOT EXISTS password_resets (
                id INT PRIMARY KEY AUTO_INCREMENT,
                email VARCHAR(100) NOT NULL,
                token VARCHAR(255) NOT NULL,
                expires_at TIMESTAMP NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_email (email),
                INDEX idx_token (token)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            break;
            
        case 'user_sessions':
            $sql = "CREATE TABLE IF NOT EXISTS user_sessions (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                session_token VARCHAR(255) NOT NULL,
                ip_address VARCHAR(45),
                user_agent TEXT,
                last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_user (user_id),
                INDEX idx_token (session_token),
                INDEX idx_expires (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            break;
            
        case 'activity_logs':
            $sql = "CREATE TABLE IF NOT EXISTS activity_logs (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT,
                action VARCHAR(100) NOT NULL,
                description TEXT,
                ip_address VARCHAR(45),
                user_agent TEXT,
                data JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_user (user_id),
                INDEX idx_action (action),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            break;
            
        case 'course_reviews':
            $sql = "CREATE TABLE IF NOT EXISTS course_reviews (
                id INT PRIMARY KEY AUTO_INCREMENT,
                course_id INT NOT NULL,
                user_id INT NOT NULL,
                rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
                title VARCHAR(200),
                review TEXT,
                is_verified_purchase TINYINT(1) DEFAULT 0,
                helpful_count INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE KEY unique_review (course_id, user_id),
                INDEX idx_course (course_id),
                INDEX idx_user (user_id),
                INDEX idx_rating (rating)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            break;
    }
    
    if (!empty($sql)) {
        $pdo->exec($sql);
        return true;
    }
    
    return false;
}

/**
 * Função para inserir dados iniciais se necessário
 */
function insertInitialDataIfNeeded($pdo) {
    // Verificar e criar módulos de exemplo para os cursos
    try {
        $courses = $pdo->query("SELECT id, title FROM courses")->fetchAll();
        
        foreach ($courses as $course) {
            $moduleCount = $pdo->query("SELECT COUNT(*) FROM modules WHERE course_id = {$course['id']}")->fetchColumn();
            
            if ($moduleCount == 0) {
                // Criar módulos de exemplo
                $modules = [
                    ['Introdução', 'Conceitos básicos e configuração do ambiente', 1],
                    ['Fundamentos', 'Principais conceitos e técnicas', 2],
                    ['Prática', 'Exercícios e projetos práticos', 3]
                ];
                
                $stmt = $pdo->prepare("INSERT INTO modules (course_id, title, description, order_index) VALUES (?, ?, ?, ?)");
                
                foreach ($modules as $module) {
                    $stmt->execute([$course['id'], $module[0], $module[1], $module[2]]);
                    $moduleId = $pdo->lastInsertId();
                    
                    // Criar algumas lições de exemplo
                    $lessons = [
                        ['Aula 1 - Introdução', 'text', 'Conteúdo da aula 1', 1],
                        ['Aula 2 - Conceitos', 'text', 'Conteúdo da aula 2', 2],
                        ['Exercício Prático', 'exercise', 'Exercício prático', 3]
                    ];
                    
                    $lessonStmt = $pdo->prepare("INSERT INTO lessons (module_id, title, content_type, content, order_index) VALUES (?, ?, ?, ?, ?)");
                    
                    foreach ($lessons as $lesson) {
                        $lessonStmt->execute([$moduleId, $lesson[0], $lesson[1], $lesson[2], $lesson[3]]);
                    }
                }
                
                echo "<div class='success'>✅ Módulos e lições criados para o curso: {$course['title']}</div>";
            }
        }
    } catch (Exception $e) {
        echo "<div class='warning'>⚠️ Erro ao criar módulos de exemplo: {$e->getMessage()}</div>";
    }
}
?>
<?php
// install/sql/create_tables.php

/**
 * Script de criação das tabelas do banco de dados
 * GameDev Academy v1.0.0
 * 
 * Este script é executado durante a instalação para criar
 * todas as tabelas necessárias e inserir dados iniciais.
 */

/**
 * Função principal que executa toda a configuração do banco de dados
 * @param PDO $pdo Conexão com o banco de dados
 * @return array Array com status e mensagens
 */
function executeDatabaseSetup($pdo) {
    $results = [
        'success' => true,
        'messages' => [],
        'errors' => []
    ];
    
    try {
        // Desabilitar checagem de foreign keys temporariamente
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        // 1. Criar todas as tabelas
        $results['messages'][] = "=== Criando Tabelas ===";
        createAllTables($pdo, $results);
        
        // 2. Inserir dados iniciais
        $results['messages'][] = "=== Inserindo Dados Iniciais ===";
        insertInitialData($pdo, $results);
        
        // Reabilitar checagem de foreign keys
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        
        $results['messages'][] = "=== Instalação concluída com sucesso! ===";
        
    } catch (Exception $e) {
        $results['success'] = false;
        $results['errors'][] = "Erro geral: " . $e->getMessage();
    }
    
    return $results;
}

/**
 * Cria todas as tabelas necessárias
 */
function createAllTables($pdo, &$results) {
    
    // 1. Tabela de Usuários
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                full_name VARCHAR(100) NOT NULL,
                avatar VARCHAR(255) DEFAULT 'default.png',
                role ENUM('student', 'instructor', 'admin') DEFAULT 'student',
                xp_total INT DEFAULT 0,
                level INT DEFAULT 1,
                streak_days INT DEFAULT 0,
                last_activity DATE,
                coins INT DEFAULT 0,
                bio TEXT,
                github_url VARCHAR(255),
                linkedin_url VARCHAR(255),
                portfolio_url VARCHAR(255),
                is_active TINYINT(1) DEFAULT 1,
                email_verified TINYINT(1) DEFAULT 0,
                email_verification_token VARCHAR(255),
                password_reset_token VARCHAR(255),
                password_reset_expires DATETIME,
                last_login DATETIME,
                login_count INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_username (username),
                INDEX idx_email (email),
                INDEX idx_xp (xp_total),
                INDEX idx_level (level),
                INDEX idx_role (role)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results['messages'][] = "✓ Tabela 'users' criada com sucesso";
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro na tabela 'users': " . $e->getMessage();
    }
    
    // 2. Tabela de Níveis
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS levels (
                id INT PRIMARY KEY AUTO_INCREMENT,
                level_number INT UNIQUE NOT NULL,
                title VARCHAR(50) NOT NULL,
                xp_required INT NOT NULL,
                badge_icon VARCHAR(100),
                color VARCHAR(7) DEFAULT '#6366f1',
                perks TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_level_number (level_number),
                INDEX idx_xp_required (xp_required)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results['messages'][] = "✓ Tabela 'levels' criada com sucesso";
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro na tabela 'levels': " . $e->getMessage();
    }
    
    // 3. Tabela de Conquistas
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS achievements (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                icon VARCHAR(100),
                xp_reward INT DEFAULT 0,
                coin_reward INT DEFAULT 0,
                requirement_type ENUM('lessons_completed', 'courses_completed', 'streak', 'xp_earned', 'time_spent', 'perfect_quiz', 'special') NOT NULL,
                requirement_value INT DEFAULT 1,
                is_secret TINYINT(1) DEFAULT 0,
                is_active TINYINT(1) DEFAULT 1,
                order_index INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_requirement_type (requirement_type),
                INDEX idx_is_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results['messages'][] = "✓ Tabela 'achievements' criada com sucesso";
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro na tabela 'achievements': " . $e->getMessage();
    }
    
    // 4. Tabela de Conquistas dos Usuários
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS user_achievements (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results['messages'][] = "✓ Tabela 'user_achievements' criada com sucesso";
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro na tabela 'user_achievements': " . $e->getMessage();
    }
    
    // 5. Tabela de Categorias
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS categories (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(100) UNIQUE NOT NULL,
                description TEXT,
                icon VARCHAR(50),
                color VARCHAR(7) DEFAULT '#6366f1',
                parent_id INT,
                order_index INT DEFAULT 0,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
                INDEX idx_slug (slug),
                INDEX idx_parent (parent_id),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results['messages'][] = "✓ Tabela 'categories' criada com sucesso";
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro na tabela 'categories': " . $e->getMessage();
    }
    
    // 6. Tabela de Cursos
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS courses (
                id INT PRIMARY KEY AUTO_INCREMENT,
                category_id INT,
                title VARCHAR(200) NOT NULL,
                slug VARCHAR(200) UNIQUE NOT NULL,
                description TEXT,
                short_description VARCHAR(500),
                thumbnail VARCHAR(255),
                preview_video VARCHAR(255),
                difficulty ENUM('beginner', 'intermediate', 'advanced', 'expert') DEFAULT 'beginner',
                estimated_hours INT DEFAULT 0,
                xp_reward INT DEFAULT 100,
                coin_reward INT DEFAULT 10,
                certificate_available TINYINT(1) DEFAULT 1,
                is_free TINYINT(1) DEFAULT 0,
                price DECIMAL(10,2) DEFAULT 0.00,
                discount_price DECIMAL(10,2),
                is_featured TINYINT(1) DEFAULT 0,
                is_published TINYINT(1) DEFAULT 0,
                published_at DATETIME,
                instructor_id INT,
                total_students INT DEFAULT 0,
                total_lessons INT DEFAULT 0,
                average_rating DECIMAL(3,2) DEFAULT 0.00,
                total_ratings INT DEFAULT 0,
                completion_rate DECIMAL(5,2) DEFAULT 0.00,
                tags TEXT,
                requirements TEXT,
                what_will_learn TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
                FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_slug (slug),
                INDEX idx_published (is_published),
                INDEX idx_featured (is_featured),
                INDEX idx_category (category_id),
                INDEX idx_instructor (instructor_id),
                INDEX idx_difficulty (difficulty)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results['messages'][] = "✓ Tabela 'courses' criada com sucesso";
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro na tabela 'courses': " . $e->getMessage();
    }
    
    // 7. Tabela de Módulos
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS modules (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results['messages'][] = "✓ Tabela 'modules' criada com sucesso";
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro na tabela 'modules': " . $e->getMessage();
    }
    
    // 8. Tabela de Lições
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS lessons (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results['messages'][] = "✓ Tabela 'lessons' criada com sucesso";
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro na tabela 'lessons': " . $e->getMessage();
    }
    
    // 9. Tabela de Matrículas
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS enrollments (
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
                FOREIGN KEY (current_lesson_id) REFERENCES lessons(id) ON DELETE SET NULL,
                UNIQUE KEY unique_enrollment (user_id, course_id),
                INDEX idx_user (user_id),
                INDEX idx_course (course_id),
                INDEX idx_progress (progress_percentage)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results['messages'][] = "✓ Tabela 'enrollments' criada com sucesso";
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro na tabela 'enrollments': " . $e->getMessage();
    }
    
    // 10. Tabela de Progresso nas Lições
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS lesson_progress (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results['messages'][] = "✓ Tabela 'lesson_progress' criada com sucesso";
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro na tabela 'lesson_progress': " . $e->getMessage();
    }
    
    // 11. Tabela de Quiz
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS quizzes (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results['messages'][] = "✓ Tabela 'quizzes' criada com sucesso";
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro na tabela 'quizzes': " . $e->getMessage();
    }
    
    // 12. Tabela de Notícias
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS news (
                id INT PRIMARY KEY AUTO_INCREMENT,
                title VARCHAR(200) NOT NULL,
                slug VARCHAR(200) UNIQUE NOT NULL,
                excerpt TEXT,
                content LONGTEXT,
                thumbnail VARCHAR(255),
                author_id INT,
                category ENUM('update', 'tutorial', 'news', 'event', 'announcement') DEFAULT 'news',
                tags TEXT,
                is_featured TINYINT(1) DEFAULT 0,
                is_published TINYINT(1) DEFAULT 0,
                allow_comments TINYINT(1) DEFAULT 1,
                views INT DEFAULT 0,
                likes INT DEFAULT 0,
                published_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_slug (slug),
                INDEX idx_published (is_published),
                INDEX idx_featured (is_featured),
                INDEX idx_category (category),
                INDEX idx_author (author_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results['messages'][] = "✓ Tabela 'news' criada com sucesso";
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro na tabela 'news': " . $e->getMessage();
    }
    
    // 13. Tabela de Histórico de XP
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS xp_history (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results['messages'][] = "✓ Tabela 'xp_history' criada com sucesso";
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro na tabela 'xp_history': " . $e->getMessage();
    }
    
    // 14. Tabela de Transações de Moedas
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS coin_transactions (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results['messages'][] = "✓ Tabela 'coin_transactions' criada com sucesso";
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro na tabela 'coin_transactions': " . $e->getMessage();
    }
    
    // 15. Tabela de Ranking Semanal
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS weekly_leaderboard (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results['messages'][] = "✓ Tabela 'weekly_leaderboard' criada com sucesso";
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro na tabela 'weekly_leaderboard': " . $e->getMessage();
    }
    
    // 16. Tabela de Notificações
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS notifications (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results['messages'][] = "✓ Tabela 'notifications' criada com sucesso";
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro na tabela 'notifications': " . $e->getMessage();
    }
    
    // 17. Tabela de Tokens de Recuperação de Senha
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS password_resets (
                id INT PRIMARY KEY AUTO_INCREMENT,
                email VARCHAR(100) NOT NULL,
                token VARCHAR(255) NOT NULL,
                expires_at TIMESTAMP NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_email (email),
                INDEX idx_token (token)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results['messages'][] = "✓ Tabela 'password_resets' criada com sucesso";
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro na tabela 'password_resets': " . $e->getMessage();
    }
    
    // 18. Tabela de Sessões de Usuário
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS user_sessions (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results['messages'][] = "✓ Tabela 'user_sessions' criada com sucesso";
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro na tabela 'user_sessions': " . $e->getMessage();
    }
    
    // 19. Tabela de Configurações
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS settings (
                id INT PRIMARY KEY AUTO_INCREMENT,
                setting_key VARCHAR(100) UNIQUE NOT NULL,
                setting_value TEXT,
                setting_type ENUM('string', 'integer', 'boolean', 'json', 'text') DEFAULT 'string',
                category VARCHAR(50),
                description TEXT,
                is_public TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_key (setting_key),
                INDEX idx_category (category),
                INDEX idx_public (is_public)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results['messages'][] = "✓ Tabela 'settings' criada com sucesso";
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro na tabela 'settings': " . $e->getMessage();
    }
    
    // 20. Tabela de Logs de Atividade
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS activity_logs (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results['messages'][] = "✓ Tabela 'activity_logs' criada com sucesso";
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro na tabela 'activity_logs': " . $e->getMessage();
    }
    
    // 21. Tabela de Comentários
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS comments (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results['messages'][] = "✓ Tabela 'comments' criada com sucesso";
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro na tabela 'comments': " . $e->getMessage();
    }
}

/**
 * Insere os dados iniciais necessários
 */
function insertInitialData($pdo, &$results) {
    
    // 1. Inserir Níveis
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM levels");
        if ($stmt->fetchColumn() == 0) {
            $levels = [
                [1, 'Iniciante', 0, '🌱', '#10b981', 'Acesso aos cursos básicos'],
                [2, 'Aprendiz', 100, '📚', '#6366f1', 'Desbloqueio de conquistas'],
                [3, 'Estudante', 300, '✏️', '#8b5cf6', 'Acesso a quizzes avançados'],
                [4, 'Praticante', 600, '💻', '#ec4899', 'Projetos práticos'],
                [5, 'Desenvolvedor Jr', 1000, '🚀', '#f59e0b', 'Certificados personalizados'],
                [6, 'Desenvolvedor', 1500, '⚡', '#ef4444', 'Acesso a conteúdo exclusivo'],
                [7, 'Desenvolvedor Sr', 2500, '🔥', '#dc2626', 'Mentoria com instrutores'],
                [8, 'Especialista', 4000, '💎', '#0ea5e9', 'Criar seus próprios cursos'],
                [9, 'Mestre', 6000, '👑', '#fbbf24', 'Acesso vitalício'],
                [10, 'Lenda', 10000, '🏆', '#f59e0b', 'Reconhecimento especial']
            ];
            
            $stmt = $pdo->prepare("
                INSERT INTO levels (level_number, title, xp_required, badge_icon, color, perks) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($levels as $level) {
                $stmt->execute($level);
            }
            
            $results['messages'][] = "✓ Níveis inseridos com sucesso";
        }
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro ao inserir níveis: " . $e->getMessage();
    }
    
    // 2. Inserir Conquistas
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM achievements");
        if ($stmt->fetchColumn() == 0) {
            $achievements = [
                ['Primeiro Passo', 'Complete sua primeira lição', '🎯', 10, 5, 'lessons_completed', 1, 1],
                ['Estudante Dedicado', 'Complete 10 lições', '📖', 50, 15, 'lessons_completed', 10, 2],
                ['Maratonista', 'Complete 50 lições', '🏃', 150, 50, 'lessons_completed', 50, 3],
                ['Expert em Lições', 'Complete 100 lições', '🎓', 300, 100, 'lessons_completed', 100, 4],
                ['Formando', 'Complete seu primeiro curso', '🎓', 100, 30, 'courses_completed', 1, 5],
                ['Colecionador', 'Complete 5 cursos', '🏅', 300, 100, 'courses_completed', 5, 6],
                ['Mestre dos Cursos', 'Complete 10 cursos', '🏆', 500, 200, 'courses_completed', 10, 7],
                ['Constante', 'Mantenha um streak de 7 dias', '🔥', 70, 25, 'streak', 7, 8],
                ['Imparável', 'Mantenha um streak de 30 dias', '⚡', 300, 100, 'streak', 30, 9],
                ['Lenda do Streak', 'Mantenha um streak de 100 dias', '🌟', 1000, 500, 'streak', 100, 10],
                ['Primeiros Passos', 'Alcance 100 XP', '⭐', 20, 10, 'xp_earned', 100, 11],
                ['Centurião', 'Alcance 1000 XP', '💯', 50, 20, 'xp_earned', 1000, 12],
                ['Veterano', 'Alcance 5000 XP', '🎖️', 200, 100, 'xp_earned', 5000, 13],
                ['Lendário', 'Alcance 10000 XP', '🌟', 500, 200, 'xp_earned', 10000, 14],
                ['Gênio do Quiz', 'Acerte 100% em um quiz', '🧠', 30, 15, 'perfect_quiz', 1, 15]
            ];
            
            $stmt = $pdo->prepare("
                INSERT INTO achievements (name, description, icon, xp_reward, coin_reward, requirement_type, requirement_value, order_index) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($achievements as $achievement) {
                $stmt->execute($achievement);
            }
            
            $results['messages'][] = "✓ Conquistas inseridas com sucesso";
        }
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro ao inserir conquistas: " . $e->getMessage();
    }
    
    // 3. Inserir Categorias
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
        if ($stmt->fetchColumn() == 0) {
            $categories = [
                ['Phaser 3', 'phaser-3', 'Framework JavaScript para desenvolvimento de jogos 2D', '🎮', '#6366f1', 1],
                ['React', 'react', 'Biblioteca JavaScript para construção de interfaces', '⚛️', '#61dafb', 2],
                ['JavaScript', 'javascript', 'Linguagem de programação essencial para web', '📜', '#f7df1e', 3],
                ['TypeScript', 'typescript', 'JavaScript com tipagem estática', '📘', '#3178c6', 4],
                ['Game Design', 'game-design', 'Princípios e técnicas de design de jogos', '🎨', '#ec4899', 5],
                ['HTML5 Canvas', 'html5-canvas', 'Criação de gráficos e animações com Canvas', '🖼️', '#e34c26', 6],
                ['Node.js', 'nodejs', 'JavaScript no servidor', '💚', '#339933', 7],
                ['WebGL', 'webgl', 'Gráficos 3D na web', '🎯', '#990000', 8],
                ['Projetos Práticos', 'projetos', 'Projetos completos do início ao fim', '🚀', '#10b981', 9]
            ];
            
            $stmt = $pdo->prepare("
                INSERT INTO categories (name, slug, description, icon, color, order_index) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($categories as $category) {
                $stmt->execute($category);
            }
            
            $results['messages'][] = "✓ Categorias inseridas com sucesso";
        }
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro ao inserir categorias: " . $e->getMessage();
    }
    
    // 4. Inserir Cursos de Exemplo
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM courses");
        if ($stmt->fetchColumn() == 0) {
            // Pegar o ID da primeira categoria
            $stmt = $pdo->query("SELECT id FROM categories WHERE slug = 'phaser-3' LIMIT 1");
            $phaserCategoryId = $stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT id FROM categories WHERE slug = 'react' LIMIT 1");
            $reactCategoryId = $stmt->fetchColumn();
            
            $stmt = $pdo->query("SELECT id FROM categories WHERE slug = 'javascript' LIMIT 1");
            $jsCategoryId = $stmt->fetchColumn();
            
            if ($phaserCategoryId) {
                $courses = [
                    [
                        $phaserCategoryId,
                        'Introdução ao Phaser 3',
                        'introducao-phaser-3',
                        'Aprenda os fundamentos do Phaser 3 e crie seu primeiro jogo 2D! Este curso é perfeito para iniciantes que querem entrar no mundo do desenvolvimento de jogos.',
                        'Curso completo para iniciantes em Phaser 3',
                        'beginner',
                        5,
                        200,
                        20,
                        1, // is_free
                        1, // is_featured
                        1, // is_published
                        1, // instructor_id
                        date('Y-m-d H:i:s')
                    ],
                    [
                        $phaserCategoryId,
                        'Criando um Jogo de Plataforma com Phaser',
                        'criando-jogo-plataforma-phaser',
                        'Desenvolva um jogo de plataforma completo do zero, incluindo física, colisões, power-ups e muito mais.',
                        'Crie um jogo de plataforma profissional',
                        'intermediate',
                        10,
                        500,
                        50,
                        0,
                        1,
                        1,
                        1,
                        date('Y-m-d H:i:s')
                    ],
                    [
                        $phaserCategoryId,
                        'Multiplayer com Phaser e Socket.io',
                        'multiplayer-phaser-socketio',
                        'Aprenda a criar jogos multiplayer em tempo real usando Phaser 3 e Socket.io.',
                        'Jogos multiplayer em tempo real',
                        'advanced',
                        15,
                        800,
                        80,
                        0,
                        0,
                        1,
                        1,
                        date('Y-m-d H:i:s')
                    ]
                ];
                
                // Adicionar curso de React se a categoria existir
                if ($reactCategoryId) {
                    $courses[] = [
                        $reactCategoryId,
                        'React do Zero ao Avançado',
                        'react-zero-avancado',
                        'Domine React com hooks, context API, Redux e muito mais. Aprenda a criar aplicações modernas e escaláveis.',
                        'Curso completo de React moderno',
                        'intermediate',
                        15,
                        600,
                        60,
                        0,
                        1,
                        1,
                        1,
                        date('Y-m-d H:i:s')
                    ];
                }
                
                // Adicionar curso de JavaScript se a categoria existir
                if ($jsCategoryId) {
                    $courses[] = [
                        $jsCategoryId,
                        'JavaScript para Jogos',
                        'javascript-para-jogos',
                        'Aprenda JavaScript com foco em desenvolvimento de jogos. Conceitos essenciais e padrões de projeto.',
                        'JavaScript essencial para game dev',
                        'beginner',
                        8,
                        300,
                        30,
                        1,
                        0,
                        1,
                        1,
                        date('Y-m-d H:i:s')
                    ];
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO courses (
                        category_id, title, slug, description, short_description,
                        difficulty, estimated_hours, xp_reward, coin_reward,
                        is_free, is_featured, is_published, instructor_id, published_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                foreach ($courses as $course) {
                    $stmt->execute($course);
                }
                
                $results['messages'][] = "✓ Cursos de exemplo inseridos com sucesso";
            }
        }
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro ao inserir cursos: " . $e->getMessage();
    }
    
    // 5. Inserir Notícias de Exemplo
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM news");
        if ($stmt->fetchColumn() == 0) {
            $news = [
                [
                    'Bem-vindo ao GameDev Academy!',
                    'bem-vindo-gamedev-academy',
                    'Conheça nossa plataforma de ensino gamificada focada em desenvolvimento de jogos.',
                    '<h2>Bem-vindo à nossa comunidade!</h2>
                    <p>Estamos muito felizes em ter você aqui no GameDev Academy, a plataforma de ensino mais inovadora para desenvolvimento de jogos!</p>
                    <h3>O que oferecemos:</h3>
                    <ul>
                        <li>Cursos práticos e diretos ao ponto</li>
                        <li>Sistema de gamificação completo</li>
                        <li>Projetos reais do início ao fim</li>
                        <li>Comunidade ativa de desenvolvedores</li>
                        <li>Certificados de conclusão</li>
                    </ul>
                    <p>Comece sua jornada hoje mesmo e transforme sua paixão por jogos em habilidades profissionais!</p>',
                    1, // author_id
                    'announcement',
                    1, // is_featured
                    1, // is_published
                    date('Y-m-d H:i:s')
                ],
                [
                    'Novo Curso: Phaser 3 Avançado',
                    'novo-curso-phaser-3-avancado',
                    'Lançamos um novo curso avançado de Phaser 3 com técnicas profissionais.',
                    '<p>Acabamos de lançar nosso curso mais avançado de Phaser 3!</p>
                    <h3>O que você aprenderá:</h3>
                    <ul>
                        <li>Otimização de performance</li>
                        <li>Sistemas de partículas avançados</li>
                        <li>Integração com APIs externas</li>
                        <li>Monetização de jogos</li>
                        <li>Publicação em múltiplas plataformas</li>
                    </ul>
                    <p>Este curso é ideal para desenvolvedores que já dominam o básico e querem levar suas habilidades para o próximo nível.</p>',
                    1,
                    'update',
                    0,
                    1,
                    date('Y-m-d H:i:s')
                ],
                [
                    'Dicas para Iniciantes em Game Dev',
                    'dicas-iniciantes-gamedev',
                    'As melhores dicas para quem está começando no desenvolvimento de jogos.',
                    '<h2>Começando sua jornada no Game Dev</h2>
                    <p>Se você está começando agora, aqui vão algumas dicas valiosas:</p>
                    <ol>
                        <li><strong>Comece pequeno:</strong> Não tente fazer um MMORPG como primeiro projeto</li>
                        <li><strong>Termine seus projetos:</strong> Um jogo simples finalizado vale mais que 10 projetos abandonados</li>
                        <li><strong>Aprenda fazendo:</strong> A prática é fundamental no desenvolvimento de jogos</li>
                        <li><strong>Participe da comunidade:</strong> Troque experiências com outros desenvolvedores</li>
                        <li><strong>Use ferramentas adequadas:</strong> Phaser 3 é perfeito para jogos 2D na web</li>
                    </ol>
                    <p>Lembre-se: todo grande desenvolvedor começou do zero!</p>',
                    1,
                    'tutorial',
                    0,
                    1,
                    date('Y-m-d H:i:s')
                ],
                [
                    'Evento: Game Jam Mensal',
                    'evento-game-jam-mensal',
                    'Participe da nossa Game Jam mensal e ganhe prêmios incríveis!',
                    '<p>Todo mês realizamos uma Game Jam exclusiva para membros da plataforma!</p>
                    <h3>Como funciona:</h3>
                    <ul>
                        <li>Tema revelado na sexta-feira às 18h</li>
                        <li>48 horas para desenvolver seu jogo</li>
                        <li>Votação da comunidade na segunda-feira</li>
                        <li>Prêmios incríveis para os 3 primeiros colocados</li>
                    </ul>
                    <h3>Prêmios:</h3>
                    <ul>
                        <li>🥇 1º lugar: 1000 moedas + Badge exclusiva + 1 mês de acesso Premium</li>
                        <li>🥈 2º lugar: 500 moedas + Badge exclusiva</li>
                        <li>🥉 3º lugar: 250 moedas + Badge exclusiva</li>
                    </ul>
                    <p>Não perca essa oportunidade de testar suas habilidades!</p>',
                    1,
                    'event',
                    0,
                    1,
                    date('Y-m-d H:i:s')
                ]
            ];
            
            $stmt = $pdo->prepare("
                INSERT INTO news (title, slug, excerpt, content, author_id, category, is_featured, is_published, published_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($news as $article) {
                $stmt->execute($article);
            }
            
            $results['messages'][] = "✓ Notícias de exemplo inseridas com sucesso";
        }
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro ao inserir notícias: " . $e->getMessage();
    }
    
    // 6. Inserir Configurações do Sistema
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM settings");
        if ($stmt->fetchColumn() == 0) {
            $settings = [
                ['site_name', 'GameDev Academy', 'string', 'general', 'Nome do site', 1],
                ['site_description', 'Plataforma de ensino gamificada para desenvolvimento de jogos', 'text', 'general', 'Descrição do site', 1],
                ['site_keywords', 'gamedev, phaser, react, javascript, cursos, jogos', 'text', 'general', 'Palavras-chave do site', 0],
                ['maintenance_mode', 'false', 'boolean', 'general', 'Modo de manutenção', 0],
                ['registration_enabled', 'true', 'boolean', 'auth', 'Permitir novos registros', 0],
                ['email_verification_required', 'false', 'boolean', 'auth', 'Exigir verificação de e-mail', 0],
                ['default_xp_lesson', '10', 'integer', 'gamification', 'XP padrão por lição', 0],
                ['default_xp_course', '100', 'integer', 'gamification', 'XP padrão por curso', 0],
                ['default_xp_quiz', '20', 'integer', 'gamification', 'XP padrão por quiz', 0],
                ['default_coins_lesson', '1', 'integer', 'gamification', 'Moedas padrão por lição', 0],
                ['default_coins_course', '10', 'integer', 'gamification', 'Moedas padrão por curso', 0],
                ['default_coins_quiz', '5', 'integer', 'gamification', 'Moedas padrão por quiz', 0],
                ['streak_bonus_xp', '5', 'integer', 'gamification', 'XP bônus por dia de streak', 0],
                ['max_daily_xp', '500', 'integer', 'gamification', 'XP máximo por dia', 0],
                ['leaderboard_size', '10', 'integer', 'gamification', 'Tamanho do ranking', 0]
            ];
            
            $stmt = $pdo->prepare("
                INSERT INTO settings (setting_key, setting_value, setting_type, category, description, is_public) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($settings as $setting) {
                $stmt->execute($setting);
            }
            
            $results['messages'][] = "✓ Configurações do sistema inseridas com sucesso";
        }
    } catch (PDOException $e) {
        $results['errors'][] = "✗ Erro ao inserir configurações: " . $e->getMessage();
    }
}

// Se este arquivo for executado diretamente (para testes)
if (basename($_SERVER['PHP_SELF']) == 'create_tables.php') {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Criação de Tabelas - GameDev Academy</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                background: #1a1a1a; 
                color: #fff; 
                padding: 20px; 
            }
            .container { 
                max-width: 800px; 
                margin: 0 auto; 
                background: #2a2a2a; 
                padding: 20px; 
                border-radius: 10px; 
            }
            h1 { color: #6366f1; }
            .success { color: #10b981; }
            .error { color: #ef4444; }
            .message { 
                padding: 5px 10px; 
                margin: 5px 0; 
                background: #3a3a3a; 
                border-radius: 5px; 
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>🎮 GameDev Academy - Teste de Criação de Tabelas</h1>";
    
    try {
        // Configurações de teste
        $host = 'localhost';
        $dbname = 'gamedev_academy';
        $user = 'root';
        $pass = '';
        
        $pdo = new PDO(
            "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        $results = executeDatabaseSetup($pdo);
        
        foreach ($results['messages'] as $msg) {
            echo "<div class='message success'>$msg</div>";
        }
        
        foreach ($results['errors'] as $error) {
            echo "<div class='message error'>$error</div>";
        }
        
        if ($results['success']) {
            echo "<h2 class='success'>✅ Banco de dados configurado com sucesso!</h2>";
        } else {
            echo "<h2 class='error'>❌ Houve erros durante a configuração</h2>";
        }
        
    } catch (Exception $e) {
        echo "<div class='message error'>Erro fatal: " . $e->getMessage() . "</div>";
    }
    
    echo "</div></body></html>";
}
?>
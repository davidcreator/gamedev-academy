<?php
/**
 * =====================================================
 * GameDev Academy - Script de Criação Completo v2.0
 * =====================================================
 * 
 * Script completo com 51 tabelas para instalação
 * Compatível com PHP 7.4+ e MySQL 5.7+
 * 
 * @version 2.0.0
 * @author GameDev Academy Team
 * @date 2024
 */

/**
 * Função principal de setup do banco de dados
 * 
 * @param PDO $pdo Conexão PDO com o banco
 * @return array Status e mensagens da instalação
 */
function executeDatabaseSetup($pdo) {
    $results = [
        'success' => true,
        'messages' => [],
        'errors' => [],
        'warnings' => [],
        'stats' => [
            'tables_created' => 0,
            'tables_skipped' => 0,
            'data_inserted' => 0
        ]
    ];
    
    try {
        // Desabilitar verificação de chaves estrangeiras
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $pdo->exec("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO'");
        
        $results['messages'][] = "=== Iniciando instalação do GameDev Academy ===";
        $results['messages'][] = "Versão: 2.0.0 | Data: " . date('Y-m-d H:i:s');
        
        // Criar todas as tabelas
        $results['messages'][] = "\n=== CRIANDO TABELAS ===";
        createAllTables($pdo, $results);
        
        // Inserir dados iniciais
        $results['messages'][] = "\n=== INSERINDO DADOS INICIAIS ===";
        insertInitialData($pdo, $results);
        
        // Reabilitar verificação
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        
        // Verificar integridade
        verifyInstallation($pdo, $results);
        
        $results['messages'][] = "\n=== INSTALAÇÃO CONCLUÍDA ===";
        $results['messages'][] = "✅ Tabelas criadas: " . $results['stats']['tables_created'];
        $results['messages'][] = "⏭️  Tabelas existentes: " . $results['stats']['tables_skipped'];
        $results['messages'][] = "📝 Registros inseridos: " . $results['stats']['data_inserted'];
        
    } catch (Exception $e) {
        $results['success'] = false;
        $results['errors'][] = "❌ Erro fatal: " . $e->getMessage();
        $results['errors'][] = "Stack trace: " . $e->getTraceAsString();
    }
    
    return $results;
}

/**
 * Cria todas as tabelas necessárias
 */
function createAllTables($pdo, &$results) {
    
    // ==================================================
    // TABELAS PRINCIPAIS (1-10)
    // ==================================================
    
    // 1. Users
    createTable($pdo, $results, 'users', "
        CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            name VARCHAR(100),
            nickname VARCHAR(50),
            avatar VARCHAR(255) DEFAULT 'default.png',
            cover_image VARCHAR(255),
            bio TEXT,
            location VARCHAR(100),
            occupation VARCHAR(100),
            skills TEXT,
            interests TEXT,
            role ENUM('student', 'instructor', 'moderator', 'admin') DEFAULT 'student',
            xp_total INT DEFAULT 0,
            level INT DEFAULT 1,
            streak_days INT DEFAULT 0,
            best_streak INT DEFAULT 0,
            last_activity DATE,
            coins INT DEFAULT 0,
            gems INT DEFAULT 0,
            github VARCHAR(50),
            github_url VARCHAR(255),
            gitlab VARCHAR(50),
            bitbucket VARCHAR(50),
            linkedin VARCHAR(100),
            linkedin_url VARCHAR(255),
            twitter VARCHAR(50),
            youtube VARCHAR(100),
            twitch VARCHAR(50),
            discord VARCHAR(100),
            instagram VARCHAR(50),
            facebook VARCHAR(100),
            tiktok VARCHAR(50),
            steam VARCHAR(50),
            itch_io VARCHAR(50),
            website VARCHAR(255),
            portfolio VARCHAR(255),
            portfolio_url VARCHAR(255),
            email_notifications TINYINT(1) DEFAULT 1,
            push_notifications TINYINT(1) DEFAULT 1,
            newsletter TINYINT(1) DEFAULT 1,
            marketing_emails TINYINT(1) DEFAULT 0,
            profile_public TINYINT(1) DEFAULT 1,
            show_email TINYINT(1) DEFAULT 0,
            show_social TINYINT(1) DEFAULT 1,
            show_activity TINYINT(1) DEFAULT 1,
            show_achievements TINYINT(1) DEFAULT 1,
            theme VARCHAR(20) DEFAULT 'system',
            language VARCHAR(10) DEFAULT 'pt-BR',
            timezone VARCHAR(50) DEFAULT 'America/Sao_Paulo',
            is_active TINYINT(1) DEFAULT 1,
            is_verified TINYINT(1) DEFAULT 0,
            is_premium TINYINT(1) DEFAULT 0,
            premium_expires_at DATETIME,
            email_verified TINYINT(1) DEFAULT 0,
            email_verified_at DATETIME,
            email_verification_token VARCHAR(255),
            password_reset_token VARCHAR(255),
            password_reset_expires DATETIME,
            two_factor_enabled TINYINT(1) DEFAULT 0,
            two_factor_secret VARCHAR(255),
            last_login DATETIME,
            last_login_ip VARCHAR(45),
            login_count INT DEFAULT 0,
            failed_login_attempts INT DEFAULT 0,
            locked_until DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL,
            INDEX idx_username (username),
            INDEX idx_email (email),
            INDEX idx_xp (xp_total),
            INDEX idx_level (level),
            INDEX idx_role (role),
            INDEX idx_active (is_active),
            INDEX idx_streak (streak_days)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 2. Levels
    createTable($pdo, $results, 'levels', "
        CREATE TABLE IF NOT EXISTS levels (
            id INT PRIMARY KEY AUTO_INCREMENT,
            level_number INT UNIQUE NOT NULL,
            title VARCHAR(50) NOT NULL,
            title_en VARCHAR(50),
            xp_required INT NOT NULL,
            badge_icon VARCHAR(100),
            badge_image VARCHAR(255),
            color VARCHAR(7) DEFAULT '#6366f1',
            gradient VARCHAR(100),
            perks TEXT,
            perks_json JSON,
            unlock_features TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_level_number (level_number),
            INDEX idx_xp_required (xp_required)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 3. Achievements
    createTable($pdo, $results, 'achievements', "
        CREATE TABLE IF NOT EXISTS achievements (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            name_en VARCHAR(100),
            slug VARCHAR(100) UNIQUE,
            description TEXT,
            description_en TEXT,
            icon VARCHAR(100),
            image VARCHAR(255),
            xp_reward INT DEFAULT 0,
            coin_reward INT DEFAULT 0,
            gem_reward INT DEFAULT 0,
            category ENUM('learning', 'social', 'challenge', 'special', 'secret') DEFAULT 'learning',
            requirement_type ENUM('lessons_completed', 'courses_completed', 'streak', 'xp_earned', 
                                  'time_spent', 'perfect_quiz', 'projects_completed', 'comments',
                                  'followers', 'reviews', 'special') NOT NULL,
            requirement_value INT DEFAULT 1,
            requirement_data JSON,
            is_secret TINYINT(1) DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            is_limited TINYINT(1) DEFAULT 0,
            available_until DATETIME,
            order_index INT DEFAULT 0,
            rarity ENUM('common', 'uncommon', 'rare', 'epic', 'legendary') DEFAULT 'common',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_requirement_type (requirement_type),
            INDEX idx_category (category),
            INDEX idx_is_active (is_active),
            INDEX idx_rarity (rarity)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 4. User Achievements
    createTable($pdo, $results, 'user_achievements', "
        CREATE TABLE IF NOT EXISTS user_achievements (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            achievement_id INT NOT NULL,
            progress INT DEFAULT 0,
            progress_max INT DEFAULT 100,
            is_unlocked TINYINT(1) DEFAULT 0,
            unlocked_at TIMESTAMP NULL,
            notified TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_achievement (user_id, achievement_id),
            INDEX idx_user_id (user_id),
            INDEX idx_achievement_id (achievement_id),
            INDEX idx_unlocked (is_unlocked)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 5. Categories
    createTable($pdo, $results, 'categories', "
        CREATE TABLE IF NOT EXISTS categories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            name_en VARCHAR(100),
            slug VARCHAR(100) UNIQUE NOT NULL,
            description TEXT,
            description_en TEXT,
            icon VARCHAR(50),
            image VARCHAR(255),
            color VARCHAR(7) DEFAULT '#6366f1',
            gradient VARCHAR(100),
            parent_id INT,
            order_index INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            is_featured TINYINT(1) DEFAULT 0,
            course_count INT DEFAULT 0,
            meta_title VARCHAR(200),
            meta_description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
            INDEX idx_slug (slug),
            INDEX idx_parent (parent_id),
            INDEX idx_active (is_active),
            INDEX idx_order (order_index)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 6. Courses
    createTable($pdo, $results, 'courses', "
        CREATE TABLE IF NOT EXISTS courses (
            id INT PRIMARY KEY AUTO_INCREMENT,
            category_id INT,
            instructor_id INT,
            title VARCHAR(200) NOT NULL,
            title_en VARCHAR(200),
            slug VARCHAR(200) UNIQUE NOT NULL,
            description TEXT,
            description_en TEXT,
            short_description VARCHAR(500),
            thumbnail VARCHAR(255),
            cover_image VARCHAR(255),
            preview_video VARCHAR(255),
            trailer_url VARCHAR(255),
            difficulty ENUM('beginner', 'intermediate', 'advanced', 'expert') DEFAULT 'beginner',
            language VARCHAR(10) DEFAULT 'pt-BR',
            estimated_hours INT DEFAULT 0,
            total_lessons INT DEFAULT 0,
            total_modules INT DEFAULT 0,
            total_quizzes INT DEFAULT 0,
            total_projects INT DEFAULT 0,
            xp_reward INT DEFAULT 100,
            coin_reward INT DEFAULT 10,
            certificate_available TINYINT(1) DEFAULT 1,
            certificate_template VARCHAR(100) DEFAULT 'default',
            is_free TINYINT(1) DEFAULT 0,
            price DECIMAL(10,2) DEFAULT 0.00,
            original_price DECIMAL(10,2),
            discount_price DECIMAL(10,2),
            discount_ends_at DATETIME,
            currency VARCHAR(3) DEFAULT 'BRL',
            is_featured TINYINT(1) DEFAULT 0,
            is_popular TINYINT(1) DEFAULT 0,
            is_new TINYINT(1) DEFAULT 0,
            is_published TINYINT(1) DEFAULT 0,
            is_approved TINYINT(1) DEFAULT 1,
            published_at DATETIME,
            total_students INT DEFAULT 0,
            total_completions INT DEFAULT 0,
            average_rating DECIMAL(3,2) DEFAULT 0.00,
            total_ratings INT DEFAULT 0,
            completion_rate DECIMAL(5,2) DEFAULT 0.00,
            tags TEXT,
            requirements TEXT,
            requirements_json JSON,
            what_will_learn TEXT,
            what_will_learn_json JSON,
            target_audience TEXT,
            meta_title VARCHAR(200),
            meta_description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
            FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_slug (slug),
            INDEX idx_published (is_published),
            INDEX idx_featured (is_featured),
            INDEX idx_category (category_id),
            INDEX idx_instructor (instructor_id),
            INDEX idx_difficulty (difficulty),
            INDEX idx_free (is_free),
            INDEX idx_rating (average_rating)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 7. Modules
    createTable($pdo, $results, 'modules', "
        CREATE TABLE IF NOT EXISTS modules (
            id INT PRIMARY KEY AUTO_INCREMENT,
            course_id INT NOT NULL,
            title VARCHAR(200) NOT NULL,
            title_en VARCHAR(200),
            description TEXT,
            order_index INT DEFAULT 0,
            xp_reward INT DEFAULT 50,
            estimated_minutes INT DEFAULT 0,
            is_published TINYINT(1) DEFAULT 1,
            is_free_preview TINYINT(1) DEFAULT 0,
            unlock_after_module INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
            FOREIGN KEY (unlock_after_module) REFERENCES modules(id) ON DELETE SET NULL,
            INDEX idx_course (course_id),
            INDEX idx_order (order_index)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 8. Lessons
    createTable($pdo, $results, 'lessons', "
        CREATE TABLE IF NOT EXISTS lessons (
            id INT PRIMARY KEY AUTO_INCREMENT,
            module_id INT NOT NULL,
            title VARCHAR(200) NOT NULL,
            title_en VARCHAR(200),
            slug VARCHAR(200),
            content_type ENUM('video', 'text', 'quiz', 'exercise', 'project', 'live', 'download') DEFAULT 'text',
            content LONGTEXT,
            content_en LONGTEXT,
            summary TEXT,
            video_url VARCHAR(255),
            video_provider ENUM('youtube', 'vimeo', 'cloudflare', 'bunny', 'self') DEFAULT 'youtube',
            video_id VARCHAR(100),
            video_duration INT DEFAULT 0,
            video_thumbnail VARCHAR(255),
            attachment_url VARCHAR(255),
            attachment_name VARCHAR(200),
            attachment_size INT,
            duration_minutes INT DEFAULT 0,
            order_index INT DEFAULT 0,
            xp_reward INT DEFAULT 10,
            coin_reward INT DEFAULT 1,
            is_free_preview TINYINT(1) DEFAULT 0,
            is_published TINYINT(1) DEFAULT 1,
            is_downloadable TINYINT(1) DEFAULT 0,
            requires_completion TINYINT(1) DEFAULT 1,
            min_watch_percentage INT DEFAULT 80,
            view_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
            INDEX idx_module (module_id),
            INDEX idx_order (order_index),
            INDEX idx_content_type (content_type),
            INDEX idx_slug (slug)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 9. Lesson Resources
    createTable($pdo, $results, 'lesson_resources', "
        CREATE TABLE IF NOT EXISTS lesson_resources (
            id INT PRIMARY KEY AUTO_INCREMENT,
            lesson_id INT NOT NULL,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            type ENUM('file', 'link', 'code', 'image') DEFAULT 'file',
            url VARCHAR(500) NOT NULL,
            file_name VARCHAR(200),
            file_size INT,
            file_type VARCHAR(50),
            order_index INT DEFAULT 0,
            download_count INT DEFAULT 0,
            is_premium TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
            INDEX idx_lesson (lesson_id),
            INDEX idx_type (type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 10. Enrollments
    createTable($pdo, $results, 'enrollments', "
        CREATE TABLE IF NOT EXISTS enrollments (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            course_id INT NOT NULL,
            enrollment_type ENUM('free', 'paid', 'gifted', 'scholarship', 'admin', 'subscription') DEFAULT 'free',
            payment_id INT,
            progress_percentage DECIMAL(5,2) DEFAULT 0.00,
            completed_lessons INT DEFAULT 0,
            total_lessons INT DEFAULT 0,
            total_time_spent INT DEFAULT 0,
            current_lesson_id INT,
            current_module_id INT,
            status ENUM('active', 'completed', 'expired', 'cancelled', 'paused') DEFAULT 'active',
            certificate_issued TINYINT(1) DEFAULT 0,
            certificate_id INT,
            certificate_url VARCHAR(255),
            rating DECIMAL(2,1),
            review_id INT,
            notes TEXT,
            started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            completed_at TIMESTAMP NULL,
            last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            expiry_date DATE,
            reminder_sent TINYINT(1) DEFAULT 0,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
            FOREIGN KEY (current_lesson_id) REFERENCES lessons(id) ON DELETE SET NULL,
            UNIQUE KEY unique_enrollment (user_id, course_id),
            INDEX idx_user (user_id),
            INDEX idx_course (course_id),
            INDEX idx_status (status),
            INDEX idx_progress (progress_percentage)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // ==================================================
    // TABELAS DE PROGRESSO (11-15)
    // ==================================================
    
    // 11. Lesson Progress
    createTable($pdo, $results, 'lesson_progress', "
        CREATE TABLE IF NOT EXISTS lesson_progress (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            lesson_id INT NOT NULL,
            enrollment_id INT,
            is_completed TINYINT(1) DEFAULT 0,
            progress_percentage INT DEFAULT 0,
            time_spent INT DEFAULT 0,
            video_progress INT DEFAULT 0,
            video_current_time INT DEFAULT 0,
            attempts INT DEFAULT 1,
            score DECIMAL(5,2),
            max_score DECIMAL(5,2),
            completed_at TIMESTAMP NULL,
            xp_earned INT DEFAULT 0,
            coins_earned INT DEFAULT 0,
            notes TEXT,
            bookmarked TINYINT(1) DEFAULT 0,
            last_position JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
            FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE SET NULL,
            UNIQUE KEY unique_lesson_progress (user_id, lesson_id),
            INDEX idx_user (user_id),
            INDEX idx_lesson (lesson_id),
            INDEX idx_completed (is_completed)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 12. Module Progress
    createTable($pdo, $results, 'module_progress', "
        CREATE TABLE IF NOT EXISTS module_progress (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            module_id INT NOT NULL,
            enrollment_id INT,
            is_completed TINYINT(1) DEFAULT 0,
            progress_percentage DECIMAL(5,2) DEFAULT 0.00,
            completed_lessons INT DEFAULT 0,
            total_lessons INT DEFAULT 0,
            started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            completed_at TIMESTAMP NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
            FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE SET NULL,
            UNIQUE KEY unique_module_progress (user_id, module_id),
            INDEX idx_user (user_id),
            INDEX idx_module (module_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 13. User Daily Stats
    createTable($pdo, $results, 'user_daily_stats', "
        CREATE TABLE IF NOT EXISTS user_daily_stats (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            date DATE NOT NULL,
            xp_earned INT DEFAULT 0,
            coins_earned INT DEFAULT 0,
            lessons_completed INT DEFAULT 0,
            time_spent INT DEFAULT 0,
            quizzes_completed INT DEFAULT 0,
            projects_completed INT DEFAULT 0,
            streak_maintained TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_date (user_id, date),
            INDEX idx_user (user_id),
            INDEX idx_date (date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 14. Learning Streaks
    createTable($pdo, $results, 'learning_streaks', "
        CREATE TABLE IF NOT EXISTS learning_streaks (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE,
            streak_length INT DEFAULT 1,
            is_active TINYINT(1) DEFAULT 1,
            broken_reason VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user (user_id),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 15. XP Transactions
    createTable($pdo, $results, 'xp_transactions', "
        CREATE TABLE IF NOT EXISTS xp_transactions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            amount INT NOT NULL,
            type ENUM('earned', 'bonus', 'achievement', 'level_up', 'streak', 'challenge', 'refund', 'admin') DEFAULT 'earned',
            source VARCHAR(50) NOT NULL,
            source_id INT,
            description VARCHAR(255),
            balance_after INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user (user_id),
            INDEX idx_type (type),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Continua com as outras tabelas... (16-51)
    // Por limitação de espaço, vou criar uma função auxiliar
    
    createQuizTables($pdo, $results);
    createCertificateAndReviewTables($pdo, $results);
    createProjectTables($pdo, $results);
    createGamificationTables($pdo, $results);
    createShopTables($pdo, $results);
    createSystemTables($pdo, $results);
    createSocialTables($pdo, $results);
}

/**
 * Cria tabelas de Quiz (16-20)
 */
function createQuizTables($pdo, &$results) {
    // 16. Quizzes
    createTable($pdo, $results, 'quizzes', "
        CREATE TABLE IF NOT EXISTS quizzes (
            id INT PRIMARY KEY AUTO_INCREMENT,
            lesson_id INT,
            course_id INT,
            module_id INT,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            instructions TEXT,
            type ENUM('lesson', 'module', 'course', 'standalone', 'certification') DEFAULT 'lesson',
            time_limit INT DEFAULT 0,
            passing_score INT DEFAULT 70,
            max_attempts INT DEFAULT 0,
            shuffle_questions TINYINT(1) DEFAULT 1,
            shuffle_options TINYINT(1) DEFAULT 1,
            show_correct_answers TINYINT(1) DEFAULT 1,
            show_explanations TINYINT(1) DEFAULT 1,
            xp_reward INT DEFAULT 20,
            coin_reward INT DEFAULT 5,
            bonus_xp_perfect INT DEFAULT 50,
            question_count INT DEFAULT 0,
            is_published TINYINT(1) DEFAULT 1,
            is_required TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
            FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
            INDEX idx_lesson (lesson_id),
            INDEX idx_course (course_id),
            INDEX idx_type (type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 17. Quiz Questions
    createTable($pdo, $results, 'quiz_questions', "
        CREATE TABLE IF NOT EXISTS quiz_questions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            quiz_id INT NOT NULL,
            question TEXT NOT NULL,
            question_type ENUM('multiple_choice', 'true_false', 'multiple_answer', 'fill_blank', 
                               'matching', 'ordering', 'code', 'short_answer') DEFAULT 'multiple_choice',
            explanation TEXT,
            hint TEXT,
            points INT DEFAULT 1,
            order_index INT DEFAULT 0,
            image VARCHAR(255),
            code_snippet TEXT,
            code_language VARCHAR(20),
            is_required TINYINT(1) DEFAULT 0,
            difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
            time_limit INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
            INDEX idx_quiz (quiz_id),
            INDEX idx_order (order_index)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 18. Quiz Options
    createTable($pdo, $results, 'quiz_options', "
        CREATE TABLE IF NOT EXISTS quiz_options (
            id INT PRIMARY KEY AUTO_INCREMENT,
            question_id INT NOT NULL,
            option_text TEXT NOT NULL,
            is_correct TINYINT(1) DEFAULT 0,
            explanation TEXT,
            order_index INT DEFAULT 0,
            match_pair VARCHAR(200),
            image VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE,
            INDEX idx_question (question_id),
            INDEX idx_correct (is_correct)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 19. Quiz Attempts
    createTable($pdo, $results, 'quiz_attempts', "
        CREATE TABLE IF NOT EXISTS quiz_attempts (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            quiz_id INT NOT NULL,
            lesson_progress_id INT,
            score DECIMAL(5,2) DEFAULT 0.00,
            max_score DECIMAL(5,2) DEFAULT 100.00,
            percentage DECIMAL(5,2) DEFAULT 0.00,
            passed TINYINT(1) DEFAULT 0,
            is_perfect TINYINT(1) DEFAULT 0,
            time_taken INT DEFAULT 0,
            questions_total INT DEFAULT 0,
            questions_correct INT DEFAULT 0,
            questions_wrong INT DEFAULT 0,
            questions_skipped INT DEFAULT 0,
            xp_earned INT DEFAULT 0,
            coins_earned INT DEFAULT 0,
            attempt_number INT DEFAULT 1,
            answers_data JSON,
            started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            completed_at TIMESTAMP NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
            FOREIGN KEY (lesson_progress_id) REFERENCES lesson_progress(id) ON DELETE SET NULL,
            INDEX idx_user (user_id),
            INDEX idx_quiz (quiz_id),
            INDEX idx_passed (passed)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 20. Quiz Answers
    createTable($pdo, $results, 'quiz_answers', "
        CREATE TABLE IF NOT EXISTS quiz_answers (
            id INT PRIMARY KEY AUTO_INCREMENT,
            attempt_id INT NOT NULL,
            question_id INT NOT NULL,
            selected_option_id INT,
            selected_options JSON,
            answer_text TEXT,
            is_correct TINYINT(1) DEFAULT 0,
            points_earned DECIMAL(5,2) DEFAULT 0.00,
            time_spent INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(id) ON DELETE CASCADE,
            FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE,
            FOREIGN KEY (selected_option_id) REFERENCES quiz_options(id) ON DELETE SET NULL,
            INDEX idx_attempt (attempt_id),
            INDEX idx_question (question_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

/**
 * Cria tabelas de Certificados e Reviews (21-25)
 */
function createCertificateAndReviewTables($pdo, &$results) {
    // 21. Certificates
    createTable($pdo, $results, 'certificates', "
        CREATE TABLE IF NOT EXISTS certificates (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            course_id INT NOT NULL,
            enrollment_id INT,
            certificate_code VARCHAR(50) UNIQUE NOT NULL,
            certificate_url VARCHAR(255),
            pdf_url VARCHAR(255),
            template_used VARCHAR(50) DEFAULT 'default',
            data JSON,
            completion_date DATE NOT NULL,
            issue_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expiry_date DATE,
            is_valid TINYINT(1) DEFAULT 1,
            verified_count INT DEFAULT 0,
            last_verified_at TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
            FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE SET NULL,
            INDEX idx_user (user_id),
            INDEX idx_course (course_id),
            INDEX idx_code (certificate_code)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 22. Course Reviews
    createTable($pdo, $results, 'course_reviews', "
        CREATE TABLE IF NOT EXISTS course_reviews (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            course_id INT NOT NULL,
            enrollment_id INT,
            rating DECIMAL(2,1) NOT NULL,
            title VARCHAR(200),
            review TEXT,
            pros TEXT,
            cons TEXT,
            is_verified_purchase TINYINT(1) DEFAULT 0,
            is_featured TINYINT(1) DEFAULT 0,
            is_approved TINYINT(1) DEFAULT 1,
            is_helpful INT DEFAULT 0,
            helpful_count INT DEFAULT 0,
            report_count INT DEFAULT 0,
            instructor_reply TEXT,
            instructor_replied_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
            FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE SET NULL,
            UNIQUE KEY unique_user_review (user_id, course_id),
            INDEX idx_user (user_id),
            INDEX idx_course (course_id),
            INDEX idx_rating (rating),
            INDEX idx_approved (is_approved)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 23. Review Votes
    createTable($pdo, $results, 'review_votes', "
        CREATE TABLE IF NOT EXISTS review_votes (
            id INT PRIMARY KEY AUTO_INCREMENT,
            review_id INT NOT NULL,
            user_id INT NOT NULL,
            is_helpful TINYINT(1) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (review_id) REFERENCES course_reviews(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_vote (review_id, user_id),
            INDEX idx_review (review_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 24. Comments
    createTable($pdo, $results, 'comments', "
        CREATE TABLE IF NOT EXISTS comments (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            lesson_id INT NOT NULL,
            parent_id INT,
            content TEXT NOT NULL,
            is_pinned TINYINT(1) DEFAULT 0,
            is_instructor_reply TINYINT(1) DEFAULT 0,
            is_approved TINYINT(1) DEFAULT 1,
            is_resolved TINYINT(1) DEFAULT 0,
            likes_count INT DEFAULT 0,
            replies_count INT DEFAULT 0,
            report_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
            FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE,
            INDEX idx_user (user_id),
            INDEX idx_lesson (lesson_id),
            INDEX idx_parent (parent_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 25. Comment Likes
    createTable($pdo, $results, 'comment_likes', "
        CREATE TABLE IF NOT EXISTS comment_likes (
            id INT PRIMARY KEY AUTO_INCREMENT,
            comment_id INT NOT NULL,
            user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_like (comment_id, user_id),
            INDEX idx_comment (comment_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

/**
 * Cria tabelas de Projetos (26-28)
 */
function createProjectTables($pdo, &$results) {
    // 26. Projects
    createTable($pdo, $results, 'projects', "
        CREATE TABLE IF NOT EXISTS projects (
            id INT PRIMARY KEY AUTO_INCREMENT,
            lesson_id INT,
            course_id INT,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            instructions TEXT,
            requirements TEXT,
            starter_files_url VARCHAR(255),
            solution_url VARCHAR(255),
            difficulty ENUM('beginner', 'intermediate', 'advanced', 'expert') DEFAULT 'beginner',
            estimated_hours INT DEFAULT 1,
            xp_reward INT DEFAULT 100,
            coin_reward INT DEFAULT 20,
            type ENUM('practice', 'challenge', 'portfolio', 'certification') DEFAULT 'practice',
            submission_type ENUM('link', 'file', 'github', 'text') DEFAULT 'link',
            allows_review TINYINT(1) DEFAULT 1,
            review_criteria TEXT,
            is_published TINYINT(1) DEFAULT 1,
            submissions_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
            INDEX idx_lesson (lesson_id),
            INDEX idx_course (course_id),
            INDEX idx_difficulty (difficulty)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 27. Project Submissions
    createTable($pdo, $results, 'project_submissions', "
        CREATE TABLE IF NOT EXISTS project_submissions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            project_id INT NOT NULL,
            user_id INT NOT NULL,
            title VARCHAR(200),
            description TEXT,
            submission_url VARCHAR(500),
            github_url VARCHAR(255),
            live_url VARCHAR(255),
            files_url VARCHAR(255),
            content TEXT,
            status ENUM('pending', 'under_review', 'approved', 'rejected', 'needs_revision') DEFAULT 'pending',
            score DECIMAL(5,2),
            feedback TEXT,
            reviewed_by INT,
            reviewed_at TIMESTAMP NULL,
            xp_earned INT DEFAULT 0,
            coins_earned INT DEFAULT 0,
            likes_count INT DEFAULT 0,
            views_count INT DEFAULT 0,
            is_featured TINYINT(1) DEFAULT 0,
            is_public TINYINT(1) DEFAULT 1,
            submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_project (project_id),
            INDEX idx_user (user_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 28. Project Likes
    createTable($pdo, $results, 'project_likes', "
        CREATE TABLE IF NOT EXISTS project_likes (
            id INT PRIMARY KEY AUTO_INCREMENT,
            submission_id INT NOT NULL,
            user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (submission_id) REFERENCES project_submissions(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_like (submission_id, user_id),
            INDEX idx_submission (submission_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

/**
 * Cria tabelas de Gamificação (29-33)
 */
function createGamificationTables($pdo, &$results) {
    // 29. Badges
    createTable($pdo, $results, 'badges', "
        CREATE TABLE IF NOT EXISTS badges (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            icon VARCHAR(100),
            image VARCHAR(255),
            color VARCHAR(7) DEFAULT '#6366f1',
            category VARCHAR(50) DEFAULT 'general',
            requirement_type VARCHAR(50) NOT NULL,
            requirement_value INT DEFAULT 1,
            xp_reward INT DEFAULT 0,
            coin_reward INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            is_limited TINYINT(1) DEFAULT 0,
            available_until DATETIME,
            order_index INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_category (category),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 30. User Badges
    createTable($pdo, $results, 'user_badges', "
        CREATE TABLE IF NOT EXISTS user_badges (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            badge_id INT NOT NULL,
            earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            is_featured TINYINT(1) DEFAULT 0,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_badge (user_id, badge_id),
            INDEX idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 31. Daily Challenges
    createTable($pdo, $results, 'daily_challenges', "
        CREATE TABLE IF NOT EXISTS daily_challenges (
            id INT PRIMARY KEY AUTO_INCREMENT,
            date DATE UNIQUE NOT NULL,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            type ENUM('lesson', 'quiz', 'time', 'streak', 'social', 'special') DEFAULT 'lesson',
            requirement_type VARCHAR(50) NOT NULL,
            requirement_value INT DEFAULT 1,
            xp_reward INT DEFAULT 50,
            coin_reward INT DEFAULT 10,
            bonus_multiplier DECIMAL(3,2) DEFAULT 1.00,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_date (date),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 32. User Daily Challenges
    createTable($pdo, $results, 'user_daily_challenges', "
        CREATE TABLE IF NOT EXISTS user_daily_challenges (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            challenge_id INT NOT NULL,
            progress INT DEFAULT 0,
            is_completed TINYINT(1) DEFAULT 0,
            completed_at TIMESTAMP NULL,
            xp_earned INT DEFAULT 0,
            coins_earned INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (challenge_id) REFERENCES daily_challenges(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_challenge (user_id, challenge_id),
            INDEX idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 33. Leaderboards
    createTable($pdo, $results, 'leaderboards', "
        CREATE TABLE IF NOT EXISTS leaderboards (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            period_type ENUM('daily', 'weekly', 'monthly', 'all_time') NOT NULL,
            period_start DATE NOT NULL,
            period_end DATE NOT NULL,
            xp_earned INT DEFAULT 0,
            lessons_completed INT DEFAULT 0,
            courses_completed INT DEFAULT 0,
            quizzes_perfect INT DEFAULT 0,
            streak_days INT DEFAULT 0,
            `rank_position` INT,
            `previous_rank` INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_period (user_id, period_type, period_start),
            INDEX idx_user (user_id),
            INDEX idx_period (period_type, period_start),
            INDEX `idx_rank` (`rank_position`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

/**
 * Cria tabelas de Loja (34-39)
 */
function createShopTables($pdo, &$results) {
    // 34. Shop Items
    createTable($pdo, $results, 'shop_items', "
        CREATE TABLE IF NOT EXISTS shop_items (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) UNIQUE,
            description TEXT,
            type ENUM('avatar', 'badge', 'theme', 'power_up', 'cosmetic', 'course_unlock') NOT NULL,
            category VARCHAR(50) DEFAULT 'general',
            image VARCHAR(255),
            preview_url VARCHAR(255),
            price_coins INT DEFAULT 0,
            price_gems INT DEFAULT 0,
            original_price_coins INT,
            discount_percentage INT DEFAULT 0,
            discount_ends_at DATETIME,
            item_data JSON,
            stock_quantity INT DEFAULT -1,
            purchase_limit INT DEFAULT 0,
            level_required INT DEFAULT 1,
            is_featured TINYINT(1) DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            is_limited TINYINT(1) DEFAULT 0,
            available_from DATETIME,
            available_until DATETIME,
            order_index INT DEFAULT 0,
            purchase_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_type (type),
            INDEX idx_category (category),
            INDEX idx_active (is_active),
            INDEX idx_featured (is_featured)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 35. User Inventory
    createTable($pdo, $results, 'user_inventory', "
        CREATE TABLE IF NOT EXISTS user_inventory (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            item_id INT NOT NULL,
            quantity INT DEFAULT 1,
            is_equipped TINYINT(1) DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (item_id) REFERENCES shop_items(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_item (user_id, item_id),
            INDEX idx_user (user_id),
            INDEX idx_equipped (is_equipped)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 36. Coin Transactions
    createTable($pdo, $results, 'coin_transactions', "
        CREATE TABLE IF NOT EXISTS coin_transactions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            amount INT NOT NULL,
            type ENUM('earned', 'spent', 'bonus', 'refund', 'gift', 'admin') NOT NULL,
            source VARCHAR(50) NOT NULL,
            source_id INT,
            description VARCHAR(255),
            balance_after INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user (user_id),
            INDEX idx_type (type),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 37. Payments
    createTable($pdo, $results, 'payments', "
        CREATE TABLE IF NOT EXISTS payments (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            course_id INT,
            subscription_id INT,
            order_number VARCHAR(50) UNIQUE,
            payment_method ENUM('pix', 'credit_card', 'boleto', 'paypal', 'stripe') NOT NULL,
            payment_gateway VARCHAR(50),
            gateway_transaction_id VARCHAR(100),
            amount DECIMAL(10,2) NOT NULL,
            currency VARCHAR(3) DEFAULT 'BRL',
            discount_amount DECIMAL(10,2) DEFAULT 0.00,
            coupon_id INT,
            status ENUM('pending', 'processing', 'completed', 'failed', 'refunded', 'cancelled') DEFAULT 'pending',
            paid_at TIMESTAMP NULL,
            refunded_at TIMESTAMP NULL,
            refund_reason TEXT,
            invoice_url VARCHAR(255),
            receipt_url VARCHAR(255),
            metadata JSON,
            ip_address VARCHAR(45),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
            INDEX idx_user (user_id),
            INDEX idx_status (status),
            INDEX idx_order (order_number)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 38. Coupons
    createTable($pdo, $results, 'coupons', "
        CREATE TABLE IF NOT EXISTS coupons (
            id INT PRIMARY KEY AUTO_INCREMENT,
            code VARCHAR(50) UNIQUE NOT NULL,
            description TEXT,
            type ENUM('percentage', 'fixed', 'free_course') DEFAULT 'percentage',
            discount_value DECIMAL(10,2) NOT NULL,
            min_purchase DECIMAL(10,2) DEFAULT 0.00,
            max_discount DECIMAL(10,2),
            applies_to ENUM('all', 'courses', 'subscriptions', 'specific') DEFAULT 'all',
            course_ids JSON,
            usage_limit INT DEFAULT 0,
            usage_count INT DEFAULT 0,
            per_user_limit INT DEFAULT 1,
            is_active TINYINT(1) DEFAULT 1,
            starts_at DATETIME,
            expires_at DATETIME,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_code (code),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 39. User Coupons
    createTable($pdo, $results, 'user_coupons', "
        CREATE TABLE IF NOT EXISTS user_coupons (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            coupon_id INT NOT NULL,
            payment_id INT,
            discount_applied DECIMAL(10,2),
            used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
            FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL,
            INDEX idx_user (user_id),
            INDEX idx_coupon (coupon_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

/**
 * Cria tabelas do Sistema (40-45)
 */
function createSystemTables($pdo, &$results) {
    // 40. Notifications
    createTable($pdo, $results, 'notifications', "
        CREATE TABLE IF NOT EXISTS notifications (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            type VARCHAR(50) NOT NULL,
            category ENUM('system', 'achievement', 'course', 'social', 'payment', 'reminder') DEFAULT 'system',
            title VARCHAR(200),
            message TEXT,
            action_url VARCHAR(255),
            action_text VARCHAR(50),
            icon VARCHAR(50),
            image VARCHAR(255),
            data JSON,
            is_read TINYINT(1) DEFAULT 0,
            is_email_sent TINYINT(1) DEFAULT 0,
            is_push_sent TINYINT(1) DEFAULT 0,
            read_at TIMESTAMP NULL,
            expires_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user (user_id),
            INDEX idx_read (is_read),
            INDEX idx_type (type),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 41. Activity Logs
    createTable($pdo, $results, 'activity_logs', "
        CREATE TABLE IF NOT EXISTS activity_logs (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT,
            action VARCHAR(100) NOT NULL,
            entity_type VARCHAR(50),
            entity_id INT,
            description TEXT,
            old_values JSON,
            new_values JSON,
            ip_address VARCHAR(45),
            user_agent TEXT,
            device_type VARCHAR(20),
            browser VARCHAR(50),
            os VARCHAR(50),
            location VARCHAR(100),
            data JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_user (user_id),
            INDEX idx_action (action),
            INDEX idx_entity (entity_type, entity_id),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 42. User Sessions
    createTable($pdo, $results, 'user_sessions', "
        CREATE TABLE IF NOT EXISTS user_sessions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            session_token VARCHAR(255) UNIQUE NOT NULL,
            refresh_token VARCHAR(255),
            ip_address VARCHAR(45),
            user_agent TEXT,
            device_name VARCHAR(100),
            device_type ENUM('desktop', 'mobile', 'tablet', 'other') DEFAULT 'desktop',
            browser VARCHAR(50),
            os VARCHAR(50),
            location VARCHAR(100),
            is_active TINYINT(1) DEFAULT 1,
            last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user (user_id),
            INDEX idx_token (session_token),
            INDEX idx_active (is_active),
            INDEX idx_expires (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 43. Settings
    createTable($pdo, $results, 'settings', "
        CREATE TABLE IF NOT EXISTS settings (
            id INT PRIMARY KEY AUTO_INCREMENT,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            setting_type ENUM('string', 'integer', 'boolean', 'json', 'html') DEFAULT 'string',
            category VARCHAR(50) DEFAULT 'general',
            label VARCHAR(100),
            description TEXT,
            is_public TINYINT(1) DEFAULT 0,
            is_editable TINYINT(1) DEFAULT 1,
            order_index INT DEFAULT 0,
            updated_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_key (setting_key),
            INDEX idx_category (category)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 44. Announcements
    createTable($pdo, $results, 'announcements', "
        CREATE TABLE IF NOT EXISTS announcements (
            id INT PRIMARY KEY AUTO_INCREMENT,
            title VARCHAR(200) NOT NULL,
            content TEXT NOT NULL,
            type ENUM('info', 'warning', 'success', 'error', 'promotion') DEFAULT 'info',
            target ENUM('all', 'students', 'instructors', 'premium', 'specific') DEFAULT 'all',
            target_course_id INT,
            action_url VARCHAR(255),
            action_text VARCHAR(50),
            image VARCHAR(255),
            is_dismissible TINYINT(1) DEFAULT 1,
            is_active TINYINT(1) DEFAULT 1,
            is_pinned TINYINT(1) DEFAULT 0,
            priority INT DEFAULT 0,
            starts_at DATETIME,
            ends_at DATETIME,
            view_count INT DEFAULT 0,
            click_count INT DEFAULT 0,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (target_course_id) REFERENCES courses(id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_active (is_active),
            INDEX idx_dates (starts_at, ends_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 45. Password Resets
    createTable($pdo, $results, 'password_resets', "
        CREATE TABLE IF NOT EXISTS password_resets (
            id INT PRIMARY KEY AUTO_INCREMENT,
            email VARCHAR(100) NOT NULL,
            token VARCHAR(255) NOT NULL,
            ip_address VARCHAR(45),
            expires_at TIMESTAMP NOT NULL,
            used_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_token (token)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 46. Email Verifications
    createTable($pdo, $results, 'email_verifications', "
        CREATE TABLE IF NOT EXISTS email_verifications (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            email VARCHAR(100) NOT NULL,
            token VARCHAR(255) NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            verified_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_token (token),
            INDEX idx_expires (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

/**
 * Cria tabelas Sociais e Auxiliares (47-51)
 */
function createSocialTables($pdo, &$results) {
    // 47. Tags
    createTable($pdo, $results, 'tags', "
        CREATE TABLE IF NOT EXISTS tags (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(50) NOT NULL,
            slug VARCHAR(50) UNIQUE NOT NULL,
            description TEXT,
            color VARCHAR(7) DEFAULT '#6366f1',
            usage_count INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_slug (slug),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 48. Course Tags
    createTable($pdo, $results, 'course_tags', "
        CREATE TABLE IF NOT EXISTS course_tags (
            id INT PRIMARY KEY AUTO_INCREMENT,
            course_id INT NOT NULL,
            tag_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
            FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
            UNIQUE KEY unique_course_tag (course_id, tag_id),
            INDEX idx_course (course_id),
            INDEX idx_tag (tag_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 49. User Follows
    createTable($pdo, $results, 'user_follows', "
        CREATE TABLE IF NOT EXISTS user_follows (
            id INT PRIMARY KEY AUTO_INCREMENT,
            follower_id INT NOT NULL,
            following_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_follow (follower_id, following_id),
            INDEX idx_follower (follower_id),
            INDEX idx_following (following_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 50. User Bookmarks
    createTable($pdo, $results, 'user_bookmarks', "
        CREATE TABLE IF NOT EXISTS user_bookmarks (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            entity_type ENUM('course', 'lesson', 'project', 'article') NOT NULL,
            entity_id INT NOT NULL,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_bookmark (user_id, entity_type, entity_id),
            INDEX idx_user (user_id),
            INDEX idx_entity (entity_type, entity_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 51. Course Prerequisites
    createTable($pdo, $results, 'course_prerequisites', "
        CREATE TABLE IF NOT EXISTS course_prerequisites (
            id INT PRIMARY KEY AUTO_INCREMENT,
            course_id INT NOT NULL,
            prerequisite_course_id INT NOT NULL,
            is_required TINYINT(1) DEFAULT 1,
            order_index INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
            FOREIGN KEY (prerequisite_course_id) REFERENCES courses(id) ON DELETE CASCADE,
            UNIQUE KEY unique_prerequisite (course_id, prerequisite_course_id),
            INDEX idx_course (course_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Tabela News (do seu script original)
    createTable($pdo, $results, 'news', "
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
    
    // XP History (do seu script)
    createTable($pdo, $results, 'xp_history', "
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
    
    // Weekly Leaderboard (do seu script)
    createTable($pdo, $results, 'weekly_leaderboard', "
        CREATE TABLE IF NOT EXISTS weekly_leaderboard (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            week_start DATE NOT NULL,
            week_end DATE NOT NULL,
            xp_earned INT DEFAULT 0,
            lessons_completed INT DEFAULT 0,
            quizzes_passed INT DEFAULT 0,
            time_spent INT DEFAULT 0,
            `rank` INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_weekly (user_id, week_start),
            INDEX idx_user (user_id),
            INDEX idx_week (week_start),
            INDEX idx_xp (xp_earned),
            INDEX `idx_rank` (`rank`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

/**
 * Função auxiliar para criar tabela
 */
function createTable($pdo, &$results, $tableName, $sql) {
    try {
        // Verificar se a tabela já existe
        $stmt = $pdo->query("SHOW TABLES LIKE '$tableName'");
        if ($stmt->rowCount() > 0) {
            $results['messages'][] = "⏭️  Tabela '$tableName' já existe";
            $results['stats']['tables_skipped']++;
            return;
        }
        
        // Criar a tabela
        $pdo->exec($sql);
        $results['messages'][] = "✅ Tabela '$tableName' criada com sucesso";
        $results['stats']['tables_created']++;
        
    } catch (PDOException $e) {
        $results['errors'][] = "❌ Erro ao criar tabela '$tableName': " . $e->getMessage();
        $results['success'] = false;
    }
}

/**
 * Insere dados iniciais (mantive a sua função com pequenas melhorias)
 */
function insertInitialData($pdo, &$results) {
    // Inserir Níveis
    insertLevels($pdo, $results);
    
    // Inserir Conquistas
    insertAchievements($pdo, $results);
    
    // Inserir Categorias
    insertCategories($pdo, $results);
    
    // Inserir Configurações
    insertSettings($pdo, $results);
    
    // Inserir Notícias (opcional)
    insertNews($pdo, $results);
}

/**
 * Insere os níveis iniciais
 */
function insertLevels($pdo, &$results) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM levels");
        if ($stmt->fetchColumn() > 0) {
            $results['messages'][] = "⏭️  Níveis já existem";
            return;
        }
        
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
            $results['stats']['data_inserted']++;
        }
        
        $results['messages'][] = "✅ 10 níveis inseridos";
        
    } catch (PDOException $e) {
        $results['errors'][] = "❌ Erro ao inserir níveis: " . $e->getMessage();
    }
}

/**
 * Insere conquistas iniciais
 */
function insertAchievements($pdo, &$results) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM achievements");
        if ($stmt->fetchColumn() > 0) {
            $results['messages'][] = "⏭️  Conquistas já existem";
            return;
        }
        
        $achievements = [
            ['Primeiro Passo', 'primeiro-passo', 'Complete sua primeira lição', '🎯', 10, 5, 'learning', 'lessons_completed', 1, 'common', 1],
            ['Estudante Dedicado', 'estudante-dedicado', 'Complete 10 lições', '📖', 50, 15, 'learning', 'lessons_completed', 10, 'common', 2],
            ['Maratonista', 'maratonista', 'Complete 50 lições', '🏃', 150, 50, 'learning', 'lessons_completed', 50, 'uncommon', 3],
            ['Mestre das Lições', 'mestre-licoes', 'Complete 100 lições', '📚', 300, 100, 'learning', 'lessons_completed', 100, 'rare', 4],
            ['Formando', 'formando', 'Complete seu primeiro curso', '🎓', 100, 30, 'learning', 'courses_completed', 1, 'uncommon', 5],
            ['Multitarefa', 'multitarefa', 'Complete 5 cursos', '🎖️', 500, 150, 'learning', 'courses_completed', 5, 'rare', 6],
            ['Acadêmico', 'academico', 'Complete 10 cursos', '🏅', 1000, 300, 'learning', 'courses_completed', 10, 'epic', 7],
            ['Constante', 'constante', 'Mantenha um streak de 7 dias', '🔥', 70, 25, 'challenge', 'streak', 7, 'uncommon', 8],
            ['Imparável', 'imparavel', 'Mantenha um streak de 30 dias', '⚡', 300, 100, 'challenge', 'streak', 30, 'rare', 9],
            ['Lenda Viva', 'lenda-viva', 'Mantenha um streak de 100 dias', '💫', 1000, 500, 'challenge', 'streak', 100, 'legendary', 10],
            ['Nota Perfeita', 'nota-perfeita', 'Acerte 100% em um quiz', '💯', 50, 20, 'learning', 'perfect_quiz', 1, 'uncommon', 11],
            ['Gênio', 'genio', 'Acerte 100% em 10 quizzes', '🧠', 200, 75, 'learning', 'perfect_quiz', 10, 'rare', 12],
            ['Caçador de XP', 'cacador-xp', 'Ganhe 1000 XP', '⭐', 100, 30, 'challenge', 'xp_earned', 1000, 'common', 13],
            ['Mestre do XP', 'mestre-xp', 'Ganhe 10000 XP', '🌟', 500, 150, 'challenge', 'xp_earned', 10000, 'epic', 14],
            ['Social', 'social', 'Faça seu primeiro comentário', '💬', 10, 5, 'social', 'comments', 1, 'common', 15]
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO achievements (name, slug, description, icon, xp_reward, coin_reward, category, requirement_type, requirement_value, rarity, order_index) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($achievements as $achievement) {
            $stmt->execute($achievement);
            $results['stats']['data_inserted']++;
        }
        
        $results['messages'][] = "✅ 15 conquistas inseridas";
        
    } catch (PDOException $e) {
        $results['errors'][] = "❌ Erro ao inserir conquistas: " . $e->getMessage();
    }
}

/**
 * Insere categorias iniciais
 */
function insertCategories($pdo, &$results) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
        if ($stmt->fetchColumn() > 0) {
            $results['messages'][] = "⏭️  Categorias já existem";
            return;
        }
        
        $categories = [
            ['Phaser 3', 'phaser-3', 'Framework JavaScript para desenvolvimento de jogos 2D', '🎮', '#6366f1', 1],
            ['React', 'react', 'Biblioteca JavaScript para construção de interfaces', '⚛️', '#61dafb', 2],
            ['JavaScript', 'javascript', 'Linguagem de programação essencial para web', '📜', '#f7df1e', 3],
            ['TypeScript', 'typescript', 'JavaScript com tipagem estática', '📘', '#3178c6', 4],
            ['Game Design', 'game-design', 'Princípios e técnicas de design de jogos', '🎨', '#ec4899', 5],
            ['Unity', 'unity', 'Motor de jogos profissional', '🎯', '#000000', 6],
            ['Godot', 'godot', 'Motor de jogos open source', '🤖', '#478cbf', 7],
            ['Pixel Art', 'pixel-art', 'Arte em pixel para jogos', '🖼️', '#ff6b6b', 8]
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO categories (name, slug, description, icon, color, order_index) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($categories as $category) {
            $stmt->execute($category);
            $results['stats']['data_inserted']++;
        }
        
        $results['messages'][] = "✅ 8 categorias inseridas";
        
    } catch (PDOException $e) {
        $results['errors'][] = "❌ Erro ao inserir categorias: " . $e->getMessage();
    }
}

/**
 * Insere configurações do sistema
 */
function insertSettings($pdo, &$results) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM settings");
        if ($stmt->fetchColumn() > 0) {
            $results['messages'][] = "⏭️  Configurações já existem";
            return;
        }
        
        $settings = [
            ['site_name', 'GameDev Academy', 'string', 'general', 'Nome do site', 1],
            ['site_description', 'Plataforma gamificada de ensino de desenvolvimento de jogos', 'string', 'general', 'Descrição', 1],
            ['maintenance_mode', '0', 'boolean', 'system', 'Modo manutenção', 0],
            ['registration_enabled', '1', 'boolean', 'system', 'Permitir registro', 0],
            ['xp_per_lesson', '10', 'integer', 'gamification', 'XP por lição', 0],
            ['coins_per_lesson', '1', 'integer', 'gamification', 'Moedas por lição', 0],
            ['default_theme', 'system', 'string', 'appearance', 'Tema padrão', 0],
            ['default_language', 'pt-BR', 'string', 'general', 'Idioma padrão', 0]
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO settings (setting_key, setting_value, setting_type, category, description, is_public) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($settings as $setting) {
            $stmt->execute($setting);
            $results['stats']['data_inserted']++;
        }
        
        $results['messages'][] = "✅ Configurações inseridas";
        
    } catch (PDOException $e) {
        $results['errors'][] = "❌ Erro ao inserir configurações: " . $e->getMessage();
    }
}

/**
 * Insere notícias de exemplo (opcional)
 */
function insertNews($pdo, &$results) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM news");
        if ($stmt->fetchColumn() > 0) {
            $results['messages'][] = "⏭️  Notícias já existem";
            return;
        }
        
        $news = [
            [
                'Bem-vindo ao GameDev Academy!',
                'bem-vindo-gamedev-academy',
                'Conheça nossa plataforma gamificada',
                '<p>Bem-vindo à nossa comunidade! Aprenda desenvolvimento de jogos de forma divertida e gamificada.</p>',
                1,
                'announcement',
                1,
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
            $results['stats']['data_inserted']++;
        }
        
        $results['messages'][] = "✅ Notícias inseridas";
        
    } catch (PDOException $e) {
        $results['errors'][] = "❌ Erro ao inserir notícias: " . $e->getMessage();
    }
}

/**
 * Verifica a integridade da instalação
 */
function verifyInstallation($pdo, &$results) {
    try {
        // Contar tabelas criadas
        $stmt = $pdo->query("
            SELECT COUNT(*) as total 
            FROM INFORMATION_SCHEMA.TABLES 
            WHERE TABLE_SCHEMA = DATABASE()
        ");
        $totalTables = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $results['messages'][] = "\n=== VERIFICAÇÃO ===";
        $results['messages'][] = "📊 Total de tabelas no banco: $totalTables";
        
        if ($totalTables >= 51) {
            $results['messages'][] = "✅ Instalação completa!";
        } else {
            $results['warnings'][] = "⚠️  Esperado: 51+ tabelas | Encontrado: $totalTables";
        }
        
    } catch (PDOException $e) {
        $results['errors'][] = "❌ Erro na verificação: " . $e->getMessage();
    }
}

/**
 * Execução direta do script (para testes)
 */
if (basename($_SERVER['PHP_SELF']) == 'create_tables.php') {
    echo "<!DOCTYPE html>
    <html lang='pt-BR'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Instalação - GameDev Academy</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                padding: 20px;
            }
            .container { 
                max-width: 900px; 
                margin: 0 auto; 
                background: #1a1a2e;
                border-radius: 20px;
                padding: 30px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            }
            h1 { 
                color: #fff; 
                text-align: center;
                margin-bottom: 10px;
                font-size: 2.5em;
            }
            .version {
                text-align: center;
                color: #888;
                margin-bottom: 30px;
                font-size: 0.9em;
            }
            .message { 
                padding: 12px 20px; 
                margin: 8px 0; 
                background: #16213e;
                border-radius: 8px;
                border-left: 4px solid #6366f1;
                color: #e0e0e0;
                font-family: 'Courier New', monospace;
                font-size: 0.9em;
                line-height: 1.6;
            }
            .success { 
                border-left-color: #10b981;
                background: #0f3d2f;
            }
            .error { 
                border-left-color: #ef4444;
                background: #3d0f0f;
            }
            .warning {
                border-left-color: #f59e0b;
                background: #3d2f0f;
            }
            .stats {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
                margin: 20px 0;
                padding: 20px;
                background: #0f1419;
                border-radius: 10px;
            }
            .stat-item {
                text-align: center;
                padding: 15px;
                background: #1a1a2e;
                border-radius: 8px;
            }
            .stat-value {
                font-size: 2em;
                font-weight: bold;
                color: #6366f1;
            }
            .stat-label {
                color: #888;
                margin-top: 5px;
                font-size: 0.9em;
            }
            .separator {
                height: 2px;
                background: linear-gradient(90deg, transparent, #6366f1, transparent);
                margin: 20px 0;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>🎮 GameDev Academy</h1>
            <div class='version'>Instalação do Banco de Dados v2.0</div>";
    
    try {
        // Configurar conexão
        $host = 'localhost';
        $dbname = 'gamedev_academy';
        $user = 'root';
        $pass = '';
        
        $pdo = new PDO(
            "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        
        // Executar instalação
        $results = executeDatabaseSetup($pdo);
        
        // Mostrar estatísticas
        echo "<div class='stats'>
                <div class='stat-item'>
                    <div class='stat-value'>{$results['stats']['tables_created']}</div>
                    <div class='stat-label'>Tabelas Criadas</div>
                </div>
                <div class='stat-item'>
                    <div class='stat-value'>{$results['stats']['tables_skipped']}</div>
                    <div class='stat-label'>Já Existentes</div>
                </div>
                <div class='stat-item'>
                    <div class='stat-value'>{$results['stats']['data_inserted']}</div>
                    <div class='stat-label'>Registros Inseridos</div>
                </div>
              </div>";
        
        echo "<div class='separator'></div>";
        
        // Mostrar mensagens
        foreach ($results['messages'] as $msg) {
            $class = '';
            if (strpos($msg, '✅') !== false || strpos($msg, '✓') !== false) {
                $class = 'success';
            } elseif (strpos($msg, '===') !== false) {
                $class = 'message';
            }
            echo "<div class='message $class'>$msg</div>";
        }
        
        // Mostrar warnings
        foreach ($results['warnings'] as $warning) {
            echo "<div class='message warning'>$warning</div>";
        }
        
        // Mostrar erros
        foreach ($results['errors'] as $error) {
            echo "<div class='message error'>$error</div>";
        }
        
        // Status final
        if ($results['success']) {
            echo "<div class='message success' style='margin-top: 30px; font-size: 1.2em; text-align: center;'>
                    🎉 INSTALAÇÃO CONCLUÍDA COM SUCESSO!
                  </div>";
        } else {
            echo "<div class='message error' style='margin-top: 30px; font-size: 1.2em; text-align: center;'>
                    ❌ INSTALAÇÃO COM ERROS - Verifique os logs acima
                  </div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='message error'>
                <strong>ERRO FATAL:</strong><br>
                {$e->getMessage()}<br><br>
                <strong>Stack Trace:</strong><br>
                <pre style='color: #ff6b6b; font-size: 0.8em;'>{$e->getTraceAsString()}</pre>
              </div>";
    }
    
    echo "</div></body></html>";
}
?>
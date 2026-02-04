-- =====================================================
-- GameDev Academy - Schema Completo do Banco de Dados
-- Versão: 2.0.0 (Atualizado)
-- Total de Tabelas: 45
-- =====================================================

-- Criar banco de dados se não existir
CREATE DATABASE IF NOT EXISTS gamedev_academy 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE gamedev_academy;

-- =====================================================
-- TABELAS PRINCIPAIS (1-25) - Suas tabelas originais
-- =====================================================

-- 1. Tabela de Usuários
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabela de Níveis
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabela de Conquistas/Badges
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Conquistas desbloqueadas pelos usuários
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Categorias de Cursos
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabela de Cursos
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
    INDEX idx_difficulty (difficulty),
    FULLTEXT idx_search (title, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Módulos dos Cursos
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Lições
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Matrículas
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Progresso nas Lições
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Quiz e Exercícios
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Questões do Quiz
CREATE TABLE IF NOT EXISTS quiz_questions (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Respostas do Quiz
CREATE TABLE IF NOT EXISTS quiz_attempts (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Notícias/Blog
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
    INDEX idx_author (author_id),
    FULLTEXT idx_search (title, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Comentários
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Histórico de XP
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Histórico de Moedas
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Ranking/Leaderboard semanal
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. Ranking Global
CREATE TABLE IF NOT EXISTS global_leaderboard (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. Notificações
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 21. Tokens de recuperação de senha
CREATE TABLE IF NOT EXISTS password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 22. Sessões de usuário
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 23. Configurações do Sistema
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 24. Logs de Atividade
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 25. Avaliações de Cursos
CREATE TABLE IF NOT EXISTS course_reviews (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELAS ADICIONAIS (26-45)
-- =====================================================

-- 26. Favoritos do Usuário
CREATE TABLE IF NOT EXISTS user_favorites (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, course_id),
    INDEX idx_user (user_id),
    INDEX idx_course (course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 27. Lista de Desejos (Wishlist)
CREATE TABLE IF NOT EXISTS user_wishlist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, course_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 28. Histórico de Visualização
CREATE TABLE IF NOT EXISTS view_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    viewable_type ENUM('course', 'lesson', 'news') NOT NULL,
    viewable_id INT NOT NULL,
    view_count INT DEFAULT 1,
    last_viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_view (user_id, viewable_type, viewable_id),
    INDEX idx_user (user_id),
    INDEX idx_viewable (viewable_type, viewable_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 29. Certificados
CREATE TABLE IF NOT EXISTS certificates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_id INT NOT NULL,
    certificate_code VARCHAR(50) UNIQUE NOT NULL,
    certificate_url VARCHAR(255),
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_course (course_id),
    INDEX idx_code (certificate_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 30. Cupons de Desconto
CREATE TABLE IF NOT EXISTS coupons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    description VARCHAR(255),
    discount_type ENUM('percentage', 'fixed') DEFAULT 'percentage',
    discount_value DECIMAL(10,2) NOT NULL,
    max_uses INT DEFAULT NULL,
    uses_count INT DEFAULT 0,
    min_purchase DECIMAL(10,2) DEFAULT 0,
    course_id INT DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    starts_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_code (code),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 31. Uso de Cupons
CREATE TABLE IF NOT EXISTS coupon_uses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    coupon_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT,
    discount_applied DECIMAL(10,2) NOT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_coupon (coupon_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 32. Pedidos/Compras
CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_id VARCHAR(255),
    coupon_id INT,
    notes TEXT,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_order_number (order_number),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 33. Itens do Pedido
CREATE TABLE IF NOT EXISTS order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    course_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 34. Mensagens Diretas
CREATE TABLE IF NOT EXISTS direct_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    read_at TIMESTAMP NULL,
    deleted_by_sender TINYINT(1) DEFAULT 0,
    deleted_by_receiver TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_sender (sender_id),
    INDEX idx_receiver (receiver_id),
    INDEX idx_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 35. Anotações do Usuário nas Lições
CREATE TABLE IF NOT EXISTS lesson_notes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    note TEXT NOT NULL,
    timestamp_seconds INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    INDEX idx_user_lesson (user_id, lesson_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 36. Bookmarks em Lições
CREATE TABLE IF NOT EXISTS lesson_bookmarks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    title VARCHAR(200),
    timestamp_seconds INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_lesson (lesson_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 37. Recursos/Downloads das Lições
CREATE TABLE IF NOT EXISTS lesson_resources (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lesson_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    file_url VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    download_count INT DEFAULT 0,
    order_index INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    INDEX idx_lesson (lesson_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 38. Projetos dos Alunos
CREATE TABLE IF NOT EXISTS student_projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_id INT,
    lesson_id INT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    project_url VARCHAR(255),
    github_url VARCHAR(255),
    demo_url VARCHAR(255),
    thumbnail VARCHAR(255),
    is_featured TINYINT(1) DEFAULT 0,
    is_public TINYINT(1) DEFAULT 1,
    likes_count INT DEFAULT 0,
    views_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_featured (is_featured),
    INDEX idx_public (is_public)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 39. Likes em Projetos
CREATE TABLE IF NOT EXISTS project_likes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES student_projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_like (project_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 40. Fórum - Categorias
CREATE TABLE IF NOT EXISTS forum_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    color VARCHAR(7) DEFAULT '#6366f1',
    order_index INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 41. Fórum - Tópicos
CREATE TABLE IF NOT EXISTS forum_topics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    is_pinned TINYINT(1) DEFAULT 0,
    is_locked TINYINT(1) DEFAULT 0,
    is_solved TINYINT(1) DEFAULT 0,
    views_count INT DEFAULT 0,
    replies_count INT DEFAULT 0,
    last_reply_at TIMESTAMP NULL,
    last_reply_user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES forum_categories(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (last_reply_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_user (user_id),
    INDEX idx_pinned (is_pinned),
    FULLTEXT idx_search (title, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 42. Fórum - Respostas
CREATE TABLE IF NOT EXISTS forum_replies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    topic_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    is_solution TINYINT(1) DEFAULT 0,
    likes_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (topic_id) REFERENCES forum_topics(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_topic (topic_id),
    INDEX idx_user (user_id),
    INDEX idx_solution (is_solution)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 43. Tags
CREATE TABLE IF NOT EXISTS tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_usage (usage_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 44. Relacionamento Tags com Cursos
CREATE TABLE IF NOT EXISTS course_tags (
    course_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (course_id, tag_id),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 45. Instrutores (dados adicionais)
CREATE TABLE IF NOT EXISTS instructor_profiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    headline VARCHAR(255),
    biography TEXT,
    expertise TEXT,
    website VARCHAR(255),
    youtube_url VARCHAR(255),
    twitter_url VARCHAR(255),
    total_students INT DEFAULT 0,
    total_courses INT DEFAULT 0,
    total_reviews INT DEFAULT 0,
    average_rating DECIMAL(3,2) DEFAULT 0,
    is_verified TINYINT(1) DEFAULT 0,
    payout_method VARCHAR(50),
    payout_info TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERÇÃO DE DADOS INICIAIS
-- =====================================================

-- Inserir níveis
INSERT IGNORE INTO levels (level_number, title, xp_required, badge_icon, color, perks) VALUES
(1, 'Iniciante', 0, '🌱', '#10b981', 'Acesso aos cursos básicos'),
(2, 'Aprendiz', 100, '📚', '#6366f1', 'Desbloqueio de conquistas'),
(3, 'Estudante', 300, '✏️', '#8b5cf6', 'Acesso a quizzes avançados'),
(4, 'Praticante', 600, '💻', '#ec4899', 'Projetos práticos'),
(5, 'Desenvolvedor Jr', 1000, '🚀', '#f59e0b', 'Certificados personalizados'),
(6, 'Desenvolvedor', 1500, '⚡', '#ef4444', 'Acesso a conteúdo exclusivo'),
(7, 'Desenvolvedor Sr', 2500, '🔥', '#dc2626', 'Mentoria com instrutores'),
(8, 'Especialista', 4000, '💎', '#0ea5e9', 'Criar seus próprios cursos'),
(9, 'Mestre', 6000, '👑', '#fbbf24', 'Acesso vitalício a todos os cursos'),
(10, 'Lenda', 10000, '🏆', '#f59e0b', 'Reconhecimento especial na plataforma');

-- Inserir conquistas
INSERT IGNORE INTO achievements (name, description, icon, xp_reward, coin_reward, requirement_type, requirement_value, order_index) VALUES
('Primeiro Passo', 'Complete sua primeira lição', '🎯', 10, 5, 'lessons_completed', 1, 1),
('Estudante Dedicado', 'Complete 10 lições', '📖', 50, 15, 'lessons_completed', 10, 2),
('Maratonista', 'Complete 50 lições', '🏃', 150, 50, 'lessons_completed', 50, 3),
('Expert em Lições', 'Complete 100 lições', '🎓', 300, 100, 'lessons_completed', 100, 4),
('Formando', 'Complete seu primeiro curso', '🎓', 100, 30, 'courses_completed', 1, 5),
('Colecionador', 'Complete 5 cursos', '🏅', 300, 100, 'courses_completed', 5, 6),
('Mestre dos Cursos', 'Complete 10 cursos', '🏆', 500, 200, 'courses_completed', 10, 7),
('Constante', 'Mantenha um streak de 7 dias', '🔥', 70, 25, 'streak', 7, 8),
('Imparável', 'Mantenha um streak de 30 dias', '⚡', 300, 100, 'streak', 30, 9),
('Lenda do Streak', 'Mantenha um streak de 100 dias', '🌟', 1000, 500, 'streak', 100, 10),
('Primeiros Passos', 'Alcance 100 XP', '⭐', 20, 10, 'xp_earned', 100, 11),
('Centurião', 'Alcance 1000 XP', '💯', 50, 20, 'xp_earned', 1000, 12),
('Veterano', 'Alcance 5000 XP', '🎖️', 200, 100, 'xp_earned', 5000, 13),
('Lendário', 'Alcance 10000 XP', '🌟', 500, 200, 'xp_earned', 10000, 14),
('Gênio do Quiz', 'Acerte 100% em um quiz', '🧠', 30, 15, 'perfect_quiz', 1, 15);

-- Inserir categorias
INSERT IGNORE INTO categories (name, slug, description, icon, color, order_index) VALUES
('Phaser 3', 'phaser-3', 'Framework JavaScript para desenvolvimento de jogos 2D', '🎮', '#6366f1', 1),
('React', 'react', 'Biblioteca JavaScript para construção de interfaces', '⚛️', '#61dafb', 2),
('JavaScript', 'javascript', 'Linguagem de programação essencial para web', '📜', '#f7df1e', 3),
('TypeScript', 'typescript', 'JavaScript com tipagem estática', '📘', '#3178c6', 4),
('Game Design', 'game-design', 'Princípios e técnicas de design de jogos', '🎨', '#ec4899', 5),
('HTML5 Canvas', 'html5-canvas', 'Criação de gráficos e animações com Canvas', '🖼️', '#e34c26', 6),
('Node.js', 'nodejs', 'JavaScript no servidor', '💚', '#339933', 7),
('WebGL', 'webgl', 'Gráficos 3D na web', '🎯', '#990000', 8),
('Projetos Práticos', 'projetos', 'Projetos completos do início ao fim', '🚀', '#10b981', 9);

-- Inserir categorias do fórum
INSERT IGNORE INTO forum_categories (name, slug, description, icon, color, order_index) VALUES
('Dúvidas Gerais', 'duvidas-gerais', 'Tire suas dúvidas sobre desenvolvimento de jogos', '❓', '#6366f1', 1),
('Phaser 3', 'phaser-3', 'Discussões sobre o framework Phaser', '🎮', '#10b981', 2),
('React', 'react', 'Discussões sobre React e React Native', '⚛️', '#61dafb', 3),
('Mostre seu Projeto', 'mostre-seu-projeto', 'Compartilhe seus projetos com a comunidade', '🚀', '#f59e0b', 4),
('Off-topic', 'off-topic', 'Conversas gerais fora do tema principal', '💬', '#8b5cf6', 5);

-- Inserir usuário admin (senha: admin123)
INSERT IGNORE INTO users (username, email, password, full_name, role, xp_total, level, is_active, email_verified) VALUES
('admin', 'admin@gamedev.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'admin', 0, 1, 1, 1);

-- Inserir cursos de exemplo
INSERT IGNORE INTO courses (category_id, title, slug, description, short_description, difficulty, estimated_hours, xp_reward, coin_reward, is_free, is_featured, is_published, instructor_id, published_at) VALUES
(1, 'Introdução ao Phaser 3', 'introducao-phaser-3', 
 'Aprenda os fundamentos do Phaser 3 e crie seu primeiro jogo 2D! Este curso é perfeito para iniciantes que querem entrar no mundo do desenvolvimento de jogos.', 
 'Curso completo para iniciantes em Phaser 3', 
 'beginner', 5, 200, 20, 1, 1, 1, 1, NOW()),

(1, 'Criando um Jogo de Plataforma com Phaser', 'jogo-plataforma-phaser', 
 'Desenvolva um jogo de plataforma completo do zero, incluindo física, colisões, power-ups e muito mais.', 
 'Crie um jogo de plataforma profissional', 
 'intermediate', 10, 500, 50, 0, 1, 1, 1, NOW()),

(2, 'React do Zero ao Avançado', 'react-zero-avancado', 
 'Domine React com hooks, context API, Redux e muito mais. Aprenda a criar aplicações modernas e escaláveis.', 
 'Curso completo de React moderno', 
 'intermediate', 15, 600, 60, 0, 1, 1, 1, NOW()),

(3, 'JavaScript para Jogos', 'javascript-para-jogos', 
 'Aprenda JavaScript com foco em desenvolvimento de jogos. Conceitos essenciais e padrões de projeto.', 
 'JavaScript essencial para game dev', 
 'beginner', 8, 300, 30, 1, 0, 1, 1, NOW()),

(5, 'Game Design: Teoria e Prática', 'game-design-teoria-pratica', 
 'Aprenda os princípios fundamentais do game design e como aplicá-los em seus projetos.', 
 'Fundamentos do design de jogos', 
 'beginner', 6, 250, 25, 0, 0, 1, 1, NOW());

-- Inserir configurações do sistema
INSERT IGNORE INTO settings (setting_key, setting_value, setting_type, category, description, is_public) VALUES
('site_name', 'GameDev Academy', 'string', 'general', 'Nome do site', 1),
('site_description', 'Plataforma de ensino gamificada para desenvolvimento de jogos', 'text', 'general', 'Descrição do site', 1),
('maintenance_mode', 'false', 'boolean', 'general', 'Modo de manutenção', 0),
('registration_enabled', 'true', 'boolean', 'auth', 'Permitir novos registros', 0),
('email_verification_required', 'false', 'boolean', 'auth', 'Exigir verificação de e-mail', 0),
('default_xp_lesson', '10', 'integer', 'gamification', 'XP padrão por lição', 0),
('default_xp_course', '100', 'integer', 'gamification', 'XP padrão por curso', 0),
('default_coins_lesson', '1', 'integer', 'gamification', 'Moedas padrão por lição', 0),
('default_coins_course', '10', 'integer', 'gamification', 'Moedas padrão por curso', 0),
('streak_bonus_xp', '5', 'integer', 'gamification', 'XP bônus por dia de streak', 0);

-- =====================================================
-- FIM DO SCHEMA
-- =====================================================
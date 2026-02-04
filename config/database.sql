-- database.sql
-- GameDev Academy - Estrutura do Banco de Dados

CREATE DATABASE IF NOT EXISTS gamedev_academy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gamedev_academy;

-- Tabela de Usuários
CREATE TABLE users (
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
    is_active TINYINT(1) DEFAULT 1,
    email_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de Níveis (configuração)
CREATE TABLE levels (
    id INT PRIMARY KEY AUTO_INCREMENT,
    level_number INT UNIQUE NOT NULL,
    title VARCHAR(50) NOT NULL,
    xp_required INT NOT NULL,
    badge_icon VARCHAR(100),
    color VARCHAR(7) DEFAULT '#6366f1'
);

-- Tabela de Conquistas/Badges
CREATE TABLE achievements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(100),
    xp_reward INT DEFAULT 0,
    coin_reward INT DEFAULT 0,
    requirement_type ENUM('lessons_completed', 'courses_completed', 'streak', 'xp_earned', 'special') NOT NULL,
    requirement_value INT DEFAULT 1,
    is_secret TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Conquistas desbloqueadas pelos usuários
CREATE TABLE user_achievements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    achievement_id INT NOT NULL,
    unlocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_achievement (user_id, achievement_id)
);

-- Categorias de Cursos
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    color VARCHAR(7) DEFAULT '#6366f1',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Cursos
CREATE TABLE courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    description TEXT,
    thumbnail VARCHAR(255),
    difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    estimated_hours INT DEFAULT 0,
    xp_reward INT DEFAULT 100,
    coin_reward INT DEFAULT 10,
    is_free TINYINT(1) DEFAULT 0,
    price DECIMAL(10,2) DEFAULT 0.00,
    is_published TINYINT(1) DEFAULT 0,
    instructor_id INT,
    total_students INT DEFAULT 0,
    average_rating DECIMAL(3,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Módulos dos Cursos
CREATE TABLE modules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    order_index INT DEFAULT 0,
    xp_reward INT DEFAULT 50,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Lições
CREATE TABLE lessons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    module_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content_type ENUM('video', 'text', 'quiz', 'exercise', 'project') DEFAULT 'text',
    content LONGTEXT,
    video_url VARCHAR(255),
    duration_minutes INT DEFAULT 0,
    order_index INT DEFAULT 0,
    xp_reward INT DEFAULT 10,
    is_free_preview TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
);

-- Matrículas
CREATE TABLE enrollments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    progress_percentage DECIMAL(5,2) DEFAULT 0.00,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (user_id, course_id)
);

-- Progresso nas Lições
CREATE TABLE lesson_progress (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    is_completed TINYINT(1) DEFAULT 0,
    progress_seconds INT DEFAULT 0,
    completed_at TIMESTAMP NULL,
    xp_earned INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    UNIQUE KEY unique_lesson_progress (user_id, lesson_id)
);

-- Notícias/Blog
CREATE TABLE news (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    excerpt TEXT,
    content LONGTEXT,
    thumbnail VARCHAR(255),
    author_id INT,
    category ENUM('update', 'tutorial', 'news', 'event') DEFAULT 'news',
    is_featured TINYINT(1) DEFAULT 0,
    is_published TINYINT(1) DEFAULT 0,
    views INT DEFAULT 0,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Histórico de XP
CREATE TABLE xp_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    xp_amount INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    description VARCHAR(255),
    reference_id INT,
    reference_type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Ranking/Leaderboard semanal
CREATE TABLE weekly_leaderboard (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    week_start DATE NOT NULL,
    xp_earned INT DEFAULT 0,
    lessons_completed INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_weekly (user_id, week_start)
);

-- Tokens de recuperação de senha
CREATE TABLE password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sessões de usuário
CREATE TABLE user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================
-- DADOS INICIAIS
-- =====================

-- Níveis
INSERT INTO levels (level_number, title, xp_required, badge_icon, color) VALUES
(1, 'Iniciante', 0, '🌱', '#10b981'),
(2, 'Aprendiz', 100, '📚', '#6366f1'),
(3, 'Estudante', 300, '✏️', '#8b5cf6'),
(4, 'Praticante', 600, '💻', '#ec4899'),
(5, 'Desenvolvedor Jr', 1000, '🚀', '#f59e0b'),
(6, 'Desenvolvedor', 1500, '⚡', '#ef4444'),
(7, 'Desenvolvedor Sr', 2500, '🔥', '#dc2626'),
(8, 'Especialista', 4000, '💎', '#0ea5e9'),
(9, 'Mestre', 6000, '👑', '#fbbf24'),
(10, 'Lenda', 10000, '🏆', '#f59e0b');

-- Conquistas
INSERT INTO achievements (name, description, icon, xp_reward, coin_reward, requirement_type, requirement_value) VALUES
('Primeiro Passo', 'Complete sua primeira lição', '🎯', 10, 5, 'lessons_completed', 1),
('Estudante Dedicado', 'Complete 10 lições', '📖', 50, 15, 'lessons_completed', 10),
('Maratonista', 'Complete 50 lições', '🏃', 150, 50, 'lessons_completed', 50),
('Formando', 'Complete seu primeiro curso', '🎓', 100, 30, 'courses_completed', 1),
('Colecionador', 'Complete 5 cursos', '🏅', 300, 100, 'courses_completed', 5),
('Constante', 'Mantenha um streak de 7 dias', '🔥', 70, 25, 'streak', 7),
('Imparável', 'Mantenha um streak de 30 dias', '⚡', 300, 100, 'streak', 30),
('Centurião', 'Alcance 1000 XP', '💯', 50, 20, 'xp_earned', 1000),
('Lendário', 'Alcance 10000 XP', '🌟', 500, 200, 'xp_earned', 10000);

-- Categorias
INSERT INTO categories (name, slug, description, icon, color) VALUES
('Phaser 3', 'phaser-3', 'Framework JavaScript para jogos 2D', '🎮', '#6366f1'),
('React', 'react', 'Biblioteca JavaScript para interfaces', '⚛️', '#61dafb'),
('JavaScript', 'javascript', 'Linguagem de programação web', '📜', '#f7df1e'),
('TypeScript', 'typescript', 'JavaScript com tipagem estática', '📘', '#3178c6'),
('Game Design', 'game-design', 'Princípios de design de jogos', '🎨', '#ec4899'),
('Projetos Práticos', 'projetos', 'Projetos completos do início ao fim', '🚀', '#10b981');

-- Admin padrão (senha: admin123)
INSERT INTO users (username, email, password, full_name, role, xp_total, level) VALUES
('admin', 'admin@gamedev.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'admin', 0, 1);

-- Curso de exemplo
INSERT INTO courses (category_id, title, slug, description, difficulty, estimated_hours, xp_reward, is_free, is_published, instructor_id) VALUES
(1, 'Introdução ao Phaser 3', 'introducao-phaser-3', 'Aprenda os fundamentos do Phaser 3 e crie seu primeiro jogo 2D!', 'beginner', 5, 200, 1, 1, 1),
(2, 'React do Zero ao Avançado', 'react-zero-avancado', 'Domine React com hooks, context e muito mais!', 'intermediate', 15, 500, 0, 1, 1);

-- Notícias de exemplo
INSERT INTO news (title, slug, excerpt, content, category, is_featured, is_published, author_id, published_at) VALUES
('Bem-vindo ao GameDev Academy!', 'bem-vindo-gamedev-academy', 'Conheça nossa plataforma de ensino focada em desenvolvimento de jogos.', '<p>Estamos muito felizes em ter você aqui! O GameDev Academy foi criado para ensinar desenvolvimento de jogos de forma prática e gamificada.</p>', 'news', 1, 1, 1, NOW()),
('Novo curso: Phaser 3 do Zero', 'novo-curso-phaser-3', 'Aprenda a criar jogos 2D incríveis com Phaser 3!', '<p>Lançamos nosso curso completo de Phaser 3. Venha aprender!</p>', 'update', 0, 1, 1, NOW());
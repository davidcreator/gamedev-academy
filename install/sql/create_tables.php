<?php
// install/sql/create_tables.php

function executeDatabaseSetup($pdo) {
    // Criar tabelas principais
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
            is_active TINYINT(1) DEFAULT 1,
            email_verified TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_email (email),
            INDEX idx_xp (xp_total)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) UNIQUE NOT NULL,
            description TEXT,
            icon VARCHAR(50),
            color VARCHAR(7) DEFAULT '#6366f1',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_slug (slug)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS courses (
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
            FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_slug (slug),
            INDEX idx_published (is_published)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Inserir dados iniciais
    $pdo->exec("
        INSERT IGNORE INTO categories (name, slug, description, icon, color) VALUES
        ('Phaser 3', 'phaser-3', 'Framework JavaScript para jogos 2D', '🎮', '#6366f1'),
        ('React', 'react', 'Biblioteca JavaScript para interfaces', '⚛️', '#61dafb'),
        ('JavaScript', 'javascript', 'Linguagem de programação web', '📜', '#f7df1e'),
        ('TypeScript', 'typescript', 'JavaScript com tipagem estática', '📘', '#3178c6'),
        ('Game Design', 'game-design', 'Princípios de design de jogos', '🎨', '#ec4899')
    ");
}
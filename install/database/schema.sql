-- ================================================================
-- GameDev Academy - Schema Completo Corrigido
-- Versão: 2.0
-- ================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE';

-- ================================================================
-- 1. TABELAS DE USUÁRIOS E AUTENTICAÇÃO
-- ================================================================

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('student', 'instructor', 'admin', 'super_admin') NOT NULL DEFAULT 'student',
    `avatar` VARCHAR(500) DEFAULT NULL,
    `bio` TEXT DEFAULT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `website` VARCHAR(255) DEFAULT NULL,
    `social_github` VARCHAR(255) DEFAULT NULL,
    `social_linkedin` VARCHAR(255) DEFAULT NULL,
    `social_twitter` VARCHAR(255) DEFAULT NULL,
    `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `email_verification_token` VARCHAR(100) DEFAULT NULL,
    `password_reset_token` VARCHAR(100) DEFAULT NULL,
    `password_reset_expires` TIMESTAMP NULL DEFAULT NULL,
    `last_login_at` TIMESTAMP NULL DEFAULT NULL,
    `last_login_ip` VARCHAR(45) DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `preferences` JSON DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_email` (`email`),
    KEY `idx_users_role` (`role`),
    KEY `idx_users_active` (`is_active`),
    KEY `idx_users_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `user_sessions`;
CREATE TABLE `user_sessions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `session_token` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` TEXT DEFAULT NULL,
    `expires_at` TIMESTAMP NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_session_token` (`session_token`),
    KEY `idx_sessions_user` (`user_id`),
    KEY `idx_sessions_expires` (`expires_at`),
    CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 2. TABELAS DE CATEGORIAS E TAGS
-- ================================================================

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(120) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `icon` VARCHAR(100) DEFAULT NULL,
    `color` VARCHAR(7) DEFAULT '#6366f1',
    `parent_id` INT UNSIGNED DEFAULT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_categories_slug` (`slug`),
    KEY `idx_categories_parent` (`parent_id`),
    KEY `idx_categories_active` (`is_active`),
    CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) 
        REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `slug` VARCHAR(60) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_tags_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 3. TABELAS DE CURSOS
-- ================================================================

DROP TABLE IF EXISTS `courses`;
CREATE TABLE `courses` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(280) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `short_description` VARCHAR(500) DEFAULT NULL,
    `thumbnail` VARCHAR(500) DEFAULT NULL,
    `preview_video` VARCHAR(500) DEFAULT NULL,
    `instructor_id` INT UNSIGNED NOT NULL,
    `category_id` INT UNSIGNED DEFAULT NULL,
    `level` ENUM('beginner', 'intermediate', 'advanced', 'all_levels') NOT NULL DEFAULT 'beginner',
    `language` VARCHAR(10) NOT NULL DEFAULT 'pt-BR',
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `original_price` DECIMAL(10,2) DEFAULT NULL,
    `currency` VARCHAR(3) NOT NULL DEFAULT 'BRL',
    `duration_hours` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Duração total em minutos',
    `total_lessons` INT UNSIGNED NOT NULL DEFAULT 0,
    `requirements` JSON DEFAULT NULL,
    `what_you_learn` JSON DEFAULT NULL,
    `target_audience` JSON DEFAULT NULL,
    `game_engine` ENUM('unity', 'unreal', 'godot', 'gamemaker', 'construct', 'phaser', 'other', 'none') DEFAULT NULL,
    `status` ENUM('draft', 'pending_review', 'published', 'archived') NOT NULL DEFAULT 'draft',
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `is_free` TINYINT(1) NOT NULL DEFAULT 0,
    `enrollment_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `rating_average` DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    `rating_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `published_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_courses_slug` (`slug`),
    KEY `idx_courses_instructor` (`instructor_id`),
    KEY `idx_courses_category` (`category_id`),
    KEY `idx_courses_status` (`status`),
    KEY `idx_courses_featured` (`is_featured`),
    KEY `idx_courses_level` (`level`),
    KEY `idx_courses_engine` (`game_engine`),
    KEY `idx_courses_price` (`price`),
    KEY `idx_courses_rating` (`rating_average`),
    KEY `idx_courses_published` (`published_at`),
    KEY `idx_courses_created` (`created_at`),
    FULLTEXT KEY `ft_courses_search` (`title`, `description`),
    CONSTRAINT `fk_courses_instructor` FOREIGN KEY (`instructor_id`) 
        REFERENCES `users` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_courses_category` FOREIGN KEY (`category_id`) 
        REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `course_tags`;
CREATE TABLE `course_tags` (
    `course_id` INT UNSIGNED NOT NULL,
    `tag_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`course_id`, `tag_id`),
    KEY `idx_coursetags_tag` (`tag_id`),
    CONSTRAINT `fk_coursetags_course` FOREIGN KEY (`course_id`) 
        REFERENCES `courses` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_coursetags_tag` FOREIGN KEY (`tag_id`) 
        REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 4. TABELAS DE MÓDULOS E AULAS
-- ================================================================

DROP TABLE IF EXISTS `modules`;
CREATE TABLE `modules` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_free_preview` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_modules_course` (`course_id`),
    KEY `idx_modules_order` (`course_id`, `sort_order`),
    CONSTRAINT `fk_modules_course` FOREIGN KEY (`course_id`) 
        REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `lessons`;
CREATE TABLE `lessons` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `module_id` INT UNSIGNED NOT NULL,
    `course_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(280) NOT NULL,
    `content_type` ENUM('video', 'text', 'quiz', 'assignment', 'download', 'live') NOT NULL DEFAULT 'video',
    `content` LONGTEXT DEFAULT NULL COMMENT 'Conteúdo texto/HTML da aula',
    `video_url` VARCHAR(500) DEFAULT NULL,
    `video_provider` ENUM('youtube', 'vimeo', 'bunny', 'self_hosted', 'other') DEFAULT NULL,
    `video_duration` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Duração em segundos',
    `attachments` JSON DEFAULT NULL COMMENT 'Array de arquivos anexos',
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_free_preview` TINYINT(1) NOT NULL DEFAULT 0,
    `is_published` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_lessons_slug_course` (`course_id`, `slug`),
    KEY `idx_lessons_module` (`module_id`),
    KEY `idx_lessons_order` (`module_id`, `sort_order`),
    KEY `idx_lessons_type` (`content_type`),
    CONSTRAINT `fk_lessons_module` FOREIGN KEY (`module_id`) 
        REFERENCES `modules` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_lessons_course` FOREIGN KEY (`course_id`) 
        REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 5. TABELAS DE MATRÍCULAS E PROGRESSO
-- ================================================================

DROP TABLE IF EXISTS `enrollments`;
CREATE TABLE `enrollments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `course_id` INT UNSIGNED NOT NULL,
    `status` ENUM('active', 'completed', 'cancelled', 'expired', 'refunded') NOT NULL DEFAULT 'active',
    `progress_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `enrolled_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completed_at` TIMESTAMP NULL DEFAULT NULL,
    `expires_at` TIMESTAMP NULL DEFAULT NULL,
    `payment_id` INT UNSIGNED DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_enrollment` (`user_id`, `course_id`),
    KEY `idx_enrollments_course` (`course_id`),
    KEY `idx_enrollments_status` (`status`),
    KEY `idx_enrollments_enrolled` (`enrolled_at`),
    KEY `idx_enrollments_created` (`created_at`),
    CONSTRAINT `fk_enrollments_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_enrollments_course` FOREIGN KEY (`course_id`) 
        REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `lesson_progress`;
CREATE TABLE `lesson_progress` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `lesson_id` INT UNSIGNED NOT NULL,
    `course_id` INT UNSIGNED NOT NULL,
    `status` ENUM('not_started', 'in_progress', 'completed') NOT NULL DEFAULT 'not_started',
    `watch_time` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Tempo assistido em segundos',
    `last_position` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Posição do vídeo em segundos',
    `completed_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_lesson_progress` (`user_id`, `lesson_id`),
    KEY `idx_progress_course` (`course_id`),
    KEY `idx_progress_status` (`status`),
    CONSTRAINT `fk_progress_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_progress_lesson` FOREIGN KEY (`lesson_id`) 
        REFERENCES `lessons` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_progress_course` FOREIGN KEY (`course_id`) 
        REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 6. TABELAS DE QUIZZES E AVALIAÇÕES
-- ================================================================

DROP TABLE IF EXISTS `quizzes`;
CREATE TABLE `quizzes` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `lesson_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `time_limit` INT UNSIGNED DEFAULT NULL COMMENT 'Limite em minutos, NULL = sem limite',
    `pass_percentage` DECIMAL(5,2) NOT NULL DEFAULT 70.00,
    `max_attempts` INT UNSIGNED DEFAULT NULL COMMENT 'NULL = ilimitado',
    `shuffle_questions` TINYINT(1) NOT NULL DEFAULT 0,
    `show_correct_answers` TINYINT(1) NOT NULL DEFAULT 1,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_quizzes_lesson` (`lesson_id`),
    CONSTRAINT `fk_quizzes_lesson` FOREIGN KEY (`lesson_id`) 
        REFERENCES `lessons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `quiz_questions`;
CREATE TABLE `quiz_questions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `quiz_id` INT UNSIGNED NOT NULL,
    `question_type` ENUM('multiple_choice', 'true_false', 'short_answer', 'code') NOT NULL DEFAULT 'multiple_choice',
    `question_text` TEXT NOT NULL,
    `code_snippet` TEXT DEFAULT NULL,
    `explanation` TEXT DEFAULT NULL,
    `points` INT UNSIGNED NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_questions_quiz` (`quiz_id`),
    CONSTRAINT `fk_questions_quiz` FOREIGN KEY (`quiz_id`) 
        REFERENCES `quizzes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `quiz_options`;
CREATE TABLE `quiz_options` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `question_id` INT UNSIGNED NOT NULL,
    `option_text` TEXT NOT NULL,
    `is_correct` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_options_question` (`question_id`),
    CONSTRAINT `fk_options_question` FOREIGN KEY (`question_id`) 
        REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `quiz_attempts`;
CREATE TABLE `quiz_attempts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `quiz_id` INT UNSIGNED NOT NULL,
    `score` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `total_points` INT UNSIGNED NOT NULL DEFAULT 0,
    `earned_points` INT UNSIGNED NOT NULL DEFAULT 0,
    `passed` TINYINT(1) NOT NULL DEFAULT 0,
    `answers` JSON DEFAULT NULL,
    `started_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completed_at` TIMESTAMP NULL DEFAULT NULL,
    `time_spent` INT UNSIGNED DEFAULT NULL COMMENT 'Tempo em segundos',
    PRIMARY KEY (`id`),
    KEY `idx_attempts_user` (`user_id`),
    KEY `idx_attempts_quiz` (`quiz_id`),
    CONSTRAINT `fk_attempts_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_attempts_quiz` FOREIGN KEY (`quiz_id`) 
        REFERENCES `quizzes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 7. TABELAS DE ASSIGNMENTS (TAREFAS/PROJETOS)
-- ================================================================

DROP TABLE IF EXISTS `assignments`;
CREATE TABLE `assignments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `lesson_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` LONGTEXT NOT NULL,
    `instructions` LONGTEXT DEFAULT NULL,
    `max_score` INT UNSIGNED NOT NULL DEFAULT 100,
    `due_days` INT UNSIGNED DEFAULT NULL COMMENT 'Dias após matrícula para entregar',
    `allow_late` TINYINT(1) NOT NULL DEFAULT 0,
    `submission_type` ENUM('file', 'text', 'url', 'github') NOT NULL DEFAULT 'file',
    `allowed_extensions` JSON DEFAULT NULL COMMENT '["zip","rar","pdf"]',
    `max_file_size` INT UNSIGNED DEFAULT 52428800 COMMENT 'Em bytes, default 50MB',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_assignments_lesson` (`lesson_id`),
    CONSTRAINT `fk_assignments_lesson` FOREIGN KEY (`lesson_id`) 
        REFERENCES `lessons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `assignment_submissions`;
CREATE TABLE `assignment_submissions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `assignment_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `content` LONGTEXT DEFAULT NULL,
    `file_url` VARCHAR(500) DEFAULT NULL,
    `github_url` VARCHAR(500) DEFAULT NULL,
    `score` INT UNSIGNED DEFAULT NULL,
    `feedback` TEXT DEFAULT NULL,
    `status` ENUM('submitted', 'under_review', 'graded', 'returned', 'resubmitted') NOT NULL DEFAULT 'submitted',
    `graded_by` INT UNSIGNED DEFAULT NULL,
    `graded_at` TIMESTAMP NULL DEFAULT NULL,
    `submitted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_submissions_assignment` (`assignment_id`),
    KEY `idx_submissions_user` (`user_id`),
    KEY `idx_submissions_status` (`status`),
    CONSTRAINT `fk_submissions_assignment` FOREIGN KEY (`assignment_id`) 
        REFERENCES `assignments` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_submissions_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_submissions_grader` FOREIGN KEY (`graded_by`) 
        REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 8. TABELAS DE REVIEWS (AVALIAÇÕES DE CURSOS)
-- ================================================================

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `course_id` INT UNSIGNED NOT NULL,
    `rating` TINYINT UNSIGNED NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `title` VARCHAR(255) DEFAULT NULL,
    `comment` TEXT DEFAULT NULL,
    `is_approved` TINYINT(1) NOT NULL DEFAULT 0,
    `instructor_reply` TEXT DEFAULT NULL,
    `instructor_reply_at` TIMESTAMP NULL DEFAULT NULL,
    `helpful_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_review` (`user_id`, `course_id`),
    KEY `idx_reviews_course` (`course_id`),
    KEY `idx_reviews_rating` (`rating`),
    KEY `idx_reviews_approved` (`is_approved`),
    CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reviews_course` FOREIGN KEY (`course_id`) 
        REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 9. TABELAS DE CERTIFICADOS
-- ================================================================

DROP TABLE IF EXISTS `certificate_templates`;
CREATE TABLE `certificate_templates` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `html_template` LONGTEXT NOT NULL,
    `css_styles` LONGTEXT DEFAULT NULL,
    `background_image` VARCHAR(500) DEFAULT NULL,
    `is_default` TINYINT(1) NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `certificates`;
CREATE TABLE `certificates` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `course_id` INT UNSIGNED NOT NULL,
    `template_id` INT UNSIGNED DEFAULT NULL,
    `certificate_code` VARCHAR(50) NOT NULL COMMENT 'Código único de verificação',
    `certificate_url` VARCHAR(500) DEFAULT NULL,
    `issued_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `metadata` JSON DEFAULT NULL COMMENT 'Dados extras: nota final, horas, etc',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_certificate` (`user_id`, `course_id`),
    UNIQUE KEY `uk_certificate_code` (`certificate_code`),
    KEY `idx_certificates_course` (`course_id`),
    CONSTRAINT `fk_certificates_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_certificates_course` FOREIGN KEY (`course_id`) 
        REFERENCES `courses` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_certificates_template` FOREIGN KEY (`template_id`) 
        REFERENCES `certificate_templates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 10. TABELAS DE PAGAMENTOS
-- ================================================================

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `course_id` INT UNSIGNED DEFAULT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `currency` VARCHAR(3) NOT NULL DEFAULT 'BRL',
    `payment_method` ENUM('credit_card', 'pix', 'boleto', 'paypal', 'stripe', 'free', 'coupon') NOT NULL,
    `payment_gateway` VARCHAR(50) DEFAULT NULL,
    `gateway_transaction_id` VARCHAR(255) DEFAULT NULL,
    `gateway_response` JSON DEFAULT NULL,
    `status` ENUM('pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded', 'disputed') NOT NULL DEFAULT 'pending',
    `coupon_id` INT UNSIGNED DEFAULT NULL,
    `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `refund_reason` TEXT DEFAULT NULL,
    `refunded_at` TIMESTAMP NULL DEFAULT NULL,
    `paid_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_payments_user` (`user_id`),
    KEY `idx_payments_course` (`course_id`),
    KEY `idx_payments_status` (`status`),
    KEY `idx_payments_gateway_tx` (`gateway_transaction_id`),
    KEY `idx_payments_created` (`created_at`),
    CONSTRAINT `fk_payments_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_payments_course` FOREIGN KEY (`course_id`) 
        REFERENCES `courses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 11. TABELAS DE CUPONS
-- ================================================================

DROP TABLE IF EXISTS `coupons`;
CREATE TABLE `coupons` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(50) NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `discount_type` ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage',
    `discount_value` DECIMAL(10,2) NOT NULL,
    `min_purchase` DECIMAL(10,2) DEFAULT NULL,
    `max_discount` DECIMAL(10,2) DEFAULT NULL COMMENT 'Teto para desconto percentual',
    `max_uses` INT UNSIGNED DEFAULT NULL COMMENT 'NULL = ilimitado',
    `max_uses_per_user` INT UNSIGNED NOT NULL DEFAULT 1,
    `used_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `applicable_courses` JSON DEFAULT NULL COMMENT 'Array de course_ids, NULL = todos',
    `starts_at` TIMESTAMP NULL DEFAULT NULL,
    `expires_at` TIMESTAMP NULL DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_coupon_code` (`code`),
    KEY `idx_coupons_active` (`is_active`),
    KEY `idx_coupons_expires` (`expires_at`),
    CONSTRAINT `fk_coupons_creator` FOREIGN KEY (`created_by`) 
        REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `coupon_uses`;
CREATE TABLE `coupon_uses` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `coupon_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `payment_id` INT UNSIGNED DEFAULT NULL,
    `used_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_couponuses_coupon` (`coupon_id`),
    KEY `idx_couponuses_user` (`user_id`),
    CONSTRAINT `fk_couponuses_coupon` FOREIGN KEY (`coupon_id`) 
        REFERENCES `coupons` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_couponuses_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 12. TABELAS DE GAMIFICAÇÃO
-- ================================================================

DROP TABLE IF EXISTS `badges`;
CREATE TABLE `badges` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(120) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `icon` VARCHAR(500) NOT NULL,
    `category` ENUM('course', 'engagement', 'achievement', 'special') NOT NULL DEFAULT 'achievement',
    `criteria_type` VARCHAR(50) NOT NULL COMMENT 'courses_completed, lessons_watched, streak_days, etc',
    `criteria_value` INT UNSIGNED NOT NULL DEFAULT 1,
    `points_reward` INT UNSIGNED NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_badges_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `user_badges`;
CREATE TABLE `user_badges` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `badge_id` INT UNSIGNED NOT NULL,
    `earned_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_badge` (`user_id`, `badge_id`),
    KEY `idx_userbadges_badge` (`badge_id`),
    CONSTRAINT `fk_userbadges_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_userbadges_badge` FOREIGN KEY (`badge_id`) 
        REFERENCES `badges` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `user_points`;
CREATE TABLE `user_points` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `points` INT NOT NULL,
    `action` VARCHAR(50) NOT NULL COMMENT 'lesson_complete, quiz_pass, course_complete, daily_login, etc',
    `reference_type` VARCHAR(50) DEFAULT NULL,
    `reference_id` INT UNSIGNED DEFAULT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_points_user` (`user_id`),
    KEY `idx_points_action` (`action`),
    KEY `idx_points_created` (`created_at`),
    CONSTRAINT `fk_points_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `user_streaks`;
CREATE TABLE `user_streaks` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `current_streak` INT UNSIGNED NOT NULL DEFAULT 0,
    `longest_streak` INT UNSIGNED NOT NULL DEFAULT 0,
    `last_activity_date` DATE DEFAULT NULL,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_streak_user` (`user_id`),
    CONSTRAINT `fk_streaks_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `leaderboard`;
CREATE TABLE `leaderboard` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `total_points` INT UNSIGNED NOT NULL DEFAULT 0,
    `courses_completed` INT UNSIGNED NOT NULL DEFAULT 0,
    `badges_earned` INT UNSIGNED NOT NULL DEFAULT 0,
    `rank_position` INT UNSIGNED DEFAULT NULL,
    `period` ENUM('weekly', 'monthly', 'all_time') NOT NULL DEFAULT 'all_time',
    `period_start` DATE DEFAULT NULL,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_leaderboard` (`user_id`, `period`, `period_start`),
    KEY `idx_leaderboard_rank` (`period`, `rank_position`),
    CONSTRAINT `fk_leaderboard_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 13. TABELAS DE NOTIFICAÇÕES
-- ================================================================

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `type` VARCHAR(50) NOT NULL COMMENT 'enrollment, achievement, announcement, reply, etc',
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `icon` VARCHAR(100) DEFAULT NULL,
    `action_url` VARCHAR(500) DEFAULT NULL,
    `data` JSON DEFAULT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `read_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_notifications_user` (`user_id`),
    KEY `idx_notifications_read` (`user_id`, `is_read`),
    KEY `idx_notifications_type` (`type`),
    KEY `idx_notifications_created` (`created_at`),
    CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `notification_preferences`;
CREATE TABLE `notification_preferences` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `notification_type` VARCHAR(50) NOT NULL,
    `email_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `push_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `in_app_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_notifpref` (`user_id`, `notification_type`),
    CONSTRAINT `fk_notifpref_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 14. TABELAS DE FÓRUM/DISCUSSÕES
-- ================================================================

DROP TABLE IF EXISTS `discussions`;
CREATE TABLE `discussions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_id` INT UNSIGNED DEFAULT NULL,
    `lesson_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `content` LONGTEXT NOT NULL,
    `is_pinned` TINYINT(1) NOT NULL DEFAULT 0,
    `is_resolved` TINYINT(1) NOT NULL DEFAULT 0,
    `reply_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `last_reply_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_discussions_course` (`course_id`),
    KEY `idx_discussions_lesson` (`lesson_id`),
    KEY `idx_discussions_user` (`user_id`),
    KEY `idx_discussions_pinned` (`is_pinned`),
    FULLTEXT KEY `ft_discussions_search` (`title`, `content`),
    CONSTRAINT `fk_discussions_course` FOREIGN KEY (`course_id`) 
        REFERENCES `courses` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_discussions_lesson` FOREIGN KEY (`lesson_id`) 
        REFERENCES `lessons` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_discussions_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `discussion_replies`;
CREATE TABLE `discussion_replies` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `discussion_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `parent_reply_id` INT UNSIGNED DEFAULT NULL,
    `content` LONGTEXT NOT NULL,
    `is_best_answer` TINYINT(1) NOT NULL DEFAULT 0,
    `upvote_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_replies_discussion` (`discussion_id`),
    KEY `idx_replies_user` (`user_id`),
    KEY `idx_replies_parent` (`parent_reply_id`),
    CONSTRAINT `fk_replies_discussion` FOREIGN KEY (`discussion_id`) 
        REFERENCES `discussions` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_replies_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_replies_parent` FOREIGN KEY (`parent_reply_id`) 
        REFERENCES `discussion_replies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 15. TABELAS DE SUPORTE
-- ================================================================

DROP TABLE IF EXISTS `support_tickets`;
CREATE TABLE `support_tickets` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `description` LONGTEXT NOT NULL,
    `category` ENUM('technical', 'billing', 'content', 'account', 'other') NOT NULL DEFAULT 'other',
    `priority` ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
    `status` ENUM('open', 'in_progress', 'waiting_response', 'resolved', 'closed') NOT NULL DEFAULT 'open',
    `assigned_to` INT UNSIGNED DEFAULT NULL,
    `course_id` INT UNSIGNED DEFAULT NULL COMMENT 'Curso relacionado ao ticket',
    `resolved_at` TIMESTAMP NULL DEFAULT NULL,
    `closed_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_tickets_user` (`user_id`),
    KEY `idx_tickets_status` (`status`),
    KEY `idx_tickets_priority` (`priority`),
    KEY `idx_tickets_assigned` (`assigned_to`),
    KEY `idx_tickets_created` (`created_at`),
    CONSTRAINT `fk_tickets_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tickets_assigned` FOREIGN KEY (`assigned_to`) 
        REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_tickets_course` FOREIGN KEY (`course_id`) 
        REFERENCES `courses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `ticket_messages`;
CREATE TABLE `ticket_messages` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ticket_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `message` LONGTEXT NOT NULL,
    `attachments` JSON DEFAULT NULL,
    `is_internal_note` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ticketmsg_ticket` (`ticket_id`),
    CONSTRAINT `fk_ticketmsg_ticket` FOREIGN KEY (`ticket_id`) 
        REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ticketmsg_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 16. TABELAS DE BLOG/CONTEÚDO
-- ================================================================

DROP TABLE IF EXISTS `blog_posts`;
CREATE TABLE `blog_posts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(280) NOT NULL,
    `excerpt` VARCHAR(500) DEFAULT NULL,
    `content` LONGTEXT NOT NULL,
    `featured_image` VARCHAR(500) DEFAULT NULL,
    `author_id` INT UNSIGNED NOT NULL,
    `category_id` INT UNSIGNED DEFAULT NULL,
    `status` ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
    `view_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `published_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_blogposts_slug` (`slug`),
    KEY `idx_blogposts_author` (`author_id`),
    KEY `idx_blogposts_status` (`status`),
    KEY `idx_blogposts_published` (`published_at`),
    FULLTEXT KEY `ft_blogposts_search` (`title`, `content`),
    CONSTRAINT `fk_blogposts_author` FOREIGN KEY (`author_id`) 
        REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 17. TABELAS DE CONFIGURAÇÕES E SISTEMA
-- ================================================================

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` LONGTEXT DEFAULT NULL,
    `setting_type` ENUM('string', 'number', 'boolean', 'json', 'html') NOT NULL DEFAULT 'string',
    `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
    `description` VARCHAR(255) DEFAULT NULL,
    `is_public` TINYINT(1) NOT NULL DEFAULT 0,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_settings_key` (`setting_key`),
    KEY `idx_settings_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(280) NOT NULL,
    `content` LONGTEXT NOT NULL,
    `meta_title` VARCHAR(255) DEFAULT NULL,
    `meta_description` VARCHAR(500) DEFAULT NULL,
    `is_published` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_pages_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `media`;
CREATE TABLE `media` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `filename` VARCHAR(255) NOT NULL,
    `original_filename` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_url` VARCHAR(500) NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `file_size` BIGINT UNSIGNED NOT NULL COMMENT 'Em bytes',
    `alt_text` VARCHAR(255) DEFAULT NULL,
    `folder` VARCHAR(100) DEFAULT 'general',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_media_user` (`user_id`),
    KEY `idx_media_folder` (`folder`),
    KEY `idx_media_mime` (`mime_type`),
    CONSTRAINT `fk_media_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 18. TABELAS DE LOGS E AUDITORIA
-- ================================================================

DROP TABLE IF EXISTS `activity_log`;
CREATE TABLE `activity_log` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `entity_type` VARCHAR(50) DEFAULT NULL,
    `entity_id` INT UNSIGNED DEFAULT NULL,
    `old_values` JSON DEFAULT NULL,
    `new_values` JSON DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_actlog_user` (`user_id`),
    KEY `idx_actlog_action` (`action`),
    KEY `idx_actlog_entity` (`entity_type`, `entity_id`),
    KEY `idx_actlog_created` (`created_at`),
    CONSTRAINT `fk_actlog_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `email_log`;
CREATE TABLE `email_log` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `to_email` VARCHAR(150) NOT NULL,
    `to_name` VARCHAR(100) DEFAULT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `template` VARCHAR(50) DEFAULT NULL,
    `status` ENUM('queued', 'sent', 'failed') NOT NULL DEFAULT 'queued',
    `error_message` TEXT DEFAULT NULL,
    `sent_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_emaillog_status` (`status`),
    KEY `idx_emaillog_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 19. TABELAS DE WISHLIST E FAVORITOS
-- ================================================================

DROP TABLE IF EXISTS `wishlists`;
CREATE TABLE `wishlists` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `course_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_wishlist` (`user_id`, `course_id`),
    KEY `idx_wishlist_course` (`course_id`),
    CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_wishlist_course` FOREIGN KEY (`course_id`) 
        REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 20. TABELA DE NOTAS/BOOKMARKS DO ALUNO
-- ================================================================

DROP TABLE IF EXISTS `student_notes`;
CREATE TABLE `student_notes` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `lesson_id` INT UNSIGNED NOT NULL,
    `content` TEXT NOT NULL,
    `timestamp_seconds` INT UNSIGNED DEFAULT NULL COMMENT 'Momento do vídeo',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_notes_user_lesson` (`user_id`, `lesson_id`),
    CONSTRAINT `fk_notes_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_notes_lesson` FOREIGN KEY (`lesson_id`) 
        REFERENCES `lessons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ================================================================
-- DADOS INICIAIS (SEEDS)
-- ================================================================

-- Admin padrão (senha: admin123 - MUDAR EM PRODUÇÃO!)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `email_verified_at`, `is_active`) VALUES
('Administrador', 'admin@gamedevacademy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', NOW(), 1);

-- Categorias iniciais
INSERT INTO `categories` (`name`, `slug`, `description`, `icon`, `color`, `sort_order`) VALUES
('Unity', 'unity', 'Desenvolvimento de jogos com Unity Engine', 'fas fa-cube', '#000000', 1),
('Unreal Engine', 'unreal-engine', 'Desenvolvimento com Unreal Engine', 'fas fa-fire', '#2563eb', 2),
('Godot', 'godot', 'Desenvolvimento com Godot Engine', 'fas fa-robot', '#478cbf', 3),
('Game Design', 'game-design', 'Princípios e teoria de game design', 'fas fa-pencil-ruler', '#8b5cf6', 4),
('Arte 2D', 'arte-2d', 'Pixel art, sprites e arte 2D para jogos', 'fas fa-palette', '#ec4899', 5),
('Arte 3D', 'arte-3d', 'Modelagem, texturização e arte 3D', 'fas fa-shapes', '#f59e0b', 6),
('Programação', 'programacao', 'Fundamentos de programação para jogos', 'fas fa-code', '#10b981', 7),
('Áudio', 'audio', 'Sound design e música para jogos', 'fas fa-music', '#6366f1', 8),
('Narrativa', 'narrativa', 'Roteiro e narrativa para jogos', 'fas fa-book', '#ef4444', 9),
('Mobile Games', 'mobile-games', 'Desenvolvimento de jogos mobile', 'fas fa-mobile-alt', '#14b8a6', 10);

-- Configurações iniciais
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `description`, `is_public`) VALUES
('site_name', 'GameDev Academy', 'string', 'general', 'Nome do site', 1),
('site_description', 'Aprenda a criar jogos do zero ao profissional', 'string', 'general', 'Descrição do site', 1),
('site_logo', '/assets/images/logo.png', 'string', 'general', 'Logo do site', 1),
('site_favicon', '/assets/images/favicon.ico', 'string', 'general', 'Favicon', 1),
('contact_email', 'contato@gamedevacademy.com', 'string', 'general', 'Email de contato', 1),
('currency', 'BRL', 'string', 'payment', 'Moeda padrão', 0),
('stripe_public_key', '', 'string', 'payment', 'Stripe Public Key', 0),
('stripe_secret_key', '', 'string', 'payment', 'Stripe Secret Key', 0),
('smtp_host', '', 'string', 'email', 'Servidor SMTP', 0),
('smtp_port', '587', 'number', 'email', 'Porta SMTP', 0),
('smtp_user', '', 'string', 'email', 'Usuário SMTP', 0),
('smtp_pass', '', 'string', 'email', 'Senha SMTP', 0),
('certificate_enabled', 'true', 'boolean', 'features', 'Habilitar certificados', 0),
('gamification_enabled', 'true', 'boolean', 'features', 'Habilitar gamificação', 0),
('forum_enabled', 'true', 'boolean', 'features', 'Habilitar fórum', 0),
('maintenance_mode', 'false', 'boolean', 'general', 'Modo manutenção', 0),
('items_per_page', '12', 'number', 'general', 'Itens por página', 0);

-- Badges iniciais
INSERT INTO `badges` (`name`, `slug`, `description`, `icon`, `category`, `criteria_type`, `criteria_value`, `points_reward`) VALUES
('Primeiro Passo', 'primeiro-passo', 'Complete sua primeira aula', '🎮', 'achievement', 'lessons_completed', 1, 10),
('Estudante Dedicado', 'estudante-dedicado', 'Complete 10 aulas', '📚', 'engagement', 'lessons_completed', 10, 50),
('Maratonista', 'maratonista', 'Complete 50 aulas', '🏃', 'engagement', 'lessons_completed', 50, 200),
('Primeiro Curso', 'primeiro-curso', 'Complete seu primeiro curso', '🎓', 'course', 'courses_completed', 1, 100),
('Colecionador', 'colecionador', 'Complete 5 cursos', '🏆', 'course', 'courses_completed', 5, 500),
('Mestre dos Jogos', 'mestre-dos-jogos', 'Complete 10 cursos', '👑', 'course', 'courses_completed', 10, 1000),
('Streak 7 Dias', 'streak-7', 'Estude por 7 dias seguidos', '🔥', 'engagement', 'streak_days', 7, 70),
('Streak 30 Dias', 'streak-30', 'Estude por 30 dias seguidos', '⚡', 'engagement', 'streak_days', 30, 300),
('Quiz Master', 'quiz-master', 'Acerte 100% em 10 quizzes', '🧠', 'achievement', 'perfect_quizzes', 10, 250),
('Ajudante', 'ajudante', 'Responda 10 perguntas no fórum', '🤝', 'engagement', 'forum_replies', 10, 150);

-- Páginas estáticas
INSERT INTO `pages` (`title`, `slug`, `content`, `is_published`) VALUES
('Sobre Nós', 'sobre', '<h1>Sobre a GameDev Academy</h1><p>Somos uma plataforma dedicada ao ensino de desenvolvimento de jogos.</p>', 1),
('Termos de Uso', 'termos', '<h1>Termos de Uso</h1><p>Ao usar esta plataforma, você concorda com nossos termos.</p>', 1),
('Política de Privacidade', 'privacidade', '<h1>Política de Privacidade</h1><p>Sua privacidade é importante para nós.</p>', 1),
('FAQ', 'faq', '<h1>Perguntas Frequentes</h1><p>Encontre respostas para as dúvidas mais comuns.</p>', 1);
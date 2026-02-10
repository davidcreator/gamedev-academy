-- =========================================================================
-- GameDev Academy - Database Schema
-- Version: 2.0.0
-- Author: David Creator
-- License: MIT
-- =========================================================================
-- 
-- Este arquivo contém a estrutura completa do banco de dados do GameDev Academy.
-- Certifique-se de ter MySQL 5.7+ ou MariaDB 10.2+ instalado.
-- 
-- Charset: UTF8MB4 (Suporte completo a emojis e caracteres especiais)
-- Collation: utf8mb4_unicode_ci
-- Engine: InnoDB (Suporte a transações e chaves estrangeiras)
-- =========================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- =========================================================================
-- LIMPEZA DE TABELAS EXISTENTES (CUIDADO: ISSO APAGARÁ TODOS OS DADOS!)
-- =========================================================================

DROP TABLE IF EXISTS `gda_activity_log`;
DROP TABLE IF EXISTS `gda_notifications`;
DROP TABLE IF EXISTS `gda_certificates`;
DROP TABLE IF EXISTS `gda_quiz_responses`;
DROP TABLE IF EXISTS `gda_quiz_answers`;
DROP TABLE IF EXISTS `gda_quiz_questions`;
DROP TABLE IF EXISTS `gda_quizzes`;
DROP TABLE IF EXISTS `gda_lesson_notes`;
DROP TABLE IF EXISTS `gda_lesson_bookmarks`;
DROP TABLE IF EXISTS `gda_lesson_progress`;
DROP TABLE IF EXISTS `gda_course_reviews`;
DROP TABLE IF EXISTS `gda_enrollments`;
DROP TABLE IF EXISTS `gda_lessons`;
DROP TABLE IF EXISTS `gda_course_sections`;
DROP TABLE IF EXISTS `gda_course_tags`;
DROP TABLE IF EXISTS `gda_courses`;
DROP TABLE IF EXISTS `gda_categories`;
DROP TABLE IF EXISTS `gda_achievements_earned`;
DROP TABLE IF EXISTS `gda_achievements`;
DROP TABLE IF EXISTS `gda_user_badges`;
DROP TABLE IF EXISTS `gda_badges`;
DROP TABLE IF EXISTS `gda_user_stats`;
DROP TABLE IF EXISTS `gda_user_profiles`;
DROP TABLE IF EXISTS `gda_password_resets`;
DROP TABLE IF EXISTS `gda_sessions`;
DROP TABLE IF EXISTS `gda_users`;
DROP TABLE IF EXISTS `gda_tags`;
DROP TABLE IF EXISTS `gda_media`;
DROP TABLE IF EXISTS `gda_settings`;
DROP TABLE IF EXISTS `gda_migrations`;

-- =========================================================================
-- TABELA: migrations
-- Controle de versões do banco de dados
-- =========================================================================

CREATE TABLE `gda_migrations` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `migration` varchar(255) NOT NULL,
    `batch` int(11) NOT NULL,
    `executed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_migration_batch` (`batch`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: settings
-- Configurações globais do sistema
-- =========================================================================

CREATE TABLE `gda_settings` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `key` varchar(100) NOT NULL,
    `value` text,
    `type` enum('string','integer','boolean','json','array','float') NOT NULL DEFAULT 'string',
    `group` varchar(50) DEFAULT 'general',
    `label` varchar(200) DEFAULT NULL,
    `description` text,
    `is_public` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Se pode ser acessada publicamente',
    `is_editable` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Se pode ser editada pelo admin',
    `validation_rules` json DEFAULT NULL,
    `options` json DEFAULT NULL COMMENT 'Opções para campos select',
    `order` int(10) UNSIGNED DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_settings_key` (`key`),
    KEY `idx_settings_group` (`group`),
    KEY `idx_settings_public` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: users
-- Usuários do sistema
-- =========================================================================

CREATE TABLE `gda_users` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL,
    `email` varchar(100) NOT NULL,
    `password` varchar(255) NOT NULL,
    `name` varchar(100) NOT NULL,
    `role` enum('admin','instructor','moderator','student','guest') NOT NULL DEFAULT 'student',
    `status` enum('active','inactive','suspended','banned','pending') NOT NULL DEFAULT 'pending',
    `email_verified_at` datetime DEFAULT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `phone_verified_at` datetime DEFAULT NULL,
    `two_factor_secret` varchar(255) DEFAULT NULL,
    `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
    `remember_token` varchar(100) DEFAULT NULL,
    `last_login_at` datetime DEFAULT NULL,
    `last_login_ip` varchar(45) DEFAULT NULL,
    `last_activity_at` datetime DEFAULT NULL,
    `login_attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
    `locked_until` datetime DEFAULT NULL,
    `password_changed_at` datetime DEFAULT NULL,
    `preferences` json DEFAULT NULL,
    `metadata` json DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_username` (`username`),
    UNIQUE KEY `uk_users_email` (`email`),
    KEY `idx_users_status` (`status`),
    KEY `idx_users_role` (`role`),
    KEY `idx_users_created` (`created_at`),
    KEY `idx_users_deleted` (`deleted_at`),
    KEY `idx_users_last_login` (`last_login_at`),
    KEY `idx_users_email_verified` (`email_verified_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: sessions
-- Sessões ativas dos usuários
-- =========================================================================

CREATE TABLE `gda_sessions` (
    `id` varchar(128) NOT NULL,
    `user_id` bigint(20) UNSIGNED DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text,
    `payload` longtext NOT NULL,
    `last_activity` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_sessions_user_id` (`user_id`),
    KEY `idx_sessions_last_activity` (`last_activity`),
    CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: password_resets
-- Tokens de recuperação de senha
-- =========================================================================

CREATE TABLE `gda_password_resets` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` varchar(100) NOT NULL,
    `token` varchar(255) NOT NULL,
    `used` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `expires_at` timestamp NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_password_resets_email` (`email`),
    KEY `idx_password_resets_token` (`token`),
    KEY `idx_password_resets_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: user_profiles
-- Perfis detalhados dos usuários
-- =========================================================================

CREATE TABLE `gda_user_profiles` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `avatar` varchar(255) DEFAULT NULL,
    `cover_image` varchar(255) DEFAULT NULL,
    `bio` text,
    `title` varchar(100) DEFAULT NULL COMMENT 'Título profissional',
    `company` varchar(100) DEFAULT NULL,
    `location` varchar(100) DEFAULT NULL,
    `country` varchar(2) DEFAULT NULL COMMENT 'ISO 3166-1 alpha-2',
    `timezone` varchar(50) DEFAULT 'UTC',
    `language` varchar(5) DEFAULT 'pt-BR',
    `website` varchar(255) DEFAULT NULL,
    `github` varchar(100) DEFAULT NULL,
    `twitter` varchar(100) DEFAULT NULL,
    `linkedin` varchar(100) DEFAULT NULL,
    `youtube` varchar(100) DEFAULT NULL,
    `instagram` varchar(100) DEFAULT NULL,
    `discord` varchar(100) DEFAULT NULL,
    `birth_date` date DEFAULT NULL,
    `gender` enum('male','female','other','prefer_not_to_say') DEFAULT 'prefer_not_to_say',
    `skills` json DEFAULT NULL,
    `interests` json DEFAULT NULL,
    `education` json DEFAULT NULL,
    `experience` json DEFAULT NULL,
    `privacy_settings` json DEFAULT NULL,
    `notification_settings` json DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_profiles_user_id` (`user_id`),
    KEY `idx_profiles_country` (`country`),
    KEY `idx_profiles_language` (`language`),
    FULLTEXT KEY `ft_profiles_bio` (`bio`),
    CONSTRAINT `fk_profiles_user` FOREIGN KEY (`user_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: user_stats
-- Estatísticas e gamificação dos usuários
-- =========================================================================

CREATE TABLE `gda_user_stats` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `xp` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `level` int(10) UNSIGNED NOT NULL DEFAULT 1,
    `coins` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `gems` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `reputation` int(11) NOT NULL DEFAULT 0,
    `courses_enrolled` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `courses_completed` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `courses_in_progress` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `lessons_completed` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `quizzes_completed` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `quizzes_perfect` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `total_study_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Em minutos',
    `total_video_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Em minutos',
    `current_streak` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `longest_streak` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `last_activity_date` date DEFAULT NULL,
    `achievements_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `badges_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `certificates_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `reviews_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `helpful_votes` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `forum_posts` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `forum_replies` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `daily_goals` json DEFAULT NULL,
    `weekly_goals` json DEFAULT NULL,
    `monthly_stats` json DEFAULT NULL,
    `yearly_stats` json DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_stats_user_id` (`user_id`),
    KEY `idx_stats_xp` (`xp`),
    KEY `idx_stats_level` (`level`),
    KEY `idx_stats_reputation` (`reputation`),
    KEY `idx_stats_streak` (`current_streak`),
    KEY `idx_stats_last_activity` (`last_activity_date`),
    CONSTRAINT `fk_stats_user` FOREIGN KEY (`user_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: badges
-- Definição de badges/conquistas disponíveis
-- =========================================================================

CREATE TABLE `gda_badges` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `slug` varchar(100) NOT NULL,
    `description` text,
    `icon` varchar(255) DEFAULT NULL,
    `category` enum('learning','social','special','achievement','milestone') NOT NULL DEFAULT 'achievement',
    `type` enum('bronze','silver','gold','platinum','diamond') NOT NULL DEFAULT 'bronze',
    `xp_reward` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `coin_reward` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `gem_reward` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `requirements` json NOT NULL COMMENT 'Critérios para obter o badge',
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `is_secret` tinyint(1) NOT NULL DEFAULT 0,
    `order` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_badges_slug` (`slug`),
    KEY `idx_badges_category` (`category`),
    KEY `idx_badges_type` (`type`),
    KEY `idx_badges_active` (`is_active`),
    KEY `idx_badges_order` (`order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: user_badges
-- Badges conquistados pelos usuários
-- =========================================================================

CREATE TABLE `gda_user_badges` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `badge_id` int(10) UNSIGNED NOT NULL,
    `earned_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `progress` decimal(5,2) DEFAULT 0.00 COMMENT 'Progresso até conquistar (0-100)',
    `metadata` json DEFAULT NULL COMMENT 'Dados adicionais sobre a conquista',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_badges` (`user_id`, `badge_id`),
    KEY `idx_user_badges_earned` (`earned_at`),
    CONSTRAINT `fk_user_badges_user` FOREIGN KEY (`user_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_user_badges_badge` FOREIGN KEY (`badge_id`) 
        REFERENCES `gda_badges` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: achievements
-- Sistema de conquistas/achievements
-- =========================================================================

CREATE TABLE `gda_achievements` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `slug` varchar(100) NOT NULL,
    `description` text,
    `icon` varchar(255) DEFAULT NULL,
    `category` varchar(50) NOT NULL,
    `points` int(10) UNSIGNED NOT NULL DEFAULT 10,
    `xp_reward` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `requirements` json NOT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `is_hidden` tinyint(1) NOT NULL DEFAULT 0,
    `rarity` enum('common','uncommon','rare','epic','legendary') NOT NULL DEFAULT 'common',
    `order` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_achievements_slug` (`slug`),
    KEY `idx_achievements_category` (`category`),
    KEY `idx_achievements_rarity` (`rarity`),
    KEY `idx_achievements_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: achievements_earned
-- Conquistas obtidas pelos usuários
-- =========================================================================

CREATE TABLE `gda_achievements_earned` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `achievement_id` int(10) UNSIGNED NOT NULL,
    `earned_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `notified` tinyint(1) NOT NULL DEFAULT 0,
    `metadata` json DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_achievements_earned` (`user_id`, `achievement_id`),
    KEY `idx_achievements_earned_date` (`earned_at`),
    CONSTRAINT `fk_achievements_earned_user` FOREIGN KEY (`user_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_achievements_earned_achievement` FOREIGN KEY (`achievement_id`) 
        REFERENCES `gda_achievements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: categories
-- Categorias de cursos
-- =========================================================================

CREATE TABLE `gda_categories` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `parent_id` int(10) UNSIGNED DEFAULT NULL,
    `name` varchar(100) NOT NULL,
    `slug` varchar(100) NOT NULL,
    `description` text,
    `icon` varchar(50) DEFAULT NULL,
    `image` varchar(255) DEFAULT NULL,
    `color` varchar(7) DEFAULT NULL COMMENT 'Cor hexadecimal',
    `meta_title` varchar(200) DEFAULT NULL,
    `meta_description` text,
    `meta_keywords` text,
    `featured` tinyint(1) NOT NULL DEFAULT 0,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `order` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `course_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_categories_slug` (`slug`),
    KEY `idx_categories_parent` (`parent_id`),
    KEY `idx_categories_active` (`is_active`),
    KEY `idx_categories_featured` (`featured`),
    KEY `idx_categories_order` (`order`),
    CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) 
        REFERENCES `gda_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: courses
-- Cursos disponíveis na plataforma
-- =========================================================================

CREATE TABLE `gda_courses` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `instructor_id` bigint(20) UNSIGNED NOT NULL,
    `category_id` int(10) UNSIGNED DEFAULT NULL,
    `title` varchar(200) NOT NULL,
    `slug` varchar(200) NOT NULL,
    `subtitle` varchar(255) DEFAULT NULL,
    `description` text,
    `short_description` varchar(500) DEFAULT NULL,
    `thumbnail` varchar(255) DEFAULT NULL,
    `cover_image` varchar(255) DEFAULT NULL,
    `video_preview` varchar(255) DEFAULT NULL,
    `level` enum('beginner','intermediate','advanced','expert','all') NOT NULL DEFAULT 'beginner',
    `language` varchar(5) NOT NULL DEFAULT 'pt-BR',
    `duration` int(10) UNSIGNED DEFAULT NULL COMMENT 'Duração total em minutos',
    `lectures_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `sections_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `quizzes_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `attachments_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `price` decimal(10,2) NOT NULL DEFAULT 0.00,
    `discount_price` decimal(10,2) DEFAULT NULL,
    `currency` varchar(3) DEFAULT 'BRL',
    `is_free` tinyint(1) NOT NULL DEFAULT 0,
    `status` enum('draft','pending','published','archived','deleted') NOT NULL DEFAULT 'draft',
    `visibility` enum('public','private','password','hidden') NOT NULL DEFAULT 'public',
    `password` varchar(255) DEFAULT NULL COMMENT 'Senha para cursos protegidos',
    `is_featured` tinyint(1) NOT NULL DEFAULT 0,
    `is_certified` tinyint(1) NOT NULL DEFAULT 0,
    `certificate_template` varchar(100) DEFAULT NULL,
    `requirements` json DEFAULT NULL,
    `objectives` json DEFAULT NULL,
    `target_audience` json DEFAULT NULL,
    `includes` json DEFAULT NULL COMMENT 'O que está incluído no curso',
    `tags` json DEFAULT NULL,
    `meta_title` varchar(200) DEFAULT NULL,
    `meta_description` text,
    `meta_keywords` text,
    `rating_avg` decimal(3,2) DEFAULT 0.00,
    `rating_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `rating_sum` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `students_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `enrolled_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `completed_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `views_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `likes_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `published_at` datetime DEFAULT NULL,
    `last_updated_at` datetime DEFAULT NULL,
    `settings` json DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_courses_slug` (`slug`),
    KEY `idx_courses_instructor` (`instructor_id`),
    KEY `idx_courses_category` (`category_id`),
    KEY `idx_courses_status` (`status`),
    KEY `idx_courses_visibility` (`visibility`),
    KEY `idx_courses_featured` (`is_featured`),
    KEY `idx_courses_level` (`level`),
    KEY `idx_courses_language` (`language`),
    KEY `idx_courses_price` (`price`),
    KEY `idx_courses_rating` (`rating_avg`),
    KEY `idx_courses_students` (`students_count`),
    KEY `idx_courses_published` (`published_at`),
    KEY `idx_courses_deleted` (`deleted_at`),
    FULLTEXT KEY `ft_courses_search` (`title`, `subtitle`, `description`, `short_description`),
    CONSTRAINT `fk_courses_instructor` FOREIGN KEY (`instructor_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_courses_category` FOREIGN KEY (`category_id`) 
        REFERENCES `gda_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: tags
-- Tags para categorização adicional
-- =========================================================================

CREATE TABLE `gda_tags` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL,
    `slug` varchar(50) NOT NULL,
    `description` text,
    `usage_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_tags_slug` (`slug`),
    KEY `idx_tags_name` (`name`),
    KEY `idx_tags_usage` (`usage_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: course_tags
-- Relação entre cursos e tags
-- =========================================================================

CREATE TABLE `gda_course_tags` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_id` bigint(20) UNSIGNED NOT NULL,
    `tag_id` int(10) UNSIGNED NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_course_tags` (`course_id`, `tag_id`),
    KEY `idx_course_tags_tag` (`tag_id`),
    CONSTRAINT `fk_course_tags_course` FOREIGN KEY (`course_id`) 
        REFERENCES `gda_courses` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_course_tags_tag` FOREIGN KEY (`tag_id`) 
        REFERENCES `gda_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: course_sections
-- Seções/módulos dos cursos
-- =========================================================================

CREATE TABLE `gda_course_sections` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_id` bigint(20) UNSIGNED NOT NULL,
    `title` varchar(200) NOT NULL,
    `description` text,
    `objectives` json DEFAULT NULL,
    `order` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `is_published` tinyint(1) NOT NULL DEFAULT 1,
    `is_preview` tinyint(1) NOT NULL DEFAULT 0,
    `drip_delay` int(10) UNSIGNED DEFAULT NULL COMMENT 'Dias de atraso para liberar',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_sections_course` (`course_id`),
    KEY `idx_sections_order` (`order`),
    KEY `idx_sections_published` (`is_published`),
    CONSTRAINT `fk_sections_course` FOREIGN KEY (`course_id`) 
        REFERENCES `gda_courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: lessons
-- Aulas/lições dos cursos
-- =========================================================================

CREATE TABLE `gda_lessons` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `section_id` bigint(20) UNSIGNED NOT NULL,
    `course_id` bigint(20) UNSIGNED NOT NULL,
    `title` varchar(200) NOT NULL,
    `slug` varchar(200) NOT NULL,
    `description` text,
    `content` longtext COMMENT 'Conteúdo HTML da aula',
    `type` enum('video','audio','text','quiz','assignment','live','download','external') NOT NULL DEFAULT 'video',
    `video_provider` enum('youtube','vimeo','self','wistia','cloudflare','aws','external') DEFAULT NULL,
    `video_url` varchar(500) DEFAULT NULL,
    `video_id` varchar(100) DEFAULT NULL,
    `video_duration` int(10) UNSIGNED DEFAULT NULL COMMENT 'Duração em segundos',
    `video_size` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Tamanho em bytes',
    `video_quality` json DEFAULT NULL COMMENT 'Qualidades disponíveis',
    `video_subtitles` json DEFAULT NULL COMMENT 'Legendas disponíveis',
    `audio_url` varchar(500) DEFAULT NULL,
    `audio_duration` int(10) UNSIGNED DEFAULT NULL,
    `attachments` json DEFAULT NULL,
    `resources` json DEFAULT NULL,
    `external_url` varchar(500) DEFAULT NULL,
    `is_published` tinyint(1) NOT NULL DEFAULT 1,
    `is_preview` tinyint(1) NOT NULL DEFAULT 0,
    `is_mandatory` tinyint(1) NOT NULL DEFAULT 1,
    `is_downloadable` tinyint(1) NOT NULL DEFAULT 0,
    `order` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `estimated_time` int(10) UNSIGNED DEFAULT NULL COMMENT 'Tempo estimado em minutos',
    `xp_reward` int(10) UNSIGNED NOT NULL DEFAULT 100,
    `coin_reward` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `pass_percentage` decimal(5,2) DEFAULT NULL COMMENT 'Porcentagem mínima para passar',
    `max_attempts` int(10) UNSIGNED DEFAULT NULL,
    `drip_delay` int(10) UNSIGNED DEFAULT NULL COMMENT 'Dias de atraso para liberar',
    `metadata` json DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_lessons_slug` (`course_id`, `slug`),
    KEY `idx_lessons_section` (`section_id`),
    KEY `idx_lessons_course` (`course_id`),
    KEY `idx_lessons_type` (`type`),
    KEY `idx_lessons_order` (`order`),
    KEY `idx_lessons_published` (`is_published`),
    KEY `idx_lessons_preview` (`is_preview`),
    FULLTEXT KEY `ft_lessons_search` (`title`, `description`, `content`),
    CONSTRAINT `fk_lessons_section` FOREIGN KEY (`section_id`) 
        REFERENCES `gda_course_sections` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_lessons_course` FOREIGN KEY (`course_id`) 
        REFERENCES `gda_courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: enrollments
-- Matrículas dos usuários nos cursos
-- =========================================================================

CREATE TABLE `gda_enrollments` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `course_id` bigint(20) UNSIGNED NOT NULL,
    `progress` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Progresso de 0 a 100',
    `status` enum('active','completed','dropped','expired','paused') NOT NULL DEFAULT 'active',
    `enrollment_method` enum('purchased','free','admin','gift','trial') NOT NULL DEFAULT 'free',
    `price_paid` decimal(10,2) DEFAULT NULL,
    `payment_id` varchar(100) DEFAULT NULL,
    `started_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completed_at` datetime DEFAULT NULL,
    `last_accessed_at` datetime DEFAULT NULL,
    `last_lesson_id` bigint(20) UNSIGNED DEFAULT NULL,
    `expires_at` datetime DEFAULT NULL,
    `certificate_id` bigint(20) UNSIGNED DEFAULT NULL,
    `certificate_issued_at` datetime DEFAULT NULL,
    `completion_rate` decimal(5,2) DEFAULT 0.00,
    `total_time_spent` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Em segundos',
    `lessons_completed` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `quizzes_completed` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `assignments_completed` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `notes_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `bookmarks_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `metadata` json DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_enrollments` (`user_id`, `course_id`),
    KEY `idx_enrollments_course` (`course_id`),
    KEY `idx_enrollments_status` (`status`),
    KEY `idx_enrollments_progress` (`progress`),
    KEY `idx_enrollments_started` (`started_at`),
    KEY `idx_enrollments_completed` (`completed_at`),
    KEY `idx_enrollments_last_accessed` (`last_accessed_at`),
    KEY `idx_enrollments_last_lesson` (`last_lesson_id`),
    CONSTRAINT `fk_enrollments_user` FOREIGN KEY (`user_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_enrollments_course` FOREIGN KEY (`course_id`) 
        REFERENCES `gda_courses` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_enrollments_last_lesson` FOREIGN KEY (`last_lesson_id`) 
        REFERENCES `gda_lessons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: lesson_progress
-- Progresso individual de cada lição
-- =========================================================================

CREATE TABLE `gda_lesson_progress` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `course_id` bigint(20) UNSIGNED NOT NULL,
    `lesson_id` bigint(20) UNSIGNED NOT NULL,
    `enrollment_id` bigint(20) UNSIGNED NOT NULL,
    `status` enum('not_started','in_progress','completed','failed') NOT NULL DEFAULT 'not_started',
    `progress_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
    `time_spent` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Tempo em segundos',
    `video_progress` int(10) UNSIGNED DEFAULT NULL COMMENT 'Posição do vídeo em segundos',
    `video_completed` tinyint(1) NOT NULL DEFAULT 0,
    `attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `score` decimal(5,2) DEFAULT NULL COMMENT 'Nota de 0 a 100',
    `passed` tinyint(1) DEFAULT NULL,
    `started_at` datetime DEFAULT NULL,
    `completed_at` datetime DEFAULT NULL,
    `last_accessed_at` datetime DEFAULT NULL,
    `xp_earned` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `coins_earned` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `metadata` json DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_lesson_progress` (`user_id`, `lesson_id`),
    KEY `idx_progress_course` (`course_id`),
    KEY `idx_progress_lesson` (`lesson_id`),
    KEY `idx_progress_enrollment` (`enrollment_id`),
    KEY `idx_progress_status` (`status`),
    KEY `idx_progress_completed` (`completed_at`),
    CONSTRAINT `fk_progress_user` FOREIGN KEY (`user_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_progress_course` FOREIGN KEY (`course_id`) 
        REFERENCES `gda_courses` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_progress_lesson` FOREIGN KEY (`lesson_id`) 
        REFERENCES `gda_lessons` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_progress_enrollment` FOREIGN KEY (`enrollment_id`) 
        REFERENCES `gda_enrollments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: lesson_notes
-- Notas/anotações dos alunos nas aulas
-- =========================================================================

CREATE TABLE `gda_lesson_notes` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `lesson_id` bigint(20) UNSIGNED NOT NULL,
    `course_id` bigint(20) UNSIGNED NOT NULL,
    `note` text NOT NULL,
    `timestamp` int(10) UNSIGNED DEFAULT NULL COMMENT 'Momento do vídeo em segundos',
    `is_public` tinyint(1) NOT NULL DEFAULT 0,
    `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_notes_user` (`user_id`),
    KEY `idx_notes_lesson` (`lesson_id`),
    KEY `idx_notes_course` (`course_id`),
    KEY `idx_notes_public` (`is_public`),
    KEY `idx_notes_created` (`created_at`),
    FULLTEXT KEY `ft_notes_content` (`note`),
    CONSTRAINT `fk_notes_user` FOREIGN KEY (`user_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_notes_lesson` FOREIGN KEY (`lesson_id`) 
        REFERENCES `gda_lessons` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_notes_course` FOREIGN KEY (`course_id`) 
        REFERENCES `gda_courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: lesson_bookmarks
-- Marcadores/favoritos nas aulas
-- =========================================================================

CREATE TABLE `gda_lesson_bookmarks` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `lesson_id` bigint(20) UNSIGNED NOT NULL,
    `course_id` bigint(20) UNSIGNED NOT NULL,
    `title` varchar(200) DEFAULT NULL,
    `timestamp` int(10) UNSIGNED DEFAULT NULL COMMENT 'Momento do vídeo em segundos',
    `note` text,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_bookmarks` (`user_id`, `lesson_id`, `timestamp`),
    KEY `idx_bookmarks_lesson` (`lesson_id`),
    KEY `idx_bookmarks_course` (`course_id`),
    KEY `idx_bookmarks_created` (`created_at`),
    CONSTRAINT `fk_bookmarks_user` FOREIGN KEY (`user_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_bookmarks_lesson` FOREIGN KEY (`lesson_id`) 
        REFERENCES `gda_lessons` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_bookmarks_course` FOREIGN KEY (`course_id`) 
        REFERENCES `gda_courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: quizzes
-- Questionários/provas dos cursos
-- =========================================================================

CREATE TABLE `gda_quizzes` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_id` bigint(20) UNSIGNED NOT NULL,
    `section_id` bigint(20) UNSIGNED DEFAULT NULL,
    `lesson_id` bigint(20) UNSIGNED DEFAULT NULL,
    `title` varchar(200) NOT NULL,
    `description` text,
    `instructions` text,
    `type` enum('quiz','exam','practice','survey') NOT NULL DEFAULT 'quiz',
    `time_limit` int(10) UNSIGNED DEFAULT NULL COMMENT 'Limite em minutos',
    `pass_percentage` decimal(5,2) NOT NULL DEFAULT 60.00,
    `max_attempts` int(10) UNSIGNED DEFAULT NULL,
    `retry_delay` int(10) UNSIGNED DEFAULT NULL COMMENT 'Delay em horas entre tentativas',
    `questions_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `total_marks` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `randomize_questions` tinyint(1) NOT NULL DEFAULT 0,
    `randomize_answers` tinyint(1) NOT NULL DEFAULT 0,
    `show_answers` tinyint(1) NOT NULL DEFAULT 1,
    `show_score` tinyint(1) NOT NULL DEFAULT 1,
    `is_mandatory` tinyint(1) NOT NULL DEFAULT 1,
    `is_published` tinyint(1) NOT NULL DEFAULT 1,
    `xp_reward` int(10) UNSIGNED NOT NULL DEFAULT 50,
    `order` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `metadata` json DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_quizzes_course` (`course_id`),
    KEY `idx_quizzes_section` (`section_id`),
    KEY `idx_quizzes_lesson` (`lesson_id`),
    KEY `idx_quizzes_type` (`type`),
    KEY `idx_quizzes_published` (`is_published`),
    CONSTRAINT `fk_quizzes_course` FOREIGN KEY (`course_id`) 
        REFERENCES `gda_courses` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_quizzes_section` FOREIGN KEY (`section_id`) 
        REFERENCES `gda_course_sections` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_quizzes_lesson` FOREIGN KEY (`lesson_id`) 
        REFERENCES `gda_lessons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: quiz_questions
-- Questões dos questionários
-- =========================================================================

CREATE TABLE `gda_quiz_questions` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `quiz_id` bigint(20) UNSIGNED NOT NULL,
    `question` text NOT NULL,
    `question_type` enum('multiple_choice','true_false','fill_blank','matching','essay','code') NOT NULL DEFAULT 'multiple_choice',
    `options` json DEFAULT NULL COMMENT 'Opções de resposta',
    `correct_answers` json NOT NULL COMMENT 'Respostas corretas',
    `explanation` text COMMENT 'Explicação da resposta',
    `hints` json DEFAULT NULL,
    `marks` int(10) UNSIGNED NOT NULL DEFAULT 1,
    `negative_marks` decimal(5,2) DEFAULT 0.00,
    `order` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `is_required` tinyint(1) NOT NULL DEFAULT 1,
    `media` json DEFAULT NULL COMMENT 'Imagens, vídeos, etc',
    `metadata` json DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_questions_quiz` (`quiz_id`),
    KEY `idx_questions_type` (`question_type`),
    KEY `idx_questions_order` (`order`),
    CONSTRAINT `fk_questions_quiz` FOREIGN KEY (`quiz_id`) 
        REFERENCES `gda_quizzes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: quiz_answers
-- Respostas dos usuários aos questionários
-- =========================================================================

CREATE TABLE `gda_quiz_answers` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `quiz_id` bigint(20) UNSIGNED NOT NULL,
    `question_id` bigint(20) UNSIGNED NOT NULL,
    `response_id` bigint(20) UNSIGNED NOT NULL,
    `answer` json NOT NULL,
    `is_correct` tinyint(1) DEFAULT NULL,
    `marks_obtained` decimal(5,2) DEFAULT 0.00,
    `time_taken` int(10) UNSIGNED DEFAULT NULL COMMENT 'Tempo em segundos',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_answers_quiz` (`quiz_id`),
    KEY `idx_answers_question` (`question_id`),
    KEY `idx_answers_response` (`response_id`),
    KEY `idx_answers_correct` (`is_correct`),
    CONSTRAINT `fk_answers_quiz` FOREIGN KEY (`quiz_id`) 
        REFERENCES `gda_quizzes` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_answers_question` FOREIGN KEY (`question_id`) 
        REFERENCES `gda_quiz_questions` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_answers_response` FOREIGN KEY (`response_id`) 
        REFERENCES `gda_quiz_responses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: quiz_responses
-- Tentativas dos usuários nos questionários
-- =========================================================================

CREATE TABLE `gda_quiz_responses` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `quiz_id` bigint(20) UNSIGNED NOT NULL,
    `enrollment_id` bigint(20) UNSIGNED NOT NULL,
    `attempt_number` int(10) UNSIGNED NOT NULL DEFAULT 1,
    `status` enum('started','in_progress','completed','timeout','abandoned') NOT NULL DEFAULT 'started',
    `score` decimal(5,2) DEFAULT NULL,
    `percentage` decimal(5,2) DEFAULT NULL,
    `passed` tinyint(1) DEFAULT NULL,
    `total_questions` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `answered_questions` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `correct_answers` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `time_taken` int(10) UNSIGNED DEFAULT NULL COMMENT 'Tempo total em segundos',
    `started_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completed_at` datetime DEFAULT NULL,
    `xp_earned` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `feedback` text,
    `metadata` json DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_responses_user` (`user_id`),
    KEY `idx_responses_quiz` (`quiz_id`),
    KEY `idx_responses_enrollment` (`enrollment_id`),
    KEY `idx_responses_status` (`status`),
    KEY `idx_responses_passed` (`passed`),
    KEY `idx_responses_completed` (`completed_at`),
    CONSTRAINT `fk_responses_user` FOREIGN KEY (`user_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_responses_quiz` FOREIGN KEY (`quiz_id`) 
        REFERENCES `gda_quizzes` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_responses_enrollment` FOREIGN KEY (`enrollment_id`) 
        REFERENCES `gda_enrollments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: course_reviews
-- Avaliações e reviews dos cursos
-- =========================================================================

CREATE TABLE `gda_course_reviews` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `course_id` bigint(20) UNSIGNED NOT NULL,
    `enrollment_id` bigint(20) UNSIGNED NOT NULL,
    `rating` tinyint(1) UNSIGNED NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `title` varchar(200) DEFAULT NULL,
    `review` text,
    `pros` text COMMENT 'Pontos positivos',
    `cons` text COMMENT 'Pontos negativos',
    `would_recommend` tinyint(1) DEFAULT NULL,
    `instructor_rating` tinyint(1) UNSIGNED DEFAULT NULL CHECK (`instructor_rating` BETWEEN 1 AND 5),
    `content_rating` tinyint(1) UNSIGNED DEFAULT NULL CHECK (`content_rating` BETWEEN 1 AND 5),
    `value_rating` tinyint(1) UNSIGNED DEFAULT NULL CHECK (`value_rating` BETWEEN 1 AND 5),
    `status` enum('pending','approved','rejected','flagged') NOT NULL DEFAULT 'pending',
    `is_featured` tinyint(1) NOT NULL DEFAULT 0,
    `is_verified_purchase` tinyint(1) NOT NULL DEFAULT 0,
    `helpful_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `unhelpful_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `reported_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `instructor_response` text,
    `instructor_response_at` datetime DEFAULT NULL,
    `approved_at` datetime DEFAULT NULL,
    `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_reviews` (`user_id`, `course_id`),
    KEY `idx_reviews_course` (`course_id`),
    KEY `idx_reviews_enrollment` (`enrollment_id`),
    KEY `idx_reviews_rating` (`rating`),
    KEY `idx_reviews_status` (`status`),
    KEY `idx_reviews_featured` (`is_featured`),
    KEY `idx_reviews_helpful` (`helpful_count`),
    KEY `idx_reviews_created` (`created_at`),
    FULLTEXT KEY `ft_reviews_content` (`title`, `review`, `pros`, `cons`),
    CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reviews_course` FOREIGN KEY (`course_id`) 
        REFERENCES `gda_courses` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reviews_enrollment` FOREIGN KEY (`enrollment_id`) 
        REFERENCES `gda_enrollments` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reviews_approved_by` FOREIGN KEY (`approved_by`) 
        REFERENCES `gda_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: certificates
-- Certificados emitidos
-- =========================================================================

CREATE TABLE `gda_certificates` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `course_id` bigint(20) UNSIGNED NOT NULL,
    `enrollment_id` bigint(20) UNSIGNED NOT NULL,
    `certificate_number` varchar(50) NOT NULL,
    `certificate_url` varchar(255) DEFAULT NULL,
    `issued_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `expires_at` datetime DEFAULT NULL,
    `completion_date` date NOT NULL,
    `grade` varchar(10) DEFAULT NULL,
    `score` decimal(5,2) DEFAULT NULL,
    `template_used` varchar(50) DEFAULT NULL,
    `verification_code` varchar(100) NOT NULL,
    `is_valid` tinyint(1) NOT NULL DEFAULT 1,
    `is_public` tinyint(1) NOT NULL DEFAULT 1,
    `metadata` json DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_certificates_number` (`certificate_number`),
    UNIQUE KEY `uk_certificates_verification` (`verification_code`),
    UNIQUE KEY `uk_certificates_enrollment` (`enrollment_id`),
    KEY `idx_certificates_user` (`user_id`),
    KEY `idx_certificates_course` (`course_id`),
    KEY `idx_certificates_issued` (`issued_at`),
    KEY `idx_certificates_valid` (`is_valid`),
    CONSTRAINT `fk_certificates_user` FOREIGN KEY (`user_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_certificates_course` FOREIGN KEY (`course_id`) 
        REFERENCES `gda_courses` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_certificates_enrollment` FOREIGN KEY (`enrollment_id`) 
        REFERENCES `gda_enrollments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: notifications
-- Sistema de notificações
-- =========================================================================

CREATE TABLE `gda_notifications` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `type` varchar(50) NOT NULL,
    `category` enum('system','course','achievement','social','payment','security') NOT NULL DEFAULT 'system',
    `title` varchar(200) NOT NULL,
    `message` text,
    `icon` varchar(50) DEFAULT NULL,
    `action_url` varchar(255) DEFAULT NULL,
    `action_text` varchar(50) DEFAULT NULL,
    `priority` enum('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
    `data` json DEFAULT NULL,
    `read_at` datetime DEFAULT NULL,
    `clicked_at` datetime DEFAULT NULL,
    `expires_at` datetime DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_notifications_user` (`user_id`),
    KEY `idx_notifications_type` (`type`),
    KEY `idx_notifications_category` (`category`),
    KEY `idx_notifications_priority` (`priority`),
    KEY `idx_notifications_read` (`read_at`),
    KEY `idx_notifications_expires` (`expires_at`),
    KEY `idx_notifications_created` (`created_at`),
    CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: activity_log
-- Log de atividades do sistema
-- =========================================================================

CREATE TABLE `gda_activity_log` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) UNSIGNED DEFAULT NULL,
    `type` varchar(50) NOT NULL,
    `action` varchar(50) NOT NULL,
    `model` varchar(50) DEFAULT NULL,
    `model_id` bigint(20) UNSIGNED DEFAULT NULL,
    `description` text,
    `changes` json DEFAULT NULL COMMENT 'Mudanças realizadas',
    `properties` json DEFAULT NULL COMMENT 'Propriedades adicionais',
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` varchar(255) DEFAULT NULL,
    `referer` varchar(255) DEFAULT NULL,
    `session_id` varchar(128) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_activity_user` (`user_id`),
    KEY `idx_activity_type` (`type`),
    KEY `idx_activity_action` (`action`),
    KEY `idx_activity_model` (`model`, `model_id`),
    KEY `idx_activity_ip` (`ip_address`),
    KEY `idx_activity_session` (`session_id`),
    KEY `idx_activity_created` (`created_at`),
    CONSTRAINT `fk_activity_user` FOREIGN KEY (`user_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- TABELA: media
-- Arquivos de mídia do sistema
-- =========================================================================

CREATE TABLE `gda_media` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) UNSIGNED DEFAULT NULL,
    `type` enum('image','video','audio','document','archive','other') NOT NULL DEFAULT 'other',
    `mime_type` varchar(100) NOT NULL,
    `original_name` varchar(255) NOT NULL,
    `file_name` varchar(255) NOT NULL,
    `file_path` varchar(500) NOT NULL,
    `file_url` varchar(500) DEFAULT NULL,
    `file_size` bigint(20) UNSIGNED NOT NULL COMMENT 'Tamanho em bytes',
    `dimensions` json DEFAULT NULL COMMENT 'Dimensões para imagens/vídeos',
    `duration` int(10) UNSIGNED DEFAULT NULL COMMENT 'Duração para vídeos/áudios em segundos',
    `thumbnails` json DEFAULT NULL COMMENT 'URLs das thumbnails geradas',
    `metadata` json DEFAULT NULL COMMENT 'Metadados do arquivo',
    `folder` varchar(100) DEFAULT '/',
    `is_public` tinyint(1) NOT NULL DEFAULT 0,
    `downloads` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `views` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `hash` varchar(64) DEFAULT NULL COMMENT 'Hash SHA256 do arquivo',
    `storage` enum('local','s3','cloudinary','cloudflare') NOT NULL DEFAULT 'local',
    `cdn_url` varchar(500) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_media_user` (`user_id`),
    KEY `idx_media_type` (`type`),
    KEY `idx_media_mime` (`mime_type`),
    KEY `idx_media_folder` (`folder`),
    KEY `idx_media_public` (`is_public`),
    KEY `idx_media_hash` (`hash`),
    KEY `idx_media_created` (`created_at`),
    CONSTRAINT `fk_media_user` FOREIGN KEY (`user_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- INSERÇÃO DE DADOS INICIAIS
-- =========================================================================

-- Configurações padrão do sistema
INSERT INTO `gda_settings` (`key`, `value`, `type`, `group`, `label`, `description`, `is_public`, `is_editable`, `order`) VALUES
-- Configurações Gerais
('site_name', 'GameDev Academy', 'string', 'general', 'Nome do Site', 'Nome que aparece no título e cabeçalho', 1, 1, 1),
('site_tagline', 'Aprenda a criar jogos incríveis', 'string', 'general', 'Slogan', 'Slogan do site', 1, 1, 2),
('site_description', 'Plataforma completa para aprender desenvolvimento de jogos', 'string', 'general', 'Descrição', 'Descrição do site para SEO', 1, 1, 3),
('site_keywords', 'gamedev,desenvolvimento de jogos,unity,unreal,godot', 'string', 'general', 'Palavras-chave', 'Palavras-chave para SEO', 1, 1, 4),
('site_logo', '/assets/images/logo.png', 'string', 'general', 'Logo', 'URL do logo do site', 1, 1, 5),
('site_favicon', '/assets/images/favicon.ico', 'string', 'general', 'Favicon', 'URL do favicon', 1, 1, 6),
('site_language', 'pt-BR', 'string', 'general', 'Idioma Padrão', 'Idioma padrão do site', 1, 1, 7),
('site_timezone', 'America/Sao_Paulo', 'string', 'general', 'Fuso Horário', 'Fuso horário padrão', 0, 1, 8),
('site_date_format', 'd/m/Y', 'string', 'general', 'Formato de Data', 'Formato padrão de data', 0, 1, 9),
('site_time_format', 'H:i', 'string', 'general', 'Formato de Hora', 'Formato padrão de hora', 0, 1, 10),
('maintenance_mode', 'false', 'boolean', 'general', 'Modo Manutenção', 'Ativa o modo de manutenção', 0, 1, 11),
('maintenance_message', 'Estamos em manutenção. Voltamos em breve!', 'string', 'general', 'Mensagem de Manutenção', 'Mensagem exibida durante manutenção', 0, 1, 12),

-- Configurações de Usuários
('users_registration', 'true', 'boolean', 'users', 'Permitir Registro', 'Permite novos usuários se registrarem', 1, 1, 20),
('users_email_verification', 'true', 'boolean', 'users', 'Verificação de Email', 'Requer verificação de email', 0, 1, 21),
('users_auto_approve', 'true', 'boolean', 'users', 'Auto-aprovar', 'Aprova automaticamente novos usuários', 0, 1, 22),
('users_default_role', 'student', 'string', 'users', 'Papel Padrão', 'Papel padrão para novos usuários', 0, 1, 23),
('users_allow_username_change', 'false', 'boolean', 'users', 'Alterar Username', 'Permite alterar nome de usuário', 0, 1, 24),
('users_profile_privacy', 'public', 'string', 'users', 'Privacidade do Perfil', 'Privacidade padrão do perfil', 1, 1, 25),
('users_max_login_attempts', '5', 'integer', 'users', 'Tentativas de Login', 'Máximo de tentativas de login', 0, 1, 26),
('users_lockout_duration', '30', 'integer', 'users', 'Duração do Bloqueio', 'Minutos de bloqueio após falhas', 0, 1, 27),

-- Configurações de Cursos
('courses_approval_required', 'true', 'boolean', 'courses', 'Aprovação Necessária', 'Novos cursos precisam de aprovação', 0, 1, 30),
('courses_allow_free', 'true', 'boolean', 'courses', 'Permitir Gratuitos', 'Permite cursos gratuitos', 1, 1, 31),
('courses_allow_reviews', 'true', 'boolean', 'courses', 'Permitir Avaliações', 'Permite avaliações de cursos', 1, 1, 32),
('courses_review_approval', 'false', 'boolean', 'courses', 'Aprovar Avaliações', 'Avaliações precisam de aprovação', 0, 1, 33),
('courses_certificate_enabled', 'true', 'boolean', 'courses', 'Certificados', 'Habilita certificados de conclusão', 1, 1, 34),
('courses_min_completion', '80', 'integer', 'courses', 'Conclusão Mínima', 'Porcentagem mínima para certificado', 0, 1, 35),
('courses_drip_content', 'false', 'boolean', 'courses', 'Conteúdo Gradual', 'Libera conteúdo gradualmente', 0, 1, 36),

-- Configurações de Gamificação
('gamification_enabled', 'true', 'boolean', 'gamification', 'Gamificação Ativa', 'Ativa sistema de gamificação', 1, 1, 40),
('xp_per_lesson', '100', 'integer', 'gamification', 'XP por Aula', 'XP ganho ao completar aula', 0, 1, 41),
('xp_per_quiz', '50', 'integer', 'gamification', 'XP por Quiz', 'XP ganho ao completar quiz', 0, 1, 42),
('xp_per_course', '500', 'integer', 'gamification', 'XP por Curso', 'XP ganho ao completar curso', 0, 1, 43),
('xp_level_base', '1000', 'integer', 'gamification', 'XP Base por Nível', 'XP base necessário por nível', 0, 1, 44),
('xp_level_multiplier', '1.5', 'float', 'gamification', 'Multiplicador de Nível', 'Multiplicador de XP por nível', 0, 1, 45),
('coins_enabled', 'true', 'boolean', 'gamification', 'Moedas Ativas', 'Ativa sistema de moedas', 1, 1, 46),
('coins_per_day', '10', 'integer', 'gamification', 'Moedas Diárias', 'Moedas ganhas por login diário', 0, 1, 47),
('streak_bonus', 'true', 'boolean', 'gamification', 'Bônus de Sequência', 'Bônus por dias consecutivos', 1, 1, 48),
('achievements_enabled', 'true', 'boolean', 'gamification', 'Conquistas Ativas', 'Ativa sistema de conquistas', 1, 1, 49),
('leaderboard_enabled', 'true', 'boolean', 'gamification', 'Ranking Ativo', 'Ativa ranking de usuários', 1, 1, 50),

-- Configurações de Email
('mail_from_address', 'noreply@gamedevacademy.com', 'string', 'mail', 'Email Remetente', 'Email usado como remetente', 0, 1, 60),
('mail_from_name', 'GameDev Academy', 'string', 'mail', 'Nome Remetente', 'Nome usado como remetente', 0, 1, 61),
('mail_driver', 'smtp', 'string', 'mail', 'Driver de Email', 'Driver usado para enviar emails', 0, 1, 62),
('mail_host', 'smtp.gmail.com', 'string', 'mail', 'Host SMTP', 'Servidor SMTP', 0, 1, 63),
('mail_port', '587', 'integer', 'mail', 'Porta SMTP', 'Porta do servidor SMTP', 0, 1, 64),
('mail_encryption', 'tls', 'string', 'mail', 'Criptografia', 'Tipo de criptografia (tls/ssl)', 0, 1, 65),

-- Configurações de Segurança
('security_2fa_enabled', 'false', 'boolean', 'security', '2FA Disponível', 'Permite autenticação 2 fatores', 0, 1, 70),
('security_password_min', '8', 'integer', 'security', 'Senha Mínima', 'Tamanho mínimo da senha', 0, 1, 71),
('security_password_uppercase', 'true', 'boolean', 'security', 'Senha Maiúscula', 'Requer letra maiúscula', 0, 1, 72),
('security_password_number', 'true', 'boolean', 'security', 'Senha Número', 'Requer número na senha', 0, 1, 73),
('security_password_special', 'false', 'boolean', 'security', 'Senha Especial', 'Requer caractere especial', 0, 1, 74),
('security_session_lifetime', '120', 'integer', 'security', 'Duração Sessão', 'Minutos de duração da sessão', 0, 1, 75),
('security_remember_me', '10080', 'integer', 'security', 'Lembrar-me', 'Minutos do lembrar-me', 0, 1, 76),

-- Configurações de SEO
('seo_title_separator', '|', 'string', 'seo', 'Separador de Título', 'Separador usado nos títulos', 1, 1, 80),
('seo_meta_robots', 'index,follow', 'string', 'seo', 'Meta Robots', 'Diretivas para robôs', 1, 1, 81),
('seo_google_analytics', '', 'string', 'seo', 'Google Analytics', 'ID do Google Analytics', 0, 1, 82),
('seo_google_tag_manager', '', 'string', 'seo', 'Tag Manager', 'ID do Google Tag Manager', 0, 1, 83),
('seo_facebook_pixel', '', 'string', 'seo', 'Facebook Pixel', 'ID do Facebook Pixel', 0, 1, 84),
('seo_sitemap_enabled', 'true', 'boolean', 'seo', 'Sitemap Ativo', 'Gera sitemap automaticamente', 1, 1, 85),

-- Configurações de Pagamento
('payment_enabled', 'false', 'boolean', 'payment', 'Pagamentos Ativos', 'Ativa sistema de pagamentos', 0, 1, 90),
('payment_gateway', 'stripe', 'string', 'payment', 'Gateway', 'Gateway de pagamento padrão', 0, 1, 91),
('payment_currency', 'BRL', 'string', 'payment', 'Moeda', 'Moeda padrão', 1, 1, 92),
('payment_tax_rate', '0', 'float', 'payment', 'Taxa', 'Taxa de imposto (%)', 0, 1, 93);

-- Categorias padrão
INSERT INTO `gda_categories` (`name`, `slug`, `description`, `icon`, `color`, `order`) VALUES
('Programação', 'programacao', 'Aprenda as linguagens e conceitos de programação para jogos', '💻', '#667eea', 1),
('Arte 2D', 'arte-2d', 'Design de personagens, cenários e assets 2D', '🎨', '#f093fb', 2),
('Arte 3D', 'arte-3d', 'Modelagem, texturização e animação 3D', '🎭', '#4facfe', 3),
('Game Design', 'game-design', 'Conceitos e práticas de design de jogos', '🎮', '#fa709a', 4),
('Áudio', 'audio', 'Música, efeitos sonoros e design de áudio', '🎵', '#30cfd0', 5),
('Marketing', 'marketing', 'Como promover e monetizar seus jogos', '📈', '#a8edea', 6),
('Unity', 'unity', 'Desenvolvimento com Unity Engine', '🔷', '#764ba2', 7),
('Unreal Engine', 'unreal', 'Desenvolvimento com Unreal Engine', '🔶', '#f093fb', 8),
('Godot', 'godot', 'Desenvolvimento com Godot Engine', '🤖', '#4facfe', 9),
('Mobile', 'mobile', 'Desenvolvimento para dispositivos móveis', '📱', '#43e97b', 10);

-- Badges padrão
INSERT INTO `gda_badges` (`name`, `slug`, `description`, `icon`, `category`, `type`, `xp_reward`, `requirements`) VALUES
('Primeira Aula', 'first-lesson', 'Complete sua primeira aula', '🎯', 'learning', 'bronze', 50, '{"lessons_completed": 1}'),
('Primeiro Curso', 'first-course', 'Complete seu primeiro curso', '🏆', 'learning', 'silver', 200, '{"courses_completed": 1}'),
('Maratonista', 'marathoner', 'Complete 10 cursos', '🏃', 'learning', 'gold', 1000, '{"courses_completed": 10}'),
('Estudante Dedicado', 'dedicated', 'Estude por 7 dias seguidos', '📚', 'milestone', 'silver', 150, '{"streak_days": 7}'),
('Mestre', 'master', 'Alcance o nível 50', '👑', 'achievement', 'platinum', 5000, '{"level": 50}'),
('Perfeccionista', 'perfectionist', 'Tire 100% em 10 quizzes', '💯', 'achievement', 'gold', 500, '{"perfect_quizzes": 10}'),
('Social', 'social', 'Faça 10 avaliações de cursos', '💬', 'social', 'bronze', 100, '{"reviews_count": 10}'),
('Ajudante', 'helper', 'Receba 50 votos úteis', '🤝', 'social', 'silver', 300, '{"helpful_votes": 50}'),
('Early Bird', 'early-bird', 'Complete uma aula antes das 6h', '🌅', 'special', 'bronze', 75, '{"time_condition": "before_6am"}'),
('Night Owl', 'night-owl', 'Complete uma aula depois da meia-noite', '🦉', 'special', 'bronze', 75, '{"time_condition": "after_midnight"}');

-- Conquistas padrão
INSERT INTO `gda_achievements` (`name`, `slug`, `description`, `category`, `points`, `xp_reward`, `requirements`, `rarity`) VALUES
('Bem-vindo!', 'welcome', 'Crie sua conta na plataforma', 'inicio', 10, 25, '{"action": "register"}', 'common'),
('Primeiro Passo', 'first-step', 'Complete sua primeira lição', 'aprendizado', 20, 50, '{"lessons": 1}', 'common'),
('Explorador', 'explorer', 'Navegue por 10 cursos diferentes', 'exploracao', 15, 30, '{"courses_viewed": 10}', 'common'),
('Comprometido', 'committed', 'Mantenha uma sequência de 30 dias', 'dedicacao', 100, 500, '{"streak": 30}', 'rare'),
('Colecionador', 'collector', 'Ganhe 20 badges diferentes', 'colecao', 50, 250, '{"badges": 20}', 'uncommon'),
('Veterano', 'veteran', 'Complete 100 lições', 'experiencia', 200, 1000, '{"lessons": 100}', 'epic'),
('Lenda', 'legend', 'Alcance o nível 100', 'prestigio', 500, 5000, '{"level": 100}', 'legendary'),
('Speedrunner', 'speedrunner', 'Complete um curso em menos de 24h', 'desafio', 75, 350, '{"course_time": "24h"}', 'rare'),
('Certificado', 'certified', 'Obtenha 10 certificados', 'certificacao', 150, 750, '{"certificates": 10}', 'epic'),
('Poliglota', 'polyglot', 'Complete cursos em 3 idiomas', 'diversidade', 100, 500, '{"languages": 3}', 'uncommon');

-- Usuário administrador padrão (senha: admin123)
-- NOTA: A senha será substituída durante a instalação
INSERT INTO `gda_users` (`username`, `email`, `password`, `name`, `role`, `status`, `email_verified_at`) VALUES
('admin', 'admin@gamedevacademy.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'admin', 'active', NOW());

-- Perfil do administrador
INSERT INTO `gda_user_profiles` (`user_id`, `bio`, `timezone`, `language`) VALUES
(1, 'Administrador do Sistema', 'America/Sao_Paulo', 'pt-BR');

-- Stats do administrador
INSERT INTO `gda_user_stats` (`user_id`) VALUES (1);

-- =========================================================================
-- TRIGGERS E PROCEDURES
-- =========================================================================

DELIMITER $$

-- Trigger para atualizar contadores de categoria
CREATE TRIGGER update_category_course_count
AFTER INSERT ON gda_courses
FOR EACH ROW
BEGIN
    IF NEW.category_id IS NOT NULL THEN
        UPDATE gda_categories 
        SET course_count = course_count + 1 
        WHERE id = NEW.category_id;
    END IF;
END$$

-- Trigger para atualizar estatísticas do usuário ao completar lição
CREATE TRIGGER update_user_stats_on_lesson_complete
AFTER UPDATE ON gda_lesson_progress
FOR EACH ROW
BEGIN
    IF NEW.status = 'completed' AND OLD.status != 'completed' THEN
        UPDATE gda_user_stats 
        SET 
            lessons_completed = lessons_completed + 1,
            xp = xp + NEW.xp_earned,
            last_activity_date = CURDATE()
        WHERE user_id = NEW.user_id;
    END IF;
END$$

-- Trigger para atualizar rating do curso
CREATE TRIGGER update_course_rating
AFTER INSERT ON gda_course_reviews
FOR EACH ROW
BEGIN
    IF NEW.status = 'approved' THEN
        UPDATE gda_courses 
        SET 
            rating_count = rating_count + 1,
            rating_sum = rating_sum + NEW.rating,
            rating_avg = (rating_sum + NEW.rating) / (rating_count + 1)
        WHERE id = NEW.course_id;
    END IF;
END$$

-- Procedure para calcular nível baseado em XP
CREATE PROCEDURE calculate_user_level(IN user_xp INT)
BEGIN
    DECLARE level INT DEFAULT 1;
    DECLARE xp_needed INT;
    DECLARE xp_base INT;
    DECLARE xp_multiplier FLOAT;
    
    SELECT `value` INTO xp_base FROM gda_settings WHERE `key` = 'xp_level_base';
    SELECT `value` INTO xp_multiplier FROM gda_settings WHERE `key` = 'xp_level_multiplier';
    
    SET xp_needed = xp_base;
    
    WHILE user_xp >= xp_needed DO
        SET level = level + 1;
        SET xp_needed = xp_needed + FLOOR(xp_base * POWER(xp_multiplier, level - 1));
    END WHILE;
    
    SELECT level;
END$$

-- Procedure para verificar e atualizar streak
CREATE PROCEDURE update_user_streak(IN p_user_id BIGINT)
BEGIN
    DECLARE last_date DATE;
    DECLARE current_streak INT;
    DECLARE longest_streak INT;
    
    SELECT last_activity_date, current_streak, longest_streak 
    INTO last_date, current_streak, longest_streak
    FROM gda_user_stats 
    WHERE user_id = p_user_id;
    
    IF last_date = CURDATE() THEN
        -- Já teve atividade hoje, não faz nada
        SELECT current_streak;
    ELSEIF last_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY) THEN
        -- Atividade ontem, continua streak
        SET current_streak = current_streak + 1;
        IF current_streak > longest_streak THEN
            SET longest_streak = current_streak;
        END IF;
        
        UPDATE gda_user_stats 
        SET 
            current_streak = current_streak,
            longest_streak = longest_streak,
            last_activity_date = CURDATE()
        WHERE user_id = p_user_id;
        
        SELECT current_streak;
    ELSE
        -- Quebrou o streak
        UPDATE gda_user_stats 
        SET 
            current_streak = 1,
            last_activity_date = CURDATE()
        WHERE user_id = p_user_id;
        
        SELECT 1;
    END IF;
END$$

DELIMITER ;

-- =========================================================================
-- ÍNDICES ADICIONAIS PARA PERFORMANCE
-- =========================================================================

-- Índices compostos para queries comuns
CREATE INDEX idx_enrollments_user_status ON gda_enrollments(user_id, status);
CREATE INDEX idx_lesson_progress_user_status ON gda_lesson_progress(user_id, status);
CREATE INDEX idx_courses_status_featured ON gda_courses(status, is_featured);
CREATE INDEX idx_reviews_course_status ON gda_course_reviews(course_id, status);

-- =========================================================================
-- VIEWS ÚTEIS
-- =========================================================================

-- View de cursos populares
CREATE VIEW view_popular_courses AS
SELECT 
    c.id,
    c.title,
    c.slug,
    c.thumbnail,
    c.rating_avg,
    c.students_count,
    c.price,
    cat.name as category_name,
    u.name as instructor_name
FROM gda_courses c
LEFT JOIN gda_categories cat ON c.category_id = cat.id
LEFT JOIN gda_users u ON c.instructor_id = u.id
WHERE c.status = 'published' 
    AND c.deleted_at IS NULL
ORDER BY c.students_count DESC, c.rating_avg DESC;

-- View de progresso dos alunos
CREATE VIEW view_student_progress AS
SELECT 
    e.user_id,
    e.course_id,
    c.title as course_title,
    e.progress,
    e.status,
    e.started_at,
    e.last_accessed_at,
    COUNT(DISTINCT lp.id) as lessons_completed,
    SUM(lp.time_spent) as total_time_spent
FROM gda_enrollments e
JOIN gda_courses c ON e.course_id = c.id
LEFT JOIN gda_lesson_progress lp ON e.id = lp.enrollment_id AND lp.status = 'completed'
GROUP BY e.id;

-- =========================================================================
-- FINALIZAÇÃO
-- =========================================================================

SET foreign_key_checks = 1;
COMMIT;

-- =========================================================================
-- CONTINUAÇÃO DO SCHEMA - TABELAS FALTANTES (34-51)
-- =========================================================================

-- =========================================================================
-- SISTEMA DE FÓRUM/COMUNIDADE
-- =========================================================================

-- TABELA: forum_categories
CREATE TABLE `gda_forum_categories` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `parent_id` int(10) UNSIGNED DEFAULT NULL,
    `name` varchar(100) NOT NULL,
    `slug` varchar(100) NOT NULL,
    `description` text,
    `icon` varchar(50) DEFAULT NULL,
    `color` varchar(7) DEFAULT NULL,
    `topics_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `posts_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `last_post_id` bigint(20) UNSIGNED DEFAULT NULL,
    `is_locked` tinyint(1) NOT NULL DEFAULT 0,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `order` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_forum_categories_slug` (`slug`),
    KEY `idx_forum_categories_parent` (`parent_id`),
    KEY `idx_forum_categories_active` (`is_active`),
    KEY `idx_forum_categories_order` (`order`),
    CONSTRAINT `fk_forum_categories_parent` FOREIGN KEY (`parent_id`) 
        REFERENCES `gda_forum_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABELA: forum_topics
CREATE TABLE `gda_forum_topics` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_id` int(10) UNSIGNED NOT NULL,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `title` varchar(200) NOT NULL,
    `slug` varchar(200) NOT NULL,
    `content` longtext NOT NULL,
    `type` enum('discussion','question','announcement','poll') NOT NULL DEFAULT 'discussion',
    `status` enum('open','closed','solved','pinned') NOT NULL DEFAULT 'open',
    `views_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `replies_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `likes_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `last_reply_id` bigint(20) UNSIGNED DEFAULT NULL,
    `last_reply_at` datetime DEFAULT NULL,
    `best_answer_id` bigint(20) UNSIGNED DEFAULT NULL,
    `is_featured` tinyint(1) NOT NULL DEFAULT 0,
    `is_sticky` tinyint(1) NOT NULL DEFAULT 0,
    `is_locked` tinyint(1) NOT NULL DEFAULT 0,
    `tags` json DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_forum_topics_slug` (`slug`),
    KEY `idx_forum_topics_category` (`category_id`),
    KEY `idx_forum_topics_user` (`user_id`),
    KEY `idx_forum_topics_type` (`type`),
    KEY `idx_forum_topics_status` (`status`),
    KEY `idx_forum_topics_featured` (`is_featured`),
    KEY `idx_forum_topics_sticky` (`is_sticky`),
    FULLTEXT KEY `ft_forum_topics` (`title`, `content`),
    CONSTRAINT `fk_forum_topics_category` FOREIGN KEY (`category_id`) 
        REFERENCES `gda_forum_categories` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_forum_topics_user` FOREIGN KEY (`user_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABELA: forum_posts
CREATE TABLE `gda_forum_posts` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `topic_id` bigint(20) UNSIGNED NOT NULL,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
    `content` text NOT NULL,
    `likes_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `is_accepted` tinyint(1) NOT NULL DEFAULT 0,
    `is_edited` tinyint(1) NOT NULL DEFAULT 0,
    `edited_at` datetime DEFAULT NULL,
    `edited_by` bigint(20) UNSIGNED DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_forum_posts_topic` (`topic_id`),
    KEY `idx_forum_posts_user` (`user_id`),
    KEY `idx_forum_posts_parent` (`parent_id`),
    KEY `idx_forum_posts_accepted` (`is_accepted`),
    FULLTEXT KEY `ft_forum_posts` (`content`),
    CONSTRAINT `fk_forum_posts_topic` FOREIGN KEY (`topic_id`) 
        REFERENCES `gda_forum_topics` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_forum_posts_user` FOREIGN KEY (`user_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_forum_posts_parent` FOREIGN KEY (`parent_id`) 
        REFERENCES `gda_forum_posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABELA: forum_votes
CREATE TABLE `gda_forum_votes` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `votable_type` varchar(50) NOT NULL COMMENT 'topic ou post',
    `votable_id` bigint(20) UNSIGNED NOT NULL,
    `vote_type` enum('up','down') NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_forum_votes` (`user_id`, `votable_type`, `votable_id`),
    KEY `idx_forum_votes_votable` (`votable_type`, `votable_id`),
    CONSTRAINT `fk_forum_votes_user` FOREIGN KEY (`user_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABELA: forum_reports
CREATE TABLE `gda_forum_reports` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `reportable_type` varchar(50) NOT NULL,
    `reportable_id` bigint(20) UNSIGNED NOT NULL,
    `reason` enum('spam','inappropriate','offensive','other') NOT NULL,
    `description` text,
    `status` enum('pending','reviewed','resolved','dismissed') NOT NULL DEFAULT 'pending',
    `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
    `reviewed_at` datetime DEFAULT NULL,
    `action_taken` text,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_forum_reports_user` (`user_id`),
    KEY `idx_forum_reports_reportable` (`reportable_type`, `reportable_id`),
    KEY `idx_forum_reports_status` (`status`),
    CONSTRAINT `fk_forum_reports_user` FOREIGN KEY (`user_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_forum_reports_reviewer` FOREIGN KEY (`reviewed_by`) 
        REFERENCES `gda_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- SISTEMA DE MENSAGENS/CHAT
-- =========================================================================

-- TABELA: conversations
CREATE TABLE `gda_conversations` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `type` enum('private','group','support') NOT NULL DEFAULT 'private',
    `title` varchar(200) DEFAULT NULL,
    `last_message_id` bigint(20) UNSIGNED DEFAULT NULL,
    `last_message_at` datetime DEFAULT NULL,
    `messages_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `created_by` bigint(20) UNSIGNED NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_conversations_type` (`type`),
    KEY `idx_conversations_last_message` (`last_message_at`),
    KEY `idx_conversations_creator` (`created_by`),
    CONSTRAINT `fk_conversations_creator` FOREIGN KEY (`created_by`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABELA: conversation_participants
CREATE TABLE `gda_conversation_participants` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `conversation_id` bigint(20) UNSIGNED NOT NULL,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `last_read_at` datetime DEFAULT NULL,
    `unread_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `is_admin` tinyint(1) NOT NULL DEFAULT 0,
    `is_muted` tinyint(1) NOT NULL DEFAULT 0,
    `muted_until` datetime DEFAULT NULL,
    `joined_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `left_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_conversation_participants` (`conversation_id`, `user_id`),
    KEY `idx_participants_user` (`user_id`),
    KEY `idx_participants_unread` (`unread_count`),
    CONSTRAINT `fk_participants_conversation` FOREIGN KEY (`conversation_id`) 
        REFERENCES `gda_conversations` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_participants_user` FOREIGN KEY (`user_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABELA: messages
CREATE TABLE `gda_messages` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `conversation_id` bigint(20) UNSIGNED NOT NULL,
    `sender_id` bigint(20) UNSIGNED NOT NULL,
    `reply_to_id` bigint(20) UNSIGNED DEFAULT NULL,
    `type` enum('text','image','video','audio','file','system') NOT NULL DEFAULT 'text',
    `content` text NOT NULL,
    `attachments` json DEFAULT NULL,
    `is_edited` tinyint(1) NOT NULL DEFAULT 0,
    `edited_at` datetime DEFAULT NULL,
    `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
    `deleted_at` datetime DEFAULT NULL,
    `read_by` json DEFAULT NULL COMMENT 'IDs dos usuários que leram',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_messages_conversation` (`conversation_id`),
    KEY `idx_messages_sender` (`sender_id`),
    KEY `idx_messages_reply` (`reply_to_id`),
    KEY `idx_messages_type` (`type`),
    KEY `idx_messages_created` (`created_at`),
    CONSTRAINT `fk_messages_conversation` FOREIGN KEY (`conversation_id`) 
        REFERENCES `gda_conversations` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_messages_reply` FOREIGN KEY (`reply_to_id`) 
        REFERENCES `gda_messages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- SISTEMA DE PAGAMENTOS E VENDAS
-- =========================================================================

-- TABELA: coupons
CREATE TABLE `gda_coupons` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` varchar(50) NOT NULL,
    `description` text,
    `type` enum('percentage','fixed','free') NOT NULL DEFAULT 'percentage',
    `value` decimal(10,2) NOT NULL,
    `min_amount` decimal(10,2) DEFAULT NULL,
    `usage_limit` int(10) UNSIGNED DEFAULT NULL,
    `usage_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `user_limit` int(10) UNSIGNED DEFAULT 1,
    `courses` json DEFAULT NULL COMMENT 'IDs dos cursos aplicáveis',
    `categories` json DEFAULT NULL COMMENT 'IDs das categorias aplicáveis',
    `valid_from` datetime NOT NULL,
    `valid_until` datetime NOT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_by` bigint(20) UNSIGNED NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_coupons_code` (`code`),
    KEY `idx_coupons_type` (`type`),
    KEY `idx_coupons_valid` (`valid_from`, `valid_until`),
    KEY `idx_coupons_active` (`is_active`),
    CONSTRAINT `fk_coupons_creator` FOREIGN KEY (`created_by`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABELA: orders
CREATE TABLE `gda_orders` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_number` varchar(50) NOT NULL,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `status` enum('pending','processing','completed','cancelled','refunded') NOT NULL DEFAULT 'pending',
    `payment_status` enum('pending','paid','failed','refunded','partial_refund') NOT NULL DEFAULT 'pending',
    `payment_method` varchar(50) DEFAULT NULL,
    `payment_id` varchar(255) DEFAULT NULL,
    `subtotal` decimal(10,2) NOT NULL,
    `discount` decimal(10,2) DEFAULT 0.00,
    `tax` decimal(10,2) DEFAULT 0.00,
    `total` decimal(10,2) NOT NULL,
    `currency` varchar(3) NOT NULL DEFAULT 'BRL',
    `coupon_id` int(10) UNSIGNED DEFAULT NULL,
    `coupon_code` varchar(50) DEFAULT NULL,
    `billing_info` json DEFAULT NULL,
    `notes` text,
    `paid_at` datetime DEFAULT NULL,
    `completed_at` datetime DEFAULT NULL,
    `refunded_at` datetime DEFAULT NULL,
    `metadata` json DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_orders_number` (`order_number`),
    KEY `idx_orders_user` (`user_id`),
    KEY `idx_orders_status` (`status`),
    KEY `idx_orders_payment_status` (`payment_status`),
    KEY `idx_orders_coupon` (`coupon_id`),
    KEY `idx_orders_created` (`created_at`),
    CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_orders_coupon` FOREIGN KEY (`coupon_id`) 
        REFERENCES `gda_coupons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABELA: order_items
CREATE TABLE `gda_order_items` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` bigint(20) UNSIGNED NOT NULL,
    `course_id` bigint(20) UNSIGNED NOT NULL,
    `price` decimal(10,2) NOT NULL,
    `discount` decimal(10,2) DEFAULT 0.00,
    `total` decimal(10,2) NOT NULL,
    `access_granted_at` datetime DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_order_items_order` (`order_id`),
    KEY `idx_order_items_course` (`course_id`),
    CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) 
        REFERENCES `gda_orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_order_items_course` FOREIGN KEY (`course_id`) 
        REFERENCES `gda_courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABELA: transactions
CREATE TABLE `gda_transactions` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `transaction_id` varchar(100) NOT NULL,
    `order_id` bigint(20) UNSIGNED DEFAULT NULL,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `type` enum('payment','refund','payout','adjustment') NOT NULL,
    `status` enum('pending','processing','success','failed','cancelled') NOT NULL,
    `gateway` varchar(50) NOT NULL,
    `gateway_response` json DEFAULT NULL,
    `amount` decimal(10,2) NOT NULL,
    `fee` decimal(10,2) DEFAULT 0.00,
    `net_amount` decimal(10,2) NOT NULL,
    `currency` varchar(3) NOT NULL DEFAULT 'BRL',
    `description` text,
    `processed_at` datetime DEFAULT NULL,
    `metadata` json DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_transactions_id` (`transaction_id`),
    KEY `idx_transactions_order` (`order_id`),
    KEY `idx_transactions_user` (`user_id`),
    KEY `idx_transactions_type` (`type`),
    KEY `idx_transactions_status` (`status`),
    KEY `idx_transactions_gateway` (`gateway`),
    CONSTRAINT `fk_transactions_order` FOREIGN KEY (`order_id`) 
        REFERENCES `gda_orders` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_transactions_user` FOREIGN KEY (`user_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- SISTEMA DE EVENTOS E WEBINARS
-- =========================================================================

-- TABELA: events
CREATE TABLE `gda_events` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `host_id` bigint(20) UNSIGNED NOT NULL,
    `course_id` bigint(20) UNSIGNED DEFAULT NULL,
    `title` varchar(200) NOT NULL,
    `slug` varchar(200) NOT NULL,
    `description` text,
    `type` enum('webinar','workshop','meetup','conference','livestream') NOT NULL DEFAULT 'webinar',
    `status` enum('draft','published','ongoing','finished','cancelled') NOT NULL DEFAULT 'draft',
    `thumbnail` varchar(255) DEFAULT NULL,
    `meeting_url` varchar(500) DEFAULT NULL,
    `meeting_id` varchar(100) DEFAULT NULL,
    `meeting_password` varchar(50) DEFAULT NULL,
    `platform` enum('zoom','teams','meet','youtube','twitch','custom') DEFAULT NULL,
    `start_at` datetime NOT NULL,
    `end_at` datetime NOT NULL,
    `timezone` varchar(50) NOT NULL DEFAULT 'UTC',
    `max_participants` int(10) UNSIGNED DEFAULT NULL,
    `registered_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `attended_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `is_recurring` tinyint(1) NOT NULL DEFAULT 0,
    `recurrence_rule` json DEFAULT NULL,
    `is_paid` tinyint(1) NOT NULL DEFAULT 0,
    `price` decimal(10,2) DEFAULT NULL,
    `recording_url` varchar(500) DEFAULT NULL,
    `materials` json DEFAULT NULL,
    `tags` json DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_events_slug` (`slug`),
    KEY `idx_events_host` (`host_id`),
    KEY `idx_events_course` (`course_id`),
    KEY `idx_events_type` (`type`),
    KEY `idx_events_status` (`status`),
    KEY `idx_events_start` (`start_at`),
    KEY `idx_events_platform` (`platform`),
    CONSTRAINT `fk_events_host` FOREIGN KEY (`host_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_events_course` FOREIGN KEY (`course_id`) 
        REFERENCES `gda_courses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABELA: event_registrations
CREATE TABLE `gda_event_registrations` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id` bigint(20) UNSIGNED NOT NULL,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `status` enum('registered','attended','no_show','cancelled') NOT NULL DEFAULT 'registered',
    `joined_at` datetime DEFAULT NULL,
    `left_at` datetime DEFAULT NULL,
    `duration` int(10) UNSIGNED DEFAULT NULL COMMENT 'Tempo de participação em minutos',
    `rating` tinyint(1) UNSIGNED DEFAULT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `feedback` text,
    `certificate_issued` tinyint(1) NOT NULL DEFAULT 0,
    `reminder_sent` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_event_registrations` (`event_id`, `user_id`),
    KEY `idx_registrations_user` (`user_id`),
    KEY `idx_registrations_status` (`status`),
    CONSTRAINT `fk_registrations_event` FOREIGN KEY (`event_id`) 
        REFERENCES `gda_events` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_registrations_user` FOREIGN KEY (`user_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- SISTEMA DE ASSIGNMENTS E PROJETOS
-- =========================================================================

-- TABELA: assignments
CREATE TABLE `gda_assignments` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_id` bigint(20) UNSIGNED NOT NULL,
    `lesson_id` bigint(20) UNSIGNED DEFAULT NULL,
    `title` varchar(200) NOT NULL,
    `description` text NOT NULL,
    `instructions` longtext,
    `type` enum('project','homework','exercise','challenge') NOT NULL DEFAULT 'homework',
    `difficulty` enum('easy','medium','hard','expert') NOT NULL DEFAULT 'medium',
    `max_score` int(10) UNSIGNED NOT NULL DEFAULT 100,
    `passing_score` int(10) UNSIGNED NOT NULL DEFAULT 60,
    `due_days` int(10) UNSIGNED DEFAULT NULL COMMENT 'Dias após início para entregar',
    `allow_late` tinyint(1) NOT NULL DEFAULT 1,
    `late_penalty` decimal(5,2) DEFAULT NULL COMMENT 'Penalidade por dia de atraso',
    `max_attempts` int(10) UNSIGNED DEFAULT 1,
    `resources` json DEFAULT NULL,
    `rubric` json DEFAULT NULL COMMENT 'Critérios de avaliação',
    `xp_reward` int(10) UNSIGNED NOT NULL DEFAULT 200,
    `is_published` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_assignments_course` (`course_id`),
    KEY `idx_assignments_lesson` (`lesson_id`),
    KEY `idx_assignments_type` (`type`),
    KEY `idx_assignments_difficulty` (`difficulty`),
    CONSTRAINT `fk_assignments_course` FOREIGN KEY (`course_id`) 
        REFERENCES `gda_courses` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_assignments_lesson` FOREIGN KEY (`lesson_id`) 
        REFERENCES `gda_lessons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABELA: assignment_submissions
CREATE TABLE `gda_assignment_submissions` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `assignment_id` bigint(20) UNSIGNED NOT NULL,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `enrollment_id` bigint(20) UNSIGNED NOT NULL,
    `attempt_number` int(10) UNSIGNED NOT NULL DEFAULT 1,
    `status` enum('draft','submitted','reviewing','graded','returned') NOT NULL DEFAULT 'draft',
    `content` longtext,
    `attachments` json DEFAULT NULL,
    `submitted_at` datetime DEFAULT NULL,
    `due_at` datetime DEFAULT NULL,
    `is_late` tinyint(1) NOT NULL DEFAULT 0,
    `score` decimal(5,2) DEFAULT NULL,
    `grade` varchar(10) DEFAULT NULL,
    `feedback` text,
    `graded_by` bigint(20) UNSIGNED DEFAULT NULL,
    `graded_at` datetime DEFAULT NULL,
    `xp_earned` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_submissions` (`assignment_id`, `user_id`, `attempt_number`),
    KEY `idx_submissions_user` (`user_id`),
    KEY `idx_submissions_enrollment` (`enrollment_id`),
    KEY `idx_submissions_status` (`status`),
    KEY `idx_submissions_grader` (`graded_by`),
    CONSTRAINT `fk_submissions_assignment` FOREIGN KEY (`assignment_id`) 
        REFERENCES `gda_assignments` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_submissions_user` FOREIGN KEY (`user_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_submissions_enrollment` FOREIGN KEY (`enrollment_id`) 
        REFERENCES `gda_enrollments` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_submissions_grader` FOREIGN KEY (`graded_by`) 
        REFERENCES `gda_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- SISTEMA DE WISHLIST E FAVORITOS
-- =========================================================================

-- TABELA: wishlists
CREATE TABLE `gda_wishlists` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `course_id` bigint(20) UNSIGNED NOT NULL,
    `priority` enum('low','medium','high') DEFAULT 'medium',
    `notes` text,
    `notified` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Se foi notificado sobre promoção',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_wishlists` (`user_id`, `course_id`),
    KEY `idx_wishlists_course` (`course_id`),
    KEY `idx_wishlists_priority` (`priority`),
    CONSTRAINT `fk_wishlists_user` FOREIGN KEY (`user_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_wishlists_course` FOREIGN KEY (`course_id`) 
        REFERENCES `gda_courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABELA: course_favorites
CREATE TABLE `gda_course_favorites` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `course_id` bigint(20) UNSIGNED NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_favorites` (`user_id`, `course_id`),
    KEY `idx_favorites_course` (`course_id`),
    CONSTRAINT `fk_favorites_user` FOREIGN KEY (`user_id`) 
        REFERENCES `gda_users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_favorites_course` FOREIGN KEY (`course_id`) 
        REFERENCES `gda_courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar tabela de reset de senha se não existir
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `used` BOOLEAN DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_token` (`token`),
    INDEX `idx_email` (`email`),
    INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Limpar tokens expirados (executar periodicamente)
DELETE FROM password_resets 
WHERE expires_at < NOW() OR used = 1;

-- =========================================================================
-- Agora temos as 51 tabelas completas!
-- =========================================================================
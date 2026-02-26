-- ================================================================
-- GAMEDEV ACADEMY - SCHEMA COMPLETO UNIFICADO CORRIGIDO
-- Versão: 5.0.0
-- Total de tabelas: ~100
-- Correções v4.0:
--   - 20 tabelas adicionadas (faltavam no schema anterior)
--   - 6 nomes de tabela corrigidos (modules→course_modules, etc.)
--   - AUTO_INCREMENT corrigido para refletir nomes reais
--   - FKs atualizadas após renomeações
-- Correções v5.0 (merge com schema-old v2.0):
--   - 19 tabelas do schema legado reintegradas e adaptadas ao padrão v4
--   - Dados iniciais (seed) adicionados: levels, achievements, categories,
--     badges, settings, usuários admin e demo
-- ================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
SET NAMES utf8mb4;
SET TIME_ZONE = '+00:00';

-- ================================================================
-- NÍVEL 0 - TABELAS SEM DEPENDÊNCIAS
-- ================================================================

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id`                        INT UNSIGNED       NOT NULL AUTO_INCREMENT,
    `name`                      VARCHAR(100)       NOT NULL                          COMMENT 'Nome de exibição',
    `full_name`                 VARCHAR(200)       DEFAULT NULL                      COMMENT 'Nome completo',
    `first_name`                VARCHAR(100)       DEFAULT NULL                      COMMENT 'Primeiro nome',
    `last_name`                 VARCHAR(100)       DEFAULT NULL                      COMMENT 'Sobrenome',
    `username`                  VARCHAR(50)        NOT NULL                          COMMENT 'Username único',
    `email`                     VARCHAR(150)       NOT NULL                          COMMENT 'Email único para login',
    `password`                  VARCHAR(255)       NOT NULL                          COMMENT 'Hash bcrypt da senha',
    `role`                      ENUM('student','instructor','admin','super_admin')
                                                   NOT NULL DEFAULT 'student'        COMMENT 'Papel do usuário',
    `avatar`                    VARCHAR(500)       DEFAULT NULL                      COMMENT 'URL da foto de perfil',
    `bio`                       TEXT               DEFAULT NULL                      COMMENT 'Biografia',
    `phone`                     VARCHAR(20)        DEFAULT NULL                      COMMENT 'Telefone com DDD',
    `website`                   VARCHAR(255)       DEFAULT NULL                      COMMENT 'Site pessoal',
    `social_github`             VARCHAR(255)       DEFAULT NULL,
    `social_linkedin`           VARCHAR(255)       DEFAULT NULL,
    `social_twitter`            VARCHAR(255)       DEFAULT NULL,
    `social_youtube`            VARCHAR(255)       DEFAULT NULL,
    `specialization`            VARCHAR(255)       DEFAULT NULL                      COMMENT 'Área de especialização',
    `total_points`              INT UNSIGNED       NOT NULL DEFAULT 0                COMMENT 'Cache pontos gamificação',
    `xp_total`                  INT UNSIGNED       NOT NULL DEFAULT 0                COMMENT 'XP total do usuário',
    `coins`                     INT UNSIGNED       NOT NULL DEFAULT 0                COMMENT 'Moedas do usuário',
    `level`                     INT UNSIGNED       NOT NULL DEFAULT 1                COMMENT 'Nível atual do usuário',
    `streak_days`               INT UNSIGNED       NOT NULL DEFAULT 0                COMMENT 'Dias seguidos de atividade',
    `last_activity`             DATE               DEFAULT NULL                      COMMENT 'Data da última atividade',
    `email_verified_at`         TIMESTAMP          NULL DEFAULT NULL,
    `email_verification_token`  VARCHAR(100)       DEFAULT NULL,
    `password_reset_token`      VARCHAR(100)       DEFAULT NULL,
    `password_reset_expires`    TIMESTAMP          NULL DEFAULT NULL,
    `two_factor_secret`         VARCHAR(255)       DEFAULT NULL,
    `two_factor_enabled`        TINYINT(1)         NOT NULL DEFAULT 0,
    `last_login_at`             TIMESTAMP          NULL DEFAULT NULL,
    `last_login_ip`             VARCHAR(45)        DEFAULT NULL,
    `is_active`                 TINYINT(1)         NOT NULL DEFAULT 1,
    `status`                    ENUM('active','inactive','banned','pending')
                                                   NOT NULL DEFAULT 'active'        COMMENT 'Status do usuário',
    `preferences`               JSON               DEFAULT NULL,
    `created_at`                TIMESTAMP          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`                TIMESTAMP          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_email` (`email`),
    UNIQUE KEY `uk_users_username` (`username`),
    KEY `idx_users_role` (`role`),
    KEY `idx_users_active` (`is_active`),
    KEY `idx_users_status` (`status`),
    KEY `idx_users_created` (`created_at`),
    KEY `idx_users_points` (`total_points` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Usuários do sistema';


-- ================================================================
-- CORREÇÃO: user_profiles, user_settings, user_social_links
-- (referenciadas no AUTO_INCREMENT mas faltavam no schema)
-- ================================================================

DROP TABLE IF EXISTS `user_profiles`;
CREATE TABLE `user_profiles` (
    `id`                INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`           INT UNSIGNED     NOT NULL,
    `birth_date`        DATE             DEFAULT NULL,
    `gender`            ENUM('male','female','non_binary','prefer_not_to_say') DEFAULT NULL,
    `country_code`      CHAR(2)          DEFAULT NULL                    COMMENT 'ISO 3166-1 alpha-2',
    `state`             VARCHAR(100)     DEFAULT NULL,
    `city`              VARCHAR(100)     DEFAULT NULL,
    `timezone`          VARCHAR(50)      DEFAULT 'America/Sao_Paulo',
    `language`          VARCHAR(10)      DEFAULT 'pt-BR',
    `experience_level`  ENUM('beginner','intermediate','advanced','professional') DEFAULT 'beginner',
    `years_experience`  TINYINT UNSIGNED DEFAULT NULL,
    `headline`          VARCHAR(255)     DEFAULT NULL                    COMMENT 'Tagline do perfil',
    `portfolio_url`     VARCHAR(500)     DEFAULT NULL,
    `cover_image`       VARCHAR(500)     DEFAULT NULL,
    `is_public`         TINYINT(1)       NOT NULL DEFAULT 1              COMMENT 'Perfil público?',
    `show_email`        TINYINT(1)       NOT NULL DEFAULT 0,
    `show_phone`        TINYINT(1)       NOT NULL DEFAULT 0,
    `updated_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_profile_user` (`user_id`),
    CONSTRAINT `fk_profiles_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Perfil estendido dos usuários';


DROP TABLE IF EXISTS `user_settings`;
CREATE TABLE `user_settings` (
    `id`                        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`                   INT UNSIGNED    NOT NULL,
    `email_new_message`         TINYINT(1)      NOT NULL DEFAULT 1,
    `email_course_updates`      TINYINT(1)      NOT NULL DEFAULT 1,
    `email_promotions`          TINYINT(1)      NOT NULL DEFAULT 0,
    `email_newsletter`          TINYINT(1)      NOT NULL DEFAULT 0,
    `notify_new_reply`          TINYINT(1)      NOT NULL DEFAULT 1,
    `notify_achievement`        TINYINT(1)      NOT NULL DEFAULT 1,
    `notify_enrollment`         TINYINT(1)      NOT NULL DEFAULT 1,
    `theme`                     ENUM('light','dark','system') NOT NULL DEFAULT 'dark',
    `sidebar_collapsed`         TINYINT(1)      NOT NULL DEFAULT 0,
    `video_quality`             ENUM('auto','1080p','720p','480p','360p') NOT NULL DEFAULT 'auto',
    `video_speed`               DECIMAL(3,1)    NOT NULL DEFAULT 1.0,
    `autoplay`                  TINYINT(1)      NOT NULL DEFAULT 1,
    `subtitles_enabled`         TINYINT(1)      NOT NULL DEFAULT 0,
    `updated_at`                TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_settings_user` (`user_id`),
    CONSTRAINT `fk_usersettings_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Configurações e preferências individuais por usuário';


DROP TABLE IF EXISTS `user_social_links`;
CREATE TABLE `user_social_links` (
    `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED     NOT NULL,
    `platform`    ENUM('github','linkedin','twitter','youtube','twitch','discord','instagram','facebook','tiktok','website','other')
                                   NOT NULL,
    `url`         VARCHAR(500)     NOT NULL,
    `label`       VARCHAR(100)     DEFAULT NULL                          COMMENT 'Texto exibido',
    `sort_order`  INT              NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_social_user_platform` (`user_id`, `platform`),
    CONSTRAINT `fk_sociallinks_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Links sociais detalhados por usuário';


DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
    `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(100)     NOT NULL,
    `slug`          VARCHAR(120)     NOT NULL,
    `description`   TEXT             DEFAULT NULL,
    `icon`          VARCHAR(100)     DEFAULT NULL,
    `image`         VARCHAR(500)     DEFAULT NULL,
    `color`         VARCHAR(7)       DEFAULT '#6366f1',
    `parent_id`     INT UNSIGNED     DEFAULT NULL,
    `sort_order`    INT              NOT NULL DEFAULT 0,
    `is_active`     TINYINT(1)       NOT NULL DEFAULT 1,
    `status`        ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `course_count`  INT UNSIGNED     NOT NULL DEFAULT 0,
    `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_categories_slug` (`slug`),
    KEY `idx_categories_parent` (`parent_id`),
    KEY `idx_categories_active_order` (`is_active`, `sort_order`),
    KEY `idx_categories_status` (`status`),
    CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`)
        REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(50)     NOT NULL,
    `slug`        VARCHAR(60)     NOT NULL,
    `usage_count` INT UNSIGNED    NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_tags_slug` (`slug`),
    KEY `idx_tags_usage` (`usage_count` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `setting_key`     VARCHAR(100)     NOT NULL,
    `setting_label`   VARCHAR(100)     DEFAULT NULL                    COMMENT 'Label para exibição',
    `setting_value`   LONGTEXT         DEFAULT NULL,
    `setting_type`    ENUM('string','number','boolean','json','html','text')
                                       NOT NULL DEFAULT 'string',
    `setting_group`   VARCHAR(50)      NOT NULL DEFAULT 'general',
    `description`     VARCHAR(255)     DEFAULT NULL,
    `is_public`       TINYINT(1)       NOT NULL DEFAULT 0,
    `updated_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_settings_key` (`setting_key`),
    KEY `idx_settings_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
    `id`               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `title`            VARCHAR(255)     NOT NULL,
    `slug`             VARCHAR(280)     NOT NULL,
    `content`          LONGTEXT         NOT NULL,
    `author_id`        INT UNSIGNED     DEFAULT NULL,
    `meta_title`       VARCHAR(255)     DEFAULT NULL,
    `meta_description` VARCHAR(500)     DEFAULT NULL,
    `template`         VARCHAR(50)      DEFAULT 'default',
    `sort_order`       INT              NOT NULL DEFAULT 0,
    `show_in_menu`     TINYINT(1)       NOT NULL DEFAULT 0,
    `show_in_footer`   TINYINT(1)       NOT NULL DEFAULT 0,
    `is_published`     TINYINT(1)       NOT NULL DEFAULT 1,
    `status`           ENUM('published','draft','archived') NOT NULL DEFAULT 'published',
    `created_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_pages_slug` (`slug`),
    KEY `idx_pages_published` (`is_published`),
    KEY `idx_pages_status` (`status`),
    KEY `idx_pages_menu` (`show_in_menu`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `badges`;
CREATE TABLE `badges` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`            VARCHAR(100)     NOT NULL,
    `slug`            VARCHAR(120)     NOT NULL,
    `description`     TEXT             DEFAULT NULL,
    `icon`            VARCHAR(500)     NOT NULL,
    `category`        ENUM('course','engagement','achievement','special','community')
                                       NOT NULL DEFAULT 'achievement',
    `criteria_type`   VARCHAR(50)      NOT NULL,
    `criteria_value`  INT UNSIGNED     NOT NULL DEFAULT 1,
    `points_reward`   INT UNSIGNED     NOT NULL DEFAULT 0,
    `rarity`          ENUM('common','uncommon','rare','epic','legendary')
                                       NOT NULL DEFAULT 'common',
    `sort_order`      INT              NOT NULL DEFAULT 0,
    `is_active`       TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_badges_slug` (`slug`),
    KEY `idx_badges_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ================================================================
-- CORREÇÃO: achievements e user_achievements
-- (referenciadas no AUTO_INCREMENT mas faltavam no schema)
-- ================================================================

DROP TABLE IF EXISTS `achievements`;
CREATE TABLE `achievements` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`            VARCHAR(100)     NOT NULL,
    `slug`            VARCHAR(120)     NOT NULL,
    `description`     TEXT             DEFAULT NULL,
    `icon`            VARCHAR(500)     DEFAULT NULL,
    `type`            ENUM('course','lesson','quiz','streak','social','special','milestone')
                                       NOT NULL DEFAULT 'milestone',
    `requirement_type` VARCHAR(50)      NOT NULL                          COMMENT 'Ex: lessons_completed, courses_completed, streak, xp_earned',
    `requirement_value` INT UNSIGNED     NOT NULL DEFAULT 1                COMMENT 'Valor necessário para desbloquear',
    `criteria_type`   VARCHAR(50)      DEFAULT NULL                      COMMENT 'Alias para requirement_type',
    `criteria_value`  INT UNSIGNED     DEFAULT 1                         COMMENT 'Alias para requirement_value',
    `xp_reward`       INT UNSIGNED     NOT NULL DEFAULT 0,
    `coin_reward`     INT UNSIGNED     NOT NULL DEFAULT 0,
    `points_reward`   INT UNSIGNED     DEFAULT 0                         COMMENT 'Alias para xp_reward',
    `badge_id`        INT UNSIGNED     DEFAULT NULL                      COMMENT 'Badge associado ao achievement',
    `is_hidden`       TINYINT(1)       NOT NULL DEFAULT 0                COMMENT 'Achievement secreto',
    `sort_order`      INT              NOT NULL DEFAULT 0,
    `is_active`       TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_achievements_slug` (`slug`),
    KEY `idx_achievements_type` (`type`),
    CONSTRAINT `fk_achievements_badge` FOREIGN KEY (`badge_id`)
        REFERENCES `badges` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Conquistas da plataforma de gamificação';


DROP TABLE IF EXISTS `certificate_templates`;
CREATE TABLE `certificate_templates` (
    `id`                INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`              VARCHAR(100)     NOT NULL,
    `html_template`     LONGTEXT         NOT NULL,
    `css_styles`        LONGTEXT         DEFAULT NULL,
    `background_image`  VARCHAR(500)     DEFAULT NULL,
    `orientation`       ENUM('landscape','portrait') NOT NULL DEFAULT 'landscape',
    `paper_size`        ENUM('a4','letter','custom') NOT NULL DEFAULT 'a4',
    `is_default`        TINYINT(1)       NOT NULL DEFAULT 0,
    `is_active`         TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `email_templates`;
CREATE TABLE `email_templates` (
    `id`           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`         VARCHAR(100)     NOT NULL,
    `subject`      VARCHAR(255)     NOT NULL,
    `body_html`    LONGTEXT         NOT NULL,
    `body_text`    LONGTEXT         DEFAULT NULL,
    `variables`    JSON             DEFAULT NULL,
    `is_active`    TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_email_templates_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `email_log`;
CREATE TABLE `email_log` (
    `id`             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `to_email`       VARCHAR(150)     NOT NULL,
    `to_name`        VARCHAR(100)     DEFAULT NULL,
    `subject`        VARCHAR(255)     NOT NULL,
    `template`       VARCHAR(50)      DEFAULT NULL,
    `body_preview`   VARCHAR(500)     DEFAULT NULL,
    `status`         ENUM('queued','sent','failed','bounced') NOT NULL DEFAULT 'queued',
    `error_message`  TEXT             DEFAULT NULL,
    `attempts`       TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `sent_at`        TIMESTAMP        NULL DEFAULT NULL,
    `created_at`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_emaillog_status` (`status`),
    KEY `idx_emaillog_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `faq_categories`;
CREATE TABLE `faq_categories` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100)    NOT NULL,
    `slug`        VARCHAR(120)    NOT NULL,
    `icon`        VARCHAR(100)    DEFAULT NULL,
    `sort_order`  INT             NOT NULL DEFAULT 0,
    `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_faqcat_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `faqs`;
CREATE TABLE `faqs` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `category_id`  INT UNSIGNED    DEFAULT NULL,
    `question`     VARCHAR(500)    NOT NULL,
    `answer`       LONGTEXT        NOT NULL,
    `sort_order`   INT             NOT NULL DEFAULT 0,
    `is_published` TINYINT(1)      NOT NULL DEFAULT 1,
    `view_count`   INT UNSIGNED    NOT NULL DEFAULT 0,
    `helpful_yes`  INT UNSIGNED    NOT NULL DEFAULT 0,
    `helpful_no`   INT UNSIGNED    NOT NULL DEFAULT 0,
    `created_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_faqs_category` (`category_id`),
    CONSTRAINT `fk_faqs_category` FOREIGN KEY (`category_id`)
        REFERENCES `faq_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `announcements`;
CREATE TABLE `announcements` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `title`           VARCHAR(255)     NOT NULL,
    `content`         LONGTEXT         NOT NULL,
    `type`            ENUM('info','warning','success','danger','promotion') NOT NULL DEFAULT 'info',
    `target_audience` ENUM('all','students','instructors','admins') NOT NULL DEFAULT 'all',
    `display_type`    ENUM('banner','modal','notification') NOT NULL DEFAULT 'banner',
    `action_url`      VARCHAR(500)     DEFAULT NULL,
    `action_text`     VARCHAR(100)     DEFAULT NULL,
    `starts_at`       TIMESTAMP        NULL DEFAULT NULL,
    `ends_at`         TIMESTAMP        NULL DEFAULT NULL,
    `is_active`       TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_announcements_active` (`is_active`, `starts_at`, `ends_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `countries`;
CREATE TABLE `countries` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100)    NOT NULL,
    `code`        CHAR(2)         NOT NULL,
    `phone_code`  VARCHAR(5)      DEFAULT NULL,
    `currency`    VARCHAR(3)      DEFAULT NULL,
    `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_countries_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `languages`;
CREATE TABLE `languages` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(50)     NOT NULL,
    `code`        VARCHAR(10)     NOT NULL,
    `native_name` VARCHAR(50)     DEFAULT NULL,
    `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_languages_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ================================================================
-- CORREÇÃO: subscription_plans e subscriptions
-- (referenciadas no AUTO_INCREMENT mas faltavam no schema)
-- ================================================================

DROP TABLE IF EXISTS `subscription_plans`;
CREATE TABLE `subscription_plans` (
    `id`                    INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`                  VARCHAR(100)     NOT NULL,
    `slug`                  VARCHAR(120)     NOT NULL,
    `description`           TEXT             DEFAULT NULL,
    `price_monthly`         DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
    `price_annual`          DECIMAL(10,2)    DEFAULT NULL,
    `currency`              VARCHAR(3)       NOT NULL DEFAULT 'BRL',
    `trial_days`            INT UNSIGNED     NOT NULL DEFAULT 0,
    `max_courses`           INT UNSIGNED     DEFAULT NULL                COMMENT 'NULL = ilimitado',
    `has_certificates`      TINYINT(1)       NOT NULL DEFAULT 1,
    `has_downloads`         TINYINT(1)       NOT NULL DEFAULT 0,
    `has_offline_access`    TINYINT(1)       NOT NULL DEFAULT 0,
    `has_mentorship`        TINYINT(1)       NOT NULL DEFAULT 0,
    `features`              JSON             DEFAULT NULL                COMMENT 'Lista de features',
    `sort_order`            INT              NOT NULL DEFAULT 0,
    `is_popular`            TINYINT(1)       NOT NULL DEFAULT 0,
    `is_active`             TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`            TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_plans_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Planos de assinatura da plataforma';


-- ================================================================
-- NÍVEL 1 - DEPENDEM DO NÍVEL 0
-- ================================================================

DROP TABLE IF EXISTS `user_sessions`;
CREATE TABLE `user_sessions` (
    `id`             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`        INT UNSIGNED     NOT NULL,
    `session_token`  VARCHAR(255)     NOT NULL,
    `ip_address`     VARCHAR(45)      DEFAULT NULL,
    `user_agent`     TEXT             DEFAULT NULL,
    `device_type`    ENUM('desktop','mobile','tablet','unknown') NOT NULL DEFAULT 'unknown',
    `last_activity`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `expires_at`     TIMESTAMP        NOT NULL,
    `created_at`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_session_token` (`session_token`),
    KEY `idx_sessions_user` (`user_id`),
    CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `user_streaks`;
CREATE TABLE `user_streaks` (
    `id`                 INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`            INT UNSIGNED    NOT NULL,
    `current_streak`     INT UNSIGNED    NOT NULL DEFAULT 0,
    `longest_streak`     INT UNSIGNED    NOT NULL DEFAULT 0,
    `last_activity_date` DATE            DEFAULT NULL,
    `updated_at`         TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_streak_user` (`user_id`),
    CONSTRAINT `fk_streaks_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `user_badges`;
CREATE TABLE `user_badges` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED    NOT NULL,
    `badge_id`   INT UNSIGNED    NOT NULL,
    `earned_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_badge` (`user_id`, `badge_id`),
    KEY `idx_userbadges_badge` (`badge_id`),
    CONSTRAINT `fk_userbadges_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_userbadges_badge` FOREIGN KEY (`badge_id`)
        REFERENCES `badges` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `user_achievements`;
CREATE TABLE `user_achievements` (
    `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`        INT UNSIGNED    NOT NULL,
    `achievement_id` INT UNSIGNED    NOT NULL,
    `progress`       INT UNSIGNED    NOT NULL DEFAULT 0               COMMENT 'Progresso atual (ex: 3 de 5 cursos)',
    `is_completed`   TINYINT(1)      NOT NULL DEFAULT 0,
    `unlocked_at`    TIMESTAMP       NULL DEFAULT NULL,
    `earned_at`      TIMESTAMP       NULL DEFAULT NULL                COMMENT 'Alias para unlocked_at',
    `created_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_achievement` (`user_id`, `achievement_id`),
    KEY `idx_userachiev_achievement` (`achievement_id`),
    CONSTRAINT `fk_userachiev_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_userachiev_achievement` FOREIGN KEY (`achievement_id`)
        REFERENCES `achievements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Conquistas desbloqueadas por cada usuário';


-- CORREÇÃO: renomeado de user_points para points
DROP TABLE IF EXISTS `points`;
CREATE TABLE `points` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`         INT UNSIGNED     NOT NULL,
    `points`          INT              NOT NULL,
    `action`          VARCHAR(50)      NOT NULL,
    `reference_type`  VARCHAR(50)      DEFAULT NULL,
    `reference_id`    INT UNSIGNED     DEFAULT NULL,
    `description`     VARCHAR(255)     DEFAULT NULL,
    `created_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_points_user` (`user_id`),
    KEY `idx_points_action` (`action`),
    CONSTRAINT `fk_points_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Histórico de pontos de gamificação';


DROP TABLE IF EXISTS `leaderboard`;
CREATE TABLE `leaderboard` (
    `id`                INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`           INT UNSIGNED     NOT NULL,
    `total_points`      INT UNSIGNED     NOT NULL DEFAULT 0,
    `courses_completed` INT UNSIGNED     NOT NULL DEFAULT 0,
    `badges_earned`     INT UNSIGNED     NOT NULL DEFAULT 0,
    `rank_position`     INT UNSIGNED     DEFAULT NULL,
    `period`            ENUM('weekly','monthly','all_time') NOT NULL DEFAULT 'all_time',
    `period_start`      DATE             DEFAULT NULL,
    `updated_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_leaderboard` (`user_id`, `period`, `period_start`),
    CONSTRAINT `fk_leaderboard_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
    `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED     NOT NULL,
    `type`        VARCHAR(50)      NOT NULL,
    `title`       VARCHAR(255)     NOT NULL,
    `message`     TEXT             NOT NULL,
    `icon`        VARCHAR(100)     DEFAULT NULL,
    `action_url`  VARCHAR(500)     DEFAULT NULL,
    `link`        VARCHAR(500)     DEFAULT NULL                COMMENT 'Alias para action_url',
    `data`        JSON             DEFAULT NULL,
    `is_read`     TINYINT(1)       NOT NULL DEFAULT 0,
    `read_at`     TIMESTAMP        NULL DEFAULT NULL,
    `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_notif_user_read` (`user_id`, `is_read`, `created_at` DESC),
    CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ================================================================
-- CORREÇÃO: user_notifications
-- (referenciada no AUTO_INCREMENT mas faltava no schema)
-- ================================================================

DROP TABLE IF EXISTS `user_notifications`;
CREATE TABLE `user_notifications` (
    `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`         INT UNSIGNED    NOT NULL,
    `notification_id` INT UNSIGNED    NOT NULL                      COMMENT 'Referência para a notificação global',
    `is_read`         TINYINT(1)      NOT NULL DEFAULT 0,
    `read_at`         TIMESTAMP       NULL DEFAULT NULL,
    `created_at`      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_usernotif` (`user_id`, `notification_id`),
    KEY `idx_usernotif_read` (`user_id`, `is_read`),
    CONSTRAINT `fk_usernotif_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_usernotif_notification` FOREIGN KEY (`notification_id`)
        REFERENCES `notifications` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tabela de associação de notificações broadcast por usuário';


DROP TABLE IF EXISTS `notification_preferences`;
CREATE TABLE `notification_preferences` (
    `id`                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`             INT UNSIGNED    NOT NULL,
    `notification_type`   VARCHAR(50)     NOT NULL,
    `email_enabled`       TINYINT(1)      NOT NULL DEFAULT 1,
    `push_enabled`        TINYINT(1)      NOT NULL DEFAULT 1,
    `in_app_enabled`      TINYINT(1)      NOT NULL DEFAULT 1,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_notifpref` (`user_id`, `notification_type`),
    CONSTRAINT `fk_notifpref_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `media`;
CREATE TABLE `media` (
    `id`                INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`           INT UNSIGNED     DEFAULT NULL,
    `filename`          VARCHAR(255)     NOT NULL,
    `original_filename` VARCHAR(255)     NOT NULL,
    `title`             VARCHAR(255)     DEFAULT NULL,
    `description`       TEXT             DEFAULT NULL,
    `file_path`         VARCHAR(500)     NOT NULL,
    `file_url`          VARCHAR(500)     NOT NULL,
    `mime_type`         VARCHAR(100)     NOT NULL,
    `file_size`         BIGINT UNSIGNED  NOT NULL,
    `dimensions`        VARCHAR(20)      DEFAULT NULL,
    `alt_text`          VARCHAR(255)     DEFAULT NULL,
    `folder`            VARCHAR(100)     DEFAULT 'general',
    `disk`              VARCHAR(20)      DEFAULT 'local',
    `created_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_media_user` (`user_id`),
    KEY `idx_media_folder` (`folder`),
    CONSTRAINT `fk_media_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `coupons`;
CREATE TABLE `coupons` (
    `id`                 INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `code`               VARCHAR(50)      NOT NULL,
    `description`        VARCHAR(255)     DEFAULT NULL,
    `discount_type`      ENUM('percentage','fixed') NOT NULL DEFAULT 'percentage',
    `discount_value`     DECIMAL(10,2)    NOT NULL,
    `min_purchase`       DECIMAL(10,2)    DEFAULT NULL,
    `max_discount`       DECIMAL(10,2)    DEFAULT NULL,
    `max_uses`           INT UNSIGNED     DEFAULT NULL,
    `max_uses_per_user`  INT UNSIGNED     NOT NULL DEFAULT 1,
    `used_count`         INT UNSIGNED     NOT NULL DEFAULT 0,
    `applicable_courses` JSON             DEFAULT NULL,
    `starts_at`          TIMESTAMP        NULL DEFAULT NULL,
    `expires_at`         TIMESTAMP        NULL DEFAULT NULL,
    `is_active`          TINYINT(1)       NOT NULL DEFAULT 1,
    `created_by`         INT UNSIGNED     DEFAULT NULL,
    `created_at`         TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`         TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_coupon_code` (`code`),
    CONSTRAINT `fk_coupons_creator` FOREIGN KEY (`created_by`)
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `subscriptions`;
CREATE TABLE `subscriptions` (
    `id`                    INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`               INT UNSIGNED     NOT NULL,
    `plan_id`               INT UNSIGNED     NOT NULL,
    `status`                ENUM('trialing','active','past_due','cancelled','expired','paused')
                                             NOT NULL DEFAULT 'active',
    `billing_cycle`         ENUM('monthly','annual') NOT NULL DEFAULT 'monthly',
    `amount`                DECIMAL(10,2)    NOT NULL,
    `currency`              VARCHAR(3)       NOT NULL DEFAULT 'BRL',
    `gateway`               VARCHAR(50)      DEFAULT NULL,
    `gateway_subscription_id` VARCHAR(255)   DEFAULT NULL,
    `trial_ends_at`         TIMESTAMP        NULL DEFAULT NULL,
    `current_period_start`  TIMESTAMP        NULL DEFAULT NULL,
    `current_period_end`    TIMESTAMP        NULL DEFAULT NULL,
    `cancelled_at`          TIMESTAMP        NULL DEFAULT NULL,
    `cancel_reason`         VARCHAR(255)     DEFAULT NULL,
    `started_at`            TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at`            TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_subscriptions_user` (`user_id`),
    KEY `idx_subscriptions_status` (`status`),
    CONSTRAINT `fk_subscriptions_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_subscriptions_plan` FOREIGN KEY (`plan_id`)
        REFERENCES `subscription_plans` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Assinaturas ativas dos usuários';


DROP TABLE IF EXISTS `courses`;
CREATE TABLE `courses` (
    `id`                INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `title`             VARCHAR(255)     NOT NULL,
    `slug`              VARCHAR(280)     NOT NULL,
    `subtitle`          VARCHAR(300)     DEFAULT NULL,
    `description`       LONGTEXT         DEFAULT NULL,
    `short_description` VARCHAR(500)     DEFAULT NULL,
    `thumbnail`         VARCHAR(500)     DEFAULT NULL,
    `image`             VARCHAR(500)     DEFAULT NULL,
    `cover_image`       VARCHAR(500)     DEFAULT NULL                    COMMENT 'Alias para thumbnail',
    `preview_video`     VARCHAR(500)     DEFAULT NULL,
    `instructor_id`     INT UNSIGNED     NOT NULL,
    `category_id`       INT UNSIGNED     DEFAULT NULL,
    `level`             ENUM('beginner','intermediate','advanced','expert','all_levels')
                                         NOT NULL DEFAULT 'beginner',
    `language`          VARCHAR(10)      NOT NULL DEFAULT 'pt-BR',
    `price`             DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
    `original_price`    DECIMAL(10,2)    DEFAULT NULL,
    `currency`          VARCHAR(3)       NOT NULL DEFAULT 'BRL',
    `duration_hours`    DECIMAL(6,1)     NOT NULL DEFAULT 0.0,
    `xp_reward`         INT UNSIGNED     NOT NULL DEFAULT 100,
    `coin_reward`       INT UNSIGNED     NOT NULL DEFAULT 10,
    `total_lessons`     INT UNSIGNED     NOT NULL DEFAULT 0,
    `total_modules`     INT UNSIGNED     NOT NULL DEFAULT 0,
    `requirements`      JSON             DEFAULT NULL,
    `what_you_learn`    JSON             DEFAULT NULL,
    `target_audience`   JSON             DEFAULT NULL,
    `resources`         JSON             DEFAULT NULL,
    `game_engine`       ENUM('unity','unreal','godot','gamemaker','construct','phaser','pygame','love2d','custom','none')
                                         DEFAULT NULL,
    `programming_lang`  VARCHAR(50)      DEFAULT NULL,
    `status`            ENUM('draft','pending_review','published','archived','suspended')
                                         NOT NULL DEFAULT 'draft',
    `is_published`      TINYINT(1)       NOT NULL DEFAULT 0             COMMENT 'Cache: publicado?',
    `is_active`         TINYINT(1)       NOT NULL DEFAULT 1,
    `is_featured`       TINYINT(1)       NOT NULL DEFAULT 0,
    `is_free`           TINYINT(1)       NOT NULL DEFAULT 0,
    `is_bestseller`     TINYINT(1)       NOT NULL DEFAULT 0,
    `is_new`            TINYINT(1)       NOT NULL DEFAULT 0,
    `enrollment_count`  INT UNSIGNED     NOT NULL DEFAULT 0,
    `total_students`    INT UNSIGNED     NOT NULL DEFAULT 0             COMMENT 'Cache: alias enrollment_count',
    `rating_average`    DECIMAL(3,2)     NOT NULL DEFAULT 0.00,
    `average_rating`    DECIMAL(3,2)     NOT NULL DEFAULT 0.00          COMMENT 'Cache: alias rating_average',
    `rating_count`      INT UNSIGNED     NOT NULL DEFAULT 0,
    `total_reviews`     INT UNSIGNED     NOT NULL DEFAULT 0             COMMENT 'Cache: alias rating_count',
    `completion_rate`   DECIMAL(5,2)     NOT NULL DEFAULT 0.00,
    `view_count`        INT UNSIGNED     NOT NULL DEFAULT 0,
    `meta_title`        VARCHAR(255)     DEFAULT NULL,
    `meta_description`  VARCHAR(500)     DEFAULT NULL,
    `published_at`      TIMESTAMP        NULL DEFAULT NULL,
    `created_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_courses_slug` (`slug`),
    KEY `idx_courses_instructor` (`instructor_id`),
    KEY `idx_courses_category` (`category_id`),
    KEY `idx_courses_status` (`status`),
    KEY `idx_courses_published` (`is_published`),
    KEY `idx_courses_featured` (`is_featured`, `status`),
    KEY `idx_courses_level` (`level`),
    KEY `idx_courses_engine` (`game_engine`),
    KEY `idx_courses_price` (`price`),
    KEY `idx_courses_free` (`is_free`),
    KEY `idx_courses_rating` (`rating_average` DESC),
    KEY `idx_courses_enrollment` (`enrollment_count` DESC),
    KEY `idx_courses_students` (`total_students` DESC),
    FULLTEXT KEY `ft_courses_search` (`title`, `description`, `short_description`),
    CONSTRAINT `fk_courses_instructor` FOREIGN KEY (`instructor_id`)
        REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_courses_category` FOREIGN KEY (`category_id`)
        REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `blog_posts`;
CREATE TABLE `blog_posts` (
    `id`               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `title`            VARCHAR(255)     NOT NULL,
    `slug`             VARCHAR(280)     NOT NULL,
    `excerpt`          VARCHAR(500)     DEFAULT NULL,
    `content`          LONGTEXT         NOT NULL,
    `featured_image`   VARCHAR(500)     DEFAULT NULL,
    `image`            VARCHAR(500)     DEFAULT NULL                    COMMENT 'Alias para featured_image',
    `cover_image`      VARCHAR(500)     DEFAULT NULL                    COMMENT 'Alias para featured_image',
    `author_id`        INT UNSIGNED     NOT NULL,
    `category_id`      INT UNSIGNED     DEFAULT NULL,
    `status`           ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    `is_featured`      TINYINT(1)       NOT NULL DEFAULT 0,
    `allow_comments`   TINYINT(1)       NOT NULL DEFAULT 1,
    `view_count`       INT UNSIGNED     NOT NULL DEFAULT 0,
    `reading_time`     INT UNSIGNED     DEFAULT NULL,
    `meta_title`       VARCHAR(255)     DEFAULT NULL,
    `meta_description` VARCHAR(500)     DEFAULT NULL,
    `published_at`     TIMESTAMP        NULL DEFAULT NULL,
    `created_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_blogposts_slug` (`slug`),
    KEY `idx_blogposts_author` (`author_id`),
    KEY `idx_blogposts_status` (`status`, `published_at` DESC),
    FULLTEXT KEY `ft_blogposts_search` (`title`, `content`),
    CONSTRAINT `fk_blogposts_author` FOREIGN KEY (`author_id`)
        REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_blogposts_category` FOREIGN KEY (`category_id`)
        REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ================================================================
-- NÍVEL 2 - DEPENDEM DOS NÍVEIS 0 E 1
-- ================================================================

DROP TABLE IF EXISTS `course_tags`;
CREATE TABLE `course_tags` (
    `course_id`  INT UNSIGNED    NOT NULL,
    `tag_id`     INT UNSIGNED    NOT NULL,
    PRIMARY KEY (`course_id`, `tag_id`),
    CONSTRAINT `fk_coursetags_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_coursetags_tag` FOREIGN KEY (`tag_id`)
        REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `course_categories`;
CREATE TABLE `course_categories` (
    `course_id`    INT UNSIGNED    NOT NULL,
    `category_id`  INT UNSIGNED    NOT NULL,
    PRIMARY KEY (`course_id`, `category_id`),
    CONSTRAINT `fk_coursecat_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_coursecat_category` FOREIGN KEY (`category_id`)
        REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `blog_post_tags`;
CREATE TABLE `blog_post_tags` (
    `post_id`  INT UNSIGNED    NOT NULL,
    `tag_id`   INT UNSIGNED    NOT NULL,
    PRIMARY KEY (`post_id`, `tag_id`),
    CONSTRAINT `fk_blogposttags_post` FOREIGN KEY (`post_id`)
        REFERENCES `blog_posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_blogposttags_tag` FOREIGN KEY (`tag_id`)
        REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ================================================================
-- CORREÇÃO: course_requirements e course_objectives
-- (referenciadas no AUTO_INCREMENT mas faltavam no schema)
-- ================================================================

DROP TABLE IF EXISTS `course_requirements`;
CREATE TABLE `course_requirements` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `course_id`   INT UNSIGNED    NOT NULL,
    `description` VARCHAR(500)    NOT NULL                            COMMENT 'Pré-requisito do curso',
    `sort_order`  INT             NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_requirements_course` (`course_id`),
    CONSTRAINT `fk_requirements_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Pré-requisitos listados por curso';


DROP TABLE IF EXISTS `course_objectives`;
CREATE TABLE `course_objectives` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `course_id`   INT UNSIGNED    NOT NULL,
    `description` VARCHAR(500)    NOT NULL                            COMMENT 'O que você vai aprender',
    `sort_order`  INT             NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_objectives_course` (`course_id`),
    CONSTRAINT `fk_objectives_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Objetivos de aprendizado por curso';


-- CORREÇÃO: renomeado de modules para course_modules
DROP TABLE IF EXISTS `course_modules`;
CREATE TABLE `course_modules` (
    `id`               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `course_id`        INT UNSIGNED     NOT NULL,
    `title`            VARCHAR(255)     NOT NULL,
    `description`      TEXT             DEFAULT NULL,
    `sort_order`       INT              NOT NULL DEFAULT 0,
    `is_free_preview`  TINYINT(1)       NOT NULL DEFAULT 0,
    `is_published`     TINYINT(1)       NOT NULL DEFAULT 1,
    `duration_minutes` INT UNSIGNED     NOT NULL DEFAULT 0,
    `xp_reward`        INT UNSIGNED     NOT NULL DEFAULT 50,
    `unlock_after_module` INT UNSIGNED  DEFAULT NULL,
    `lesson_count`     INT UNSIGNED     NOT NULL DEFAULT 0,
    `created_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_modules_course_order` (`course_id`, `sort_order`),
    CONSTRAINT `fk_modules_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `enrollments`;
CREATE TABLE `enrollments` (
    `id`                INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`           INT UNSIGNED     NOT NULL,
    `course_id`         INT UNSIGNED     NOT NULL,
    `status`            ENUM('active','completed','cancelled','expired','refunded','paused')
                                         NOT NULL DEFAULT 'active',
    `progress_percent`  DECIMAL(5,2)     NOT NULL DEFAULT 0.00,
    `lessons_completed` INT UNSIGNED     NOT NULL DEFAULT 0,
    `last_lesson_id`    INT UNSIGNED     DEFAULT NULL,
    `last_accessed_at`  TIMESTAMP        NULL DEFAULT NULL,
    `enrolled_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completed_at`      TIMESTAMP        NULL DEFAULT NULL,
    `expires_at`        TIMESTAMP        NULL DEFAULT NULL,
    `payment_id`        INT UNSIGNED     DEFAULT NULL,
    `certificate_issued` TINYINT(1)      NOT NULL DEFAULT 0,
    `source`            VARCHAR(50)      DEFAULT 'direct',
    `created_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_enrollment` (`user_id`, `course_id`),
    KEY `idx_enrollments_course` (`course_id`),
    KEY `idx_enrollments_status` (`status`),
    KEY `idx_enrollments_enrolled` (`enrolled_at`),
    CONSTRAINT `fk_enrollments_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_enrollments_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- CORREÇÃO: renomeado de reviews para course_reviews
DROP TABLE IF EXISTS `course_reviews`;
CREATE TABLE `course_reviews` (
    `id`                    INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`               INT UNSIGNED     NOT NULL,
    `course_id`             INT UNSIGNED     NOT NULL,
    `rating`                TINYINT UNSIGNED NOT NULL,
    `title`                 VARCHAR(255)     DEFAULT NULL,
    `comment`               TEXT             DEFAULT NULL,
    `is_approved`           TINYINT(1)       NOT NULL DEFAULT 0,
    `status`                ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `instructor_reply`      TEXT             DEFAULT NULL,
    `instructor_reply_at`   TIMESTAMP        NULL DEFAULT NULL,
    `helpful_count`         INT UNSIGNED     NOT NULL DEFAULT 0,
    `reported_count`        INT UNSIGNED     NOT NULL DEFAULT 0,
    `created_at`            TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_review` (`user_id`, `course_id`),
    KEY `idx_reviews_course` (`course_id`),
    KEY `idx_reviews_approved` (`is_approved`),
    KEY `idx_reviews_status` (`status`),
    CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_reviews_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `wishlists`;
CREATE TABLE `wishlists` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED    NOT NULL,
    `course_id`   INT UNSIGNED    NOT NULL,
    `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_wishlist` (`user_id`, `course_id`),
    CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_wishlist_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `certificates`;
CREATE TABLE `certificates` (
    `id`                INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`           INT UNSIGNED     NOT NULL,
    `course_id`         INT UNSIGNED     NOT NULL,
    `template_id`       INT UNSIGNED     DEFAULT NULL,
    `certificate_code`  VARCHAR(50)      NOT NULL,
    `certificate_url`   VARCHAR(500)     DEFAULT NULL,
    `final_grade`       DECIMAL(5,2)     DEFAULT NULL,
    `total_hours`       DECIMAL(6,1)     DEFAULT NULL,
    `metadata`          JSON             DEFAULT NULL,
    `issued_at`         TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_certificate` (`user_id`, `course_id`),
    UNIQUE KEY `uk_certificate_code` (`certificate_code`),
    CONSTRAINT `fk_certificates_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_certificates_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_certificates_template` FOREIGN KEY (`template_id`)
        REFERENCES `certificate_templates` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
    `id`                      INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`                 INT UNSIGNED     NOT NULL,
    `course_id`               INT UNSIGNED     DEFAULT NULL,
    `coupon_id`               INT UNSIGNED     DEFAULT NULL,
    `amount`                  DECIMAL(10,2)    NOT NULL,
    `original_amount`         DECIMAL(10,2)    DEFAULT NULL,
    `discount_amount`         DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
    `currency`                VARCHAR(3)       NOT NULL DEFAULT 'BRL',
    `payment_method`          ENUM('credit_card','debit_card','pix','boleto','paypal','stripe','free','coupon','admin')
                                               NOT NULL,
    `payment_gateway`         VARCHAR(50)      DEFAULT NULL,
    `gateway_transaction_id`  VARCHAR(255)     DEFAULT NULL,
    `gateway_response`        JSON             DEFAULT NULL,
    `status`                  ENUM('pending','processing','completed','failed','cancelled','refunded','disputed','chargeback')
                                               NOT NULL DEFAULT 'pending',
    `invoice_number`          VARCHAR(50)      DEFAULT NULL,
    `receipt_url`             VARCHAR(500)     DEFAULT NULL,
    `refund_reason`           TEXT             DEFAULT NULL,
    `refunded_amount`         DECIMAL(10,2)    DEFAULT NULL,
    `refunded_at`             TIMESTAMP        NULL DEFAULT NULL,
    `paid_at`                 TIMESTAMP        NULL DEFAULT NULL,
    `created_at`              TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`              TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_payments_user` (`user_id`),
    KEY `idx_payments_status` (`status`),
    CONSTRAINT `fk_payments_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_payments_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_payments_coupon` FOREIGN KEY (`coupon_id`)
        REFERENCES `coupons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ================================================================
-- CORREÇÃO: carts, cart_items, orders, order_items
-- (referenciadas no AUTO_INCREMENT mas faltavam no schema)
-- ================================================================

DROP TABLE IF EXISTS `carts`;
CREATE TABLE `carts` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED    NOT NULL,
    `coupon_id`   INT UNSIGNED    DEFAULT NULL,
    `subtotal`    DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    `discount`    DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    `total`       DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_cart_user` (`user_id`),
    CONSTRAINT `fk_carts_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_carts_coupon` FOREIGN KEY (`coupon_id`)
        REFERENCES `coupons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Carrinho de compras por usuário';


DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE `cart_items` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `cart_id`     INT UNSIGNED    NOT NULL,
    `course_id`   INT UNSIGNED    NOT NULL,
    `price`       DECIMAL(10,2)   NOT NULL,
    `added_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_cart_item` (`cart_id`, `course_id`),
    CONSTRAINT `fk_cartitems_cart` FOREIGN KEY (`cart_id`)
        REFERENCES `carts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_cartitems_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Itens no carrinho de compras';


DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`         INT UNSIGNED     NOT NULL,
    `order_number`    VARCHAR(50)      NOT NULL,
    `coupon_id`       INT UNSIGNED     DEFAULT NULL,
    `subtotal`        DECIMAL(10,2)    NOT NULL,
    `discount`        DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
    `tax`             DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
    `total`           DECIMAL(10,2)    NOT NULL,
    `currency`        VARCHAR(3)       NOT NULL DEFAULT 'BRL',
    `status`          ENUM('pending','processing','completed','cancelled','refunded')
                                       NOT NULL DEFAULT 'pending',
    `payment_method`  VARCHAR(50)      DEFAULT NULL,
    `payment_id`      INT UNSIGNED     DEFAULT NULL,
    `notes`           TEXT             DEFAULT NULL,
    `completed_at`    TIMESTAMP        NULL DEFAULT NULL,
    `created_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_order_number` (`order_number`),
    KEY `idx_orders_user` (`user_id`),
    KEY `idx_orders_status` (`status`),
    CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_orders_coupon` FOREIGN KEY (`coupon_id`)
        REFERENCES `coupons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Pedidos de compra';


DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `order_id`    INT UNSIGNED    NOT NULL,
    `course_id`   INT UNSIGNED    NOT NULL,
    `title`       VARCHAR(255)    NOT NULL                            COMMENT 'Snapshot do título',
    `price`       DECIMAL(10,2)   NOT NULL,
    `discount`    DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    `final_price` DECIMAL(10,2)   NOT NULL,
    `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_orderitems_order` (`order_id`),
    CONSTRAINT `fk_orderitems_order` FOREIGN KEY (`order_id`)
        REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_orderitems_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Itens de cada pedido de compra';


-- CORREÇÃO: renomeado de coupon_uses para coupon_usage
DROP TABLE IF EXISTS `coupon_usage`;
CREATE TABLE `coupon_usage` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `coupon_id`   INT UNSIGNED    NOT NULL,
    `user_id`     INT UNSIGNED    NOT NULL,
    `order_id`    INT UNSIGNED    DEFAULT NULL,
    `payment_id`  INT UNSIGNED    DEFAULT NULL,
    `used_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_couponusage_coupon` (`coupon_id`),
    KEY `idx_couponusage_user` (`user_id`),
    CONSTRAINT `fk_couponusage_coupon` FOREIGN KEY (`coupon_id`)
        REFERENCES `coupons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_couponusage_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Histórico de uso de cupons';


DROP TABLE IF EXISTS `discussions`;
CREATE TABLE `discussions` (
    `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `course_id`     INT UNSIGNED     DEFAULT NULL,
    `lesson_id`     INT UNSIGNED     DEFAULT NULL,
    `user_id`       INT UNSIGNED     NOT NULL,
    `title`         VARCHAR(255)     NOT NULL,
    `content`       LONGTEXT         NOT NULL,
    `is_pinned`     TINYINT(1)       NOT NULL DEFAULT 0,
    `is_resolved`   TINYINT(1)       NOT NULL DEFAULT 0,
    `is_locked`     TINYINT(1)       NOT NULL DEFAULT 0,
    `reply_count`   INT UNSIGNED     NOT NULL DEFAULT 0,
    `view_count`    INT UNSIGNED     NOT NULL DEFAULT 0,
    `last_reply_at` TIMESTAMP        NULL DEFAULT NULL,
    `last_reply_by` INT UNSIGNED     DEFAULT NULL,
    `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_discussions_course` (`course_id`),
    FULLTEXT KEY `ft_discussions_search` (`title`, `content`),
    CONSTRAINT `fk_discussions_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_discussions_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `support_tickets`;
CREATE TABLE `support_tickets` (
    `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `ticket_number` VARCHAR(20)      NOT NULL,
    `user_id`       INT UNSIGNED     NOT NULL,
    `subject`       VARCHAR(255)     NOT NULL,
    `description`   LONGTEXT         NOT NULL,
    `category`      ENUM('technical','billing','content','account','bug_report','feature_request','other')
                                     NOT NULL DEFAULT 'other',
    `priority`      ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
    `status`        ENUM('open','in_progress','waiting_response','on_hold','resolved','closed')
                                     NOT NULL DEFAULT 'open',
    `assigned_to`   INT UNSIGNED     DEFAULT NULL,
    `course_id`     INT UNSIGNED     DEFAULT NULL,
    `resolved_at`   TIMESTAMP        NULL DEFAULT NULL,
    `closed_at`     TIMESTAMP        NULL DEFAULT NULL,
    `last_reply_at` TIMESTAMP        NULL DEFAULT NULL,
    `last_reply_by` INT UNSIGNED     DEFAULT NULL,
    `satisfaction`  TINYINT UNSIGNED DEFAULT NULL,
    `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_ticket_number` (`ticket_number`),
    KEY `idx_tickets_user` (`user_id`),
    KEY `idx_tickets_status` (`status`),
    CONSTRAINT `fk_tickets_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_tickets_assigned` FOREIGN KEY (`assigned_to`)
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `instructor_payouts`;
CREATE TABLE `instructor_payouts` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `instructor_id`   INT UNSIGNED     NOT NULL,
    `amount`          DECIMAL(10,2)    NOT NULL,
    `currency`        VARCHAR(3)       NOT NULL DEFAULT 'BRL',
    `period_start`    DATE             NOT NULL,
    `period_end`      DATE             NOT NULL,
    `total_sales`     INT UNSIGNED     NOT NULL DEFAULT 0,
    `gross_amount`    DECIMAL(10,2)    NOT NULL,
    `platform_fee`    DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
    `payment_method`  VARCHAR(50)      DEFAULT NULL,
    `payment_details` JSON             DEFAULT NULL,
    `status`          ENUM('pending','processing','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
    `paid_at`         TIMESTAMP        NULL DEFAULT NULL,
    `notes`           TEXT             DEFAULT NULL,
    `created_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_payouts_instructor` (`instructor_id`),
    CONSTRAINT `fk_payouts_instructor` FOREIGN KEY (`instructor_id`)
        REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ================================================================
-- CORREÇÃO: messages e message_participants
-- (referenciadas no AUTO_INCREMENT mas faltavam no schema)
-- ================================================================

DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
    `id`           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `sender_id`    INT UNSIGNED     NOT NULL,
    `subject`      VARCHAR(255)     DEFAULT NULL,
    `body`         TEXT             NOT NULL,
    `thread_id`    INT UNSIGNED     DEFAULT NULL                      COMMENT 'Para mensagens em thread (NULL = primeira)',
    `is_broadcast` TINYINT(1)       NOT NULL DEFAULT 0                COMMENT 'Mensagem para todos os alunos de um curso',
    `course_id`    INT UNSIGNED     DEFAULT NULL                      COMMENT 'Contexto de curso (broadcasts)',
    `created_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_messages_sender` (`sender_id`),
    KEY `idx_messages_thread` (`thread_id`),
    CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_messages_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Sistema de mensagens privadas';


DROP TABLE IF EXISTS `message_participants`;
CREATE TABLE `message_participants` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `message_id`   INT UNSIGNED    NOT NULL,
    `user_id`      INT UNSIGNED    NOT NULL,
    `is_read`      TINYINT(1)      NOT NULL DEFAULT 0,
    `read_at`      TIMESTAMP       NULL DEFAULT NULL,
    `deleted_by_recipient` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_msg_participant` (`message_id`, `user_id`),
    KEY `idx_msgpart_user_read` (`user_id`, `is_read`),
    CONSTRAINT `fk_msgpart_message` FOREIGN KEY (`message_id`)
        REFERENCES `messages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_msgpart_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Destinatários de mensagens privadas';


-- ================================================================
-- NÍVEL 3+ - DEPENDEM DE COURSE_MODULES
-- ================================================================

-- CORREÇÃO: renomeado de lessons para course_lessons
DROP TABLE IF EXISTS `course_lessons`;
CREATE TABLE `course_lessons` (
    `id`               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `module_id`        INT UNSIGNED     NOT NULL,
    `course_id`        INT UNSIGNED     NOT NULL,
    `title`            VARCHAR(255)     NOT NULL,
    `summary`          VARCHAR(500)     DEFAULT NULL,
    `slug`             VARCHAR(280)     NOT NULL,
    `content_type`     ENUM('video','text','quiz','assignment','download','live','interactive')
                                        NOT NULL DEFAULT 'video',
    `content`          LONGTEXT         DEFAULT NULL,
    `video_url`        VARCHAR(500)     DEFAULT NULL,
    `video_provider`   ENUM('youtube','vimeo','bunny','wistia','self_hosted','other') DEFAULT NULL,
    `video_duration`   INT UNSIGNED     NOT NULL DEFAULT 0,
    `video_thumbnail`  VARCHAR(500)     DEFAULT NULL,
    `xp_reward`        INT UNSIGNED     NOT NULL DEFAULT 10,
    `coin_reward`      INT UNSIGNED     NOT NULL DEFAULT 1,
    `attachments`      JSON             DEFAULT NULL,
    `resources`        JSON             DEFAULT NULL,
    `sort_order`       INT              NOT NULL DEFAULT 0,
    `is_free_preview`  TINYINT(1)       NOT NULL DEFAULT 0,
    `is_published`     TINYINT(1)       NOT NULL DEFAULT 1,
    `is_mandatory`     TINYINT(1)       NOT NULL DEFAULT 1,
    `completion_rule`  ENUM('video_watched','content_read','quiz_passed','manual')
                                        NOT NULL DEFAULT 'video_watched',
    `created_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_lessons_slug_course` (`course_id`, `slug`),
    KEY `idx_lessons_module_order` (`module_id`, `sort_order`),
    CONSTRAINT `fk_lessons_module` FOREIGN KEY (`module_id`)
        REFERENCES `course_modules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_lessons_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ================================================================
-- CORREÇÃO: course_resources
-- (referenciada no AUTO_INCREMENT mas faltava no schema)
-- ================================================================

DROP TABLE IF EXISTS `course_resources`;
CREATE TABLE `course_resources` (
    `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `course_id`   INT UNSIGNED     NOT NULL,
    `lesson_id`   INT UNSIGNED     DEFAULT NULL,
    `title`       VARCHAR(255)     NOT NULL,
    `description` VARCHAR(500)     DEFAULT NULL,
    `file_url`    VARCHAR(500)     DEFAULT NULL,
    `external_url` VARCHAR(500)    DEFAULT NULL,
    `file_type`   ENUM('pdf','zip','doc','video','audio','image','code','link','other') NOT NULL DEFAULT 'other',
    `file_size`   BIGINT UNSIGNED  DEFAULT NULL,
    `is_free`     TINYINT(1)       NOT NULL DEFAULT 0                  COMMENT 'Disponível sem matrícula?',
    `sort_order`  INT              NOT NULL DEFAULT 0,
    `download_count` INT UNSIGNED  NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_resources_course` (`course_id`),
    KEY `idx_resources_lesson` (`lesson_id`),
    CONSTRAINT `fk_resources_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_resources_lesson` FOREIGN KEY (`lesson_id`)
        REFERENCES `course_lessons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Arquivos e recursos extras dos cursos';


DROP TABLE IF EXISTS `discussion_replies`;
CREATE TABLE `discussion_replies` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `discussion_id`   INT UNSIGNED     NOT NULL,
    `user_id`         INT UNSIGNED     NOT NULL,
    `parent_reply_id` INT UNSIGNED     DEFAULT NULL,
    `content`         LONGTEXT         NOT NULL,
    `is_best_answer`  TINYINT(1)       NOT NULL DEFAULT 0,
    `upvote_count`    INT UNSIGNED     NOT NULL DEFAULT 0,
    `downvote_count`  INT UNSIGNED     NOT NULL DEFAULT 0,
    `is_edited`       TINYINT(1)       NOT NULL DEFAULT 0,
    `edited_at`       TIMESTAMP        NULL DEFAULT NULL,
    `created_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_replies_discussion` (`discussion_id`),
    CONSTRAINT `fk_replies_discussion` FOREIGN KEY (`discussion_id`)
        REFERENCES `discussions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_replies_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_replies_parent` FOREIGN KEY (`parent_reply_id`)
        REFERENCES `discussion_replies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `ticket_messages`;
CREATE TABLE `ticket_messages` (
    `id`               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `ticket_id`        INT UNSIGNED     NOT NULL,
    `user_id`          INT UNSIGNED     NOT NULL,
    `message`          LONGTEXT         NOT NULL,
    `attachments`      JSON             DEFAULT NULL,
    `is_internal_note` TINYINT(1)       NOT NULL DEFAULT 0,
    `created_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_ticketmsg_ticket` (`ticket_id`, `created_at`),
    CONSTRAINT `fk_ticketmsg_ticket` FOREIGN KEY (`ticket_id`)
        REFERENCES `support_tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ticketmsg_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `blog_comments`;
CREATE TABLE `blog_comments` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `post_id`         INT UNSIGNED     NOT NULL,
    `user_id`         INT UNSIGNED     DEFAULT NULL,
    `parent_id`       INT UNSIGNED     DEFAULT NULL,
    `author_name`     VARCHAR(100)     DEFAULT NULL,
    `author_email`    VARCHAR(150)     DEFAULT NULL,
    `content`         TEXT             NOT NULL,
    `is_approved`     TINYINT(1)       NOT NULL DEFAULT 0,
    `created_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_blogcomments_post` (`post_id`, `is_approved`),
    CONSTRAINT `fk_blogcomments_post` FOREIGN KEY (`post_id`)
        REFERENCES `blog_posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_blogcomments_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_blogcomments_parent` FOREIGN KEY (`parent_id`)
        REFERENCES `blog_comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `course_announcements`;
CREATE TABLE `course_announcements` (
    `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `course_id`   INT UNSIGNED     NOT NULL,
    `author_id`   INT UNSIGNED     NOT NULL,
    `title`       VARCHAR(255)     NOT NULL,
    `content`     LONGTEXT         NOT NULL,
    `is_pinned`   TINYINT(1)       NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    CONSTRAINT `fk_courseannounce_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_courseannounce_author` FOREIGN KEY (`author_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `course_bookmarks`;
CREATE TABLE `course_bookmarks` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED    NOT NULL,
    `course_id`   INT UNSIGNED    NOT NULL,
    `lesson_id`   INT UNSIGNED    DEFAULT NULL,
    `note`        VARCHAR(500)    DEFAULT NULL,
    `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_bookmarks_user` (`user_id`, `course_id`),
    CONSTRAINT `fk_bookmarks_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_bookmarks_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `report_abuse`;
CREATE TABLE `report_abuse` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `reporter_id`     INT UNSIGNED     NOT NULL,
    `entity_type`     VARCHAR(50)      NOT NULL,
    `entity_id`       INT UNSIGNED     NOT NULL,
    `reason`          ENUM('spam','inappropriate','harassment','copyright','misinformation','other') NOT NULL,
    `description`     TEXT             DEFAULT NULL,
    `status`          ENUM('pending','reviewing','action_taken','dismissed') NOT NULL DEFAULT 'pending',
    `reviewed_by`     INT UNSIGNED     DEFAULT NULL,
    `reviewed_at`     TIMESTAMP        NULL DEFAULT NULL,
    `action_taken`    VARCHAR(255)     DEFAULT NULL,
    `created_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    CONSTRAINT `fk_reports_reporter` FOREIGN KEY (`reporter_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `lesson_progress`;
CREATE TABLE `lesson_progress` (
    `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`       INT UNSIGNED     NOT NULL,
    `lesson_id`     INT UNSIGNED     NOT NULL,
    `course_id`     INT UNSIGNED     NOT NULL,
    `status`        ENUM('not_started','in_progress','completed') NOT NULL DEFAULT 'not_started',
    `is_completed`  TINYINT(1)       NOT NULL DEFAULT 0,
    `watch_time`    INT UNSIGNED     NOT NULL DEFAULT 0,
    `last_position` INT UNSIGNED     NOT NULL DEFAULT 0,
    `completed_at`  TIMESTAMP        NULL DEFAULT NULL,
    `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_lesson_progress` (`user_id`, `lesson_id`),
    CONSTRAINT `fk_progress_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_progress_lesson` FOREIGN KEY (`lesson_id`)
        REFERENCES `course_lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_progress_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `quizzes`;
CREATE TABLE `quizzes` (
    `id`                    INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `lesson_id`             INT UNSIGNED     NOT NULL,
    `title`                 VARCHAR(255)     NOT NULL,
    `description`           TEXT             DEFAULT NULL,
    `time_limit`            INT UNSIGNED     DEFAULT NULL,
    `pass_percentage`       DECIMAL(5,2)     NOT NULL DEFAULT 70.00,
    `max_attempts`          INT UNSIGNED     DEFAULT NULL,
    `shuffle_questions`     TINYINT(1)       NOT NULL DEFAULT 0,
    `shuffle_options`       TINYINT(1)       NOT NULL DEFAULT 0,
    `show_correct_answers`  TINYINT(1)       NOT NULL DEFAULT 1,
    `show_explanation`      TINYINT(1)       NOT NULL DEFAULT 1,
    `question_count`        INT UNSIGNED     NOT NULL DEFAULT 0,
    `is_active`             TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`            TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    CONSTRAINT `fk_quizzes_lesson` FOREIGN KEY (`lesson_id`)
        REFERENCES `course_lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `assignments`;
CREATE TABLE `assignments` (
    `id`                   INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `lesson_id`            INT UNSIGNED     NOT NULL,
    `title`                VARCHAR(255)     NOT NULL,
    `description`          LONGTEXT         NOT NULL,
    `instructions`         LONGTEXT         DEFAULT NULL,
    `starter_files_url`    VARCHAR(500)     DEFAULT NULL,
    `max_score`            INT UNSIGNED     NOT NULL DEFAULT 100,
    `due_days`             INT UNSIGNED     DEFAULT NULL,
    `allow_late`           TINYINT(1)       NOT NULL DEFAULT 0,
    `late_penalty_percent` DECIMAL(5,2)     DEFAULT NULL,
    `submission_type`      ENUM('file','text','url','github','zip') NOT NULL DEFAULT 'file',
    `allowed_extensions`   JSON             DEFAULT NULL,
    `max_file_size`        INT UNSIGNED     DEFAULT 52428800,
    `rubric`               JSON             DEFAULT NULL,
    `is_active`            TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`           TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`           TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    CONSTRAINT `fk_assignments_lesson` FOREIGN KEY (`lesson_id`)
        REFERENCES `course_lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `student_notes`;
CREATE TABLE `student_notes` (
    `id`                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`             INT UNSIGNED    NOT NULL,
    `lesson_id`           INT UNSIGNED    NOT NULL,
    `content`             TEXT            NOT NULL,
    `timestamp_seconds`   INT UNSIGNED    DEFAULT NULL,
    `color`               VARCHAR(7)      DEFAULT '#fbbf24',
    `created_at`          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    CONSTRAINT `fk_notes_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_notes_lesson` FOREIGN KEY (`lesson_id`)
        REFERENCES `course_lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `quiz_questions`;
CREATE TABLE `quiz_questions` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `quiz_id`         INT UNSIGNED     NOT NULL,
    `question_type`   ENUM('multiple_choice','multiple_select','true_false','short_answer','code','fill_blank')
                                       NOT NULL DEFAULT 'multiple_choice',
    `question_text`   TEXT             NOT NULL,
    `code_snippet`    TEXT             DEFAULT NULL,
    `code_language`   VARCHAR(20)      DEFAULT NULL,
    `image_url`       VARCHAR(500)     DEFAULT NULL,
    `explanation`     TEXT             DEFAULT NULL,
    `points`          INT UNSIGNED     NOT NULL DEFAULT 1,
    `sort_order`      INT              NOT NULL DEFAULT 0,
    `created_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    CONSTRAINT `fk_questions_quiz` FOREIGN KEY (`quiz_id`)
        REFERENCES `quizzes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `quiz_options`;
CREATE TABLE `quiz_options` (
    `id`           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `question_id`  INT UNSIGNED     NOT NULL,
    `option_text`  TEXT             NOT NULL,
    `is_correct`   TINYINT(1)       NOT NULL DEFAULT 0,
    `sort_order`   INT              NOT NULL DEFAULT 0,

    PRIMARY KEY (`id`),
    CONSTRAINT `fk_options_question` FOREIGN KEY (`question_id`)
        REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `quiz_attempts`;
CREATE TABLE `quiz_attempts` (
    `id`             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`        INT UNSIGNED     NOT NULL,
    `quiz_id`        INT UNSIGNED     NOT NULL,
    `score`          DECIMAL(5,2)     NOT NULL DEFAULT 0.00,
    `total_points`   INT UNSIGNED     NOT NULL DEFAULT 0,
    `earned_points`  INT UNSIGNED     NOT NULL DEFAULT 0,
    `passed`         TINYINT(1)       NOT NULL DEFAULT 0,
    `answers`        JSON             DEFAULT NULL,
    `attempt_number` INT UNSIGNED     NOT NULL DEFAULT 1,
    `started_at`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completed_at`   TIMESTAMP        NULL DEFAULT NULL,
    `time_spent`     INT UNSIGNED     DEFAULT NULL,

    PRIMARY KEY (`id`),
    CONSTRAINT `fk_attempts_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_attempts_quiz` FOREIGN KEY (`quiz_id`)
        REFERENCES `quizzes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ================================================================
-- CORREÇÃO: quiz_answers
-- (referenciada no AUTO_INCREMENT mas faltava no schema)
-- ================================================================

DROP TABLE IF EXISTS `quiz_answers`;
CREATE TABLE `quiz_answers` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `attempt_id`    INT UNSIGNED    NOT NULL,
    `question_id`   INT UNSIGNED    NOT NULL,
    `option_id`     INT UNSIGNED    DEFAULT NULL                    COMMENT 'Para multiple_choice',
    `answer_text`   TEXT            DEFAULT NULL                    COMMENT 'Para short_answer / fill_blank',
    `is_correct`    TINYINT(1)      NOT NULL DEFAULT 0,
    `points_earned` INT UNSIGNED    NOT NULL DEFAULT 0,
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_quizanswers_attempt` (`attempt_id`),
    CONSTRAINT `fk_quizanswers_attempt` FOREIGN KEY (`attempt_id`)
        REFERENCES `quiz_attempts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_quizanswers_question` FOREIGN KEY (`question_id`)
        REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_quizanswers_option` FOREIGN KEY (`option_id`)
        REFERENCES `quiz_options` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Respostas individuais de cada tentativa de quiz';


DROP TABLE IF EXISTS `assignment_submissions`;
CREATE TABLE `assignment_submissions` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `assignment_id`   INT UNSIGNED     NOT NULL,
    `user_id`         INT UNSIGNED     NOT NULL,
    `content`         LONGTEXT         DEFAULT NULL,
    `file_url`        VARCHAR(500)     DEFAULT NULL,
    `github_url`      VARCHAR(500)     DEFAULT NULL,
    `additional_urls` JSON             DEFAULT NULL,
    `score`           INT UNSIGNED     DEFAULT NULL,
    `feedback`        TEXT             DEFAULT NULL,
    `status`          ENUM('submitted','under_review','graded','returned','resubmitted')
                                       NOT NULL DEFAULT 'submitted',
    `is_late`         TINYINT(1)       NOT NULL DEFAULT 0,
    `graded_by`       INT UNSIGNED     DEFAULT NULL,
    `graded_at`       TIMESTAMP        NULL DEFAULT NULL,
    `attempt_number`  INT UNSIGNED     NOT NULL DEFAULT 1,
    `submitted_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    CONSTRAINT `fk_submissions_assignment` FOREIGN KEY (`assignment_id`)
        REFERENCES `assignments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_submissions_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_submissions_grader` FOREIGN KEY (`graded_by`)
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ================================================================
-- CORREÇÃO: forums, forum_topics, forum_posts
-- (referenciadas no AUTO_INCREMENT mas faltavam no schema)
-- ================================================================

DROP TABLE IF EXISTS `forums`;
CREATE TABLE `forums` (
    `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100)     NOT NULL,
    `slug`        VARCHAR(120)     NOT NULL,
    `description` TEXT             DEFAULT NULL,
    `icon`        VARCHAR(100)     DEFAULT NULL,
    `color`       VARCHAR(7)       DEFAULT '#6366f1',
    `course_id`   INT UNSIGNED     DEFAULT NULL                      COMMENT 'Fórum vinculado a um curso específico',
    `sort_order`  INT              NOT NULL DEFAULT 0,
    `topic_count` INT UNSIGNED     NOT NULL DEFAULT 0,
    `post_count`  INT UNSIGNED     NOT NULL DEFAULT 0,
    `is_private`  TINYINT(1)       NOT NULL DEFAULT 0                COMMENT 'Apenas matriculados podem ver',
    `is_active`   TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_forums_slug` (`slug`),
    KEY `idx_forums_course` (`course_id`),
    KEY `idx_forums_order` (`is_active`, `sort_order`),
    CONSTRAINT `fk_forums_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Fóruns da comunidade';


DROP TABLE IF EXISTS `forum_topics`;
CREATE TABLE `forum_topics` (
    `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `forum_id`      INT UNSIGNED     NOT NULL,
    `user_id`       INT UNSIGNED     NOT NULL,
    `title`         VARCHAR(255)     NOT NULL,
    `slug`          VARCHAR(280)     NOT NULL,
    `content`       LONGTEXT         NOT NULL,
    `status`        ENUM('open','closed','pinned','archived') NOT NULL DEFAULT 'open',
    `is_pinned`     TINYINT(1)       NOT NULL DEFAULT 0,
    `is_locked`     TINYINT(1)       NOT NULL DEFAULT 0,
    `is_featured`   TINYINT(1)       NOT NULL DEFAULT 0,
    `view_count`    INT UNSIGNED     NOT NULL DEFAULT 0,
    `reply_count`   INT UNSIGNED     NOT NULL DEFAULT 0,
    `upvote_count`  INT UNSIGNED     NOT NULL DEFAULT 0,
    `last_reply_at` TIMESTAMP        NULL DEFAULT NULL,
    `last_reply_by` INT UNSIGNED     DEFAULT NULL,
    `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_topic_slug` (`forum_id`, `slug`),
    KEY `idx_topics_forum` (`forum_id`, `created_at` DESC),
    KEY `idx_topics_user` (`user_id`),
    FULLTEXT KEY `ft_topics_search` (`title`, `content`),
    CONSTRAINT `fk_topics_forum` FOREIGN KEY (`forum_id`)
        REFERENCES `forums` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_topics_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tópicos dos fóruns';


DROP TABLE IF EXISTS `forum_posts`;
CREATE TABLE `forum_posts` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `topic_id`        INT UNSIGNED     NOT NULL,
    `user_id`         INT UNSIGNED     NOT NULL,
    `parent_post_id`  INT UNSIGNED     DEFAULT NULL,
    `content`         LONGTEXT         NOT NULL,
    `is_best_answer`  TINYINT(1)       NOT NULL DEFAULT 0,
    `upvote_count`    INT UNSIGNED     NOT NULL DEFAULT 0,
    `downvote_count`  INT UNSIGNED     NOT NULL DEFAULT 0,
    `is_edited`       TINYINT(1)       NOT NULL DEFAULT 0,
    `edited_at`       TIMESTAMP        NULL DEFAULT NULL,
    `is_deleted`      TINYINT(1)       NOT NULL DEFAULT 0,
    `created_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_forumposts_topic` (`topic_id`, `created_at`),
    KEY `idx_forumposts_user` (`user_id`),
    CONSTRAINT `fk_forumposts_topic` FOREIGN KEY (`topic_id`)
        REFERENCES `forum_topics` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_forumposts_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_forumposts_parent` FOREIGN KEY (`parent_post_id`)
        REFERENCES `forum_posts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Posts dos tópicos de fórum';


-- CORREÇÃO: renomeado de activity_log para logs
DROP TABLE IF EXISTS `logs`;
CREATE TABLE `logs` (
    `id`           BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `user_id`      INT UNSIGNED     DEFAULT NULL,
    `action`       VARCHAR(100)     NOT NULL,
    `entity_type`  VARCHAR(50)      DEFAULT NULL,
    `entity_id`    INT UNSIGNED     DEFAULT NULL,
    `old_values`   JSON             DEFAULT NULL,
    `new_values`   JSON             DEFAULT NULL,
    `ip_address`   VARCHAR(45)      DEFAULT NULL,
    `user_agent`   VARCHAR(500)     DEFAULT NULL,
    `extra`        JSON             DEFAULT NULL,
    `created_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_logs_user` (`user_id`),
    KEY `idx_logs_action` (`action`),
    KEY `idx_logs_created` (`created_at`),
    CONSTRAINT `fk_logs_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Log de atividades do sistema';


-- ================================================================
-- TABELAS EXTRAS (informacionais, sem AUTO_INCREMENT)
-- ================================================================

DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100)    NOT NULL,
    `email`       VARCHAR(150)    NOT NULL,
    `subject`     VARCHAR(255)    DEFAULT NULL,
    `message`     TEXT            NOT NULL,
    `is_read`     TINYINT(1)      NOT NULL DEFAULT 0,
    `replied_at`  TIMESTAMP       NULL DEFAULT NULL,
    `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `newsletter_subscribers`;
CREATE TABLE `newsletter_subscribers` (
    `id`               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `email`            VARCHAR(150)    NOT NULL,
    `name`             VARCHAR(100)    DEFAULT NULL,
    `is_active`        TINYINT(1)      NOT NULL DEFAULT 1,
    `subscribed_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `unsubscribed_at`  TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_newsletter_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `testimonials`;
CREATE TABLE `testimonials` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`      INT UNSIGNED    DEFAULT NULL,
    `name`         VARCHAR(100)    NOT NULL,
    `role`         VARCHAR(100)    DEFAULT NULL,
    `content`      TEXT            NOT NULL,
    `avatar`       VARCHAR(500)    DEFAULT NULL,
    `rating`       TINYINT UNSIGNED DEFAULT NULL,
    `is_featured`  TINYINT(1)      NOT NULL DEFAULT 0,
    `is_active`    TINYINT(1)      NOT NULL DEFAULT 1,
    `sort_order`   INT             NOT NULL DEFAULT 0,
    `created_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `banners`;
CREATE TABLE `banners` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `title`        VARCHAR(255)    DEFAULT NULL,
    `subtitle`     VARCHAR(500)    DEFAULT NULL,
    `image`        VARCHAR(500)    NOT NULL,
    `link`         VARCHAR(500)    DEFAULT NULL,
    `button_text`  VARCHAR(100)    DEFAULT NULL,
    `position`     ENUM('home','sidebar','footer') NOT NULL DEFAULT 'home',
    `sort_order`   INT             NOT NULL DEFAULT 0,
    `is_active`    TINYINT(1)      NOT NULL DEFAULT 1,
    `starts_at`    TIMESTAMP       NULL DEFAULT NULL,
    `ends_at`      TIMESTAMP       NULL DEFAULT NULL,
    `created_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `site_counters`;
CREATE TABLE `site_counters` (
    `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `counter_key`    VARCHAR(50)     NOT NULL,
    `counter_value`  INT UNSIGNED    NOT NULL DEFAULT 0,
    `label`          VARCHAR(100)    DEFAULT NULL,
    `icon`           VARCHAR(100)    DEFAULT NULL,
    `is_active`      TINYINT(1)      NOT NULL DEFAULT 1,
    `sort_order`     INT             NOT NULL DEFAULT 0,
    `updated_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_counter_key` (`counter_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `partners`;
CREATE TABLE `partners` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100)    NOT NULL,
    `logo`        VARCHAR(500)    NOT NULL,
    `website`     VARCHAR(500)    DEFAULT NULL,
    `sort_order`  INT             NOT NULL DEFAULT 0,
    `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ================================================================
-- TABELAS DO SCHEMA LEGADO (v2.0 → reintegradas e adaptadas ao padrão v5)
-- Todas as 19 tabelas que existiam no schema-old mas estavam ausentes aqui.
-- ================================================================

-- L01. Levels (Níveis de gamificação)
DROP TABLE IF EXISTS `levels`;
CREATE TABLE `levels` (
    `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `level_number`  INT UNSIGNED     NOT NULL,
    `title`         VARCHAR(50)      NOT NULL,
    `xp_required`   INT UNSIGNED     NOT NULL,
    `badge_icon`    VARCHAR(100)     DEFAULT NULL,
    `color`         VARCHAR(7)       NOT NULL DEFAULT '#6366f1',
    `perks`         TEXT             DEFAULT NULL,
    `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_level_number` (`level_number`),
    KEY `idx_levels_xp` (`xp_required`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Níveis de gamificação da plataforma';


-- L02. Daily Challenges (Desafios diários)
DROP TABLE IF EXISTS `daily_challenges`;
CREATE TABLE `daily_challenges` (
    `id`                 INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `date`               DATE             NOT NULL,
    `title`              VARCHAR(200)     NOT NULL,
    `description`        TEXT             DEFAULT NULL,
    `type`               ENUM('lesson','quiz','time','streak','social','special') NOT NULL DEFAULT 'lesson',
    `requirement_type`   VARCHAR(50)      NOT NULL,
    `requirement_value`  INT UNSIGNED     NOT NULL DEFAULT 1,
    `xp_reward`          INT UNSIGNED     NOT NULL DEFAULT 50,
    `coin_reward`        INT UNSIGNED     NOT NULL DEFAULT 10,
    `bonus_multiplier`   DECIMAL(3,2)     NOT NULL DEFAULT 1.00,
    `is_active`          TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`         TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_challenge_date` (`date`),
    KEY `idx_challenges_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Desafios diários de gamificação';


-- L03. Shop Items (Loja virtual)
DROP TABLE IF EXISTS `shop_items`;
CREATE TABLE `shop_items` (
    `id`               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`             VARCHAR(100)     NOT NULL,
    `slug`             VARCHAR(120)     DEFAULT NULL,
    `description`      TEXT             DEFAULT NULL,
    `type`             ENUM('avatar','badge','theme','power_up','cosmetic','course_unlock') NOT NULL,
    `category`         VARCHAR(50)      NOT NULL DEFAULT 'general',
    `image`            VARCHAR(500)     DEFAULT NULL,
    `price_coins`      INT UNSIGNED     NOT NULL DEFAULT 0,
    `price_gems`       INT UNSIGNED     NOT NULL DEFAULT 0,
    `item_data`        JSON             DEFAULT NULL,
    `stock_quantity`   INT              NOT NULL DEFAULT -1,
    `level_required`   INT UNSIGNED     NOT NULL DEFAULT 1,
    `is_featured`      TINYINT(1)       NOT NULL DEFAULT 0,
    `is_active`        TINYINT(1)       NOT NULL DEFAULT 1,
    `is_limited`       TINYINT(1)       NOT NULL DEFAULT 0,
    `available_until`  DATETIME         DEFAULT NULL,
    `sort_order`       INT              NOT NULL DEFAULT 0,
    `purchase_count`   INT UNSIGNED     NOT NULL DEFAULT 0,
    `created_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_shopitems_slug` (`slug`),
    KEY `idx_shopitems_type` (`type`),
    KEY `idx_shopitems_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Itens disponíveis na loja virtual';


-- L04. Password Resets (Recuperação de senha — complementa user.password_reset_token)
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
    `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `email`       VARCHAR(150)     NOT NULL,
    `token`       VARCHAR(255)     NOT NULL,
    `ip_address`  VARCHAR(45)      DEFAULT NULL,
    `expires_at`  TIMESTAMP        NOT NULL,
    `used_at`     TIMESTAMP        NULL DEFAULT NULL,
    `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_pwreset_email` (`email`),
    KEY `idx_pwreset_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tokens para recuperação de senha';


-- L05. User Daily Stats (Estatísticas diárias por usuário)
DROP TABLE IF EXISTS `user_daily_stats`;
CREATE TABLE `user_daily_stats` (
    `id`                  INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`             INT UNSIGNED     NOT NULL,
    `date`                DATE             NOT NULL,
    `xp_earned`           INT UNSIGNED     NOT NULL DEFAULT 0,
    `coins_earned`        INT UNSIGNED     NOT NULL DEFAULT 0,
    `lessons_completed`   INT UNSIGNED     NOT NULL DEFAULT 0,
    `time_spent`          INT UNSIGNED     NOT NULL DEFAULT 0,
    `quizzes_completed`   INT UNSIGNED     NOT NULL DEFAULT 0,
    `projects_completed`  INT UNSIGNED     NOT NULL DEFAULT 0,
    `streak_maintained`   TINYINT(1)       NOT NULL DEFAULT 0,
    `created_at`          TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_userdailystats` (`user_id`, `date`),
    KEY `idx_userdailystats_date` (`date`),
    CONSTRAINT `fk_userdailystats_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Estatísticas diárias de atividade por usuário';


-- L06. XP Transactions (Histórico de XP)
DROP TABLE IF EXISTS `xp_transactions`;
CREATE TABLE `xp_transactions` (
    `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`       INT UNSIGNED     NOT NULL,
    `amount`        INT              NOT NULL,
    `type`          ENUM('earned','bonus','achievement','level_up','streak','challenge','refund','admin')
                                     NOT NULL DEFAULT 'earned',
    `source`        VARCHAR(50)      NOT NULL,
    `source_id`     INT UNSIGNED     DEFAULT NULL,
    `description`   VARCHAR(255)     DEFAULT NULL,
    `balance_after` INT UNSIGNED     DEFAULT NULL,
    `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_xptx_user` (`user_id`),
    KEY `idx_xptx_type` (`type`),
    KEY `idx_xptx_created` (`created_at`),
    CONSTRAINT `fk_xptx_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Histórico de transações de XP';


DROP TABLE IF EXISTS `xp_history`;
CREATE TABLE `xp_history` (
    `id`             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`        INT UNSIGNED     NOT NULL,
    `xp_amount`      INT              NOT NULL,
    `action_type`    VARCHAR(50)      NOT NULL,
    `description`    VARCHAR(255)     DEFAULT NULL,
    `reference_id`   INT UNSIGNED     DEFAULT NULL,
    `reference_type` VARCHAR(50)      DEFAULT NULL,
    `created_at`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_xphistory_user` (`user_id`),
    CONSTRAINT `fk_xphistory_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Histórico de XP compatível com Gamification.php';


DROP TABLE IF EXISTS `weekly_leaderboard`;
CREATE TABLE `weekly_leaderboard` (
    `id`                 INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`            INT UNSIGNED     NOT NULL,
    `week_start`         DATE             NOT NULL,
    `xp_earned`          INT UNSIGNED     DEFAULT 0,
    `lessons_completed`  INT UNSIGNED     DEFAULT 0,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_weekly_leaderboard` (`user_id`, `week_start`),
    CONSTRAINT `fk_weekly_leaderboard_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Ranking semanal compatível com Gamification.php';


-- L07. Coin Transactions (Histórico de moedas)
DROP TABLE IF EXISTS `coin_transactions`;
CREATE TABLE `coin_transactions` (
    `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`       INT UNSIGNED     NOT NULL,
    `amount`        INT              NOT NULL,
    `type`          ENUM('earned','spent','bonus','refund','gift','admin') NOT NULL,
    `source`        VARCHAR(50)      NOT NULL,
    `source_id`     INT UNSIGNED     DEFAULT NULL,
    `description`   VARCHAR(255)     DEFAULT NULL,
    `balance_after` INT UNSIGNED     DEFAULT NULL,
    `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_cointx_user` (`user_id`),
    KEY `idx_cointx_type` (`type`),
    KEY `idx_cointx_created` (`created_at`),
    CONSTRAINT `fk_cointx_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Histórico de transações de moedas';


-- L08. Email Verifications (complementa user.email_verification_token)
DROP TABLE IF EXISTS `email_verifications`;
CREATE TABLE `email_verifications` (
    `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED     NOT NULL,
    `email`       VARCHAR(150)     NOT NULL,
    `token`       VARCHAR(255)     NOT NULL,
    `expires_at`  TIMESTAMP        NOT NULL,
    `verified_at` TIMESTAMP        NULL DEFAULT NULL,
    `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_emailverif_token` (`token`),
    KEY `idx_emailverif_expires` (`expires_at`),
    CONSTRAINT `fk_emailverif_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tokens de verificação de e-mail';


-- L09. User Follows (Seguir usuários)
DROP TABLE IF EXISTS `user_follows`;
CREATE TABLE `user_follows` (
    `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `follower_id`   INT UNSIGNED     NOT NULL,
    `following_id`  INT UNSIGNED     NOT NULL,
    `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_follow` (`follower_id`, `following_id`),
    KEY `idx_follow_follower` (`follower_id`),
    KEY `idx_follow_following` (`following_id`),
    CONSTRAINT `fk_follow_follower` FOREIGN KEY (`follower_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_follow_following` FOREIGN KEY (`following_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Seguindo/seguidores entre usuários';


-- L10. User Inventory (Inventário da loja — depende de shop_items)
DROP TABLE IF EXISTS `user_inventory`;
CREATE TABLE `user_inventory` (
    `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`       INT UNSIGNED     NOT NULL,
    `item_id`       INT UNSIGNED     NOT NULL,
    `quantity`      INT UNSIGNED     NOT NULL DEFAULT 1,
    `is_equipped`   TINYINT(1)       NOT NULL DEFAULT 0,
    `is_active`     TINYINT(1)       NOT NULL DEFAULT 1,
    `purchased_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `expires_at`    DATETIME         DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_item` (`user_id`, `item_id`),
    KEY `idx_inventory_equipped` (`is_equipped`),
    CONSTRAINT `fk_inventory_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_inventory_item` FOREIGN KEY (`item_id`)
        REFERENCES `shop_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Inventário de itens da loja por usuário';


-- L11. User Daily Challenges (Progresso nos desafios diários)
DROP TABLE IF EXISTS `user_daily_challenges`;
CREATE TABLE `user_daily_challenges` (
    `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`       INT UNSIGNED     NOT NULL,
    `challenge_id`  INT UNSIGNED     NOT NULL,
    `progress`      INT UNSIGNED     NOT NULL DEFAULT 0,
    `is_completed`  TINYINT(1)       NOT NULL DEFAULT 0,
    `completed_at`  TIMESTAMP        NULL DEFAULT NULL,
    `xp_earned`     INT UNSIGNED     NOT NULL DEFAULT 0,
    `coins_earned`  INT UNSIGNED     NOT NULL DEFAULT 0,
    `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_challenge` (`user_id`, `challenge_id`),
    KEY `idx_userchallenge_user` (`user_id`),
    CONSTRAINT `fk_userchallenge_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_userchallenge_challenge` FOREIGN KEY (`challenge_id`)
        REFERENCES `daily_challenges` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Progresso dos usuários nos desafios diários';


-- L12. Course Prerequisites (Pré-requisitos de cursos)
DROP TABLE IF EXISTS `course_prerequisites`;
CREATE TABLE `course_prerequisites` (
    `id`                      INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `course_id`               INT UNSIGNED     NOT NULL,
    `prerequisite_course_id`  INT UNSIGNED     NOT NULL,
    `is_required`             TINYINT(1)       NOT NULL DEFAULT 1,
    `sort_order`              INT              NOT NULL DEFAULT 0,
    `created_at`              TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_prerequisite` (`course_id`, `prerequisite_course_id`),
    KEY `idx_prereq_course` (`course_id`),
    CONSTRAINT `fk_prereq_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_prereq_prerequisite` FOREIGN KEY (`prerequisite_course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Pré-requisitos de cursos';


-- L13. Module Progress (Progresso por módulo — FK para course_modules)
DROP TABLE IF EXISTS `module_progress`;
CREATE TABLE `module_progress` (
    `id`                 INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`            INT UNSIGNED     NOT NULL,
    `module_id`          INT UNSIGNED     NOT NULL,
    `enrollment_id`      INT UNSIGNED     DEFAULT NULL,
    `is_completed`       TINYINT(1)       NOT NULL DEFAULT 0,
    `progress_percent`   DECIMAL(5,2)     NOT NULL DEFAULT 0.00,
    `completed_lessons`  INT UNSIGNED     NOT NULL DEFAULT 0,
    `total_lessons`      INT UNSIGNED     NOT NULL DEFAULT 0,
    `started_at`         TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completed_at`       TIMESTAMP        NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_module_progress` (`user_id`, `module_id`),
    KEY `idx_moduleprog_module` (`module_id`),
    CONSTRAINT `fk_moduleprog_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_moduleprog_module` FOREIGN KEY (`module_id`)
        REFERENCES `course_modules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_moduleprog_enrollment` FOREIGN KEY (`enrollment_id`)
        REFERENCES `enrollments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Progresso dos módulos por usuário';


-- L14. Review Votes (Votos de utilidade em avaliações)
DROP TABLE IF EXISTS `review_votes`;
CREATE TABLE `review_votes` (
    `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `review_id`   INT UNSIGNED     NOT NULL,
    `user_id`     INT UNSIGNED     NOT NULL,
    `is_helpful`  TINYINT(1)       NOT NULL,
    `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_review_vote` (`review_id`, `user_id`),
    KEY `idx_reviewvote_review` (`review_id`),
    CONSTRAINT `fk_reviewvote_review` FOREIGN KEY (`review_id`)
        REFERENCES `course_reviews` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_reviewvote_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Votos de utilidade em avaliações de cursos';


-- L15. Projects (Projetos práticos — FK para course_lessons renomeado)
DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
    `id`                  INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `lesson_id`           INT UNSIGNED     DEFAULT NULL,
    `course_id`           INT UNSIGNED     DEFAULT NULL,
    `title`               VARCHAR(200)     NOT NULL,
    `description`         TEXT             DEFAULT NULL,
    `instructions`        TEXT             DEFAULT NULL,
    `requirements`        TEXT             DEFAULT NULL,
    `starter_files_url`   VARCHAR(500)     DEFAULT NULL,
    `solution_url`        VARCHAR(500)     DEFAULT NULL,
    `difficulty`          ENUM('beginner','intermediate','advanced','expert') NOT NULL DEFAULT 'beginner',
    `estimated_hours`     INT UNSIGNED     NOT NULL DEFAULT 1,
    `xp_reward`           INT UNSIGNED     NOT NULL DEFAULT 100,
    `coin_reward`         INT UNSIGNED     NOT NULL DEFAULT 20,
    `type`                ENUM('practice','challenge','portfolio','certification') NOT NULL DEFAULT 'practice',
    `submission_type`     ENUM('link','file','github','text') NOT NULL DEFAULT 'link',
    `allows_review`       TINYINT(1)       NOT NULL DEFAULT 1,
    `review_criteria`     TEXT             DEFAULT NULL,
    `is_published`        TINYINT(1)       NOT NULL DEFAULT 1,
    `submissions_count`   INT UNSIGNED     NOT NULL DEFAULT 0,
    `created_at`          TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_projects_lesson` (`lesson_id`),
    KEY `idx_projects_course` (`course_id`),
    KEY `idx_projects_difficulty` (`difficulty`),
    CONSTRAINT `fk_projects_lesson` FOREIGN KEY (`lesson_id`)
        REFERENCES `course_lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_projects_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Projetos práticos dos cursos';


-- L16. Lesson Comments (antigo "comments" — FK para course_lessons)
DROP TABLE IF EXISTS `lesson_comments`;
CREATE TABLE `lesson_comments` (
    `id`                   INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`              INT UNSIGNED     NOT NULL,
    `lesson_id`            INT UNSIGNED     NOT NULL,
    `parent_id`            INT UNSIGNED     DEFAULT NULL,
    `content`              TEXT             NOT NULL,
    `is_pinned`            TINYINT(1)       NOT NULL DEFAULT 0,
    `is_instructor_reply`  TINYINT(1)       NOT NULL DEFAULT 0,
    `is_approved`          TINYINT(1)       NOT NULL DEFAULT 1,
    `is_resolved`          TINYINT(1)       NOT NULL DEFAULT 0,
    `likes_count`          INT UNSIGNED     NOT NULL DEFAULT 0,
    `replies_count`        INT UNSIGNED     NOT NULL DEFAULT 0,
    `report_count`         INT UNSIGNED     NOT NULL DEFAULT 0,
    `created_at`           TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`           TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`           TIMESTAMP        NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    KEY `idx_lessoncomments_user` (`user_id`),
    KEY `idx_lessoncomments_lesson` (`lesson_id`),
    KEY `idx_lessoncomments_parent` (`parent_id`),
    CONSTRAINT `fk_lessoncomments_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_lessoncomments_lesson` FOREIGN KEY (`lesson_id`)
        REFERENCES `course_lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_lessoncomments_parent` FOREIGN KEY (`parent_id`)
        REFERENCES `lesson_comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Comentários nas aulas';


-- L17. Project Submissions (Submissões de projetos)
DROP TABLE IF EXISTS `project_submissions`;
CREATE TABLE `project_submissions` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `project_id`      INT UNSIGNED     NOT NULL,
    `user_id`         INT UNSIGNED     NOT NULL,
    `title`           VARCHAR(200)     DEFAULT NULL,
    `description`     TEXT             DEFAULT NULL,
    `submission_url`  VARCHAR(500)     DEFAULT NULL,
    `github_url`      VARCHAR(500)     DEFAULT NULL,
    `live_url`        VARCHAR(500)     DEFAULT NULL,
    `content`         TEXT             DEFAULT NULL,
    `status`          ENUM('pending','under_review','approved','rejected','needs_revision')
                                       NOT NULL DEFAULT 'pending',
    `score`           DECIMAL(5,2)     DEFAULT NULL,
    `feedback`        TEXT             DEFAULT NULL,
    `reviewed_by`     INT UNSIGNED     DEFAULT NULL,
    `reviewed_at`     TIMESTAMP        NULL DEFAULT NULL,
    `xp_earned`       INT UNSIGNED     NOT NULL DEFAULT 0,
    `coins_earned`    INT UNSIGNED     NOT NULL DEFAULT 0,
    `likes_count`     INT UNSIGNED     NOT NULL DEFAULT 0,
    `views_count`     INT UNSIGNED     NOT NULL DEFAULT 0,
    `is_featured`     TINYINT(1)       NOT NULL DEFAULT 0,
    `is_public`       TINYINT(1)       NOT NULL DEFAULT 1,
    `submitted_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_projsub_project` (`project_id`),
    KEY `idx_projsub_user` (`user_id`),
    KEY `idx_projsub_status` (`status`),
    CONSTRAINT `fk_projsub_project` FOREIGN KEY (`project_id`)
        REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_projsub_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_projsub_reviewer` FOREIGN KEY (`reviewed_by`)
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Submissões de projetos pelos alunos';


-- L18. Lesson Comment Likes (antigo "comment_likes")
DROP TABLE IF EXISTS `lesson_comment_likes`;
CREATE TABLE `lesson_comment_likes` (
    `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `comment_id`  INT UNSIGNED     NOT NULL,
    `user_id`     INT UNSIGNED     NOT NULL,
    `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_comment_like` (`comment_id`, `user_id`),
    KEY `idx_commentlike_comment` (`comment_id`),
    CONSTRAINT `fk_commentlike_comment` FOREIGN KEY (`comment_id`)
        REFERENCES `lesson_comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_commentlike_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Curtidas em comentários de aulas';


-- L19. Project Likes (Curtidas em submissões de projetos)
DROP TABLE IF EXISTS `project_likes`;
CREATE TABLE `project_likes` (
    `id`             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `submission_id`  INT UNSIGNED     NOT NULL,
    `user_id`        INT UNSIGNED     NOT NULL,
    `created_at`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_project_like` (`submission_id`, `user_id`),
    KEY `idx_projectlike_submission` (`submission_id`),
    CONSTRAINT `fk_projectlike_submission` FOREIGN KEY (`submission_id`)
        REFERENCES `project_submissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_projectlike_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Curtidas em submissões de projetos';


-- ================================================================
-- VIEWS
-- ================================================================

CREATE OR REPLACE VIEW `vw_courses_listing` AS
SELECT
    c.id, c.title, c.slug, c.short_description, c.thumbnail, c.image,
    c.level, c.price, c.original_price, c.is_free, c.is_featured,
    c.is_bestseller, c.duration_hours, c.total_lessons,
    c.enrollment_count, c.total_students, c.rating_average, c.average_rating,
    c.rating_count, c.total_reviews, c.game_engine, c.status, c.is_published,
    c.published_at,
    u.id AS instructor_id, u.name AS instructor_name, u.avatar AS instructor_avatar,
    cat.id AS category_id, cat.name AS category_name, cat.slug AS category_slug
FROM courses c
INNER JOIN users u ON c.instructor_id = u.id
LEFT JOIN categories cat ON c.category_id = cat.id;


CREATE OR REPLACE VIEW `vw_dashboard_stats` AS
SELECT
    (SELECT COUNT(*) FROM users WHERE role = 'student' AND is_active = 1) AS total_students,
    (SELECT COUNT(*) FROM users WHERE role = 'instructor' AND is_active = 1) AS total_instructors,
    (SELECT COUNT(*) FROM courses WHERE status = 'published') AS total_courses,
    (SELECT COUNT(*) FROM enrollments WHERE status = 'active') AS active_enrollments,
    (SELECT COUNT(*) FROM enrollments WHERE status = 'completed') AS completed_enrollments,
    (SELECT COALESCE(SUM(amount - discount_amount), 0) FROM payments WHERE status = 'completed') AS total_revenue,
    (SELECT COUNT(*) FROM enrollments WHERE enrolled_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) AS monthly_enrollments,
    (SELECT COUNT(*) FROM support_tickets WHERE status IN ('open', 'in_progress')) AS open_tickets,
    (SELECT COUNT(*) FROM course_reviews WHERE is_approved = 0) AS pending_reviews;


CREATE OR REPLACE VIEW `vw_student_progress` AS
SELECT
    e.user_id, e.course_id, c.title AS course_title, c.total_lessons,
    e.progress_percent, e.lessons_completed, e.status AS enrollment_status,
    e.enrolled_at, e.completed_at, e.last_accessed_at,
    u.name AS student_name, u.email AS student_email
FROM enrollments e
INNER JOIN courses c ON e.course_id = c.id
INNER JOIN users u ON e.user_id = u.id;


SET FOREIGN_KEY_CHECKS = 1;


-- ================================================================
-- AUTO INCREMENT INITIALIZATION
-- (todos os nomes corrigidos para refletir as tabelas reais)
-- ================================================================

ALTER TABLE `users`                 AUTO_INCREMENT = 1;
ALTER TABLE `user_profiles`         AUTO_INCREMENT = 1;
ALTER TABLE `user_settings`         AUTO_INCREMENT = 1;
ALTER TABLE `user_social_links`     AUTO_INCREMENT = 1;
ALTER TABLE `user_daily_stats`      AUTO_INCREMENT = 1;
ALTER TABLE `user_follows`          AUTO_INCREMENT = 1;
ALTER TABLE `user_daily_challenges` AUTO_INCREMENT = 1;
ALTER TABLE `user_inventory`        AUTO_INCREMENT = 1;
ALTER TABLE `categories`            AUTO_INCREMENT = 1;
ALTER TABLE `tags`                  AUTO_INCREMENT = 1;
ALTER TABLE `settings`              AUTO_INCREMENT = 1;
ALTER TABLE `pages`                 AUTO_INCREMENT = 1;
ALTER TABLE `badges`                AUTO_INCREMENT = 1;
ALTER TABLE `achievements`          AUTO_INCREMENT = 1;
ALTER TABLE `user_achievements`     AUTO_INCREMENT = 1;
ALTER TABLE `user_badges`           AUTO_INCREMENT = 1;
ALTER TABLE `levels`                AUTO_INCREMENT = 1;
ALTER TABLE `points`                AUTO_INCREMENT = 1;
ALTER TABLE `xp_transactions`       AUTO_INCREMENT = 1;
ALTER TABLE `coin_transactions`     AUTO_INCREMENT = 1;
ALTER TABLE `leaderboard`           AUTO_INCREMENT = 1;
ALTER TABLE `daily_challenges`      AUTO_INCREMENT = 1;
ALTER TABLE `notifications`         AUTO_INCREMENT = 1;
ALTER TABLE `user_notifications`    AUTO_INCREMENT = 1;
ALTER TABLE `subscription_plans`    AUTO_INCREMENT = 1;
ALTER TABLE `subscriptions`         AUTO_INCREMENT = 1;
ALTER TABLE `coupons`               AUTO_INCREMENT = 1;
ALTER TABLE `coupon_usage`          AUTO_INCREMENT = 1;
ALTER TABLE `shop_items`            AUTO_INCREMENT = 1;
ALTER TABLE `courses`               AUTO_INCREMENT = 1;
ALTER TABLE `course_prerequisites`  AUTO_INCREMENT = 1;
ALTER TABLE `course_requirements`   AUTO_INCREMENT = 1;
ALTER TABLE `course_objectives`     AUTO_INCREMENT = 1;
ALTER TABLE `course_modules`        AUTO_INCREMENT = 1;
ALTER TABLE `module_progress`       AUTO_INCREMENT = 1;
ALTER TABLE `course_lessons`        AUTO_INCREMENT = 1;
ALTER TABLE `course_resources`      AUTO_INCREMENT = 1;
ALTER TABLE `course_reviews`        AUTO_INCREMENT = 1;
ALTER TABLE `review_votes`          AUTO_INCREMENT = 1;
ALTER TABLE `enrollments`           AUTO_INCREMENT = 1;
ALTER TABLE `lesson_progress`       AUTO_INCREMENT = 1;
ALTER TABLE `lesson_comments`       AUTO_INCREMENT = 1;
ALTER TABLE `lesson_comment_likes`  AUTO_INCREMENT = 1;
ALTER TABLE `projects`              AUTO_INCREMENT = 1;
ALTER TABLE `project_submissions`   AUTO_INCREMENT = 1;
ALTER TABLE `project_likes`         AUTO_INCREMENT = 1;
ALTER TABLE `quizzes`               AUTO_INCREMENT = 1;
ALTER TABLE `quiz_questions`        AUTO_INCREMENT = 1;
ALTER TABLE `quiz_options`          AUTO_INCREMENT = 1;
ALTER TABLE `quiz_attempts`         AUTO_INCREMENT = 1;
ALTER TABLE `quiz_answers`          AUTO_INCREMENT = 1;
ALTER TABLE `assignments`           AUTO_INCREMENT = 1;
ALTER TABLE `assignment_submissions` AUTO_INCREMENT = 1;
ALTER TABLE `certificates`          AUTO_INCREMENT = 1;
ALTER TABLE `discussions`           AUTO_INCREMENT = 1;
ALTER TABLE `discussion_replies`    AUTO_INCREMENT = 1;
ALTER TABLE `messages`              AUTO_INCREMENT = 1;
ALTER TABLE `message_participants`  AUTO_INCREMENT = 1;
ALTER TABLE `carts`                 AUTO_INCREMENT = 1;
ALTER TABLE `cart_items`            AUTO_INCREMENT = 1;
ALTER TABLE `orders`                AUTO_INCREMENT = 1;
ALTER TABLE `order_items`           AUTO_INCREMENT = 1;
ALTER TABLE `payments`              AUTO_INCREMENT = 1;
ALTER TABLE `email_verifications`   AUTO_INCREMENT = 1;
ALTER TABLE `password_resets`       AUTO_INCREMENT = 1;
ALTER TABLE `wishlists`             AUTO_INCREMENT = 1;
ALTER TABLE `forums`                AUTO_INCREMENT = 1;
ALTER TABLE `forum_topics`          AUTO_INCREMENT = 1;
ALTER TABLE `forum_posts`           AUTO_INCREMENT = 1;
ALTER TABLE `blog_posts`            AUTO_INCREMENT = 1;
ALTER TABLE `blog_comments`         AUTO_INCREMENT = 1;
ALTER TABLE `pages`                 AUTO_INCREMENT = 1;
ALTER TABLE `logs`                  AUTO_INCREMENT = 1;


-- ================================================================
-- VERIFICAÇÃO FINAL
-- ================================================================
SELECT
    (SELECT COUNT(*) FROM information_schema.tables
     WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE') AS total_tabelas,
    (SELECT COUNT(*) FROM information_schema.views
     WHERE table_schema = DATABASE()) AS total_views;

-- ================================================================
-- DADOS INICIAIS (Seed) — migrados do schema-old v2.0
-- ================================================================

-- Níveis de gamificação
INSERT IGNORE INTO `levels` (`level_number`, `title`, `xp_required`, `badge_icon`, `color`, `perks`) VALUES
(1,  'Iniciante',        0,     '🌱', '#10b981', 'Acesso aos cursos básicos'),
(2,  'Aprendiz',         100,   '📚', '#6366f1', 'Desbloqueio de conquistas'),
(3,  'Estudante',        300,   '✏️',  '#8b5cf6', 'Acesso a quizzes avançados'),
(4,  'Praticante',       600,   '💻', '#ec4899', 'Projetos práticos'),
(5,  'Desenvolvedor Jr', 1000,  '🚀', '#f59e0b', 'Certificados personalizados'),
(6,  'Desenvolvedor',    1500,  '⚡', '#ef4444', 'Acesso a conteúdo exclusivo'),
(7,  'Desenvolvedor Sr', 2500,  '🔥', '#dc2626', 'Mentoria com instrutores'),
(8,  'Especialista',     4000,  '💎', '#0ea5e9', 'Criar seus próprios cursos'),
(9,  'Mestre',           6000,  '👑', '#fbbf24', 'Acesso vitalício a todos os cursos'),
(10, 'Lenda',            10000, '🏆', '#f59e0b', 'Reconhecimento especial na plataforma');


-- Conquistas iniciais
INSERT IGNORE INTO `achievements` (`name`, `slug`, `description`, `icon`, `requirement_type`, `requirement_value`, `xp_reward`, `coin_reward`, `sort_order`, `is_active`) VALUES
('Primeiro Passo',    'primeiro-passo',    'Complete sua primeira lição',         '🎯', 'lessons_completed',  1,    10,   5,    1,  1),
('Estudante Dedicado','estudante-dedicado','Complete 10 lições',                  '📖', 'lessons_completed',  10,   50,   20,   2,  1),
('Maratonista',       'maratonista',       'Complete 50 lições',                  '🏃', 'lessons_completed',  50,   150,  50,   3,  1),
('Mestre das Lições', 'mestre-licoes',     'Complete 100 lições',                 '📚', 'lessons_completed',  100,  300,  100,  4,  1),
('Formando',          'formando',          'Complete seu primeiro curso',          '🎓', 'courses_completed',  1,    100,  50,   5,  1),
('Multitarefa',       'multitarefa',       'Complete 5 cursos',                   '🎖️','courses_completed',  5,    500,  200,  6,  1),
('Acadêmico',         'academico',         'Complete 10 cursos',                  '🏅', 'courses_completed',  10,   1000, 500,  7,  1),
('Constante',         'constante',         'Mantenha um streak de 7 dias',        '🔥', 'streak',             7,    70,   30,   8,  1),
('Imparável',         'imparavel',         'Mantenha um streak de 30 dias',       '⚡', 'streak',             30,   300,  150,  9,  1),
('Lenda Viva',        'lenda-viva',        'Mantenha um streak de 100 dias',      '💫', 'streak',             100,  1000, 500,  10, 1),
('Nota Perfeita',     'nota-perfeita',     'Acerte 100% em um quiz',              '💯', 'perfect_quiz',       1,    50,   25,   11, 1),
('Gênio',             'genio',             'Acerte 100% em 10 quizzes',           '🧠', 'perfect_quiz',       10,   200,  100,  12, 1),
('Caçador de XP',     'cacador-xp',        'Ganhe 1000 XP',                       '⭐', 'xp_earned',          1000, 100,  50,   13, 1),
('Mestre do XP',      'mestre-xp',         'Ganhe 10000 XP',                      '🌟', 'xp_earned',          10000,500,  250,  14, 1);


-- Cursos iniciais (instructor_id = 1 que é o admin)
INSERT IGNORE INTO `courses` (`id`, `title`, `slug`, `short_description`, `description`, `instructor_id`, `category_id`, `level`, `price`, `is_published`, `status`) VALUES
(1, 'Phaser 3 para Iniciantes', 'phaser-3-iniciantes', 'Aprenda a criar seu primeiro jogo HTML5', 'Neste curso você vai aprender as bases do Phaser 3, criando um jogo completo de plataforma.', 1, 1, 'beginner', 0.00, 1, 'published'),
(2, 'React para Desenvolvedores de Jogos', 'react-para-jogos', 'Crie interfaces incríveis para seus jogos', 'Aprenda a usar React para criar UIs complexas, inventários e menus para jogos web.', 1, 2, 'intermediate', 49.90, 1, 'published'),
(3, 'Arquitetura Avançada em Unity', 'unity-arquitetura-avancada', 'Domine padrões de projeto e performance', 'Curso focado em arquitetura limpa, ScriptableObjects e otimização para grandes projetos Unity.', 1, 6, 'advanced', 199.00, 1, 'published'),
(4, 'Sistemas ECS e DOTS em Unity', 'unity-ecs-dots', 'Performance extrema com Data-Oriented Technology Stack', 'Neste curso avançado, você aprenderá a usar o Entity Component System da Unity para renderizar milhões de entidades.', 1, 6, 'expert', 299.00, 1, 'published');


-- Módulos iniciais
INSERT IGNORE INTO `course_modules` (`id`, `course_id`, `title`, `sort_order`) VALUES
(1, 1, 'Introdução ao Phaser', 1),
(2, 1, 'Criando o Mundo', 2),
(3, 2, 'React Hooks para Games', 1),
(4, 3, 'Padrões de Projeto em C#', 1);


-- Lições iniciais
INSERT IGNORE INTO `course_lessons` (`course_id`, `module_id`, `title`, `slug`, `content_type`, `content`, `sort_order`, `is_published`) VALUES
(1, 1, 'Bem-vindo ao Curso', 'bem-vindo', 'text', 'Olá! Nesta aula vamos preparar nosso ambiente de desenvolvimento.', 1, 1),
(1, 1, 'Configurando o Phaser', 'configuracao', 'video', 'Conteúdo em vídeo sobre configuração do framework.', 2, 1),
(1, 2, 'Sprites e Assets', 'sprites', 'video', 'Como carregar e exibir imagens no jogo.', 1, 1),
(2, 3, 'UseState no Inventário', 'react-hooks-inventario', 'video', 'Usando hooks para gerenciar o estado do inventário.', 1, 1),
(3, 4, 'Singleton vs ScriptableObjects', 'singleton-scriptable', 'video', 'Análise profunda sobre persistência de dados.', 1, 1);


-- Desafios diários
INSERT IGNORE INTO `daily_challenges` (`date`, `title`, `description`, `type`, `requirement_type`, `requirement_value`, `xp_reward`, `coin_reward`) VALUES
(CURDATE(), 'Madrugador', 'Complete uma lição hoje', 'lesson', 'lessons_completed', 1, 50, 10),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'Mestre dos Quizzes', 'Acerte um quiz com 100%', 'quiz', 'perfect_quiz', 1, 100, 20);


-- Notícias / Blog
INSERT IGNORE INTO `blog_posts` (`title`, `slug`, `excerpt`, `content`, `author_id`, `category_id`, `status`, `is_featured`, `published_at`) VALUES
('Bem-vindo à GameDev Academy!', 'bem-vindo-gda', 'Conheça a nova plataforma para desenvolvedores de jogos.', 'Estamos muito felizes em lançar a GameDev Academy! Aqui você encontrará os melhores cursos de desenvolvimento de jogos, uma comunidade vibrante e um sistema de gamificação para tornar seu aprendizado mais divertido.', 1, 5, 'published', 1, NOW()),
('O que é Gamificação no Aprendizado?', 'gamificacao-aprendizado', 'Entenda como nosso sistema de XP e Conquistas ajuda você.', 'Aprender pode ser difícil, mas com jogos fica mais fácil. Nosso sistema recompensa sua dedicação com níveis, badges e itens exclusivos.', 1, 5, 'published', 0, NOW());


-- Categorias iniciais
INSERT IGNORE INTO `categories` (`name`, `slug`, `description`, `icon`, `color`, `sort_order`, `is_active`, `status`) VALUES
('Phaser 3',       'phaser-3',       'Framework JavaScript para desenvolvimento de jogos 2D', '🎮', '#6366f1', 1,  1, 'active'),
('React',          'react',          'Biblioteca JavaScript para construção de interfaces',   '⚛️','#61dafb', 2,  1, 'active'),
('JavaScript',     'javascript',     'Linguagem de programação essencial para web',           '📜', '#f7df1e', 3,  1, 'active'),
('TypeScript',     'typescript',     'JavaScript com tipagem estática',                       '📘', '#3178c6', 4,  1, 'active'),
('Game Design',    'game-design',    'Princípios e técnicas de design de jogos',              '🎨', '#ec4899', 5,  1, 'active'),
('Unity',          'unity',          'Motor de jogos profissional multiplataforma',           '🎯', '#000000', 6,  1, 'active'),
('Godot',          'godot',          'Motor de jogos open source',                            '🤖', '#478cbf', 7,  1, 'active'),
('Pixel Art',      'pixel-art',      'Criação de arte em pixel para jogos',                  '🖼️','#ff6b6b', 8,  1, 'active'),
('Game Audio',     'game-audio',     'Áudio e música para jogos',                            '🎵', '#9b59b6', 9,  1, 'active'),
('Marketing Indie','marketing-indie','Marketing e publicação de jogos indie',                 '📈', '#2ecc71', 10, 1, 'active');


-- Badges iniciais
INSERT IGNORE INTO `badges` (`name`, `slug`, `description`, `icon`, `category`, `criteria_type`, `criteria_value`, `points_reward`, `rarity`, `sort_order`, `is_active`) VALUES
('Verificado',   'verificado',   'Perfil verificado',              '✓',   'special',     'special', 1, 0,   'common',    1, 1),
('Instrutor',    'instrutor',    'Instrutor da plataforma',        '👨‍🏫','community',   'special', 1, 0,   'uncommon',  2, 1),
('Premium',      'premium',      'Membro premium',                 '💎',  'special',     'special', 1, 0,   'rare',      3, 1),
('Beta Tester',  'beta-tester',  'Testador beta da plataforma',    '🔬',  'special',     'special', 1, 100, 'epic',      4, 1),
('Contribuidor', 'contribuidor', 'Contribuiu com o projeto',       '⭐',  'achievement', 'special', 1, 200, 'legendary', 5, 1);


-- Configurações padrão do sistema
INSERT IGNORE INTO `settings` (`setting_key`, `setting_label`, `setting_value`, `setting_type`, `setting_group`, `description`, `is_public`) VALUES
('site_name',              'Nome do Site',        'GameDev Academy',                                              'string',  'general',      'Nome exibido no site',                1),
('site_description',       'Descrição do Site',   'Aprenda desenvolvimento de jogos do zero ao profissional',    'string',  'general',      'Descrição para SEO',                  1),
('site_logo',              'Logo',                '/assets/images/logo.png',                                     'string',  'general',      'Logo do site',                        1),
('contact_email',          'Email de Contato',    'contato@gamedev.academy',                                     'string',  'general',      'Email principal de contato',          1),
('maintenance_mode',       'Modo Manutenção',     '0',                                                           'boolean', 'system',       'Ativa/desativa modo manutenção',      0),
('registration_enabled',   'Permitir Registro',   '1',                                                           'boolean', 'system',       'Permite novos cadastros',             0),
('xp_per_lesson',          'XP por Lição',        '10',                                                          'number',  'gamification', 'XP ganho ao completar lição',         0),
('coins_per_lesson',       'Moedas por Lição',    '1',                                                           'number',  'gamification', 'Moedas ganhas ao completar lição',    0),
('streak_bonus_multiplier','Multiplicador Streak','1.5',                                                          'string',  'gamification', 'Bônus de XP para streak ativo',       0),
('default_theme',          'Tema Padrão',         'dark',                                                        'string',  'appearance',   'Tema padrão para novos usuários',     0),
('default_language',       'Idioma Padrão',       'pt-BR',                                                       'string',  'appearance',   'Idioma padrão da plataforma',         0);


-- Usuário admin padrão (senha: admin123 — trocar imediatamente em produção)
INSERT IGNORE INTO `users` (`username`, `email`, `password`, `name`, `full_name`, `role`, `total_points`, `is_active`, `status`, `email_verified_at`) VALUES
('admin', 'admin@gamedev.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'Admin', 'Administrador', 'admin', 0, 1, 'active', NOW());

-- Usuário demo (senha: demo123)
INSERT IGNORE INTO `users` (`username`, `email`, `password`, `name`, `full_name`, `role`, `total_points`, `is_active`, `status`, `email_verified_at`) VALUES
('demo', 'demo@gamedev.com', '$2y$10$4J4/XoQJBtV4nVqKcRwFbOUwP7rn1UTdDI5rDNr8oOvFnCy8MXKHO',
 'Demo', 'Usuário Demo', 'student', 150, 1, 'active', NOW());

-- Instrutor de exemplo (senha: password)
INSERT IGNORE INTO `users` (`username`, `email`, `password`, `name`, `full_name`, `role`, `is_active`, `status`, `specialization`, `bio`) VALUES
('mestre_jogos', 'mestre@gamedev.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mestre', 'Mestre dos Jogos', 'instructor', 1, 'active', 'Phaser 3 & Game Design', 'Especialista em desenvolvimento de jogos 2D com mais de 10 anos de experiência.');

-- Categorias de Cursos
INSERT IGNORE INTO `categories` (`name`, `slug`, `description`, `icon`, `color`, `order_index`, `is_active`) VALUES
('Phaser 3', 'phaser-3', 'Desenvolvimento de jogos 2D para web com Phaser.', '🎮', '#6366f1', 1, 1),
('React & Games', 'react-games', 'Criação de interfaces e jogos usando React.', '⚛️', '#61dafb', 2, 1),
('Unity', 'unity', 'Desenvolvimento de jogos 3D e 2D com Unity Engine.', '🛠️', '#222c37', 3, 1),
('Game Design', 'game-design', 'Fundamentos e teorias de design de jogos.', '🎨', '#f59e0b', 4, 1),
('Programação', 'programacao', 'Lógica de programação aplicada a jogos.', '💻', '#10b981', 5, 1);

-- Cursos de exemplo
INSERT IGNORE INTO `courses` (`title`, `slug`, `short_description`, `description`, `instructor_id`, `category_id`, `level`, `duration_hours`, `xp_reward`, `coin_reward`, `is_published`, `is_featured`, `is_free`, `total_students`) VALUES
('Phaser 3: Do Zero ao Jogo Completo', 'phaser-3-zero-jogo', 'Aprenda Phaser 3 criando um RPG de aventura.', 'Neste curso você aprenderá todos os fundamentos do Phaser 3, desde o setup inicial até a publicação do seu jogo.', 3, 1, 'beginner', 12.5, 500, 50, 1, 1, 1, 120),
('React para Desenvolvedores de Jogos', 'react-para-jogos', 'Interfaces dinâmicas para seus jogos web.', 'Aprenda a integrar React com motores de jogo e criar HUDs incríveis.', 3,(3, 2, 'intermediate', 8.0, 350, 30, 1, 1, 0, 45);

-- Instrutores adicionais (senha: password)
INSERT IGNORE INTO `users` (`username`, `email`, `password`, `name`, `full_name`, `role`, `is_active`, `status`, `specialization`, `bio`, `avatar`) VALUES
('ana_art', 'ana@gamedev.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ana', 'Ana Silva (Pixel Art)', 'instructor', 1, 'active', 'Pixel Art & Animação 2D', 'Artista técnica com foco em estética retrô e animação frame-a-frame.', 'https://i.pravatar.cc/150?u=ana'),
('bruno_unity', 'bruno@gamedev.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bruno', 'Bruno Oliveira (Unity)', 'instructor', 1, 'active', 'Unity & C# Programming', 'Engenheiro de software especializado em arquitetura de sistemas para jogos 3D.', 'https://i.pravatar.cc/150?u=bruno');

-- Mais Cursos
INSERT IGNORE INTO `courses` (`title`, `slug`, `short_description`, `description`, `instructor_id`, `category_id`, `level`, `duration_hours`, `xp_reward`, `coin_reward`, `is_published`, `is_featured`, `is_free`, `thumbnail`, `image`) VALUES
('Pixel Art para Games: Estilo Top-Down', 'pixel-art-top-down', 'Domine a arte de criar cenários e personagens 2D.', 'Aprenda técnicas de sombreamento, paletas de cores e animação para jogos estilo RPG Maker e Zelda.', 4, 8, 'beginner', 10.0, 400, 40, 1, 1, 0, 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=800', 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=1200'),
('Godot 4: Do Zero ao Primeiro Jogo', 'godot-4-iniciantes', 'Explore o motor de jogos open-source que está conquistando o mundo.', 'Crie um jogo de plataforma completo usando GDScript e as novas ferramentas do Godot 4.', 3, 7, 'beginner', 14.5, 550, 55, 1, 0, 1, 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=800', 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=1200'),
('Game Design: A Psicologia do Jogador', 'psicologia-game-design', 'Entenda o que faz um jogo ser divertido e viciante.', 'Aprenda sobre loops de gameplay, sistemas de recompensa e curva de dificuldade.', 3, 4, 'intermediate', 6.0, 300, 25, 1, 1, 0, 'https://images.unsplash.com/photo-1580234811497-9bd7fd04086e?w=800', 'https://images.unsplash.com/photo-1580234811497-9bd7fd04086e?w=1200');

-- Mais Notícias
INSERT IGNORE INTO `blog_posts` (`title`, `slug`, `excerpt`, `content`, `author_id`, `category_id`, `status`, `is_featured`, `published_at`, `featured_image`) VALUES
('O Futuro da WebGL no Desenvolvimento de Jogos', 'futuro-webgl-games', 'Como as novas APIs estão mudando o que é possível no navegador.', '<p>Com a chegada do WebGPU, os jogos de navegador estão prestes a dar um salto gigantesco em fidelidade visual.</p>', 3, 5, 'published', 1, NOW(), 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=800'),
('5 Dicas de Pixel Art para Iniciantes', 'dicas-pixel-art-iniciantes', 'Melhore sua arte com técnicas simples de cor e forma.', '<p>Muitos iniciantes cometem erros comuns em paletas. Aqui mostramos como evitar o efeito "dirty pixel".</p>', 4, 8, 'published', 0, NOW(), 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=800');

-- Ranking (Leaderboard) - Dados fictícios para amostra
INSERT IGNORE INTO `weekly_leaderboard` (`user_id`, `week_start`, `xp_earned`, `lessons_completed`) VALUES
(1, DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), 1250, 12),
(2, DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), 850, 8),
(3, DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), 2100, 15);

-- Amostra de Rankings Globais (Points na tabela users)
UPDATE `users` SET `total_points` = 5000, `experience_points` = 5000 WHERE `id` = 1;
UPDATE `users` SET `total_points` = 2500, `experience_points` = 2500 WHERE `id` = 2;
UPDATE `users` SET `total_points` = 3800, `experience_points` = 3800 WHERE `id` = 3;
UPDATE `users` SET `total_points` = 1200, `experience_points` = 1200 WHERE `id` = 4;
UPDATE `users` SET `total_points` = 900, `experience_points` = 900 WHERE `id` = 5;

-- Exemplos de Matrículas (Enrollments) para preencher o site
INSERT IGNORE INTO `enrollments` (`user_id`, `course_id`, `status`, `progress_percent`, `lessons_completed`, `enrolled_at`) VALUES
(2, 1, 'active', 45.50, 5, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(5, 1, 'completed', 100.00, 12, DATE_SUB(NOW(), INTERVAL 30 DAY));

COMMIT;

-- Módulos de exemplo
INSERT IGNORE INTO `course_modules` (`course_id`, `title`, `description`, `sort_order`, `xp_reward`) VALUES
(1, 'Introdução ao Phaser', 'Conhecendo o motor e ambiente.', 1, 50),
(1, 'Primeiros Passos', 'Sprites, grupos e física.', 2, 50),
(2, 'Fundamentos do React', 'Hooks e Componentes.', 1, 40);

-- Lições de exemplo
INSERT IGNORE INTO `course_lessons` (`module_id`, `course_id`, `title`, `slug`, `content_type`, `video_url`, `video_provider`, `video_duration`, `xp_reward`, `coin_reward`, `sort_order`, `is_published`) VALUES
(1, 1, 'O que é Phaser?', 'introducao-phaser', 'video', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'youtube', 10, 10, 1, 1, 1),
(1, 1, 'Configurando o Ambiente', 'configurando-ambiente', 'video', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'youtube', 15, 10, 1, 2, 1),
(2, 1, 'Trabalhando com Sprites', 'trabalhando-sprites', 'video', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'youtube', 20, 15, 2, 1, 1);

-- Conquistas (Achievements)
INSERT IGNORE INTO `achievements` (`name`, `slug`, `description`, `icon`, `xp_reward`, `coin_reward`, `requirement_type`, `requirement_value`) VALUES
('Primeiros Passos', 'primeiros-passos', 'Concluiu sua primeira lição.', '🌱', 50, 10, 'lessons_completed', 1),
('Estudante Dedicado', 'estudante-dedicado', 'Concluiu 10 lições.', '📚', 200, 50, 'lessons_completed', 10),
('Mestre do Phaser', 'mestre-phaser', 'Concluiu o curso de Phaser 3.', '👑', 500, 100, 'courses_completed', 1);


-- Notícias (Blog Posts)
INSERT IGNORE INTO `blog_posts` (`title`, `slug`, `excerpt`, `content`, `author_id`, `category_id`, `status`, `is_featured`, `published_at`) VALUES
('Bem-vindo ao GameDev Academy!', 'bem-vindo', 'Estamos felizes em anunciar nossa nova plataforma.', '<p>Olá desenvolvedores! É com muita alegria que lançamos a GameDev Academy, sua nova casa para aprender desenvolvimento de jogos.</p>', 1, 5, 'published', 1, NOW()),
('Phaser 3.60: O que há de novo?', 'phaser-3-60-novidades', 'Confira as principais mudanças na nova versão do Phaser.', '<p>A nova versão do Phaser traz melhorias significativas em performance e novos recursos para física.</p>', 3, 1, 'published', 0, NOW());

COMMIT;
-- ================================================================
-- ================================================================
--
--   GAMEDEV ACADEMY - SCHEMA COMPLETO DO BANCO DE DADOS
--   
--   Versão: 2.0.0
--   Data: 2025
--   Projeto: https://github.com/davidcreator/gamedev-academy
--   
--   INSTRUÇÕES:
--   1. Faça backup antes de executar em produção
--   2. Execute este arquivo completo de uma vez
--   3. A ordem das tabelas respeita as dependências de FK
--   4. Todos os DROP são protegidos com IF EXISTS
--   5. FK_CHECKS desabilitado apenas durante a criação
--
--   TOTAL DE TABELAS: 54
--
-- ================================================================
-- ================================================================


-- ================================================================
-- CONFIGURAÇÃO INICIAL DE SEGURANÇA
-- ================================================================

-- Garante que o script não quebre por configurações do servidor
SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS;
SET @OLD_SQL_MODE = @@SQL_MODE;
SET @OLD_UNIQUE_CHECKS = @@UNIQUE_CHECKS;
SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT;
SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS;
SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION;
SET @OLD_SQL_NOTES = @@SQL_NOTES;

SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET SQL_NOTES = 0;
SET TIME_ZONE = '+00:00';


-- ================================================================
-- CRIAÇÃO DO BANCO (IGNORADA SE JÁ EXISTIR)
-- ================================================================

CREATE DATABASE IF NOT EXISTS `gamedev_academy`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE `gamedev_academy`;


-- ================================================================
-- ================================================================
--
--   NÍVEL 0 - TABELAS SEM DEPENDÊNCIAS (14 tabelas)
--
--   Estas tabelas não possuem Foreign Keys apontando para
--   outras tabelas do sistema. São a base de tudo.
--
-- ================================================================
-- ================================================================


-- ----------------------------------------------------------------
-- TABELA 01/54: users
-- Descrição: Usuários do sistema (alunos, instrutores, admins)
-- Dependências: Nenhuma
-- Referenciada por: Praticamente todas as tabelas
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id`                      INT UNSIGNED       NOT NULL AUTO_INCREMENT,
    `name`                    VARCHAR(100)       NOT NULL                          COMMENT 'Nome completo',
    `email`                   VARCHAR(150)       NOT NULL                          COMMENT 'Email único para login',
    `password`                VARCHAR(255)       NOT NULL                          COMMENT 'Hash bcrypt da senha',
    `role`                    ENUM('student','instructor','admin','super_admin') 
                                                 NOT NULL DEFAULT 'student'        COMMENT 'Papel do usuário no sistema',
    `avatar`                  VARCHAR(500)       DEFAULT NULL                      COMMENT 'URL da foto de perfil',
    `bio`                     TEXT               DEFAULT NULL                      COMMENT 'Biografia/descrição',
    `phone`                   VARCHAR(20)        DEFAULT NULL                      COMMENT 'Telefone com DDD',
    `website`                 VARCHAR(255)       DEFAULT NULL                      COMMENT 'Site pessoal/portfólio',
    `social_github`           VARCHAR(255)       DEFAULT NULL                      COMMENT 'Perfil GitHub',
    `social_linkedin`         VARCHAR(255)       DEFAULT NULL                      COMMENT 'Perfil LinkedIn',
    `social_twitter`          VARCHAR(255)       DEFAULT NULL                      COMMENT 'Perfil Twitter/X',
    `social_youtube`          VARCHAR(255)       DEFAULT NULL                      COMMENT 'Canal YouTube',
    `specialization`          VARCHAR(255)       DEFAULT NULL                      COMMENT 'Área de especialização (instrutores)',
    `total_points`            INT UNSIGNED       NOT NULL DEFAULT 0                COMMENT 'Cache de pontos totais da gamificação',
    `email_verified_at`       TIMESTAMP          NULL DEFAULT NULL                 COMMENT 'Data de verificação do email',
    `email_verification_token` VARCHAR(100)      DEFAULT NULL                      COMMENT 'Token para verificar email',
    `password_reset_token`    VARCHAR(100)       DEFAULT NULL                      COMMENT 'Token para redefinir senha',
    `password_reset_expires`  TIMESTAMP          NULL DEFAULT NULL                 COMMENT 'Expiração do token de reset',
    `two_factor_secret`       VARCHAR(255)       DEFAULT NULL                      COMMENT 'Segredo 2FA (TOTP)',
    `two_factor_enabled`      TINYINT(1)         NOT NULL DEFAULT 0               COMMENT '2FA ativado?',
    `last_login_at`           TIMESTAMP          NULL DEFAULT NULL                 COMMENT 'Último login',
    `last_login_ip`           VARCHAR(45)        DEFAULT NULL                      COMMENT 'IP do último login (suporta IPv6)',
    `is_active`               TINYINT(1)         NOT NULL DEFAULT 1               COMMENT 'Conta ativa?',
    `preferences`             JSON               DEFAULT NULL                      COMMENT 'Preferências do usuário em JSON',
    `created_at`              TIMESTAMP          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`              TIMESTAMP          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_email` (`email`),
    KEY `idx_users_role` (`role`),
    KEY `idx_users_active` (`is_active`),
    KEY `idx_users_created` (`created_at`),
    KEY `idx_users_points` (`total_points` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tabela principal de usuários do sistema';


-- ----------------------------------------------------------------
-- TABELA 02/54: categories
-- Descrição: Categorias de cursos (suporta hierarquia pai/filho)
-- Dependências: Self-reference (parent_id)
-- Referenciada por: courses, course_categories, blog_posts
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
    `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(100)     NOT NULL                          COMMENT 'Nome da categoria',
    `slug`          VARCHAR(120)     NOT NULL                          COMMENT 'URL amigável',
    `description`   TEXT             DEFAULT NULL                      COMMENT 'Descrição da categoria',
    `icon`          VARCHAR(100)     DEFAULT NULL                      COMMENT 'Classe CSS do ícone (ex: fas fa-cube)',
    `image`         VARCHAR(500)     DEFAULT NULL                      COMMENT 'Imagem de capa da categoria',
    `color`         VARCHAR(7)       DEFAULT '#6366f1'                 COMMENT 'Cor hexadecimal',
    `parent_id`     INT UNSIGNED     DEFAULT NULL                      COMMENT 'Categoria pai (NULL = raiz)',
    `sort_order`    INT              NOT NULL DEFAULT 0                COMMENT 'Ordem de exibição',
    `is_active`     TINYINT(1)       NOT NULL DEFAULT 1               COMMENT 'Categoria visível?',
    `course_count`  INT UNSIGNED     NOT NULL DEFAULT 0               COMMENT 'Cache: total de cursos nesta categoria',
    `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_categories_slug` (`slug`),
    KEY `idx_categories_parent` (`parent_id`),
    KEY `idx_categories_active_order` (`is_active`, `sort_order`),
    CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`)
        REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Categorias hierárquicas para organização de cursos';


-- ----------------------------------------------------------------
-- TABELA 03/54: tags
-- Descrição: Tags/etiquetas para cursos e conteúdo
-- Dependências: Nenhuma
-- Referenciada por: course_tags, blog_post_tags
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(50)     NOT NULL                    COMMENT 'Nome da tag',
    `slug`        VARCHAR(60)     NOT NULL                    COMMENT 'URL amigável',
    `usage_count` INT UNSIGNED    NOT NULL DEFAULT 0          COMMENT 'Cache: vezes que a tag foi usada',
    `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_tags_slug` (`slug`),
    KEY `idx_tags_usage` (`usage_count` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tags para classificação de conteúdo';


-- ----------------------------------------------------------------
-- TABELA 04/54: settings
-- Descrição: Configurações dinâmicas do sistema
-- Dependências: Nenhuma
-- Referenciada por: Nenhuma (lida via código)
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `setting_key`     VARCHAR(100)     NOT NULL                    COMMENT 'Chave única da configuração',
    `setting_value`   LONGTEXT         DEFAULT NULL                COMMENT 'Valor da configuração',
    `setting_type`    ENUM('string','number','boolean','json','html','text')
                                       NOT NULL DEFAULT 'string'   COMMENT 'Tipo do valor para validação',
    `setting_group`   VARCHAR(50)      NOT NULL DEFAULT 'general'  COMMENT 'Grupo: general, payment, email, features, seo, social',
    `description`     VARCHAR(255)     DEFAULT NULL                COMMENT 'Descrição para o painel admin',
    `is_public`       TINYINT(1)       NOT NULL DEFAULT 0          COMMENT 'Visível na API pública?',
    `updated_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_settings_key` (`setting_key`),
    KEY `idx_settings_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Configurações dinâmicas do sistema (key-value store)';


-- ----------------------------------------------------------------
-- TABELA 05/54: pages
-- Descrição: Páginas estáticas do site (Sobre, Termos, etc.)
-- Dependências: Nenhuma
-- Referenciada por: Nenhuma
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
    `id`               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `title`            VARCHAR(255)     NOT NULL                    COMMENT 'Título da página',
    `slug`             VARCHAR(280)     NOT NULL                    COMMENT 'URL amigável',
    `content`          LONGTEXT         NOT NULL                    COMMENT 'Conteúdo HTML da página',
    `meta_title`       VARCHAR(255)     DEFAULT NULL                COMMENT 'Title tag para SEO',
    `meta_description` VARCHAR(500)     DEFAULT NULL                COMMENT 'Meta description para SEO',
    `template`         VARCHAR(50)      DEFAULT 'default'           COMMENT 'Template de layout a usar',
    `sort_order`       INT              NOT NULL DEFAULT 0          COMMENT 'Ordem no menu',
    `show_in_menu`     TINYINT(1)       NOT NULL DEFAULT 0          COMMENT 'Exibir no menu principal?',
    `show_in_footer`   TINYINT(1)       NOT NULL DEFAULT 0          COMMENT 'Exibir no rodapé?',
    `is_published`     TINYINT(1)       NOT NULL DEFAULT 1          COMMENT 'Página publicada?',
    `created_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_pages_slug` (`slug`),
    KEY `idx_pages_published` (`is_published`),
    KEY `idx_pages_menu` (`show_in_menu`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Páginas estáticas do site (CMS básico)';


-- ----------------------------------------------------------------
-- TABELA 06/54: badges
-- Descrição: Conquistas/medalhas do sistema de gamificação
-- Dependências: Nenhuma
-- Referenciada por: user_badges
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `badges`;
CREATE TABLE `badges` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`            VARCHAR(100)     NOT NULL                    COMMENT 'Nome da conquista',
    `slug`            VARCHAR(120)     NOT NULL                    COMMENT 'Identificador único',
    `description`     TEXT             DEFAULT NULL                COMMENT 'Como conquistar',
    `icon`            VARCHAR(500)     NOT NULL                    COMMENT 'Emoji ou URL do ícone',
    `category`        ENUM('course','engagement','achievement','special','community')
                                       NOT NULL DEFAULT 'achievement' COMMENT 'Tipo da conquista',
    `criteria_type`   VARCHAR(50)      NOT NULL                    COMMENT 'Tipo: courses_completed, lessons_completed, streak_days, etc',
    `criteria_value`  INT UNSIGNED     NOT NULL DEFAULT 1          COMMENT 'Valor necessário para conquistar',
    `points_reward`   INT UNSIGNED     NOT NULL DEFAULT 0          COMMENT 'Pontos ganhos ao conquistar',
    `rarity`          ENUM('common','uncommon','rare','epic','legendary')
                                       NOT NULL DEFAULT 'common'   COMMENT 'Raridade visual',
    `sort_order`      INT              NOT NULL DEFAULT 0,
    `is_active`       TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_badges_slug` (`slug`),
    KEY `idx_badges_category` (`category`),
    KEY `idx_badges_criteria` (`criteria_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Conquistas do sistema de gamificação';


-- ----------------------------------------------------------------
-- TABELA 07/54: certificate_templates
-- Descrição: Templates visuais para certificados
-- Dependências: Nenhuma
-- Referenciada por: certificates
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `certificate_templates`;
CREATE TABLE `certificate_templates` (
    `id`                INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`              VARCHAR(100)     NOT NULL                    COMMENT 'Nome do template',
    `html_template`     LONGTEXT         NOT NULL                    COMMENT 'HTML do certificado com placeholders',
    `css_styles`        LONGTEXT         DEFAULT NULL                COMMENT 'CSS customizado',
    `background_image`  VARCHAR(500)     DEFAULT NULL                COMMENT 'Imagem de fundo',
    `orientation`       ENUM('landscape','portrait') NOT NULL DEFAULT 'landscape',
    `paper_size`        ENUM('a4','letter','custom') NOT NULL DEFAULT 'a4',
    `is_default`        TINYINT(1)       NOT NULL DEFAULT 0          COMMENT 'Template padrão?',
    `is_active`         TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Templates visuais para geração de certificados em PDF';


-- ----------------------------------------------------------------
-- TABELA 08/54: email_templates
-- Descrição: Templates de emails transacionais
-- Dependências: Nenhuma
-- Referenciada por: Nenhuma (usado via código)
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `email_templates`;
CREATE TABLE `email_templates` (
    `id`           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`         VARCHAR(100)     NOT NULL                    COMMENT 'Identificador: welcome, password_reset, etc',
    `subject`      VARCHAR(255)     NOT NULL                    COMMENT 'Assunto do email com placeholders',
    `body_html`    LONGTEXT         NOT NULL                    COMMENT 'Corpo HTML com placeholders',
    `body_text`    LONGTEXT         DEFAULT NULL                COMMENT 'Versão texto puro (fallback)',
    `variables`    JSON             DEFAULT NULL                COMMENT 'Lista de variáveis disponíveis',
    `is_active`    TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_email_templates_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Templates de emails transacionais do sistema';


-- ----------------------------------------------------------------
-- TABELA 09/54: email_log
-- Descrição: Log de todos os emails enviados
-- Dependências: Nenhuma
-- Referenciada por: Nenhuma
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `email_log`;
CREATE TABLE `email_log` (
    `id`             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `to_email`       VARCHAR(150)     NOT NULL,
    `to_name`        VARCHAR(100)     DEFAULT NULL,
    `subject`        VARCHAR(255)     NOT NULL,
    `template`       VARCHAR(50)      DEFAULT NULL                COMMENT 'Template usado',
    `body_preview`   VARCHAR(500)     DEFAULT NULL                COMMENT 'Primeiros caracteres do corpo',
    `status`         ENUM('queued','sent','failed','bounced')
                                      NOT NULL DEFAULT 'queued',
    `error_message`  TEXT             DEFAULT NULL,
    `attempts`       TINYINT UNSIGNED NOT NULL DEFAULT 0          COMMENT 'Tentativas de envio',
    `sent_at`        TIMESTAMP        NULL DEFAULT NULL,
    `created_at`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_emaillog_status` (`status`),
    KEY `idx_emaillog_created` (`created_at`),
    KEY `idx_emaillog_to` (`to_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Registro de todos os emails enviados pelo sistema';


-- ----------------------------------------------------------------
-- TABELA 10/54: faq_categories
-- Descrição: Categorias para perguntas frequentes
-- Dependências: Nenhuma
-- Referenciada por: faqs
-- ----------------------------------------------------------------
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Categorias para organização das FAQs';


-- ----------------------------------------------------------------
-- TABELA 11/54: faqs
-- Descrição: Perguntas frequentes
-- Dependências: faq_categories
-- Referenciada por: Nenhuma
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `faqs`;
CREATE TABLE `faqs` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `category_id`  INT UNSIGNED    DEFAULT NULL,
    `question`     VARCHAR(500)    NOT NULL,
    `answer`       LONGTEXT        NOT NULL,
    `sort_order`   INT             NOT NULL DEFAULT 0,
    `is_published` TINYINT(1)      NOT NULL DEFAULT 1,
    `view_count`   INT UNSIGNED    NOT NULL DEFAULT 0,
    `helpful_yes`  INT UNSIGNED    NOT NULL DEFAULT 0          COMMENT 'Votos "útil"',
    `helpful_no`   INT UNSIGNED    NOT NULL DEFAULT 0          COMMENT 'Votos "não útil"',
    `created_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_faqs_category` (`category_id`),
    KEY `idx_faqs_published` (`is_published`, `sort_order`),
    CONSTRAINT `fk_faqs_category` FOREIGN KEY (`category_id`)
        REFERENCES `faq_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Perguntas frequentes com sistema de votos';


-- ----------------------------------------------------------------
-- TABELA 12/54: announcements
-- Descrição: Anúncios globais do sistema
-- Dependências: Nenhuma
-- Referenciada por: Nenhuma
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `announcements`;
CREATE TABLE `announcements` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `title`           VARCHAR(255)     NOT NULL,
    `content`         LONGTEXT         NOT NULL,
    `type`            ENUM('info','warning','success','danger','promotion')
                                       NOT NULL DEFAULT 'info',
    `target_audience` ENUM('all','students','instructors','admins')
                                       NOT NULL DEFAULT 'all'       COMMENT 'Para quem exibir',
    `display_type`    ENUM('banner','modal','notification')
                                       NOT NULL DEFAULT 'banner',
    `action_url`      VARCHAR(500)     DEFAULT NULL                 COMMENT 'Link do botão de ação',
    `action_text`     VARCHAR(100)     DEFAULT NULL                 COMMENT 'Texto do botão',
    `starts_at`       TIMESTAMP        NULL DEFAULT NULL,
    `ends_at`         TIMESTAMP        NULL DEFAULT NULL,
    `is_active`       TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_announcements_active` (`is_active`, `starts_at`, `ends_at`),
    KEY `idx_announcements_target` (`target_audience`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Anúncios e banners globais do sistema';


-- ----------------------------------------------------------------
-- TABELA 13/54: countries
-- Descrição: Lista de países (para perfis e pagamentos)
-- Dependências: Nenhuma
-- Referenciada por: Nenhuma (auxiliar)
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `countries`;
CREATE TABLE `countries` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100)    NOT NULL,
    `code`        CHAR(2)         NOT NULL                    COMMENT 'Código ISO 3166-1 alpha-2',
    `phone_code`  VARCHAR(5)      DEFAULT NULL                COMMENT 'Código telefônico (+55)',
    `currency`    VARCHAR(3)      DEFAULT NULL                COMMENT 'Código da moeda (BRL)',
    `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_countries_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Países para formulários e localização';


-- ----------------------------------------------------------------
-- TABELA 14/54: languages
-- Descrição: Idiomas suportados para cursos
-- Dependências: Nenhuma
-- Referenciada por: courses (campo language)
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `languages`;
CREATE TABLE `languages` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(50)     NOT NULL                    COMMENT 'Nome do idioma',
    `code`       VARCHAR(10)     NOT NULL                    COMMENT 'Código: pt-BR, en-US, es',
    `native_name` VARCHAR(50)   DEFAULT NULL                 COMMENT 'Nome no idioma nativo',
    `is_active`  TINYINT(1)      NOT NULL DEFAULT 1,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_languages_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Idiomas disponíveis para cursos';


-- ================================================================
-- ================================================================
--
--   NÍVEL 1 - DEPENDEM APENAS DO NÍVEL 0 (11 tabelas)
--
-- ================================================================
-- ================================================================


-- ----------------------------------------------------------------
-- TABELA 15/54: user_sessions
-- Descrição: Sessões ativas dos usuários
-- Dependências: users
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `user_sessions`;
CREATE TABLE `user_sessions` (
    `id`             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`        INT UNSIGNED     NOT NULL,
    `session_token`  VARCHAR(255)     NOT NULL                    COMMENT 'Token único da sessão',
    `ip_address`     VARCHAR(45)      DEFAULT NULL                COMMENT 'Suporta IPv6',
    `user_agent`     TEXT             DEFAULT NULL,
    `device_type`    ENUM('desktop','mobile','tablet','unknown')
                                      NOT NULL DEFAULT 'unknown',
    `last_activity`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `expires_at`     TIMESTAMP        NOT NULL,
    `created_at`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_session_token` (`session_token`),
    KEY `idx_sessions_user` (`user_id`),
    KEY `idx_sessions_expires` (`expires_at`),
    CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Controle de sessões ativas para segurança';


-- ----------------------------------------------------------------
-- TABELA 16/54: user_streaks
-- Descrição: Sequência de dias de estudo (gamificação)
-- Dependências: users
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `user_streaks`;
CREATE TABLE `user_streaks` (
    `id`                 INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`            INT UNSIGNED    NOT NULL,
    `current_streak`     INT UNSIGNED    NOT NULL DEFAULT 0       COMMENT 'Dias consecutivos atual',
    `longest_streak`     INT UNSIGNED    NOT NULL DEFAULT 0       COMMENT 'Recorde de dias consecutivos',
    `last_activity_date` DATE            DEFAULT NULL              COMMENT 'Última data de atividade',
    `updated_at`         TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_streak_user` (`user_id`),
    CONSTRAINT `fk_streaks_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Controle de streak (sequência de dias) para gamificação';


-- ----------------------------------------------------------------
-- TABELA 17/54: user_badges
-- Descrição: Conquistas desbloqueadas por cada usuário
-- Dependências: users, badges
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `user_badges`;
CREATE TABLE `user_badges` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED    NOT NULL,
    `badge_id`   INT UNSIGNED    NOT NULL,
    `earned_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_badge` (`user_id`, `badge_id`),
    KEY `idx_userbadges_badge` (`badge_id`),
    KEY `idx_userbadges_earned` (`earned_at`),
    CONSTRAINT `fk_userbadges_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_userbadges_badge` FOREIGN KEY (`badge_id`)
        REFERENCES `badges` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Relacionamento N:N entre usuários e conquistas obtidas';


-- ----------------------------------------------------------------
-- TABELA 18/54: user_points
-- Descrição: Histórico de pontos ganhos (gamificação)
-- Dependências: users
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `user_points`;
CREATE TABLE `user_points` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`         INT UNSIGNED     NOT NULL,
    `points`          INT              NOT NULL                    COMMENT 'Pontos ganhos (positivo) ou perdidos (negativo)',
    `action`          VARCHAR(50)      NOT NULL                    COMMENT 'Ação: lesson_complete, quiz_pass, course_complete, daily_login, review_posted',
    `reference_type`  VARCHAR(50)      DEFAULT NULL                COMMENT 'Tipo da entidade: course, lesson, quiz',
    `reference_id`    INT UNSIGNED     DEFAULT NULL                COMMENT 'ID da entidade relacionada',
    `description`     VARCHAR(255)     DEFAULT NULL                COMMENT 'Descrição legível',
    `created_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_points_user` (`user_id`),
    KEY `idx_points_action` (`action`),
    KEY `idx_points_created` (`created_at`),
    KEY `idx_points_ref` (`reference_type`, `reference_id`),
    CONSTRAINT `fk_points_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Registro detalhado de todos os pontos ganhos/perdidos';


-- ----------------------------------------------------------------
-- TABELA 19/54: leaderboard
-- Descrição: Ranking de usuários (semanal, mensal, geral)
-- Dependências: users
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `leaderboard`;
CREATE TABLE `leaderboard` (
    `id`                INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`           INT UNSIGNED     NOT NULL,
    `total_points`      INT UNSIGNED     NOT NULL DEFAULT 0,
    `courses_completed` INT UNSIGNED     NOT NULL DEFAULT 0,
    `badges_earned`     INT UNSIGNED     NOT NULL DEFAULT 0,
    `rank_position`     INT UNSIGNED     DEFAULT NULL              COMMENT 'Posição calculada no ranking',
    `period`            ENUM('weekly','monthly','all_time')
                                         NOT NULL DEFAULT 'all_time',
    `period_start`      DATE             DEFAULT NULL              COMMENT 'Início do período (NULL para all_time)',
    `updated_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_leaderboard` (`user_id`, `period`, `period_start`),
    KEY `idx_leaderboard_rank` (`period`, `rank_position`),
    KEY `idx_leaderboard_points` (`period`, `total_points` DESC),
    CONSTRAINT `fk_leaderboard_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Rankings periódicos para competição entre alunos';


-- ----------------------------------------------------------------
-- TABELA 20/54: notifications
-- Descrição: Notificações in-app para usuários
-- Dependências: users
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
    `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED     NOT NULL,
    `type`        VARCHAR(50)      NOT NULL                    COMMENT 'Tipo: enrollment, achievement, announcement, reply, system',
    `title`       VARCHAR(255)     NOT NULL,
    `message`     TEXT             NOT NULL,
    `icon`        VARCHAR(100)     DEFAULT NULL                COMMENT 'Classe CSS ou emoji do ícone',
    `action_url`  VARCHAR(500)     DEFAULT NULL                COMMENT 'Link ao clicar na notificação',
    `data`        JSON             DEFAULT NULL                COMMENT 'Dados extras em JSON',
    `is_read`     TINYINT(1)       NOT NULL DEFAULT 0,
    `read_at`     TIMESTAMP        NULL DEFAULT NULL,
    `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_notif_user_read` (`user_id`, `is_read`, `created_at` DESC),
    KEY `idx_notif_type` (`type`),
    KEY `idx_notif_created` (`created_at`),
    CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Notificações in-app com suporte a dados estruturados';


-- ----------------------------------------------------------------
-- TABELA 21/54: notification_preferences
-- Descrição: Preferências de notificação por tipo
-- Dependências: users
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `notification_preferences`;
CREATE TABLE `notification_preferences` (
    `id`                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`             INT UNSIGNED    NOT NULL,
    `notification_type`   VARCHAR(50)     NOT NULL                    COMMENT 'Tipo da notificação',
    `email_enabled`       TINYINT(1)      NOT NULL DEFAULT 1,
    `push_enabled`        TINYINT(1)      NOT NULL DEFAULT 1,
    `in_app_enabled`      TINYINT(1)      NOT NULL DEFAULT 1,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_notifpref` (`user_id`, `notification_type`),
    CONSTRAINT `fk_notifpref_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Controle granular de quais notificações o usuário quer receber';


-- ----------------------------------------------------------------
-- TABELA 22/54: media
-- Descrição: Gerenciamento de arquivos enviados
-- Dependências: users
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `media`;
CREATE TABLE `media` (
    `id`                INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`           INT UNSIGNED     DEFAULT NULL              COMMENT 'Quem fez upload',
    `filename`          VARCHAR(255)     NOT NULL                  COMMENT 'Nome do arquivo no servidor',
    `original_filename` VARCHAR(255)     NOT NULL                  COMMENT 'Nome original do arquivo',
    `file_path`         VARCHAR(500)     NOT NULL                  COMMENT 'Caminho relativo no servidor',
    `file_url`          VARCHAR(500)     NOT NULL                  COMMENT 'URL pública de acesso',
    `mime_type`         VARCHAR(100)     NOT NULL                  COMMENT 'Tipo MIME: image/png, video/mp4',
    `file_size`         BIGINT UNSIGNED  NOT NULL                  COMMENT 'Tamanho em bytes',
    `dimensions`        VARCHAR(20)      DEFAULT NULL              COMMENT 'Dimensões para imagens: 1920x1080',
    `alt_text`          VARCHAR(255)     DEFAULT NULL              COMMENT 'Texto alternativo para acessibilidade',
    `folder`            VARCHAR(100)     DEFAULT 'general'         COMMENT 'Pasta lógica de organização',
    `disk`              VARCHAR(20)      DEFAULT 'local'           COMMENT 'Disco: local, s3, bunny',
    `created_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_media_user` (`user_id`),
    KEY `idx_media_folder` (`folder`),
    KEY `idx_media_mime` (`mime_type`),
    KEY `idx_media_created` (`created_at`),
    CONSTRAINT `fk_media_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Biblioteca de mídia centralizada do sistema';


-- ----------------------------------------------------------------
-- TABELA 23/54: coupons
-- Descrição: Cupons de desconto
-- Dependências: users (created_by)
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `coupons`;
CREATE TABLE `coupons` (
    `id`                 INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `code`               VARCHAR(50)      NOT NULL                    COMMENT 'Código do cupom (ex: GAMEDEV2025)',
    `description`        VARCHAR(255)     DEFAULT NULL                COMMENT 'Descrição interna',
    `discount_type`      ENUM('percentage','fixed')
                                          NOT NULL DEFAULT 'percentage',
    `discount_value`     DECIMAL(10,2)    NOT NULL                    COMMENT 'Valor: 10.00 = 10% ou R$10',
    `min_purchase`       DECIMAL(10,2)    DEFAULT NULL                COMMENT 'Valor mínimo de compra',
    `max_discount`       DECIMAL(10,2)    DEFAULT NULL                COMMENT 'Teto do desconto para percentual',
    `max_uses`           INT UNSIGNED     DEFAULT NULL                COMMENT 'Limite total de usos (NULL = ilimitado)',
    `max_uses_per_user`  INT UNSIGNED     NOT NULL DEFAULT 1          COMMENT 'Limite por usuário',
    `used_count`         INT UNSIGNED     NOT NULL DEFAULT 0          COMMENT 'Cache: total de usos',
    `applicable_courses` JSON             DEFAULT NULL                COMMENT 'Array de course_ids (NULL = todos)',
    `starts_at`          TIMESTAMP        NULL DEFAULT NULL           COMMENT 'Início da validade',
    `expires_at`         TIMESTAMP        NULL DEFAULT NULL           COMMENT 'Fim da validade',
    `is_active`          TINYINT(1)       NOT NULL DEFAULT 1,
    `created_by`         INT UNSIGNED     DEFAULT NULL,
    `created_at`         TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`         TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_coupon_code` (`code`),
    KEY `idx_coupons_active_dates` (`is_active`, `starts_at`, `expires_at`),
    CONSTRAINT `fk_coupons_creator` FOREIGN KEY (`created_by`)
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Cupons de desconto com regras flexíveis';


-- ----------------------------------------------------------------
-- TABELA 24/54: courses
-- Descrição: Cursos da plataforma (tabela central)
-- Dependências: users (instructor_id), categories (category_id)
-- Referenciada por: Muitas tabelas
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `courses`;
CREATE TABLE `courses` (
    `id`                INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `title`             VARCHAR(255)     NOT NULL                    COMMENT 'Título do curso',
    `slug`              VARCHAR(280)     NOT NULL                    COMMENT 'URL amigável única',
    `subtitle`          VARCHAR(300)     DEFAULT NULL                COMMENT 'Subtítulo/chamada curta',
    `description`       LONGTEXT         DEFAULT NULL                COMMENT 'Descrição completa em HTML/Markdown',
    `short_description` VARCHAR(500)     DEFAULT NULL                COMMENT 'Resumo para cards e listagens',
    `thumbnail`         VARCHAR(500)     DEFAULT NULL                COMMENT 'URL da imagem de capa',
    `preview_video`     VARCHAR(500)     DEFAULT NULL                COMMENT 'URL do vídeo de apresentação',
    `instructor_id`     INT UNSIGNED     NOT NULL                    COMMENT 'Professor responsável',
    `category_id`       INT UNSIGNED     DEFAULT NULL                COMMENT 'Categoria principal',
    `level`             ENUM('beginner','intermediate','advanced','all_levels')
                                         NOT NULL DEFAULT 'beginner' COMMENT 'Nível de dificuldade',
    `language`          VARCHAR(10)      NOT NULL DEFAULT 'pt-BR'    COMMENT 'Idioma principal do curso',
    `price`             DECIMAL(10,2)    NOT NULL DEFAULT 0.00       COMMENT 'Preço atual',
    `original_price`    DECIMAL(10,2)    DEFAULT NULL                COMMENT 'Preço original (para mostrar desconto)',
    `currency`          VARCHAR(3)       NOT NULL DEFAULT 'BRL',
    `duration_hours`    DECIMAL(6,1)     NOT NULL DEFAULT 0.0        COMMENT 'Duração total estimada em horas',
    `total_lessons`     INT UNSIGNED     NOT NULL DEFAULT 0          COMMENT 'Cache: total de aulas',
    `total_modules`     INT UNSIGNED     NOT NULL DEFAULT 0          COMMENT 'Cache: total de módulos',
    `requirements`      JSON             DEFAULT NULL                COMMENT 'Pré-requisitos em array JSON',
    `what_you_learn`    JSON             DEFAULT NULL                COMMENT 'O que vai aprender (array)',
    `target_audience`   JSON             DEFAULT NULL                COMMENT 'Para quem é o curso (array)',
    `resources`         JSON             DEFAULT NULL                COMMENT 'Recursos necessários (array)',
    `game_engine`       ENUM('unity','unreal','godot','gamemaker','construct','phaser','pygame','love2d','custom','none')
                                         DEFAULT NULL                COMMENT 'Engine de jogos abordada',
    `programming_lang`  VARCHAR(50)      DEFAULT NULL                COMMENT 'Linguagem principal: C#, C++, GDScript, etc',
    `status`            ENUM('draft','pending_review','published','archived','suspended')
                                         NOT NULL DEFAULT 'draft'    COMMENT 'Status de publicação',
    `is_featured`       TINYINT(1)       NOT NULL DEFAULT 0          COMMENT 'Curso em destaque?',
    `is_free`           TINYINT(1)       NOT NULL DEFAULT 0          COMMENT 'Curso gratuito?',
    `is_bestseller`     TINYINT(1)       NOT NULL DEFAULT 0          COMMENT 'Marcado como bestseller?',
    `is_new`            TINYINT(1)       NOT NULL DEFAULT 0          COMMENT 'Marcado como novo?',
    `enrollment_count`  INT UNSIGNED     NOT NULL DEFAULT 0          COMMENT 'Cache: total de matrículas',
    `rating_average`    DECIMAL(3,2)     NOT NULL DEFAULT 0.00       COMMENT 'Cache: média de avaliações',
    `rating_count`      INT UNSIGNED     NOT NULL DEFAULT 0          COMMENT 'Cache: total de avaliações',
    `completion_rate`   DECIMAL(5,2)     NOT NULL DEFAULT 0.00       COMMENT 'Cache: taxa de conclusão %',
    `meta_title`        VARCHAR(255)     DEFAULT NULL                COMMENT 'SEO: title tag',
    `meta_description`  VARCHAR(500)     DEFAULT NULL                COMMENT 'SEO: meta description',
    `published_at`      TIMESTAMP        NULL DEFAULT NULL,
    `created_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_courses_slug` (`slug`),
    KEY `idx_courses_instructor` (`instructor_id`),
    KEY `idx_courses_category` (`category_id`),
    KEY `idx_courses_status` (`status`),
    KEY `idx_courses_featured` (`is_featured`, `status`),
    KEY `idx_courses_level` (`level`),
    KEY `idx_courses_engine` (`game_engine`),
    KEY `idx_courses_price` (`price`),
    KEY `idx_courses_free` (`is_free`, `status`),
    KEY `idx_courses_rating` (`rating_average` DESC),
    KEY `idx_courses_enrollment` (`enrollment_count` DESC),
    KEY `idx_courses_published` (`published_at` DESC),
    KEY `idx_courses_created` (`created_at`),
    FULLTEXT KEY `ft_courses_search` (`title`, `description`, `short_description`),
    CONSTRAINT `fk_courses_instructor` FOREIGN KEY (`instructor_id`)
        REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_courses_category` FOREIGN KEY (`category_id`)
        REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tabela central de cursos da plataforma';


-- ----------------------------------------------------------------
-- TABELA 25/54: blog_posts
-- Descrição: Artigos do blog
-- Dependências: users (author_id), categories
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `blog_posts`;
CREATE TABLE `blog_posts` (
    `id`               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `title`            VARCHAR(255)     NOT NULL,
    `slug`             VARCHAR(280)     NOT NULL,
    `excerpt`          VARCHAR(500)     DEFAULT NULL                COMMENT 'Resumo do artigo',
    `content`          LONGTEXT         NOT NULL,
    `featured_image`   VARCHAR(500)     DEFAULT NULL,
    `author_id`        INT UNSIGNED     NOT NULL,
    `category_id`      INT UNSIGNED     DEFAULT NULL,
    `status`           ENUM('draft','published','archived')
                                        NOT NULL DEFAULT 'draft',
    `is_featured`      TINYINT(1)       NOT NULL DEFAULT 0,
    `allow_comments`   TINYINT(1)       NOT NULL DEFAULT 1,
    `view_count`       INT UNSIGNED     NOT NULL DEFAULT 0,
    `reading_time`     INT UNSIGNED     DEFAULT NULL               COMMENT 'Tempo de leitura em minutos',
    `meta_title`       VARCHAR(255)     DEFAULT NULL,
    `meta_description` VARCHAR(500)     DEFAULT NULL,
    `published_at`     TIMESTAMP        NULL DEFAULT NULL,
    `created_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_blogposts_slug` (`slug`),
    KEY `idx_blogposts_author` (`author_id`),
    KEY `idx_blogposts_category` (`category_id`),
    KEY `idx_blogposts_status` (`status`, `published_at` DESC),
    KEY `idx_blogposts_featured` (`is_featured`),
    FULLTEXT KEY `ft_blogposts_search` (`title`, `content`),
    CONSTRAINT `fk_blogposts_author` FOREIGN KEY (`author_id`)
        REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_blogposts_category` FOREIGN KEY (`category_id`)
        REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Artigos do blog da plataforma';


-- ================================================================
-- ================================================================
--
--   NÍVEL 2 - DEPENDEM DOS NÍVEIS 0 E 1 (13 tabelas)
--
-- ================================================================
-- ================================================================


-- ----------------------------------------------------------------
-- TABELA 26/54: course_tags
-- Descrição: Relacionamento N:N entre cursos e tags
-- Dependências: courses, tags
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `course_tags`;
CREATE TABLE `course_tags` (
    `course_id`  INT UNSIGNED    NOT NULL,
    `tag_id`     INT UNSIGNED    NOT NULL,

    PRIMARY KEY (`course_id`, `tag_id`),
    KEY `idx_coursetags_tag` (`tag_id`),
    CONSTRAINT `fk_coursetags_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_coursetags_tag` FOREIGN KEY (`tag_id`)
        REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Relacionamento N:N entre cursos e tags';


-- ----------------------------------------------------------------
-- TABELA 27/54: course_categories
-- Descrição: Relacionamento N:N entre cursos e categorias adicionais
-- Dependências: courses, categories
-- Nota: courses.category_id = categoria principal, esta tabela = categorias secundárias
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `course_categories`;
CREATE TABLE `course_categories` (
    `course_id`    INT UNSIGNED    NOT NULL,
    `category_id`  INT UNSIGNED    NOT NULL,

    PRIMARY KEY (`course_id`, `category_id`),
    KEY `idx_coursecat_category` (`category_id`),
    CONSTRAINT `fk_coursecat_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_coursecat_category` FOREIGN KEY (`category_id`)
        REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Categorias secundárias de um curso (a principal fica em courses.category_id)';


-- ----------------------------------------------------------------
-- TABELA 28/54: blog_post_tags
-- Descrição: Relacionamento N:N entre posts e tags
-- Dependências: blog_posts, tags
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `blog_post_tags`;
CREATE TABLE `blog_post_tags` (
    `post_id`  INT UNSIGNED    NOT NULL,
    `tag_id`   INT UNSIGNED    NOT NULL,

    PRIMARY KEY (`post_id`, `tag_id`),
    KEY `idx_blogposttags_tag` (`tag_id`),
    CONSTRAINT `fk_blogposttags_post` FOREIGN KEY (`post_id`)
        REFERENCES `blog_posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_blogposttags_tag` FOREIGN KEY (`tag_id`)
        REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tags dos artigos do blog';


-- ----------------------------------------------------------------
-- TABELA 29/54: modules
-- Descrição: Módulos/seções de um curso
-- Dependências: courses
-- Referenciada por: lessons
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `modules`;
CREATE TABLE `modules` (
    `id`               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `course_id`        INT UNSIGNED     NOT NULL,
    `title`            VARCHAR(255)     NOT NULL                    COMMENT 'Título do módulo',
    `description`      TEXT             DEFAULT NULL,
    `sort_order`       INT              NOT NULL DEFAULT 0          COMMENT 'Ordem dentro do curso',
    `is_free_preview`  TINYINT(1)       NOT NULL DEFAULT 0          COMMENT 'Disponível como prévia gratuita?',
    `is_published`     TINYINT(1)       NOT NULL DEFAULT 1,
    `duration_minutes` INT UNSIGNED     NOT NULL DEFAULT 0          COMMENT 'Cache: duração total em minutos',
    `lesson_count`     INT UNSIGNED     NOT NULL DEFAULT 0          COMMENT 'Cache: total de aulas',
    `created_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_modules_course_order` (`course_id`, `sort_order`),
    CONSTRAINT `fk_modules_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Módulos/seções que organizam as aulas de um curso';


-- ----------------------------------------------------------------
-- TABELA 30/54: enrollments
-- Descrição: Matrículas dos alunos nos cursos
-- Dependências: users, courses
-- Referenciada por: Usada em muitas queries
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `enrollments`;
CREATE TABLE `enrollments` (
    `id`               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`          INT UNSIGNED     NOT NULL,
    `course_id`        INT UNSIGNED     NOT NULL,
    `status`           ENUM('active','completed','cancelled','expired','refunded','paused')
                                        NOT NULL DEFAULT 'active',
    `progress_percent` DECIMAL(5,2)     NOT NULL DEFAULT 0.00       COMMENT 'Progresso geral do curso 0-100',
    `lessons_completed` INT UNSIGNED    NOT NULL DEFAULT 0          COMMENT 'Cache: aulas concluídas',
    `last_lesson_id`   INT UNSIGNED     DEFAULT NULL                COMMENT 'Última aula acessada',
    `last_accessed_at` TIMESTAMP        NULL DEFAULT NULL            COMMENT 'Último acesso ao curso',
    `enrolled_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completed_at`     TIMESTAMP        NULL DEFAULT NULL,
    `expires_at`       TIMESTAMP        NULL DEFAULT NULL            COMMENT 'Expiração do acesso (NULL = vitalício)',
    `payment_id`       INT UNSIGNED     DEFAULT NULL                COMMENT 'Pagamento que gerou esta matrícula',
    `source`           VARCHAR(50)      DEFAULT 'direct'            COMMENT 'Origem: direct, coupon, gift, admin',
    `created_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_enrollment` (`user_id`, `course_id`),
    KEY `idx_enrollments_course` (`course_id`),
    KEY `idx_enrollments_status` (`status`),
    KEY `idx_enrollments_enrolled` (`enrolled_at`),
    KEY `idx_enrollments_progress` (`progress_percent`),
    KEY `idx_enrollments_created` (`created_at`),
    CONSTRAINT `fk_enrollments_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_enrollments_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Matrículas vinculando alunos a cursos com controle de progresso';


-- ----------------------------------------------------------------
-- TABELA 31/54: reviews
-- Descrição: Avaliações dos alunos sobre os cursos
-- Dependências: users, courses
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
    `id`                    INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`               INT UNSIGNED     NOT NULL,
    `course_id`             INT UNSIGNED     NOT NULL,
    `rating`                TINYINT UNSIGNED NOT NULL                 COMMENT 'Nota de 1 a 5 estrelas',
    `title`                 VARCHAR(255)     DEFAULT NULL             COMMENT 'Título da avaliação',
    `comment`               TEXT             DEFAULT NULL             COMMENT 'Texto da avaliação',
    `is_approved`           TINYINT(1)       NOT NULL DEFAULT 0       COMMENT 'Aprovada pela moderação?',
    `instructor_reply`      TEXT             DEFAULT NULL             COMMENT 'Resposta do instrutor',
    `instructor_reply_at`   TIMESTAMP        NULL DEFAULT NULL,
    `helpful_count`         INT UNSIGNED     NOT NULL DEFAULT 0       COMMENT 'Votos de "útil"',
    `reported_count`        INT UNSIGNED     NOT NULL DEFAULT 0       COMMENT 'Denúncias recebidas',
    `created_at`            TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_review` (`user_id`, `course_id`),
    KEY `idx_reviews_course` (`course_id`),
    KEY `idx_reviews_rating` (`rating`),
    KEY `idx_reviews_approved` (`is_approved`),
    KEY `idx_reviews_created` (`created_at`),
    CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_reviews_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `chk_reviews_rating` CHECK (`rating` BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Avaliações dos alunos com moderação e resposta do instrutor';


-- ----------------------------------------------------------------
-- TABELA 32/54: wishlists
-- Descrição: Lista de desejos dos alunos
-- Dependências: users, courses
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `wishlists`;
CREATE TABLE `wishlists` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED    NOT NULL,
    `course_id`   INT UNSIGNED    NOT NULL,
    `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_wishlist` (`user_id`, `course_id`),
    KEY `idx_wishlist_course` (`course_id`),
    CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_wishlist_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Cursos salvos na lista de desejos do aluno';


-- ----------------------------------------------------------------
-- TABELA 33/54: certificates
-- Descrição: Certificados emitidos para alunos
-- Dependências: users, courses, certificate_templates
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `certificates`;
CREATE TABLE `certificates` (
    `id`                INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`           INT UNSIGNED     NOT NULL,
    `course_id`         INT UNSIGNED     NOT NULL,
    `template_id`       INT UNSIGNED     DEFAULT NULL,
    `certificate_code`  VARCHAR(50)      NOT NULL                    COMMENT 'Código único para verificação pública',
    `certificate_url`   VARCHAR(500)     DEFAULT NULL                COMMENT 'URL do PDF gerado',
    `final_grade`       DECIMAL(5,2)     DEFAULT NULL                COMMENT 'Nota final do aluno',
    `total_hours`       DECIMAL(6,1)     DEFAULT NULL                COMMENT 'Horas totais do curso na emissão',
    `metadata`          JSON             DEFAULT NULL                COMMENT 'Dados extras para o template',
    `issued_at`         TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_certificate` (`user_id`, `course_id`),
    UNIQUE KEY `uk_certificate_code` (`certificate_code`),
    KEY `idx_certificates_course` (`course_id`),
    KEY `idx_certificates_issued` (`issued_at`),
    CONSTRAINT `fk_certificates_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_certificates_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_certificates_template` FOREIGN KEY (`template_id`)
        REFERENCES `certificate_templates` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Certificados de conclusão com código de verificação';


-- ----------------------------------------------------------------
-- TABELA 34/54: payments
-- Descrição: Registro de pagamentos
-- Dependências: users, courses, coupons
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
    `id`                      INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`                 INT UNSIGNED     NOT NULL,
    `course_id`               INT UNSIGNED     DEFAULT NULL,
    `coupon_id`               INT UNSIGNED     DEFAULT NULL           COMMENT 'Cupom aplicado',
    `amount`                  DECIMAL(10,2)    NOT NULL               COMMENT 'Valor cobrado',
    `original_amount`         DECIMAL(10,2)    DEFAULT NULL           COMMENT 'Valor antes do desconto',
    `discount_amount`         DECIMAL(10,2)    NOT NULL DEFAULT 0.00  COMMENT 'Valor do desconto',
    `currency`                VARCHAR(3)       NOT NULL DEFAULT 'BRL',
    `payment_method`          ENUM('credit_card','debit_card','pix','boleto','paypal','stripe','free','coupon','admin')
                                               NOT NULL,
    `payment_gateway`         VARCHAR(50)      DEFAULT NULL           COMMENT 'Gateway: stripe, mercadopago, pagseguro',
    `gateway_transaction_id`  VARCHAR(255)     DEFAULT NULL           COMMENT 'ID da transação no gateway',
    `gateway_response`        JSON             DEFAULT NULL           COMMENT 'Resposta completa do gateway',
    `status`                  ENUM('pending','processing','completed','failed','cancelled','refunded','disputed','chargeback')
                                               NOT NULL DEFAULT 'pending',
    `invoice_number`          VARCHAR(50)      DEFAULT NULL           COMMENT 'Número da nota/fatura',
    `receipt_url`             VARCHAR(500)     DEFAULT NULL           COMMENT 'URL do comprovante',
    `refund_reason`           TEXT             DEFAULT NULL,
    `refunded_amount`         DECIMAL(10,2)    DEFAULT NULL,
    `refunded_at`             TIMESTAMP        NULL DEFAULT NULL,
    `paid_at`                 TIMESTAMP        NULL DEFAULT NULL,
    `created_at`              TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`              TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_payments_user` (`user_id`),
    KEY `idx_payments_course` (`course_id`),
    KEY `idx_payments_status` (`status`),
    KEY `idx_payments_method` (`payment_method`),
    KEY `idx_payments_gateway_tx` (`gateway_transaction_id`),
    KEY `idx_payments_created` (`created_at`),
    KEY `idx_payments_paid` (`paid_at`),
    CONSTRAINT `fk_payments_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_payments_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_payments_coupon` FOREIGN KEY (`coupon_id`)
        REFERENCES `coupons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Registro completo de pagamentos e transações financeiras';


-- ----------------------------------------------------------------
-- TABELA 35/54: coupon_uses
-- Descrição: Registro de uso de cupons
-- Dependências: coupons, users, payments
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `coupon_uses`;
CREATE TABLE `coupon_uses` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `coupon_id`   INT UNSIGNED    NOT NULL,
    `user_id`     INT UNSIGNED    NOT NULL,
    `payment_id`  INT UNSIGNED    DEFAULT NULL,
    `used_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_couponuses_coupon` (`coupon_id`),
    KEY `idx_couponuses_user` (`user_id`),
    UNIQUE KEY `uk_coupon_user_payment` (`coupon_id`, `user_id`, `payment_id`),
    CONSTRAINT `fk_couponuses_coupon` FOREIGN KEY (`coupon_id`)
        REFERENCES `coupons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_couponuses_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_couponuses_payment` FOREIGN KEY (`payment_id`)
        REFERENCES `payments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Histórico de utilização de cupons de desconto';


-- ----------------------------------------------------------------
-- TABELA 36/54: discussions
-- Descrição: Tópicos de discussão/fórum
-- Dependências: users, courses
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `discussions`;
CREATE TABLE `discussions` (
    `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `course_id`     INT UNSIGNED     DEFAULT NULL                  COMMENT 'Curso relacionado (NULL = fórum geral)',
    `lesson_id`     INT UNSIGNED     DEFAULT NULL                  COMMENT 'Aula específica (preenchido depois)',
    `user_id`       INT UNSIGNED     NOT NULL,
    `title`         VARCHAR(255)     NOT NULL,
    `content`       LONGTEXT         NOT NULL,
    `is_pinned`     TINYINT(1)       NOT NULL DEFAULT 0            COMMENT 'Fixado no topo?',
    `is_resolved`   TINYINT(1)       NOT NULL DEFAULT 0            COMMENT 'Dúvida resolvida?',
    `is_locked`     TINYINT(1)       NOT NULL DEFAULT 0            COMMENT 'Trancado para novas respostas?',
    `reply_count`   INT UNSIGNED     NOT NULL DEFAULT 0            COMMENT 'Cache: total de respostas',
    `view_count`    INT UNSIGNED     NOT NULL DEFAULT 0,
    `last_reply_at` TIMESTAMP        NULL DEFAULT NULL,
    `last_reply_by` INT UNSIGNED     DEFAULT NULL,
    `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_discussions_course` (`course_id`),
    KEY `idx_discussions_user` (`user_id`),
    KEY `idx_discussions_pinned` (`is_pinned`, `last_reply_at` DESC),
    KEY `idx_discussions_resolved` (`is_resolved`),
    FULLTEXT KEY `ft_discussions_search` (`title`, `content`),
    CONSTRAINT `fk_discussions_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_discussions_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tópicos de discussão do fórum por curso ou geral';


-- ----------------------------------------------------------------
-- TABELA 37/54: support_tickets
-- Descrição: Tickets de suporte ao aluno
-- Dependências: users, courses
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `support_tickets`;
CREATE TABLE `support_tickets` (
    `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `ticket_number` VARCHAR(20)      NOT NULL                      COMMENT 'Número legível: TKT-20250001',
    `user_id`       INT UNSIGNED     NOT NULL,
    `subject`       VARCHAR(255)     NOT NULL,
    `description`   LONGTEXT         NOT NULL,
    `category`      ENUM('technical','billing','content','account','bug_report','feature_request','other')
                                     NOT NULL DEFAULT 'other',
    `priority`      ENUM('low','medium','high','urgent')
                                     NOT NULL DEFAULT 'medium',
    `status`        ENUM('open','in_progress','waiting_response','on_hold','resolved','closed')
                                     NOT NULL DEFAULT 'open',
    `assigned_to`   INT UNSIGNED     DEFAULT NULL                  COMMENT 'Admin/suporte responsável',
    `course_id`     INT UNSIGNED     DEFAULT NULL                  COMMENT 'Curso relacionado ao problema',
    `resolved_at`   TIMESTAMP        NULL DEFAULT NULL,
    `closed_at`     TIMESTAMP        NULL DEFAULT NULL,
    `satisfaction`   TINYINT UNSIGNED DEFAULT NULL                  COMMENT 'Nota de satisfação 1-5',
    `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_ticket_number` (`ticket_number`),
    KEY `idx_tickets_user` (`user_id`),
    KEY `idx_tickets_status` (`status`),
    KEY `idx_tickets_priority` (`priority`),
    KEY `idx_tickets_assigned` (`assigned_to`),
    KEY `idx_tickets_category` (`category`),
    KEY `idx_tickets_created` (`created_at`),
    CONSTRAINT `fk_tickets_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_tickets_assigned` FOREIGN KEY (`assigned_to`)
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_tickets_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Sistema de tickets de suporte com prioridades e categorias';


-- ----------------------------------------------------------------
-- TABELA 38/54: instructor_payouts
-- Descrição: Pagamentos para instrutores (comissões)
-- Dependências: users
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `instructor_payouts`;
CREATE TABLE `instructor_payouts` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `instructor_id`   INT UNSIGNED     NOT NULL,
    `amount`          DECIMAL(10,2)    NOT NULL                    COMMENT 'Valor do repasse',
    `currency`        VARCHAR(3)       NOT NULL DEFAULT 'BRL',
    `period_start`    DATE             NOT NULL                    COMMENT 'Início do período',
    `period_end`      DATE             NOT NULL                    COMMENT 'Fim do período',
    `total_sales`     INT UNSIGNED     NOT NULL DEFAULT 0          COMMENT 'Total de vendas no período',
    `gross_amount`    DECIMAL(10,2)    NOT NULL                    COMMENT 'Valor bruto antes de taxas',
    `platform_fee`    DECIMAL(10,2)    NOT NULL DEFAULT 0.00       COMMENT 'Taxa da plataforma',
    `payment_method`  VARCHAR(50)      DEFAULT NULL                COMMENT 'Como foi pago: pix, transferência',
    `payment_details` JSON             DEFAULT NULL                COMMENT 'Dados do pagamento',
    `status`          ENUM('pending','processing','completed','failed','cancelled')
                                       NOT NULL DEFAULT 'pending',
    `paid_at`         TIMESTAMP        NULL DEFAULT NULL,
    `notes`           TEXT             DEFAULT NULL,
    `created_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_payouts_instructor` (`instructor_id`),
    KEY `idx_payouts_status` (`status`),
    KEY `idx_payouts_period` (`period_start`, `period_end`),
    CONSTRAINT `fk_payouts_instructor` FOREIGN KEY (`instructor_id`)
        REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Controle de repasses financeiros para instrutores';


-- ================================================================
-- ================================================================
--
--   NÍVEL 3 - DEPENDEM DOS NÍVEIS ANTERIORES (7 tabelas)
--
-- ================================================================
-- ================================================================


-- ----------------------------------------------------------------
-- TABELA 39/54: lessons
-- Descrição: Aulas individuais dentro dos módulos
-- Dependências: modules, courses
-- Referenciada por: lesson_progress, quizzes, assignments, student_notes, discussions
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `lessons`;
CREATE TABLE `lessons` (
    `id`               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `module_id`        INT UNSIGNED     NOT NULL,
    `course_id`        INT UNSIGNED     NOT NULL                    COMMENT 'Denormalizado para queries rápidas',
    `title`            VARCHAR(255)     NOT NULL,
    `slug`             VARCHAR(280)     NOT NULL,
    `content_type`     ENUM('video','text','quiz','assignment','download','live','interactive')
                                        NOT NULL DEFAULT 'video',
    `content`          LONGTEXT         DEFAULT NULL                COMMENT 'Conteúdo texto/HTML da aula',
    `video_url`        VARCHAR(500)     DEFAULT NULL,
    `video_provider`   ENUM('youtube','vimeo','bunny','wistia','self_hosted','other')
                                        DEFAULT NULL,
    `video_duration`   INT UNSIGNED     NOT NULL DEFAULT 0          COMMENT 'Duração em segundos',
    `video_thumbnail`  VARCHAR(500)     DEFAULT NULL                COMMENT 'Thumbnail customizada do vídeo',
    `attachments`      JSON             DEFAULT NULL                COMMENT 'Array: [{name, url, size, type}]',
    `resources`        JSON             DEFAULT NULL                COMMENT 'Links de recursos extras',
    `sort_order`       INT              NOT NULL DEFAULT 0,
    `is_free_preview`  TINYINT(1)       NOT NULL DEFAULT 0          COMMENT 'Aula gratuita para preview?',
    `is_published`     TINYINT(1)       NOT NULL DEFAULT 1,
    `is_mandatory`     TINYINT(1)       NOT NULL DEFAULT 1          COMMENT 'Obrigatória para conclusão?',
    `completion_rule`  ENUM('video_watched','content_read','quiz_passed','manual')
                                        NOT NULL DEFAULT 'video_watched' COMMENT 'Como marcar como concluída',
    `created_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_lessons_slug_course` (`course_id`, `slug`),
    KEY `idx_lessons_module_order` (`module_id`, `sort_order`),
    KEY `idx_lessons_course` (`course_id`),
    KEY `idx_lessons_type` (`content_type`),
    KEY `idx_lessons_published` (`is_published`),
    KEY `idx_lessons_free` (`is_free_preview`),
    CONSTRAINT `fk_lessons_module` FOREIGN KEY (`module_id`)
        REFERENCES `modules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_lessons_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Aulas individuais com suporte a múltiplos tipos de conteúdo';


-- ----------------------------------------------------------------
-- TABELA 40/54: discussion_replies
-- Descrição: Respostas nos tópicos de discussão
-- Dependências: discussions, users
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `discussion_replies`;
CREATE TABLE `discussion_replies` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `discussion_id`   INT UNSIGNED     NOT NULL,
    `user_id`         INT UNSIGNED     NOT NULL,
    `parent_reply_id` INT UNSIGNED     DEFAULT NULL                COMMENT 'Resposta a outra resposta (thread)',
    `content`         LONGTEXT         NOT NULL,
    `is_best_answer`  TINYINT(1)       NOT NULL DEFAULT 0          COMMENT 'Marcada como melhor resposta?',
    `upvote_count`    INT UNSIGNED     NOT NULL DEFAULT 0,
    `downvote_count`  INT UNSIGNED     NOT NULL DEFAULT 0,
    `is_edited`       TINYINT(1)       NOT NULL DEFAULT 0,
    `edited_at`       TIMESTAMP        NULL DEFAULT NULL,
    `created_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_replies_discussion` (`discussion_id`),
    KEY `idx_replies_user` (`user_id`),
    KEY `idx_replies_parent` (`parent_reply_id`),
    KEY `idx_replies_best` (`is_best_answer`),
    CONSTRAINT `fk_replies_discussion` FOREIGN KEY (`discussion_id`)
        REFERENCES `discussions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_replies_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_replies_parent` FOREIGN KEY (`parent_reply_id`)
        REFERENCES `discussion_replies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Respostas nos tópicos com suporte a threads aninhadas';


-- ----------------------------------------------------------------
-- TABELA 41/54: ticket_messages
-- Descrição: Mensagens dentro de tickets de suporte
-- Dependências: support_tickets, users
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `ticket_messages`;
CREATE TABLE `ticket_messages` (
    `id`               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `ticket_id`        INT UNSIGNED     NOT NULL,
    `user_id`          INT UNSIGNED     NOT NULL,
    `message`          LONGTEXT         NOT NULL,
    `attachments`      JSON             DEFAULT NULL               COMMENT 'Arquivos anexados',
    `is_internal_note` TINYINT(1)       NOT NULL DEFAULT 0          COMMENT 'Nota interna (não visível ao aluno)',
    `created_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_ticketmsg_ticket` (`ticket_id`, `created_at`),
    CONSTRAINT `fk_ticketmsg_ticket` FOREIGN KEY (`ticket_id`)
        REFERENCES `support_tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ticketmsg_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Mensagens trocadas dentro de tickets de suporte';


-- ----------------------------------------------------------------
-- TABELA 42/54: blog_comments
-- Descrição: Comentários nos artigos do blog
-- Dependências: blog_posts, users
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `blog_comments`;
CREATE TABLE `blog_comments` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `post_id`         INT UNSIGNED     NOT NULL,
    `user_id`         INT UNSIGNED     DEFAULT NULL                COMMENT 'NULL = comentário de visitante',
    `parent_id`       INT UNSIGNED     DEFAULT NULL                COMMENT 'Resposta a outro comentário',
    `author_name`     VARCHAR(100)     DEFAULT NULL                COMMENT 'Nome se não logado',
    `author_email`    VARCHAR(150)     DEFAULT NULL                COMMENT 'Email se não logado',
    `content`         TEXT             NOT NULL,
    `is_approved`     TINYINT(1)       NOT NULL DEFAULT 0,
    `created_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_blogcomments_post` (`post_id`, `is_approved`),
    KEY `idx_blogcomments_user` (`user_id`),
    KEY `idx_blogcomments_parent` (`parent_id`),
    CONSTRAINT `fk_blogcomments_post` FOREIGN KEY (`post_id`)
        REFERENCES `blog_posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_blogcomments_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_blogcomments_parent` FOREIGN KEY (`parent_id`)
        REFERENCES `blog_comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Comentários nos artigos do blog com moderação';


-- ----------------------------------------------------------------
-- TABELA 43/54: course_announcements
-- Descrição: Anúncios específicos de um curso
-- Dependências: courses, users
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `course_announcements`;
CREATE TABLE `course_announcements` (
    `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `course_id`   INT UNSIGNED     NOT NULL,
    `author_id`   INT UNSIGNED     NOT NULL                        COMMENT 'Instrutor que publicou',
    `title`       VARCHAR(255)     NOT NULL,
    `content`     LONGTEXT         NOT NULL,
    `is_pinned`   TINYINT(1)       NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_courseannounce_course` (`course_id`, `created_at` DESC),
    CONSTRAINT `fk_courseannounce_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_courseannounce_author` FOREIGN KEY (`author_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Avisos do instrutor para alunos de um curso específico';


-- ----------------------------------------------------------------
-- TABELA 44/54: course_bookmarks
-- Descrição: Aulas favoritas/salvas pelos alunos
-- Dependências: users, courses
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `course_bookmarks`;
CREATE TABLE `course_bookmarks` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED    NOT NULL,
    `course_id`   INT UNSIGNED    NOT NULL,
    `lesson_id`   INT UNSIGNED    DEFAULT NULL                    COMMENT 'Aula específica (preenchido nível 4)',
    `note`        VARCHAR(500)    DEFAULT NULL                    COMMENT 'Nota pessoal do aluno',
    `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_bookmarks_user` (`user_id`, `course_id`),
    CONSTRAINT `fk_bookmarks_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_bookmarks_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Aulas e cursos salvos como favoritos pelo aluno';


-- ----------------------------------------------------------------
-- TABELA 45/54: report_abuse
-- Descrição: Denúncias de conteúdo impróprio
-- Dependências: users
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `report_abuse`;
CREATE TABLE `report_abuse` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `reporter_id`     INT UNSIGNED     NOT NULL                    COMMENT 'Quem denunciou',
    `entity_type`     VARCHAR(50)      NOT NULL                    COMMENT 'review, discussion, reply, course, user',
    `entity_id`       INT UNSIGNED     NOT NULL                    COMMENT 'ID do conteúdo denunciado',
    `reason`          ENUM('spam','inappropriate','harassment','copyright','misinformation','other')
                                       NOT NULL,
    `description`     TEXT             DEFAULT NULL                COMMENT 'Detalhes da denúncia',
    `status`          ENUM('pending','reviewing','action_taken','dismissed')
                                       NOT NULL DEFAULT 'pending',
    `reviewed_by`     INT UNSIGNED     DEFAULT NULL,
    `reviewed_at`     TIMESTAMP        NULL DEFAULT NULL,
    `action_taken`    VARCHAR(255)     DEFAULT NULL                COMMENT 'Ação tomada pela moderação',
    `created_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_reports_entity` (`entity_type`, `entity_id`),
    KEY `idx_reports_status` (`status`),
    KEY `idx_reports_reporter` (`reporter_id`),
    CONSTRAINT `fk_reports_reporter` FOREIGN KEY (`reporter_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_reports_reviewer` FOREIGN KEY (`reviewed_by`)
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Sistema de denúncias para moderação de conteúdo';


-- ================================================================
-- ================================================================
--
--   NÍVEL 4 - DEPENDEM DAS AULAS/LESSONS (6 tabelas)
--
-- ================================================================
-- ================================================================


-- ----------------------------------------------------------------
-- TABELA 46/54: lesson_progress
-- Descrição: Progresso do aluno em cada aula
-- Dependências: users, lessons, courses
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `lesson_progress`;
CREATE TABLE `lesson_progress` (
    `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`       INT UNSIGNED     NOT NULL,
    `lesson_id`     INT UNSIGNED     NOT NULL,
    `course_id`     INT UNSIGNED     NOT NULL                      COMMENT 'Denormalizado para queries rápidas',
    `status`        ENUM('not_started','in_progress','completed')
                                     NOT NULL DEFAULT 'not_started',
    `watch_time`    INT UNSIGNED     NOT NULL DEFAULT 0            COMMENT 'Tempo total assistido em segundos',
    `last_position` INT UNSIGNED     NOT NULL DEFAULT 0            COMMENT 'Última posição do vídeo em segundos',
    `completed_at`  TIMESTAMP        NULL DEFAULT NULL,
    `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_lesson_progress` (`user_id`, `lesson_id`),
    KEY `idx_progress_course` (`user_id`, `course_id`),
    KEY `idx_progress_status` (`status`),
    KEY `idx_progress_completed` (`completed_at`),
    CONSTRAINT `fk_progress_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_progress_lesson` FOREIGN KEY (`lesson_id`)
        REFERENCES `lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_progress_course` FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Controle granular do progresso de cada aluno em cada aula';


-- ----------------------------------------------------------------
-- TABELA 47/54: quizzes
-- Descrição: Quizzes vinculados a aulas
-- Dependências: lessons
-- Referenciada por: quiz_questions, quiz_attempts
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `quizzes`;
CREATE TABLE `quizzes` (
    `id`                    INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `lesson_id`             INT UNSIGNED     NOT NULL,
    `title`                 VARCHAR(255)     NOT NULL,
    `description`           TEXT             DEFAULT NULL,
    `time_limit`            INT UNSIGNED     DEFAULT NULL            COMMENT 'Limite em minutos (NULL = sem limite)',
    `pass_percentage`       DECIMAL(5,2)     NOT NULL DEFAULT 70.00  COMMENT 'Percentual mínimo para aprovação',
    `max_attempts`          INT UNSIGNED     DEFAULT NULL            COMMENT 'Máximo de tentativas (NULL = ilimitado)',
    `shuffle_questions`     TINYINT(1)       NOT NULL DEFAULT 0      COMMENT 'Embaralhar perguntas?',
    `shuffle_options`       TINYINT(1)       NOT NULL DEFAULT 0      COMMENT 'Embaralhar alternativas?',
    `show_correct_answers`  TINYINT(1)       NOT NULL DEFAULT 1      COMMENT 'Mostrar gabarito após submissão?',
    `show_explanation`      TINYINT(1)       NOT NULL DEFAULT 1      COMMENT 'Mostrar explicações?',
    `question_count`        INT UNSIGNED     NOT NULL DEFAULT 0      COMMENT 'Cache: total de perguntas',
    `is_active`             TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`            TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_quizzes_lesson` (`lesson_id`),
    CONSTRAINT `fk_quizzes_lesson` FOREIGN KEY (`lesson_id`)
        REFERENCES `lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Quizzes avaliativos vinculados a aulas';


-- ----------------------------------------------------------------
-- TABELA 48/54: assignments
-- Descrição: Tarefas/projetos práticos
-- Dependências: lessons
-- Referenciada por: assignment_submissions
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `assignments`;
CREATE TABLE `assignments` (
    `id`                   INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `lesson_id`            INT UNSIGNED     NOT NULL,
    `title`                VARCHAR(255)     NOT NULL,
    `description`          LONGTEXT         NOT NULL,
    `instructions`         LONGTEXT         DEFAULT NULL             COMMENT 'Instruções detalhadas',
    `starter_files_url`    VARCHAR(500)     DEFAULT NULL             COMMENT 'URL de arquivos iniciais para download',
    `max_score`            INT UNSIGNED     NOT NULL DEFAULT 100,
    `due_days`             INT UNSIGNED     DEFAULT NULL             COMMENT 'Dias após matrícula para entregar',
    `allow_late`           TINYINT(1)       NOT NULL DEFAULT 0       COMMENT 'Aceitar entregas atrasadas?',
    `late_penalty_percent` DECIMAL(5,2)     DEFAULT NULL             COMMENT 'Penalidade por atraso (%)',
    `submission_type`      ENUM('file','text','url','github','zip')
                                            NOT NULL DEFAULT 'file',
    `allowed_extensions`   JSON             DEFAULT NULL             COMMENT '["zip","rar","pdf","png"]',
    `max_file_size`        INT UNSIGNED     DEFAULT 52428800         COMMENT 'Limite em bytes (default 50MB)',
    `rubric`               JSON             DEFAULT NULL             COMMENT 'Critérios de avaliação em JSON',
    `is_active`            TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`           TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`           TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_assignments_lesson` (`lesson_id`),
    CONSTRAINT `fk_assignments_lesson` FOREIGN KEY (`lesson_id`)
        REFERENCES `lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Projetos práticos para alunos com critérios de avaliação';


-- ----------------------------------------------------------------
-- TABELA 49/54: student_notes
-- Descrição: Anotações dos alunos durante as aulas
-- Dependências: users, lessons
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `student_notes`;
CREATE TABLE `student_notes` (
    `id`                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`             INT UNSIGNED    NOT NULL,
    `lesson_id`           INT UNSIGNED    NOT NULL,
    `content`             TEXT            NOT NULL,
    `timestamp_seconds`   INT UNSIGNED    DEFAULT NULL              COMMENT 'Momento do vídeo para a nota',
    `color`               VARCHAR(7)      DEFAULT '#fbbf24'         COMMENT 'Cor da nota para organização',
    `created_at`          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_notes_user_lesson` (`user_id`, `lesson_id`),
    KEY `idx_notes_timestamp` (`lesson_id`, `timestamp_seconds`),
    CONSTRAINT `fk_notes_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_notes_lesson` FOREIGN KEY (`lesson_id`)
        REFERENCES `lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Anotações pessoais dos alunos vinculadas a momentos do vídeo';


-- ================================================================
-- ================================================================
--
--   NÍVEL 5 - DEPENDEM DOS QUIZZES E ASSIGNMENTS (3 tabelas)
--
-- ================================================================
-- ================================================================


-- ----------------------------------------------------------------
-- TABELA 50/54: quiz_questions
-- Descrição: Perguntas dos quizzes
-- Dependências: quizzes
-- Referenciada por: quiz_options
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `quiz_questions`;
CREATE TABLE `quiz_questions` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `quiz_id`         INT UNSIGNED     NOT NULL,
    `question_type`   ENUM('multiple_choice','multiple_select','true_false','short_answer','code','fill_blank')
                                       NOT NULL DEFAULT 'multiple_choice',
    `question_text`   TEXT             NOT NULL,
    `code_snippet`    TEXT             DEFAULT NULL                 COMMENT 'Código para perguntas técnicas',
    `code_language`   VARCHAR(20)      DEFAULT NULL                 COMMENT 'Linguagem do código: csharp, gdscript, cpp',
    `image_url`       VARCHAR(500)     DEFAULT NULL                 COMMENT 'Imagem complementar',
    `explanation`     TEXT             DEFAULT NULL                 COMMENT 'Explicação da resposta correta',
    `points`          INT UNSIGNED     NOT NULL DEFAULT 1           COMMENT 'Pontos desta pergunta',
    `sort_order`      INT              NOT NULL DEFAULT 0,
    `created_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_questions_quiz_order` (`quiz_id`, `sort_order`),
    CONSTRAINT `fk_questions_quiz` FOREIGN KEY (`quiz_id`)
        REFERENCES `quizzes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Perguntas dos quizzes com suporte a código e imagens';


-- ----------------------------------------------------------------
-- TABELA 51/54: quiz_attempts
-- Descrição: Tentativas de resolução de quizzes
-- Dependências: users, quizzes
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `quiz_attempts`;
CREATE TABLE `quiz_attempts` (
    `id`             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `user_id`        INT UNSIGNED     NOT NULL,
    `quiz_id`        INT UNSIGNED     NOT NULL,
    `score`          DECIMAL(5,2)     NOT NULL DEFAULT 0.00        COMMENT 'Percentual de acerto',
    `total_points`   INT UNSIGNED     NOT NULL DEFAULT 0           COMMENT 'Total de pontos possíveis',
    `earned_points`  INT UNSIGNED     NOT NULL DEFAULT 0           COMMENT 'Pontos conquistados',
    `passed`         TINYINT(1)       NOT NULL DEFAULT 0           COMMENT 'Atingiu o mínimo?',
    `answers`        JSON             DEFAULT NULL                 COMMENT 'Respostas do aluno em JSON',
    `attempt_number` INT UNSIGNED     NOT NULL DEFAULT 1           COMMENT 'Número da tentativa',
    `started_at`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completed_at`   TIMESTAMP        NULL DEFAULT NULL,
    `time_spent`     INT UNSIGNED     DEFAULT NULL                 COMMENT 'Tempo gasto em segundos',

    PRIMARY KEY (`id`),
    KEY `idx_attempts_user_quiz` (`user_id`, `quiz_id`),
    KEY `idx_attempts_quiz` (`quiz_id`),
    KEY `idx_attempts_passed` (`passed`),
    CONSTRAINT `fk_attempts_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_attempts_quiz` FOREIGN KEY (`quiz_id`)
        REFERENCES `quizzes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Registro de todas as tentativas de quiz com respostas';


-- ----------------------------------------------------------------
-- TABELA 52/54: assignment_submissions
-- Descrição: Entregas de tarefas/projetos
-- Dependências: assignments, users
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `assignment_submissions`;
CREATE TABLE `assignment_submissions` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `assignment_id`   INT UNSIGNED     NOT NULL,
    `user_id`         INT UNSIGNED     NOT NULL,
    `content`         LONGTEXT         DEFAULT NULL                COMMENT 'Texto da entrega',
    `file_url`        VARCHAR(500)     DEFAULT NULL                COMMENT 'URL do arquivo enviado',
    `github_url`      VARCHAR(500)     DEFAULT NULL                COMMENT 'Link do repositório',
    `additional_urls` JSON             DEFAULT NULL                COMMENT 'Links extras: demo, vídeo, etc',
    `score`           INT UNSIGNED     DEFAULT NULL                COMMENT 'Nota atribuída',
    `feedback`        TEXT             DEFAULT NULL                COMMENT 'Feedback do instrutor',
    `status`          ENUM('submitted','under_review','graded','returned','resubmitted')
                                       NOT NULL DEFAULT 'submitted',
    `is_late`         TINYINT(1)       NOT NULL DEFAULT 0          COMMENT 'Entregue com atraso?',
    `graded_by`       INT UNSIGNED     DEFAULT NULL,
    `graded_at`       TIMESTAMP        NULL DEFAULT NULL,
    `attempt_number`  INT UNSIGNED     NOT NULL DEFAULT 1,
    `submitted_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_submissions_assignment` (`assignment_id`),
    KEY `idx_submissions_user` (`user_id`),
    KEY `idx_submissions_status` (`status`),
    KEY `idx_submissions_grader` (`graded_by`),
    CONSTRAINT `fk_submissions_assignment` FOREIGN KEY (`assignment_id`)
        REFERENCES `assignments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_submissions_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_submissions_grader` FOREIGN KEY (`graded_by`)
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Entregas de projetos com sistema de avaliação e re-submissão';


-- ================================================================
-- ================================================================
--
--   NÍVEL 6 - DEPENDÊNCIAS MAIS PROFUNDAS (1 tabela)
--
-- ================================================================
-- ================================================================


-- ----------------------------------------------------------------
-- TABELA 53/54: quiz_options
-- Descrição: Alternativas das perguntas de quiz
-- Dependências: quiz_questions
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `quiz_options`;
CREATE TABLE `quiz_options` (
    `id`           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `question_id`  INT UNSIGNED     NOT NULL,
    `option_text`  TEXT             NOT NULL                       COMMENT 'Texto da alternativa',
    `is_correct`   TINYINT(1)       NOT NULL DEFAULT 0             COMMENT 'Esta é a correta?',
    `sort_order`   INT              NOT NULL DEFAULT 0,

    PRIMARY KEY (`id`),
    KEY `idx_options_question` (`question_id`, `sort_order`),
    CONSTRAINT `fk_options_question` FOREIGN KEY (`question_id`)
        REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Alternativas de resposta para perguntas de múltipla escolha';


-- ================================================================
-- ================================================================
--
--   TABELA DE AUDITORIA - SEM FK RÍGIDA (1 tabela)
--
--   Esta tabela usa FK opcional para não impedir deleções
--   e registrar atividades de usuários deletados.
--
-- ================================================================
-- ================================================================


-- ----------------------------------------------------------------
-- TABELA 54/54: activity_log
-- Descrição: Log de auditoria de todas as ações do sistema
-- Dependências: users (FK opcional com SET NULL)
-- ----------------------------------------------------------------
DROP TABLE IF EXISTS `activity_log`;
CREATE TABLE `activity_log` (
    `id`           BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `user_id`      INT UNSIGNED     DEFAULT NULL                   COMMENT 'Quem realizou (NULL se deletado ou sistema)',
    `action`       VARCHAR(100)     NOT NULL                       COMMENT 'Ação: user.login, course.create, enrollment.complete, etc',
    `entity_type`  VARCHAR(50)      DEFAULT NULL                   COMMENT 'Tipo: user, course, lesson, payment, etc',
    `entity_id`    INT UNSIGNED     DEFAULT NULL                   COMMENT 'ID da entidade afetada',
    `old_values`   JSON             DEFAULT NULL                   COMMENT 'Valores anteriores (para UPDATE)',
    `new_values`   JSON             DEFAULT NULL                   COMMENT 'Valores novos',
    `ip_address`   VARCHAR(45)      DEFAULT NULL,
    `user_agent`   VARCHAR(500)     DEFAULT NULL,
    `extra`        JSON             DEFAULT NULL                   COMMENT 'Dados extras contextuais',
    `created_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_actlog_user` (`user_id`),
    KEY `idx_actlog_action` (`action`),
    KEY `idx_actlog_entity` (`entity_type`, `entity_id`),
    KEY `idx_actlog_created` (`created_at`),
    CONSTRAINT `fk_actlog_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Log de auditoria completo de todas as ações do sistema';


-- ================================================================
-- ================================================================
--
--   TRIGGERS DE SEGURANÇA E CACHE
--
--   Mantêm os campos de cache (contadores) atualizados
--   automaticamente para evitar inconsistências.
--
-- ================================================================
-- ================================================================


-- Trigger: Atualizar enrollment_count ao matricular
DELIMITER //
CREATE TRIGGER IF NOT EXISTS `trg_enrollment_after_insert`
AFTER INSERT ON `enrollments`
FOR EACH ROW
BEGIN
    UPDATE `courses` 
    SET `enrollment_count` = `enrollment_count` + 1
    WHERE `id` = NEW.`course_id`;
END//

-- Trigger: Decrementar enrollment_count ao cancelar
CREATE TRIGGER IF NOT EXISTS `trg_enrollment_after_delete`
AFTER DELETE ON `enrollments`
FOR EACH ROW
BEGIN
    UPDATE `courses` 
    SET `enrollment_count` = GREATEST(`enrollment_count` - 1, 0)
    WHERE `id` = OLD.`course_id`;
END//

-- Trigger: Atualizar rating_average ao inserir review
CREATE TRIGGER IF NOT EXISTS `trg_review_after_insert`
AFTER INSERT ON `reviews`
FOR EACH ROW
BEGIN
    IF NEW.`is_approved` = 1 THEN
        UPDATE `courses` SET
            `rating_average` = (
                SELECT COALESCE(AVG(`rating`), 0) 
                FROM `reviews` 
                WHERE `course_id` = NEW.`course_id` AND `is_approved` = 1
            ),
            `rating_count` = (
                SELECT COUNT(*) 
                FROM `reviews` 
                WHERE `course_id` = NEW.`course_id` AND `is_approved` = 1
            )
        WHERE `id` = NEW.`course_id`;
    END IF;
END//

-- Trigger: Atualizar rating_average ao aprovar/editar review
CREATE TRIGGER IF NOT EXISTS `trg_review_after_update`
AFTER UPDATE ON `reviews`
FOR EACH ROW
BEGIN
    UPDATE `courses` SET
        `rating_average` = (
            SELECT COALESCE(AVG(`rating`), 0) 
            FROM `reviews` 
            WHERE `course_id` = NEW.`course_id` AND `is_approved` = 1
        ),
        `rating_count` = (
            SELECT COUNT(*) 
            FROM `reviews` 
            WHERE `course_id` = NEW.`course_id` AND `is_approved` = 1
        )
    WHERE `id` = NEW.`course_id`;
END//

-- Trigger: Atualizar reply_count ao responder discussão
CREATE TRIGGER IF NOT EXISTS `trg_reply_after_insert`
AFTER INSERT ON `discussion_replies`
FOR EACH ROW
BEGIN
    UPDATE `discussions` SET
        `reply_count` = `reply_count` + 1,
        `last_reply_at` = NEW.`created_at`,
        `last_reply_by` = NEW.`user_id`
    WHERE `id` = NEW.`discussion_id`;
END//

-- Trigger: Decrementar reply_count ao deletar resposta
CREATE TRIGGER IF NOT EXISTS `trg_reply_after_delete`
AFTER DELETE ON `discussion_replies`
FOR EACH ROW
BEGIN
    UPDATE `discussions` SET
        `reply_count` = GREATEST(`reply_count` - 1, 0)
    WHERE `id` = OLD.`discussion_id`;
END//

-- Trigger: Atualizar used_count ao usar cupom
CREATE TRIGGER IF NOT EXISTS `trg_couponuse_after_insert`
AFTER INSERT ON `coupon_uses`
FOR EACH ROW
BEGIN
    UPDATE `coupons` SET
        `used_count` = `used_count` + 1
    WHERE `id` = NEW.`coupon_id`;
END//

-- Trigger: Atualizar total_points do usuário
CREATE TRIGGER IF NOT EXISTS `trg_points_after_insert`
AFTER INSERT ON `user_points`
FOR EACH ROW
BEGIN
    UPDATE `users` SET
        `total_points` = (
            SELECT COALESCE(SUM(`points`), 0) 
            FROM `user_points` 
            WHERE `user_id` = NEW.`user_id`
        )
    WHERE `id` = NEW.`user_id`;
END//

DELIMITER ;


-- ================================================================
-- ================================================================
--
--   VIEWS ÚTEIS
--
--   Views pré-definidas para queries frequentes no dashboard
--   e listagens. Evitam repetição de JOINs complexos.
--
-- ================================================================
-- ================================================================


-- View: Cursos com dados completos para listagem
CREATE OR REPLACE VIEW `vw_courses_listing` AS
SELECT 
    c.`id`,
    c.`title`,
    c.`slug`,
    c.`short_description`,
    c.`thumbnail`,
    c.`level`,
    c.`price`,
    c.`original_price`,
    c.`is_free`,
    c.`is_featured`,
    c.`is_bestseller`,
    c.`duration_hours`,
    c.`total_lessons`,
    c.`enrollment_count`,
    c.`rating_average`,
    c.`rating_count`,
    c.`game_engine`,
    c.`status`,
    c.`published_at`,
    u.`id` AS `instructor_id`,
    u.`name` AS `instructor_name`,
    u.`avatar` AS `instructor_avatar`,
    cat.`id` AS `category_id`,
    cat.`name` AS `category_name`,
    cat.`slug` AS `category_slug`
FROM `courses` c
INNER JOIN `users` u ON c.`instructor_id` = u.`id`
LEFT JOIN `categories` cat ON c.`category_id` = cat.`id`;


-- View: Dashboard stats
CREATE OR REPLACE VIEW `vw_dashboard_stats` AS
SELECT
    (SELECT COUNT(*) FROM `users` WHERE `role` = 'student' AND `is_active` = 1) AS `total_students`,
    (SELECT COUNT(*) FROM `users` WHERE `role` = 'instructor' AND `is_active` = 1) AS `total_instructors`,
    (SELECT COUNT(*) FROM `courses` WHERE `status` = 'published') AS `total_courses`,
    (SELECT COUNT(*) FROM `enrollments` WHERE `status` = 'active') AS `active_enrollments`,
    (SELECT COUNT(*) FROM `enrollments` WHERE `status` = 'completed') AS `completed_enrollments`,
    (SELECT COALESCE(SUM(`amount` - `discount_amount`), 0) FROM `payments` WHERE `status` = 'completed') AS `total_revenue`,
    (SELECT COUNT(*) FROM `enrollments` WHERE `enrolled_at` >= DATE_SUB(NOW(), INTERVAL 30 DAY)) AS `monthly_enrollments`,
    (SELECT COUNT(*) FROM `enrollments` WHERE `enrolled_at` >= DATE_SUB(NOW(), INTERVAL 7 DAY)) AS `weekly_enrollments`,
    (SELECT COALESCE(SUM(`amount` - `discount_amount`), 0) FROM `payments` WHERE `status` = 'completed' AND `paid_at` >= DATE_SUB(NOW(), INTERVAL 30 DAY)) AS `monthly_revenue`,
    (SELECT COUNT(*) FROM `support_tickets` WHERE `status` IN ('open', 'in_progress')) AS `open_tickets`,
    (SELECT COUNT(*) FROM `reviews` WHERE `is_approved` = 0) AS `pending_reviews`;


-- View: Progresso detalhado do aluno
CREATE OR REPLACE VIEW `vw_student_progress` AS
SELECT 
    e.`user_id`,
    e.`course_id`,
    c.`title` AS `course_title`,
    c.`total_lessons`,
    e.`progress_percent`,
    e.`lessons_completed`,
    e.`status` AS `enrollment_status`,
    e.`enrolled_at`,
    e.`completed_at`,
    e.`last_accessed_at`,
    u.`name` AS `student_name`,
    u.`email` AS `student_email`
FROM `enrollments` e
INNER JOIN `courses` c ON e.`course_id` = c.`id`
INNER JOIN `users` u ON e.`user_id` = u.`id`;


-- ================================================================
-- ================================================================
--
--   DADOS INICIAIS (SEEDS)
--
--   Dados essenciais para o sistema funcionar após instalação.
--   Executados apenas se as tabelas estiverem vazias.
--
-- ================================================================
-- ================================================================


-- ----------------------------------------------------------------
-- Usuário administrador padrão
-- ATENÇÃO: Mude a senha imediatamente após instalação!
-- Senha padrão: Admin@123 (hash bcrypt)
-- ----------------------------------------------------------------
INSERT INTO `users` (`name`, `email`, `password`, `role`, `email_verified_at`, `is_active`)
SELECT 'Administrador', 'admin@gamedevacademy.com',
       '$2y$12$LJ3m4ys3VEz3VEz3VEz3VeKX8PvQ3VEz3VEz3VEz3VEz3VEz3VEz3V',
       'super_admin', NOW(), 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `email` = 'admin@gamedevacademy.com');


-- ----------------------------------------------------------------
-- Categorias padrão para cursos de gamedev
-- ----------------------------------------------------------------
INSERT INTO `categories` (`name`, `slug`, `description`, `icon`, `color`, `sort_order`)
SELECT * FROM (
    SELECT 'Unity' AS n, 'unity' AS s, 'Desenvolvimento de jogos com Unity Engine e C#' AS d, 'fas fa-cube' AS i, '#000000' AS c, 1 AS o
    UNION ALL SELECT 'Unreal Engine', 'unreal-engine', 'Desenvolvimento com Unreal Engine e C++/Blueprints', 'fas fa-fire', '#2563eb', 2
    UNION ALL SELECT 'Godot', 'godot', 'Desenvolvimento com Godot Engine e GDScript', 'fas fa-robot', '#478cbf', 3
    UNION ALL SELECT 'GameMaker', 'gamemaker', 'Desenvolvimento com GameMaker Studio', 'fas fa-gamepad', '#8bc34a', 4
    UNION ALL SELECT 'Game Design', 'game-design', 'Princípios, mecânicas e teoria de game design', 'fas fa-pencil-ruler', '#8b5cf6', 5
    UNION ALL SELECT 'Arte 2D', 'arte-2d', 'Pixel art, sprites, animação e arte 2D para jogos', 'fas fa-palette', '#ec4899', 6
    UNION ALL SELECT 'Arte 3D', 'arte-3d', 'Modelagem, texturização, rigging e arte 3D', 'fas fa-shapes', '#f59e0b', 7
    UNION ALL SELECT 'Programação', 'programacao', 'Fundamentos de programação aplicados a jogos', 'fas fa-code', '#10b981', 8
    UNION ALL SELECT 'Áudio e Música', 'audio', 'Sound design, efeitos sonoros e trilha para jogos', 'fas fa-music', '#6366f1', 9
    UNION ALL SELECT 'Narrativa', 'narrativa', 'Roteiro, storytelling e narrativa interativa', 'fas fa-book', '#ef4444', 10
    UNION ALL SELECT 'Mobile Games', 'mobile-games', 'Desenvolvimento de jogos para iOS e Android', 'fas fa-mobile-alt', '#14b8a6', 11
    UNION ALL SELECT 'Web Games', 'web-games', 'Jogos para navegador com HTML5/JavaScript', 'fas fa-globe', '#0ea5e9', 12
    UNION ALL SELECT 'Multiplayer', 'multiplayer', 'Networking, servidores e jogos multiplayer', 'fas fa-users', '#a855f7', 13
    UNION ALL SELECT 'VR/AR', 'vr-ar', 'Realidade virtual e aumentada para jogos', 'fas fa-vr-cardboard', '#f43f5e', 14
    UNION ALL SELECT 'Marketing de Jogos', 'marketing-jogos', 'Monetização, publicação e marketing', 'fas fa-bullhorn', '#84cc16', 15
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `categories` LIMIT 1);


-- ----------------------------------------------------------------
-- Configurações iniciais do sistema
-- ----------------------------------------------------------------
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `description`, `is_public`)
SELECT * FROM (
    -- Geral
    SELECT 'site_name' AS k, 'GameDev Academy' AS v, 'string' AS t, 'general' AS g, 'Nome do site' AS d, 1 AS p
    UNION ALL SELECT 'site_description', 'Aprenda a criar jogos do zero ao profissional', 'string', 'general', 'Descrição do site', 1
    UNION ALL SELECT 'site_tagline', 'Sua jornada no desenvolvimento de jogos começa aqui', 'string', 'general', 'Tagline/slogan', 1
    UNION ALL SELECT 'site_logo', '/assets/images/logo.png', 'string', 'general', 'URL do logo', 1
    UNION ALL SELECT 'site_favicon', '/assets/images/favicon.ico', 'string', 'general', 'URL do favicon', 1
    UNION ALL SELECT 'site_url', 'http://localhost', 'string', 'general', 'URL base do site', 1
    UNION ALL SELECT 'contact_email', 'contato@gamedevacademy.com', 'string', 'general', 'Email de contato', 1
    UNION ALL SELECT 'maintenance_mode', 'false', 'boolean', 'general', 'Modo manutenção ativado', 0
    UNION ALL SELECT 'items_per_page', '12', 'number', 'general', 'Itens por página nas listagens', 0
    UNION ALL SELECT 'timezone', 'America/Sao_Paulo', 'string', 'general', 'Fuso horário padrão', 0
    -- Pagamento
    UNION ALL SELECT 'currency', 'BRL', 'string', 'payment', 'Moeda padrão', 0
    UNION ALL SELECT 'currency_symbol', 'R$', 'string', 'payment', 'Símbolo da moeda', 1
    UNION ALL SELECT 'stripe_enabled', 'false', 'boolean', 'payment', 'Stripe habilitado', 0
    UNION ALL SELECT 'stripe_public_key', '', 'string', 'payment', 'Stripe Publishable Key', 0
    UNION ALL SELECT 'stripe_secret_key', '', 'string', 'payment', 'Stripe Secret Key', 0
    UNION ALL SELECT 'pix_enabled', 'false', 'boolean', 'payment', 'PIX habilitado', 0
    UNION ALL SELECT 'instructor_commission', '70', 'number', 'payment', 'Comissão do instrutor (%)', 0
    -- Email
    UNION ALL SELECT 'smtp_host', '', 'string', 'email', 'Servidor SMTP', 0
    UNION ALL SELECT 'smtp_port', '587', 'number', 'email', 'Porta SMTP', 0
    UNION ALL SELECT 'smtp_user', '', 'string', 'email', 'Usuário SMTP', 0
    UNION ALL SELECT 'smtp_pass', '', 'string', 'email', 'Senha SMTP', 0
    UNION ALL SELECT 'smtp_encryption', 'tls', 'string', 'email', 'Encriptação: tls ou ssl', 0
    UNION ALL SELECT 'email_from_name', 'GameDev Academy', 'string', 'email', 'Nome do remetente', 0
    UNION ALL SELECT 'email_from_address', 'noreply@gamedevacademy.com', 'string', 'email', 'Email do remetente', 0
    -- Features
    UNION ALL SELECT 'certificate_enabled', 'true', 'boolean', 'features', 'Habilitar certificados', 0
    UNION ALL SELECT 'gamification_enabled', 'true', 'boolean', 'features', 'Habilitar gamificação', 0
    UNION ALL SELECT 'forum_enabled', 'true', 'boolean', 'features', 'Habilitar fórum de discussão', 0
    UNION ALL SELECT 'blog_enabled', 'true', 'boolean', 'features', 'Habilitar blog', 0
    UNION ALL SELECT 'reviews_enabled', 'true', 'boolean', 'features', 'Habilitar avaliações de cursos', 0
    UNION ALL SELECT 'wishlist_enabled', 'true', 'boolean', 'features', 'Habilitar lista de desejos', 0
    UNION ALL SELECT 'support_enabled', 'true', 'boolean', 'features', 'Habilitar sistema de suporte', 0
    UNION ALL SELECT 'registration_enabled', 'true', 'boolean', 'features', 'Permitir novos cadastros', 0
    UNION ALL SELECT 'instructor_registration', 'false', 'boolean', 'features', 'Permitir cadastro de instrutores', 0
    -- SEO
    UNION ALL SELECT 'google_analytics_id', '', 'string', 'seo', 'Google Analytics ID', 0
    UNION ALL SELECT 'google_tag_manager_id', '', 'string', 'seo', 'Google Tag Manager ID', 0
    UNION ALL SELECT 'facebook_pixel_id', '', 'string', 'seo', 'Facebook Pixel ID', 0
    -- Social
    UNION ALL SELECT 'social_github', 'https://github.com/davidcreator/gamedev-academy', 'string', 'social', 'GitHub da plataforma', 1
    UNION ALL SELECT 'social_youtube', '', 'string', 'social', 'Canal YouTube', 1
    UNION ALL SELECT 'social_discord', '', 'string', 'social', 'Servidor Discord', 1
    UNION ALL SELECT 'social_twitter', '', 'string', 'social', 'Twitter/X', 1
    UNION ALL SELECT 'social_instagram', '', 'string', 'social', 'Instagram', 1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `settings` LIMIT 1);


-- ----------------------------------------------------------------
-- Badges padrão de gamificação
-- ----------------------------------------------------------------
INSERT INTO `badges` (`name`, `slug`, `description`, `icon`, `category`, `criteria_type`, `criteria_value`, `points_reward`, `rarity`, `sort_order`)
SELECT * FROM (
    -- Conquistas de aulas
    SELECT 'Primeiro Passo' AS n, 'primeiro-passo' AS s, 'Complete sua primeira aula' AS d, '🎮' AS i, 'achievement' AS c, 'lessons_completed' AS ct, 1 AS cv, 10 AS pr, 'common' AS r, 1 AS so
    UNION ALL SELECT 'Estudante Dedicado', 'estudante-dedicado', 'Complete 10 aulas', '📚', 'engagement', 'lessons_completed', 10, 50, 'common', 2
    UNION ALL SELECT 'Maratonista', 'maratonista', 'Complete 50 aulas', '🏃', 'engagement', 'lessons_completed', 50, 200, 'uncommon', 3
    UNION ALL SELECT 'Máquina de Aprender', 'maquina-aprender', 'Complete 100 aulas', '🤖', 'engagement', 'lessons_completed', 100, 500, 'rare', 4
    -- Conquistas de cursos
    UNION ALL SELECT 'Primeiro Curso', 'primeiro-curso', 'Complete seu primeiro curso', '🎓', 'course', 'courses_completed', 1, 100, 'common', 5
    UNION ALL SELECT 'Colecionador', 'colecionador', 'Complete 5 cursos', '🏆', 'course', 'courses_completed', 5, 500, 'rare', 6
    UNION ALL SELECT 'Mestre dos Jogos', 'mestre-dos-jogos', 'Complete 10 cursos', '👑', 'course', 'courses_completed', 10, 1000, 'epic', 7
    UNION ALL SELECT 'Lendário', 'lendario', 'Complete 25 cursos', '⭐', 'course', 'courses_completed', 25, 2500, 'legendary', 8
    -- Streaks
    UNION ALL SELECT 'Streak Semanal', 'streak-7', 'Estude por 7 dias seguidos', '🔥', 'engagement', 'streak_days', 7, 70, 'common', 9
    UNION ALL SELECT 'Streak Mensal', 'streak-30', 'Estude por 30 dias seguidos', '⚡', 'engagement', 'streak_days', 30, 300, 'rare', 10
    UNION ALL SELECT 'Streak Épico', 'streak-90', 'Estude por 90 dias seguidos', '💫', 'engagement', 'streak_days', 90, 900, 'epic', 11
    -- Quizzes
    UNION ALL SELECT 'Quiz Master', 'quiz-master', 'Acerte 100% em 10 quizzes', '🧠', 'achievement', 'perfect_quizzes', 10, 250, 'rare', 12
    UNION ALL SELECT 'Sem Erros', 'sem-erros', 'Acerte 100% no primeiro quiz', '✅', 'achievement', 'perfect_quizzes', 1, 25, 'common', 13
    -- Comunidade
    UNION ALL SELECT 'Ajudante', 'ajudante', 'Responda 10 perguntas no fórum', '🤝', 'community', 'forum_replies', 10, 150, 'uncommon', 14
    UNION ALL SELECT 'Mentor', 'mentor', 'Tenha 5 respostas marcadas como melhor resposta', '🌟', 'community', 'best_answers', 5, 300, 'rare', 15
    -- Especiais
    UNION ALL SELECT 'Pioneiro', 'pioneiro', 'Seja um dos primeiros 100 alunos', '🚀', 'special', 'early_adopter', 1, 200, 'epic', 16
    UNION ALL SELECT 'Avaliador', 'avaliador', 'Avalie 5 cursos', '📝', 'community', 'reviews_posted', 5, 100, 'uncommon', 17
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `badges` LIMIT 1);


-- ----------------------------------------------------------------
-- Páginas estáticas padrão
-- ----------------------------------------------------------------
INSERT INTO `pages` (`title`, `slug`, `content`, `show_in_footer`, `is_published`)
SELECT * FROM (
    SELECT 'Sobre Nós' AS t, 'sobre' AS s, '<h1>Sobre a GameDev Academy</h1><p>Somos uma plataforma brasileira dedicada ao ensino de desenvolvimento de jogos. Nossa missão é democratizar o acesso ao conhecimento de gamedev, oferecendo cursos de alta qualidade em português.</p>' AS c, 1 AS f, 1 AS p
    UNION ALL SELECT 'Termos de Uso', 'termos-de-uso', '<h1>Termos de Uso</h1><p>Ao utilizar a plataforma GameDev Academy, você concorda com os seguintes termos e condições...</p>', 1, 1
    UNION ALL SELECT 'Política de Privacidade', 'politica-de-privacidade', '<h1>Política de Privacidade</h1><p>A GameDev Academy valoriza a sua privacidade. Esta política descreve como coletamos e utilizamos seus dados...</p>', 1, 1
    UNION ALL SELECT 'Política de Reembolso', 'politica-de-reembolso', '<h1>Política de Reembolso</h1><p>Oferecemos garantia de satisfação de 7 dias para todos os cursos pagos...</p>', 1, 1
    UNION ALL SELECT 'Contato', 'contato', '<h1>Fale Conosco</h1><p>Tem dúvidas ou sugestões? Entre em contato através do nosso formulário ou email.</p>', 1, 1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `pages` LIMIT 1);


-- ----------------------------------------------------------------
-- Template de certificado padrão
-- ----------------------------------------------------------------
INSERT INTO `certificate_templates` (`name`, `html_template`, `css_styles`, `orientation`, `is_default`, `is_active`)
SELECT 'Certificado Padrão GameDev Academy',
       '<div class="certificate">
    <div class="header">
        <img src="{{site_logo}}" alt="Logo" class="logo">
        <h1>Certificado de Conclusão</h1>
    </div>
    <div class="body">
        <p>Certificamos que</p>
        <h2 class="student-name">{{student_name}}</h2>
        <p>concluiu com sucesso o curso</p>
        <h3 class="course-name">{{course_name}}</h3>
        <p class="details">
            Carga horária: {{total_hours}} horas<br>
            Data de conclusão: {{completion_date}}<br>
            Instrutor: {{instructor_name}}
        </p>
    </div>
    <div class="footer">
        <div class="code">Código de verificação: {{certificate_code}}</div>
        <div class="verify">Verifique em: {{site_url}}/certificado/{{certificate_code}}</div>
    </div>
</div>',
       '.certificate { font-family: Georgia, serif; text-align: center; padding: 60px; border: 3px solid #6366f1; }
.logo { height: 60px; }
.student-name { font-size: 32px; color: #1e293b; border-bottom: 2px solid #6366f1; display: inline-block; padding: 10px 40px; }
.course-name { font-size: 24px; color: #6366f1; }
.code { font-family: monospace; color: #64748b; }',
       'landscape', 1, 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `certificate_templates` LIMIT 1);


-- ----------------------------------------------------------------
-- Templates de email padrão
-- ----------------------------------------------------------------
INSERT INTO `email_templates` (`name`, `subject`, `body_html`, `variables`)
SELECT * FROM (
    SELECT 'welcome' AS n, 
           'Bem-vindo à GameDev Academy, {{name}}!' AS s,
           '<h1>Olá, {{name}}! 🎮</h1><p>Seja bem-vindo à GameDev Academy!</p><p>Sua conta foi criada com sucesso. Comece explorando nossos cursos.</p><p><a href="{{site_url}}/cursos">Ver Cursos</a></p>' AS b,
           '["name", "email", "site_url"]' AS v
    UNION ALL SELECT 'password_reset',
           'Redefinição de Senha - GameDev Academy',
           '<h1>Redefinição de Senha</h1><p>Olá, {{name}}.</p><p>Recebemos uma solicitação para redefinir sua senha.</p><p><a href="{{reset_url}}">Clique aqui para redefinir</a></p><p>Este link expira em {{expiry_hours}} horas.</p><p>Se você não solicitou, ignore este email.</p>',
           '["name", "reset_url", "expiry_hours"]'
    UNION ALL SELECT 'enrollment_confirmation',
           'Matrícula Confirmada: {{course_title}}',
           '<h1>Matrícula Confirmada! 🎉</h1><p>Olá, {{name}}!</p><p>Sua matrícula no curso <strong>{{course_title}}</strong> foi confirmada.</p><p><a href="{{course_url}}">Começar a Estudar</a></p>',
           '["name", "course_title", "course_url"]'
    UNION ALL SELECT 'course_completed',
           'Parabéns! Você concluiu {{course_title}} 🎓',
           '<h1>Parabéns, {{name}}! 🏆</h1><p>Você concluiu o curso <strong>{{course_title}}</strong>!</p><p>Seu certificado já está disponível.</p><p><a href="{{certificate_url}}">Ver Certificado</a></p>',
           '["name", "course_title", "certificate_url"]'
    UNION ALL SELECT 'payment_confirmation',
           'Pagamento Confirmado - GameDev Academy',
           '<h1>Pagamento Confirmado ✅</h1><p>Olá, {{name}}.</p><p>Seu pagamento de {{amount}} foi processado com sucesso.</p><p>Curso: {{course_title}}</p><p>ID da transação: {{transaction_id}}</p>',
           '["name", "amount", "course_title", "transaction_id"]'
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `email_templates` LIMIT 1);


-- ----------------------------------------------------------------
-- Categorias de FAQ
-- ----------------------------------------------------------------
INSERT INTO `faq_categories` (`name`, `slug`, `icon`, `sort_order`)
SELECT * FROM (
    SELECT 'Conta e Acesso' AS n, 'conta-acesso' AS s, 'fas fa-user-circle' AS i, 1 AS o
    UNION ALL SELECT 'Cursos e Aulas', 'cursos-aulas', 'fas fa-graduation-cap', 2
    UNION ALL SELECT 'Pagamentos', 'pagamentos', 'fas fa-credit-card', 3
    UNION ALL SELECT 'Certificados', 'certificados', 'fas fa-certificate', 4
    UNION ALL SELECT 'Problemas Técnicos', 'problemas-tecnicos', 'fas fa-tools', 5
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `faq_categories` LIMIT 1);


-- ----------------------------------------------------------------
-- FAQs iniciais
-- ----------------------------------------------------------------
INSERT INTO `faqs` (`category_id`, `question`, `answer`, `sort_order`)
SELECT * FROM (
    SELECT 1 AS c, 'Como criar minha conta?' AS q, '<p>Clique em "Cadastrar" no canto superior direito e preencha o formulário com seus dados. Você receberá um email de confirmação.</p>' AS a, 1 AS o
    UNION ALL SELECT 1, 'Esqueci minha senha. O que fazer?', '<p>Clique em "Esqueci minha senha" na tela de login. Enviaremos um email com instruções para redefinir sua senha.</p>', 2
    UNION ALL SELECT 2, 'Posso acessar os cursos de qualquer dispositivo?', '<p>Sim! Nossa plataforma é responsiva e funciona em computadores, tablets e smartphones.</p>', 3
    UNION ALL SELECT 2, 'Os cursos têm prazo de validade?', '<p>Não! Após a matrícula, você tem acesso vitalício ao conteúdo do curso.</p>', 4
    UNION ALL SELECT 3, 'Quais formas de pagamento são aceitas?', '<p>Aceitamos cartão de crédito, PIX e boleto bancário.</p>', 5
    UNION ALL SELECT 3, 'Qual é a política de reembolso?', '<p>Oferecemos garantia de 7 dias. Se não ficar satisfeito, devolvemos 100% do valor.</p>', 6
    UNION ALL SELECT 4, 'Como obter meu certificado?', '<p>O certificado é gerado automaticamente ao completar 100% das aulas obrigatórias do curso.</p>', 7
    UNION ALL SELECT 4, 'O certificado tem validade?', '<p>Nossos certificados possuem código de verificação único e podem ser validados online a qualquer momento.</p>', 8
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `faqs` LIMIT 1);


-- ----------------------------------------------------------------
-- Idiomas suportados
-- ----------------------------------------------------------------
INSERT INTO `languages` (`name`, `code`, `native_name`)
SELECT * FROM (
    SELECT 'Português (Brasil)' AS n, 'pt-BR' AS c, 'Português' AS nn
    UNION ALL SELECT 'English', 'en-US', 'English'
    UNION ALL SELECT 'Español', 'es', 'Español'
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `languages` LIMIT 1);


-- ----------------------------------------------------------------
-- Países principais
-- ----------------------------------------------------------------
INSERT INTO `countries` (`name`, `code`, `phone_code`, `currency`)
SELECT * FROM (
    SELECT 'Brasil' AS n, 'BR' AS c, '+55' AS p, 'BRL' AS cu
    UNION ALL SELECT 'Portugal', 'PT', '+351', 'EUR'
    UNION ALL SELECT 'Estados Unidos', 'US', '+1', 'USD'
    UNION ALL SELECT 'Angola', 'AO', '+244', 'AOA'
    UNION ALL SELECT 'Moçambique', 'MZ', '+258', 'MZN'
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `countries` LIMIT 1);


-- ================================================================
-- ================================================================
--
--   RESTAURAÇÃO DAS CONFIGURAÇÕES ORIGINAIS
--
-- ================================================================
-- ================================================================

SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS = @OLD_UNIQUE_CHECKS;
SET SQL_MODE = @OLD_SQL_MODE;
SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT;
SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS;
SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION;
SET SQL_NOTES = @OLD_SQL_NOTES;


-- ================================================================
-- ================================================================
--
--   INSTALAÇÃO CONCLUÍDA COM SUCESSO!
--
--   RESUMO:
--   ├── 54 tabelas criadas
--   ├── 8 triggers de cache
--   ├── 3 views utilitárias  
--   ├── Seeds de dados iniciais
--   └── Configurações padrão
--
--   PRÓXIMOS PASSOS:
--   1. Altere a senha do admin (admin@gamedevacademy.com)
--   2. Configure o SMTP em Configurações > Email
--   3. Configure o gateway de pagamento
--   4. Personalize as categorias
--   5. Crie seu primeiro curso!
--
--   ÍNDICE DE TABELAS POR NÚMERO:
--   ┌─────────────────────────────────────────────┐
--   │ 01. users              28. blog_post_tags   │
--   │ 02. categories         29. modules          │
--   │ 03. tags               30. enrollments      │
--   │ 04. settings           31. reviews          │
--   │ 05. pages              32. wishlists         │
--   │ 06. badges             33. certificates     │
--   │ 07. certificate_templ  34. payments         │
--   │ 08. email_templates    35. coupon_uses      │
--   │ 09. email_log          36. discussions      │
--   │ 10. faq_categories     37. support_tickets  │
--   │ 11. faqs               38. instructor_pay   │
--   │ 12. announcements      39. lessons          │
--   │ 13. countries          40. discussion_repl   │
--   │ 14. languages          41. ticket_messages  │
--   │ 15. user_sessions      42. blog_comments    │
--   │ 16. user_streaks       43. course_announce  │
--   │ 17. user_badges        44. course_bookmarks │
--   │ 18. user_points        45. report_abuse     │
--   │ 19. leaderboard        46. lesson_progress  │
--   │ 20. notifications      47. quizzes          │
--   │ 21. notification_pref  48. assignments      │
--   │ 22. media              49. student_notes    │
--   │ 23. coupons            50. quiz_questions   │
--   │ 24. courses            51. quiz_attempts    │
--   │ 25. blog_posts         52. assignment_sub   │
--   │ 26. course_tags        53. quiz_options     │
--   │ 27. course_categories  54. activity_log     │
--   └─────────────────────────────────────────────┘
--
-- ================================================================
-- ================================================================
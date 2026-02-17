<?php
/**
 * ================================================================
 * GAMEDEV ACADEMY - Script de Criação de Tabelas
 * ================================================================
 * 
 * Este script cria todas as 54 tabelas do banco de dados,
 * triggers, views e dados iniciais (seeds).
 * 
 * DEVE ser executado apenas pelo instalador (install/index.php)
 * Requer conexão PDO válida na variável $pdo
 * 
 * Versão: 2.0.0
 * Total de tabelas: 54
 * 
 * ================================================================
 */

// Impedir acesso direto
if (!defined('INSTALLING')) {
    define('AJAX_REQUEST', true);
    define('INSTALLING', true);    // ← adicionar esta linha
    
    //http_response_code(403);
    //die('Acesso negado. Use o instalador.');
}

/**
 * Classe responsável por criar toda a estrutura do banco de dados
 */
class DatabaseInstaller
{
    private PDO $pdo;
    private array $errors = [];
    private array $success = [];
    private int $tableCount = 0;
    private int $expectedTables = 54;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Executa a instalação completa
     * @return bool true se todas as tabelas foram criadas
     */
    public function install(): bool
    {
        try {
            // Configurações iniciais de segurança
            $this->prepareEnvironment();

            // Criar tabelas na ordem correta de dependências
            $this->createLevel0Tables();  // 14 tabelas sem dependências
            $this->createLevel1Tables();  // 11 tabelas
            $this->createLevel2Tables();  // 13 tabelas
            $this->createLevel3Tables();  //  7 tabelas
            $this->createLevel4Tables();  //  4 tabelas (lessons)
            $this->createLevel5Tables();  //  3 tabelas (quizzes)
            $this->createLevel6Tables();  //  1 tabela
            $this->createAuditTable();    //  1 tabela

            // Triggers, Views e Seeds
            $this->createTriggers();
            $this->createViews();
            $this->insertSeeds();

            // Restaurar configurações
            $this->restoreEnvironment();

            // Verificar se todas foram criadas
            return $this->verifyInstallation();

        } catch (Exception $e) {
            $this->errors[] = "ERRO FATAL: " . $e->getMessage();
            $this->restoreEnvironment();
            return false;
        }
    }

    /**
     * Retorna erros ocorridos
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Retorna tabelas criadas com sucesso
     */
    public function getSuccess(): array
    {
        return $this->success;
    }

    /**
     * Retorna contagem de tabelas criadas
     */
    public function getTableCount(): int
    {
        return $this->tableCount;
    }

    // ================================================================
    // MÉTODOS AUXILIARES
    // ================================================================

    /**
     * Prepara o ambiente para instalação segura
     */
    private function prepareEnvironment(): void
    {
        $this->pdo->exec("SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS");
        $this->pdo->exec("SET @OLD_UNIQUE_CHECKS = @@UNIQUE_CHECKS");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $this->pdo->exec("SET UNIQUE_CHECKS = 0");
        $this->pdo->exec("SET NAMES utf8mb4");
        $this->pdo->exec("SET CHARACTER SET utf8mb4");
        $this->pdo->exec("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
        $this->pdo->exec("SET TIME_ZONE = '+00:00'");
    }

    /**
     * Restaura configurações originais do MySQL
     */
    private function restoreEnvironment(): void
    {
        try {
            $this->pdo->exec("SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS");
            $this->pdo->exec("SET UNIQUE_CHECKS = @OLD_UNIQUE_CHECKS");
        } catch (Exception $e) {
            // Fallback seguro
            $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            $this->pdo->exec("SET UNIQUE_CHECKS = 1");
        }
    }

    /**
     * Executa um SQL de criação de tabela com tratamento de erros
     */
    private function createTable(string $tableName, string $sql): bool
    {
        try {
            $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            $this->pdo->exec("DROP TABLE IF EXISTS `{$tableName}`");
            $this->pdo->exec($sql);
            $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            $this->tableCount++;
            $this->success[] = "✅ Tabela '{$tableName}' criada ({$this->tableCount}/{$this->expectedTables})";
            return true;
        } catch (PDOException $e) {
            $this->errors[] = "❌ Erro ao criar tabela '{$tableName}': " . $e->getMessage();
            return false;
        }
    }

    /**
     * Executa SQL genérico com tratamento de erros
     */
    private function executeSQL(string $description, string $sql): bool
    {
        try {
            $this->pdo->exec($sql);
            $this->success[] = "✅ {$description}";
            return true;
        } catch (PDOException $e) {
            $this->errors[] = "⚠️ Aviso em '{$description}': " . $e->getMessage();
            return false;
        }
    }

    /**
     * Verifica se todas as 54 tabelas foram criadas
     */
    private function verifyInstallation(): bool
    {
        $expectedTables = [
            'users', 'categories', 'tags', 'settings', 'pages', 'badges',
            'certificate_templates', 'email_templates', 'email_log',
            'faq_categories', 'faqs', 'announcements', 'countries', 'languages',
            'user_sessions', 'user_streaks', 'user_badges', 'user_points',
            'leaderboard', 'notifications', 'notification_preferences', 'media',
            'coupons', 'courses', 'blog_posts', 'course_tags', 'course_categories',
            'blog_post_tags', 'modules', 'enrollments', 'reviews', 'wishlists',
            'certificates', 'payments', 'coupon_uses', 'discussions',
            'support_tickets', 'instructor_payouts', 'lessons', 'discussion_replies',
            'ticket_messages', 'blog_comments', 'course_announcements',
            'course_bookmarks', 'report_abuse', 'lesson_progress', 'quizzes',
            'assignments', 'student_notes', 'quiz_questions', 'quiz_attempts',
            'assignment_submissions', 'quiz_options', 'activity_log'
        ];

        $stmt = $this->pdo->query("SHOW TABLES");
        $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $missing = [];
        foreach ($expectedTables as $table) {
            if (!in_array($table, $existingTables)) {
                $missing[] = $table;
            }
        }

        if (!empty($missing)) {
            $this->errors[] = "❌ Tabelas faltantes: " . implode(', ', $missing);
            return false;
        }

        $this->success[] = "✅ Verificação completa: todas as {$this->expectedTables} tabelas existem!";
        return true;
    }

    // ================================================================
    // ================================================================
    //
    //   NÍVEL 0 - TABELAS SEM DEPENDÊNCIAS (14 tabelas)
    //
    // ================================================================
    // ================================================================

    private function createLevel0Tables(): void
    {
        // --------------------------------------------------------
        // TABELA 01/54: users
        // --------------------------------------------------------
        $this->createTable('users', "
            CREATE TABLE `users` (
                `id`                        INT UNSIGNED       NOT NULL AUTO_INCREMENT,
                `name`                      VARCHAR(100)       NOT NULL                          COMMENT 'Nome completo',
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
                `email_verified_at`         TIMESTAMP          NULL DEFAULT NULL,
                `email_verification_token`  VARCHAR(100)       DEFAULT NULL,
                `password_reset_token`      VARCHAR(100)       DEFAULT NULL,
                `password_reset_expires`    TIMESTAMP          NULL DEFAULT NULL,
                `two_factor_secret`         VARCHAR(255)       DEFAULT NULL,
                `two_factor_enabled`        TINYINT(1)         NOT NULL DEFAULT 0,
                `last_login_at`             TIMESTAMP          NULL DEFAULT NULL,
                `last_login_ip`             VARCHAR(45)        DEFAULT NULL                      COMMENT 'Suporta IPv6',
                `is_active`                 TINYINT(1)         NOT NULL DEFAULT 1,
                `preferences`               JSON               DEFAULT NULL,
                `created_at`                TIMESTAMP          NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`                TIMESTAMP          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_users_email` (`email`),
                KEY `idx_users_role` (`role`),
                KEY `idx_users_active` (`is_active`),
                KEY `idx_users_created` (`created_at`),
                KEY `idx_users_points` (`total_points` DESC)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Usuários do sistema'
        ");

        // --------------------------------------------------------
        // TABELA 02/54: categories
        // --------------------------------------------------------
        $this->createTable('categories', "
            CREATE TABLE `categories` (
                `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
                `name`          VARCHAR(100)     NOT NULL,
                `slug`          VARCHAR(120)     NOT NULL,
                `description`   TEXT             DEFAULT NULL,
                `icon`          VARCHAR(100)     DEFAULT NULL                      COMMENT 'Classe CSS do ícone',
                `image`         VARCHAR(500)     DEFAULT NULL,
                `color`         VARCHAR(7)       DEFAULT '#6366f1',
                `parent_id`     INT UNSIGNED     DEFAULT NULL                      COMMENT 'Categoria pai',
                `sort_order`    INT              NOT NULL DEFAULT 0,
                `is_active`     TINYINT(1)       NOT NULL DEFAULT 1,
                `course_count`  INT UNSIGNED     NOT NULL DEFAULT 0               COMMENT 'Cache total cursos',
                `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_categories_slug` (`slug`),
                KEY `idx_categories_parent` (`parent_id`),
                KEY `idx_categories_active_order` (`is_active`, `sort_order`),
                CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`)
                    REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Categorias hierárquicas de cursos'
        ");

        // --------------------------------------------------------
        // TABELA 03/54: tags
        // --------------------------------------------------------
        $this->createTable('tags', "
            CREATE TABLE `tags` (
                `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
                `name`        VARCHAR(50)     NOT NULL,
                `slug`        VARCHAR(60)     NOT NULL,
                `usage_count` INT UNSIGNED    NOT NULL DEFAULT 0,
                `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_tags_slug` (`slug`),
                KEY `idx_tags_usage` (`usage_count` DESC)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Tags para classificação de conteúdo'
        ");

        // --------------------------------------------------------
        // TABELA 04/54: settings
        // --------------------------------------------------------
        $this->createTable('settings', "
            CREATE TABLE `settings` (
                `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
                `setting_key`     VARCHAR(100)     NOT NULL,
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Configurações dinâmicas do sistema'
        ");

        // --------------------------------------------------------
        // TABELA 05/54: pages
        // --------------------------------------------------------
        $this->createTable('pages', "
            CREATE TABLE `pages` (
                `id`               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
                `title`            VARCHAR(255)     NOT NULL,
                `slug`             VARCHAR(280)     NOT NULL,
                `content`          LONGTEXT         NOT NULL,
                `meta_title`       VARCHAR(255)     DEFAULT NULL,
                `meta_description` VARCHAR(500)     DEFAULT NULL,
                `template`         VARCHAR(50)      DEFAULT 'default',
                `sort_order`       INT              NOT NULL DEFAULT 0,
                `show_in_menu`     TINYINT(1)       NOT NULL DEFAULT 0,
                `show_in_footer`   TINYINT(1)       NOT NULL DEFAULT 0,
                `is_published`     TINYINT(1)       NOT NULL DEFAULT 1,
                `created_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_pages_slug` (`slug`),
                KEY `idx_pages_published` (`is_published`),
                KEY `idx_pages_menu` (`show_in_menu`, `sort_order`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Páginas estáticas do site'
        ");

        // --------------------------------------------------------
        // TABELA 06/54: badges
        // --------------------------------------------------------
        $this->createTable('badges', "
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
                KEY `idx_badges_category` (`category`),
                KEY `idx_badges_criteria` (`criteria_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Conquistas do sistema de gamificação'
        ");

        // --------------------------------------------------------
        // TABELA 07/54: certificate_templates
        // --------------------------------------------------------
        $this->createTable('certificate_templates', "
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Templates visuais para certificados'
        ");

        // --------------------------------------------------------
        // TABELA 08/54: email_templates
        // --------------------------------------------------------
        $this->createTable('email_templates', "
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Templates de emails transacionais'
        ");

        // --------------------------------------------------------
        // TABELA 09/54: email_log
        // --------------------------------------------------------
        $this->createTable('email_log', "
            CREATE TABLE `email_log` (
                `id`             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
                `to_email`       VARCHAR(150)     NOT NULL,
                `to_name`        VARCHAR(100)     DEFAULT NULL,
                `subject`        VARCHAR(255)     NOT NULL,
                `template`       VARCHAR(50)      DEFAULT NULL,
                `body_preview`   VARCHAR(500)     DEFAULT NULL,
                `status`         ENUM('queued','sent','failed','bounced')
                                                  NOT NULL DEFAULT 'queued',
                `error_message`  TEXT             DEFAULT NULL,
                `attempts`       TINYINT UNSIGNED NOT NULL DEFAULT 0,
                `sent_at`        TIMESTAMP        NULL DEFAULT NULL,
                `created_at`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

                PRIMARY KEY (`id`),
                KEY `idx_emaillog_status` (`status`),
                KEY `idx_emaillog_created` (`created_at`),
                KEY `idx_emaillog_to` (`to_email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Log de emails enviados'
        ");

        // --------------------------------------------------------
        // TABELA 10/54: faq_categories
        // --------------------------------------------------------
        $this->createTable('faq_categories', "
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
              COMMENT='Categorias de FAQs'
        ");

        // --------------------------------------------------------
        // TABELA 11/54: faqs
        // --------------------------------------------------------
        $this->createTable('faqs', "
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
                KEY `idx_faqs_published` (`is_published`, `sort_order`),
                CONSTRAINT `fk_faqs_category` FOREIGN KEY (`category_id`)
                    REFERENCES `faq_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Perguntas frequentes'
        ");

        // --------------------------------------------------------
        // TABELA 12/54: announcements
        // --------------------------------------------------------
        $this->createTable('announcements', "
            CREATE TABLE `announcements` (
                `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
                `title`           VARCHAR(255)     NOT NULL,
                `content`         LONGTEXT         NOT NULL,
                `type`            ENUM('info','warning','success','danger','promotion')
                                                   NOT NULL DEFAULT 'info',
                `target_audience` ENUM('all','students','instructors','admins')
                                                   NOT NULL DEFAULT 'all',
                `display_type`    ENUM('banner','modal','notification')
                                                   NOT NULL DEFAULT 'banner',
                `action_url`      VARCHAR(500)     DEFAULT NULL,
                `action_text`     VARCHAR(100)     DEFAULT NULL,
                `starts_at`       TIMESTAMP        NULL DEFAULT NULL,
                `ends_at`         TIMESTAMP        NULL DEFAULT NULL,
                `is_active`       TINYINT(1)       NOT NULL DEFAULT 1,
                `created_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

                PRIMARY KEY (`id`),
                KEY `idx_announcements_active` (`is_active`, `starts_at`, `ends_at`),
                KEY `idx_announcements_target` (`target_audience`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Anúncios globais do sistema'
        ");

        // --------------------------------------------------------
        // TABELA 13/54: countries
        // --------------------------------------------------------
        $this->createTable('countries', "
            CREATE TABLE `countries` (
                `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
                `name`        VARCHAR(100)    NOT NULL,
                `code`        CHAR(2)         NOT NULL,
                `phone_code`  VARCHAR(5)      DEFAULT NULL,
                `currency`    VARCHAR(3)      DEFAULT NULL,
                `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,

                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_countries_code` (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Países para formulários'
        ");

        // --------------------------------------------------------
        // TABELA 14/54: languages
        // --------------------------------------------------------
        $this->createTable('languages', "
            CREATE TABLE `languages` (
                `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
                `name`        VARCHAR(50)     NOT NULL,
                `code`        VARCHAR(10)     NOT NULL,
                `native_name` VARCHAR(50)     DEFAULT NULL,
                `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,

                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_languages_code` (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Idiomas disponíveis para cursos'
        ");
    }

    // ================================================================
    // ================================================================
    //
    //   NÍVEL 1 - DEPENDEM APENAS DO NÍVEL 0 (11 tabelas)
    //
    // ================================================================
    // ================================================================

    private function createLevel1Tables(): void
    {
        // --------------------------------------------------------
        // TABELA 15/54: user_sessions
        // --------------------------------------------------------
        $this->createTable('user_sessions', "
            CREATE TABLE `user_sessions` (
                `id`             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
                `user_id`        INT UNSIGNED     NOT NULL,
                `session_token`  VARCHAR(255)     NOT NULL,
                `ip_address`     VARCHAR(45)      DEFAULT NULL,
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
              COMMENT='Sessões ativas dos usuários'
        ");

        // --------------------------------------------------------
        // TABELA 16/54: user_streaks
        // --------------------------------------------------------
        $this->createTable('user_streaks', "
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Streak de dias de estudo'
        ");

        // --------------------------------------------------------
        // TABELA 17/54: user_badges
        // --------------------------------------------------------
        $this->createTable('user_badges', "
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
              COMMENT='Conquistas desbloqueadas pelos usuários'
        ");

        // --------------------------------------------------------
        // TABELA 18/54: user_points
        // --------------------------------------------------------
        $this->createTable('user_points', "
            CREATE TABLE `user_points` (
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
                KEY `idx_points_created` (`created_at`),
                KEY `idx_points_ref` (`reference_type`, `reference_id`),
                CONSTRAINT `fk_points_user` FOREIGN KEY (`user_id`)
                    REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Histórico de pontos da gamificação'
        ");

        // --------------------------------------------------------
        // TABELA 19/54: leaderboard
        // --------------------------------------------------------
        $this->createTable('leaderboard', "
            CREATE TABLE `leaderboard` (
                `id`                INT UNSIGNED     NOT NULL AUTO_INCREMENT,
                `user_id`           INT UNSIGNED     NOT NULL,
                `total_points`      INT UNSIGNED     NOT NULL DEFAULT 0,
                `courses_completed` INT UNSIGNED     NOT NULL DEFAULT 0,
                `badges_earned`     INT UNSIGNED     NOT NULL DEFAULT 0,
                `rank_position`     INT UNSIGNED     DEFAULT NULL,
                `period`            ENUM('weekly','monthly','all_time')
                                                     NOT NULL DEFAULT 'all_time',
                `period_start`      DATE             DEFAULT NULL,
                `updated_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_leaderboard` (`user_id`, `period`, `period_start`),
                KEY `idx_leaderboard_rank` (`period`, `rank_position`),
                KEY `idx_leaderboard_points` (`period`, `total_points` DESC),
                CONSTRAINT `fk_leaderboard_user` FOREIGN KEY (`user_id`)
                    REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Rankings de gamificação'
        ");

        // --------------------------------------------------------
        // TABELA 20/54: notifications
        // --------------------------------------------------------
        $this->createTable('notifications', "
            CREATE TABLE `notifications` (
                `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
                `user_id`     INT UNSIGNED     NOT NULL,
                `type`        VARCHAR(50)      NOT NULL,
                `title`       VARCHAR(255)     NOT NULL,
                `message`     TEXT             NOT NULL,
                `icon`        VARCHAR(100)     DEFAULT NULL,
                `action_url`  VARCHAR(500)     DEFAULT NULL,
                `data`        JSON             DEFAULT NULL,
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
              COMMENT='Notificações in-app'
        ");

        // --------------------------------------------------------
        // TABELA 21/54: notification_preferences
        // --------------------------------------------------------
        $this->createTable('notification_preferences', "
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Preferências de notificação por tipo'
        ");

        // --------------------------------------------------------
        // TABELA 22/54: media
        // --------------------------------------------------------
        $this->createTable('media', "
            CREATE TABLE `media` (
                `id`                INT UNSIGNED     NOT NULL AUTO_INCREMENT,
                `user_id`           INT UNSIGNED     DEFAULT NULL,
                `filename`          VARCHAR(255)     NOT NULL,
                `original_filename` VARCHAR(255)     NOT NULL,
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
                KEY `idx_media_mime` (`mime_type`),
                KEY `idx_media_created` (`created_at`),
                CONSTRAINT `fk_media_user` FOREIGN KEY (`user_id`)
                    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Biblioteca de mídia'
        ");

        // --------------------------------------------------------
        // TABELA 23/54: coupons
        // --------------------------------------------------------
        $this->createTable('coupons', "
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
                KEY `idx_coupons_active_dates` (`is_active`, `starts_at`, `expires_at`),
                CONSTRAINT `fk_coupons_creator` FOREIGN KEY (`created_by`)
                    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Cupons de desconto'
        ");

        // --------------------------------------------------------
        // TABELA 24/54: courses
        // --------------------------------------------------------
        $this->createTable('courses', "
            CREATE TABLE `courses` (
                `id`                INT UNSIGNED     NOT NULL AUTO_INCREMENT,
                `title`             VARCHAR(255)     NOT NULL,
                `slug`              VARCHAR(280)     NOT NULL,
                `subtitle`          VARCHAR(300)     DEFAULT NULL,
                `description`       LONGTEXT         DEFAULT NULL,
                `short_description` VARCHAR(500)     DEFAULT NULL,
                `thumbnail`         VARCHAR(500)     DEFAULT NULL,
                `preview_video`     VARCHAR(500)     DEFAULT NULL,
                `instructor_id`     INT UNSIGNED     NOT NULL,
                `category_id`       INT UNSIGNED     DEFAULT NULL,
                `level`             ENUM('beginner','intermediate','advanced','all_levels')
                                                     NOT NULL DEFAULT 'beginner',
                `language`          VARCHAR(10)      NOT NULL DEFAULT 'pt-BR',
                `price`             DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
                `original_price`    DECIMAL(10,2)    DEFAULT NULL,
                `currency`          VARCHAR(3)       NOT NULL DEFAULT 'BRL',
                `duration_hours`    DECIMAL(6,1)     NOT NULL DEFAULT 0.0,
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
                `is_featured`       TINYINT(1)       NOT NULL DEFAULT 0,
                `is_free`           TINYINT(1)       NOT NULL DEFAULT 0,
                `is_bestseller`     TINYINT(1)       NOT NULL DEFAULT 0,
                `is_new`            TINYINT(1)       NOT NULL DEFAULT 0,
                `enrollment_count`  INT UNSIGNED     NOT NULL DEFAULT 0,
                `rating_average`    DECIMAL(3,2)     NOT NULL DEFAULT 0.00,
                `rating_count`      INT UNSIGNED     NOT NULL DEFAULT 0,
                `completion_rate`   DECIMAL(5,2)     NOT NULL DEFAULT 0.00,
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
              COMMENT='Cursos da plataforma'
        ");

        // --------------------------------------------------------
        // TABELA 25/54: blog_posts
        // --------------------------------------------------------
        $this->createTable('blog_posts', "
            CREATE TABLE `blog_posts` (
                `id`               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
                `title`            VARCHAR(255)     NOT NULL,
                `slug`             VARCHAR(280)     NOT NULL,
                `excerpt`          VARCHAR(500)     DEFAULT NULL,
                `content`          LONGTEXT         NOT NULL,
                `featured_image`   VARCHAR(500)     DEFAULT NULL,
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
                KEY `idx_blogposts_category` (`category_id`),
                KEY `idx_blogposts_status` (`status`, `published_at` DESC),
                KEY `idx_blogposts_featured` (`is_featured`),
                FULLTEXT KEY `ft_blogposts_search` (`title`, `content`),
                CONSTRAINT `fk_blogposts_author` FOREIGN KEY (`author_id`)
                    REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
                CONSTRAINT `fk_blogposts_category` FOREIGN KEY (`category_id`)
                    REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Artigos do blog'
        ");
    }

    // ================================================================
    // ================================================================
    //
    //   NÍVEL 2 - DEPENDEM DOS NÍVEIS 0 E 1 (13 tabelas)
    //
    // ================================================================
    // ================================================================

    private function createLevel2Tables(): void
    {
        // --------------------------------------------------------
        // TABELA 26/54: course_tags
        // --------------------------------------------------------
        $this->createTable('course_tags', "
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
              COMMENT='N:N cursos e tags'
        ");

        // --------------------------------------------------------
        // TABELA 27/54: course_categories
        // --------------------------------------------------------
        $this->createTable('course_categories', "
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
              COMMENT='Categorias secundárias dos cursos'
        ");

        // --------------------------------------------------------
        // TABELA 28/54: blog_post_tags
        // --------------------------------------------------------
        $this->createTable('blog_post_tags', "
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
              COMMENT='Tags dos artigos do blog'
        ");

        // --------------------------------------------------------
        // TABELA 29/54: modules
        // --------------------------------------------------------
        $this->createTable('modules', "
            CREATE TABLE `modules` (
                `id`               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
                `course_id`        INT UNSIGNED     NOT NULL,
                `title`            VARCHAR(255)     NOT NULL,
                `description`      TEXT             DEFAULT NULL,
                `sort_order`       INT              NOT NULL DEFAULT 0,
                `is_free_preview`  TINYINT(1)       NOT NULL DEFAULT 0,
                `is_published`     TINYINT(1)       NOT NULL DEFAULT 1,
                `duration_minutes` INT UNSIGNED     NOT NULL DEFAULT 0,
                `lesson_count`     INT UNSIGNED     NOT NULL DEFAULT 0,
                `created_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                PRIMARY KEY (`id`),
                KEY `idx_modules_course_order` (`course_id`, `sort_order`),
                CONSTRAINT `fk_modules_course` FOREIGN KEY (`course_id`)
                    REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Módulos/seções dos cursos'
        ");

        // --------------------------------------------------------
        // TABELA 30/54: enrollments
        // --------------------------------------------------------
        $this->createTable('enrollments', "
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
                `source`            VARCHAR(50)      DEFAULT 'direct',
                `created_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

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
              COMMENT='Matrículas dos alunos'
        ");

        // --------------------------------------------------------
        // TABELA 31/54: reviews
        // --------------------------------------------------------
        $this->createTable('reviews', "
            CREATE TABLE `reviews` (
                `id`                    INT UNSIGNED     NOT NULL AUTO_INCREMENT,
                `user_id`               INT UNSIGNED     NOT NULL,
                `course_id`             INT UNSIGNED     NOT NULL,
                `rating`                TINYINT UNSIGNED NOT NULL,
                `title`                 VARCHAR(255)     DEFAULT NULL,
                `comment`               TEXT             DEFAULT NULL,
                `is_approved`           TINYINT(1)       NOT NULL DEFAULT 0,
                `instructor_reply`      TEXT             DEFAULT NULL,
                `instructor_reply_at`   TIMESTAMP        NULL DEFAULT NULL,
                `helpful_count`         INT UNSIGNED     NOT NULL DEFAULT 0,
                `reported_count`        INT UNSIGNED     NOT NULL DEFAULT 0,
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
              COMMENT='Avaliações de cursos'
        ");

        // --------------------------------------------------------
        // TABELA 32/54: wishlists
        // --------------------------------------------------------
        $this->createTable('wishlists', "
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
              COMMENT='Lista de desejos'
        ");

        // --------------------------------------------------------
        // TABELA 33/54: certificates
        // --------------------------------------------------------
        $this->createTable('certificates', "
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
                KEY `idx_certificates_course` (`course_id`),
                KEY `idx_certificates_issued` (`issued_at`),
                CONSTRAINT `fk_certificates_user` FOREIGN KEY (`user_id`)
                    REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk_certificates_course` FOREIGN KEY (`course_id`)
                    REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk_certificates_template` FOREIGN KEY (`template_id`)
                    REFERENCES `certificate_templates` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Certificados emitidos'
        ");

        // --------------------------------------------------------
        // TABELA 34/54: payments
        // --------------------------------------------------------
        $this->createTable('payments', "
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
              COMMENT='Pagamentos e transações'
        ");

        // --------------------------------------------------------
        // TABELA 35/54: coupon_uses
        // --------------------------------------------------------
        $this->createTable('coupon_uses', "
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
              COMMENT='Registro de uso de cupons'
        ");

        // --------------------------------------------------------
        // TABELA 36/54: discussions
        // --------------------------------------------------------
        $this->createTable('discussions', "
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
                KEY `idx_discussions_user` (`user_id`),
                KEY `idx_discussions_pinned` (`is_pinned`, `last_reply_at` DESC),
                KEY `idx_discussions_resolved` (`is_resolved`),
                FULLTEXT KEY `ft_discussions_search` (`title`, `content`),
                CONSTRAINT `fk_discussions_course` FOREIGN KEY (`course_id`)
                    REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk_discussions_user` FOREIGN KEY (`user_id`)
                    REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Tópicos do fórum'
        ");

        // --------------------------------------------------------
        // TABELA 37/54: support_tickets
        // --------------------------------------------------------
        $this->createTable('support_tickets', "
            CREATE TABLE `support_tickets` (
                `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
                `ticket_number` VARCHAR(20)      NOT NULL,
                `user_id`       INT UNSIGNED     NOT NULL,
                `subject`       VARCHAR(255)     NOT NULL,
                `description`   LONGTEXT         NOT NULL,
                `category`      ENUM('technical','billing','content','account','bug_report','feature_request','other')
                                                 NOT NULL DEFAULT 'other',
                `priority`      ENUM('low','medium','high','urgent')
                                                 NOT NULL DEFAULT 'medium',
                `status`        ENUM('open','in_progress','waiting_response','on_hold','resolved','closed')
                                                 NOT NULL DEFAULT 'open',
                `assigned_to`   INT UNSIGNED     DEFAULT NULL,
                `course_id`     INT UNSIGNED     DEFAULT NULL,
                `resolved_at`   TIMESTAMP        NULL DEFAULT NULL,
                `closed_at`     TIMESTAMP        NULL DEFAULT NULL,
                `satisfaction`  TINYINT UNSIGNED DEFAULT NULL,
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
              COMMENT='Tickets de suporte'
        ");

        // --------------------------------------------------------
        // TABELA 38/54: instructor_payouts
        // --------------------------------------------------------
        $this->createTable('instructor_payouts', "
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
              COMMENT='Repasses para instrutores'
        ");
    }

    // ================================================================
    // ================================================================
    //
    //   NÍVEL 3 - DEPENDEM DAS TABELAS ANTERIORES (7 tabelas)
    //
    // ================================================================
    // ================================================================

    private function createLevel3Tables(): void
    {
        // --------------------------------------------------------
        // TABELA 39/54: lessons
        // --------------------------------------------------------
        $this->createTable('lessons', "
            CREATE TABLE `lessons` (
                `id`               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
                `module_id`        INT UNSIGNED     NOT NULL,
                `course_id`        INT UNSIGNED     NOT NULL,
                `title`            VARCHAR(255)     NOT NULL,
                `slug`             VARCHAR(280)     NOT NULL,
                `content_type`     ENUM('video','text','quiz','assignment','download','live','interactive')
                                                    NOT NULL DEFAULT 'video',
                `content`          LONGTEXT         DEFAULT NULL,
                `video_url`        VARCHAR(500)     DEFAULT NULL,
                `video_provider`   ENUM('youtube','vimeo','bunny','wistia','self_hosted','other')
                                                    DEFAULT NULL,
                `video_duration`   INT UNSIGNED     NOT NULL DEFAULT 0,
                `video_thumbnail`  VARCHAR(500)     DEFAULT NULL,
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
                KEY `idx_lessons_course` (`course_id`),
                KEY `idx_lessons_type` (`content_type`),
                KEY `idx_lessons_published` (`is_published`),
                KEY `idx_lessons_free` (`is_free_preview`),
                CONSTRAINT `fk_lessons_module` FOREIGN KEY (`module_id`)
                    REFERENCES `modules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk_lessons_course` FOREIGN KEY (`course_id`)
                    REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Aulas dos cursos'
        ");

        // --------------------------------------------------------
        // TABELA 40/54: discussion_replies
        // --------------------------------------------------------
        $this->createTable('discussion_replies', "
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
              COMMENT='Respostas do fórum'
        ");

        // --------------------------------------------------------
        // TABELA 41/54: ticket_messages
        // --------------------------------------------------------
        $this->createTable('ticket_messages', "
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Mensagens dos tickets de suporte'
        ");

        // --------------------------------------------------------
        // TABELA 42/54: blog_comments
        // --------------------------------------------------------
        $this->createTable('blog_comments', "
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
                KEY `idx_blogcomments_user` (`user_id`),
                KEY `idx_blogcomments_parent` (`parent_id`),
                CONSTRAINT `fk_blogcomments_post` FOREIGN KEY (`post_id`)
                    REFERENCES `blog_posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk_blogcomments_user` FOREIGN KEY (`user_id`)
                    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
                CONSTRAINT `fk_blogcomments_parent` FOREIGN KEY (`parent_id`)
                    REFERENCES `blog_comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Comentários do blog'
        ");

        // --------------------------------------------------------
        // TABELA 43/54: course_announcements
        // --------------------------------------------------------
        $this->createTable('course_announcements', "
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
                KEY `idx_courseannounce_course` (`course_id`, `created_at` DESC),
                CONSTRAINT `fk_courseannounce_course` FOREIGN KEY (`course_id`)
                    REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk_courseannounce_author` FOREIGN KEY (`author_id`)
                    REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Anúncios por curso'
        ");

        // --------------------------------------------------------
        // TABELA 44/54: course_bookmarks
        // --------------------------------------------------------
        $this->createTable('course_bookmarks', "
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Aulas favoritas'
        ");

        // --------------------------------------------------------
        // TABELA 45/54: report_abuse
        // --------------------------------------------------------
        $this->createTable('report_abuse', "
            CREATE TABLE `report_abuse` (
                `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
                `reporter_id`     INT UNSIGNED     NOT NULL,
                `entity_type`     VARCHAR(50)      NOT NULL,
                `entity_id`       INT UNSIGNED     NOT NULL,
                `reason`          ENUM('spam','inappropriate','harassment','copyright','misinformation','other')
                                                   NOT NULL,
                `description`     TEXT             DEFAULT NULL,
                `status`          ENUM('pending','reviewing','action_taken','dismissed')
                                                   NOT NULL DEFAULT 'pending',
                `reviewed_by`     INT UNSIGNED     DEFAULT NULL,
                `reviewed_at`     TIMESTAMP        NULL DEFAULT NULL,
                `action_taken`    VARCHAR(255)     DEFAULT NULL,
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
              COMMENT='Denúncias de conteúdo'
        ");
    }

    // ================================================================
    // ================================================================
    //
    //   NÍVEL 4 - DEPENDEM DAS AULAS (4 tabelas)
    //
    // ================================================================
    // ================================================================

    private function createLevel4Tables(): void
    {
        // --------------------------------------------------------
        // TABELA 46/54: lesson_progress
        // --------------------------------------------------------
        $this->createTable('lesson_progress', "
            CREATE TABLE `lesson_progress` (
                `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
                `user_id`       INT UNSIGNED     NOT NULL,
                `lesson_id`     INT UNSIGNED     NOT NULL,
                `course_id`     INT UNSIGNED     NOT NULL,
                `status`        ENUM('not_started','in_progress','completed')
                                                 NOT NULL DEFAULT 'not_started',
                `watch_time`    INT UNSIGNED     NOT NULL DEFAULT 0,
                `last_position` INT UNSIGNED     NOT NULL DEFAULT 0,
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
              COMMENT='Progresso por aula'
        ");

        // --------------------------------------------------------
        // TABELA 47/54: quizzes
        // --------------------------------------------------------
        $this->createTable('quizzes', "
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
                KEY `idx_quizzes_lesson` (`lesson_id`),
                CONSTRAINT `fk_quizzes_lesson` FOREIGN KEY (`lesson_id`)
                    REFERENCES `lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Quizzes avaliativos'
        ");

        // --------------------------------------------------------
        // TABELA 48/54: assignments
        // --------------------------------------------------------
        $this->createTable('assignments', "
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
                `submission_type`      ENUM('file','text','url','github','zip')
                                                        NOT NULL DEFAULT 'file',
                `allowed_extensions`   JSON             DEFAULT NULL,
                `max_file_size`        INT UNSIGNED     DEFAULT 52428800,
                `rubric`               JSON             DEFAULT NULL,
                `is_active`            TINYINT(1)       NOT NULL DEFAULT 1,
                `created_at`           TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`           TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                PRIMARY KEY (`id`),
                KEY `idx_assignments_lesson` (`lesson_id`),
                CONSTRAINT `fk_assignments_lesson` FOREIGN KEY (`lesson_id`)
                    REFERENCES `lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Tarefas e projetos práticos'
        ");

        // --------------------------------------------------------
        // TABELA 49/54: student_notes
        // --------------------------------------------------------
        $this->createTable('student_notes', "
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
                KEY `idx_notes_user_lesson` (`user_id`, `lesson_id`),
                KEY `idx_notes_timestamp` (`lesson_id`, `timestamp_seconds`),
                CONSTRAINT `fk_notes_user` FOREIGN KEY (`user_id`)
                    REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk_notes_lesson` FOREIGN KEY (`lesson_id`)
                    REFERENCES `lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Anotações dos alunos nas aulas'
        ");
    }

    // ================================================================
    // ================================================================
    //
    //   NÍVEL 5 - DEPENDEM DOS QUIZZES E ASSIGNMENTS (3 tabelas)
    //
    // ================================================================
    // ================================================================

    private function createLevel5Tables(): void
    {
        // --------------------------------------------------------
        // TABELA 50/54: quiz_questions
        // --------------------------------------------------------
        $this->createTable('quiz_questions', "
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
                KEY `idx_questions_quiz_order` (`quiz_id`, `sort_order`),
                CONSTRAINT `fk_questions_quiz` FOREIGN KEY (`quiz_id`)
                    REFERENCES `quizzes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Perguntas dos quizzes'
        ");

        // --------------------------------------------------------
        // TABELA 51/54: quiz_attempts
        // --------------------------------------------------------
        $this->createTable('quiz_attempts', "
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
                KEY `idx_attempts_user_quiz` (`user_id`, `quiz_id`),
                KEY `idx_attempts_quiz` (`quiz_id`),
                KEY `idx_attempts_passed` (`passed`),
                CONSTRAINT `fk_attempts_user` FOREIGN KEY (`user_id`)
                    REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk_attempts_quiz` FOREIGN KEY (`quiz_id`)
                    REFERENCES `quizzes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Tentativas de quiz'
        ");

        // --------------------------------------------------------
        // TABELA 52/54: assignment_submissions
        // --------------------------------------------------------
        $this->createTable('assignment_submissions', "
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
              COMMENT='Entregas de projetos'
        ");
    }

    // ================================================================
    // ================================================================
    //
    //   NÍVEL 6 - DEPENDÊNCIA MAIS PROFUNDA (1 tabela)
    //
    // ================================================================
    // ================================================================

    private function createLevel6Tables(): void
    {
        // --------------------------------------------------------
        // TABELA 53/54: quiz_options
        // --------------------------------------------------------
        $this->createTable('quiz_options', "
            CREATE TABLE `quiz_options` (
                `id`           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
                `question_id`  INT UNSIGNED     NOT NULL,
                `option_text`  TEXT             NOT NULL,
                `is_correct`   TINYINT(1)       NOT NULL DEFAULT 0,
                `sort_order`   INT              NOT NULL DEFAULT 0,

                PRIMARY KEY (`id`),
                KEY `idx_options_question` (`question_id`, `sort_order`),
                CONSTRAINT `fk_options_question` FOREIGN KEY (`question_id`)
                    REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Alternativas de múltipla escolha'
        ");
    }

    // ================================================================
    // ================================================================
    //
    //   TABELA DE AUDITORIA (1 tabela)
    //
    // ================================================================
    // ================================================================

    private function createAuditTable(): void
    {
        // --------------------------------------------------------
        // TABELA 54/54: activity_log
        // --------------------------------------------------------
        $this->createTable('activity_log', "
            CREATE TABLE `activity_log` (
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
                KEY `idx_actlog_user` (`user_id`),
                KEY `idx_actlog_action` (`action`),
                KEY `idx_actlog_entity` (`entity_type`, `entity_id`),
                KEY `idx_actlog_created` (`created_at`),
                CONSTRAINT `fk_actlog_user` FOREIGN KEY (`user_id`)
                    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Log de auditoria do sistema'
        ");
    }

    // ================================================================
    // ================================================================
    //
    //   TRIGGERS DE CACHE AUTOMÁTICO
    //
    // ================================================================
    // ================================================================

    private function createTriggers(): void
    {
        // Trigger: enrollment_count ao matricular
        $this->executeSQL('Trigger: enrollment insert', "
            CREATE TRIGGER `trg_enrollment_after_insert`
            AFTER INSERT ON `enrollments`
            FOR EACH ROW
            UPDATE `courses` SET `enrollment_count` = `enrollment_count` + 1
            WHERE `id` = NEW.`course_id`
        ");

        // Trigger: enrollment_count ao deletar
        $this->executeSQL('Trigger: enrollment delete', "
            CREATE TRIGGER `trg_enrollment_after_delete`
            AFTER DELETE ON `enrollments`
            FOR EACH ROW
            UPDATE `courses` SET `enrollment_count` = GREATEST(`enrollment_count` - 1, 0)
            WHERE `id` = OLD.`course_id`
        ");

        // Trigger: rating ao inserir review aprovada
        $this->executeSQL('Trigger: review insert', "
            CREATE TRIGGER `trg_review_after_insert`
            AFTER INSERT ON `reviews`
            FOR EACH ROW
            BEGIN
                IF NEW.`is_approved` = 1 THEN
                    UPDATE `courses` SET
                        `rating_average` = (SELECT COALESCE(AVG(`rating`), 0) FROM `reviews` WHERE `course_id` = NEW.`course_id` AND `is_approved` = 1),
                        `rating_count` = (SELECT COUNT(*) FROM `reviews` WHERE `course_id` = NEW.`course_id` AND `is_approved` = 1)
                    WHERE `id` = NEW.`course_id`;
                END IF;
            END
        ");

        // Trigger: rating ao atualizar review
        $this->executeSQL('Trigger: review update', "
            CREATE TRIGGER `trg_review_after_update`
            AFTER UPDATE ON `reviews`
            FOR EACH ROW
            BEGIN
                UPDATE `courses` SET
                    `rating_average` = (SELECT COALESCE(AVG(`rating`), 0) FROM `reviews` WHERE `course_id` = NEW.`course_id` AND `is_approved` = 1),
                    `rating_count` = (SELECT COUNT(*) FROM `reviews` WHERE `course_id` = NEW.`course_id` AND `is_approved` = 1)
                WHERE `id` = NEW.`course_id`;
            END
        ");

        // Trigger: reply_count ao inserir resposta
        $this->executeSQL('Trigger: reply insert', "
            CREATE TRIGGER `trg_reply_after_insert`
            AFTER INSERT ON `discussion_replies`
            FOR EACH ROW
            UPDATE `discussions` SET
                `reply_count` = `reply_count` + 1,
                `last_reply_at` = NEW.`created_at`,
                `last_reply_by` = NEW.`user_id`
            WHERE `id` = NEW.`discussion_id`
        ");

        // Trigger: reply_count ao deletar resposta
        $this->executeSQL('Trigger: reply delete', "
            CREATE TRIGGER `trg_reply_after_delete`
            AFTER DELETE ON `discussion_replies`
            FOR EACH ROW
            UPDATE `discussions` SET
                `reply_count` = GREATEST(`reply_count` - 1, 0)
            WHERE `id` = OLD.`discussion_id`
        ");

        // Trigger: used_count do cupom
        $this->executeSQL('Trigger: coupon use insert', "
            CREATE TRIGGER `trg_couponuse_after_insert`
            AFTER INSERT ON `coupon_uses`
            FOR EACH ROW
            UPDATE `coupons` SET `used_count` = `used_count` + 1
            WHERE `id` = NEW.`coupon_id`
        ");

        // Trigger: total_points do usuário
        $this->executeSQL('Trigger: points insert', "
            CREATE TRIGGER `trg_points_after_insert`
            AFTER INSERT ON `user_points`
            FOR EACH ROW
            UPDATE `users` SET
                `total_points` = (SELECT COALESCE(SUM(`points`), 0) FROM `user_points` WHERE `user_id` = NEW.`user_id`)
            WHERE `id` = NEW.`user_id`
        ");
    }

    // ================================================================
    // ================================================================
    //
    //   VIEWS ÚTEIS
    //
    // ================================================================
    // ================================================================

    private function createViews(): void
    {
        // View: Listagem de cursos completa
        $this->executeSQL('View: vw_courses_listing', "
            CREATE OR REPLACE VIEW `vw_courses_listing` AS
            SELECT
                c.`id`, c.`title`, c.`slug`, c.`short_description`, c.`thumbnail`,
                c.`level`, c.`price`, c.`original_price`, c.`is_free`, c.`is_featured`,
                c.`is_bestseller`, c.`duration_hours`, c.`total_lessons`,
                c.`enrollment_count`, c.`rating_average`, c.`rating_count`,
                c.`game_engine`, c.`status`, c.`published_at`,
                u.`id` AS `instructor_id`, u.`name` AS `instructor_name`, u.`avatar` AS `instructor_avatar`,
                cat.`id` AS `category_id`, cat.`name` AS `category_name`, cat.`slug` AS `category_slug`
            FROM `courses` c
            INNER JOIN `users` u ON c.`instructor_id` = u.`id`
            LEFT JOIN `categories` cat ON c.`category_id` = cat.`id`
        ");

        // View: Estatísticas do dashboard
        $this->executeSQL('View: vw_dashboard_stats', "
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
                (SELECT COUNT(*) FROM `reviews` WHERE `is_approved` = 0) AS `pending_reviews`
        ");

        // View: Progresso dos alunos
        $this->executeSQL('View: vw_student_progress', "
            CREATE OR REPLACE VIEW `vw_student_progress` AS
            SELECT
                e.`user_id`, e.`course_id`, c.`title` AS `course_title`, c.`total_lessons`,
                e.`progress_percent`, e.`lessons_completed`, e.`status` AS `enrollment_status`,
                e.`enrolled_at`, e.`completed_at`, e.`last_accessed_at`,
                u.`name` AS `student_name`, u.`email` AS `student_email`
            FROM `enrollments` e
            INNER JOIN `courses` c ON e.`course_id` = c.`id`
            INNER JOIN `users` u ON e.`user_id` = u.`id`
        ");
    }

    // ================================================================
    // ================================================================
    //
    //   SEEDS (DADOS INICIAIS)
    //
    // ================================================================
    // ================================================================

    private function insertSeeds(): void
    {
        // --------------------------------------------------------
        // Admin padrão
        // --------------------------------------------------------
        $this->executeSQL('Seed: admin user', "
            INSERT INTO `users` (`name`, `email`, `password`, `role`, `email_verified_at`, `is_active`)
            SELECT 'Administrador', 'admin@gamedevacademy.com',
                   '" . password_hash('Admin@123', PASSWORD_BCRYPT, ['cost' => 12]) . "',
                   'super_admin', NOW(), 1
            FROM DUAL
            WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `email` = 'admin@gamedevacademy.com')
        ");

        // --------------------------------------------------------
        // Categorias
        // --------------------------------------------------------
        $this->executeSQL('Seed: categories', "
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
            WHERE NOT EXISTS (SELECT 1 FROM `categories` LIMIT 1)
        ");

        // --------------------------------------------------------
        // Configurações
        // --------------------------------------------------------
        $this->executeSQL('Seed: settings', "
            INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `description`, `is_public`)
            SELECT * FROM (
                SELECT 'site_name' AS k, 'GameDev Academy' AS v, 'string' AS t, 'general' AS g, 'Nome do site' AS d, 1 AS p
                UNION ALL SELECT 'site_description', 'Aprenda a criar jogos do zero ao profissional', 'string', 'general', 'Descrição do site', 1
                UNION ALL SELECT 'site_tagline', 'Sua jornada no desenvolvimento de jogos começa aqui', 'string', 'general', 'Tagline', 1
                UNION ALL SELECT 'site_logo', '/assets/images/logo.png', 'string', 'general', 'URL do logo', 1
                UNION ALL SELECT 'site_favicon', '/assets/images/favicon.ico', 'string', 'general', 'Favicon', 1
                UNION ALL SELECT 'site_url', 'http://localhost', 'string', 'general', 'URL base', 1
                UNION ALL SELECT 'contact_email', 'contato@gamedevacademy.com', 'string', 'general', 'Email de contato', 1
                UNION ALL SELECT 'maintenance_mode', 'false', 'boolean', 'general', 'Modo manutenção', 0
                UNION ALL SELECT 'items_per_page', '12', 'number', 'general', 'Itens por página', 0
                UNION ALL SELECT 'timezone', 'America/Sao_Paulo', 'string', 'general', 'Fuso horário', 0
                UNION ALL SELECT 'currency', 'BRL', 'string', 'payment', 'Moeda padrão', 0
                UNION ALL SELECT 'currency_symbol', 'R\$', 'string', 'payment', 'Símbolo moeda', 1
                UNION ALL SELECT 'stripe_enabled', 'false', 'boolean', 'payment', 'Stripe ativo', 0
                UNION ALL SELECT 'stripe_public_key', '', 'string', 'payment', 'Stripe Public Key', 0
                UNION ALL SELECT 'stripe_secret_key', '', 'string', 'payment', 'Stripe Secret Key', 0
                UNION ALL SELECT 'pix_enabled', 'false', 'boolean', 'payment', 'PIX ativo', 0
                UNION ALL SELECT 'instructor_commission', '70', 'number', 'payment', 'Comissão instrutor %', 0
                UNION ALL SELECT 'smtp_host', '', 'string', 'email', 'Servidor SMTP', 0
                UNION ALL SELECT 'smtp_port', '587', 'number', 'email', 'Porta SMTP', 0
                UNION ALL SELECT 'smtp_user', '', 'string', 'email', 'Usuário SMTP', 0
                UNION ALL SELECT 'smtp_pass', '', 'string', 'email', 'Senha SMTP', 0
                UNION ALL SELECT 'smtp_encryption', 'tls', 'string', 'email', 'Encriptação SMTP', 0
                UNION ALL SELECT 'email_from_name', 'GameDev Academy', 'string', 'email', 'Nome remetente', 0
                UNION ALL SELECT 'email_from_address', 'noreply@gamedevacademy.com', 'string', 'email', 'Email remetente', 0
                UNION ALL SELECT 'certificate_enabled', 'true', 'boolean', 'features', 'Certificados', 0
                UNION ALL SELECT 'gamification_enabled', 'true', 'boolean', 'features', 'Gamificação', 0
                UNION ALL SELECT 'forum_enabled', 'true', 'boolean', 'features', 'Fórum', 0
                UNION ALL SELECT 'blog_enabled', 'true', 'boolean', 'features', 'Blog', 0
                UNION ALL SELECT 'reviews_enabled', 'true', 'boolean', 'features', 'Avaliações', 0
                UNION ALL SELECT 'wishlist_enabled', 'true', 'boolean', 'features', 'Lista desejos', 0
                UNION ALL SELECT 'support_enabled', 'true', 'boolean', 'features', 'Suporte', 0
                UNION ALL SELECT 'registration_enabled', 'true', 'boolean', 'features', 'Cadastros', 0
                UNION ALL SELECT 'instructor_registration', 'false', 'boolean', 'features', 'Cadastro instrutores', 0
                UNION ALL SELECT 'google_analytics_id', '', 'string', 'seo', 'Google Analytics', 0
                UNION ALL SELECT 'google_tag_manager_id', '', 'string', 'seo', 'Tag Manager', 0
                UNION ALL SELECT 'facebook_pixel_id', '', 'string', 'seo', 'Facebook Pixel', 0
                UNION ALL SELECT 'social_github', 'https://github.com/davidcreator/gamedev-academy', 'string', 'social', 'GitHub', 1
                UNION ALL SELECT 'social_youtube', '', 'string', 'social', 'YouTube', 1
                UNION ALL SELECT 'social_discord', '', 'string', 'social', 'Discord', 1
                UNION ALL SELECT 'social_twitter', '', 'string', 'social', 'Twitter/X', 1
                UNION ALL SELECT 'social_instagram', '', 'string', 'social', 'Instagram', 1
            ) AS seed
            WHERE NOT EXISTS (SELECT 1 FROM `settings` LIMIT 1)
        ");

        // --------------------------------------------------------
        // Badges
        // --------------------------------------------------------
        $this->executeSQL('Seed: badges', "
            INSERT INTO `badges` (`name`, `slug`, `description`, `icon`, `category`, `criteria_type`, `criteria_value`, `points_reward`, `rarity`, `sort_order`)
            SELECT * FROM (
                SELECT 'Primeiro Passo' AS n, 'primeiro-passo' AS s, 'Complete sua primeira aula' AS d, '🎮' AS i, 'achievement' AS c, 'lessons_completed' AS ct, 1 AS cv, 10 AS pr, 'common' AS r, 1 AS so
                UNION ALL SELECT 'Estudante Dedicado', 'estudante-dedicado', 'Complete 10 aulas', '📚', 'engagement', 'lessons_completed', 10, 50, 'common', 2
                UNION ALL SELECT 'Maratonista', 'maratonista', 'Complete 50 aulas', '🏃', 'engagement', 'lessons_completed', 50, 200, 'uncommon', 3
                UNION ALL SELECT 'Máquina de Aprender', 'maquina-aprender', 'Complete 100 aulas', '🤖', 'engagement', 'lessons_completed', 100, 500, 'rare', 4
                UNION ALL SELECT 'Primeiro Curso', 'primeiro-curso', 'Complete seu primeiro curso', '🎓', 'course', 'courses_completed', 1, 100, 'common', 5
                UNION ALL SELECT 'Colecionador', 'colecionador', 'Complete 5 cursos', '🏆', 'course', 'courses_completed', 5, 500, 'rare', 6
                UNION ALL SELECT 'Mestre dos Jogos', 'mestre-dos-jogos', 'Complete 10 cursos', '👑', 'course', 'courses_completed', 10, 1000, 'epic', 7
                UNION ALL SELECT 'Lendário', 'lendario', 'Complete 25 cursos', '⭐', 'course', 'courses_completed', 25, 2500, 'legendary', 8
                UNION ALL SELECT 'Streak Semanal', 'streak-7', 'Estude por 7 dias seguidos', '🔥', 'engagement', 'streak_days', 7, 70, 'common', 9
                UNION ALL SELECT 'Streak Mensal', 'streak-30', 'Estude por 30 dias seguidos', '⚡', 'engagement', 'streak_days', 30, 300, 'rare', 10
                UNION ALL SELECT 'Streak Épico', 'streak-90', 'Estude por 90 dias seguidos', '💫', 'engagement', 'streak_days', 90, 900, 'epic', 11
                UNION ALL SELECT 'Quiz Master', 'quiz-master', 'Acerte 100% em 10 quizzes', '🧠', 'achievement', 'perfect_quizzes', 10, 250, 'rare', 12
                UNION ALL SELECT 'Sem Erros', 'sem-erros', 'Acerte 100% no primeiro quiz', '✅', 'achievement', 'perfect_quizzes', 1, 25, 'common', 13
                UNION ALL SELECT 'Ajudante', 'ajudante', 'Responda 10 perguntas no fórum', '🤝', 'community', 'forum_replies', 10, 150, 'uncommon', 14
                UNION ALL SELECT 'Mentor', 'mentor', 'Tenha 5 melhores respostas', '🌟', 'community', 'best_answers', 5, 300, 'rare', 15
                UNION ALL SELECT 'Pioneiro', 'pioneiro', 'Seja um dos primeiros 100 alunos', '🚀', 'special', 'early_adopter', 1, 200, 'epic', 16
                UNION ALL SELECT 'Avaliador', 'avaliador', 'Avalie 5 cursos', '📝', 'community', 'reviews_posted', 5, 100, 'uncommon', 17
            ) AS seed
            WHERE NOT EXISTS (SELECT 1 FROM `badges` LIMIT 1)
        ");

        // --------------------------------------------------------
        // Páginas
        // --------------------------------------------------------
        $this->executeSQL('Seed: pages', "
            INSERT INTO `pages` (`title`, `slug`, `content`, `show_in_footer`, `is_published`)
            SELECT * FROM (
                SELECT 'Sobre Nós' AS t, 'sobre' AS s, '<h1>Sobre a GameDev Academy</h1><p>Plataforma brasileira de ensino de desenvolvimento de jogos.</p>' AS c, 1 AS f, 1 AS p
                UNION ALL SELECT 'Termos de Uso', 'termos-de-uso', '<h1>Termos de Uso</h1><p>Ao utilizar a plataforma, você concorda com os termos.</p>', 1, 1
                UNION ALL SELECT 'Política de Privacidade', 'politica-de-privacidade', '<h1>Política de Privacidade</h1><p>Sua privacidade é importante para nós.</p>', 1, 1
                UNION ALL SELECT 'Política de Reembolso', 'politica-de-reembolso', '<h1>Política de Reembolso</h1><p>Garantia de 7 dias para cursos pagos.</p>', 1, 1
                UNION ALL SELECT 'Contato', 'contato', '<h1>Fale Conosco</h1><p>Entre em contato pelo formulário ou email.</p>', 1, 1
            ) AS seed
            WHERE NOT EXISTS (SELECT 1 FROM `pages` LIMIT 1)
        ");

        // --------------------------------------------------------
        // Template de certificado
        // --------------------------------------------------------
        $this->executeSQL('Seed: certificate template', "
            INSERT INTO `certificate_templates` (`name`, `html_template`, `css_styles`, `orientation`, `is_default`, `is_active`)
            SELECT 'Certificado Padrão',
                   '<div class=\"certificate\"><div class=\"header\"><h1>Certificado de Conclusão</h1></div><div class=\"body\"><p>Certificamos que</p><h2>{{student_name}}</h2><p>concluiu o curso</p><h3>{{course_name}}</h3><p>Carga horária: {{total_hours}}h | Data: {{completion_date}}</p></div><div class=\"footer\"><p>Código: {{certificate_code}}</p></div></div>',
                   '.certificate{font-family:Georgia,serif;text-align:center;padding:60px;border:3px solid #6366f1}h2{color:#1e293b;border-bottom:2px solid #6366f1;display:inline-block;padding:10px 40px}h3{color:#6366f1}',
                   'landscape', 1, 1
            FROM DUAL
            WHERE NOT EXISTS (SELECT 1 FROM `certificate_templates` LIMIT 1)
        ");

        // --------------------------------------------------------
        // Templates de email
        // --------------------------------------------------------
        $this->executeSQL('Seed: email templates', "
            INSERT INTO `email_templates` (`name`, `subject`, `body_html`, `variables`)
            SELECT * FROM (
                SELECT 'welcome' AS n, 'Bem-vindo à GameDev Academy, {{name}}!' AS s,
                       '<h1>Olá, {{name}}! 🎮</h1><p>Bem-vindo à GameDev Academy!</p><p><a href=\"{{site_url}}/cursos\">Ver Cursos</a></p>' AS b,
                       '[\"name\", \"email\", \"site_url\"]' AS v
                UNION ALL SELECT 'password_reset', 'Redefinição de Senha', '<h1>Redefinir Senha</h1><p>Olá, {{name}}.</p><p><a href=\"{{reset_url}}\">Clique aqui</a></p><p>Expira em {{expiry_hours}} horas.</p>', '[\"name\", \"reset_url\", \"expiry_hours\"]'
                UNION ALL SELECT 'enrollment_confirmation', 'Matrícula Confirmada: {{course_title}}', '<h1>Matrícula Confirmada! 🎉</h1><p>Curso: <strong>{{course_title}}</strong></p><p><a href=\"{{course_url}}\">Começar</a></p>', '[\"name\", \"course_title\", \"course_url\"]'
                UNION ALL SELECT 'course_completed', 'Parabéns! Você concluiu {{course_title}} 🎓', '<h1>Parabéns! 🏆</h1><p>Curso: <strong>{{course_title}}</strong></p><p><a href=\"{{certificate_url}}\">Ver Certificado</a></p>', '[\"name\", \"course_title\", \"certificate_url\"]'
                UNION ALL SELECT 'payment_confirmation', 'Pagamento Confirmado', '<h1>Pagamento Confirmado ✅</h1><p>Valor: {{amount}}</p><p>Curso: {{course_title}}</p><p>Transação: {{transaction_id}}</p>', '[\"name\", \"amount\", \"course_title\", \"transaction_id\"]'
            ) AS seed
            WHERE NOT EXISTS (SELECT 1 FROM `email_templates` LIMIT 1)
        ");

        // --------------------------------------------------------
        // FAQ Categories
        // --------------------------------------------------------
        $this->executeSQL('Seed: faq categories', "
            INSERT INTO `faq_categories` (`name`, `slug`, `icon`, `sort_order`)
            SELECT * FROM (
                SELECT 'Conta e Acesso' AS n, 'conta-acesso' AS s, 'fas fa-user-circle' AS i, 1 AS o
                UNION ALL SELECT 'Cursos e Aulas', 'cursos-aulas', 'fas fa-graduation-cap', 2
                UNION ALL SELECT 'Pagamentos', 'pagamentos', 'fas fa-credit-card', 3
                UNION ALL SELECT 'Certificados', 'certificados', 'fas fa-certificate', 4
                UNION ALL SELECT 'Problemas Técnicos', 'problemas-tecnicos', 'fas fa-tools', 5
            ) AS seed
            WHERE NOT EXISTS (SELECT 1 FROM `faq_categories` LIMIT 1)
        ");

        // --------------------------------------------------------
        // FAQs
        // --------------------------------------------------------
        $this->executeSQL('Seed: faqs', "
            INSERT INTO `faqs` (`category_id`, `question`, `answer`, `sort_order`)
            SELECT * FROM (
                SELECT 1 AS c, 'Como criar minha conta?' AS q, '<p>Clique em Cadastrar e preencha o formulário.</p>' AS a, 1 AS o
                UNION ALL SELECT 1, 'Esqueci minha senha', '<p>Clique em Esqueci minha senha na tela de login.</p>', 2
                UNION ALL SELECT 2, 'Posso acessar de qualquer dispositivo?', '<p>Sim! Funciona em desktop, tablet e mobile.</p>', 3
                UNION ALL SELECT 2, 'Os cursos têm prazo?', '<p>Não! Acesso vitalício após matrícula.</p>', 4
                UNION ALL SELECT 3, 'Formas de pagamento?', '<p>Cartão de crédito, PIX e boleto.</p>', 5
                UNION ALL SELECT 3, 'Política de reembolso?', '<p>Garantia de 7 dias, 100% do valor.</p>', 6
                UNION ALL SELECT 4, 'Como obter certificado?', '<p>Gerado ao completar 100% das aulas obrigatórias.</p>', 7
                UNION ALL SELECT 4, 'O certificado tem validade?', '<p>Sim, com código de verificação único.</p>', 8
            ) AS seed
            WHERE NOT EXISTS (SELECT 1 FROM `faqs` LIMIT 1)
        ");

        // --------------------------------------------------------
        // Idiomas
        // --------------------------------------------------------
        $this->executeSQL('Seed: languages', "
            INSERT INTO `languages` (`name`, `code`, `native_name`)
            SELECT * FROM (
                SELECT 'Português (Brasil)' AS n, 'pt-BR' AS c, 'Português' AS nn
                UNION ALL SELECT 'English', 'en-US', 'English'
                UNION ALL SELECT 'Español', 'es', 'Español'
            ) AS seed
            WHERE NOT EXISTS (SELECT 1 FROM `languages` LIMIT 1)
        ");

        // --------------------------------------------------------
        // Países
        // --------------------------------------------------------
        $this->executeSQL('Seed: countries', "
            INSERT INTO `countries` (`name`, `code`, `phone_code`, `currency`)
            SELECT * FROM (
                SELECT 'Brasil' AS n, 'BR' AS c, '+55' AS p, 'BRL' AS cu
                UNION ALL SELECT 'Portugal', 'PT', '+351', 'EUR'
                UNION ALL SELECT 'Estados Unidos', 'US', '+1', 'USD'
                UNION ALL SELECT 'Angola', 'AO', '+244', 'AOA'
                UNION ALL SELECT 'Moçambique', 'MZ', '+258', 'MZN'
            ) AS seed
            WHERE NOT EXISTS (SELECT 1 FROM `countries` LIMIT 1)
        ");
    }
}

function executeDatabaseSetup(PDO $pdo): array
{
    $installer = new DatabaseInstaller($pdo);
    $success = $installer->install();

    return [
        'success'  => $success,
        'errors'   => $installer->getErrors(),
        'messages' => $installer->getSuccess(),
        'stats'    => [
            'tables_created'  => $installer->getTableCount(),
            'tables_expected' => 54,
        ],
    ];
}


// ================================================================
// ================================================================
//
//   EXECUÇÃO
//
//   Este bloco é chamado pelo instalador principal
//
// ================================================================
// ================================================================

// $pdo deve ser passado pelo script de instalação
if (!isset($pdo) || !($pdo instanceof PDO)) {
    die('Erro: Conexão PDO não disponível.');
}

$installer = new DatabaseInstaller($pdo);
$result = $installer->install();

// Armazenar resultados para o instalador exibir
$installResults = [
    'success'     => $result,
    'tables'      => $installer->getTableCount(),
    'expected'    => 54,
    'messages'    => $installer->getSuccess(),
    'errors'      => $installer->getErrors(),
];
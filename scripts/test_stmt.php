<?php
$pdo = new PDO(
    'mysql:host=localhost;dbname=gda_test;charset=utf8mb4',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$pdo->exec('SET FOREIGN_KEY_CHECKS=0;');

$sql = <<<SQL
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
SQL;

$start = microtime(true);

foreach (explode(';', $sql) as $chunk) {
    $chunk = trim($chunk);
    if (!$chunk) continue;
    $pdo->exec($chunk);
}

echo "OK\nTime: " . (microtime(true) - $start) . "s\n";

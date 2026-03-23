<?php
// scripts/install-business-finance-upgrade.php

require __DIR__ . '/../config/database.php';

function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare("
        SELECT 1
        FROM information_schema.tables
        WHERE table_schema = DATABASE()
          AND table_name = ?
        LIMIT 1
    ");
    $stmt->execute([$table]);
    return (bool) $stmt->fetchColumn();
}

function settingExists(PDO $pdo, string $key): bool
{
    if (!tableExists($pdo, 'settings')) {
        return false;
    }

    $stmt = $pdo->prepare("SELECT id FROM settings WHERE setting_key = ? LIMIT 1");
    $stmt->execute([$key]);
    return (bool) $stmt->fetchColumn();
}

echo "Aplicando upgrade de negocio, certificados e financeiro...\n";

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `financial_expenses` (
            `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `title`        VARCHAR(255) NOT NULL,
            `category`     VARCHAR(100) NOT NULL DEFAULT 'geral',
            `amount`       DECIMAL(10,2) NOT NULL,
            `currency`     VARCHAR(3) NOT NULL DEFAULT 'BRL',
            `expense_date` DATE NOT NULL,
            `status`       ENUM('planned','approved','paid','cancelled') NOT NULL DEFAULT 'planned',
            `vendor_name`  VARCHAR(255) DEFAULT NULL,
            `notes`        TEXT DEFAULT NULL,
            `created_by`   INT UNSIGNED DEFAULT NULL,
            `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_financial_expenses_date` (`expense_date`),
            KEY `idx_financial_expenses_status` (`status`),
            KEY `idx_financial_expenses_category` (`category`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "- tabela financial_expenses pronta\n";

    $settings = [
        [
            'setting_key' => 'certificate_enabled',
            'setting_label' => 'Certificados habilitados',
            'setting_value' => '1',
            'setting_type' => 'boolean',
            'setting_group' => 'features',
            'description' => 'Liga ou desliga a emissao de certificados',
            'is_public' => 0,
        ],
        [
            'setting_key' => 'certificate_free_on_completion',
            'setting_label' => 'Certificado em curso gratuito',
            'setting_value' => '1',
            'setting_type' => 'boolean',
            'setting_group' => 'finance',
            'description' => 'Emite certificado para curso gratuito ao concluir',
            'is_public' => 0,
        ],
        [
            'setting_key' => 'certificate_paid_requires_payment',
            'setting_label' => 'Certificado exige pagamento',
            'setting_value' => '1',
            'setting_type' => 'boolean',
            'setting_group' => 'finance',
            'description' => 'Em cursos pagos, exige conclusao e pagamento confirmado',
            'is_public' => 0,
        ],
        [
            'setting_key' => 'certificate_subscription_requires_active_paid_plan',
            'setting_label' => 'Certificado via assinatura ativa',
            'setting_value' => '1',
            'setting_type' => 'boolean',
            'setting_group' => 'finance',
            'description' => 'Permite certificado quando o acesso vier de assinatura paga ativa',
            'is_public' => 0,
        ],
        [
            'setting_key' => 'finance_long_course_min_hours',
            'setting_label' => 'Carga horaria de curso longo',
            'setting_value' => '40',
            'setting_type' => 'number',
            'setting_group' => 'finance',
            'description' => 'Limite em horas para classificar curso de longa duracao',
            'is_public' => 0,
        ],
        [
            'setting_key' => 'finance_paid_course_instructor_rate',
            'setting_label' => 'Repasse curso pago',
            'setting_value' => '60',
            'setting_type' => 'number',
            'setting_group' => 'finance',
            'description' => 'Percentual do instrutor nos cursos pagos avulsos',
            'is_public' => 0,
        ],
        [
            'setting_key' => 'finance_long_course_instructor_rate',
            'setting_label' => 'Repasse curso longo',
            'setting_value' => '70',
            'setting_type' => 'number',
            'setting_group' => 'finance',
            'description' => 'Percentual do instrutor nos cursos de longa duracao',
            'is_public' => 0,
        ],
        [
            'setting_key' => 'finance_subscription_instructor_pool_rate',
            'setting_label' => 'Pool de assinatura para instrutores',
            'setting_value' => '40',
            'setting_type' => 'number',
            'setting_group' => 'finance',
            'description' => 'Percentual da receita recorrente reservado para o pool de instrutores',
            'is_public' => 0,
        ],
        [
            'setting_key' => 'finance_payout_hold_days',
            'setting_label' => 'Retencao de repasse',
            'setting_value' => '14',
            'setting_type' => 'number',
            'setting_group' => 'finance',
            'description' => 'Dias de retencao antes do repasse ao instrutor',
            'is_public' => 0,
        ],
        [
            'setting_key' => 'finance_payout_cycle',
            'setting_label' => 'Ciclo de pagamento',
            'setting_value' => 'monthly',
            'setting_type' => 'string',
            'setting_group' => 'finance',
            'description' => 'Periodicidade de fechamento financeiro para instrutores',
            'is_public' => 0,
        ],
        [
            'setting_key' => 'finance_expense_categories',
            'setting_label' => 'Categorias de despesas',
            'setting_value' => '["infraestrutura","marketing","ferramentas","suporte","juridico","tributos","pessoal"]',
            'setting_type' => 'json',
            'setting_group' => 'finance',
            'description' => 'Categorias padrao para lancamentos financeiros',
            'is_public' => 0,
        ],
    ];

    if (tableExists($pdo, 'settings')) {
        $insert = $pdo->prepare("
            INSERT INTO settings
                (setting_key, setting_label, setting_value, setting_type, setting_group, description, is_public)
            VALUES
                (:setting_key, :setting_label, :setting_value, :setting_type, :setting_group, :description, :is_public)
        ");

        foreach ($settings as $setting) {
            if (!settingExists($pdo, $setting['setting_key'])) {
                $insert->execute($setting);
                echo "- setting adicionada: {$setting['setting_key']}\n";
            }
        }
    } else {
        echo "- tabela settings nao encontrada; configuracoes nao foram registradas\n";
    }

    echo "Upgrade concluido com sucesso.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Falha no upgrade: " . $e->getMessage() . "\n");
    exit(1);
}

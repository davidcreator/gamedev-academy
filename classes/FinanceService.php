<?php
// classes/FinanceService.php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Setting.php';

class FinanceService
{
    private Database $db;
    private Setting $settings;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->settings = new Setting();
    }

    public function hasExpenseTable(): bool
    {
        return $this->tableExists('financial_expenses');
    }

    public function getBusinessRules(): array
    {
        return [
            'long_course_hours' => $this->settings->getInt('finance_long_course_min_hours', 40),
            'paid_course_rate' => $this->settings->getFloat('finance_paid_course_instructor_rate', 60.0),
            'long_course_rate' => $this->settings->getFloat('finance_long_course_instructor_rate', 70.0),
            'subscription_pool_rate' => $this->settings->getFloat('finance_subscription_instructor_pool_rate', 40.0),
            'payout_hold_days' => $this->settings->getInt('finance_payout_hold_days', 14),
            'payout_cycle' => $this->settings->get('finance_payout_cycle', 'monthly'),
            'free_certificate_on_completion' => $this->settings->getBool('certificate_free_on_completion', true),
            'paid_certificate_requires_payment' => $this->settings->getBool('certificate_paid_requires_payment', true),
            'expense_categories' => $this->settings->getJson('finance_expense_categories', [
                'infraestrutura',
                'marketing',
                'ferramentas',
                'suporte',
                'juridico',
                'tributos',
                'pessoal',
            ]),
        ];
    }

    public function getOverview(?string $from = null, ?string $to = null): array
    {
        $hasPayments = $this->tableExists('payments');
        $hasSubscriptions = $this->tableExists('subscriptions');
        $hasPayouts = $this->tableExists('instructor_payouts');

        $paymentParams = [];
        $paymentFilter = $this->buildDateFilter('COALESCE(paid_at, created_at)', $from, $to, $paymentParams);

        $directRevenue = $hasPayments ? ($this->db->fetch(
            "SELECT
                COUNT(*) AS total_sales,
                COALESCE(SUM(amount), 0) AS gross_revenue,
                COALESCE(SUM(discount_amount), 0) AS total_discounts,
                COALESCE(SUM(amount - discount_amount), 0) AS net_revenue
             FROM payments
             WHERE status = 'completed'
               AND course_id IS NOT NULL{$paymentFilter}",
            $paymentParams
        ) ?: []) : [];

        $refundParams = [];
        $refundFilter = $this->buildDateFilter('COALESCE(refunded_at, updated_at, created_at)', $from, $to, $refundParams);

        $refunds = $hasPayments ? ($this->db->fetch(
            "SELECT
                COUNT(*) AS total_refunds,
                COALESCE(SUM(COALESCE(refunded_amount, amount - discount_amount)), 0) AS refunded_total
             FROM payments
             WHERE status IN ('refunded', 'chargeback'){$refundFilter}",
            $refundParams
        ) ?: []) : [];

        $activeSubscriptions = $hasSubscriptions ? ($this->db->fetch(
            "SELECT
                COUNT(*) AS active_count,
                COALESCE(SUM(CASE WHEN billing_cycle = 'annual' THEN amount / 12 ELSE amount END), 0) AS mrr,
                COALESCE(SUM(CASE WHEN billing_cycle = 'annual' THEN amount ELSE amount * 12 END), 0) AS arr
             FROM subscriptions
             WHERE status = 'active'"
        ) ?: []) : [];

        $subParams = [];
        $subFilter = $this->buildDateFilter('started_at', $from, $to, $subParams);
        $newSubscriptions = $hasSubscriptions ? ($this->db->fetch(
            "SELECT COUNT(*) AS new_count
             FROM subscriptions
             WHERE status IN ('active', 'trialing', 'past_due'){$subFilter}",
            $subParams
        ) ?: []) : [];

        $expenseSummary = $this->getExpenseSummary($from, $to);

        $payoutParams = [];
        $payoutFilter = $this->buildDateFilter('created_at', $from, $to, $payoutParams);
        $pendingPayouts = $hasPayouts ? ($this->db->fetch(
            "SELECT COALESCE(SUM(amount), 0) AS total
             FROM instructor_payouts
             WHERE status IN ('pending', 'processing'){$payoutFilter}",
            $payoutParams
        ) ?: []) : [];
        $paidPayouts = $hasPayouts ? ($this->db->fetch(
            "SELECT COALESCE(SUM(amount), 0) AS total
             FROM instructor_payouts
             WHERE status = 'completed'{$payoutFilter}",
            $payoutParams
        ) ?: []) : [];

        $enrollmentParams = [];
        $enrollmentFilter = $this->buildDateFilter('e.enrolled_at', $from, $to, $enrollmentParams);
        $freeCourses = $this->db->fetch(
            "SELECT
                COUNT(*) AS free_enrollments,
                COALESCE(SUM(CASE WHEN e.completed_at IS NOT NULL THEN 1 ELSE 0 END), 0) AS free_completions
             FROM enrollments e
             INNER JOIN courses c ON c.id = e.course_id
             WHERE c.is_free = 1{$enrollmentFilter}",
            $enrollmentParams
        ) ?: [];

        $directNet = (float) ($directRevenue['net_revenue'] ?? 0);
        $paidExpenses = (float) ($expenseSummary['paid_total'] ?? 0);
        $paidPayoutsTotal = (float) ($paidPayouts['total'] ?? 0);

        return [
            'total_sales' => (int) ($directRevenue['total_sales'] ?? 0),
            'gross_revenue' => $this->toMoney($directRevenue['gross_revenue'] ?? 0),
            'total_discounts' => $this->toMoney($directRevenue['total_discounts'] ?? 0),
            'net_revenue' => $this->toMoney($directNet),
            'total_refunds' => (int) ($refunds['total_refunds'] ?? 0),
            'refunded_total' => $this->toMoney($refunds['refunded_total'] ?? 0),
            'active_subscriptions' => (int) ($activeSubscriptions['active_count'] ?? 0),
            'subscription_mrr' => $this->toMoney($activeSubscriptions['mrr'] ?? 0),
            'subscription_arr' => $this->toMoney($activeSubscriptions['arr'] ?? 0),
            'new_subscriptions' => (int) ($newSubscriptions['new_count'] ?? 0),
            'planned_expenses' => $this->toMoney($expenseSummary['planned_total'] ?? 0),
            'approved_expenses' => $this->toMoney($expenseSummary['approved_total'] ?? 0),
            'paid_expenses' => $this->toMoney($paidExpenses),
            'pending_payouts' => $this->toMoney($pendingPayouts['total'] ?? 0),
            'paid_payouts' => $this->toMoney($paidPayoutsTotal),
            'operating_balance' => $this->toMoney($directNet - $paidExpenses - $paidPayoutsTotal),
            'free_enrollments' => (int) ($freeCourses['free_enrollments'] ?? 0),
            'free_completions' => (int) ($freeCourses['free_completions'] ?? 0),
        ];
    }

    public function getRevenueBreakdown(?string $from = null, ?string $to = null): array
    {
        $hasPayments = $this->tableExists('payments');
        $hasSubscriptions = $this->tableExists('subscriptions');
        $rules = $this->getBusinessRules();
        $longHours = (int) $rules['long_course_hours'];

        $params = [];
        $paymentFilter = $this->buildDateFilter('COALESCE(p.paid_at, p.created_at)', $from, $to, $params);

        $payments = $hasPayments ? $this->db->fetchAll(
            "SELECT
                c.id,
                c.title,
                c.is_free,
                c.duration_hours,
                p.amount,
                p.discount_amount
             FROM payments p
             INNER JOIN courses c ON c.id = p.course_id
             WHERE p.status = 'completed'
               AND p.course_id IS NOT NULL{$paymentFilter}",
            $params
        ) : [];

        $shortPaidRevenue = 0.0;
        $longPaidRevenue = 0.0;
        $shortPaidSales = 0;
        $longPaidSales = 0;

        foreach ($payments as $payment) {
            $net = $this->netAmount($payment);
            if ((float) ($payment['duration_hours'] ?? 0) >= $longHours) {
                $longPaidRevenue += $net;
                $longPaidSales++;
            } else {
                $shortPaidRevenue += $net;
                $shortPaidSales++;
            }
        }

        $subscriptions = $hasSubscriptions ? ($this->db->fetch(
            "SELECT
                COUNT(*) AS active_plans,
                COALESCE(SUM(CASE WHEN billing_cycle = 'annual' THEN amount / 12 ELSE amount END), 0) AS mrr
             FROM subscriptions
             WHERE status = 'active'"
        ) ?: []) : [];

        $freeParams = [];
        $freeFilter = $this->buildDateFilter('e.enrolled_at', $from, $to, $freeParams);

        $freeMetrics = $this->db->fetch(
            "SELECT
                COUNT(*) AS enrollments,
                COALESCE(SUM(CASE WHEN e.completed_at IS NOT NULL THEN 1 ELSE 0 END), 0) AS completions
             FROM enrollments e
             INNER JOIN courses c ON c.id = e.course_id
             WHERE c.is_free = 1{$freeFilter}",
            $freeParams
        ) ?: [];

        return [
            [
                'line' => 'Cursos gratuitos',
                'metric_a' => (int) ($freeMetrics['enrollments'] ?? 0) . ' matrículas',
                'metric_b' => (int) ($freeMetrics['completions'] ?? 0) . ' conclusões',
                'value' => $this->toMoney(0),
            ],
            [
                'line' => 'Cursos pagos avulsos',
                'metric_a' => $shortPaidSales . ' vendas',
                'metric_b' => '< ' . $longHours . 'h',
                'value' => $this->toMoney($shortPaidRevenue),
            ],
            [
                'line' => 'Cursos de longa duração',
                'metric_a' => $longPaidSales . ' vendas',
                'metric_b' => '>= ' . $longHours . 'h',
                'value' => $this->toMoney($longPaidRevenue),
            ],
            [
                'line' => 'Assinaturas',
                'metric_a' => (int) ($subscriptions['active_plans'] ?? 0) . ' assinaturas ativas',
                'metric_b' => 'MRR projetado',
                'value' => $this->toMoney($subscriptions['mrr'] ?? 0),
            ],
        ];
    }

    public function getInstructorPayoutPreview(?string $from = null, ?string $to = null, int $limit = 10): array
    {
        if (!$this->tableExists('payments')) {
            return [];
        }

        $rules = $this->getBusinessRules();
        $longHours = (int) $rules['long_course_hours'];

        $paymentParams = [];
        $paymentFilter = $this->buildDateFilter('COALESCE(p.paid_at, p.created_at)', $from, $to, $paymentParams);

        $rows = $this->db->fetchAll(
            "SELECT
                p.id AS payment_id,
                p.amount,
                p.discount_amount,
                c.id AS course_id,
                c.title AS course_title,
                c.duration_hours,
                c.is_free,
                c.instructor_id,
                u.full_name AS instructor_name
             FROM payments p
             INNER JOIN courses c ON c.id = p.course_id
             INNER JOIN users u ON u.id = c.instructor_id
             WHERE p.status = 'completed'
               AND p.course_id IS NOT NULL{$paymentFilter}",
            $paymentParams
        );

        $grouped = [];
        foreach ($rows as $row) {
            $instructorId = (int) $row['instructor_id'];
            $netAmount = $this->netAmount($row);
            $rate = $this->resolveInstructorRate($row, $longHours);
            $instructorShare = round($netAmount * ($rate / 100), 2);
            $platformShare = round($netAmount - $instructorShare, 2);

            if (!isset($grouped[$instructorId])) {
                $grouped[$instructorId] = [
                    'instructor_id' => $instructorId,
                    'instructor_name' => $row['instructor_name'] ?: 'Instrutor',
                    'sales_count' => 0,
                    'gross_sales' => 0.0,
                    'instructor_rate_avg' => 0.0,
                    'instructor_share' => 0.0,
                    'platform_share' => 0.0,
                    'long_courses_sales' => 0,
                ];
            }

            $grouped[$instructorId]['sales_count']++;
            $grouped[$instructorId]['gross_sales'] += $netAmount;
            $grouped[$instructorId]['instructor_share'] += $instructorShare;
            $grouped[$instructorId]['platform_share'] += $platformShare;
            $grouped[$instructorId]['instructor_rate_avg'] += $rate;

            if ((float) ($row['duration_hours'] ?? 0) >= $longHours) {
                $grouped[$instructorId]['long_courses_sales']++;
            }
        }

        foreach ($grouped as &$item) {
            $salesCount = max(1, (int) $item['sales_count']);
            $item['instructor_rate_avg'] = round($item['instructor_rate_avg'] / $salesCount, 2);
            $item['gross_sales'] = $this->toMoney($item['gross_sales']);
            $item['instructor_share'] = $this->toMoney($item['instructor_share']);
            $item['platform_share'] = $this->toMoney($item['platform_share']);
        }
        unset($item);

        usort($grouped, static function (array $a, array $b): int {
            return $b['instructor_share'] <=> $a['instructor_share'];
        });

        return array_slice($grouped, 0, $limit);
    }

    public function getRecentExpenses(?string $from = null, ?string $to = null, int $limit = 15): array
    {
        if (!$this->hasExpenseTable()) {
            return [];
        }

        $params = [];
        $filter = $this->buildDateFilter('expense_date', $from, $to, $params);
        $params[] = $limit;

        return $this->db->fetchAll(
            "SELECT *
             FROM financial_expenses
             WHERE 1 = 1{$filter}
             ORDER BY expense_date DESC, id DESC
             LIMIT ?",
            $params
        );
    }

    public function getExpenseCategorySummary(?string $from = null, ?string $to = null): array
    {
        if (!$this->hasExpenseTable()) {
            return [];
        }

        $params = [];
        $filter = $this->buildDateFilter('expense_date', $from, $to, $params);

        return $this->db->fetchAll(
            "SELECT
                category,
                COUNT(*) AS total_items,
                COALESCE(SUM(amount), 0) AS total_amount
             FROM financial_expenses
             WHERE status <> 'cancelled'{$filter}
             GROUP BY category
             ORDER BY total_amount DESC",
            $params
        );
    }

    public function createExpense(array $data, int $userId): array
    {
        if (!$this->hasExpenseTable()) {
            return ['success' => false, 'message' => 'Tabela financial_expenses não encontrada.'];
        }

        $title = trim((string) ($data['title'] ?? ''));
        $category = trim((string) ($data['category'] ?? 'geral'));
        $amount = (float) str_replace(',', '.', (string) ($data['amount'] ?? 0));
        $expenseDate = $this->normalizeDate($data['expense_date'] ?? '');
        $status = (string) ($data['status'] ?? 'planned');
        $vendorName = trim((string) ($data['vendor_name'] ?? ''));
        $notes = trim((string) ($data['notes'] ?? ''));

        if ($title === '' || $amount <= 0 || $expenseDate === null) {
            return ['success' => false, 'message' => 'Preencha título, valor e data da despesa.'];
        }

        if (!in_array($status, ['planned', 'approved', 'paid', 'cancelled'], true)) {
            $status = 'planned';
        }

        $this->db->insert('financial_expenses', [
            'title' => $title,
            'category' => $category,
            'amount' => $amount,
            'currency' => $this->settings->get('currency', 'BRL'),
            'expense_date' => $expenseDate,
            'status' => $status,
            'vendor_name' => $vendorName ?: null,
            'notes' => $notes ?: null,
            'created_by' => $userId,
        ]);

        return ['success' => true, 'message' => 'Despesa registrada com sucesso.'];
    }

    private function getExpenseSummary(?string $from, ?string $to): array
    {
        if (!$this->hasExpenseTable()) {
            return [
                'planned_total' => 0,
                'approved_total' => 0,
                'paid_total' => 0,
            ];
        }

        $params = [];
        $filter = $this->buildDateFilter('expense_date', $from, $to, $params);

        return $this->db->fetch(
            "SELECT
                COALESCE(SUM(CASE WHEN status = 'planned' THEN amount ELSE 0 END), 0) AS planned_total,
                COALESCE(SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END), 0) AS approved_total,
                COALESCE(SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END), 0) AS paid_total
             FROM financial_expenses
             WHERE 1 = 1{$filter}",
            $params
        ) ?: [];
    }

    private function resolveInstructorRate(array $row, int $longHours): float
    {
        if (!empty($row['is_free'])) {
            return 0.0;
        }

        if ((float) ($row['duration_hours'] ?? 0) >= $longHours) {
            return $this->settings->getFloat('finance_long_course_instructor_rate', 70.0);
        }

        return $this->settings->getFloat('finance_paid_course_instructor_rate', 60.0);
    }

    private function netAmount(array $row): float
    {
        return round((float) ($row['amount'] ?? 0) - (float) ($row['discount_amount'] ?? 0), 2);
    }

    private function buildDateFilter(string $field, ?string $from, ?string $to, array &$params): string
    {
        $clauses = [];

        $fromDate = $this->normalizeDate($from);
        if ($fromDate !== null) {
            $clauses[] = "{$field} >= ?";
            $params[] = $fromDate . ' 00:00:00';
        }

        $toDate = $this->normalizeDate($to);
        if ($toDate !== null) {
            $clauses[] = "{$field} <= ?";
            $params[] = $toDate . ' 23:59:59';
        }

        return $clauses ? ' AND ' . implode(' AND ', $clauses) : '';
    }

    private function normalizeDate(?string $date): ?string
    {
        $date = trim((string) $date);
        if ($date === '') {
            return null;
        }

        $parsed = date_create($date);
        if (!$parsed) {
            return null;
        }

        return $parsed->format('Y-m-d');
    }

    private function tableExists(string $table): bool
    {
        try {
            return $this->db->fetch(
                "SELECT 1
                 FROM information_schema.tables
                 WHERE table_schema = DATABASE()
                   AND table_name = ?
                 LIMIT 1",
                [$table]
            ) !== null;
        } catch (Throwable $e) {
            return false;
        }
    }

    private function toMoney($value): float
    {
        return round((float) $value, 2);
    }
}

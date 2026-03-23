<?php
// classes/CertificateService.php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Setting.php';

class CertificateService
{
    private Database $db;
    private Setting $settings;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->settings = new Setting();
    }

    public function issueForEnrollment(int $enrollmentId): array
    {
        if (!$this->tableExists('certificates') || !$this->tableExists('enrollments')) {
            return ['issued' => false, 'reason' => 'schema_missing'];
        }

        if (!$this->settings->getBool('certificate_enabled', true)) {
            return ['issued' => false, 'reason' => 'disabled'];
        }

        $enrollment = $this->db->fetch(
            "SELECT e.*, c.title AS course_title, c.slug AS course_slug, c.price, c.is_free, c.duration_hours,
                    u.full_name, u.name, u.username, u.email
             FROM enrollments e
             INNER JOIN courses c ON c.id = e.course_id
             INNER JOIN users u ON u.id = e.user_id
             WHERE e.id = ?
             LIMIT 1",
            [$enrollmentId]
        );

        if (!$enrollment) {
            return ['issued' => false, 'reason' => 'enrollment_not_found'];
        }

        if (!$this->isEnrollmentCompleted($enrollment)) {
            return ['issued' => false, 'reason' => 'course_not_completed'];
        }

        $existing = $this->db->fetch(
            "SELECT id, certificate_code FROM certificates WHERE user_id = ? AND course_id = ? LIMIT 1",
            [$enrollment['user_id'], $enrollment['course_id']]
        );

        if ($existing) {
            $this->markEnrollmentAsIssued((int) $enrollment['id']);

            return [
                'issued' => false,
                'reason' => 'already_issued',
                'certificate_id' => (int) $existing['id'],
                'certificate_code' => $existing['certificate_code'],
            ];
        }

        $eligibility = $this->resolveEligibility($enrollment);
        if (!$eligibility['eligible']) {
            return [
                'issued' => false,
                'reason' => $eligibility['reason'],
            ];
        }

        $template = $this->tableExists('certificate_templates')
            ? $this->db->fetch(
                "SELECT id FROM certificate_templates WHERE is_active = 1 ORDER BY is_default DESC, id ASC LIMIT 1"
            )
            : null;

        $certificateCode = $this->generateCertificateCode((int) $enrollment['course_id'], (int) $enrollment['user_id']);

        $certificateId = $this->db->insert('certificates', [
            'user_id' => $enrollment['user_id'],
            'course_id' => $enrollment['course_id'],
            'template_id' => $template['id'] ?? null,
            'certificate_code' => $certificateCode,
            'certificate_url' => null,
            'final_grade' => null,
            'total_hours' => $enrollment['duration_hours'] ?? null,
            'metadata' => json_encode([
                'student_name' => $this->resolveStudentName($enrollment),
                'course_title' => $enrollment['course_title'],
                'completion_date' => $enrollment['completed_at'],
                'eligibility_source' => $eligibility['source'],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        $this->markEnrollmentAsIssued((int) $enrollment['id']);

        return [
            'issued' => true,
            'reason' => 'issued',
            'certificate_id' => $certificateId,
            'certificate_code' => $certificateCode,
        ];
    }

    private function isEnrollmentCompleted(array $enrollment): bool
    {
        return !empty($enrollment['completed_at']) || (
            isset($enrollment['progress_percent']) && (float) $enrollment['progress_percent'] >= 100
        );
    }

    private function resolveEligibility(array $enrollment): array
    {
        $isFreeCourse = !empty($enrollment['is_free']) || (float) ($enrollment['price'] ?? 0) <= 0;

        if ($isFreeCourse && $this->settings->getBool('certificate_free_on_completion', true)) {
            return ['eligible' => true, 'source' => 'free_course', 'reason' => 'free_course_completed'];
        }

        if (!$this->settings->getBool('certificate_paid_requires_payment', true)) {
            return ['eligible' => true, 'source' => 'completion_only', 'reason' => 'payment_not_required'];
        }

        $payment = $this->findCompletedPayment($enrollment);
        if ($payment) {
            return ['eligible' => true, 'source' => 'payment', 'reason' => 'paid_course_completed'];
        }

        $subscription = $this->findEligibleSubscription((int) $enrollment['user_id']);
        if ($subscription) {
            return ['eligible' => true, 'source' => 'subscription', 'reason' => 'active_paid_subscription'];
        }

        return ['eligible' => false, 'source' => null, 'reason' => 'payment_required'];
    }

    private function findCompletedPayment(array $enrollment): ?array
    {
        if (!$this->tableExists('payments')) {
            return null;
        }

        if (!empty($enrollment['payment_id'])) {
            $payment = $this->db->fetch(
                "SELECT * FROM payments WHERE id = ? LIMIT 1",
                [$enrollment['payment_id']]
            );

            if ($this->isPaymentSettled($payment)) {
                return $payment;
            }
        }

        $payment = $this->db->fetch(
            "SELECT * FROM payments
             WHERE user_id = ? AND course_id = ?
             ORDER BY paid_at DESC, created_at DESC
             LIMIT 1",
            [$enrollment['user_id'], $enrollment['course_id']]
        );

        return $this->isPaymentSettled($payment) ? $payment : null;
    }

    private function isPaymentSettled(?array $payment): bool
    {
        if (!$payment) {
            return false;
        }

        return in_array($payment['status'] ?? '', ['completed'], true);
    }

    private function findEligibleSubscription(int $userId): ?array
    {
        if (!$this->tableExists('subscriptions') || !$this->tableExists('subscription_plans')) {
            return null;
        }

        if (!$this->settings->getBool('certificate_subscription_requires_active_paid_plan', true)) {
            return null;
        }

        try {
            return $this->db->fetch(
                "SELECT s.*, sp.name AS plan_name, sp.has_certificates
                 FROM subscriptions s
                 INNER JOIN subscription_plans sp ON sp.id = s.plan_id
                 WHERE s.user_id = ?
                   AND s.status = 'active'
                   AND s.amount > 0
                   AND sp.has_certificates = 1
                   AND (s.current_period_end IS NULL OR s.current_period_end >= NOW())
                 ORDER BY s.current_period_end DESC, s.id DESC
                 LIMIT 1",
                [$userId]
            );
        } catch (Throwable $e) {
            return null;
        }
    }

    private function markEnrollmentAsIssued(int $enrollmentId): void
    {
        $this->db->query(
            "UPDATE enrollments SET certificate_issued = 1 WHERE id = ?",
            [$enrollmentId]
        );
    }

    private function resolveStudentName(array $enrollment): string
    {
        $name = trim((string) ($enrollment['full_name'] ?? ''));
        if ($name !== '') {
            return $name;
        }

        $name = trim((string) ($enrollment['name'] ?? ''));
        if ($name !== '') {
            return $name;
        }

        return (string) ($enrollment['username'] ?? $enrollment['email'] ?? 'Aluno');
    }

    private function generateCertificateCode(int $courseId, int $userId): string
    {
        do {
            $suffix = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            $code = sprintf('GDA-%d-%d-%s', $courseId, $userId, $suffix);

            $exists = $this->db->fetch(
                "SELECT id FROM certificates WHERE certificate_code = ? LIMIT 1",
                [$code]
            );
        } while ($exists);

        return $code;
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
}

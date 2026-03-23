<?php
$pageTitle = 'Financeiro';
require_once __DIR__ . '/../../classes/FinanceService.php';
include '../includes/header.php';

$finance = new FinanceService();
$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_expense') {
    $result = $finance->createExpense($_POST, (int) ($currentUser['id'] ?? 0));

    if ($result['success']) {
        flash('success', $result['message']);
    } else {
        flash('error', $result['message']);
    }

    redirect(url('admin/finance/index.php?from=' . urlencode($from) . '&to=' . urlencode($to)));
}

$overview = $finance->getOverview($from, $to);
$rules = $finance->getBusinessRules();
$breakdown = $finance->getRevenueBreakdown($from, $to);
$payoutPreview = $finance->getInstructorPayoutPreview($from, $to, 12);
$recentExpenses = $finance->getRecentExpenses($from, $to, 12);
$expenseCategories = $finance->getExpenseCategorySummary($from, $to);
$expenseTableReady = $finance->hasExpenseTable();

$money = static fn($value): string => 'R$ ' . number_format((float) $value, 2, ',', '.');
?>

<?= showFlashMessages() ?>

<?php if (!$expenseTableReady): ?>
<div class="alert alert-warning">
    O modulo de despesas ainda nao foi instalado neste banco. Para habilitar a gestao financeira completa, rode
    <code>php scripts/install-business-finance-upgrade.php</code>.
</div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="d-flex gap-2 align-end" style="flex-wrap: wrap;">
            <div>
                <label class="form-label">De</label>
                <input type="date" name="from" class="form-control" value="<?= escape($from) ?>">
            </div>
            <div>
                <label class="form-label">Até</label>
                <input type="date" name="to" class="form-control" value="<?= escape($to) ?>">
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Atualizar relatório</button>
            </div>
        </form>
    </div>
</div>

<div class="admin-stats">
    <div class="admin-stat-card">
        <div class="admin-stat-header">
            <div>
                <div class="admin-stat-value"><?= $money($overview['gross_revenue']) ?></div>
                <div class="admin-stat-label">Receita bruta de cursos</div>
            </div>
            <div class="admin-stat-icon primary">R$</div>
        </div>
        <div class="admin-stat-change"><?= (int) $overview['total_sales'] ?> vendas concluídas</div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-header">
            <div>
                <div class="admin-stat-value"><?= $money($overview['net_revenue']) ?></div>
                <div class="admin-stat-label">Receita líquida de cursos</div>
            </div>
            <div class="admin-stat-icon success">+</div>
        </div>
        <div class="admin-stat-change">Descontos: <?= $money($overview['total_discounts']) ?></div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-header">
            <div>
                <div class="admin-stat-value"><?= $money($overview['subscription_mrr']) ?></div>
                <div class="admin-stat-label">MRR de assinaturas</div>
            </div>
            <div class="admin-stat-icon warning">M</div>
        </div>
        <div class="admin-stat-change"><?= (int) $overview['active_subscriptions'] ?> assinaturas ativas</div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-header">
            <div>
                <div class="admin-stat-value"><?= $money($overview['operating_balance']) ?></div>
                <div class="admin-stat-label">Saldo operacional</div>
            </div>
            <div class="admin-stat-icon danger">=</div>
        </div>
        <div class="admin-stat-change">Despesas pagas + repasses concluídos</div>
    </div>
</div>

<div class="dashboard-grid mt-4">
    <div class="admin-table-container">
        <div class="admin-table-header">
            <h3>Modelo de negócio ativo</h3>
        </div>
        <div style="padding: 1rem;">
            <p><strong>Cursos gratuitos:</strong> certificado ao concluir: <?= $rules['free_certificate_on_completion'] ? 'sim' : 'não' ?>.</p>
            <p><strong>Cursos pagos avulsos:</strong> certificado exige conclusão e pagamento: <?= $rules['paid_certificate_requires_payment'] ? 'sim' : 'não' ?>.</p>
            <p><strong>Cursos de longa duração:</strong> a partir de <?= (int) $rules['long_course_hours'] ?> horas.</p>
            <p><strong>Repasse padrão cursos pagos:</strong> <?= number_format((float) $rules['paid_course_rate'], 2, ',', '.') ?>% para instrutor.</p>
            <p><strong>Repasse cursos longos:</strong> <?= number_format((float) $rules['long_course_rate'], 2, ',', '.') ?>% para instrutor.</p>
            <p><strong>Pool de assinaturas:</strong> <?= number_format((float) $rules['subscription_pool_rate'], 2, ',', '.') ?>% da receita recorrente reservado para instrutores.</p>
            <p><strong>Ciclo de pagamento:</strong> <?= escape((string) $rules['payout_cycle']) ?> com retenção de <?= (int) $rules['payout_hold_days'] ?> dias.</p>
        </div>
    </div>

    <div class="admin-table-container">
        <div class="admin-table-header">
            <h3>Resumo financeiro</h3>
        </div>
        <table class="admin-table">
            <tbody>
                <tr><td>Reembolsos</td><td><?= $money($overview['refunded_total']) ?></td></tr>
                <tr><td>Despesas planejadas</td><td><?= $money($overview['planned_expenses']) ?></td></tr>
                <tr><td>Despesas aprovadas</td><td><?= $money($overview['approved_expenses']) ?></td></tr>
                <tr><td>Despesas pagas</td><td><?= $money($overview['paid_expenses']) ?></td></tr>
                <tr><td>Repasses pendentes</td><td><?= $money($overview['pending_payouts']) ?></td></tr>
                <tr><td>Repasses concluídos</td><td><?= $money($overview['paid_payouts']) ?></td></tr>
                <tr><td>ARR de assinaturas</td><td><?= $money($overview['subscription_arr']) ?></td></tr>
                <tr><td>Novas assinaturas no período</td><td><?= (int) $overview['new_subscriptions'] ?></td></tr>
                <tr><td>Matrículas em cursos grátis</td><td><?= (int) $overview['free_enrollments'] ?></td></tr>
                <tr><td>Conclusões em cursos grátis</td><td><?= (int) $overview['free_completions'] ?></td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="admin-table-container mt-4">
    <div class="admin-table-header">
        <h3>Receita por linha de produto</h3>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Linha</th>
                <th>Métrica 1</th>
                <th>Métrica 2</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($breakdown as $row): ?>
            <tr>
                <td><?= escape($row['line']) ?></td>
                <td><?= escape($row['metric_a']) ?></td>
                <td><?= escape($row['metric_b']) ?></td>
                <td><?= $money($row['value']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="admin-table-container mt-4">
    <div class="admin-table-header">
        <h3>Preview de repasse para instrutores</h3>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Instrutor</th>
                <th>Vendas</th>
                <th>Taxa média</th>
                <th>Bruto</th>
                <th>Repasse</th>
                <th>Parcela da plataforma</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($payoutPreview): ?>
                <?php foreach ($payoutPreview as $item): ?>
                <tr>
                    <td><?= escape($item['instructor_name']) ?></td>
                    <td><?= (int) $item['sales_count'] ?></td>
                    <td><?= number_format((float) $item['instructor_rate_avg'], 2, ',', '.') ?>%</td>
                    <td><?= $money($item['gross_sales']) ?></td>
                    <td><?= $money($item['instructor_share']) ?></td>
                    <td><?= $money($item['platform_share']) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Ainda não há pagamentos concluídos no período filtrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <div style="padding: 1rem; color: var(--gray-400);">
        O preview usa as regras padrão de comissão por tipo de curso. A distribuição fina do pool de assinaturas
        ainda depende de métricas de consumo por instrutor.
    </div>
</div>

<div class="dashboard-grid mt-4">
    <div class="admin-table-container">
        <div class="admin-table-header">
            <h3>Lançar despesa</h3>
        </div>
        <div style="padding: 1rem;">
            <?php if ($expenseTableReady): ?>
            <form method="POST">
                <input type="hidden" name="action" value="add_expense">
                <div class="grid-cols-2 gap-2">
                    <label>Título
                        <input type="text" name="title" class="form-control" required>
                    </label>
                    <label>Categoria
                        <input type="text" name="category" class="form-control" list="expense-categories" required>
                    </label>
                    <label>Valor
                        <input type="number" step="0.01" min="0" name="amount" class="form-control" required>
                    </label>
                    <label>Data da despesa
                        <input type="date" name="expense_date" class="form-control" value="<?= escape($to) ?>" required>
                    </label>
                    <label>Status
                        <select name="status" class="form-control">
                            <option value="planned">Planejada</option>
                            <option value="approved">Aprovada</option>
                            <option value="paid">Paga</option>
                            <option value="cancelled">Cancelada</option>
                        </select>
                    </label>
                    <label>Fornecedor
                        <input type="text" name="vendor_name" class="form-control">
                    </label>
                </div>
                <label class="mt-3">Observações
                    <textarea name="notes" class="form-control" rows="3"></textarea>
                </label>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Registrar despesa</button>
                </div>
            </form>
            <datalist id="expense-categories">
                <?php foreach ($rules['expense_categories'] as $category): ?>
                <option value="<?= escape((string) $category) ?>">
                <?php endforeach; ?>
            </datalist>
            <?php else: ?>
            <p>Instale primeiro o upgrade financeiro para habilitar o lançamento de despesas.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-table-container">
        <div class="admin-table-header">
            <h3>Despesas por categoria</h3>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Categoria</th>
                    <th>Itens</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($expenseCategories): ?>
                    <?php foreach ($expenseCategories as $item): ?>
                    <tr>
                        <td><?= escape($item['category']) ?></td>
                        <td><?= (int) $item['total_items'] ?></td>
                        <td><?= $money($item['total_amount']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">Nenhuma despesa encontrada no período.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="admin-table-container mt-4">
    <div class="admin-table-header">
        <h3>Últimas despesas</h3>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Data</th>
                <th>Título</th>
                <th>Categoria</th>
                <th>Status</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($recentExpenses): ?>
                <?php foreach ($recentExpenses as $expense): ?>
                <tr>
                    <td><?= formatDate($expense['expense_date']) ?></td>
                    <td><?= escape($expense['title']) ?></td>
                    <td><?= escape($expense['category']) ?></td>
                    <td><?= escape(ucfirst($expense['status'])) ?></td>
                    <td><?= $money($expense['amount']) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Nenhuma despesa registrada.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>

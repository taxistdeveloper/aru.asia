<?php
ob_start();
$payouts = $payouts ?? [];
?>

<div class="container mt-4 mb-5">
    <?php include __DIR__ . '/_nav.php'; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Мои выплаты</h4>
        <a href="<?= BASE_URL ?>profile/earnings/withdraw" class="btn btn-sm btn-success">
            <i class="bi bi-plus-lg"></i> Новая заявка
        </a>
    </div>

    <?php if (empty($payouts)): ?>
        <div class="alert alert-light border">Заявок на выплату пока нет.</div>
    <?php else: ?>
        <div class="table-responsive card border-0 shadow-sm">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Дата</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                        <th>Реквизиты</th>
                        <th>Комментарий</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payouts as $p): ?>
                        <tr>
                            <td><small><?= Helper::escape(date('d.m.Y H:i', strtotime($p['created_at']))) ?></small></td>
                            <td class="fw-semibold"><?= Helper::escape(Partner::formatMoney((float)$p['amount'], $p['currency'] ?? 'KZT')) ?></td>
                            <td>
                                <span class="badge <?= Partner::statusBadgeClass($p['status']) ?>">
                                    <?= Helper::escape(Partner::statusLabel($p['status'])) ?>
                                </span>
                            </td>
                            <td><small><?= nl2br(Helper::escape($p['payment_details'])) ?></small></td>
                            <td><small class="text-muted"><?= Helper::escape($p['admin_note'] ?? '—') ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$title = 'Мои выплаты';
include __DIR__ . '/../layout.php';
?>

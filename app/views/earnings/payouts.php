<?php
ob_start();
$payouts = $payouts ?? [];
$earnAction = [
    'href' => BASE_URL . 'profile/earnings/withdraw',
    'label' => 'Вывести',
    'icon' => 'bi-bank',
    'class' => 'btn-success',
];
?>

<div class="container mt-4 mb-5">
    <?php include __DIR__ . '/_nav.php'; ?>

    <?php if (empty($payouts)): ?>
        <div class="earn-empty">Заявок на выплату пока нет.</div>
    <?php else: ?>
        <div class="table-responsive earn-table">
            <table class="table table-hover align-middle mb-0 bg-white">
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

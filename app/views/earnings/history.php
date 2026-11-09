<?php
ob_start();
$earnings = $earnings ?? [];
?>

<div class="container mt-4 mb-5">
    <?php include __DIR__ . '/_nav.php'; ?>

    <?php if (empty($earnings)): ?>
        <div class="earn-empty">Начислений пока нет.</div>
    <?php else: ?>
        <div class="table-responsive earn-table">
            <table class="table table-hover align-middle mb-0 bg-white">
                <thead class="table-light">
                    <tr>
                        <th>Дата</th>
                        <th>Сумма</th>
                        <th>Компания</th>
                        <th>Описание</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($earnings as $e): ?>
                        <tr>
                            <td><small><?= Helper::escape(date('d.m.Y H:i', strtotime($e['created_at']))) ?></small></td>
                            <td class="fw-semibold text-success">
                                +<?= Helper::escape(Partner::formatMoney((float)$e['amount'], $e['currency'] ?? 'KZT')) ?>
                            </td>
                            <td><?= Helper::escape($e['company_name'] ?? '—') ?></td>
                            <td><?= Helper::escape($e['description'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$title = 'История начислений';
include __DIR__ . '/../layout.php';
?>

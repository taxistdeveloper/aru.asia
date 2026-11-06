<?php
/**
 * АДМИНКА — ПАРТНЁРЫ
 */
ob_start();
$partners = $partners ?? [];
$pendingPayouts = $pendingPayouts ?? [];
?>

<style>
@media (max-width: 767.98px) {
    .admin-partners-table-wrapper { display: none; }
}
@media (min-width: 768px) {
    .admin-partners-mobile-list { display: none; }
}
</style>

<div class="mt-4">
    <h2 class="mb-4">Партнёры</h2>

    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= Helper::escape($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= Helper::escape($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <?php if (!empty($pendingPayouts)): ?>
        <div class="card mb-4 border-warning">
            <div class="card-header bg-warning-subtle">
                <strong>Заявки на вывод (<?= count($pendingPayouts) ?>)</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Партнёр</th>
                                <th>Сумма</th>
                                <th>Реквизиты</th>
                                <th>Дата</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingPayouts as $p): ?>
                                <tr>
                                    <td>
                                        <?= Helper::escape($p['partner_name'] ?: $p['partner_email']) ?>
                                        <div class="small text-muted">#<?= (int)$p['partner_user_id'] ?></div>
                                    </td>
                                    <td class="fw-semibold"><?= Helper::escape(Partner::formatMoney((float)$p['amount'])) ?></td>
                                    <td><small><?= nl2br(Helper::escape($p['payment_details'])) ?></small></td>
                                    <td><small><?= Helper::escape(date('d.m.Y H:i', strtotime($p['created_at']))) ?></small></td>
                                    <td class="text-nowrap">
                                        <a href="<?= BASE_URL ?>admin/partners/view?id=<?= (int)$p['partner_user_id'] ?>"
                                           class="btn btn-sm btn-outline-primary">Кабинет</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <?php if (empty($partners)): ?>
                <div class="text-muted">Партнёров пока нет — появятся после первой заявки на бизнес или начисления.</div>
            <?php else: ?>
                <div class="table-responsive admin-partners-table-wrapper">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Партнёр</th>
                                <th>Компании</th>
                                <th>Заработано</th>
                                <th>Выплачено</th>
                                <th>Осталось</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($partners as $p): ?>
                                <tr>
                                    <td><?= (int)$p['id'] ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= Helper::escape($p['full_name'] ?: '—') ?></div>
                                        <small class="text-muted"><?= Helper::escape($p['email']) ?></small>
                                    </td>
                                    <td><span class="badge bg-secondary"><?= (int)$p['companies_count'] ?></span></td>
                                    <td><?= Helper::escape(Partner::formatMoney((float)$p['earned'])) ?></td>
                                    <td><?= Helper::escape(Partner::formatMoney((float)$p['paid'])) ?></td>
                                    <td class="fw-semibold"><?= Helper::escape(Partner::formatMoney((float)$p['remaining'])) ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>admin/partners/view?id=<?= (int)$p['id'] ?>"
                                           class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> Кабинет
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="admin-partners-mobile-list">
                    <?php foreach ($partners as $p): ?>
                        <div class="border rounded p-3 mb-2">
                            <div class="fw-semibold"><?= Helper::escape($p['full_name'] ?: $p['email']) ?></div>
                            <div class="small text-muted mb-2"><?= Helper::escape($p['email']) ?></div>
                            <div class="small mb-2">
                                Компании: <strong><?= (int)$p['companies_count'] ?></strong> ·
                                Заработано: <strong><?= Helper::escape(Partner::formatMoney((float)$p['earned'])) ?></strong> ·
                                Осталось: <strong><?= Helper::escape(Partner::formatMoney((float)$p['remaining'])) ?></strong>
                            </div>
                            <a href="<?= BASE_URL ?>admin/partners/view?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-primary">Кабинет</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Партнёры';
include __DIR__ . '/../admin_layout.php';
?>

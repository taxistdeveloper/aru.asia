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
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h2 class="mb-0"><i class="bi bi-person-badge"></i> ПАРТНЁРЫ</h2>
        <?php if (!empty($houseUserId)): ?>
            <a href="<?= BASE_URL ?>admin/partners/view?id=<?= (int)$houseUserId ?>" class="btn btn-success">
                <i class="bi bi-wallet2"></i> Рабочий кошелёк
                <?php if (!empty($houseBalance)): ?>
                    · <?= Helper::escape(Partner::formatMoney((float)$houseBalance['available'])) ?>
                <?php endif; ?>
            </a>
        <?php endif; ?>
    </div>
   
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

    <?php if (!empty($reclaimedNow)): ?>
        <div class="alert alert-info">
            На рабочий кошелёк передано неактивных аккаунтов: <strong><?= (int)$reclaimedNow ?></strong>
        </div>
    <?php endif; ?>

    <?php
    $totals = $finance['totals'] ?? [];
    $financeAds = $finance['ads'] ?? [];
    $financeEarnings = $finance['earnings'] ?? [];
    $financePayouts = $finance['payouts'] ?? [];
    ?>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="border rounded p-3 h-100">
                <div class="small text-muted">Начислено партнёрам</div>
                <div class="fs-5 fw-semibold"><?= Helper::escape(Partner::formatMoney((float)($totals['earned'] ?? 0))) ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="border rounded p-3 h-100">
                <div class="small text-muted">Выплачено</div>
                <div class="fs-5 fw-semibold"><?= Helper::escape(Partner::formatMoney((float)($totals['paid'] ?? 0))) ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="border rounded p-3 h-100">
                <div class="small text-muted">Ждёт выплаты</div>
                <div class="fs-5 fw-semibold"><?= Helper::escape(Partner::formatMoney((float)($totals['remaining'] ?? 0))) ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="border rounded p-3 h-100">
                <div class="small text-muted">Реклама ждёт 50%</div>
                <div class="fs-5 fw-semibold"><?= (int)($totals['waiting_ads'] ?? 0) ?></div>
            </div>
        </div>
    </div>

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
                                <th>Кто с ссылки</th>
                                <th>Заработано</th>
                                <th>Выплачено</th>
                                <th>В кошельке</th>
                                <th>Был на сайте</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($partners as $p): ?>
                                <tr>
                                    <td><?= (int)$p['id'] ?></td>
                                    <td>
                                        <div class="fw-semibold">
                                            <?= Helper::escape($p['full_name'] ?: '—') ?>
                                            <?php if (!empty($p['is_house_wallet'])): ?>
                                                <span class="badge bg-success">Кошелёк</span>
                                            <?php elseif (!empty($p['earnings_redirect_user_id'])): ?>
                                                <span class="badge bg-secondary">Ссылка передана</span>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted"><?= Helper::escape($p['email']) ?></small>
                                    </td>
                                    <td><?= (int)($p['referrals_count'] ?? 0) ?></td>
                                    <td><?= Helper::escape(Partner::formatMoney((float)$p['earned'])) ?></td>
                                    <td><?= Helper::escape(Partner::formatMoney((float)$p['paid'])) ?></td>
                                    <td class="fw-semibold"><?= Helper::escape(Partner::formatMoney((float)($p['available'] ?? $p['remaining']))) ?></td>
                                    <td>
                                        <small>
                                            <?php if (!empty($p['last_activity_at'])): ?>
                                                <?= Helper::escape(date('d.m.Y', strtotime($p['last_activity_at']))) ?>
                                            <?php else: ?>
                                                нет входов
                                            <?php endif; ?>
                                        </small>
                                    </td>
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
                                С ссылки: <strong><?= (int)($p['referrals_count'] ?? 0) ?></strong> ·
                                В кошельке: <strong><?= Helper::escape(Partner::formatMoney((float)($p['available'] ?? $p['remaining']))) ?></strong>
                                <?php if (!empty($p['is_house_wallet'])): ?>
                                    · <span class="badge bg-success">Кошелёк</span>
                                <?php elseif (!empty($p['earnings_redirect_user_id'])): ?>
                                    · <span class="badge bg-secondary">Ссылка передана</span>
                                <?php endif; ?>
                            </div>
                            <a href="<?= BASE_URL ?>admin/partners/view?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-primary">Кабинет</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><strong>Реклама и 50%</strong></div>
        <div class="card-body p-0">
            <?php if (empty($financeAds)): ?>
                <div class="p-3 text-muted">Заявок на рекламу пока нет.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Кто даёт рекламу</th>
                                <th>Оплата</th>
                                <th>Кому 50%</th>
                                <th>Начисление</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($financeAds as $ad): ?>
                                <?php
                                $state = $ad['commission_state'] ?? 'unpaid';
                                $receiver = $ad['credited_partner_name'] ?: $ad['credited_partner_email']
                                    ?: $ad['link_partner_name'] ?: $ad['link_partner_email'] ?: '';
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= Helper::escape($ad['advertiser_name'] ?: '—') ?></div>
                                        <small class="text-muted"><?= Helper::escape($ad['advertiser_email']) ?></small>
                                    </td>
                                    <td><?= Helper::escape(Partner::formatMoney((float)$ad['paid_amount'])) ?></td>
                                    <td>
                                        <?php if ($receiver !== ''): ?>
                                            <?= Helper::escape($receiver) ?>
                                        <?php else: ?>
                                            <span class="text-muted">нет партнёра</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($state === 'credited'): ?>
                                            <span class="badge bg-success">Начислено <?= Helper::escape(Partner::formatMoney((float)$ad['commission'])) ?></span>
                                        <?php elseif ($state === 'waiting'): ?>
                                            <form method="POST" action="<?= BASE_URL ?>admin/partners/credit-ad" class="d-inline">
                                                <input type="hidden" name="ad_id" value="<?= (int)$ad['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-warning">Начислить 50%</button>
                                            </form>
                                        <?php elseif ($state === 'no_partner'): ?>
                                            <span class="badge bg-secondary">Нет партнёра</span>
                                        <?php else: ?>
                                            <span class="text-muted">Оплата не отмечена</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><strong>Движение денег</strong></div>
        <div class="card-body p-0">
            <?php if (empty($financeEarnings) && empty($financePayouts)): ?>
                <div class="p-3 text-muted">Проводок пока нет.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Дата</th>
                                <th>Кому</th>
                                <th>Операция</th>
                                <th>Сумма</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($financeEarnings as $row): ?>
                                <tr>
                                    <td><small><?= Helper::escape(date('d.m.Y H:i', strtotime($row['created_at']))) ?></small></td>
                                    <td>
                                        <?= Helper::escape($row['full_name'] ?: $row['email']) ?>
                                        <?php if (!empty($row['is_house_wallet'])): ?>
                                            <span class="badge bg-success">Кошелёк</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><small><?= Helper::escape($row['description'] ?: 'Начисление') ?></small></td>
                                    <td class="<?= (float)$row['amount'] < 0 ? 'text-danger' : 'text-success' ?>">
                                        <?= Helper::escape(Partner::formatMoney((float)$row['amount'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php foreach ($financePayouts as $row): ?>
                                <tr>
                                    <td><small><?= Helper::escape(date('d.m.Y H:i', strtotime($row['created_at']))) ?></small></td>
                                    <td><?= Helper::escape($row['full_name'] ?: $row['email']) ?></td>
                                    <td><small>Выплата · <?= Helper::escape(Partner::statusLabel($row['status'])) ?></small></td>
                                    <td class="text-danger">−<?= Helper::escape(Partner::formatMoney((float)$row['amount'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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

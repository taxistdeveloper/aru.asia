<?php

/**
 * АДМИН-ПАНЕЛЬ — ОПЛАТЫ И ЧЕКИ
 * Ожидающие оплаты + архив всех чеков, автоматически разложенный по папкам «Год → Месяц».
 */

$fmt = function ($v) {
    return number_format((float)$v, 0, ',', ' ');
};
$objectLink = function (array $p) {
    if ($p['kind'] === 'ad' && !empty($p['ad_id'])) {
        return 'Реклама №' . (int)$p['ad_id'];
    }
    if ($p['kind'] === 'event' && !empty($p['event_id'])) {
        return 'Мероприятие №' . (int)$p['event_id'];
    }
    return '—';
};

ob_start();
?>

<style>
    .rc-folders { display: flex; flex-direction: column; gap: 6px; }
    .rc-year > summary {
        cursor: pointer; font-weight: 700; padding: 8px 10px; border-radius: 8px;
        background: #f7fafc; list-style: none;
    }
    .rc-year > summary::-webkit-details-marker { display: none; }
    .rc-months { padding: 6px 0 6px 18px; display: flex; flex-direction: column; gap: 2px; }
    .rc-month {
        display: flex; justify-content: space-between; align-items: center;
        padding: 6px 10px; border-radius: 8px; color: #2d3748; text-decoration: none;
    }
    .rc-month:hover { background: #edf2f7; color: #2d3748; }
    .rc-month.active { background: #667eea; color: #fff; }
    .rc-month.empty { color: #a0aec0; pointer-events: none; }
    .rc-month .badge { font-weight: 600; }
</style>

<div class="container-fluid">
    <h2 class="mb-4"><i class="bi bi-receipt"></i> Оплаты и чеки</h2>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= Helper::escape($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= Helper::escape($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Ожидают подтверждения оплаты -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-hourglass-split"></i> Ожидают подтверждения оплаты</strong>
            <span class="badge bg-warning text-dark"><?= count($pending) ?></span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($pending)): ?>
                <p class="text-muted m-3">Нет заявок, ожидающих оплаты.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Дата согласия</th>
                                <th>Плательщик</th>
                                <th>Услуга</th>
                                <th>Заказ</th>
                                <th class="text-end">Сумма</th>
                                <th class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending as $p): ?>
                                <tr>
                                    <td><?= (int)$p['id'] ?></td>
                                    <td><?= date('d.m.Y H:i', strtotime($p['agreed_at'])) ?></td>
                                    <td>
                                        <?= Helper::escape($p['payer_name'] ?: '—') ?><br>
                                        <small class="text-muted"><?= Helper::escape($p['payer_email']) ?></small>
                                    </td>
                                    <td><?= Helper::escape($p['description']) ?></td>
                                    <td><?= Helper::escape($objectLink($p)) ?></td>
                                    <td class="text-end fw-bold"><?= $fmt($p['amount']) ?> ₸</td>
                                    <td class="text-end text-nowrap">
                                        <form method="POST" action="<?= BASE_URL ?>admin/payments/confirm" class="d-inline"
                                              onsubmit="return confirm('Подтвердить получение <?= $fmt($p['amount']) ?> ₸? Чек будет отправлен пользователю на email и в сообщения.');">
                                            <input type="hidden" name="payment_id" value="<?= (int)$p['id'] ?>">
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="bi bi-check2-circle"></i> Оплата получена
                                            </button>
                                        </form>
                                        <form method="POST" action="<?= BASE_URL ?>admin/payments/cancel" class="d-inline"
                                              onsubmit="return confirm('Отменить заявку на оплату?');">
                                            <input type="hidden" name="payment_id" value="<?= (int)$p['id'] ?>">
                                            <button type="submit" class="btn btn-outline-secondary btn-sm">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Архив чеков: Год → Месяц -->
    <div class="row">
        <div class="col-lg-3 mb-4">
            <div class="card">
                <div class="card-header"><strong><i class="bi bi-archive"></i> Архив чеков</strong></div>
                <div class="card-body">
                    <?php if (empty($tree)): ?>
                        <p class="text-muted mb-0">Чеков пока нет. Папки по годам и месяцам появятся автоматически после первой подтверждённой оплаты.</p>
                    <?php else: ?>
                        <div class="rc-folders">
                            <?php foreach ($tree as $y => $months): ?>
                                <?php $yearCount = array_sum(array_column($months, 'count')); ?>
                                <details class="rc-year" <?= (int)$y === (int)$year ? 'open' : '' ?>>
                                    <summary>
                                        <i class="bi bi-folder"></i> <?= (int)$y ?> год
                                        <span class="badge bg-secondary float-end"><?= $yearCount ?></span>
                                    </summary>
                                    <div class="rc-months">
                                        <?php foreach (PaymentService::MONTHS as $m => $mName): ?>
                                            <?php $info = $months[$m] ?? null; ?>
                                            <a class="rc-month <?= !$info ? 'empty' : '' ?> <?= ((int)$y === (int)$year && $m === (int)$month) ? 'active' : '' ?>"
                                               href="<?= BASE_URL ?>admin/payments?year=<?= (int)$y ?>&month=<?= $m ?>">
                                                <span><i class="bi bi-folder2"></i> <?= $mName ?></span>
                                                <?php if ($info): ?><span class="badge bg-light text-dark"><?= $info['count'] ?></span><?php endif; ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </details>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-9 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>
                        <i class="bi bi-folder2-open"></i>
                        <?= ($year && $month) ? (int)$year . ' / ' . PaymentService::MONTHS[(int)$month] : 'Чеки' ?>
                    </strong>
                    <?php if (!empty($receipts)): ?>
                        <span class="text-muted">
                            <?= count($receipts) ?> чек(ов) на <?= $fmt(array_sum(array_column($receipts, 'amount'))) ?> ₸
                        </span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($receipts)): ?>
                        <p class="text-muted m-3">В этой папке нет чеков.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>№ чека</th>
                                        <th>Дата оплаты</th>
                                        <th>Плательщик</th>
                                        <th>Услуга</th>
                                        <th class="text-end">Сумма</th>
                                        <th>Отправлено</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($receipts as $r): ?>
                                        <tr>
                                            <td class="fw-bold text-nowrap"><?= Helper::escape($r['receipt_number']) ?></td>
                                            <td class="text-nowrap"><?= date('d.m.Y H:i', strtotime($r['paid_at'])) ?></td>
                                            <td>
                                                <?= Helper::escape($r['payer_name'] ?: '—') ?><br>
                                                <small class="text-muted"><?= Helper::escape($r['payer_email']) ?></small>
                                            </td>
                                            <td><?= Helper::escape($r['description']) ?><br><small class="text-muted"><?= Helper::escape($objectLink($r)) ?></small></td>
                                            <td class="text-end fw-bold text-nowrap"><?= $fmt($r['amount']) ?> ₸</td>
                                            <td class="text-nowrap">
                                                <span class="badge <?= $r['receipt_email_sent_at'] ? 'bg-success' : 'bg-danger' ?>" title="Email">
                                                    <i class="bi bi-envelope"></i>
                                                </span>
                                                <span class="badge <?= $r['receipt_message_id'] ? 'bg-success' : 'bg-secondary' ?>" title="Сообщение на сайте">
                                                    <i class="bi bi-chat-dots"></i>
                                                </span>
                                            </td>
                                            <td class="text-end text-nowrap">
                                                <a class="btn btn-sm btn-outline-primary" target="_blank"
                                                   href="<?= BASE_URL ?>admin/receipts/view?id=<?= (int)$r['id'] ?>">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a class="btn btn-sm btn-outline-secondary"
                                                   href="<?= BASE_URL ?>admin/receipts/view?id=<?= (int)$r['id'] ?>&download=1">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Оплаты и чеки - Админ-панель';
include __DIR__ . '/../admin_layout.php';
?>

<?php
/**
 * АДМИНКА — КАБИНЕТ ПАРТНЁРА
 */
ob_start();
$cabinet = $cabinet ?? null;
$user = $cabinet['user'] ?? null;
$balance = $cabinet['balance'] ?? ['earned' => 0, 'paid' => 0, 'remaining' => 0, 'available' => 0];
$companies = $cabinet['companies'] ?? [];
$earnings = $cabinet['earnings'] ?? [];
$payouts = $cabinet['payouts'] ?? [];
?>

<div class="mt-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <a href="<?= BASE_URL ?>admin/partners" class="text-decoration-none text-muted small">
                <i class="bi bi-arrow-left"></i> К списку партнёров
            </a>
            <h2 class="mb-0 mt-1">Кабинет партнёра</h2>
        </div>
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

    <?php if (!$user): ?>
        <div class="alert alert-danger">Партнёр не найден</div>
    <?php else: ?>
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Партнёр</div>
                        <div class="fs-5 fw-semibold"><?= Helper::escape($user['full_name'] ?: '—') ?></div>
                        <div><?= Helper::escape($user['email']) ?></div>
                        <div class="small text-muted">ID: <?= (int)$user['id'] ?> · <?= Helper::escape($user['city'] ?? '') ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <div class="small text-muted">Компании</div>
                                    <strong><?= (int)($cabinet['companies_count'] ?? 0) ?></strong>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <div class="small text-muted">Заработано</div>
                                    <strong><?= Helper::escape(Partner::formatMoney((float)$balance['earned'])) ?></strong>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <div class="small text-muted">Осталось</div>
                                    <strong><?= Helper::escape(Partner::formatMoney((float)$balance['remaining'])) ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="small text-muted mt-2">
                            Выплачено: <?= Helper::escape(Partner::formatMoney((float)$balance['paid'])) ?> ·
                            Доступно: <?= Helper::escape(Partner::formatMoney((float)$balance['available'])) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Начислить -->
        <div class="card mb-4">
            <div class="card-header"><strong>Начислить вознаграждение</strong></div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>admin/partners/add-earning" class="row g-3 align-items-end">
                    <input type="hidden" name="partner_user_id" value="<?= (int)$user['id'] ?>">
                    <div class="col-md-3">
                        <label class="form-label">Сумма (KZT)</label>
                        <input type="number" name="amount" class="form-control" min="1" step="1" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Компания</label>
                        <select name="company_id" class="form-select">
                            <option value="">— без привязки —</option>
                            <?php foreach ($companies as $c): ?>
                                <option value="<?= (int)$c['id'] ?>"><?= Helper::escape($c['company_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Описание</label>
                        <input type="text" name="description" class="form-control" maxlength="500"
                               placeholder="За что начисление">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100">Начислить</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Компании -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Компании партнёра</strong>
                <span class="badge bg-secondary">перепривязка запрещена</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($companies)): ?>
                    <div class="p-3 text-muted">Нет компаний</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Компания</th>
                                    <th>Контакты</th>
                                    <th>Статус</th>
                                    <th>Дата</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($companies as $c): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?= Helper::escape($c['company_name']) ?></div>
                                            <?php if (!empty($c['description'])): ?>
                                                <small class="text-muted"><?= Helper::escape($c['description']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small>
                                                <?= Helper::escape($c['contact_email'] ?? '') ?><br>
                                                <?= Helper::escape($c['contact_phone'] ?? '') ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge <?= Partner::statusBadgeClass($c['status']) ?>">
                                                <?= Helper::escape(Partner::statusLabel($c['status'])) ?>
                                            </span>
                                        </td>
                                        <td><small><?= Helper::escape(date('d.m.Y', strtotime($c['created_at']))) ?></small></td>
                                        <td>
                                            <form method="POST" action="<?= BASE_URL ?>admin/partners/update-company-status"
                                                  class="d-flex flex-wrap gap-1">
                                                <input type="hidden" name="company_id" value="<?= (int)$c['id'] ?>">
                                                <input type="hidden" name="partner_user_id" value="<?= (int)$user['id'] ?>">
                                                <select name="status" class="form-select form-select-sm" style="width:auto">
                                                    <?php foreach (PartnerCompany::STATUSES as $st): ?>
                                                        <option value="<?= $st ?>" <?= $c['status'] === $st ? 'selected' : '' ?>>
                                                            <?= Helper::escape(Partner::statusLabel($st)) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-outline-primary">OK</button>
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

        <!-- Начисления -->
        <div class="card mb-4">
            <div class="card-header"><strong>История начислений</strong></div>
            <div class="card-body p-0">
                <?php if (empty($earnings)): ?>
                    <div class="p-3 text-muted">Нет начислений</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
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
                                        <td class="text-success fw-semibold">+<?= Helper::escape(Partner::formatMoney((float)$e['amount'])) ?></td>
                                        <td><?= Helper::escape($e['company_name'] ?? '—') ?></td>
                                        <td><?= Helper::escape($e['description'] ?? '—') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Выплаты -->
        <div class="card mb-4">
            <div class="card-header"><strong>Выплаты</strong></div>
            <div class="card-body p-0">
                <?php if (empty($payouts)): ?>
                    <div class="p-3 text-muted">Нет заявок</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Дата</th>
                                    <th>Сумма</th>
                                    <th>Статус</th>
                                    <th>Реквизиты</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payouts as $p): ?>
                                    <tr>
                                        <td><small><?= Helper::escape(date('d.m.Y H:i', strtotime($p['created_at']))) ?></small></td>
                                        <td class="fw-semibold"><?= Helper::escape(Partner::formatMoney((float)$p['amount'])) ?></td>
                                        <td>
                                            <span class="badge <?= Partner::statusBadgeClass($p['status']) ?>">
                                                <?= Helper::escape(Partner::statusLabel($p['status'])) ?>
                                            </span>
                                            <?php if (!empty($p['admin_note'])): ?>
                                                <div class="small text-muted"><?= Helper::escape($p['admin_note']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td><small><?= nl2br(Helper::escape($p['payment_details'])) ?></small></td>
                                        <td>
                                            <?php if ($p['status'] === 'pending'): ?>
                                                <form method="POST" action="<?= BASE_URL ?>admin/partners/process-payout" class="d-flex flex-column gap-1">
                                                    <input type="hidden" name="payout_id" value="<?= (int)$p['id'] ?>">
                                                    <input type="hidden" name="partner_user_id" value="<?= (int)$user['id'] ?>">
                                                    <input type="text" name="admin_note" class="form-control form-control-sm" placeholder="Комментарий">
                                                    <div class="btn-group">
                                                        <button type="submit" name="status" value="paid" class="btn btn-sm btn-success">Выплатить</button>
                                                        <button type="submit" name="status" value="rejected" class="btn btn-sm btn-outline-danger">Отклонить</button>
                                                    </div>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted small">—</span>
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
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$title = 'Кабинет партнёра';
include __DIR__ . '/../admin_layout.php';
?>

<?php
ob_start();
$companies = $companies ?? [];
?>

<div class="container mt-4 mb-5">
    <?php include __DIR__ . '/_nav.php'; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Мои компании</h4>
        <a href="<?= BASE_URL ?>profile/earnings/invite" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg"></i> Пригласить
        </a>
    </div>

    <?php if (empty($companies)): ?>
        <div class="alert alert-light border">Пока нет компаний. Пригласите первый бизнес.</div>
    <?php else: ?>
        <div class="table-responsive card border-0 shadow-sm">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Компания</th>
                        <th>Контакты</th>
                        <th>Статус</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($companies as $c): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= Helper::escape($c['company_name']) ?></div>
                                <?php if (!empty($c['contact_name'])): ?>
                                    <small class="text-muted"><?= Helper::escape($c['contact_name']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($c['contact_email'])): ?>
                                    <div><small><?= Helper::escape($c['contact_email']) ?></small></div>
                                <?php endif; ?>
                                <?php if (!empty($c['contact_phone'])): ?>
                                    <div><small><?= Helper::escape($c['contact_phone']) ?></small></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= Partner::statusBadgeClass($c['status']) ?>">
                                    <?= Helper::escape(Partner::statusLabel($c['status'])) ?>
                                </span>
                            </td>
                            <td><small><?= Helper::escape(date('d.m.Y H:i', strtotime($c['created_at']))) ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$title = 'Мои компании';
include __DIR__ . '/../layout.php';
?>

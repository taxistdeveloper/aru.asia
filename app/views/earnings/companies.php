<?php
ob_start();
$companies = $companies ?? [];
$earnAction = [
    'href' => BASE_URL . 'profile/earnings/invite',
    'label' => 'Пригласить',
    'icon' => 'bi-plus-lg',
    'class' => 'btn-primary',
];
?>

<div class="container mt-4 mb-5">
    <?php include __DIR__ . '/_nav.php'; ?>

    <?php if (empty($companies)): ?>
        <div class="earn-empty">Пока нет партнёров.</div>
    <?php else: ?>
        <div class="table-responsive earn-table">
            <table class="table table-hover align-middle mb-0 bg-white">
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
$title = 'Партнёры';
include __DIR__ . '/../layout.php';
?>

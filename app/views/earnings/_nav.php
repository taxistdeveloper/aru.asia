<?php
/**
 * Навигация кабинета заработка
 */
$earnPath = $_SERVER['REQUEST_URI'] ?? '';
$items = [
    ['url' => 'profile/earnings', 'label' => 'Обзор', 'icon' => 'bi-grid', 'exact' => true],
    ['url' => 'profile/earnings/instructions', 'label' => 'Инструкции', 'icon' => 'bi-book'],
    ['url' => 'profile/earnings/invite', 'label' => 'Пригласить бизнес', 'icon' => 'bi-building-add'],
    ['url' => 'profile/earnings/companies', 'label' => 'Мои компании', 'icon' => 'bi-buildings'],
    ['url' => 'profile/earnings/balance', 'label' => 'Мой баланс', 'icon' => 'bi-wallet2'],
    ['url' => 'profile/earnings/history', 'label' => 'История начислений', 'icon' => 'bi-clock-history'],
    ['url' => 'profile/earnings/payouts', 'label' => 'Мои выплаты', 'icon' => 'bi-cash-stack'],
    ['url' => 'profile/earnings/withdraw', 'label' => 'Вывести средства', 'icon' => 'bi-bank'],
];
?>
<style>
.earnings-nav {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1.25rem;
}
.earnings-nav .btn {
    border-radius: 999px;
    font-size: 0.875rem;
}
.earnings-balance-chip {
    background: linear-gradient(135deg, #0f766e 0%, #134e4a 100%);
    color: #fff;
    border-radius: 12px;
    padding: 0.85rem 1.1rem;
}
@media (max-width: 767px) {
    .earnings-nav .btn {
        flex: 1 1 calc(50% - 0.5rem);
        justify-content: center;
    }
}
</style>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
    <div>
        <a href="<?= BASE_URL ?>profile" class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left"></i> Личный кабинет
        </a>
        <h2 class="mt-1 mb-0">Мой заработок</h2>
    </div>
    <?php if (!empty($balance)): ?>
        <div class="earnings-balance-chip">
            <div class="small opacity-75">Доступно к выводу</div>
            <div class="fs-5 fw-semibold"><?= Helper::escape(Partner::formatMoney((float)$balance['available'], $balance['currency'] ?? 'KZT')) ?></div>
        </div>
    <?php endif; ?>
</div>

<nav class="earnings-nav">
    <?php foreach ($items as $item): ?>
        <?php
        $full = BASE_URL . $item['url'];
        $pathCheck = '/' . $item['url'];
        $active = !empty($item['exact'])
            ? (preg_match('#/profile/earnings/?(\?|$)#', $earnPath) === 1)
            : (strpos($earnPath, $pathCheck) !== false);
        ?>
        <a href="<?= $full ?>" class="btn btn-sm <?= $active ? 'btn-dark' : 'btn-outline-secondary' ?>">
            <i class="bi <?= $item['icon'] ?>"></i> <?= Helper::escape($item['label']) ?>
        </a>
    <?php endforeach; ?>
</nav>

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

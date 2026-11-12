<?php
ob_start();
$balance = $balance ?? ['available' => 0, 'earned' => 0, 'paid' => 0, 'reserved' => 0, 'remaining' => 0, 'currency' => 'KZT'];
$companiesCount = (int)($companiesCount ?? 0);
$isPartner = !empty($isPartner);
$partnerLink = $partnerLink ?? null;
$reserved = (float)($balance['reserved'] ?? 0);
?>

<div class="container mt-4 mb-5">
    <?php include __DIR__ . '/_nav.php'; ?>

    <?php if (!$isPartner): ?>
        <section class="earn-panel">
            <h2 class="earn-panel-title">Стать партнёром</h2>
            <p class="text-muted mb-3">
                Своя ссылка для предпринимателей. С оплаты их рекламы вам начисляется 50%.
            </p>
            <form method="POST" action="<?= BASE_URL ?>profile/earnings/become">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-handshake"></i> Получить ссылку
                </button>
            </form>
        </section>
    <?php else: ?>
        <section class="earn-panel">
            <h2 class="earn-panel-title">Партнёрская ссылка:</h2>
            <p class="text-muted small mb-2">Отправляйте её новым участникам — они будут закреплены за вами.</p>
            <div class="input-group">
                <input type="text" class="form-control" id="partnerLinkInput" readonly
                       value="<?= Helper::escape($partnerLink) ?>">
                <button type="button" class="btn btn-dark" id="copyPartnerLinkBtn">
                    <i class="bi bi-clipboard"></i> Копировать
                </button>
            </div>
        </section>
    <?php endif; ?>

    <section class="earn-stats" aria-label="Сводка">
        <a class="earn-stat earn-stat-main" href="<?= BASE_URL ?>profile/earnings/withdraw">
            <span>Доступно к выводу</span>
            <strong><?= Helper::escape(Partner::formatMoney((float)$balance['available'])) ?></strong>
        </a>
        <a class="earn-stat" href="<?= BASE_URL ?>profile/earnings/history">
            <span>Начислено</span>
            <strong><?= Helper::escape(Partner::formatMoney((float)$balance['earned'])) ?></strong>
        </a>
        <a class="earn-stat" href="<?= BASE_URL ?>profile/earnings/payouts">
            <span>Выплачено</span>
            <strong><?= Helper::escape(Partner::formatMoney((float)$balance['paid'])) ?></strong>
        </a>
        <a class="earn-stat" href="<?= BASE_URL ?>profile/earnings/companies">
            <span>Партнёры</span>
            <strong><?= $companiesCount ?></strong>
        </a>
    </section>
    <?php if ($reserved > 0): ?>
        <p class="earn-note">В заявках на выплату: <?= Helper::escape(Partner::formatMoney($reserved)) ?></p>
    <?php endif; ?>
</div>

<script>
document.getElementById('copyPartnerLinkBtn')?.addEventListener('click', function () {
    const input = document.getElementById('partnerLinkInput');
    if (!input) return;
    const button = this;
    const done = function () {
        button.innerHTML = '<i class="bi bi-check2"></i> Скопировано';
        setTimeout(function () {
            button.innerHTML = '<i class="bi bi-clipboard"></i> Копировать';
        }, 2000);
    };
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(input.value).then(done).catch(function () {
            input.select();
            document.execCommand('copy');
            done();
        });
        return;
    }
    input.select();
    document.execCommand('copy');
    done();
});
</script>

<?php
$content = ob_get_clean();
$title = 'Личный кабинет';
include __DIR__ . '/../layout.php';
?>

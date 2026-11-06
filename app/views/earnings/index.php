<?php
ob_start();
$balance = $balance ?? ['available' => 0, 'earned' => 0, 'paid' => 0, 'remaining' => 0, 'currency' => 'KZT'];
$companiesCount = (int)($companiesCount ?? 0);
$isPartner = !empty($isPartner);
$partnerLink = $partnerLink ?? null;
?>

<div class="container mt-4 mb-5">
    <?php include __DIR__ . '/_nav.php'; ?>

    <?php if (!$isPartner): ?>
        <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #ecfdf5 0%, #f0fdfa 100%);">
            <div class="card-body p-4">
                <h4 class="mb-2">Стать партнёром</h4>
                <p class="text-muted mb-3">
                    Получите ссылку, отправляйте предпринимателям и автоматически получайте
                    <strong>50&nbsp;%</strong> от каждой оплаты рекламы — навсегда.
                </p>
                <form method="POST" action="<?= BASE_URL ?>profile/earnings/become" class="d-inline">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-handshake"></i> Стать партнёром
                    </button>
                </form>
                <a href="<?= BASE_URL ?>profile/earnings/instructions" class="btn btn-outline-secondary btn-lg ms-2">
                    Инструкция
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h5 class="mb-2"><i class="bi bi-link-45deg"></i> Ваша партнёрская ссылка</h5>
                <p class="text-muted small mb-2">Отправляйте её предпринимателям. Компании закрепляются за вами навсегда.</p>
                <div class="input-group">
                    <input type="text" class="form-control" id="partnerLinkInput" readonly
                           value="<?= Helper::escape($partnerLink) ?>">
                    <button type="button" class="btn btn-dark" id="copyPartnerLinkBtn">
                        <i class="bi bi-clipboard"></i> Копировать
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Всего заработано</div>
                    <div class="fs-4 fw-semibold"><?= Helper::escape(Partner::formatMoney((float)$balance['earned'])) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Выплачено</div>
                    <div class="fs-4 fw-semibold"><?= Helper::escape(Partner::formatMoney((float)$balance['paid'])) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Мои компании</div>
                    <div class="fs-4 fw-semibold"><?= $companiesCount ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <h5 class="card-title">Быстрые действия</h5>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <a href="<?= BASE_URL ?>profile/earnings/invite" class="btn btn-primary">
                    <i class="bi bi-building-add"></i> Пригласить бизнес
                </a>
                <a href="<?= BASE_URL ?>profile/earnings/withdraw" class="btn btn-success">
                    <i class="bi bi-bank"></i> Вывести средства
                </a>
                <a href="<?= BASE_URL ?>profile/earnings/instructions" class="btn btn-outline-secondary">
                    <i class="bi bi-book"></i> Инструкция
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('copyPartnerLinkBtn')?.addEventListener('click', function () {
    const input = document.getElementById('partnerLinkInput');
    if (!input) return;
    navigator.clipboard.writeText(input.value).then(() => {
        this.innerHTML = '<i class="bi bi-check2"></i> Скопировано';
        setTimeout(() => { this.innerHTML = '<i class="bi bi-clipboard"></i> Копировать'; }, 2000);
    }).catch(() => {
        input.select();
        document.execCommand('copy');
    });
});
</script>

<?php
$content = ob_get_clean();
$title = 'Мой заработок';
include __DIR__ . '/../layout.php';
?>

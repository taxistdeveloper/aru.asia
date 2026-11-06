<?php
ob_start();
$balance = $balance ?? ['earned' => 0, 'paid' => 0, 'available' => 0, 'remaining' => 0, 'currency' => 'KZT'];
?>

<div class="container mt-4 mb-5">
    <?php include __DIR__ . '/_nav.php'; ?>

    <div class="row g-3">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Всего начислено</div>
                    <div class="fs-4 fw-semibold text-success"><?= Helper::escape(Partner::formatMoney((float)$balance['earned'])) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Выплачено</div>
                    <div class="fs-4 fw-semibold"><?= Helper::escape(Partner::formatMoney((float)$balance['paid'])) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Осталось выплатить</div>
                    <div class="fs-4 fw-semibold text-warning"><?= Helper::escape(Partner::formatMoney((float)$balance['remaining'])) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Доступно к выводу</div>
                    <div class="fs-4 fw-semibold text-primary"><?= Helper::escape(Partner::formatMoney((float)$balance['available'])) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="<?= BASE_URL ?>profile/earnings/withdraw" class="btn btn-success">
            <i class="bi bi-bank"></i> Вывести средства
        </a>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Мой баланс';
include __DIR__ . '/../layout.php';
?>

<?php
ob_start();
$balance = $balance ?? ['available' => 0, 'currency' => 'KZT'];
$available = (float)$balance['available'];
?>

<div class="container mt-4 mb-5">
    <?php include __DIR__ . '/_nav.php'; ?>

    <section class="earn-panel mb-0">
        <h2 class="earn-panel-title">Вывод средств</h2>
        <?php if ($available <= 0): ?>
            <p class="mb-0 text-muted">Сейчас нечего выводить. Доступная сумма появится после начислений.</p>
        <?php else: ?>
            <form method="POST" action="<?= BASE_URL ?>profile/earnings/withdraw" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="withdrawAmount">Сумма, KZT</label>
                    <input type="number" name="amount" id="withdrawAmount" class="form-control" min="1" step="1"
                           max="<?= $available ?>" required
                           value="<?= (int)$available ?>">
                    <div class="form-text">Не больше <?= Helper::escape(Partner::formatMoney($available)) ?></div>
                </div>
                <div class="col-12">
                    <label class="form-label" for="withdrawDetails">Реквизиты для выплаты</label>
                    <textarea name="payment_details" id="withdrawDetails" class="form-control" rows="3" required
                              placeholder="Карта, Kaspi, IBAN или телефон кошелька"></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-send"></i> Отправить заявку
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </section>
</div>

<?php
$content = ob_get_clean();
$title = 'Вывести средства';
include __DIR__ . '/../layout.php';
?>

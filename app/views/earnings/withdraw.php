<?php
ob_start();
$balance = $balance ?? ['available' => 0, 'currency' => 'KZT'];
?>

<div class="container mt-4 mb-5">
    <?php include __DIR__ . '/_nav.php'; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h4 class="mb-3">Вывести средства</h4>
            <p class="text-muted">
                Доступно к выводу:
                <strong><?= Helper::escape(Partner::formatMoney((float)$balance['available'])) ?></strong>
            </p>

            <?php if ((float)$balance['available'] <= 0): ?>
                <div class="alert alert-warning mb-0">
                    Сейчас нет доступной суммы для вывода.
                </div>
            <?php else: ?>
                <form method="POST" action="<?= BASE_URL ?>profile/earnings/withdraw" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Сумма (KZT) *</label>
                        <input type="number" name="amount" class="form-control" min="1" step="1"
                               max="<?= (float)$balance['available'] ?>" required
                               value="<?= (int)$balance['available'] ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Реквизиты для выплаты *</label>
                        <textarea name="payment_details" class="form-control" rows="3" required
                                  placeholder="Карта / Kaspi / IBAN / телефон кошелька"></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-send"></i> Отправить заявку
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Вывести средства';
include __DIR__ . '/../layout.php';
?>

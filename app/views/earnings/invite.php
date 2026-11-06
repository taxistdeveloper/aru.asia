<?php
ob_start();
?>

<div class="container mt-4 mb-5">
    <?php include __DIR__ . '/_nav.php'; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h4 class="mb-3">Пригласить бизнес</h4>
            <p class="text-muted">Укажите данные компании. Для защиты от перепривязки нужен email или телефон.</p>

            <form method="POST" action="<?= BASE_URL ?>profile/earnings/invite" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Название компании *</label>
                    <input type="text" name="company_name" class="form-control" required maxlength="255"
                           value="<?= Helper::escape($_POST['company_name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Контактное лицо</label>
                    <input type="text" name="contact_name" class="form-control" maxlength="255">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email компании</label>
                    <input type="email" name="contact_email" class="form-control" maxlength="255">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Телефон компании</label>
                    <input type="text" name="contact_phone" class="form-control" maxlength="64"
                           placeholder="+7 700 000 00 00">
                </div>
                <div class="col-12">
                    <label class="form-label">Сайт</label>
                    <input type="url" name="website" class="form-control" maxlength="255"
                           placeholder="https://">
                </div>
                <div class="col-12">
                    <label class="form-label">Комментарий</label>
                    <textarea name="description" class="form-control" rows="3"
                              placeholder="Чем занимается компания, как вы договорились..."></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Отправить заявку
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Пригласить бизнес';
include __DIR__ . '/../layout.php';
?>

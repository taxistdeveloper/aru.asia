<?php
ob_start();
?>

<div class="container mt-4 mb-5">
    <?php include __DIR__ . '/_nav.php'; ?>

    <section class="earn-panel mb-0">
        <h2 class="earn-panel-title">Пригласить компанию</h2>
        <p class="text-muted">Укажите данные компании. Чтобы её нельзя было перепривязать, нужен email или телефон.</p>

        <form method="POST" action="<?= BASE_URL ?>profile/earnings/invite" class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="companyName">Название компании</label>
                <input type="text" name="company_name" id="companyName" class="form-control" required maxlength="255"
                       value="<?= Helper::escape($_POST['company_name'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="contactName">Контактное лицо</label>
                <input type="text" name="contact_name" id="contactName" class="form-control" maxlength="255">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="contactEmail">Email компании</label>
                <input type="email" name="contact_email" id="contactEmail" class="form-control" maxlength="255">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="contactPhone">Телефон компании</label>
                <input type="text" name="contact_phone" id="contactPhone" class="form-control" maxlength="64"
                       placeholder="+7 700 000 00 00">
            </div>
            <div class="col-12">
                <label class="form-label" for="companyWebsite">Сайт</label>
                <input type="url" name="website" id="companyWebsite" class="form-control" maxlength="255"
                       placeholder="https://">
            </div>
            <div class="col-12">
                <label class="form-label" for="companyDescription">Комментарий</label>
                <textarea name="description" id="companyDescription" class="form-control" rows="3"
                          placeholder="Чем занимается компания и о чём вы договорились"></textarea>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send"></i> Отправить заявку
                </button>
            </div>
        </form>
    </section>
</div>

<?php
$content = ob_get_clean();
$title = 'Пригласить бизнес';
include __DIR__ . '/../layout.php';
?>

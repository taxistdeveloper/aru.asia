<?php
ob_start();
$isPartner = !empty($isPartner);
$partnerLink = $partnerLink ?? null;
?>

<div class="container mt-4 mb-5">
    <?php include __DIR__ . '/_nav.php'; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h4 class="mb-3">Инструкция</h4>

            <ol class="mb-4" style="line-height: 1.75; font-size: 1.05rem;">
                <li class="mb-3">Нажми <strong>стать партнёром</strong> — получи свою ссылку.</li>
                <li class="mb-3">Отправляй ссылку предпринимателям.</li>
                <li class="mb-3">
                    Предприниматель регистрируется по ссылке, оплачивает рекламу —
                    ты автоматически получаешь <strong>50&nbsp;%</strong> от каждой его оплаты.
                    Если эта компания вернётся позже, ты снова автоматически получаешь <strong>50&nbsp;%</strong>.
                </li>
                <li class="mb-0">
                    Все компании от тебя становятся автоматически твоими <strong>навсегда</strong>.
                    Пригласи бизнес один раз и получай <strong>50&nbsp;%</strong> снова и снова.
                </li>
            </ol>

            <?php if ($isPartner && $partnerLink): ?>
                <div class="alert alert-success mb-3">
                    <div class="fw-semibold mb-2">Ваша партнёрская ссылка</div>
                    <div class="input-group">
                        <input type="text" class="form-control" id="partnerLinkInput" readonly
                               value="<?= Helper::escape($partnerLink) ?>">
                        <button type="button" class="btn btn-dark" id="copyPartnerLinkBtn">
                            <i class="bi bi-clipboard"></i> Копировать
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <form method="POST" action="<?= BASE_URL ?>profile/earnings/become" class="mb-3">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-handshake"></i> Стать партнёром
                    </button>
                </form>
            <?php endif; ?>

            <div class="alert alert-info mb-0">
                <i class="bi bi-shield-lock"></i>
                Компания закрепляется за вами навсегда. Перепривязка другому партнёру невозможна.
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
$title = 'Инструкция по заработку';
include __DIR__ . '/../layout.php';
?>

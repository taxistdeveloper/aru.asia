<?php
ob_start();
$companies = $companies ?? [];
$isPartner = !empty($isPartner);
$partnerLink = $partnerLink ?? null;
$shareText = 'Присоединяйтесь к Aru — размещайте рекламу и мероприятия. Моя ссылка:';
$earnAction = [
    'label' => 'Пригласить',
    'icon' => 'bi-share',
    'class' => 'btn-primary',
    'attrs' => $partnerLink
        ? 'data-bs-toggle="modal" data-bs-target="#partnerShareModal"'
        : 'disabled title="Сначала получите партнёрскую ссылку на обзоре"',
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

<?php if ($partnerLink): ?>
<style>
.partner-share-sheet .modal-dialog {
    margin: 0 auto;
    max-width: 420px;
}
@media (max-width: 575.98px) {
    .partner-share-sheet .modal-dialog {
        margin: 0;
        max-width: 100%;
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
    }
    .partner-share-sheet .modal-content {
        border-radius: 18px 18px 0 0;
        border-bottom: 0;
    }
}
.partner-share-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.75rem 0.5rem;
    padding: 0.25rem 0 0.5rem;
}
.partner-share-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.4rem;
    padding: 0.35rem 0.25rem;
    border: 0;
    background: transparent;
    color: #1f1f2e;
    text-decoration: none;
    font-size: 0.78rem;
    font-weight: 550;
    cursor: pointer;
}
.partner-share-item:hover { color: #111; text-decoration: none; }
.partner-share-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    color: #fff;
}
.partner-share-icon.wa { background: #25D366; }
.partner-share-icon.tg { background: #229ED9; }
.partner-share-icon.copy { background: #4b5563; }
.partner-share-icon.more { background: #111827; }
.partner-share-link {
    font-size: 0.8rem;
    word-break: break-all;
    color: #6b6b80;
    background: #f5f5f8;
    border-radius: 10px;
    padding: 0.65rem 0.75rem;
}
</style>

<div class="modal fade partner-share-sheet" id="partnerShareModal" tabindex="-1" aria-labelledby="partnerShareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="partnerShareModalLabel">Поделиться ссылкой</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted small mb-3">Отправьте партнёрскую ссылку предпринимателю — он закрепится за вами.</p>
                <div class="partner-share-link mb-3" id="partnerShareLinkText"><?= Helper::escape($partnerLink) ?></div>
                <div class="partner-share-grid" role="list">
                    <a class="partner-share-item" role="listitem" id="shareWhatsApp" target="_blank" rel="noopener noreferrer">
                        <span class="partner-share-icon wa"><i class="bi bi-whatsapp"></i></span>
                        WhatsApp
                    </a>
                    <a class="partner-share-item" role="listitem" id="shareTelegram" target="_blank" rel="noopener noreferrer">
                        <span class="partner-share-icon tg"><i class="bi bi-telegram"></i></span>
                        Telegram
                    </a>
                    <button type="button" class="partner-share-item" role="listitem" id="shareCopyLink">
                        <span class="partner-share-icon copy"><i class="bi bi-clipboard"></i></span>
                        <span id="shareCopyLabel">Копировать</span>
                    </button>
                    <button type="button" class="partner-share-item" role="listitem" id="shareNative" hidden>
                        <span class="partner-share-icon more"><i class="bi bi-three-dots"></i></span>
                        Ещё
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var link = <?= json_encode($partnerLink, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    var text = <?= json_encode($shareText, JSON_UNESCAPED_UNICODE) ?>;
    var full = text + ' ' + link;
    var encodedFull = encodeURIComponent(full);
    var encodedLink = encodeURIComponent(link);
    var encodedText = encodeURIComponent(text);

    var wa = document.getElementById('shareWhatsApp');
    var tg = document.getElementById('shareTelegram');
    var copyBtn = document.getElementById('shareCopyLink');
    var copyLabel = document.getElementById('shareCopyLabel');
    var nativeBtn = document.getElementById('shareNative');

    if (wa) wa.href = 'https://wa.me/?text=' + encodedFull;
    if (tg) tg.href = 'https://t.me/share/url?url=' + encodedLink + '&text=' + encodedText;

    if (nativeBtn && navigator.share) {
        nativeBtn.hidden = false;
        nativeBtn.addEventListener('click', function () {
            navigator.share({ title: 'Aru', text: text, url: link }).catch(function () {});
        });
    }

    if (copyBtn) {
        copyBtn.addEventListener('click', function () {
            var done = function () {
                if (copyLabel) copyLabel.textContent = 'Скопировано';
                setTimeout(function () {
                    if (copyLabel) copyLabel.textContent = 'Копировать';
                }, 2000);
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(link).then(done).catch(function () {
                    fallbackCopy(link);
                    done();
                });
                return;
            }
            fallbackCopy(link);
            done();
        });
    }

    function fallbackCopy(value) {
        var ta = document.createElement('textarea');
        ta.value = value;
        ta.setAttribute('readonly', '');
        ta.style.position = 'absolute';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(ta);
    }
})();
</script>
<?php endif; ?>

<?php
$content = ob_get_clean();
$title = 'Партнёры';
include __DIR__ . '/../layout.php';
?>

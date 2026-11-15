<?php
ob_start();
$partnerLink = $partnerLink ?? null;
$shareText = $shareText ?? 'Присоединяйтесь к Aru — размещайте рекламу и мероприятия. Моя ссылка:';
?>

<style>
    .earn-invite-wrap { margin-top: 0.15rem; }
    @media (max-width: 767px) {
        .earn-invite-wrap { margin-bottom: 96px; }
    }
</style>

<div class="container mt-4 mb-5">
    <?php include __DIR__ . '/_nav.php'; ?>

    <section class="earn-panel">
        <ol class="mb-0" style="line-height: 1.7;">
            <li class="mb-2">На обзоре ваша партнёрская ссылка.</li>
            <li class="mb-2">Делитесь ссылкой с предпринимателями, самозанятыми и всеми, кто ведёт своё дело.</li>
            <li>
                Как начисляется ваше вознаграждение:
                <ul class="mt-2 mb-0" style="padding-left: 1.1rem;">
                    <li class="mb-2">
                        когда клиент, которого вы привели, оплачивает за рекламу или создание мероприятий,
                        вам начисляется <strong>50%</strong> от суммы его платежа (за вычетом налогов).
                    </li>
                    <li class="mb-2">клиент закрепляется за вами навсегда.</li>
                    <li>
                        это значит, при каждой его последующей оплате — независимо от того,
                        когда и на какую сумму она была сделана, — вознаграждение начисляется вам снова и снова.
                    </li>
                </ul>
            </li>
        </ol>
    </section>

    <div class="earn-invite-wrap">
        <?php if (!empty($partnerLink)): ?>
            <button type="button" class="btn btn-primary btn-lg w-100" data-bs-toggle="modal" data-bs-target="#partnerShareModal">
                Пригласить
            </button>
        <?php else: ?>
            <a href="<?= BASE_URL ?>profile/earnings" class="btn btn-primary btn-lg w-100">
                Пригласить
            </a>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/_share_modal.php'; ?>

<?php
$content = ob_get_clean();
$title = 'Инструкция';
include __DIR__ . '/../layout.php';
?>

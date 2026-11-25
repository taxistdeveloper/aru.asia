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
            <li class="mb-2">На ОБЗОРЕ ваша партнёрская ссылка.</li>
            <li class="mb-2">Делитесь ссылкой с предпринимателями, самозанятыми и просто с знакомыми.</li>
            <li>
                Как начисляется ваше вознаграждение:
                <ul class="mt-2 mb-0" style="padding-left: 1.1rem;">
                    <li class="mb-2">Приглашай предпринимателей, самозанятых и просто своих знакомых.</li>
                    <li class="mb-2">Они размещают рекламу на сайте, дают объявления.</li>
                    <li class="mb-2">Ты получаешь 50% от стоимости каждого размещения (за вычетом налогов и банковских комиссий)</li>
                    <li class="mb-2">Каждый зарегистрировавшийся по твоей ссылке навсегда закрепляется за тобой.</li>
                    <li class="mb-2">Ты получаешь 50% независимо от того, когда он разместит рекламу или объявление.</li>
                    <li>Пригласил один раз, зарабатываешь снова и снова!!!</li>
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

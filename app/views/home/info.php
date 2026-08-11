<?php

/**
 * ИНФОРМАЦИОННАЯ СТРАНИЦА
 * Номер Aru, поиск по номеру, сообщение разработчику, выход
 */

$aruNumber = $aruNumber ?? null;
$aruNumberEnabled = $aruNumberEnabled ?? true;
$searchQuery = $searchQuery ?? '';
$searchStatus = $searchStatus ?? null;
$searchMessage = $searchMessage ?? null;
$toggleMessage = $toggleMessage ?? null;

ob_start();
?>

<style>
    .info-search-hint {
        margin-top: 12px;
        margin-bottom: 0;
        text-align: center;
    }

    .info-search-hint-text {
        display: block;
        font-size: 12px;
        color: #9ca3af;
        margin-bottom: 8px;
    }

    .info-search-platforms {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 6px;
    }

    .info-search-platform {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.01em;
        line-height: 1;
        background: #f3f4f6;
        color: #4b5563;
        border: 1px solid #e5e7eb;
    }

    .info-search-platform i {
        font-size: 13px;
        line-height: 1;
    }

    .info-search-platform--tiktok {
        background: #f5f5f5;
        color: #111827;
        border-color: #e5e7eb;
    }

    .info-search-platform--instagram {
        background: linear-gradient(135deg, rgba(131, 58, 180, 0.08), rgba(253, 29, 29, 0.08), rgba(252, 176, 69, 0.12));
        color: #c13584;
        border-color: rgba(193, 53, 132, 0.2);
    }

    .info-search-platform--telegram {
        background: rgba(34, 158, 217, 0.08);
        color: #229ed9;
        border-color: rgba(34, 158, 217, 0.22);
    }

    .info-search-platform--youtube {
        background: rgba(255, 0, 0, 0.06);
        color: #cc0000;
        border-color: rgba(255, 0, 0, 0.16);
    }

    .info-aru-card .info-search-platforms {
        justify-content: flex-start;
        margin-bottom: 10px;
    }

    .info-aru-card .info-search-hint-text {
        text-align: left;
    }
</style>

<div class="mobile-page-container">
    <div class="text-center py-5">
        <img src="<?= BASE_URL ?>assets/images/logo.jpg" alt="Aru App" style="height: 80px; width: auto; margin-bottom: 30px;">

        <?php if (!empty($_SESSION['delete_account_error'])): ?>
            <div class="alert alert-danger mx-auto" style="max-width: 300px;">
                <?= Helper::escape($_SESSION['delete_account_error']) ?>
            </div>
            <?php unset($_SESSION['delete_account_error']); ?>
        <?php endif; ?>

        <?php if (!empty($toggleMessage)): ?>
            <div class="alert alert-success mx-auto" style="max-width: 300px;">
                <?= Helper::escape($toggleMessage) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($searchMessage)): ?>
            <div class="alert <?= $searchStatus === 'disabled' ? 'alert-warning' : 'alert-info' ?> mx-auto" style="max-width: 300px;">
                <?= Helper::escape($searchMessage) ?>
            </div>
        <?php endif; ?>



        <?php if (Helper::isLoggedIn() && $aruNumber): ?>
            <div class="mx-auto mb-4 text-start" style="max-width: 300px;">
                <div class="border rounded p-3 bg-light info-aru-card">
                    <div class="small text-muted mb-1">Ваш номер</div>
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                        <strong class="fs-4 user-select-all" id="aru-number-value"><?= Helper::escape($aruNumber) ?></strong>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="aru-copy-btn" title="Скопировать">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    <div class="info-search-platforms" aria-label="Платформы">
                        <span class="info-search-platform info-search-platform--tiktok">
                            <i class="bi bi-tiktok" aria-hidden="true"></i> TikTok
                        </span>
                        <span class="info-search-platform info-search-platform--instagram">
                            <i class="bi bi-instagram" aria-hidden="true"></i> Instagram
                        </span>
                        <span class="info-search-platform info-search-platform--telegram">
                            <i class="bi bi-telegram" aria-hidden="true"></i> Telegram
                        </span>
                        <span class="info-search-platform info-search-platform--youtube">
                            <i class="bi bi-youtube" aria-hidden="true"></i> YouTube
                        </span>
                    </div>
                    <p class="small text-muted mb-3 mb-md-2">
                        Укажите в описании или на экране номер
                        <strong><?= Helper::escape($aruNumber) ?></strong>
                    </p>

                    <form method="POST" action="<?= BASE_URL ?>info/toggle-aru-number" class="d-flex align-items-center justify-content-between gap-2">
                        <input type="hidden" name="aru_number_enabled" value="<?= $aruNumberEnabled ? '0' : '1' ?>">
                        <span class="small">
                            <?php if ($aruNumberEnabled): ?>
                                <span class="text-success"><i class="bi bi-eye"></i> Номер включён</span>
                            <?php else: ?>
                                <span class="text-secondary"><i class="bi bi-eye-slash"></i> Номер отключён</span>
                            <?php endif; ?>
                        </span>
                        <button type="submit" class="btn btn-sm <?= $aruNumberEnabled ? 'btn-outline-secondary' : 'btn-success' ?>">
                            <?= $aruNumberEnabled ? 'Отключить' : 'Включить' ?>
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-column gap-3 mt-4" style="max-width: 300px; margin: 0 auto;">
            <!-- Кнопка "Сообщение разработчику" -->
            <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#feedbackModal">
                <i class="bi bi-chat-dots"></i> Сообщение разработчику
            </button>

            <!-- Кнопка всегда доступна здесь (в отличие от баннера/FAB), кроме режима уже установленного PWA -->
            <button type="button" class="btn btn-outline-primary btn-lg pwa-install-trigger pwa-install-persistent" id="info-page-pwa-trigger">
                <i class="bi bi-phone"></i> Добавить на главный экран
            </button>

            <!-- Кнопка "Выход" (только для авторизованных) -->
            <?php if (Helper::isLoggedIn()): ?>
                <a href="<?= BASE_URL ?>auth/logout" class="btn btn-outline-danger btn-lg">
                    <i class="bi bi-box-arrow-right"></i> Выход
                </a>

                <button type="button" class="btn btn-danger btn-lg" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                    <i class="bi bi-trash"></i> Удалить аккаунт
                </button>
            <?php endif; ?>

            <!-- Кнопка "Назад" -->
            <a href="<?= BASE_URL ?><?= Helper::isLoggedIn() ? 'platform' : '' ?>" class="btn btn-secondary btn-lg">
                <i class="bi bi-arrow-left"></i> Назад
            </a>
        </div>
    </div>
</div>

<?php if (Helper::isLoggedIn()): ?>
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="deleteAccountModalLabel">Удалить аккаунт?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Вся ваша <strong>переписка</strong> и <strong>фото</strong> будут удалены сразу.</p>
                <p class="mb-2">Аккаунт сохранится ещё <strong><?= (int)User::SOFT_DELETE_MONTHS ?> месяцев</strong>. Если передумаете — войдите тем же email и паролем, и аккаунт восстановится.</p>
                <p class="mb-0 text-muted small">Если за это время не войдёте, аккаунт будет удалён из базы безвозвратно.</p>
            </div>
            <div class="modal-footer border-0 pt-0 flex-column gap-2">
                <form method="POST" action="<?= BASE_URL ?>auth/delete-account" class="w-100">
                    <button type="submit" class="btn btn-danger w-100">
                        Да, удалить аккаунт
                    </button>
                </form>
                <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">Отмена</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (Helper::isLoggedIn() && $aruNumber): ?>
<script>
(function () {
    var btn = document.getElementById('aru-copy-btn');
    var valueEl = document.getElementById('aru-number-value');
    if (!btn || !valueEl) return;
    btn.addEventListener('click', function () {
        var text = valueEl.textContent.trim();
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () {
                btn.innerHTML = '<i class="bi bi-check2"></i>';
                setTimeout(function () { btn.innerHTML = '<i class="bi bi-clipboard"></i>'; }, 1500);
            });
        }
    });
})();
</script>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>

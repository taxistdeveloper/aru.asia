<?php

/**
 * ИНФОРМАЦИОННАЯ СТРАНИЦА
 * Страница с кнопками "Сообщение разработчику" и "Выход"
 */

// Подключаем базовый шаблон
ob_start();
?>

<div class="mobile-page-container">
    <div class="text-center py-5">
        <img src="<?= BASE_URL ?>assets/images/logo.jpg" alt="Aru App" style="height: 80px; width: auto; margin-bottom: 30px;">

        <?php if (!empty($_SESSION['delete_account_error'])): ?>
            <div class="alert alert-danger mx-auto" style="max-width: 300px;">
                <?= Helper::escape($_SESSION['delete_account_error']) ?>
            </div>
            <?php unset($_SESSION['delete_account_error']); ?>
        <?php endif; ?>

        <div class="d-flex flex-column gap-3 mt-4" style="max-width: 300px; margin: 0 auto;">
            <!-- Кнопка "Сообщение разработчику" -->
            <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#feedbackModal">
                <i class="bi bi-chat-dots"></i> Сообщение разработчику
            </button>

            <!-- Кнопка "Добавить на главный экран телефона" (PWA; на iOS — ручная установка через Safari) -->
            <button type="button" class="btn btn-outline-primary btn-lg pwa-install-trigger" id="info-page-pwa-trigger">
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

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>

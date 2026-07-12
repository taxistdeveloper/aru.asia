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
            <?php endif; ?>

            <!-- Кнопка "Назад" -->
            <a href="<?= BASE_URL ?><?= Helper::isLoggedIn() ? 'platform' : '' ?>" class="btn btn-secondary btn-lg">
                <i class="bi bi-arrow-left"></i> Назад
            </a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>
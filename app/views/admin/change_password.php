<?php

/**
 * АДМИН-ПАНЕЛЬ - СМЕНА ПАРОЛЯ АДМИНИСТРАТОРА
 */

ob_start();
?>

<div class="mt-4">
    <h2 class="mb-3">
        <i class="bi bi-key"></i> Смена пароля администратора
    </h2>

    <p class="text-muted mb-4">
        По безопасности рекомендуется регулярно менять пароль и не использовать тот же пароль, что и для других сервисов.
    </p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= Helper::escape($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>admin/change-password" class="row g-3">
                <div class="col-12">
                    <label for="current_password" class="form-label">Текущий пароль</label>
                    <input type="password"
                           class="form-control"
                           id="current_password"
                           name="current_password"
                           required
                           autocomplete="current-password">
                </div>
                <div class="col-12 col-md-6">
                    <label for="new_password" class="form-label">Новый пароль</label>
                    <input type="password"
                           class="form-control"
                           id="new_password"
                           name="new_password"
                           required
                           minlength="8"
                           autocomplete="new-password">
                    <div class="form-text">Минимум 8 символов.</div>
                </div>
                <div class="col-12 col-md-6">
                    <label for="new_password_confirmation" class="form-label">Повторите новый пароль</label>
                    <input type="password"
                           class="form-control"
                           id="new_password_confirmation"
                           name="new_password_confirmation"
                           required
                           minlength="8"
                           autocomplete="new-password">
                </div>
                <div class="col-12 d-flex justify-content-between align-items-center mt-3">
                    <a href="<?= BASE_URL ?>admin" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Назад в дашборд
                    </a>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-key"></i> Сменить пароль
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Смена пароля администратора';
include __DIR__ . '/../admin_layout.php';
?>



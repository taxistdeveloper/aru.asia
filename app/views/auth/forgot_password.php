<?php

/**
 * СТРАНИЦА ВОССТАНОВЛЕНИЯ ПАРОЛЯ - ВВОД EMAIL
 */

ob_start();
?>

<div class="mobile-form-container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h3 class="card-title text-center mb-4">Восстановление пароля</h3>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?= Helper::escape($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success">
                            <?= Helper::escape($success) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!isset($success)): ?>
                        <p class="text-muted mb-4">Введите ваш email адрес, и мы отправим вам ссылку для восстановления пароля.</p>

                        <form method="POST" action="<?= BASE_URL ?>auth/forgot-password">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email"
                                    class="form-control"
                                    id="email"
                                    name="email"
                                    required
                                    placeholder="your@email.com">
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                Отправить ссылку
                            </button>
                        </form>
                    <?php endif; ?>

                    <div class="text-center">
                        <a href="<?= BASE_URL ?>auth/login">Вернуться к входу</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Восстановление пароля';
include __DIR__ . '/../layout.php';
?>











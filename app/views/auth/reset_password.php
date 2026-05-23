<?php

/**
 * СТРАНИЦА ВОССТАНОВЛЕНИЯ ПАРОЛЯ - ВВОД НОВОГО ПАРОЛЯ
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

                    <?php if (!isset($error) || (isset($error) && strpos($error, 'Неверный токен') === false && strpos($error, 'Токен истек') === false)): ?>
                        <p class="text-muted mb-4">Введите новый пароль для вашего аккаунта.</p>

                        <form method="POST" action="<?= BASE_URL ?>auth/reset-password">
                            <input type="hidden" name="token" value="<?= Helper::escape($token ?? '') ?>">

                            <div class="mb-3">
                                <label for="password" class="form-label">Новый пароль</label>
                                <div class="position-relative">
                                    <input type="password"
                                        class="form-control"
                                        id="password"
                                        name="password"
                                        required
                                        placeholder="Введите новый пароль"
                                        minlength="6"
                                        style="padding-right: 50px;">
                                    <button type="button"
                                        class="btn btn-link position-absolute top-50 translate-middle-y"
                                        id="togglePassword"
                                        style="right: 8px; border: none; background: transparent; padding: 0; margin: 0; color: #6c757d; text-decoration: none; cursor: pointer; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; z-index: 10; -webkit-tap-highlight-color: transparent; line-height: 1;">
                                        <i class="bi bi-eye" id="togglePasswordIcon" style="font-size: 1.2rem; display: inline-block; vertical-align: middle;"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted">Минимум 6 символов</small>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Подтвердите пароль</label>
                                <div class="position-relative">
                                    <input type="password"
                                        class="form-control"
                                        id="confirm_password"
                                        name="confirm_password"
                                        required
                                        placeholder="Повторите новый пароль"
                                        minlength="6"
                                        style="padding-right: 50px;">
                                    <button type="button"
                                        class="btn btn-link position-absolute top-50 translate-middle-y"
                                        id="toggleConfirmPassword"
                                        style="right: 8px; border: none; background: transparent; padding: 0; margin: 0; color: #6c757d; text-decoration: none; cursor: pointer; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; z-index: 10; -webkit-tap-highlight-color: transparent; line-height: 1;">
                                        <i class="bi bi-eye" id="toggleConfirmPasswordIcon" style="font-size: 1.2rem; display: inline-block; vertical-align: middle;"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                Изменить пароль
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Переключение видимости основного пароля
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        if (togglePassword && passwordInput && toggleIcon) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                if (type === 'password') {
                    toggleIcon.classList.remove('bi-eye-slash');
                    toggleIcon.classList.add('bi-eye');
                } else {
                    toggleIcon.classList.remove('bi-eye');
                    toggleIcon.classList.add('bi-eye-slash');
                }
            });
        }

        // Переключение видимости подтверждения пароля
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const toggleConfirmIcon = document.getElementById('toggleConfirmPasswordIcon');

        if (toggleConfirmPassword && confirmPasswordInput && toggleConfirmIcon) {
            toggleConfirmPassword.addEventListener('click', function() {
                const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPasswordInput.setAttribute('type', type);

                if (type === 'password') {
                    toggleConfirmIcon.classList.remove('bi-eye-slash');
                    toggleConfirmIcon.classList.add('bi-eye');
                } else {
                    toggleConfirmIcon.classList.remove('bi-eye');
                    toggleConfirmIcon.classList.add('bi-eye-slash');
                }
            });
        }
    });
</script>

<?php
$content = ob_get_clean();
$title = 'Восстановление пароля';
include __DIR__ . '/../layout.php';
?>











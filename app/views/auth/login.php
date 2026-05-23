<?php

/**
 * СТРАНИЦА ВХОДА
 */

ob_start();
?>

<div class="mobile-form-container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h3 class="card-title text-center mb-4">Вход</h3>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?= Helper::escape($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['deleted_user_message'])): ?>
                        <div class="alert alert-danger">
                            <?= Helper::escape($_SESSION['deleted_user_message']) ?>
                        </div>
                        <?php unset($_SESSION['deleted_user_message']); ?>
                    <?php endif; ?>

                    <?php if (isset($_GET['password_reset']) && $_GET['password_reset'] === 'success'): ?>
                        <div class="alert alert-success">
                            Пароль успешно изменен! Теперь вы можете войти с новым паролем.
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= BASE_URL ?>auth/login">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                required
                                placeholder="your@email.com">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Пароль</label>
                            <div class="position-relative">
                                <input type="password"
                                    class="form-control"
                                    id="password"
                                    name="password"
                                    required
                                    placeholder="Введите пароль"
                                    style="padding-right: 50px;">
                                <button type="button"
                                    class="btn btn-link position-absolute top-50 translate-middle-y"
                                    id="togglePassword"
                                    style="right: 8px; border: none; background: transparent; padding: 0; margin: 0; color: #6c757d; text-decoration: none; cursor: pointer; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; z-index: 10; -webkit-tap-highlight-color: transparent; line-height: 1;">
                                    <i class="bi bi-eye" id="togglePasswordIcon" style="font-size: 1.2rem; display: inline-block; vertical-align: middle;"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox"
                                class="form-check-input"
                                id="remember_me"
                                name="remember_me"
                                value="1">
                            <label class="form-check-label" for="remember_me">
                                Запомнить меня
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            Войти
                        </button>
                    </form>

                    <div class="text-center mb-2">
                        <a href="<?= BASE_URL ?>auth/forgot-password" style="text-decoration: none; color: #6c757d; font-size: 0.9rem;">Забыли пароль?</a>
                    </div>

                    <div class="text-center">
                        <p>Нет аккаунта? <a href="<?= BASE_URL ?>auth/register">Зарегистрироваться</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        if (togglePassword && passwordInput && toggleIcon) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                // Переключаем иконку
                if (type === 'password') {
                    toggleIcon.classList.remove('bi-eye-slash');
                    toggleIcon.classList.add('bi-eye');
                } else {
                    toggleIcon.classList.remove('bi-eye');
                    toggleIcon.classList.add('bi-eye-slash');
                }
            });
        }
    });
</script>

<?php
$content = ob_get_clean();
$title = 'Вход';
include __DIR__ . '/../layout.php';
?>

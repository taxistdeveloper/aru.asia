<?php
/**
 * СТРАНИЦА ВХОДА В АДМИН-ПАНЕЛЬ
 */

ob_start();
?>

<style>
    .admin-login {
        min-height: 100vh;
    }

    @media (max-width: 576px) {
        .admin-login {
            margin-top: 0;
            padding: 24px 12px;
        }

        .admin-login .card {
            border: 0;
            border-radius: 18px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        }

        .admin-login .card-body {
            padding: 24px;
        }

        .admin-login .card-title {
            font-size: 1.25rem;
            margin-bottom: 20px;
        }

        .admin-login .form-control {
            height: 48px;
            border-radius: 12px;
        }

        .admin-login .btn {
            padding: 12px;
            border-radius: 12px;
        }

        .admin-login .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .admin-login .text-center a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
    }
</style>

<div class="row justify-content-center mt-5 admin-login">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow border-danger">
            <div class="card-body p-4">
                <h3 class="card-title text-center mb-4">
                    <i class="bi bi-shield-lock"></i> Вход в админ-панель
                </h3>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?= Helper::escape($error) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="<?= BASE_URL ?>admin/login">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email администратора</label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               required 
                               placeholder="admin@tanisu-app.com"
                               autocomplete="username">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль</label>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               required
                               autocomplete="current-password">
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="remember_admin">
                        <label class="form-check-label" for="remember_admin">
                            Запомнить логин и пароль
                        </label>
                    </div>

                    <button type="submit" class="btn btn-danger w-100 mb-3">
                        Войти
                    </button>
                </form>

                <script>
                    (function () {
                        const emailInput = document.getElementById('email');
                        const passwordInput = document.getElementById('password');
                        const rememberInput = document.getElementById('remember_admin');
                        const form = emailInput && emailInput.form;

                        const savedEmail = localStorage.getItem('admin_login_email');
                        const savedPassword = localStorage.getItem('admin_login_password');
                        const rememberFlag = localStorage.getItem('admin_login_remember') === '1';

                        if (savedEmail) {
                            emailInput.value = savedEmail;
                        }
                        if (savedPassword) {
                            passwordInput.value = savedPassword;
                        }
                        if (rememberFlag) {
                            rememberInput.checked = true;
                        }

                        if (form) {
                            form.addEventListener('submit', function () {
                                if (rememberInput.checked) {
                                    localStorage.setItem('admin_login_email', emailInput.value);
                                    localStorage.setItem('admin_login_password', passwordInput.value);
                                    localStorage.setItem('admin_login_remember', '1');
                                } else {
                                    localStorage.removeItem('admin_login_email');
                                    localStorage.removeItem('admin_login_password');
                                    localStorage.removeItem('admin_login_remember');
                                }
                            });
                        }
                    })();
                </script>
                
                <div class="text-center">
                    <a href="<?= BASE_URL ?>home" class="text-muted">
                        <small>← Вернуться на сайт</small>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Вход в админ-панель';
include __DIR__ . '/../layout.php';
?>


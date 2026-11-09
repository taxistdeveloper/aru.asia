<?php

/**
 * СТРАНИЦА РЕГИСТРАЦИИ
 */

ob_start();
?>

<div class="mobile-form-container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h3 class="card-title text-center mb-4">Регистрация</h3>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?= Helper::escape($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= BASE_URL ?>auth/register">
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
                                    minlength="6"
                                    placeholder="Введите пароль"
                                    style="padding-right: 50px;">
                                <button type="button"
                                    class="btn btn-link position-absolute top-50 translate-middle-y"
                                    id="togglePassword"
                                    style="right: 8px; border: none; background: transparent; padding: 0; margin: 0; color: #6c757d; text-decoration: none; cursor: pointer; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; z-index: 10; -webkit-tap-highlight-color: transparent; line-height: 1;">
                                    <i class="bi bi-eye" id="togglePasswordIcon" style="font-size: 1.2rem; display: inline-block; vertical-align: middle;"></i>
                                </button>
                            </div>
                            <small class="text-muted">Минимум 6 символов</small>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Подтвердите пароль</label>
                            <div class="position-relative">
                                <input type="password"
                                    class="form-control"
                                    id="confirm_password"
                                    name="confirm_password"
                                    required
                                    placeholder="Повторите пароль"
                                    style="padding-right: 50px;">
                                <button type="button"
                                    class="btn btn-link position-absolute top-50 translate-middle-y"
                                    id="toggleConfirmPassword"
                                    style="right: 8px; border: none; background: transparent; padding: 0; margin: 0; color: #6c757d; text-decoration: none; cursor: pointer; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; z-index: 10; -webkit-tap-highlight-color: transparent; line-height: 1;">
                                    <i class="bi bi-eye" id="toggleConfirmPasswordIcon" style="font-size: 1.2rem; display: inline-block; vertical-align: middle;"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="consent1" name="consent1" required>
                                <label class="form-check-label" for="consent1">
                                    Я согласен с условиями пользовательского соглашения
                                </label>
                            </div>
                            <div class="text-center">
                                <button type="button" class="btn btn-link btn-sm p-2" data-bs-toggle="modal" data-bs-target="#consentModal1" style="text-decoration: none; font-size: 0.875rem;">
                                    Подробнее
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            Зарегистрироваться
                        </button>
                    </form>

                    <div class="text-center">
                        <p>Уже есть аккаунт? <a href="<?= BASE_URL ?>auth/login">Войти</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для пользовательского соглашения -->
<div class="modal fade" id="consentModal1" tabindex="-1" aria-labelledby="consentModal1Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="consentModal1Label">Пользовательское соглашение</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body agreement-text">
                <p class="mb-3">Настоящее пользовательское соглашение определяет условия использования сервиса. Регистрируясь на сайте, вы подтверждаете, что ознакомились с условиями и согласны с ними.</p>
                <p class="mb-3">Пользователь обязуется использовать сервис в соответствии с законодательством и не нарушать права других пользователей.</p>

                <p class="fw-semibold mb-2">Основные положения:</p>
                <ol class="agreement-list">
                    <li class="mb-3">
                        <strong>Ответственность пользователя</strong>
                        <ol type="a">
                            <li>Пользователь самостоятельно несёт полную ответственность за своё нахождение на сайте знакомств, а также за все действия, совершаемые им на сайте, включая, но не ограничиваясь, публикацией текстовой информации, фотографий, видео и иных материалов.</li>
                            <li>Пользователь несёт ответственность за содержание размещаемой им информации, включая высказывания, которые могут содержать оскорбления, унижения, клевету или иные противоправные действия в отношении других Пользователей или третьих лиц.</li>
                            <li>Пользователь несёт ответственность за предоставление своих личных данных другим Пользователям, а также за достоверность и точность указанных им персональных данных, в том числе возраста. Пользователь подтверждает, что ему исполнилось 18 (восемнадцать) лет, поскольку использование сайта разрешено только лицам, достигшим совершеннолетия.</li>
                        </ol>
                    </li>
                    <li class="mb-3">
                        <strong>Ответственность за объявления о свиданиях и проведение встреч</strong>
                        <ol type="a">
                            <li>Пользователь самостоятельно несёт ответственность за содержание и достоверность объявлений о свиданиях, размещаемых им на сайте.</li>
                            <li>Пользователь несёт ответственность за организацию и проведение встреч, инициированных посредством размещённых объявлений, а также за последствия таких встреч.</li>
                        </ol>
                    </li>
                    <li class="mb-3">
                        <strong>Ответственность за рекламную информацию</strong>
                        <ol type="a">
                            <li>Пользователь несёт полную ответственность за размещаемую им рекламную информацию на сайте, включая указанные бренды, цены, условия и иные сведения.</li>
                            <li>Пользователь гарантирует, что размещаемая им рекламная информация не нарушает права третьих лиц, включая авторские права, права на товарные знаки и иные права интеллектуальной собственности.</li>
                            <li>Пользователь несёт ответственность за законность, достоверность и соответствие размещаемой рекламной информации требованиям действующего законодательства.</li>
                        </ol>
                    </li>
                    <li class="mb-3">
                        <strong>Ответственность за объявления о мероприятиях</strong>
                        <ol type="a">
                            <li>Пользователь несёт ответственность за содержание объявлений о коммерческих и некоммерческих мероприятиях, размещаемых им на сайте.</li>
                            <li>Пользователь несёт ответственность за достоверность информации, указанной в таких объявлениях, включая цены, условия и иные сведения.</li>
                            <li>Пользователь несёт ответственность за организацию и проведение мероприятий, о которых он объявляет, а также за последствия проведения таких мероприятий.</li>
                        </ol>
                    </li>
                    <li class="mb-3">
                        <strong>Ответственность Администрации</strong>
                        <ol type="a">
                            <li>Администрация предоставляет Пользователям площадку для знакомства, общения, размещения рекламы и объявлений о мероприятиях.</li>
                            <li>Администрация не несёт ответственности за содержание, достоверность, законность и последствия размещённой Пользователями информации.</li>
                            <li>Администрация не несёт ответственности за действия Пользователей, включая организацию и проведение встреч, мероприятий, а также за любые последствия, возникшие в результате использования Пользователями сайта.</li>
                        </ol>
                    </li>
                    <li class="mb-3">
                        <strong>Применимое законодательство и разрешение споров</strong>
                        <ol type="a">
                            <li>Все отношения, возникающие в связи с настоящим Договором, регулируются законодательством страны, в которой находится Пользователь, а также законодательством страны, на территории которой размещается информация, предоставляемая Пользователем.</li>
                            <li>Пользователь обязуется соблюдать требования законодательства соответствующих юрисдикций, включая, но не ограничиваясь, нормами о защите персональных данных, авторских правах, рекламе, а также иными применимыми нормативными актами.</li>
                            <li>Все споры и разногласия, возникающие из настоящего Договора или в связи с ним, подлежат разрешению путём переговоров между сторонами. В случае недостижения соглашения спор подлежит рассмотрению в судебном порядке в соответствии с применимым законодательством.</li>
                        </ol>
                    </li>
                    <li class="mb-3">
                        <strong>Заключительные положения</strong>
                        <ol type="a">
                            <li>Настоящий Договор вступает в силу с момента регистрации Пользователя на сайте и действует до прекращения использования сайта.</li>
                            <li>Администрация оставляет за собой право в любое время вносить изменения и дополнения в настоящий Договор без предварительного согласия Пользователя. Изменения вступают в силу с момента их публикации на сайте.</li>
                            <li>Продолжение использования сайта после внесения изменений означает согласие Пользователя с новыми условиями Договора.</li>
                            <li>Если какое-либо положение настоящего Договора будет признано недействительным или неисполнимым, это не влияет на действительность остальных положений Договора.</li>
                            <li>Настоящий Договор является публичной офертой. Регистрация и использование сайта означает полное и безусловное принятие Пользователем всех условий настоящего Договора.</li>
                        </ol>
                    </li>
                </ol>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для политики конфиденциальности -->
<div class="modal fade" id="consentModal2" tabindex="-1" aria-labelledby="consentModal2Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="consentModal2Label">Политика конфиденциальности</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body agreement-text">
                <p>Настоящая политика конфиденциальности описывает, как мы собираем, используем и защищаем ваши персональные данные.</p>
                <p class="mb-0">Мы обязуемся защищать конфиденциальность ваших данных и использовать их только в целях предоставления услуг.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Переключатель для основного поля пароля
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

        // Переключатель для поля подтверждения пароля
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const toggleConfirmIcon = document.getElementById('toggleConfirmPasswordIcon');

        if (toggleConfirmPassword && confirmPasswordInput && toggleConfirmIcon) {
            toggleConfirmPassword.addEventListener('click', function() {
                const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPasswordInput.setAttribute('type', type);

                // Переключаем иконку
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
$title = 'Регистрация';
include __DIR__ . '/../layout.php';
?>

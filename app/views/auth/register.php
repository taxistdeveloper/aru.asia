<?php

/**
 * СТРАНИЦА РЕГИСТРАЦИИ
 */

ob_start();
?>

<style>
    .consent-block {
        border: 1px solid #e8ecf4;
        border-radius: 12px;
        background: linear-gradient(180deg, #f8f9ff 0%, #ffffff 100%);
        overflow: hidden;
    }

    .consent-block-header {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 14px;
        font-size: 0.875rem;
        font-weight: 600;
        color: #4a5568;
        background: rgba(102, 126, 234, 0.08);
        border-bottom: 1px solid #e8ecf4;
    }

    .consent-block-header i {
        color: #667eea;
        font-size: 1.1rem;
    }

    .consent-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 12px 14px;
        border-bottom: 1px solid #eef1f7;
        transition: background-color 0.2s ease;
    }

    .consent-row:last-child {
        border-bottom: none;
    }

    .consent-row:hover {
        background-color: rgba(102, 126, 234, 0.04);
    }

    .consent-row .form-check {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
        flex: 1;
    }

    .consent-row .form-check-input {
        width: 1.1rem;
        height: 1.1rem;
        margin: 0;
        flex-shrink: 0;
        border-color: #c5cbe0;
        cursor: pointer;
    }

    .consent-row .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }

    .consent-row .form-check-input:focus {
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.2);
        border-color: #667eea;
    }

    .consent-row .form-check-label {
        font-size: 0.9rem;
        color: #2d3748;
        cursor: pointer;
        line-height: 1.3;
    }

    .consent-read-btn {
        display: inline-flex;
        align-items: center;
        gap: 2px;
        border: none;
        background: transparent;
        color: #667eea;
        font-size: 0.8rem;
        font-weight: 500;
        padding: 4px 6px;
        border-radius: 6px;
        white-space: nowrap;
        flex-shrink: 0;
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    .consent-read-btn:hover {
        background: rgba(102, 126, 234, 0.1);
        color: #5a67d8;
    }

    .consent-read-btn i {
        font-size: 0.75rem;
    }

    .agreement-text {
        font-size: 0.92rem;
        line-height: 1.55;
        color: #4a5568;
    }

    .agreement-list {
        padding-left: 1.1rem;
    }

    .agreement-list > li > strong {
        color: #2d3748;
        display: inline-block;
        margin-bottom: 0.35rem;
    }

    #consentModalOffer .modal-dialog {
        height: calc(100% - 2rem);
        max-height: calc(100vh - 2rem);
    }

    #consentModalOffer .modal-content {
        height: 100%;
    }

    #consentModalOffer .modal-body {
        min-height: 0;
    }

    .offer-pdf-frame {
        display: block;
        width: 100%;
        height: 100%;
        border: 0;
        background: #f7f8fc;
    }

    @media (max-width: 576px) {
        #consentModalOffer .modal-dialog {
            margin: 0.5rem;
            max-width: calc(100% - 1rem);
            height: calc(100% - 1rem);
            max-height: calc(100vh - 1rem);
        }
    }
</style>

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

                        <div class="consent-block mb-4">
                            <div class="consent-block-header">
                                <i class="bi bi-shield-check"></i>
                                <span>Для регистрации примите условия</span>
                            </div>

                            <div class="consent-row">
                                <div class="form-check m-0">
                                    <input class="form-check-input" type="checkbox" id="consent1" name="consent1" required>
                                    <label class="form-check-label" for="consent1">Пользовательское соглашение</label>
                                </div>
                                <button type="button" class="consent-read-btn" data-bs-toggle="modal" data-bs-target="#consentModal1">
                                    Читать <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>

                            <div class="consent-row">
                                <div class="form-check m-0">
                                    <input class="form-check-input" type="checkbox" id="consentOffer" name="consent_offer" required>
                                    <label class="form-check-label" for="consentOffer">Договор оферты</label>
                                </div>
                                <button type="button" class="consent-read-btn" data-bs-toggle="modal" data-bs-target="#consentModalOffer">
                                    Читать <i class="bi bi-chevron-right"></i>
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
                    <li class="mb-3">
                        <strong>Политика конфиденциальности</strong>
                        <ol type="a">
                            <li>Настоящая политика конфиденциальности описывает, как Администрация собирает, использует и защищает персональные данные Пользователя.</li>
                            <li>Администрация обязуется защищать конфиденциальность персональных данных Пользователя и использовать их только в целях предоставления услуг сайта.</li>
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

<!-- Модальное окно для договора оферты -->
<div class="modal fade" id="consentModalOffer" tabindex="-1" aria-labelledby="consentModalOfferLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="consentModalOfferLabel">Договор оферты</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body p-0">
                <iframe
                    id="offerPdfFrame"
                    class="offer-pdf-frame"
                    title="Договор публичной оферты"
                    data-src="<?= BASE_URL ?>oferta.pdf"></iframe>
            </div>
            <div class="modal-footer">
                <a class="btn btn-outline-primary" href="<?= BASE_URL ?>oferta.pdf" target="_blank" rel="noopener">Открыть PDF</a>
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

        const offerModal = document.getElementById('consentModalOffer');
        const offerFrame = document.getElementById('offerPdfFrame');
        if (offerModal && offerFrame) {
            offerModal.addEventListener('show.bs.modal', function() {
                if (!offerFrame.getAttribute('src')) {
                    offerFrame.src = offerFrame.dataset.src;
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

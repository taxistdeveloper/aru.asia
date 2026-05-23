<?php

/**
 * СТРАНИЦА УСПЕШНОЙ РЕГИСТРАЦИИ
 */

ob_start();
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-body p-4 text-center">
                <i class="bi bi-check-circle text-success" style="font-size: 64px;"></i>
                <h3 class="mt-3">Регистрация успешна!</h3>
                <?php if (isset($emailSent) && $emailSent): ?>
                    <p class="text-muted">
                        Мы отправили письмо на ваш email <strong><?= Helper::escape($email ?? '') ?></strong> для подтверждения регистрации.
                    </p>
                    <p class="text-muted">
                        Пожалуйста, проверьте вашу почту и перейдите по ссылке в письме для активации аккаунта.
                    </p>
                    
                    <?php 
                    $emailServiceUrl = Helper::getEmailServiceUrl($email ?? '');
                    $emailServiceName = Helper::getEmailServiceName($email ?? '');
                    ?>
                    
                    <?php if ($emailServiceUrl): ?>
                        <div class="mt-4 mb-3">
                            <a href="<?= Helper::escape($emailServiceUrl) ?>" 
                               target="_blank" 
                               class="btn btn-success btn-lg"
                               style="min-width: 200px;">
                                <i class="bi bi-envelope-check"></i> Открыть <?= Helper::escape($emailServiceName) ?>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="mt-4 mb-3">
                            <a href="mailto:" 
                               class="btn btn-success btn-lg"
                               style="min-width: 200px;">
                                <i class="bi bi-envelope-check"></i> Открыть почту
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-info mt-3">
                        <small>
                            <i class="bi bi-info-circle"></i> Если письмо не пришло, проверьте папку "Спам" или подождите несколько минут.
                        </small>
                    </div>
                <?php else: ?>
                    <p class="text-muted">
                        Регистрация завершена, но произошла ошибка при отправке письма на ваш email <strong><?= Helper::escape($email ?? '') ?></strong>.
                    </p>
                    <p class="text-muted">
                        Не волнуйтесь! Вы можете активировать аккаунт прямо сейчас, перейдя по ссылке ниже:
                    </p>
                    <?php if (isset($verifyUrl) && !empty($verifyUrl)): ?>
                        <div class="alert alert-info mt-3">
                            <p class="mb-2"><strong>Ссылка для активации аккаунта:</strong></p>
                            <p class="mb-0">
                                <a href="<?= Helper::escape($verifyUrl) ?>" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Активировать аккаунт сейчас
                                </a>
                            </p>
                            <p class="mt-3 mb-0">
                                <small><strong>Или скопируйте и вставьте эту ссылку в браузер:</strong></small><br>
                                <code style="font-size: 12px; word-break: break-all; display: block; padding: 10px; background: #f8f9fa; border-radius: 4px; margin-top: 5px;"><?= Helper::escape($verifyUrl) ?></code>
                            </p>
                        </div>
                        <div class="alert alert-warning mt-3">
                            <small>
                                <i class="bi bi-info-circle"></i> <strong>Совет:</strong> Сохраните эту ссылку, если хотите активировать аккаунт позже. Ссылка действительна до активации.
                            </small>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mt-3">
                            <small>
                                <i class="bi bi-exclamation-triangle"></i> Email: <strong><?= Helper::escape($email ?? '') ?></strong>
                            </small>
                            <p class="mt-2 mb-0">
                                Пожалуйста, обратитесь в поддержку для активации вашего аккаунта.
                            </p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                <div class="mt-4">
                    
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Регистрация успешна';
include __DIR__ . '/../layout.php';
?>

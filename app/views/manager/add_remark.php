<?php
/**
 * СТРАНИЦА ДОБАВЛЕНИЯ ЗАМЕЧАНИЯ ДЛЯ ПОЛЬЗОВАТЕЛЯ
 */

ob_start();
?>

<div class="mt-4">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">
                <i class="bi bi-exclamation-triangle"></i> Добавить замечание для пользователя
            </h4>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <div class="mb-4">
                <h5>Информация о пользователе:</h5>
                <p class="mb-1"><strong>Email:</strong> <?= Helper::escape($user['email']) ?></p>
                <?php if (!empty($user['full_name'])): ?>
                    <p class="mb-1"><strong>Имя:</strong> <?= Helper::escape($user['full_name']) ?></p>
                <?php endif; ?>
                <p class="mb-0"><strong>ID:</strong> <?= $user['id'] ?></p>
            </div>

            <form method="POST" action="<?= BASE_URL ?>manager/users/set-remark">
                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                
                <div class="mb-3">
                    <label for="remark_type" class="form-label">
                        <strong>Тип замечания <span class="text-danger">*</span></strong>
                    </label>
                    <select class="form-select" id="remark_type" name="remark_type" required>
                        <option value="">-- Выберите тип замечания --</option>
                        <option value="full_name">ФИО (Полное имя)</option>
                        <option value="about">О себе</option>
                        <option value="photo">Фотография</option>
                    </select>
                    <small class="text-muted">Выберите, какое поле профиля нужно исправить.</small>
                </div>

                <div class="mb-3">
                    <label for="remark" class="form-label">
                        <strong>Замечание <span class="text-danger">*</span></strong>
                    </label>
                    <textarea class="form-control"
                        id="remark"
                        name="remark"
                        rows="8"
                        required
                        placeholder="Опишите, что нужно исправить в профиле пользователя. После добавления замечания профиль будет заблокирован до исправления."></textarea>
                    <small class="text-muted">Профиль будет заблокирован до исправления указанных ошибок.</small>
                </div>

                <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>manager/users" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Отмена
                    </a>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-exclamation-triangle"></i> Заблокировать профиль
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = $title ?? 'Добавить замечание - Панель менеджера';
include __DIR__ . '/../admin_layout.php';
?>


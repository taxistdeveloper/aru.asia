<?php
/**
 * СТРАНИЦА ПРОСМОТРА ЗАМЕЧАНИЯ ДЛЯ ПОЛЬЗОВАТЕЛЯ
 */

ob_start();
?>

<div class="mt-4">
    <div class="card">
        <div class="card-header bg-warning">
            <h4 class="mb-0">
                <i class="bi bi-exclamation-triangle"></i> Замечание для пользователя
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

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <div class="mb-4">
                <h5>Информация о пользователе:</h5>
                <p class="mb-1"><strong>Email:</strong> <?= Helper::escape($user['email']) ?></p>
                <?php if (!empty($user['full_name'])): ?>
                    <p class="mb-1"><strong>Имя:</strong> <?= Helper::escape($user['full_name']) ?></p>
                <?php endif; ?>
                <p class="mb-0"><strong>ID:</strong> <?= $user['id'] ?></p>
            </div>

            <div class="alert alert-warning">
                <h5 class="alert-heading">
                    <i class="bi bi-exclamation-triangle"></i> Замечание:
                </h5>
                <?php
                $fieldNames = [
                    'full_name' => 'ФИО (Полное имя)',
                    'about' => 'О себе',
                    'photo' => 'Фотография'
                ];
                $fieldName = !empty($user['remark_type']) && isset($fieldNames[$user['remark_type']]) ? $fieldNames[$user['remark_type']] : null;
                ?>
                <?php if ($fieldName): ?>
                    <p class="mb-2"><strong>Тип замечания: <span class="text-danger"><?= Helper::escape($fieldName) ?></span></strong></p>
                <?php endif; ?>
                <p class="mb-0 mt-2"><?= nl2br(Helper::escape($user['admin_remark'])) ?></p>
            </div>

            <p class="text-muted">
                <i class="bi bi-info-circle"></i> Профиль заблокирован до исправления указанных ошибок.
            </p>

            <div class="d-flex gap-2 flex-wrap">
                <a href="<?= BASE_URL ?>profile/view?id=<?= $user['id'] ?>"
                    class="btn btn-primary"
                    target="_blank">
                    <i class="bi bi-person"></i> Просмотреть профиль
                </a>
                <a href="<?= BASE_URL ?>manager/users" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Назад к списку
                </a>
                <form method="POST" action="<?= BASE_URL ?>manager/users/unblock" class="d-inline">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-unlock"></i> Разблокировать профиль
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = $title ?? 'Просмотр замечания - Панель менеджера';
include __DIR__ . '/../admin_layout.php';
?>


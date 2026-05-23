<?php

/**
 * АДМИН-ПАНЕЛЬ - УПРАВЛЕНИЕ ПОЛЬЗОВАТЕЛЯМИ
 */

ob_start();
?>

<style>
@media (max-width: 767.98px) {
    .admin-users-table-wrapper {
        display: none;
    }
}
@media (min-width: 768px) {
    .admin-users-mobile-list {
        display: none;
    }
}
</style>

<div class="mt-4">
    <h2 class="mb-4">Управление пользователями</h2>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <!-- Десктоп: таблица -->
            <div class="table-responsive admin-users-table-wrapper">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Фото</th>
                            <th>Email</th>
                            <th>Пол</th>
                            <th>Возраст</th>
                            <th>Город</th>
                            <th>IP</th>
                            <th>Страна</th>
                            <th>Подтвержден</th>
                            <th>Роль</th>
                            <th>Статус</th>
                            <th>Дата регистрации</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td>
                                    <?php if (!empty($user['main_photo'])): ?>
                                        <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . $user['main_photo'] ?>"
                                            alt="Фото пользователя"
                                            class="rounded"
                                            style="width: 50px; height: 50px; object-fit: cover;"
                                            onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2750%27 height=%2750%27%3E%3Crect fill=%27%23ddd%27 width=%2750%27 height=%2750%27/%3E%3Ctext x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27 dy=%27.3em%27 fill=%27%23999%27%3EНет фото%3C/text%3E%3C/svg%3E';">
                                    <?php else: ?>
                                        <div class="bg-secondary rounded d-flex align-items-center justify-content-center text-white"
                                            style="width: 50px; height: 50px; font-size: 0.75rem;">
                                            Нет фото
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?= Helper::escape($user['email']) ?></td>
                                <td><?= $user['gender'] === 'male' ? 'М' : ($user['gender'] === 'female' ? 'Ж' : '-') ?></td>
                                <td><?= $user['age'] ?? '-' ?></td>
                                <td><?= Helper::escape($user['city'] ?? '-') ?></td>
                                <td>
                                    <small class="text-muted"><?= Helper::escape($user['registration_ip'] ?? '-') ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($user['registration_country'])): ?>
                                        <span class="badge bg-info"><?= Helper::escape($user['registration_country']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['email_verified']): ?>
                                        <span class="badge bg-success">Да</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Нет</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $currentRole = $user['role'] ?? 'user';
                                    $roleLabels = [
                                        'user' => ['label' => 'Пользователь', 'class' => 'secondary'],
                                        'manager' => ['label' => 'Менеджер', 'class' => 'info']
                                    ];
                                    $roleInfo = $roleLabels[$currentRole] ?? $roleLabels['user'];
                                    ?>
                                    <span class="badge bg-<?= $roleInfo['class'] ?>"><?= $roleInfo['label'] ?></span>
                                </td>
                                <td>
                                    <?php
                                    $isBlocked = ($user['profile_blocked'] ?? 0) == 1;
                                    if ($isBlocked):
                                    ?>
                                        <span class="badge bg-danger">Заблокирован</span>
                                        <?php if (!empty($user['admin_remark'])): ?>
                                            <br><small class="text-muted" title="<?= Helper::escape($user['admin_remark']) ?>">
                                                <i class="bi bi-exclamation-triangle"></i> Есть замечание
                                            </small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-success">Активен</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <div class="d-flex gap-2 align-items-center flex-wrap">
                                        <a href="<?= BASE_URL ?>profile/view?id=<?= $user['id'] ?>"
                                            class="btn btn-primary btn-sm"
                                            title="Просмотреть профиль"
                                            target="_blank">
                                            <i class="bi bi-person"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>admin/send-message?user_id=<?= $user['id'] ?>"
                                            class="btn btn-info btn-sm"
                                            title="Отправить сообщение">
                                            <i class="bi bi-send"></i>
                                        </a>
                                        <?php if ($isBlocked): ?>
                                            <?php if (!empty($user['admin_remark'])): ?>
                                                <button type="button"
                                                    class="btn btn-warning btn-sm"
                                                    title="Просмотреть замечание"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#viewRemarkModal<?= $user['id'] ?>">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            <?php endif; ?>
                                            <form method="POST" action="<?= BASE_URL ?>admin/users/unblock" class="d-inline">
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <button type="submit" class="btn btn-success btn-sm" title="Разблокировать профиль">
                                                    <i class="bi bi-unlock"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button type="button"
                                                class="btn btn-warning btn-sm"
                                                title="Добавить замечание и заблокировать"
                                                data-bs-toggle="modal"
                                                data-bs-target="#remarkModal<?= $user['id'] ?>">
                                                <i class="bi bi-exclamation-triangle"></i>
                                            </button>
                                        <?php endif; ?>
                                        <form method="POST" action="<?= BASE_URL ?>admin/users/update-role" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <div class="input-group input-group-sm" style="width: 150px;">
                                                <select name="role" class="form-select form-select-sm">
                                                    <option value="user" <?= $currentRole === 'user' ? 'selected' : '' ?>>Пользователь</option>
                                                    <option value="manager" <?= $currentRole === 'manager' ? 'selected' : '' ?>>Менеджер</option>
                                                </select>
                                                <button type="submit" class="btn btn-primary btn-sm" title="Сохранить">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                            </div>
                                        </form>
                                        <form method="POST" action="<?= BASE_URL ?>admin/users/delete" class="d-inline" onsubmit="return confirm('Вы уверены, что хотите удалить этого пользователя? Это действие нельзя отменить!');">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Удалить пользователя">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Модальное окно для замечания -->
                                    <div class="modal fade" id="remarkModal<?= $user['id'] ?>" tabindex="-1" aria-labelledby="remarkModalLabel<?= $user['id'] ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="remarkModalLabel<?= $user['id'] ?>">Замечание для пользователя</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                                                </div>
                                                <form method="POST" action="<?= BASE_URL ?>admin/users/set-remark">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                        <div class="mb-3">
                                                            <label for="remark_type<?= $user['id'] ?>" class="form-label">Тип замечания <span class="text-danger">*</span></label>
                                                            <select class="form-select" id="remark_type<?= $user['id'] ?>" name="remark_type" required>
                                                                <option value="">-- Выберите тип замечания --</option>
                                                                <option value="full_name">ФИО (Полное имя)</option>
                                                                <option value="about">О себе</option>
                                                                <option value="photo">Фотография</option>
                                                            </select>
                                                            <small class="text-muted">Выберите, какое поле профиля нужно исправить.</small>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="remark<?= $user['id'] ?>" class="form-label">Замечание</label>
                                                            <textarea class="form-control"
                                                                id="remark<?= $user['id'] ?>"
                                                                name="remark"
                                                                rows="5"
                                                                required
                                                                placeholder="Опишите, что нужно исправить в профиле пользователя"></textarea>
                                                            <small class="text-muted">Профиль будет заблокирован до исправления</small>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                                        <button type="submit" class="btn btn-warning">Заблокировать профиль</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Модальное окно для просмотра замечания -->
                                    <?php if ($isBlocked && !empty($user['admin_remark'])): ?>
                                        <div class="modal fade" id="viewRemarkModal<?= $user['id'] ?>" tabindex="-1" aria-labelledby="viewRemarkModalLabel<?= $user['id'] ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-warning">
                                                        <h5 class="modal-title" id="viewRemarkModalLabel<?= $user['id'] ?>">
                                                            <i class="bi bi-exclamation-triangle"></i> Замечание для пользователя
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><strong>Пользователь:</strong> <?= Helper::escape($user['email']) ?></p>
                                                        <div class="alert alert-warning">
                                                            <?php
                                                            $fieldNames = [
                                                                'full_name' => 'ФИО (Полное имя)',
                                                                'about' => 'О себе',
                                                                'photo' => 'Фотография'
                                                            ];
                                                            $fieldName = !empty($user['remark_type']) && isset($fieldNames[$user['remark_type']]) ? $fieldNames[$user['remark_type']] : null;
                                                            ?>
                                                            <?php if ($fieldName): ?>
                                                                <strong>Замечание по полю: <span class="text-danger"><?= Helper::escape($fieldName) ?></span></strong>
                                                            <?php else: ?>
                                                                <strong>Замечание:</strong>
                                                            <?php endif; ?>
                                                            <p class="mb-0 mt-2"><?= nl2br(Helper::escape($user['admin_remark'])) ?></p>
                                                        </div>
                                                        <p class="text-muted"><small>Профиль заблокирован до исправления указанных ошибок.</small></p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <a href="<?= BASE_URL ?>profile/view?id=<?= $user['id'] ?>"
                                                            class="btn btn-primary"
                                                            target="_blank">
                                                            <i class="bi bi-person"></i> Просмотреть профиль
                                                        </a>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                                                        <form method="POST" action="<?= BASE_URL ?>admin/users/unblock" class="d-inline">
                                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                            <button type="submit" class="btn btn-success">Разблокировать профиль</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Мобильная версия в виде карточек -->
            <div class="admin-users-mobile-list">
                <?php foreach ($users as $user): ?>
                    <?php
                    $currentRole = $user['role'] ?? 'user';
                    $roleLabels = [
                        'user' => ['label' => 'Пользователь', 'class' => 'secondary'],
                        'manager' => ['label' => 'Менеджер', 'class' => 'info']
                    ];
                    $roleInfo = $roleLabels[$currentRole] ?? $roleLabels['user'];
                    $isBlocked = ($user['profile_blocked'] ?? 0) == 1;
                    ?>
                    <div class="card mb-2">
                        <div class="card-body p-2">
                            <div class="d-flex">
                                <div class="me-2">
                                    <?php if (!empty($user['main_photo'])): ?>
                                        <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . $user['main_photo'] ?>"
                                            alt="Фото пользователя"
                                            class="rounded"
                                            style="width: 52px; height: 52px; object-fit: cover;"
                                            onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2752%27 height=%2752%27%3E%3Crect fill=%27%23ddd%27 width=%2752%27 height=%2752%27/%3E%3Ctext x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27 dy=%27.3em%27 fill=%27%23999%27%3EНет фото%3C/text%3E%3C/svg%3E';">
                                    <?php else: ?>
                                        <div class="bg-secondary rounded d-flex align-items-center justify-content-center text-white"
                                            style="width: 52px; height: 52px; font-size: 0.7rem;">
                                            Нет фото
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong><?= Helper::escape($user['email']) ?></strong>
                                            <div class="small text-muted">
                                                ID: <?= $user['id'] ?> • <?= $user['gender'] === 'male' ? 'М' : ($user['gender'] === 'female' ? 'Ж' : '-') ?>
                                                <?php if ($user['age']): ?> • <?= $user['age'] ?><?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-<?= $roleInfo['class'] ?> mb-1"><?= $roleInfo['label'] ?></span><br>
                                            <?php if ($isBlocked): ?>
                                                <span class="badge bg-danger">Заблокирован</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Активен</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        <?= Helper::escape($user['city'] ?? '-') ?> •
                                        <?= !empty($user['registration_country']) ? Helper::escape($user['registration_country']) : '-' ?>
                                        <br>
                                        <span title="IP регистрации"><?= Helper::escape($user['registration_ip'] ?? '-') ?></span> •
                                        с <?= date('d.m.Y', strtotime($user['created_at'])) ?>
                                    </div>
                                    <div class="mt-2 d-flex flex-wrap gap-1">
                                        <a href="<?= BASE_URL ?>profile/view?id=<?= $user['id'] ?>"
                                           class="btn btn-outline-primary btn-sm"
                                           title="Профиль"
                                           target="_blank">
                                            <i class="bi bi-person"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>admin/send-message?user_id=<?= $user['id'] ?>"
                                           class="btn btn-outline-info btn-sm"
                                           title="Сообщение">
                                            <i class="bi bi-send"></i>
                                        </a>
                                        <?php if ($isBlocked): ?>
                                            <?php if (!empty($user['admin_remark'])): ?>
                                                <button type="button"
                                                    class="btn btn-outline-warning btn-sm"
                                                    title="Замечание"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#viewRemarkModal<?= $user['id'] ?>">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            <?php endif; ?>
                                            <form method="POST" action="<?= BASE_URL ?>admin/users/unblock" class="d-inline">
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <button type="submit" class="btn btn-outline-success btn-sm" title="Разблокировать">
                                                    <i class="bi bi-unlock"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button type="button"
                                                class="btn btn-outline-warning btn-sm"
                                                title="Замечание + блок"
                                                data-bs-toggle="modal"
                                                data-bs-target="#remarkModal<?= $user['id'] ?>">
                                                <i class="bi bi-exclamation-triangle"></i>
                                            </button>
                                        <?php endif; ?>
                                        <form method="POST" action="<?= BASE_URL ?>admin/users/delete" class="d-inline" onsubmit="return confirm('Вы уверены, что хотите удалить этого пользователя? Это действие нельзя отменить!');">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Удалить">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Управление пользователями - Админ-панель';
include __DIR__ . '/../admin_layout.php';
?>

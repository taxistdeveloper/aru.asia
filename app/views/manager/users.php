<?php

/**
 * ПАНЕЛЬ МЕНЕДЖЕРА - УПРАВЛЕНИЕ ПОЛЬЗОВАТЕЛЯМИ
 */

ob_start();
?>

<style>
    /* Стили для адаптивной таблицы */
    .users-table-desktop {
        display: block;
    }

    .users-cards-mobile {
        display: none;
    }

    /* Мобильная версия - карточки */
    @media (max-width: 767px) {
        .users-table-desktop {
            display: none !important;
        }

        .users-cards-mobile {
            display: block;
        }

        .user-card-item {
            background: #ffffff;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .user-card-item:active {
            transform: scale(0.98);
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
        }

        .user-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        .user-card-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e2e8f0;
            flex-shrink: 0;
        }

        .user-card-name-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .user-card-name {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            word-break: break-word;
        }

        .user-card-email {
            font-size: 14px;
            color: #718096;
            word-break: break-word;
        }

        .user-card-id {
            font-size: 12px;
            color: #a0aec0;
            margin-top: 4px;
        }

        .user-card-info {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .user-card-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
        }

        .user-card-label {
            font-size: 14px;
            color: #718096;
            font-weight: 500;
        }

        .user-card-value {
            font-size: 14px;
            color: #2d3748;
            font-weight: 600;
            text-align: right;
        }

        .user-card-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .user-card-badges .badge {
            font-size: 12px;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
        }

        .user-card-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
        }

        .user-card-actions .btn {
            flex: 1;
            min-width: 80px;
            font-size: 12px;
            padding: 8px 12px;
        }

        .mobile-page-container {
            margin: 0;
            padding: 16px;
            background: #f8f9fa;
            min-height: calc(100vh - 70px);
        }

        .mobile-page-container h2 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #2d3748;
        }

        .mobile-page-container .alert {
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 14px;
            margin-bottom: 20px;
            border: none;
            line-height: 1.6;
        }

        .mobile-page-container .alert-warning {
            background-color: #feebc8;
            color: #c05621;
        }

        .mobile-page-container .alert-success {
            background-color: #c6f6d5;
            color: #22543d;
        }

        .mobile-page-container .alert-danger {
            background-color: #fed7d7;
            color: #742a2a;
        }

        .filter-buttons {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .filter-buttons .btn {
            flex: 1;
            min-width: 100px;
            font-size: 14px;
            padding: 10px 16px;
        }

    }

    /* Десктоп версия - таблица */
    @media (min-width: 768px) {
        .users-table-desktop {
            display: block;
        }

        .users-cards-mobile {
            display: none !important;
        }

        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }

        .table thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #2d3748;
            border-bottom: 2px solid #dee2e6;
            padding: 12px;
        }

        .table tbody td {
            padding: 12px;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #dee2e6;
        }

        .user-info-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-name-email {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            color: #2d3748;
            font-size: 14px;
        }

        .user-email-small {
            font-size: 12px;
            color: #718096;
        }

        .filter-buttons {
            margin-bottom: 20px;
        }

        .filter-buttons .btn {
            font-size: 14px;
            padding: 8px 20px;
        }
    }

    /* Стили для скрытых действий */
    .user-actions-extra {
        display: none;
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px solid #e2e8f0;
    }

    .user-actions-extra.show {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn-toggle-actions {
        font-size: 12px;
        padding: 6px 12px;
    }

    .user-card-actions-extra {
        display: none;
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px solid #e2e8f0;
        gap: 8px;
        flex-wrap: wrap;
    }

    .user-card-actions-extra.show {
        display: flex;
    }

    .user-card-info-hidden,
    .user-card-badges-hidden {
        display: none;
    }

    .user-card-info-hidden.show,
    .user-card-badges-hidden.show {
        display: block;
    }

    /* Стили для пагинации */
    .pagination {
        margin: 20px 0;
    }

    .pagination .page-link {
        color: #495057;
        border-color: #dee2e6;
        padding: 8px 12px;
    }

    .pagination .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
    }

    .pagination .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        cursor: auto;
        background-color: #fff;
        border-color: #dee2e6;
    }

    .pagination .page-link:hover:not(.disabled) {
        background-color: #e9ecef;
        border-color: #dee2e6;
        color: #0d6efd;
    }

    /* Мобильная версия пагинации */
    @media (max-width: 767px) {
        .pagination {
            flex-wrap: wrap;
            justify-content: center;
        }

        .pagination .page-item {
            margin: 2px;
        }

        .pagination .page-link {
            padding: 6px 10px;
            font-size: 14px;
            min-width: 40px;
        }
    }
</style>

<div class="mt-4 mobile-page-container">
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

    <!-- Поиск по IP (кто регистрировался с этого IP) -->
    <div class="mb-4">
        <form method="get" action="<?= BASE_URL ?>manager/users" class="row g-2 align-items-end">
            <input type="hidden" name="filter" value="<?= Helper::escape($filter ?? 'all') ?>">
            <div class="col-auto">
                <label for="search_ip" class="form-label mb-0">Поиск по IP</label>
                <input type="text"
                    id="search_ip"
                    name="search_ip"
                    class="form-control"
                    placeholder="Например: 192.168.1.1"
                    value="<?= isset($searchIp) && $searchIp !== '' ? Helper::escape($searchIp) : '' ?>"
                    style="min-width: 180px;">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Найти
                </button>
            </div>
            <?php if (isset($searchIp) && $searchIp !== ''): ?>
                <div class="col-auto">
                    <a href="<?= BASE_URL ?>manager/users?filter=<?= $filter ?? 'all' ?>" class="btn btn-outline-secondary">Сбросить</a>
                </div>
            <?php endif; ?>
        </form>
        <?php if (isset($searchIp) && $searchIp !== ''): ?>
            <p class="text-muted mt-2 mb-0 small">
                <i class="bi bi-info-circle"></i> Найдено пользователей с IP <strong><?= Helper::escape($searchIp) ?></strong>: <?= $totalUsers ?? 0 ?>.
                <?php if ($totalUsers > 0): ?>Ниже — все анкеты, зарегистрированные с этого IP.<?php endif; ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- Фильтры -->
    <div class="mb-3">
        <div class="btn-group filter-buttons" role="group" aria-label="Фильтры пользователей">
            <a href="<?= BASE_URL ?>manager/users?filter=all<?= (isset($searchIp) && $searchIp !== '') ? '&search_ip=' . urlencode($searchIp) : '' ?>"
                class="btn <?= ($filter ?? 'all') === 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">
                <i class="bi bi-people"></i> Все
            </a>
            <a href="<?= BASE_URL ?>manager/users?filter=new<?= (isset($searchIp) && $searchIp !== '') ? '&search_ip=' . urlencode($searchIp) : '' ?>"
                class="btn <?= ($filter ?? 'all') === 'new' ? 'btn-primary' : 'btn-outline-primary' ?>">
                <i class="bi bi-star"></i> Новые
            </a>
        </div>
    </div>

    <!-- Десктоп версия - таблица -->
    <div class="users-table-desktop">
        <div class="card">
            <div class="card-body">
                <?php if (empty($users)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-people" style="font-size: 48px; color: #ccc;"></i>
                        <p class="text-muted mt-3">Пользователи не найдены</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Фото / Имя</th>
                                    <th>IP</th>
                                    <th>Страна</th>
                                    <th>Подтвержден</th>
                                    <th>Роль</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <?php
                                    $currentRole = $user['role'] ?? 'user';
                                    $roleLabels = [
                                        'user' => ['label' => 'Пользователь', 'class' => 'secondary'],
                                        'manager' => ['label' => 'Менеджер', 'class' => 'info']
                                    ];
                                    $roleInfo = $roleLabels[$currentRole] ?? $roleLabels['user'];
                                    $isBlocked = ($user['profile_blocked'] ?? 0) == 1;
                                    $displayName = !empty($user['full_name'])
                                        ? Helper::escape($user['full_name'])
                                        : Helper::escape($user['email']);
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="user-info-cell">
                                                <?php if (!empty($user['first_photo'])): ?>
                                                    <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . $user['first_photo'] ?>"
                                                        alt="<?= $displayName ?>"
                                                        class="user-avatar">
                                                <?php else: ?>
                                                    <div class="user-avatar d-flex align-items-center justify-content-center bg-secondary text-white" style="width: 50px; height: 50px; border-radius: 50%; border: 2px solid #dee2e6;">
                                                        <i class="bi bi-person" style="font-size: 24px;"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="user-name-email">
                                                    <span class="user-name"><?= $displayName ?></span>
                                                    <span class="user-email-small"><?= Helper::escape($user['email']) ?></span>
                                                </div>
                                            </div>
                                        </td>
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
                                            <span class="badge bg-<?= $roleInfo['class'] ?>"><?= $roleInfo['label'] ?></span>
                                        </td>
                                        <td>
                                            <?php if ($isBlocked): ?>
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
                                        <td>
                                            <div class="d-flex flex-column gap-2">
                                                <!-- Основные действия (всегда видимые) -->
                                                <div class="d-flex gap-2 align-items-center flex-wrap">
                                                    <a href="<?= BASE_URL ?>profile/view?id=<?= $user['id'] ?>"
                                                        class="btn btn-primary btn-sm"
                                                        title="Просмотреть профиль"
                                                        target="_blank">
                                                        <i class="bi bi-person"></i>
                                                    </a>
                                                    <a href="<?= BASE_URL ?>manager/send-message?user_id=<?= $user['id'] ?>"
                                                        class="btn btn-info btn-sm"
                                                        title="Отправить сообщение">
                                                        <i class="bi bi-send"></i>
                                                    </a>
                                                    <button type="button"
                                                        class="btn btn-secondary btn-sm btn-toggle-actions"
                                                        onclick="toggleUserActions(<?= $user['id'] ?>)"
                                                        data-user-id="<?= $user['id'] ?>"
                                                        title="Показать дополнительные действия">
                                                        <i class="bi bi-chevron-down" id="icon-<?= $user['id'] ?>"></i>
                                                    </button>
                                                </div>

                                                <!-- Дополнительные действия (скрытые) -->
                                                <div class="user-actions-extra" id="actions-<?= $user['id'] ?>">
                                                    <?php if ($isBlocked): ?>
                                                        <?php if (!empty($user['admin_remark'])): ?>
                                                            <a href="<?= BASE_URL ?>manager/users/view-remark?user_id=<?= $user['id'] ?>"
                                                                class="btn btn-warning btn-sm"
                                                                title="Просмотреть замечание">
                                                                <i class="bi bi-eye"></i> Замечание
                                                            </a>
                                                        <?php endif; ?>
                                                        <form method="POST" action="<?= BASE_URL ?>manager/users/unblock" class="d-inline">
                                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                            <button type="submit" class="btn btn-success btn-sm" title="Разблокировать профиль">
                                                                <i class="bi bi-unlock"></i> Разблокировать
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <a href="<?= BASE_URL ?>manager/users/add-remark?user_id=<?= $user['id'] ?>"
                                                            class="btn btn-warning btn-sm"
                                                            title="Добавить замечание и заблокировать">
                                                            <i class="bi bi-exclamation-triangle"></i> Заблокировать
                                                        </a>
                                                    <?php endif; ?>
                                                    <form method="POST" action="<?= BASE_URL ?>manager/users/delete" class="d-inline" onsubmit="return confirm('Вы уверены, что хотите удалить этого пользователя? Это действие нельзя отменить!');">
                                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm" title="Удалить пользователя">
                                                            <i class="bi bi-trash"></i> Удалить
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Мобильная версия - карточки -->
    <div class="users-cards-mobile">
        <?php if (empty($users)): ?>
            <div class="text-center py-5">
                <i class="bi bi-people" style="font-size: 48px; color: #ccc;"></i>
                <p class="text-muted mt-3">Пользователи не найдены</p>
            </div>
        <?php else: ?>
            <?php foreach ($users as $user): ?>
                <?php
                $currentRole = $user['role'] ?? 'user';
                $roleLabels = [
                    'user' => ['label' => 'Пользователь', 'class' => 'secondary'],
                    'manager' => ['label' => 'Менеджер', 'class' => 'info']
                ];
                $roleInfo = $roleLabels[$currentRole] ?? $roleLabels['user'];
                $isBlocked = ($user['profile_blocked'] ?? 0) == 1;
                $displayName = !empty($user['full_name'])
                    ? Helper::escape($user['full_name'])
                    : Helper::escape($user['email']);
                ?>
                <div class="user-card-item">
                    <div class="user-card-header">
                        <?php if (!empty($user['first_photo'])): ?>
                            <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . $user['first_photo'] ?>"
                                alt="<?= $displayName ?>"
                                class="user-card-avatar">
                        <?php else: ?>
                            <div class="user-card-avatar d-flex align-items-center justify-content-center bg-secondary text-white" style="width: 60px; height: 60px; border-radius: 50%; border: 2px solid #e2e8f0;">
                                <i class="bi bi-person" style="font-size: 28px;"></i>
                            </div>
                        <?php endif; ?>
                        <div class="user-card-name-info">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <div class="user-card-name"><?= $displayName ?></div>
                                    <div class="user-card-email">
                                        <i class="bi bi-envelope"></i> <?= Helper::escape($user['email']) ?>
                                    </div>
                                    <div class="user-card-id">ID: <?= $user['id'] ?></div>
                                </div>
                                <span class="badge bg-<?= $roleInfo['class'] ?>"><?= $roleInfo['label'] ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="user-card-info user-card-info-hidden" id="info-mobile-<?= $user['id'] ?>">
                        <div class="user-card-row">
                            <span class="user-card-label">
                                <i class="bi bi-info-circle"></i> Статус:
                            </span>
                            <span class="user-card-value">
                                <?php if ($isBlocked): ?>
                                    <span class="badge bg-danger">Заблокирован</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Активен</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="user-card-row">
                            <span class="user-card-label">
                                <i class="bi bi-globe"></i> IP:
                            </span>
                            <span class="user-card-value">
                                <small class="text-muted"><?= Helper::escape($user['registration_ip'] ?? '-') ?></small>
                            </span>
                        </div>
                        <div class="user-card-row">
                            <span class="user-card-label">
                                <i class="bi bi-flag"></i> Страна:
                            </span>
                            <span class="user-card-value">
                                <?php if (!empty($user['registration_country'])): ?>
                                    <span class="badge bg-info"><?= Helper::escape($user['registration_country']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <div class="user-card-badges user-card-badges-hidden" id="badges-mobile-<?= $user['id'] ?>">
                        <?php if ($user['email_verified']): ?>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Подтвержден
                            </span>
                        <?php else: ?>
                            <span class="badge bg-warning">
                                <i class="bi bi-exclamation-circle"></i> Не подтвержден
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="user-card-actions">
                        <!-- Основные действия (всегда видимые) -->
                        <a href="<?= BASE_URL ?>profile/view?id=<?= $user['id'] ?>"
                            class="btn btn-primary btn-sm"
                            title="Просмотреть профиль"
                            target="_blank">
                            <i class="bi bi-person"></i>
                        </a>
                        <a href="<?= BASE_URL ?>manager/send-message?user_id=<?= $user['id'] ?>"
                            class="btn btn-info btn-sm"
                            title="Отправить сообщение">
                            <i class="bi bi-send"></i>
                        </a>
                        <button type="button"
                            class="btn btn-secondary btn-sm btn-toggle-actions flex-fill"
                            onclick="toggleUserActions(<?= $user['id'] ?>)"
                            data-user-id="<?= $user['id'] ?>"
                            title="Показать дополнительные действия">
                            <i class="bi bi-chevron-down" id="icon-mobile-<?= $user['id'] ?>"></i> Показать
                        </button>

                        <!-- Дополнительные действия (скрытые) -->
                        <div class="user-card-actions-extra" id="actions-mobile-<?= $user['id'] ?>">
                            <?php if ($isBlocked): ?>
                                <?php if (!empty($user['admin_remark'])): ?>
                                    <a href="<?= BASE_URL ?>manager/users/view-remark?user_id=<?= $user['id'] ?>"
                                        class="btn btn-warning btn-sm flex-fill"
                                        title="Просмотреть замечание">
                                        <i class="bi bi-eye"></i> Замечание
                                    </a>
                                <?php endif; ?>
                                <form method="POST" action="<?= BASE_URL ?>manager/users/unblock" class="d-inline flex-fill">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <button type="submit" class="btn btn-success btn-sm w-100" title="Разблокировать профиль">
                                        <i class="bi bi-unlock"></i> Разблокировать
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="<?= BASE_URL ?>manager/users/add-remark?user_id=<?= $user['id'] ?>"
                                    class="btn btn-warning btn-sm flex-fill"
                                    title="Добавить замечание и заблокировать">
                                    <i class="bi bi-exclamation-triangle"></i> Заблокировать
                                </a>
                            <?php endif; ?>
                            <form method="POST" action="<?= BASE_URL ?>manager/users/delete" class="d-inline flex-fill" onsubmit="return confirm('Вы уверены, что хотите удалить этого пользователя? Это действие нельзя отменить!');">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm w-100" title="Удалить пользователя">
                                    <i class="bi bi-trash"></i> Удалить
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Пагинация -->
    <?php
    $paginationBase = BASE_URL . 'manager/users?filter=' . $filter;
    if (!empty($searchIp)) {
        $paginationBase .= '&search_ip=' . urlencode($searchIp);
    }
    $paginationBase .= '&page=';
    ?>
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Навигация по страницам" class="mt-4">
            <ul class="pagination justify-content-center">
                <!-- Кнопка "Предыдущая" -->
                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link"
                        href="<?= $paginationBase . ($currentPage - 1) ?>"
                        <?= $currentPage <= 1 ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                        <i class="bi bi-chevron-left"></i> Предыдущая
                    </a>
                </li>

                <!-- Номера страниц -->
                <?php
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);

                if ($startPage > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= $paginationBase ?>1">1</a>
                    </li>
                    <?php if ($startPage > 2): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                        <a class="page-link" href="<?= $paginationBase . $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= $paginationBase . $totalPages ?>"><?= $totalPages ?></a>
                    </li>
                <?php endif; ?>

                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link"
                        href="<?= $paginationBase . ($currentPage + 1) ?>"
                        <?= $currentPage >= $totalPages ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                        Следующая <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Информация о пагинации -->
        <div class="text-center text-muted mt-2 mb-4">
            <small>
                Показано <?= count($users) ?> из <?= $totalUsers ?> пользователей
                (страница <?= $currentPage ?> из <?= $totalPages ?>)
            </small>
        </div>
    <?php endif; ?>
</div>

<script>
    function toggleUserActions(userId) {
        // Десктоп версия
        const desktopActions = document.getElementById('actions-' + userId);
        const desktopIcon = document.getElementById('icon-' + userId);

        // Мобильная версия
        const mobileActions = document.getElementById('actions-mobile-' + userId);
        const mobileIcon = document.getElementById('icon-mobile-' + userId);
        const mobileInfo = document.getElementById('info-mobile-' + userId);
        const mobileBadges = document.getElementById('badges-mobile-' + userId);

        if (desktopActions) {
            desktopActions.classList.toggle('show');
            if (desktopIcon) {
                if (desktopActions.classList.contains('show')) {
                    desktopIcon.classList.remove('bi-chevron-down');
                    desktopIcon.classList.add('bi-chevron-up');
                } else {
                    desktopIcon.classList.remove('bi-chevron-up');
                    desktopIcon.classList.add('bi-chevron-down');
                }
            }
        }

        if (mobileActions) {
            const isShowing = mobileActions.classList.contains('show');
            mobileActions.classList.toggle('show');

            // Показываем/скрываем статус и бейджи
            if (mobileInfo) {
                if (isShowing) {
                    mobileInfo.classList.remove('show');
                } else {
                    mobileInfo.classList.add('show');
                }
            }

            if (mobileBadges) {
                if (isShowing) {
                    mobileBadges.classList.remove('show');
                } else {
                    mobileBadges.classList.add('show');
                }
            }

            if (mobileIcon) {
                if (mobileActions.classList.contains('show')) {
                    mobileIcon.classList.remove('bi-chevron-down');
                    mobileIcon.classList.add('bi-chevron-up');
                } else {
                    mobileIcon.classList.remove('bi-chevron-up');
                    mobileIcon.classList.add('bi-chevron-down');
                }
            }
        }
    }
</script>

<?php
$content = ob_get_clean();
$title = 'Пользователи - Панель менеджера';
include __DIR__ . '/../admin_layout.php';
?>
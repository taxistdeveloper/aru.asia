<?php

/**
 * АДМИН-ПАНЕЛЬ - УПРАВЛЕНИЕ РЕКЛАМОЙ
 */

ob_start();
?>

<style>
    /* Стили для модальных окон */
    body.modal-open {
        overflow: hidden !important;
        padding-right: 0 !important;
    }

    /* Мобильная адаптация для страницы управления рекламой */
    @media (max-width: 767.98px) {
        .ads-mobile-container {
            padding: 0;
        }

        .ads-mobile-container h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #2d3748;
            padding: 0 4px;
        }

        .ads-mobile-container .alert-info {
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
            background-color: #e6f3ff;
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .ads-mobile-container .alert-info h5 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #1e3c72;
        }

        .ads-mobile-container .alert-info ul {
            margin-bottom: 0;
            padding-left: 20px;
        }

        .ads-mobile-container .alert-info li {
            font-size: 14px;
            margin-bottom: 6px;
            color: #2d3748;
            line-height: 1.5;
        }

        /* Десктопная таблица - скрываем на мобильных */
        .ads-desktop-table {
            display: none;
        }

        /* Мобильные карточки */
        .ads-mobile-cards {
            display: block;
        }

        .ad-card-mobile {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            margin-bottom: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }

        .ad-card-mobile:active {
            transform: scale(0.98);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .ad-card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            user-select: none;
            transition: background 0.3s ease;
        }

        .ad-card-header:hover {
            background: linear-gradient(135deg, #5568d3 0%, #653d91 100%);
        }

        .ad-card-header .expand-icon {
            transition: transform 0.3s ease;
            font-size: 18px;
            margin-left: 10px;
        }

        .ad-card-mobile.expanded .ad-card-header .expand-icon {
            transform: rotate(180deg);
        }

        .ad-card-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease-out, padding 0.3s ease;
            padding: 0 16px;
        }

        .ad-card-mobile.expanded .ad-card-content {
            max-height: 2000px;
            padding: 16px;
            transition: max-height 0.5s ease-in, padding 0.3s ease;
        }

        .ad-card-header .ad-id {
            font-weight: 700;
            font-size: 16px;
        }

        .ad-card-header .ad-status {
            font-size: 12px;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .ad-card-body {
            padding: 16px;
            display: none;
        }

        .ad-card-mobile.expanded .ad-card-body {
            display: block;
        }

        .ad-info-row {
            display: flex;
            align-items: flex-start;
            padding: 12px 0;
            border-bottom: 1px solid #f1f3f5;
        }

        .ad-info-row:last-child {
            border-bottom: none;
        }

        .ad-info-label {
            font-weight: 700;
            font-size: 13px;
            color: #718096;
            min-width: 100px;
            margin-right: 12px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .ad-info-value {
            flex: 1;
            font-size: 15px;
            color: #2d3748;
            font-weight: 500;
            word-break: break-word;
        }

        .ad-info-value .badge {
            font-size: 12px;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
        }

        .ad-actions-mobile {
            padding: 0 16px 16px;
            background-color: #f8f9fa;
            border-top: 1px solid #e2e8f0;
            display: none;
            flex-direction: column;
            gap: 10px;
        }

        .ad-card-mobile.expanded .ad-actions-mobile {
            display: flex;
            padding: 16px;
        }

        .ad-actions-mobile .btn {
            width: 100%;
            padding: 12px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 10px;
            border: none;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .ad-actions-mobile .btn:active {
            transform: scale(0.97);
        }

        .ad-actions-mobile .btn i {
            font-size: 18px;
        }

        /* Модальное окно для мобильных */
        .ads-mobile-container .modal-dialog {
            margin: 0.5rem;
        }

        .ads-mobile-container .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .ads-mobile-container .modal-header {
            border-bottom: 2px solid #e2e8f0;
            padding: 16px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 16px 16px 0 0;
        }

        .ads-mobile-container .modal-title {
            font-size: 18px;
            font-weight: 700;
        }

        .ads-mobile-container .btn-close {
            filter: invert(1);
            opacity: 0.9;
        }

        .ads-mobile-container .modal-body {
            padding: 20px;
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }

        .ads-mobile-container .modal-body .mb-3 {
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid #f1f3f5;
        }

        .ads-mobile-container .modal-body .mb-3:last-child {
            border-bottom: none;
        }

        .ads-mobile-container .modal-body strong {
            display: block;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #718096;
            margin-bottom: 6px;
            font-weight: 700;
        }

        .ads-mobile-container .modal-body .ad-detail-value {
            font-size: 15px;
            color: #2d3748;
            font-weight: 500;
            word-break: break-word;
        }

        .ads-mobile-container .modal-body img {
            border-radius: 12px;
            margin-top: 8px;
            max-width: 100%;
            height: auto;
        }

        .ads-mobile-container .modal-footer {
            padding: 16px 20px;
            border-top: 2px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .ads-mobile-container .modal-footer form {
            width: 100%;
            margin: 0;
        }

        .ads-mobile-container .modal-footer .btn {
            width: 100%;
            padding: 12px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 10px;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        /* Улучшенные анимации для модальных окон */
        .ads-mobile-container .modal.fade .modal-dialog {
            transition: transform 0.3s ease-out;
            transform: translate(0, -50px);
        }

        .ads-mobile-container .modal.show .modal-dialog {
            transform: translate(0, 0);
        }

        .ads-mobile-container .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5);
        }

        .ads-mobile-container .modal-backdrop.fade {
            opacity: 0;
        }

        .ads-mobile-container .modal-backdrop.show {
            opacity: 1;
        }
    }

    /* Десктопная версия */
    @media (min-width: 768px) {
        .ads-desktop-table {
            display: table;
        }

        .ads-mobile-cards {
            display: none;
        }

        /* Анимации для десктопа */
        .ads-mobile-container .modal.fade .modal-dialog {
            transition: transform 0.3s ease-out;
            transform: translate(0, -50px);
        }

        .ads-mobile-container .modal.show .modal-dialog {
            transform: translate(0, 0);
        }

        /* Десктопная версия для modal-footer */
        .ads-mobile-container .modal-footer {
            flex-direction: row;
            justify-content: flex-end;
            gap: 10px;
        }

        .ads-mobile-container .modal-footer form {
            width: auto;
            margin: 0;
        }

        .ads-mobile-container .modal-footer .btn {
            width: auto;
            padding: 8px 16px;
        }
    }
</style>

<div class="mt-4 ads-mobile-container">
    <h2 class="mb-4">Управление рекламой</h2>

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

    <div class="alert alert-info">
        <h5>Требования к рекламным баннерам:</h5>
        <ul>
            <li>Размер изображения: <?= AD_IMAGE_WIDTH ?>x<?= AD_IMAGE_HEIGHT ?> пикселей</li>
            <li>Формат: JPG, PNG</li>
            <li>Максимальный размер файла: 2MB</li>
        </ul>
    </div>

    <?php
    // Определяем префикс маршрута в зависимости от роли
    $routePrefix = Helper::isAdminLoggedIn() ? 'admin' : 'manager';
    ?>

    <!-- Десктопная таблица -->
    <div class="card">
        <div class="card-body">
            <table class="table table-striped ads-desktop-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Рекламодатель</th>
                        <th>Пользователь</th>
                        <th>Email</th>
                        <th>Страна</th>
                        <th>Город</th>
                        <th>Период</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ads as $ad): ?>
                        <tr>
                            <td><?= $ad['id'] ?></td>
                            <td><?= Helper::escape($ad['advertiser_name']) ?></td>
                            <td>
                                <?php if (!empty($ad['full_name'])): ?>
                                    <div>
                                        <strong><?= Helper::escape($ad['full_name']) ?></strong>
                                        <?php if (!empty($ad['age'])): ?>
                                            <span class="text-muted">(<?= $ad['age'] ?> лет)</span>
                                        <?php endif; ?>
                                        <?php if (!empty($ad['gender'])): ?>
                                            <span class="badge bg-<?= $ad['gender'] === 'male' ? 'primary' : 'danger' ?> ms-1">
                                                <?= $ad['gender'] === 'male' ? 'М' : 'Ж' ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= Helper::escape($ad['advertiser_email']) ?></td>
                            <td><?= Helper::escape($ad['country']) ?></td>
                            <td><?= !empty($ad['city']) ? Helper::escape($ad['city']) : '-' ?></td>
                            <td>
                                <?= date('d.m.Y', strtotime($ad['start_date'])) ?> -
                                <?= date('d.m.Y', strtotime($ad['end_date'])) ?>
                            </td>
                            <td>
                                <?php
                                $statusBadgeClass = 'secondary';
                                $statusText = 'Неизвестно';
                                if ($ad['status'] === 'active') {
                                    $statusBadgeClass = 'success';
                                    $statusText = 'Активна';
                                } elseif ($ad['status'] === 'pending') {
                                    $statusBadgeClass = 'warning';
                                    $statusText = 'На модерации';
                                } elseif ($ad['status'] === 'rejected') {
                                    $statusBadgeClass = 'danger';
                                    $statusText = 'Отказано';
                                } elseif ($ad['status'] === 'expired') {
                                    $statusBadgeClass = 'secondary';
                                    $statusText = 'Истекла';
                                }
                                ?>
                                <span class="badge bg-<?= $statusBadgeClass ?>"><?= $statusText ?></span>
                            </td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <?php if ($ad['status'] === 'pending'): ?>
                                        <button type="button"
                                            class="btn btn-sm btn-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#moderateModal<?= $ad['id'] ?>">
                                            <i class="bi bi-check-circle"></i> Модерировать
                                        </button>
                                    <?php else: ?>
                                        <form method="POST" action="<?= BASE_URL ?><?= $routePrefix ?>/ads/delete" class="d-inline" onsubmit="return confirm('Вы уверены, что хотите удалить эту рекламу? Это действие нельзя отменить!');">
                                            <input type="hidden" name="ad_id" value="<?= $ad['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i> Удалить
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Мобильные карточки -->
    <div class="ads-mobile-cards">
        <?php foreach ($ads as $ad): ?>
            <div class="ad-card-mobile" id="adCard<?= $ad['id'] ?>">
                <div class="ad-card-header" onclick="toggleAdCard(<?= $ad['id'] ?>)">
                    <span class="ad-id">
                        <?php if (!empty($ad['full_name'])): ?>
                            <?= Helper::escape($ad['full_name']) ?>
                        <?php else: ?>
                            <?= Helper::escape($ad['advertiser_name']) ?>
                        <?php endif; ?>
                    </span>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <?php
                        $statusBadgeClass = 'secondary';
                        $statusText = 'Неизвестно';
                        if ($ad['status'] === 'active') {
                            $statusBadgeClass = 'success';
                            $statusText = 'Активна';
                        } elseif ($ad['status'] === 'pending') {
                            $statusBadgeClass = 'warning';
                            $statusText = 'На модерации';
                        } elseif ($ad['status'] === 'rejected') {
                            $statusBadgeClass = 'danger';
                            $statusText = 'Отказано';
                        } elseif ($ad['status'] === 'expired') {
                            $statusBadgeClass = 'secondary';
                            $statusText = 'Истекла';
                        }
                        ?>
                        <span class="badge bg-<?= $statusBadgeClass ?> ad-status"><?= $statusText ?></span>
                        <i class="bi bi-chevron-down expand-icon"></i>
                    </div>
                </div>
                <div class="ad-card-content">
                    <div class="ad-card-body">
                        <div class="ad-info-row">
                            <span class="ad-info-label">
                                <i class="bi bi-person"></i> Рекламодатель
                            </span>
                            <span class="ad-info-value"><?= Helper::escape($ad['advertiser_name']) ?></span>
                        </div>
                        <div class="ad-info-row">
                            <span class="ad-info-label">
                                <i class="bi bi-person"></i> Пользователь
                            </span>
                            <span class="ad-info-value">
                                <?php if (!empty($ad['full_name'])): ?>
                                    <strong><?= Helper::escape($ad['full_name']) ?></strong>
                                    <?php if (!empty($ad['age'])): ?>
                                        <span class="text-muted">(<?= $ad['age'] ?> лет)</span>
                                    <?php endif; ?>
                                    <?php if (!empty($ad['gender'])): ?>
                                        <span class="badge bg-<?= $ad['gender'] === 'male' ? 'primary' : 'danger' ?> ms-1">
                                            <?= $ad['gender'] === 'male' ? 'М' : 'Ж' ?>
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="ad-info-row">
                            <span class="ad-info-label">
                                <i class="bi bi-envelope"></i> Email
                            </span>
                            <span class="ad-info-value"><?= Helper::escape($ad['advertiser_email']) ?></span>
                        </div>
                        <div class="ad-info-row">
                            <span class="ad-info-label">
                                <i class="bi bi-geo-alt"></i> Страна
                            </span>
                            <span class="ad-info-value"><?= Helper::escape($ad['country']) ?></span>
                        </div>
                        <div class="ad-info-row">
                            <span class="ad-info-label">
                                <i class="bi bi-building"></i> Город
                            </span>
                            <span class="ad-info-value"><?= !empty($ad['city']) ? Helper::escape($ad['city']) : '-' ?></span>
                        </div>
                        <div class="ad-info-row">
                            <span class="ad-info-label">
                                <i class="bi bi-calendar-range"></i> Период
                            </span>
                            <span class="ad-info-value">
                                <?= date('d.m.Y', strtotime($ad['start_date'])) ?> - <?= date('d.m.Y', strtotime($ad['end_date'])) ?>
                            </span>
                        </div>
                        <?php if ($ad['click_url']): ?>
                            <div class="ad-info-row">
                                <span class="ad-info-label">
                                    <i class="bi bi-link-45deg"></i> Ссылка
                                </span>
                                <span class="ad-info-value">
                                    <a href="<?= Helper::escape($ad['click_url']) ?>" target="_blank" class="text-break" style="color: #667eea; word-break: break-all;">
                                        <?= Helper::escape($ad['click_url']) ?>
                                    </a>
                                </span>
                            </div>
                        <?php endif; ?>
                        <?php if ($ad['image_path']): ?>
                            <div class="ad-info-row" style="flex-direction: column; align-items: flex-start;">
                                <span class="ad-info-label" style="margin-bottom: 8px;">
                                    <i class="bi bi-image"></i> Баннер (как на сайте)
                                </span>
                                <img src="<?= BASE_URL . UPLOAD_DIR . 'ads/' . $ad['image_path'] ?>"
                                    alt="<?= Helper::escape($ad['advertiser_name']) ?>"
                                    class="img-fluid"
                                    style="max-width: 100%; border-radius: 8px; border: 1px solid #ddd;">
                            </div>
                        <?php endif; ?>
                        <div class="ad-info-row">
                            <span class="ad-info-label">
                                <i class="bi bi-calendar"></i> Создана
                            </span>
                            <span class="ad-info-value"><?= date('d.m.Y H:i', strtotime($ad['created_at'])) ?></span>
                        </div>
                    </div>
                    <div class="ad-actions-mobile">
                        <?php if ($ad['status'] === 'pending'): ?>
                            <button type="button"
                                class="btn btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#moderateModal<?= $ad['id'] ?>"
                                onclick="event.stopPropagation();">
                                <i class="bi bi-check-circle"></i>
                                Модерировать
                            </button>
                        <?php else: ?>
                            <form method="POST" action="<?= BASE_URL ?><?= $routePrefix ?>/ads/delete" onsubmit="return confirm('Вы уверены, что хотите удалить эту рекламу? Это действие нельзя отменить!');" onclick="event.stopPropagation();">
                                <input type="hidden" name="ad_id" value="<?= $ad['id'] ?>">
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-trash"></i>
                                    Удалить
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>
    </div>

    <!-- Модальные окна для отклонения (для всех устройств) -->
    <?php foreach ($ads as $ad): ?>
        <?php if ($ad['status'] === 'pending'): ?>
            <!-- Модальное окно для быстрого отклонения -->
            <div class="modal fade" id="rejectModal<?= $ad['id'] ?>" tabindex="-1" aria-labelledby="rejectModalLabel<?= $ad['id'] ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="rejectModalLabel<?= $ad['id'] ?>">
                                Отклонить рекламу #<?= $ad['id'] ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="<?= BASE_URL ?><?= $routePrefix ?>/ads/reject" id="rejectFormQuick<?= $ad['id'] ?>" onsubmit="return validateRejectionReasonQuick(<?= $ad['id'] ?>);">
                            <div class="modal-body">
                                <p>Рекламодатель: <strong><?= Helper::escape($ad['advertiser_name']) ?></strong></p>
                                <div class="mb-3">
                                    <label for="rejection_reason_quick<?= $ad['id'] ?>" class="form-label">
                                        <strong>Причина отказа (обязательно):</strong>
                                    </label>
                                    <textarea 
                                        class="form-control" 
                                        id="rejection_reason_quick<?= $ad['id'] ?>" 
                                        name="rejection_reason" 
                                        rows="4" 
                                        placeholder="Укажите причину отказа рекламы. Это сообщение будет отправлено рекламодателю."
                                        required
                                        style="min-height: 100px; resize: vertical;"></textarea>
                                    <small class="form-text text-muted">Это сообщение будет отправлено рекламодателю на email</small>
                                </div>
                                <input type="hidden" name="ad_id" value="<?= $ad['id'] ?>">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="bi bi-x-lg"></i> Отмена
                                </button>
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-x-circle"></i> Отклонить рекламу
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <!-- Модальные окна для модерации (для всех устройств) -->
    <?php foreach ($ads as $ad): ?>
        <?php if ($ad['status'] === 'pending'): ?>
            <div class="modal fade" id="moderateModal<?= $ad['id'] ?>" tabindex="-1" aria-labelledby="moderateModalLabel<?= $ad['id'] ?>" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="moderateModalLabel<?= $ad['id'] ?>">
                                Модерация рекламы #<?= $ad['id'] ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <strong>Рекламодатель:</strong>
                                <span class="ad-detail-value"><?= Helper::escape($ad['advertiser_name']) ?></span>
                            </div>
                            <?php if (!empty($ad['full_name'])): ?>
                            <div class="mb-3">
                                <strong>Пользователь:</strong>
                                <span class="ad-detail-value">
                                    <?= Helper::escape($ad['full_name']) ?>
                                    <?php if (!empty($ad['age'])): ?>
                                        (<?= $ad['age'] ?> лет)
                                    <?php endif; ?>
                                    <?php if (!empty($ad['gender'])): ?>
                                        <span class="badge bg-<?= $ad['gender'] === 'male' ? 'primary' : 'danger' ?> ms-2">
                                            <?= $ad['gender'] === 'male' ? 'Мужчина' : 'Женщина' ?>
                                        </span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <strong>Email:</strong>
                                <span class="ad-detail-value"><?= Helper::escape($ad['advertiser_email']) ?></span>
                            </div>
                            <div class="mb-3">
                                <strong>Страна:</strong>
                                <span class="ad-detail-value"><?= Helper::escape($ad['country']) ?></span>
                            </div>
                            <div class="mb-3">
                                <strong>Город:</strong>
                                <span class="ad-detail-value"><?= !empty($ad['city']) ? Helper::escape($ad['city']) : '-' ?></span>
                            </div>
                            <div class="mb-3">
                                <strong>Период показа:</strong>
                                <span class="ad-detail-value">
                                    <?= date('d.m.Y', strtotime($ad['start_date'])) ?> -
                                    <?= date('d.m.Y', strtotime($ad['end_date'])) ?>
                                </span>
                            </div>
                            <?php if ($ad['click_url']): ?>
                                <div class="mb-3">
                                    <strong>Ссылка:</strong>
                                    <div class="ad-detail-value mt-1">
                                        <a href="<?= Helper::escape($ad['click_url']) ?>" target="_blank" class="text-break">
                                            <?= Helper::escape($ad['click_url']) ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ($ad['image_path']): ?>
                                <div class="mb-3">
                                    <strong>Баннер (так он будет отображаться на сайте):</strong>
                                    <img src="<?= BASE_URL . UPLOAD_DIR . 'ads/' . $ad['image_path'] ?>"
                                        alt="<?= Helper::escape($ad['advertiser_name']) ?>"
                                        class="img-fluid mt-2"
                                        style="max-height: 300px; border-radius: 8px; border: 1px solid #ddd;">
                                </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <strong>Дата создания:</strong>
                                <span class="ad-detail-value"><?= date('d.m.Y H:i', strtotime($ad['created_at'])) ?></span>
                            </div>
                            <div class="mb-3">
                                <label for="rejection_reason<?= $ad['id'] ?>" class="form-label">
                                    <strong>Причина отказа (обязательно при отклонении):</strong>
                                </label>
                                <textarea 
                                    class="form-control" 
                                    id="rejection_reason<?= $ad['id'] ?>" 
                                    name="rejection_reason" 
                                    rows="4" 
                                    placeholder="Укажите причину отказа рекламы. Это сообщение будет отправлено рекламодателю."
                                    style="min-height: 100px; resize: vertical;"></textarea>
                                <small class="form-text text-muted">Это сообщение будет отправлено рекламодателю на email при отклонении рекламы</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <form method="POST" action="<?= BASE_URL ?><?= $routePrefix ?>/ads/reject" id="rejectForm<?= $ad['id'] ?>" onsubmit="return validateRejectionReason(<?= $ad['id'] ?>);">
                                <input type="hidden" name="ad_id" value="<?= $ad['id'] ?>">
                                <input type="hidden" name="rejection_reason" id="rejection_reason_hidden<?= $ad['id'] ?>">
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-x-circle"></i> Отклонить
                                </button>
                            </form>
                            <form method="POST" action="<?= BASE_URL ?><?= $routePrefix ?>/ads/approve">
                                <input type="hidden" name="ad_id" value="<?= $ad['id'] ?>">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Одобрить
                                </button>
                            </form>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-lg"></i> Закрыть
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<script>
    // Функция для раскрытия/закрытия карточек рекламы
    function toggleAdCard(adId) {
        const card = document.getElementById('adCard' + adId);
        if (card) {
            card.classList.toggle('expanded');
        }
    }

    // Функция для валидации причины отказа перед отправкой формы (в модальном окне модерации)
    function validateRejectionReason(adId) {
        const reasonTextarea = document.getElementById('rejection_reason' + adId);
        const reasonHidden = document.getElementById('rejection_reason_hidden' + adId);
        const reason = reasonTextarea ? reasonTextarea.value.trim() : '';

        // Убираем класс ошибки, если был
        if (reasonTextarea) {
            reasonTextarea.classList.remove('is-invalid');
        }

        if (!reason) {
            alert('Пожалуйста, укажите причину отказа. Это сообщение будет отправлено рекламодателю.');
            if (reasonTextarea) {
                reasonTextarea.focus();
                reasonTextarea.classList.add('is-invalid');
            }
            return false;
        }

        if (reason.length < 10) {
            alert('Причина отказа должна содержать минимум 10 символов.');
            if (reasonTextarea) {
                reasonTextarea.focus();
                reasonTextarea.classList.add('is-invalid');
            }
            return false;
        }

        // Копируем значение в скрытое поле
        if (reasonHidden) {
            reasonHidden.value = reason;
        }

        return confirm('Вы уверены, что хотите отклонить эту рекламу? Причина отказа будет отправлена рекламодателю на email.');
    }

    // Функция для валидации причины отказа в быстром модальном окне отклонения
    function validateRejectionReasonQuick(adId) {
        const reasonTextarea = document.getElementById('rejection_reason_quick' + adId);
        const reason = reasonTextarea ? reasonTextarea.value.trim() : '';

        // Убираем класс ошибки, если был
        if (reasonTextarea) {
            reasonTextarea.classList.remove('is-invalid');
        }

        if (!reason) {
            alert('Пожалуйста, укажите причину отказа. Это сообщение будет отправлено рекламодателю.');
            if (reasonTextarea) {
                reasonTextarea.focus();
                reasonTextarea.classList.add('is-invalid');
            }
            return false;
        }

        if (reason.length < 10) {
            alert('Причина отказа должна содержать минимум 10 символов.');
            if (reasonTextarea) {
                reasonTextarea.focus();
                reasonTextarea.classList.add('is-invalid');
            }
            return false;
        }

        return confirm('Вы уверены, что хотите отклонить эту рекламу? Причина отказа будет отправлена рекламодателю на email.');
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Очистка текста причины отказа при закрытии модального окна
        const modalElements = document.querySelectorAll('.modal');
        modalElements.forEach(function(modal) {
            modal.addEventListener('hidden.bs.modal', function() {
                const textareas = this.querySelectorAll('textarea[name="rejection_reason"]');
                textareas.forEach(function(textarea) {
                    textarea.value = '';
                    // Убираем класс ошибки валидации, если был
                    textarea.classList.remove('is-invalid');
                });
            });
        });
    });
</script>

<?php
$content = ob_get_clean();
$title = 'Управление рекламой' . (Helper::isAdminLoggedIn() ? ' - Админ-панель' : ' - Панель менеджера');
include __DIR__ . '/../admin_layout.php';
?>

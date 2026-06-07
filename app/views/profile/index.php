<?php

/**
 * ЛИЧНЫЙ КАБИНЕТ
 */

// Убеждаемся, что все переменные определены и имеют правильный тип
if (!isset($userEvent) || (!is_array($userEvent) && $userEvent !== false && $userEvent !== null)) {
    $userEvent = null;
} elseif ($userEvent === false) {
    $userEvent = null;
}

if (!isset($userDate) || (!is_array($userDate) && $userDate !== false && $userDate !== null)) {
    $userDate = null;
} elseif ($userDate === false) {
    $userDate = null;
}

if (!isset($userAds) || !is_array($userAds)) {
    $userAds = [];
}

ob_start();
?>

<style>
    /* Мобильные стили для страницы профиля */
    @media (max-width: 767px) {
        .profile-header {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 15px;
        }

        .profile-header h2 {
            font-size: 1.5rem;
            margin-bottom: 0;
        }

        .profile-header-actions {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .profile-header-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .profile-card {
            margin-bottom: 20px;
            border-radius: 12px;
        }

        .profile-card .card-body {
            padding: 20px 15px;
        }

        .profile-card .card-title {
            font-size: 1.1rem;
            margin-bottom: 15px;
        }

        .progress-section {
            margin-bottom: 20px;
        }

        .progress-section .badge {
            font-size: 0.9rem;
            padding: 6px 12px;
        }

        .progress {
            height: 20px !important;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar {
            font-size: 0.75rem;
            font-weight: 600;
        }

        .info-section p {
            margin-bottom: 12px;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .info-section strong {
            display: inline-block;
            min-width: 90px;
            color: #667eea;
            font-weight: 600;
        }

        .photos-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .photos-grid img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .photo-item {
            position: relative;
        }

        .photo-item .btn {
            z-index: 10 !important;
            opacity: 0.9 !important;
        }

        .photos-title {
            font-size: 1.1rem;
            margin-bottom: 15px;
        }

        .ads-section .btn:not(.btn-sm) {
            width: 100%;
            margin-bottom: 10px;
        }

        .profile-announcements-accordion .accordion-button {
            font-weight: 600;
            padding: 12px 16px;
        }

        .profile-announcements-accordion .accordion-button:not(.collapsed) {
            background-color: rgba(102, 126, 234, 0.08);
            color: #667eea;
        }

        .profile-announcements-accordion .accordion-body {
            padding: 16px;
        }

        .ads-section .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .ads-section .alert .btn {
            width: auto;
            margin-top: 10px;
            float: none !important;
            display: block;
        }

        .alert-warning {
            border-radius: 10px;
            padding: 15px;
        }

        .alert-warning h5 {
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .alert-warning ul {
            margin-bottom: 10px;
            padding-left: 20px;
        }

        .alert-warning li {
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .ads-section .table {
            font-size: 0.85rem;
        }

        .ads-section .table th,
        .ads-section .table td {
            padding: 8px 4px;
        }

        .ads-section .btn-sm {
            padding: 4px 8px;
            font-size: 0.8rem;
            width: auto !important;
            opacity: 1 !important;
            pointer-events: auto !important;
            position: relative !important;
            z-index: 10 !important;
        }

        .delete-ad-btn {
            cursor: pointer !important;
        }

        .text__del {
            color: #fff;
        }
    }

    /* Общие улучшения для всех устройств */
    .profile-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .profile-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Стили для фотографий */
    .photo-item {
        position: relative;
    }

    .photo-item .btn {
        transition: all 0.3s ease;
        opacity: 0.85 !important;
        z-index: 10 !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 36px !important;
        height: 36px !important;
        min-width: 36px !important;
        border-radius: 50% !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25), 0 0 0 2px rgba(255, 255, 255, 0.3) inset !important;
        border: none !important;
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
    }

    .photo-item .btn i {
        font-size: 1rem !important;
        line-height: 1 !important;
        margin: 0 !important;
        padding: 0 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .photo-item:hover .btn {
        opacity: 1 !important;
        transform: scale(1.15) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.35), 0 0 0 2px rgba(255, 255, 255, 0.4) inset !important;
    }

    .photo-item:active .btn {
        transform: scale(1.05) !important;
    }

    .profile-announcements-accordion .accordion-button {
        font-weight: 600;
    }

    .profile-announcements-accordion .accordion-button:not(.collapsed) {
        background-color: rgba(102, 126, 234, 0.08);
        color: #667eea;
        box-shadow: none;
    }

    .profile-announcements-accordion .accordion-item {
        border-radius: 0;
    }

    .profile-announcements-accordion .accordion-item:first-of-type {
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }

    .profile-announcements-accordion .accordion-item:last-of-type {
        border-bottom-left-radius: 8px;
        border-bottom-right-radius: 8px;
    }
</style>

<div class="mobile-page-container mt-3 mt-md-4">
    <div class="d-flex justify-content-between align-items-center mb-4 profile-header">
        <h2>Мой кабинет</h2>
        <div class="profile-header-actions">
            <?php if (Helper::isManager()): ?>
                <a href="<?= BASE_URL ?>manager" class="btn btn-info">
                    <i class="bi bi-speedometer2"></i> Панель менеджера
                </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>profile/edit" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Редактировать профиль
            </a>
        </div>
    </div>
    <!-- Мои объявления -->
    <?php
    try {
        $userEvent = $userEvent ?? null;
        $userDate = $userDate ?? null;
        $userAds = $userAds ?? [];
        if (!is_array($userAds)) {
            $userAds = [];
        }

        $isDateExpired = false;
        if (!empty($userDate) && is_array($userDate) && isset($userDate['id'])) {
            if (!empty($userDate['date_time']) && strtotime($userDate['date_time']) < time()) {
                $isDateExpired = true;
            }
        }

        $hasPendingAd = false;
        if (!empty($userAds)) {
            foreach ($userAds as $ad) {
                if (isset($ad['status']) && $ad['status'] === 'pending') {
                    $hasPendingAd = true;
                    break;
                }
            }
        }

        $hasActiveEvent = !empty($userEvent) && is_array($userEvent) && isset($userEvent['id']);
        $hasActiveDate = !empty($userDate) && is_array($userDate) && isset($userDate['id']) && !$isDateExpired;
        $hasAds = !empty($userAds);
    } catch (Exception $e) {
        $userEvent = null;
        $userDate = null;
        $userAds = [];
        $isDateExpired = false;
        $hasPendingAd = false;
        $hasActiveEvent = false;
        $hasActiveDate = false;
        $hasAds = false;
    }
    ?>
    <div class="card mb-4 profile-card ads-section">
        <div class="card-body">
            <h5 class="card-title text-center">
                <i class="bi bi-megaphone"></i> Мои объявления
            </h5>

            <div class="accordion profile-announcements-accordion" id="profileAnnouncementsAccordion">
                <!-- Мероприятия -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="eventsSectionHeading">
                        <button class="accordion-button collapsed py-3" type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#eventsSection"
                            aria-expanded="false"
                            aria-controls="eventsSection">
                            <span class="d-flex align-items-center gap-2 w-100">
                                <i class="bi bi-calendar-event"></i>
                                <span>Создать мероприятие</span>
                                <?php if ($hasActiveEvent): ?>
                                    <span class="badge bg-info ms-auto me-2">Активно</span>
                                <?php endif; ?>
                            </span>
                        </button>
                    </h2>
                    <div id="eventsSection" class="accordion-collapse collapse" aria-labelledby="eventsSectionHeading" data-bs-parent="#profileAnnouncementsAccordion">
                        <div class="accordion-body">
                            <div class="mb-3">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEventModal" <?= $hasActiveEvent ? 'disabled' : '' ?>>
                                    <i class="bi bi-calendar-plus"></i> Создать мероприятие
                                </button>
                            </div>

                            <?php if ($hasActiveEvent): ?>
                                <div class="alert alert-info d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-0">
                                    <div class="mb-2 mb-md-0">
                                        <strong><i class="bi bi-calendar-event"></i> Активное мероприятие:</strong><br>
                                        <span class="ms-3"><?= Helper::escape($userEvent['title'] ?? '') ?></span>
                                        <?php if (!empty($userEvent['event_date']) && strtotime($userEvent['event_date']) >= time() && ($userEvent['status'] ?? 'pending') === 'approved'): ?>
                                            <div class="mt-2 ms-3">
                                                <small class="text-muted d-block mb-1"><i class="bi bi-clock"></i> До дедлайна:</small>
                                                <div class="countdown-timer" data-deadline="<?= date('Y-m-d H:i:s', strtotime($userEvent['event_date'])) ?>" data-event-id="<?= $userEvent['id'] ?>">
                                                    <span class="badge bg-info">Загрузка...</span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="<?= BASE_URL ?>events/delete?id=<?= $userEvent['id'] ?>"
                                            class="btn btn-sm btn-danger"
                                            style="color: white;"
                                            onclick="return confirm('Удалить мероприятие?')">
                                            <i class="bi bi-trash"></i> Удалить
                                        </a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center py-2 mb-0">
                                    <i class="bi bi-info-circle"></i> У вас пока нет активных мероприятий
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Свидания -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="datesSectionHeading">
                        <button class="accordion-button collapsed py-3" type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#datesSection"
                            aria-expanded="false"
                            aria-controls="datesSection">
                            <span class="d-flex align-items-center gap-2 w-100">
                                <i class="bi bi-heart-fill"></i>
                                <span>Создать свидание</span>
                                <?php if ($hasActiveDate): ?>
                                    <span class="badge bg-info ms-auto me-2">Активно</span>
                                <?php endif; ?>
                            </span>
                        </button>
                    </h2>
                    <div id="datesSection" class="accordion-collapse collapse" aria-labelledby="datesSectionHeading" data-bs-parent="#profileAnnouncementsAccordion">
                        <div class="accordion-body">
                            <div class="mb-3">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createDateModal" <?= $hasActiveDate ? 'disabled' : '' ?>>
                                    <i class="bi bi-heart-fill"></i> Создать свидание
                                </button>
                            </div>

                            <?php if ($hasActiveDate): ?>
                                <div class="alert alert-info d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-0">
                                    <div class="mb-2 mb-md-0">
                                        <strong><i class="bi bi-heart"></i> Активное свидание:</strong><br>
                                        <span class="ms-3"><?= Helper::escape($userDate['title'] ?? '') ?></span>
                                        <?php if (!empty($userDate['date_time'])): ?>
                                            <div class="mt-2 ms-3">
                                                <small class="text-muted d-block mb-1"><i class="bi bi-clock"></i> До дедлайна:</small>
                                                <div class="countdown-timer" data-deadline="<?= date('Y-m-d H:i:s', strtotime($userDate['date_time'])) ?>" data-date-id="<?= $userDate['id'] ?>">
                                                    <span class="badge bg-info">Загрузка...</span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="<?= BASE_URL ?>dates/edit?id=<?= $userDate['id'] ?>"
                                            class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i> Редактировать
                                        </a>
                                        <a href="<?= BASE_URL ?>dates/delete?id=<?= $userDate['id'] ?>"
                                            class="btn btn-sm btn-danger text__del"
                                            style="color: white;"
                                            onclick="return confirm('Удалить свидание?')">
                                            <i class="bi bi-trash"></i> Удалить
                                        </a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center py-2 mb-0">
                                    <i class="bi bi-info-circle"></i> У вас пока нет активных свиданий
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Реклама -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="adsSectionHeading">
                        <button class="accordion-button collapsed py-3" type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#adsSection"
                            aria-expanded="false"
                            aria-controls="adsSection">
                            <span class="d-flex align-items-center gap-2 w-100">
                                <i class="bi bi-bullhorn"></i>
                                <span>Подать рекламу</span>
                                <?php if ($hasPendingAd): ?>
                                    <span class="badge bg-warning text-dark ms-auto me-2">На модерации</span>
                                <?php elseif ($hasAds): ?>
                                    <span class="badge bg-secondary ms-auto me-2"><?= count($userAds) ?></span>
                                <?php endif; ?>
                            </span>
                        </button>
                    </h2>
                    <div id="adsSection" class="accordion-collapse collapse" aria-labelledby="adsSectionHeading" data-bs-parent="#profileAnnouncementsAccordion">
                        <div class="accordion-body">
                            <?php if ($hasPendingAd): ?>
                                <div class="alert alert-warning mb-3">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <strong>У вас есть заявка на модерации.</strong> Дождитесь рассмотрения текущей заявки перед подачей новой.
                                </div>
                            <?php else: ?>
                                <div class="mb-3">
                                    <a href="<?= BASE_URL ?>ads/create" class="btn btn-primary">
                                        <i class="bi bi-plus-circle"></i> Подать рекламу
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if ($hasAds): ?>
                                <div class="accordion" id="userAdsAccordion">
                    <?php foreach ($userAds as $ad): ?>
                        <?php if (!is_array($ad) || empty($ad['id'])) continue; ?>
                        <?php
                        $adId = (int)($ad['id'] ?? 0);
                        $adName = trim((string)($ad['advertiser_name'] ?? ''));
                        if ($adName === '') $adName = 'Без названия';

                        $statusClass = 'secondary';
                        $statusText = 'Ожидает';
                        $isRejected = false;
                        if (isset($ad['status'])) {
                            if ($ad['status'] === 'active') {
                                $statusClass = 'success';
                                $statusText = 'Активна';
                            } elseif ($ad['status'] === 'expired') {
                                $statusClass = 'danger';
                                $statusText = 'Истекла';
                            } elseif ($ad['status'] === 'pending') {
                                $statusClass = 'warning';
                                $statusText = 'На модерации';
                            } elseif ($ad['status'] === 'rejected') {
                                $statusClass = 'danger';
                                $statusText = 'Отказано';
                                $isRejected = true;
                            }
                        }

                        $endDateTs = !empty($ad['end_date']) ? strtotime($ad['end_date']) : null;
                        $isExpired = $endDateTs ? ($endDateTs < time()) : false;
                        $isActive = isset($ad['status']) && $ad['status'] === 'active';
                        ?>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="adHeading<?= $adId ?>">
                                <button class="accordion-button collapsed py-2" type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#adCollapse<?= $adId ?>"
                                    aria-expanded="false"
                                    aria-controls="adCollapse<?= $adId ?>">
                                    <div class="w-100 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-1 pe-1">
                                        <div class="d-flex align-items-center flex-wrap column-gap-2 row-gap-0">
                                            <div class="fw-semibold small"><?= Helper::escape($adName) ?></div>
                                            <?php if (!empty($ad['country'])): ?>
                                                <small class="text-muted d-flex align-items-center gap-1 lh-sm">
                                                    <i class="bi bi-geo-alt"></i>
                                                    <?= Helper::escape($ad['country']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="d-flex align-items-center gap-1 flex-wrap">
                                            <span class="badge bg-<?= $statusClass ?> small"><?= $statusText ?></span>
                                            <?php if (!empty($ad['end_date'])): ?>
                                                <small class="text-muted"><i class="bi bi-calendar-check"></i> <?= date('d.m.Y', strtotime($ad['end_date'])) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </button>
                            </h2>

                            <div id="adCollapse<?= $adId ?>" class="accordion-collapse collapse" aria-labelledby="adHeading<?= $adId ?>">
                                <div class="accordion-body py-2">
                                    <div class="row g-2">
                                        <div class="col-12 col-md-6">
                                            <div class="p-2 border rounded bg-light h-100">
                                                <div class="fw-semibold small mb-1"><i class="bi bi-calendar-range"></i> Период</div>
                                                <div class="d-flex flex-column gap-1 small">
                                                    <?php if (!empty($ad['start_date'])): ?>
                                                        <div class="d-flex align-items-center gap-2">

                                                            <span class="text-muted">С:</span>
                                                            <span class="fw-semibold"><?= date('d.m.Y', strtotime($ad['start_date'])) ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($ad['end_date'])): ?>
                                                        <div class="d-flex align-items-center gap-2">

                                                            <span class="text-muted">По:</span>
                                                            <span class="fw-semibold"><?= date('d.m.Y', strtotime($ad['end_date'])) ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (empty($ad['start_date']) && empty($ad['end_date'])): ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="p-2 border rounded bg-light h-100">
                                                <div class="fw-semibold small mb-1"><i class="bi bi-hourglass-split"></i> Дедлайн</div>
                                                <?php if ($endDateTs): ?>
                                                    <div class="d-flex flex-column gap-1 small">
                                                        <small class="text-muted">
                                                            <i class="bi bi-calendar-check"></i> <?= date('d.m.Y H:i', $endDateTs) ?>
                                                        </small>
                                                        <?php if ($isActive && !$isExpired): ?>
                                                            <div>
                                                                <small class="text-muted d-block mb-1"><i class="bi bi-clock"></i> Осталось:</small>
                                                                <div class="countdown-timer" data-deadline="<?= date('Y-m-d H:i:s', $endDateTs) ?>" data-ad-id="<?= $adId ?>">
                                                                    <span class="badge bg-info">Загрузка...</span>
                                                                </div>
                                                            </div>
                                                        <?php elseif ($isExpired): ?>
                                                            <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Истекла</span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <?php if ($isRejected && !empty($ad['rejection_reason'])): ?>
                                            <div class="col-12">
                                                <div class="alert alert-danger mb-2 py-2">
                                                    <div class="fw-semibold small mb-1">
                                                        <i class="bi bi-exclamation-triangle-fill"></i> Причина отказа:
                                                    </div>
                                                    <div class="small"><?= nl2br(Helper::escape($ad['rejection_reason'])) ?></div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="col-12">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                <div class="text-muted small">
                                                    <i class="bi bi-flag"></i> Страна: <span class="fw-semibold"><?= Helper::escape($ad['country'] ?? '—') ?></span>
                                                </div>
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <?php if ($isRejected): ?>
                                                        <a href="<?= BASE_URL ?>ads/create"
                                                            class="btn btn-sm btn-primary rounded-pill px-3 d-inline-flex align-items-center gap-2">
                                                            <i class="bi bi-pencil-square"></i>
                                                            <span>Исправить</span>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if (!empty($ad['image_path'])): ?>
                                                        <a href="<?= BASE_URL ?>ads/view?id=<?= $adId ?>"
                                                            class="btn btn-sm btn-outline-primary rounded-pill px-3 d-inline-flex align-items-center gap-2">
                                                            <i class="bi bi-eye"></i>
                                                            <span>Просмотр</span>
                                                        </a>
                                                    <?php endif; ?>
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-danger rounded-pill px-3 d-inline-flex align-items-center gap-2 delete-ad-btn"
                                                        data-ad-id="<?= $adId ?>"
                                                        data-ad-name="<?= htmlspecialchars($adName, ENT_QUOTES) ?>">
                                                        <i class="bi bi-trash"></i>
                                                        <span>Удалить</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                                <p class="text-muted text-center py-2 mb-0">
                                    <i class="bi bi-info-circle"></i> У вас пока нет поданных реклам
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ползунок заполняемости профиля -->
    <?php
    // Рассчитываем процент заполняемости
    $completion = 0;
    $maxCompletion = 100;

    if (!empty($user['gender'])) $completion += 20; // Пол - 20%
    if (!empty($user['age'])) $completion += 20; // Возраст - 20%
    if (!empty($user['country'])) $completion += 15; // Страна - 15%
    if (!empty($user['city'])) $completion += 15; // Город - 15%
    if (count($photos) >= MIN_PHOTOS) $completion += 20; // Фото - 20%
    if (!empty($user['about'])) $completion += 10; // О себе - 10%

    // Определяем цвет ползунка в зависимости от заполняемости
    $progressColor = 'bg-success';
    if ($completion < 50) {
        $progressColor = 'bg-danger';
    } elseif ($completion < 80) {
        $progressColor = 'bg-warning';
    }
    ?>

    <div class="card mb-4 profile-card progress-section">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">Заполненность профиля</h5>
                <span class="badge <?= $progressColor ?> fs-6"><?= $completion ?>%</span>
            </div>
            <div class="progress" style="height: 25px;">
                <div class="progress-bar <?= $progressColor ?> progress-bar-striped progress-bar-animated"
                    role="progressbar"
                    style="width: <?= $completion ?>%"
                    aria-valuenow="<?= $completion ?>"
                    aria-valuemin="0"
                    aria-valuemax="100">
                    <?= $completion ?>%
                </div>
            </div>
            <?php if ($completion < 100): ?>
                <!-- <small class="text-muted mt-2 d-block">
                    Для полного заполнения профиля добавьте:
                    <?php
                    $missing = [];
                    if (empty($user['gender'])) $missing[] = 'пол';
                    if (empty($user['age'])) $missing[] = 'возраст';
                    if (empty($user['country'])) $missing[] = 'страну';
                    if (empty($user['city'])) $missing[] = 'город';
                    if (count($photos) < MIN_PHOTOS) $missing[] = 'фотографии (минимум ' . MIN_PHOTOS . ')';
                    if (empty($user['about'])) $missing[] = 'информацию о себе';
                    echo implode(', ', $missing);
                    ?>
                </small> -->
            <?php else: ?>
                <small class="text-success mt-2 d-block">
                    <i class="bi bi-check-circle"></i> Профиль полностью заполнен!
                </small>
            <?php endif; ?>
        </div>
    </div>

    <!-- Проверка заполненности профиля -->
    <?php
    $userModel = new User();
    $profileComplete = $userModel->isProfileComplete(Helper::getUserId());

    // Показываем сообщение из сессии, если есть
    if (isset($_SESSION['profile_incomplete_message'])) {
        $incompleteMessage = $_SESSION['profile_incomplete_message'];
        unset($_SESSION['profile_incomplete_message']);
    }
    ?>

    <?php if (!$profileComplete || isset($incompleteMessage)): ?>
        <div class="alert alert-danger mb-4">
            <h5 class="mb-3"><i class="bi bi-exclamation-triangle-fill"></i> Профиль не заполнен</h5>
            <?php if (isset($incompleteMessage)): ?>
                <p class="mb-3"><strong><?= Helper::escape($incompleteMessage) ?></strong></p>
            <?php else: ?>
                <p class="mb-2"><strong>Для использования всех функций приложения необходимо заполнить профиль:</strong></p>
            <?php endif; ?>

            <ul class="mb-3">
                <?php if (empty($user['full_name'])): ?><li>Указать ФИО</li><?php endif; ?>
                <?php if (empty($user['gender'])): ?><li>Указать свой пол</li><?php endif; ?>
                <?php if (empty($user['age'])): ?><li>Указать возраст</li><?php endif; ?>
                <?php if (empty($user['marital_status'])): ?><li>Указать семейный статус</li><?php endif; ?>
                <?php if (empty($user['country'])): ?><li>Указать страну</li><?php endif; ?>
                <?php if (empty($user['city'])): ?><li>Указать город</li><?php endif; ?>
            </ul>
            <p class="mb-0">
                <strong>Без заполнения профиля вы не сможете отправлять сообщения, создавать свидания и мероприятия.</strong>
            </p>
            <p class="mb-0 mt-2">
                <a href="<?= BASE_URL ?>profile/edit" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Заполнить профиль
                </a>
            </p>
        </div>
    <?php endif; ?>

    <!-- Информация о пользователе -->
    <div class="card mb-4 profile-card">
        <div class="card-body info-section">
            <h5 class="card-title">Информация</h5>
            <?php if (!empty($user['full_name'])): ?>
                <p><strong>ФИО:</strong> <?= Helper::escape($user['full_name']) ?></p>
            <?php endif; ?>
            <p><strong>Email:</strong> <?= Helper::escape($user['email']) ?></p>
            <p><strong>Пол:</strong> <?= $user['gender'] === 'male' ? 'Мужской' : ($user['gender'] === 'female' ? 'Женский' : 'Не указан') ?></p>
            <p><strong>Возраст:</strong> <?= $user['age'] ?? 'Не указан' ?></p>
            <p><strong>Страна:</strong> <?= Helper::escape($user['country'] ?? 'Не указана') ?></p>
            <?php
            $city = $user['city'] ?? 'Не указан';
            if ($city !== 'Не указан') {
                // Универсальная обработка названий типа "Название городская администрация"
                if (preg_match('/^(.+?)(?:ская|ая)\s+городская\s+администрация$/iu', $city, $matches)) {
                    $cityName = trim($matches[1]);

                    // Специальные случаи для известных городов
                    $specialCases = [
                        'Карагандин' => 'Караганда',
                        'Алматин' => 'Алматы',
                        'Астанин' => 'Астана',
                        'Шымкент' => 'Шымкент',
                    ];

                    // Проверяем специальные случаи
                    if (isset($specialCases[$cityName])) {
                        $city = $specialCases[$cityName];
                    } else {
                        // Универсальное преобразование: убираем "ин" в конце, если есть
                        $city = preg_replace('/ин$/', '', $cityName);
                        // Если после преобразования получилась пустая строка, оставляем оригинал
                        if (empty($city)) {
                            $city = $user['city'];
                        }
                    }
                }
            }
            ?>
            <p><strong>Город:</strong> <?= Helper::escape($city) ?></p>
            <?php if (!empty($user['about'])): ?>
                <p><strong>О себе:</strong> <?= Helper::escape($user['about']) ?></p>
            <?php endif; ?>
            <?php if ($user['age_changes_count'] > 0): ?>
                <p class="text-muted mt-3">
                    <small><i class="bi bi-info-circle"></i> Возраст изменен <?= $user['age_changes_count'] ?> раз(а) из 2 возможных</small>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Фотографии -->
    <div class="card mb-4 profile-card">
        <div class="card-body">
            <h5 class="card-title photos-title">
                <i class="bi bi-images"></i> Фотографии
            </h5>

            <?php if (count($photos) > 0): ?>
                <div class="photos-grid">
                    <?php foreach ($photos as $photo): ?>
                        <div class="photo-item position-relative">
                            <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . $photo['photo'] ?>"
                                class="img-fluid"
                                alt="Фото профиля">
                            <a href="<?= BASE_URL ?>profile/deletePhoto?id=<?= $photo['id'] ?>"
                                class="btn  btn-sm btn-danger text__del position-absolute top-0 end-0 m-2"
                                onclick="return confirm('Удалить эту фотографию?')"
                                title="Удалить фотографию">
                                <i class="bi bi-trash" style="color: white;"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted text-center py-4">
                    <i class="bi bi-image" style="font-size: 2rem;"></i><br>
                    Фотографии не загружены
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Модальное окно для создания мероприятия -->
    <div class="modal fade" id="createEventModal" tabindex="-1" aria-labelledby="createEventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createEventModalLabel">Создать мероприятие</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if ($userEvent): ?>
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle"></i>
                            <!-- <strong>У вас уже есть активное объявление о мероприятии</strong> -->
                            <p class="mb-0 mt-2">Название: <?= Helper::escape($userEvent['title']) ?></p>
                        </div>
                    <?php else: ?>
                        <p>Вы будете перенаправлены на страницу создания мероприятия.</p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                    <?php if (!$userEvent): ?>
                        <a href="<?= BASE_URL ?>events/create" class="btn btn-primary">
                            <i class="bi bi-calendar-plus"></i> Создать мероприятие
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно для создания свидания -->
    <div class="modal fade" id="createDateModal" tabindex="-1" aria-labelledby="createDateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createDateModalLabel">Создать свидание</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if ($userDate): ?>
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle"></i>

                            <p class="mb-0 mt-2">Заголовок: <?= Helper::escape($userDate['title']) ?></p>
                        </div>
                    <?php else: ?>
                        <p>Вы будете перенаправлены на страницу создания свидания.</p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                    <?php if (!$userDate): ?>
                        <a href="<?= BASE_URL ?>dates/create" class="btn btn-primary">
                            <i class="bi bi-heart-fill"></i> Создать свидание
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Обработчик для удаления рекламы через делегирование событий
        document.body.addEventListener('click', function(e) {
            // Проверяем, была ли нажата кнопка удаления или её дочерний элемент
            const deleteBtn = e.target.closest('.delete-ad-btn');

            if (deleteBtn) {
                e.preventDefault();
                e.stopPropagation();

                const adId = deleteBtn.getAttribute('data-ad-id');
                const adName = deleteBtn.getAttribute('data-ad-name');

                console.log('Удаление рекламы:', {
                    adId,
                    adName
                });

                if (confirm('Вы уверены, что хотите удалить рекламу "' + adName + '"?\n\nЭто действие нельзя отменить.')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '<?= BASE_URL ?>ads/delete';
                    form.style.display = 'none';

                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ad_id';
                    input.value = adId;
                    form.appendChild(input);

                    document.body.appendChild(form);
                    console.log('Отправка формы для удаления рекламы ID:', adId);
                    form.submit();
                }
            }
        });

        let expiredCleanupInProgress = false;

        function updateCountdown() {
            const timers = document.querySelectorAll('.countdown-timer');

            timers.forEach(function(timer) {
                const deadline = new Date(timer.getAttribute('data-deadline')).getTime();
                const now = new Date().getTime();
                const distance = deadline - now;

                if (distance < 0) {
                    timer.innerHTML = '<span class="badge bg-danger">Истекло</span>';

                    // Защита от множественных запросов и бесконечной перезагрузки
                    if (expiredCleanupInProgress) return;

                    // Проверяем, не было ли уже перезагрузки
                    const reloadKey = 'expired_reload_' + timer.getAttribute('data-date-id');
                    if (sessionStorage.getItem(reloadKey)) {
                        // Уже пытались перезагрузить - просто скрываем блок
                        const parentAlert = timer.closest('.alert');
                        if (parentAlert) parentAlert.style.display = 'none';
                        sessionStorage.removeItem(reloadKey);
                        return;
                    }

                    expiredCleanupInProgress = true;
                    sessionStorage.setItem(reloadKey, '1');

                    // Вызываем AJAX для удаления просроченных свиданий из базы
                    fetch(BASE_URL + 'dates/deleteExpired', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).then(function(response) {
                        return response.json();
                    }).then(function(data) {
                        if (data.success) {
                            // Перезагружаем страницу после успешного удаления
                            window.location.reload();
                        } else {
                            // При ошибке скрываем блок
                            const parentAlert = timer.closest('.alert');
                            if (parentAlert) parentAlert.style.display = 'none';
                            sessionStorage.removeItem(reloadKey);
                        }
                    }).catch(function() {
                        // При ошибке скрываем блок
                        const parentAlert = timer.closest('.alert');
                        if (parentAlert) parentAlert.style.display = 'none';
                        sessionStorage.removeItem(reloadKey);
                    });
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                let timeString = '';
                if (days > 0) {
                    timeString += days + ' дн. ';
                }
                if (hours > 0 || days > 0) {
                    timeString += hours + ' ч. ';
                }
                if (minutes > 0 || hours > 0 || days > 0) {
                    timeString += minutes + ' мин. ';
                }
                timeString += seconds + ' сек.';

                let badgeClass = 'bg-success';
                if (distance < 3600000) { // Меньше часа
                    badgeClass = 'bg-danger';
                } else if (distance < 86400000) { // Меньше суток
                    badgeClass = 'bg-warning';
                }

                timer.innerHTML = '<span class="badge ' + badgeClass + '">' + timeString + '</span>';
            });
        }

        // Обновляем счетчик каждую секунду
        updateCountdown();
        setInterval(updateCountdown, 1000);

        // Функция для сжатия изображения (максимально оптимизированная версия)
        function compressImage(file, maxWidth = 1024, maxHeight = 1024, quality = 0.7) {
            return new Promise((resolve, reject) => {
                // Если файл маленький (меньше 2MB), не сжимаем
                if (file.size < 2 * 1024 * 1024) {
                    resolve(file);
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = new Image();
                    img.onload = function() {
                        const canvas = document.createElement('canvas');
                        let width = img.width;
                        let height = img.height;

                        // Вычисляем новые размеры с сохранением пропорций
                        if (width > maxWidth || height > maxHeight) {
                            if (width > height) {
                                if (width > maxWidth) {
                                    height = Math.round((height * maxWidth) / width);
                                    width = maxWidth;
                                }
                            } else {
                                if (height > maxHeight) {
                                    width = Math.round((width * maxHeight) / height);
                                    height = maxHeight;
                                }
                            }
                        }

                        canvas.width = width;
                        canvas.height = height;

                        const ctx = canvas.getContext('2d');
                        // Используем быстрое сглаживание для максимальной скорости
                        ctx.imageSmoothingEnabled = true;
                        ctx.imageSmoothingQuality = 'low';
                        ctx.drawImage(img, 0, 0, width, height);

                        // Конвертируем в Blob с качеством
                        canvas.toBlob(function(blob) {
                            if (blob) {
                                // Если сжатый файл больше оригинала, используем оригинал
                                if (blob.size >= file.size) {
                                    resolve(file);
                                } else {
                                    // Создаем новый File объект с оригинальным именем
                                    const compressedFile = new File([blob], file.name, {
                                        type: 'image/jpeg',
                                        lastModified: Date.now()
                                    });
                                    resolve(compressedFile);
                                }
                            } else {
                                reject(new Error('Ошибка сжатия изображения'));
                            }
                        }, 'image/jpeg', quality);
                    };
                    img.onerror = function() {
                        reject(new Error('Ошибка загрузки изображения'));
                    };
                    img.src = e.target.result;
                };
                reader.onerror = function() {
                    reject(new Error('Ошибка чтения файла'));
                };
                reader.readAsDataURL(file);
            });
        }

        // Обработчик отправки формы с фотографиями
        const photoForm = document.getElementById('photoUploadForm');
        if (photoForm) {
            photoForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const fileInput = document.getElementById('photos');
                const uploadBtn = document.getElementById('uploadBtn');
                const statusDiv = document.getElementById('compressionStatus');

                if (!fileInput.files || fileInput.files.length === 0) {
                    return;
                }

                // Отключаем кнопку и показываем статус
                uploadBtn.disabled = true;
                uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Обработка...';
                statusDiv.style.display = 'block';
                const compressionText = document.getElementById('compressionText');
                const compressionProgress = document.getElementById('compressionProgress');
                const progressBar = compressionProgress.querySelector('.progress-bar');

                try {
                    const files = Array.from(fileInput.files);
                    const totalFiles = files.length;

                    // Показываем прогресс-бар
                    compressionProgress.style.display = 'block';
                    compressionText.textContent = `Подготовка ${totalFiles} файлов...`;

                    // Функция для параллельной обработки с ограничением
                    async function processFilesInBatches(files, batchSize = 3) {
                        const results = [];
                        let processed = 0;

                        for (let i = 0; i < files.length; i += batchSize) {
                            const batch = files.slice(i, i + batchSize);

                            // Обрабатываем батч параллельно
                            const batchPromises = batch.map(async (file, index) => {
                                // Проверяем, является ли файл изображением
                                if (!file.type.startsWith('image/')) {
                                    return file;
                                }

                                try {
                                    const compressedFile = await compressImage(file);
                                    processed++;
                                    // Обновляем прогресс
                                    const progress = Math.round((processed / totalFiles) * 100);
                                    progressBar.style.width = progress + '%';
                                    compressionText.textContent = `Обработка ${processed} из ${totalFiles}...`;
                                    return compressedFile;
                                } catch (error) {
                                    console.error('Ошибка сжатия файла ' + file.name + ':', error);
                                    processed++;
                                    const progress = Math.round((processed / totalFiles) * 100);
                                    progressBar.style.width = progress + '%';
                                    compressionText.textContent = `Обработка ${processed} из ${totalFiles}...`;
                                    // Если не удалось сжать, используем оригинальный файл
                                    return file;
                                }
                            });

                            const batchResults = await Promise.all(batchPromises);
                            results.push(...batchResults);
                        }

                        return results;
                    }

                    // Обрабатываем файлы параллельно батчами
                    const compressedFiles = await processFilesInBatches(files, 3);

                    // Обновляем статус перед отправкой
                    compressionText.textContent = 'Отправка на сервер...';
                    progressBar.style.width = '100%';

                    // Создаем новую FormData с сжатыми файлами
                    const formData = new FormData();
                    compressedFiles.forEach((file) => {
                        formData.append('photos[]', file);
                    });

                    // Отправляем сжатые файлы
                    const response = await fetch(photoForm.action, {
                        method: 'POST',
                        body: formData
                    });

                    if (response.ok) {
                        // Перезагружаем страницу для отображения новых фотографий
                        window.location.reload();
                    } else {
                        alert('Ошибка при загрузке фотографий. Попробуйте еще раз.');
                        uploadBtn.disabled = false;
                        uploadBtn.innerHTML = '<i class="bi bi-upload"></i> Загрузить';
                        statusDiv.style.display = 'none';
                    }
                } catch (error) {
                    console.error('Ошибка:', error);
                    alert('Произошла ошибка при обработке фотографий. Попробуйте еще раз.');
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = '<i class="bi bi-upload"></i> Загрузить';
                    statusDiv.style.display = 'none';
                }
            });
        }
    });
</script>

<?php
$content = ob_get_clean();
$title = 'Личный кабинет';
include __DIR__ . '/../layout.php';
?>
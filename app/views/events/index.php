<?php

/**
 * СТРАНИЦА МЕРОПРИЯТИЙ
 */

ob_start();

$eventPublishPrice = defined('EVENT_PUBLISH_PRICE_KZT') ? (int) EVENT_PUBLISH_PRICE_KZT : 500;
$eventPublishPaid = defined('EVENT_PUBLISH_PAYMENT_ENABLED') && EVENT_PUBLISH_PAYMENT_ENABLED;
?>

<div class="mobile-page-container">
    <div class="events-header">
        
        <h2 class="events-title"></h2>
        <div class="events-header-buttons">
            <?php if (Helper::isLoggedIn()): ?>
                <?php
                $totalUnread = 0;
                if (!empty($myEventChats)) {
                    foreach ($myEventChats as $event) {
                        $totalUnread += $event['unread_count'] ?? 0;
                    }
                }
                ?>
                <a href="<?= BASE_URL ?>messages/events-list" class="btn-my-chats">
                    <i class="bi bi-chat-dots"></i>
                    Мои чаты
                    <?php if ($totalUnread > 0): ?>
                        <span class="chats-badge"><?= $totalUnread > 99 ? '99+' : $totalUnread ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
            <?php if (Helper::isLoggedIn()): ?>
                <button type="button" class="btn-create-event" data-bs-toggle="modal" data-bs-target="#createEventModal">
                    Создать мероприятие
                </button>
            <?php else: ?>
                <div class="events-register-hint">
                    <a href="<?= BASE_URL ?>auth/register">Зарегистрируйтесь</a> чтобы создать мероприятие
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (Helper::isLoggedIn() && !empty($myEvents)): ?>
        <h3 class="events-section-title">Мои мероприятия</h3>
        <div class="events-list">
            <?php foreach ($myEvents as $event): ?>
                <div class="event-item-compact event-item-mine" 
                     id="event-<?= $event['id'] ?>" 
                     data-event='<?= htmlspecialchars(json_encode($event), ENT_QUOTES, 'UTF-8') ?>'
                     data-is-mine="true"
                     style="cursor: pointer;">
                    <?php if (!empty($event['photo'])): ?>
                        <div class="event-banner">
                            <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . $event['photo'] ?>" alt="Баннер">
                                    </div>
                    <?php else: ?>
                        <div class="event-banner-placeholder">
                            <i class="bi bi-calendar-event"></i>
                                </div>
                            <?php endif; ?>
                    <div class="event-compact-content">
                        <h4 class="event-compact-title"><?= Helper::escape($event['title']) ?></h4>
                        <div class="event-compact-time">
                            <i class="bi bi-calendar3"></i>
                            <span><?= date('d.m.Y', strtotime($event['event_date'])) ?> в <?= date('H:i', strtotime($event['event_date'])) ?></span>
                        </div>
                        <?php if (!empty($event['location'])): ?>
                            <?php
                            // Парсим адрес для получения города и страны
                            $locationParts = explode(',', $event['location']);
                            $locationParts = array_map('trim', $locationParts);
                            $locationParts = array_filter($locationParts);
                            $locationParts = array_values($locationParts);
                            
                            $city = '';
                            $country = '';
                            if (count($locationParts) >= 2) {
                                // Последний элемент - обычно страна
                                $country = end($locationParts);
                                // Предпоследний - обычно город
                                if (count($locationParts) >= 2) {
                                    $city = $locationParts[count($locationParts) - 2];
                                }
                            }
                            
                            // Если есть координаты, используем их для определения правильной страны (через JavaScript)
                            $hasCoordinates = !empty($event['latitude']) && !empty($event['longitude']);
                            ?>
                            <?php if ($city || $country): ?>
                                <div class="event-compact-location" 
                                     <?php if ($hasCoordinates): ?>
                                     data-lat="<?= $event['latitude'] ?>" 
                                     data-lon="<?= $event['longitude'] ?>"
                                     data-city="<?= Helper::escape($city) ?>"
                                     data-country="<?= Helper::escape($country) ?>"
                                     <?php endif; ?>>
                                    <i class="bi bi-geo-alt"></i>
                                    <span class="location-text">
                                        <?php if ($city && $country): ?>
                                            <?= Helper::escape($city) ?>, <?= Helper::escape($country) ?>
                                        <?php elseif ($city): ?>
                                            <?= Helper::escape($city) ?>
                                        <?php elseif ($country): ?>
                                            <?= Helper::escape($country) ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if (!empty($event['price']) && $event['price'] > 0): ?>
                            <?php
                            $currencyCode = $event['currency_code'] ?? 'KZT';
                            $currencySymbol = '₸';
                            $currencyMap = [
                                'KZT' => '₸',
                                'RUB' => '₽',
                                'BYN' => 'Br',
                                'UAH' => '₴',
                                'USD' => '$',
                                'EUR' => '€',
                                'GBP' => '£',
                                'CNY' => '¥',
                                'JPY' => '¥',
                                'TRY' => '₺',
                                'KGS' => 'сом',
                                'UZS' => 'сум'
                            ];
                            if (isset($currencyMap[$currencyCode])) {
                                $currencySymbol = $currencyMap[$currencyCode];
                            }
                            ?>
                            <div class="event-compact-price"
                                 data-amount="<?= htmlspecialchars((string) (float) $event['price'], ENT_QUOTES, 'UTF-8') ?>"
                                 data-currency="<?= Helper::escape($currencyCode) ?>">
                                <i class="bi bi-currency-exchange"></i>
                                <div class="event-compact-price-main">
                                    <span>
                                        <?= number_format($event['price'], 0, ',', ' ') ?> <?= $currencySymbol ?>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (Helper::isLoggedIn() && (empty($userLat) || empty($userLon))): ?>
        <div class="events-warning">
            <i class="bi bi-geo-alt"></i>
            <p>Укажите геолокацию в <a href="<?= BASE_URL ?>profile/edit">профиле</a> для просмотра мероприятий рядом с вами</p>
        </div>
    <?php endif; ?>

    <?php if (empty($events)): ?>
        <h3 class="events-section-title"><?= Helper::isLoggedIn() ? 'Другие мероприятия' : 'Мероприятия' ?></h3>
        <div class="events-empty">
            <i class="bi bi-inbox"></i>
            <p><?= Helper::isLoggedIn() ? 'В радиусе 50км нет мероприятий' : 'Пока нет доступных мероприятий' ?></p>
            <?php if (!Helper::isLoggedIn()): ?>
                <a href="<?= BASE_URL ?>auth/register">Зарегистрируйтесь</a> чтобы создать мероприятие
            <?php endif; ?>
        </div>
    <?php else: ?>
        <h3 class="events-section-title"><?= Helper::isLoggedIn() ? 'Другие мероприятия' : 'Мероприятия' ?></h3>
        <?php
        // Фильтруем свои мероприятия из списка других
        $otherEvents = [];
        if ($currentUserId) {
            $myEventIds = array_column($myEvents, 'id');
            foreach ($events as $event) {
                if (!in_array($event['id'], $myEventIds) && (int)$event['user_id'] !== (int)$currentUserId) {
                    $otherEvents[] = $event;
                }
            }
        } else {
            $otherEvents = $events;
        }
        ?>
        <?php if (empty($otherEvents)): ?>
            <div class="events-empty">
                <i class="bi bi-inbox"></i>
                <p>В радиусе 50км нет других мероприятий</p>
            </div>
        <?php else: ?>
            <div class="events-list">
                <?php foreach ($otherEvents as $event): ?>
                    <div class="event-item-compact" 
                         id="event-<?= $event['id'] ?>" 
                         data-event='<?= htmlspecialchars(json_encode($event), ENT_QUOTES, 'UTF-8') ?>'
                         data-is-mine="false"
                         style="cursor: pointer;">
                        <?php if (!empty($event['photo'])): ?>
                            <div class="event-banner">
                                <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . $event['photo'] ?>" alt="Баннер">
                                        </div>
                        <?php else: ?>
                            <div class="event-banner-placeholder">
                                <i class="bi bi-calendar-event"></i>
                                    </div>
                                <?php endif; ?>
                        <div class="event-compact-content">
                            <h4 class="event-compact-title"><?= Helper::escape($event['title']) ?></h4>
                            <div class="event-compact-time">
                                <i class="bi bi-calendar3"></i>
                                <span><?= date('d.m.Y', strtotime($event['event_date'])) ?> в <?= date('H:i', strtotime($event['event_date'])) ?></span>
                            </div>
                            <?php if (!empty($event['location'])): ?>
                                <?php
                                // Парсим адрес для получения города и страны
                                $locationParts = explode(',', $event['location']);
                                $locationParts = array_map('trim', $locationParts);
                                $locationParts = array_filter($locationParts);
                                $locationParts = array_values($locationParts);
                                
                                $city = '';
                                $country = '';
                                if (count($locationParts) >= 2) {
                                    // Последний элемент - обычно страна
                                    $country = end($locationParts);
                                    // Предпоследний - обычно город
                                    if (count($locationParts) >= 2) {
                                        $city = $locationParts[count($locationParts) - 2];
                                    }
                                }
                                
                                // Если есть координаты, используем их для определения правильной страны (через JavaScript)
                                $hasCoordinates = !empty($event['latitude']) && !empty($event['longitude']);
                                ?>
                                <?php if ($city || $country): ?>
                                    <div class="event-compact-location" 
                                         <?php if ($hasCoordinates): ?>
                                         data-lat="<?= $event['latitude'] ?>" 
                                         data-lon="<?= $event['longitude'] ?>"
                                         data-city="<?= Helper::escape($city) ?>"
                                         data-country="<?= Helper::escape($country) ?>"
                                         <?php endif; ?>>
                                        <i class="bi bi-geo-alt"></i>
                                        <span class="location-text">
                                            <?php if ($city && $country): ?>
                                                <?= Helper::escape($city) ?>, <?= Helper::escape($country) ?>
                                            <?php elseif ($city): ?>
                                                <?= Helper::escape($city) ?>
                                            <?php elseif ($country): ?>
                                                <?= Helper::escape($country) ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if (!empty($event['price']) && $event['price'] > 0): ?>
                                <?php
                                $currencyCode = $event['currency_code'] ?? 'KZT';
                                $currencySymbol = '₸';
                                $currencyMap = [
                                    'KZT' => '₸',
                                    'RUB' => '₽',
                                    'BYN' => 'Br',
                                    'UAH' => '₴',
                                    'USD' => '$',
                                    'EUR' => '€',
                                    'GBP' => '£',
                                    'CNY' => '¥',
                                    'JPY' => '¥',
                                    'TRY' => '₺',
                                    'KGS' => 'сом',
                                    'UZS' => 'сум'
                                ];
                                if (isset($currencyMap[$currencyCode])) {
                                    $currencySymbol = $currencyMap[$currencyCode];
                                }
                                ?>
                                <div class="event-compact-price"
                                     data-amount="<?= htmlspecialchars((string) (float) $event['price'], ENT_QUOTES, 'UTF-8') ?>"
                                     data-currency="<?= Helper::escape($currencyCode) ?>">
                                    <i class="bi bi-currency-exchange"></i>
                                    <div class="event-compact-price-main">
                                        <span>
                                            <?= number_format($event['price'], 0, ',', ' ') ?> <?= $currencySymbol ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
    /* Минималистичный дизайн для мобильных устройств */
    @media (max-width: 767px) {
        /* Заголовок страницы */
        .events-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .events-header-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn-my-chats {
            padding: 10px 16px;
            border-radius: 10px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white !important;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            position: relative;
            text-decoration: none;
        }

        .btn-my-chats,
        .btn-my-chats * {
            color: white !important;
        }

        .btn-my-chats:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .btn-my-chats:active {
            transform: scale(0.98);
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
        }

        .btn-my-chats i {
            font-size: 16px;
            color: white !important;
        }

        .chats-badge {
            background: #dc2626;
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
            line-height: 1.4;
        }

        .events-title {
            font-size: 28px;
            font-weight: 600;
            color: #111827;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .btn-create-event {
            padding: 10px 16px;
            border-radius: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .btn-create-event:hover {
            background: #374151;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .btn-create-event:active {
            transform: scale(0.98);
            background: #4b5563;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .events-register-hint {
            font-size: 13px;
            color: #6b7280;
            text-align: right;
        }

        .events-register-hint a {
            color: #111827;
            font-weight: 600;
            text-decoration: none;
        }

        /* Секции */
        .events-section-title {
            font-size: 20px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 16px;
            margin-top: 32px;
        }

        .events-section-title:first-of-type {
            margin-top: 0;
        }

        /* Список мероприятий */
        .events-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* Компактная карточка — фон как в ленте WhatsApp, фото — «пузырь» */
        .event-item-compact {
            background: #f0f2f5;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 0.5px rgba(11, 20, 26, 0.08);
            transition: all 0.2s ease;
            cursor: pointer;
            border: 1px solid rgba(11, 20, 26, 0.06);
        }

        .event-item-compact:active {
            transform: scale(0.98);
            box-shadow: 0 1px 2px rgba(11, 20, 26, 0.1);
        }

        .event-item-mine {
            border: 1px solid rgba(11, 20, 26, 0.08);
        }

        .event-item-chat {
            border: 1px solid #667eea;
            border-left: 3px solid #667eea;
        }

        .event-banner {
            width: calc(100% - 16px);
            margin: 8px 8px 0;
            height: 180px;
            overflow: hidden;
            background: #eceff1;
            border-radius: 8px;
            box-shadow: 0 1px 0.5px rgba(11, 20, 26, 0.12);
        }

        .event-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 7.5px;
            display: block;
        }

        .event-banner-placeholder {
            width: calc(100% - 16px);
            margin: 8px 8px 0;
            height: 180px;
            border-radius: 8px;
            background: #dfe5e8;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 1px 0.5px rgba(11, 20, 26, 0.1);
        }

        .event-banner-placeholder i {
            font-size: 56px;
            color: #94a3b8;
            opacity: 0.95;
        }

        .event-compact-content {
            padding: 12px 12px 14px;
            margin: 8px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 0.5px rgba(11, 20, 26, 0.06);
        }

        .event-compact-title {
            font-size: 17px;
            font-weight: 600;
            color: #111827;
            margin: 0 0 8px 0;
            line-height: 1.3;
        }

        .event-compact-time {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .event-compact-time i {
            font-size: 14px;
            color: #9ca3af;
        }

        .event-compact-location {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #667eea;
            margin-bottom: 6px;
            font-weight: 500;
        }

        .event-compact-location i {
            font-size: 13px;
            color: #667eea;
        }

        .event-compact-price {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            gap: 6px;
            font-size: 13px;
            color: #059669;
            font-weight: 600;
        }

        .event-compact-price i {
            font-size: 14px;
            color: #059669;
            margin-top: 2px;
        }

        .event-compact-price-main {
            display: flex;
            flex-direction: column;
            gap: 2px;
            line-height: 1.25;
        }

        .event-price-converted {
            font-size: 11px;
            font-weight: 500;
            color: #667781;
        }

        /* Модальное окно деталей */
        .event-modal-content {
            border-radius: 16px;
            border: none;
            overflow: hidden;
        }

        .event-modal-header {
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 20px;
        }

        .event-modal-header .modal-title {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
        }

        .event-modal-body {
            padding: 0;
            max-height: 80vh;
            overflow-y: auto;
        }

        .event-modal-banner {
            width: calc(100% - 24px);
            margin: 12px auto 0;
            height: 220px;
            overflow: hidden;
            background: #eceff1;
            border-radius: 8px;
            box-shadow: 0 1px 0.5px rgba(11, 20, 26, 0.12);
        }

        .event-modal-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 7.5px;
            display: block;
        }

        .event-modal-banner-placeholder {
            width: calc(100% - 24px);
            margin: 12px auto 0;
            height: 220px;
            border-radius: 8px;
            background: #dfe5e8;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 1px 0.5px rgba(11, 20, 26, 0.1);
        }

        .event-modal-banner-placeholder i {
            font-size: 72px;
            color: #94a3b8;
            opacity: 0.95;
        }

        .event-modal-title {
            font-size: 20px;
            font-weight: 600;
            color: #111827;
            margin: 20px 20px 12px 20px;
            line-height: 1.3;
        }

        .event-modal-status {
            margin: 0 20px 16px 20px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .event-status-badge {
            display: inline-block;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
        }

        .event-status-approved {
            background: #d1fae5;
            color: #065f46;
        }

        .event-status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .event-status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .event-status-active {
            background: #dbeafe;
            color:rgb(214, 15, 45);
        }

        .event-status-past {
            background: #f3f4f6;
            color: #6b7280;
        }

        .event-modal-description {
            margin: 0 20px 16px 20px;
            padding: 16px;
            background: #f8fafc;
            border-radius: 10px;
            border-left: 3px solid #667eea;
        }

        .event-modal-description p {
            font-size: 14px;
            color: #374151;
            line-height: 1.6;
            margin: 0;
        }

        .event-modal-info {
            padding: 16px 20px;
            border-top: 1px solid #f3f4f6;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .event-modal-info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #374151;
        }

        .event-modal-info-item.event-price-info {
            align-items: flex-start;
        }

        .event-modal-info-item i {
            font-size: 16px;
            color: #9ca3af;
            width: 20px;
        }

        /* Красивый счетчик времени */
        .event-modal-countdown-wrapper {
            margin-top: 16px;
            padding: 16px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .event-modal-countdown-label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .event-modal-countdown-label i {
            font-size: 16px;
            color: #94a3b8;
        }

        .event-modal-countdown {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .countdown-timer {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .countdown-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 50px;
        }

        .countdown-value {
            font-size: 24px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            line-height: 1.2;
            color: #111827;
            background: white;
            padding: 10px 12px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            min-width: 50px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .countdown-label {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .countdown-separator {
            font-size: 20px;
            font-weight: 700;
            color: #cbd5e1;
            margin: 0 2px;
            padding-bottom: 20px;
        }

        .countdown-timer.countdown-normal .countdown-value {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .countdown-timer.countdown-warning .countdown-value {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
            animation: pulse-warning 2s infinite;
        }

        .countdown-timer.countdown-urgent .countdown-value {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
            animation: pulse-urgent 1s infinite;
        }

        .countdown-timer.countdown-expired {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px;
        }

        .countdown-expired-content {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #9ca3af;
        }

        .countdown-expired-content i {
            font-size: 18px;
        }

        @keyframes pulse-warning {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        @keyframes pulse-urgent {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
            }
            50% {
                transform: scale(1.08);
                box-shadow: 0 6px 20px rgba(239, 68, 68, 0.6);
            }
        }

        .event-modal-status-wrapper {
            margin-top: 16px;
            padding: 16px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .event-modal-status-text {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #9ca3af;
        }

        .event-modal-status-text i {
            font-size: 18px;
        }

        .event-modal-actions {
            padding: 16px 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .event-modal-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            min-width: 120px;
        }

        .event-modal-btn-full {
            width: 100%;
        }

        .event-modal-btn-chat {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .event-modal-btn-chat:active {
            background: #374151;
            transform: scale(0.98);
        }

        .event-modal-btn-edit {
            background: #f3f4f6;
            color: #111827;
        }

        .event-modal-btn-edit:active {
            background: #e5e7eb;
            transform: scale(0.98);
        }

        .event-modal-btn-delete {
            background: #fee2e2;
            color: #dc2626;
        }

        .event-modal-btn-delete:active {
            background: #fecaca;
            transform: scale(0.98);
        }

        .event-modal-btn i {
            font-size: 18px;
        }

        .event-modal-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #dc2626;
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
            line-height: 1.4;
        }

        /* Пустое состояние */
        .events-empty {
            text-align: center;
            padding: 48px 24px;
            color: #6b7280;
        }

        .events-empty i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .events-empty p {
            font-size: 15px;
            margin-bottom: 8px;
            color: #6b7280;
        }

        .events-empty a {
            color: #111827;
            font-weight: 600;
            text-decoration: none;
        }

        /* Предупреждение */
        .events-warning {
            background: #fef3c7;
            border-left: 3px solid #f59e0b;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .events-warning i {
            font-size: 18px;
            color: #f59e0b;
            margin-top: 2px;
        }

        .events-warning p {
            font-size: 13px;
            color: #92400e;
            margin: 0;
            line-height: 1.5;
        }

        .events-warning a {
            color: #92400e;
            font-weight: 600;
            text-decoration: underline;
        }

        /* Модальное окно */
        .events-modal-warning {
            text-align: center;
            padding: 8px 0;
        }

        .events-modal-warning i {
            font-size: 32px;
            color: #f59e0b;
            margin-bottom: 12px;
        }

        .events-modal-warning p {
            font-size: 14px;
            color: #374151;
            margin: 8px 0;
            line-height: 1.5;
        }

        .events-modal-text {
            text-align: center;
            font-size: 14px;
            color: #6b7280;
            margin: 0;
        }

        /* Диалоговый формат для чатов */
        .chats-dialog-list {
            padding: 0 !important;
        }

        .chat-dialog-item-wrapper {
            position: relative;
            border-bottom: 1px solid #e5e7eb;
        }

        .chat-dialog-item-wrapper:last-child {
            border-bottom: none;
        }

        .chat-dialog-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            text-decoration: none;
            color: inherit;
            transition: background-color 0.2s ease;
            cursor: pointer;
        }

        .chat-dialog-item:active {
            background-color: #f3f4f6;
        }

        .chat-dialog-delete-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: #fee2e2;
            border: none;
            border-radius: 8px;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            z-index: 10;
            color: #dc2626;
            opacity: 0;
        }

        .chat-dialog-item-wrapper:hover .chat-dialog-delete-btn {
            opacity: 1;
        }

        .chat-dialog-delete-btn:active {
            background: #fecaca;
            transform: translateY(-50%) scale(0.95);
        }

        .chat-dialog-delete-btn i {
            font-size: 16px;
        }

        .chat-dialog-avatar {
            position: relative;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            margin-right: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .chat-dialog-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .chat-dialog-avatar-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .chat-dialog-avatar-placeholder i {
            font-size: 24px;
            color: white;
            opacity: 0.9;
        }

        .chat-dialog-unread-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background: #dc2626;
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
            line-height: 1.4;
            border: 2px solid white;
        }

        .chat-dialog-content {
            flex: 1;
            min-width: 0;
        }

        .chat-dialog-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 4px;
        }

        .chat-dialog-title {
            font-size: 15px;
            font-weight: 600;
            color: #111827;
            margin: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex: 1;
        }

        .chat-dialog-time {
            font-size: 12px;
            color: #9ca3af;
            margin-left: 8px;
            flex-shrink: 0;
        }

        .chat-dialog-preview {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .chat-dialog-meta {
            font-size: 13px;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 4px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .chat-dialog-meta i {
            font-size: 12px;
            color: #9ca3af;
        }

        .chat-dialog-arrow {
            color: #9ca3af;
            font-size: 18px;
            margin-left: 8px;
            flex-shrink: 0;
        }
    }

    /* Десктоп версия - современный дизайн */
    @media (min-width: 768px) {
        .mobile-page-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* Заголовок страницы */
        .events-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 24px;
            border-bottom: 2px solid #e5e7eb;
        }

        .events-header-buttons {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .btn-my-chats {
            padding: 14px 24px;
            border-radius: 12px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            position: relative;
            text-decoration: none;
        }

        .btn-my-chats:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
            color: white;
        }

        .btn-my-chats:active {
            transform: translateY(0);
        }

        .btn-my-chats i {
            font-size: 18px;
        }

        .chats-badge {
            background: #dc2626;
            color: white;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 12px;
            min-width: 22px;
            text-align: center;
            line-height: 1.4;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.4);
        }

        .events-title {
            font-size: 36px;
            font-weight: 700;
            color: #111827;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .btn-create-event {
            padding: 14px 28px;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-create-event:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-create-event:active {
            transform: translateY(0);
        }

        .events-register-hint {
            font-size: 15px;
            color: #6b7280;
        }

        .events-register-hint a {
            color: #667eea;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }

        .events-register-hint a:hover {
            color: #764ba2;
        }

        /* Секции */
        .events-section-title {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 24px;
            margin-top: 48px;
            position: relative;
            padding-left: 16px;
        }

        .events-section-title:first-of-type {
            margin-top: 0;
        }

        .events-section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
        }

        /* Список мероприятий - сетка */
        .events-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .event-item-compact {
            background: #f0f2f5;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(11, 20, 26, 0.08);
            transition: all 0.25s ease;
            cursor: pointer;
            border: 1px solid rgba(11, 20, 26, 0.06);
            display: flex;
            flex-direction: column;
        }

        .event-item-compact:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(11, 20, 26, 0.12);
        }

        .event-item-mine {
            border: 2px solid rgba(102, 126, 234, 0.45);
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.12);
        }

        .event-item-mine:hover {
            box-shadow: 0 6px 18px rgba(102, 126, 234, 0.2);
        }

        .event-item-chat {
            border: 2px solid #667eea;
            border-left: 4px solid #667eea;
            box-shadow: 0 2px 10px rgba(102, 126, 234, 0.12);
        }

        .event-item-chat:hover {
            box-shadow: 0 6px 18px rgba(102, 126, 234, 0.18);
            border-color: #5b21b6;
        }

        .event-banner {
            width: calc(100% - 20px);
            margin: 10px 10px 0;
            height: 220px;
            overflow: hidden;
            background: #eceff1;
            position: relative;
            border-radius: 8px;
            box-shadow: 0 1px 0.5px rgba(11, 20, 26, 0.12);
        }

        .event-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 7.5px;
            display: block;
            transition: transform 0.25s ease;
        }

        .event-item-compact:hover .event-banner img {
            transform: scale(1.02);
        }

        .event-banner-placeholder {
            width: calc(100% - 20px);
            margin: 10px 10px 0;
            height: 220px;
            border-radius: 8px;
            background: #dfe5e8;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 1px 0.5px rgba(11, 20, 26, 0.1);
        }

        .event-banner-placeholder i {
            font-size: 64px;
            color: #94a3b8;
            opacity: 0.95;
        }

        .event-compact-content {
            padding: 16px 18px 18px;
            margin: 10px;
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 0.5px rgba(11, 20, 26, 0.06);
        }

        .event-compact-title {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 12px 0;
            line-height: 1.4;
        }

        .event-compact-time {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .event-compact-time i {
            font-size: 16px;
            color: #667eea;
        }

        .event-compact-location {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #667eea;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .event-compact-location i {
            font-size: 15px;
            color: #667eea;
        }

        .event-compact-price {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            gap: 8px;
            font-size: 14px;
            color: #059669;
            font-weight: 600;
            margin-top: auto;
        }

        .event-compact-price i {
            font-size: 16px;
            color: #059669;
            margin-top: 2px;
        }

        .event-compact-price-main {
            display: flex;
            flex-direction: column;
            gap: 2px;
            line-height: 1.25;
        }

        .event-price-converted {
            font-size: 12px;
            font-weight: 500;
            color: #667781;
        }

        /* Модальное окно деталей для десктопа */
        .event-modal-content {
            border-radius: 20px;
            border: none;
            overflow: hidden;
            max-width: 700px;
        }

        .event-modal-header {
            border-bottom: 1px solid #e5e7eb;
            padding: 24px 32px;
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        }

        .event-modal-header .modal-title {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
        }

        .event-modal-body {
            padding: 0;
            max-height: 85vh;
            overflow-y: auto;
        }

        .event-modal-banner {
            width: calc(100% - 32px);
            margin: 16px auto 0;
            height: 300px;
            overflow: hidden;
            background: #eceff1;
            cursor: pointer;
            position: relative;
            border-radius: 8px;
            box-shadow: 0 1px 0.5px rgba(11, 20, 26, 0.12);
        }

        .event-modal-banner::after {
            content: 'Нажмите для увеличения';
            position: absolute;
            bottom: 12px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(17, 27, 33, 0.65);
            color: white;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 11px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .event-modal-banner:hover::after {
            opacity: 1;
        }

        .event-modal-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 7.5px;
            display: block;
            transition: transform 0.25s;
        }

        .event-modal-banner:hover img {
            transform: scale(1.02);
        }

        .event-modal-banner-placeholder {
            width: calc(100% - 32px);
            margin: 16px auto 0;
            height: 300px;
            border-radius: 8px;
            background: #dfe5e8;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 1px 0.5px rgba(11, 20, 26, 0.1);
        }

        .event-modal-banner-placeholder i {
            font-size: 80px;
            color: #94a3b8;
            opacity: 0.95;
        }

        .event-modal-title {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            margin: 32px 32px 16px 32px;
            line-height: 1.3;
        }

        .event-modal-status {
            margin: 0 32px 20px 32px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .event-status-badge {
            display: inline-block;
            font-size: 13px;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 8px;
        }

        .event-status-approved {
            background: #d1fae5;
            color: #065f46;
        }

        .event-status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .event-status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .event-status-active {
            background: #dbeafe;
            color:rgb(194, 16, 16);
        }

        .event-status-past {
            background: #f3f4f6;
            color: #6b7280;
        }

        .event-modal-description {
            margin: 0 32px 24px 32px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
            border-left: 4px solid #667eea;
        }

        .event-modal-description p {
            font-size: 15px;
            color: #374151;
            line-height: 1.7;
            margin: 0;
        }

        .event-modal-info {
            padding: 24px 32px;
            border-top: 1px solid #f3f4f6;
            border-bottom: 1px solid #f3f4f6;
            background: #fafbfc;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .event-modal-info-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 16px;
            color: #374151;
        }

        .event-modal-info-item.event-price-info {
            align-items: flex-start;
        }

        .event-modal-info-item i {
            font-size: 20px;
            color: #667eea;
            width: 24px;
        }

        /* Счетчик времени для десктопа */
        .event-modal-countdown-wrapper {
            margin: 24px 32px;
            padding: 24px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 16px;
            border: 2px solid #e2e8f0;
        }

        .event-modal-countdown-label {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
            font-size: 14px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .event-modal-countdown-label i {
            font-size: 18px;
            color: #94a3b8;
        }

        .event-modal-countdown {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .countdown-timer {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .countdown-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 70px;
        }

        .countdown-value {
            font-size: 32px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            line-height: 1.2;
            color: #111827;
            background: white;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            min-width: 70px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .countdown-label {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            margin-top: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .countdown-separator {
            font-size: 28px;
            font-weight: 700;
            color: #cbd5e1;
            margin: 0 4px;
            padding-bottom: 28px;
        }

        /* Цветовые варианты счетчика */
        .countdown-timer.countdown-normal .countdown-value {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);
        }

        .countdown-timer.countdown-warning .countdown-value {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.35);
            animation: pulse-warning 2s infinite;
        }

        .countdown-timer.countdown-urgent .countdown-value {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 6px 16px rgba(239, 68, 68, 0.45);
            animation: pulse-urgent 1s infinite;
        }

        .countdown-timer.countdown-expired {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .countdown-expired-content {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
            font-weight: 600;
            color: #9ca3af;
        }

        .countdown-expired-content i {
            font-size: 20px;
        }

        @keyframes pulse-warning {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        @keyframes pulse-urgent {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 6px 16px rgba(239, 68, 68, 0.45);
            }
            50% {
                transform: scale(1.08);
                box-shadow: 0 8px 24px rgba(239, 68, 68, 0.6);
            }
        }

        .event-modal-status-wrapper {
            margin: 24px 32px;
            padding: 24px;
            background: #f8fafc;
            border-radius: 16px;
            border: 2px solid #e2e8f0;
        }

        .event-modal-status-text {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 16px;
            font-weight: 600;
            color: #9ca3af;
        }

        .event-modal-status-text i {
            font-size: 20px;
        }

        .event-modal-actions {
            padding: 24px 32px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            background: #fafbfc;
        }

        .event-modal-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px 24px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            min-width: 140px;
        }

        .event-modal-btn-full {
            width: 100%;
        }

        .event-modal-btn-chat {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .event-modal-btn-chat:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .event-modal-btn-edit {
            background: #f3f4f6;
            color: #111827;
            border: 1px solid #e5e7eb;
        }

        .event-modal-btn-edit:hover {
            background: #e5e7eb;
            transform: translateY(-2px);
        }

        .event-modal-btn-delete {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .event-modal-btn-delete:hover {
            background: #fecaca;
            transform: translateY(-2px);
        }

        .event-modal-btn i {
            font-size: 20px;
        }

        .event-modal-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #dc2626;
            color: white;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 12px;
            min-width: 22px;
            text-align: center;
            line-height: 1.4;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.4);
        }

        /* Пустое состояние */
        .events-empty {
            text-align: center;
            padding: 80px 40px;
            color: #6b7280;
            background: white;
            border-radius: 16px;
            border: 2px dashed #e5e7eb;
        }

        .events-empty i {
            font-size: 64px;
            color: #d1d5db;
            margin-bottom: 24px;
        }

        .events-empty p {
            font-size: 18px;
            margin-bottom: 12px;
            color: #6b7280;
            font-weight: 500;
        }

        .events-empty a {
            color: #667eea;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }

        .events-empty a:hover {
            color: #764ba2;
        }

        /* Предупреждение */
        .events-warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-left: 4px solid #f59e0b;
            padding: 20px 24px;
            border-radius: 12px;
            margin-bottom: 32px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.1);
        }

        .events-warning i {
            font-size: 24px;
            color: #f59e0b;
            margin-top: 2px;
        }

        .events-warning p {
            font-size: 15px;
            color: #92400e;
            margin: 0;
            line-height: 1.6;
        }

        .events-warning a {
            color: #92400e;
            font-weight: 600;
            text-decoration: underline;
        }

        /* Модальное окно создания */
        .events-modal-warning {
            text-align: center;
            padding: 16px 0;
        }

        .events-modal-warning i {
            font-size: 48px;
            color: #f59e0b;
            margin-bottom: 16px;
        }

        .events-modal-warning p {
            font-size: 16px;
            color: #374151;
            margin: 12px 0;
            line-height: 1.6;
        }

        .events-modal-text {
            text-align: center;
            font-size: 16px;
            color: #6b7280;
            margin: 0;
        }

        /* Диалоговый формат для чатов - десктоп */
        .chats-dialog-list {
            padding: 0 !important;
        }

        .chat-dialog-item-wrapper {
            position: relative;
            border-bottom: 1px solid #e5e7eb;
        }

        .chat-dialog-item-wrapper:last-child {
            border-bottom: none;
        }

        .chat-dialog-item {
            display: flex;
            align-items: center;
            padding: 16px 24px;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .chat-dialog-item:hover {
            background-color: #f9fafb;
        }

        .chat-dialog-item:active {
            background-color: #f3f4f6;
        }

        .chat-dialog-delete-btn {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: #fee2e2;
            border: none;
            border-radius: 10px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            z-index: 10;
            color: #dc2626;
            opacity: 0;
        }

        .chat-dialog-item-wrapper:hover .chat-dialog-delete-btn {
            opacity: 1;
        }

        .chat-dialog-delete-btn:hover {
            background: #fecaca;
            transform: translateY(-50%) scale(1.05);
        }

        .chat-dialog-delete-btn:active {
            background: #fca5a5;
            transform: translateY(-50%) scale(0.95);
        }

        .chat-dialog-delete-btn i {
            font-size: 18px;
        }

        .chat-dialog-avatar {
            position: relative;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            margin-right: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .chat-dialog-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .chat-dialog-avatar-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .chat-dialog-avatar-placeholder i {
            font-size: 28px;
            color: white;
            opacity: 0.9;
        }

        .chat-dialog-unread-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #dc2626;
            color: white;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 12px;
            min-width: 22px;
            text-align: center;
            line-height: 1.4;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.4);
        }

        .chat-dialog-content {
            flex: 1;
            min-width: 0;
        }

        .chat-dialog-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 6px;
        }

        .chat-dialog-title {
            font-size: 17px;
            font-weight: 600;
            color: #111827;
            margin: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex: 1;
        }

        .chat-dialog-time {
            font-size: 13px;
            color: #9ca3af;
            margin-left: 12px;
            flex-shrink: 0;
        }

        .chat-dialog-preview {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .chat-dialog-meta {
            font-size: 14px;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 6px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .chat-dialog-meta i {
            font-size: 14px;
            color: #667eea;
        }

        .chat-dialog-arrow {
            color: #9ca3af;
            font-size: 20px;
            margin-left: 12px;
            flex-shrink: 0;
            transition: transform 0.2s ease;
        }

        .chat-dialog-item:hover .chat-dialog-arrow {
            transform: translateX(4px);
            color: #667eea;
        }
    }
</style>

<!-- Модальное окно для просмотра полной информации о мероприятии -->
<div class="modal fade" id="eventDetailModal" tabindex="-1" aria-labelledby="eventDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content event-modal-content">
            <div class="modal-header event-modal-header">
                <h5 class="modal-title" id="eventDetailModalLabel">Детали мероприятия</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body event-modal-body">
                <div id="eventDetailContent"></div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для просмотра фото -->
<div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-header border-0">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 text-center">
                <img id="photoModalImage" src="" alt="Фото" class="img-fluid" style="max-height: 90vh; border-radius: 8px;">
            </div>
        </div>
    </div>
</div>


<!-- Модальное окно для создания мероприятия -->
<?php if (Helper::isLoggedIn()): ?>
    <div class="modal fade" id="createEventModal" tabindex="-1" aria-labelledby="createEventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createEventModalLabel">Создать мероприятие</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (isset($userEvent) && $userEvent): ?>
                        <div class="events-modal-warning">
                            <i class="bi bi-info-circle"></i>
                            <p><strong>У вас уже есть активное объявление о мероприятии</strong></p>
                        </div>
                    <?php else: ?>
                        <p class="events-modal-text">Вы будете перенаправлены на страницу создания мероприятия.</p>
                        <p class="events-modal-text mb-1">
                            Публикация на платформе: <strong><?= number_format($eventPublishPrice, 0, ',', ' ') ?> ₸</strong> за одно мероприятие.
                        </p>
                        <?php if (!$eventPublishPaid): ?>
                            <p class="events-modal-text mb-0" style="color: #047857; font-weight: 600;">
                                <i class="bi bi-gift"></i> Сейчас без оплаты — тариф на будущее.
                            </p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                    <?php if (!isset($userEvent) || !$userEvent): ?>
                        <a href="<?= BASE_URL ?>events/create" class="btn btn-primary">Создать</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    // Глобальные константы
    const BASE_URL = '<?= BASE_URL ?>';
    const UPLOAD_DIR = '<?= UPLOAD_DIR ?>';
    const IS_LOGGED_IN = <?= Helper::isLoggedIn() ? 'true' : 'false' ?>;

    let __aruKztRates = null;

    function guessViewerCurrency() {
        const lang = (navigator.languages && navigator.languages[0]) || navigator.language || 'en-US';
        if (/kk|kz/i.test(lang)) return 'KZT';
        if (/^ru/i.test(lang)) return 'RUB';
        try {
            const loc = new Intl.Locale(lang);
            const region = loc.maximize().region;
            const map = { US: 'USD', GB: 'GBP', DE: 'EUR', FR: 'EUR', IT: 'EUR', ES: 'EUR', NL: 'EUR', BE: 'EUR', AT: 'EUR',
                PL: 'PLN', TR: 'TRY', JP: 'JPY', CN: 'CNY', IN: 'INR', BR: 'BRL', KR: 'KRW', TH: 'THB', AE: 'AED', SA: 'SAR',
                KZ: 'KZT', RU: 'RUB', UA: 'UAH', UZ: 'UZS', BY: 'BYN', KG: 'KGS', CH: 'CHF', SE: 'SEK', NO: 'NOK', DK: 'DKK',
                FI: 'EUR', PT: 'EUR', IE: 'EUR', GR: 'EUR', IL: 'ILS', ZA: 'ZAR', AU: 'AUD', NZ: 'NZD', CA: 'CAD', MX: 'MXN',
                AR: 'ARS', CL: 'CLP', CO: 'COP', PE: 'PEN', EG: 'EGP', NG: 'NGN', MY: 'MYR', SG: 'SGD', ID: 'IDR', PH: 'PHP',
                VN: 'VND', PK: 'PKR', BD: 'BDT' };
            if (region && map[region]) return map[region];
        } catch (e) { /* noop */ }
        return 'USD';
    }

    function toKzt(amount, from, rates) {
        if (!rates || !from) return null;
        if (from === 'KZT') return amount;
        const r = rates[from];
        if (!r || r === 0) return null;
        return amount / r;
    }

    function fromKzt(kzt, to, rates) {
        if (!rates || !to) return null;
        if (to === 'KZT') return kzt;
        const r = rates[to];
        if (r == null) return null;
        return kzt * r;
    }

    function formatMoneyAmount(val, cur) {
        try {
            return new Intl.NumberFormat(navigator.language || 'ru-RU', {
                style: 'currency',
                currency: cur,
                maximumFractionDigits: 2
            }).format(val);
        } catch (e) {
            return (Math.round(val * 100) / 100).toFixed(2) + ' ' + cur;
        }
    }

    function fillPriceConversionLine(targetEl, amount, fromCur, toCur, rates) {
        if (!targetEl || !rates || !fromCur || !toCur || toCur === fromCur) {
            if (targetEl) targetEl.textContent = '';
            return;
        }
        const kzt = toKzt(amount, fromCur, rates);
        if (kzt == null) { targetEl.textContent = ''; return; }
        const out = fromKzt(kzt, toCur, rates);
        if (out == null) { targetEl.textContent = ''; return; }
        targetEl.textContent = '≈ ' + formatMoneyAmount(out, toCur);
    }

    function applyCompactEventPriceConversions(rates) {
        const toCur = guessViewerCurrency();
        document.querySelectorAll('.event-compact-price[data-amount][data-currency]').forEach(function(row) {
            const amt = parseFloat(row.getAttribute('data-amount'));
            const from = row.getAttribute('data-currency') || 'KZT';
            const main = row.querySelector('.event-compact-price-main');
            if (!main || !amt) return;
            let conv = main.querySelector('.event-price-converted');
            if (!conv) {
                conv = document.createElement('div');
                conv.className = 'event-price-converted';
                main.appendChild(conv);
            }
            fillPriceConversionLine(conv, amt, from, toCur, rates);
        });
    }

    function refreshModalPriceConversion() {
        const info = document.querySelector('#eventDetailContent .event-price-info');
        if (!info || !__aruKztRates) return;
        const conv = info.querySelector('.event-price-converted');
        if (!conv) return;
        const amt = parseFloat(info.getAttribute('data-amount') || '0');
        const from = info.getAttribute('data-currency') || 'KZT';
        fillPriceConversionLine(conv, amt, from, guessViewerCurrency(), __aruKztRates);
    }

    document.addEventListener('DOMContentLoaded', function() {
        let expiredCleanupInProgress = false;
        
        function updateCountdown() {
            const timers = document.querySelectorAll('.countdown-timer');

            timers.forEach(function(timer) {
                const deadline = new Date(timer.getAttribute('data-deadline')).getTime();
                const now = new Date().getTime();
                const distance = deadline - now;

                if (distance < 0) {
                    timer.className = 'countdown-timer countdown-expired';
                    timer.innerHTML = 
                        '<div class="countdown-expired-content">' +
                        '<i class="bi bi-clock-history"></i>' +
                        '<span>Истекло</span>' +
                        '</div>';
                    
                    // Защита от множественных запросов и бесконечной перезагрузки
                    if (expiredCleanupInProgress) return;
                    
                    const eventId = timer.getAttribute('data-event-id');
                    const reloadKey = 'expired_reload_event_' + eventId;
                    if (sessionStorage.getItem(reloadKey)) {
                        // Уже пытались перезагрузить - скрываем карточку
                        const card = timer.closest('.event-card, .chat-dialog-item-wrapper');
                        if (card) card.style.display = 'none';
                        sessionStorage.removeItem(reloadKey);
                        return;
                    }
                    
                    expiredCleanupInProgress = true;
                    sessionStorage.setItem(reloadKey, '1');
                    
                    // Вызываем AJAX для удаления просроченных записей из базы
                    fetch(BASE_URL + 'dates/deleteExpired', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).then(function(response) {
                        return response.json();
                    }).then(function(data) {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            const card = timer.closest('.event-card, .chat-dialog-item-wrapper');
                            if (card) card.style.display = 'none';
                            sessionStorage.removeItem(reloadKey);
                        }
                    }).catch(function() {
                        const card = timer.closest('.event-card, .chat-dialog-item-wrapper');
                        if (card) card.style.display = 'none';
                        sessionStorage.removeItem(reloadKey);
                    });
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                let countdownClass = 'countdown-normal';
                if (distance < 3600000) {
                    countdownClass = 'countdown-urgent';
                } else if (distance < 86400000) {
                    countdownClass = 'countdown-warning';
                }

                const formatValue = (val) => String(val).padStart(2, '0');

                timer.className = 'countdown-timer ' + countdownClass;
                timer.innerHTML = 
                    '<div class="countdown-item"><div class="countdown-value">' + formatValue(days) + '</div><div class="countdown-label">дн</div></div>' +
                    '<div class="countdown-separator">:</div>' +
                    '<div class="countdown-item"><div class="countdown-value">' + formatValue(hours) + '</div><div class="countdown-label">ч</div></div>' +
                    '<div class="countdown-separator">:</div>' +
                    '<div class="countdown-item"><div class="countdown-value">' + formatValue(minutes) + '</div><div class="countdown-label">мин</div></div>' +
                    '<div class="countdown-separator">:</div>' +
                    '<div class="countdown-item"><div class="countdown-value">' + formatValue(seconds) + '</div><div class="countdown-label">сек</div></div>';
            });
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);

        // Автоматическое определение страны по координатам для карточек мероприятий
        document.querySelectorAll('.event-compact-location[data-lat][data-lon]').forEach(function(locationEl) {
            const lat = parseFloat(locationEl.getAttribute('data-lat'));
            const lon = parseFloat(locationEl.getAttribute('data-lon'));
            const currentCity = locationEl.getAttribute('data-city') || '';
            const currentCountry = locationEl.getAttribute('data-country') || '';
            
            if (lat && lon) {
                getCountryByCoordinates(lat, lon).then(function(locationData) {
                    if (locationData && locationData.country) {
                        const locationText = locationEl.querySelector('.location-text');
                        if (locationText) {
                            // Используем город из данных или текущий город
                            const city = locationData.city || currentCity;
                            const country = locationData.country;
                            
                            if (city && country) {
                                locationText.textContent = city + ', ' + country;
                            } else if (country) {
                                locationText.textContent = country;
                            }
                        }
                    }
                });
            }
        });

        // Функция для открытия фото в модальном окне
        window.openPhotoModal = function(photoUrl) {
            const modal = new bootstrap.Modal(document.getElementById('photoModal'));
            document.getElementById('photoModalImage').src = photoUrl;
            modal.show();
        };

        // Добавляем обработчики клика на карточки мероприятий
        document.querySelectorAll('.event-item-compact').forEach(function(card) {
            card.addEventListener('click', function(e) {
                // Предотвращаем клик, если кликнули на ссылку или кнопку внутри
                if (e.target.closest('a, button')) {
                    return;
                }
                
                const eventData = JSON.parse(this.getAttribute('data-event'));
                const isMine = this.getAttribute('data-is-mine') === 'true';
                openEventModal(eventData, isMine);
            });
        });

        fetch(BASE_URL + 'api/exchange-rates')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data && data.rates) {
                    __aruKztRates = data.rates;
                    applyCompactEventPriceConversions(data.rates);
                }
            })
            .catch(function() { /* офлайн / блокировка — остаётся только цена в валюте объявления */ });

        // Функция для удаления чата мероприятия
        window.deleteEventChat = function(eventId, e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }

            if (!confirm('Вы уверены, что хотите удалить этот чат? Все ваши сообщения в этом чате будут удалены.')) {
                return;
            }

            const formData = new FormData();
            formData.append('event_id', eventId);

            fetch(BASE_URL + 'messages/deleteEventChat', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Удаляем элемент из списка
                    const wrapper = document.getElementById('chat-wrapper-' + eventId);
                    if (wrapper) {
                        wrapper.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        wrapper.style.opacity = '0';
                        wrapper.style.transform = 'translateX(100%)';
                        setTimeout(function() {
                            wrapper.remove();
                            
                            // Если список пуст, перезагружаем страницу
                            const list = document.querySelector('.chats-dialog-list');
                            if (list && list.querySelectorAll('.chat-dialog-item-wrapper').length === 0) {
                                window.location.reload();
                            }
                        }, 300);
                    }
                } else {
                    alert(data.error || 'Не удалось удалить чат. Попробуйте еще раз.');
                }
            })
            .catch(error => {
                console.error('Ошибка при удалении чата:', error);
                alert('Произошла ошибка при удалении чата. Попробуйте еще раз.');
            });
        };

        // Функция для открытия детальной информации о мероприятии
        window.openEventModal = function(event, isMine) {
            const modal = new bootstrap.Modal(document.getElementById('eventDetailModal'));
            const content = document.getElementById('eventDetailContent');
            
            let html = '';
            
            // Баннер
            if (event.photo) {
                const photoUrl = BASE_URL + UPLOAD_DIR + 'photos/' + event.photo;
                html += '<div class="event-modal-banner" onclick="openPhotoModal(\'' + photoUrl + '\')">';
                html += '<img src="' + photoUrl + '" alt="Баннер">';
                html += '</div>';
            } else {
                html += '<div class="event-modal-banner-placeholder">';
                html += '<i class="bi bi-calendar-event"></i>';
                html += '</div>';
            }
            
            // Заголовок
            html += '<h4 class="event-modal-title">' + escapeHtml(event.title) + '</h4>';
            
            // Статус
            if (isMine) {
                let statusHtml = '';
                switch (event.status || 'pending') {
                    case 'approved':
                        statusHtml = '<span class="event-status-badge event-status-approved">Одобрено</span>';
                        break;
                    case 'rejected':
                        statusHtml = '<span class="event-status-badge event-status-rejected">Отклонено</span>';
                        break;
                    default:
                        statusHtml = '<span class="event-status-badge event-status-pending">На модерации</span>';
                }
                if (new Date(event.event_date).getTime() < Date.now()) {
                    statusHtml += '<span class="event-status-badge event-status-past">Прошло</span>';
                } else if ((event.status || 'pending') === 'approved') {
                    statusHtml += '<span class="event-status-badge event-status-active">Активно</span>';
                }
                html += '<div class="event-modal-status">' + statusHtml + '</div>';
            }
            
            // Описание
            if (event.description) {
                html += '<div class="event-modal-description">';
                html += '<p>' + escapeHtml(event.description) + '</p>';
                html += '</div>';
            }
            
            // Информация
            html += '<div class="event-modal-info">';
            
            // Дата и время
            const dateTime = new Date(event.event_date);
            const dateStr = dateTime.toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
            const timeStr = dateTime.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
            html += '<div class="event-modal-info-item">';
            html += '<i class="bi bi-calendar3"></i>';
            html += '<span>' + dateStr + ' в ' + timeStr + '</span>';
            html += '</div>';
            
            // Адрес (форматируем: Страна, Город, Улица, номер дома)
            if (event.location) {
                const formattedAddress = formatAddress(event.location, event.latitude, event.longitude);
                html += '<div class="event-modal-info-item">';
                html += '<i class="bi bi-geo-alt"></i>';
                html += '<span>' + escapeHtml(formattedAddress) + '</span>';
                html += '</div>';
                
                // Определяем страну по координатам (асинхронно)
                if (event.latitude && event.longitude) {
                    getCountryByCoordinates(event.latitude, event.longitude).then(function(locationData) {
                        if (locationData && locationData.country) {
                            // Обновляем информацию о стране/городе создания мероприятия
                            const locationInfo = document.querySelector('#eventDetailContent .event-location-info');
                            if (locationInfo) {
                                const cityText = locationData.city ? locationData.city + ', ' : '';
                                locationInfo.innerHTML = '<i class="bi bi-globe"></i><span>Создано в: ' + escapeHtml(cityText + locationData.country) + '</span>';
                            }
                            
                            // Если страна - Россия, обновляем валюту на рубль
                            const countryLower = locationData.country.toLowerCase();
                            if (countryLower.includes('россия') || countryLower.includes('russia') || countryLower.includes('российская')) {
                                const priceInfo = document.querySelector('#eventDetailContent .event-price-info .event-price-original');
                                if (priceInfo && event.price) {
                                    const priceValue = parseFloat(event.price).toLocaleString('ru-RU');
                                    priceInfo.textContent = priceValue + ' ₽';
                                }
                                refreshModalPriceConversion();
                            }
                        } else {
                            // Если не удалось определить по координатам, используем данные из адреса
                            const locationParts = event.location.split(',').map(p => p.trim()).filter(p => p.length > 0);
                            if (locationParts.length >= 2) {
                                const country = locationParts[locationParts.length - 1];
                                const city = locationParts[locationParts.length - 2];
                                const locationInfo = document.querySelector('#eventDetailContent .event-location-info');
                                if (locationInfo) {
                                    locationInfo.innerHTML = '<i class="bi bi-globe"></i><span>Создано в: ' + escapeHtml(city) + ', ' + escapeHtml(country) + '</span>';
                                }
                                
                                // Проверяем адрес на наличие России
                                const countryLower = country.toLowerCase();
                                if (countryLower.includes('россия') || countryLower.includes('russia') || countryLower.includes('российская')) {
                                    const priceInfo = document.querySelector('#eventDetailContent .event-price-info .event-price-original');
                                    if (priceInfo && event.price) {
                                        const priceValue = parseFloat(event.price).toLocaleString('ru-RU');
                                        priceInfo.textContent = priceValue + ' ₽';
                                    }
                                    refreshModalPriceConversion();
                                }
                            }
                        }
                    });
                } else {
                    // Если координат нет, используем данные из адреса
                    const locationParts = event.location.split(',').map(p => p.trim()).filter(p => p.length > 0);
                    if (locationParts.length >= 2) {
                        const country = locationParts[locationParts.length - 1];
                        const city = locationParts[locationParts.length - 2];
                        html += '<div class="event-modal-info-item" style="color: #667eea; font-weight: 500;">';
                        html += '<i class="bi bi-globe"></i>';
                        html += '<span>Создано в: ' + escapeHtml(city) + ', ' + escapeHtml(country) + '</span>';
                        html += '</div>';
                        
                        // Проверяем адрес на наличие России и обновляем валюту
                        const countryLower = country.toLowerCase();
                        if ((countryLower.includes('россия') || countryLower.includes('russia') || countryLower.includes('российская')) && event.price) {
                            setTimeout(function() {
                                const priceInfo = document.querySelector('#eventDetailContent .event-price-info .event-price-original');
                                if (priceInfo) {
                                    const priceValue = parseFloat(event.price).toLocaleString('ru-RU');
                                    priceInfo.textContent = priceValue + ' ₽';
                                }
                                refreshModalPriceConversion();
                            }, 100);
                        }
                    }
                }
            }
            
            // Цена
            if (event.price) {
                let currencyCode = event.currency_code || 'KZT';
                
                // Проверяем адрес на наличие России
                if (event.location) {
                    const locationParts = event.location.split(',').map(p => p.trim()).filter(p => p.length > 0);
                    const countryFromAddress = locationParts.length > 0 ? locationParts[locationParts.length - 1].toLowerCase() : '';
                    if (countryFromAddress.includes('россия') || countryFromAddress.includes('russia') || countryFromAddress.includes('российская')) {
                        currencyCode = 'RUB';
                    }
                }
                
                const currencyMap = {
                    'KZT': '₸',
                    'RUB': '₽',
                    'BYN': 'Br',
                    'UAH': '₴',
                    'USD': '$',
                    'EUR': '€',
                    'GBP': '£',
                    'CNY': '¥',
                    'JPY': '¥',
                    'TRY': '₺',
                    'KGS': 'сом',
                    'UZS': 'сум'
                };
                let currencySymbol = currencyMap[currencyCode] || '₸';
                const priceAmt = parseFloat(event.price);
                html += '<div class="event-modal-info-item event-price-info" data-amount="' + priceAmt + '" data-currency="' + String(currencyCode || 'KZT').replace(/"/g, '') + '">';
                html += '<i class="bi bi-currency-exchange"></i>';
                html += '<div class="event-compact-price-main">';
                html += '<span class="event-price-original">' + priceAmt.toLocaleString('ru-RU') + ' ' + currencySymbol + '</span>';
                html += '<div class="event-price-converted"></div>';
                html += '</div></div>';
            }
            
            // Расстояние (только для других мероприятий)
            if (!isMine && event.distance && event.distance > 0) {
                html += '<div class="event-modal-info-item">';
                html += '<i class="bi bi-rulers"></i>';
                html += '<span>' + parseFloat(event.distance).toFixed(1) + ' км</span>';
                html += '</div>';
            }
            
            // Счетчик времени
            if (new Date(event.event_date).getTime() >= Date.now() && (event.status || 'pending') === 'approved') {
                html += '<div class="event-modal-countdown-wrapper">';
                html += '<div class="event-modal-countdown-label">';
                html += '<i class="bi bi-clock"></i>';
                html += '<span>До дедлайна</span>';
                html += '</div>';
                html += '<div class="event-modal-countdown">';
                html += '<div class="countdown-timer" data-deadline="' + event.event_date + '" data-event-id="' + event.id + '">';
                html += '<div class="countdown-item"><div class="countdown-value">-</div><div class="countdown-label">дн</div></div>';
                html += '<div class="countdown-separator">:</div>';
                html += '<div class="countdown-item"><div class="countdown-value">-</div><div class="countdown-label">ч</div></div>';
                html += '<div class="countdown-separator">:</div>';
                html += '<div class="countdown-item"><div class="countdown-value">-</div><div class="countdown-label">мин</div></div>';
                html += '<div class="countdown-separator">:</div>';
                html += '<div class="countdown-item"><div class="countdown-value">-</div><div class="countdown-label">сек</div></div>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
            } else if (new Date(event.event_date).getTime() < Date.now()) {
                html += '<div class="event-modal-status-wrapper">';
                html += '<div class="event-modal-status-text">';
                html += '<i class="bi bi-x-circle"></i>';
                html += '<span>Прошло</span>';
                html += '</div>';
                html += '</div>';
            }
            
            html += '</div>';
            
            // Кнопки действий
            html += '<div class="event-modal-actions">';
            if (isMine) {
                const unreadCount = event.unread_count || 0;
                html += '<a href="' + BASE_URL + 'messages/event?event_id=' + event.id + '" class="event-modal-btn event-modal-btn-chat">';
                html += '<i class="bi bi-chat"></i>';
                html += '<span>Чат</span>';
                if (unreadCount > 0) {
                    html += '<span class="event-modal-badge">' + (unreadCount > 99 ? '99+' : unreadCount) + '</span>';
                }
                html += '</a>';
                html += '<a href="' + BASE_URL + 'events/edit?id=' + event.id + '" class="event-modal-btn event-modal-btn-edit">';
                html += '<i class="bi bi-pencil"></i>';
                html += '<span>Редактировать</span>';
                html += '</a>';
                html += '<a href="' + BASE_URL + 'events/delete?id=' + event.id + '" class="event-modal-btn event-modal-btn-delete" onclick="return confirm(\'Вы уверены, что хотите удалить это мероприятие?\')">';
                html += '<i class="bi bi-trash"></i>';
                html += '<span>Удалить</span>';
                html += '</a>';
            } else {
                if (IS_LOGGED_IN) {
                    const unreadCount = event.unread_count || 0;
                    html += '<a href="' + BASE_URL + 'messages/event?event_id=' + event.id + '" class="event-modal-btn event-modal-btn-chat event-modal-btn-full">';
                    html += '<i class="bi bi-chat"></i>';
                    html += '<span>Чат мероприятия</span>';
                    if (unreadCount > 0) {
                        html += '<span class="event-modal-badge">' + (unreadCount > 99 ? '99+' : unreadCount) + '</span>';
                    }
                    html += '</a>';
                } else {
                    html += '<button class="event-modal-btn event-modal-btn-chat event-modal-btn-full" onclick="alert(\'Зарегистрируйтесь чтобы написать сообщение\')">';
                    html += '<i class="bi bi-chat"></i>';
                    html += '<span>Чат мероприятия</span>';
                    html += '</button>';
                }
            }
            html += '</div>';
            
            content.innerHTML = html;
            updateCountdown();
            modal.show();
            window.requestAnimationFrame(function() {
                refreshModalPriceConversion();
            });
        };
        
        // Кэш для reverse geocoding
        const countryCache = {};
        
        /**
         * Определение страны по координатам через reverse geocoding
         */
        async function getCountryByCoordinates(lat, lon) {
            if (!lat || !lon) return null;
            
            const cacheKey = lat.toFixed(4) + ',' + lon.toFixed(4);
            if (countryCache[cacheKey]) {
                return countryCache[cacheKey];
            }
            
            try {
                const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&addressdetails=1`;
                const response = await fetch(url, {
                    headers: {
                        'User-Agent': 'Tanisu App'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    const address = data.address || {};
                    const country = address.country || '';
                    const city = address.city || address.town || address.village || '';
                    
                    if (country) {
                        countryCache[cacheKey] = { country: country, city: city };
                        return countryCache[cacheKey];
                    }
                }
            } catch (error) {
                console.error('Ошибка reverse geocoding:', error);
            }
            
            return null;
        }
        
        function formatAddress(address, lat, lon) {
            if (!address) return '';
            
            // Разделяем адрес по запятым
            const parts = address.split(',').map(part => part.trim()).filter(part => part.length > 0);
            
            if (parts.length < 3) {
                // Если частей меньше 3, возвращаем как есть
                return address;
            }
            
            // Формат в БД: [доп.инфо?], номер дома, улица, город, страна
            // Последний элемент - страна (может быть неправильной)
            // Предпоследний - город
            // Пред-предпоследний - улица
            // Пред-пред-предпоследний - номер дома
            
            let country = parts[parts.length - 1] || '';
            const city = parts[parts.length - 2] || '';
            const street = parts[parts.length - 3] || '';
            const houseNumber = parts.length >= 4 ? parts[parts.length - 4] : '';
            
            // Если есть координаты, используем их для определения правильной страны
            // (это будет работать асинхронно, но для отображения в модальном окне)
            
            // Собираем в нужном формате: Страна, Город, Улица, номер дома
            const formattedParts = [];
            if (country) formattedParts.push(country);
            if (city) formattedParts.push(city);
            if (street) formattedParts.push(street);
            if (houseNumber) formattedParts.push(houseNumber);
            
            // Если есть дополнительная информация (в начале), добавляем её в конец
            if (parts.length > 4) {
                const additionalInfo = parts.slice(0, parts.length - 4).join(', ');
                if (additionalInfo) {
                    formattedParts.push(additionalInfo);
                }
            }
            
            return formattedParts.join(', ');
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

    // Автоматическая прокрутка к мероприятию при загрузке страницы с якорем
        if (window.location.hash) {
            const eventId = window.location.hash.substring(1);
            const eventElement = document.getElementById(eventId);
            if (eventElement) {
                setTimeout(function() {
                    eventElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    eventElement.style.transition = 'box-shadow 0.5s';
                    eventElement.style.boxShadow = '0 0 20px rgba(232, 7, 7, 0.5)';
                    setTimeout(function() {
                        eventElement.style.boxShadow = '';
                    }, 2000);
                }, 100);
            }
        }
    });
</script>

<?php
$content = ob_get_clean();
$title = 'Мероприятия';
include __DIR__ . '/../layout.php';
?>

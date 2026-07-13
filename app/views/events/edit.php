<?php

/**
 * РЕДАКТИРОВАНИЕ МЕРОПРИЯТИЯ
 */

// Функция для парсинга адреса
function parseEventAddress($address) {
    if (empty($address)) {
        return [
            'city' => '',
            'street' => '',
            'house_number' => '',
            'location' => '',
            'country' => ''
        ];
    }

    // Разделяем адрес по запятым
    $parts = array_map('trim', explode(',', $address));
    $parts = array_filter($parts, function($part) {
        return !empty($part);
    });
    $parts = array_values($parts);

    $result = [
        'city' => '',
        'street' => '',
        'house_number' => '',
        'location' => '',
        'country' => ''
    ];

    if (count($parts) >= 3) {
        // Формат: [доп.инфо?], номер дома, улица, город, страна
        // Или: номер дома, улица, город, страна
        
        // Последний элемент - страна
        $result['country'] = end($parts);
        
        // Предпоследний - город
        if (count($parts) >= 2) {
            $result['city'] = $parts[count($parts) - 2];
        }
        
        // Пред-предпоследний - улица
        if (count($parts) >= 3) {
            $result['street'] = $parts[count($parts) - 3];
        }
        
        // Пред-пред-предпоследний - номер дома
        if (count($parts) >= 4) {
            $result['house_number'] = $parts[count($parts) - 4];
        }
        
        // Все остальное до номера дома - дополнительная информация
        if (count($parts) > 4) {
            $result['location'] = implode(', ', array_slice($parts, 0, count($parts) - 4));
        }
    } else {
        // Если формат не распознан, пытаемся найти город и улицу по ключевым словам
        foreach ($parts as $part) {
            if (preg_match('/(город|г\.|city|town)/i', $part)) {
                $result['city'] = preg_replace('/(город|г\.|city|town)/i', '', $part);
            } elseif (preg_match('/(улица|ул\.|ул|street|st|road|rd)/i', $part)) {
                $result['street'] = preg_replace('/(улица|ул\.|ул|street|st|road|rd)/i', '', $part);
            } elseif (preg_match('/^\d+[а-яa-z]*$/i', $part)) {
                $result['house_number'] = $part;
            }
        }
    }

    return $result;
}

$parsedAddress = parseEventAddress($event['location'] ?? '');

$eventPublishPrice = defined('EVENT_PUBLISH_PRICE_KZT') ? (int) EVENT_PUBLISH_PRICE_KZT : 500;
$eventPublishPaid = defined('EVENT_PUBLISH_PAYMENT_ENABLED') && EVENT_PUBLISH_PAYMENT_ENABLED;

ob_start();
?>

<style>
    /* Мобильная оптимизация формы редактирования мероприятия */
    @media (max-width: 767px) {
        .event-edit-container {
            margin: 0;
            padding: 16px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .event-edit-container h2 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 24px;
            color: #2d3748;
            text-align: center;
            letter-spacing: -0.5px;
            padding-top: 8px;
        }

        .event-edit-form .card {
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            border: none;
            overflow: hidden;
            background: #ffffff;
        }

        .event-edit-form .card-body {
            padding: 24px 20px;
        }

        .event-edit-form .mb-3 {
            margin-bottom: 24px !important;
        }

        .event-edit-form .form-label {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 10px;
            color: #2d3748;
            display: block;
            letter-spacing: -0.2px;
        }

        .event-edit-form .form-label::after {
            content: '';
            display: block;
            width: 40px;
            height: 3px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
            margin-top: 4px;
        }

        .event-edit-form .form-control,
        .event-edit-form .form-select {
            font-size: 16px;
            /* Предотвращает зум на iOS */
            padding: 14px 18px;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            margin-bottom: 0;
            background-color: #f8fafc;
            transition: all 0.3s ease;
            color: #2d3748;
        }

        .event-edit-form textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .event-edit-form .form-control:focus,
        .event-edit-form .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
            background-color: #ffffff;
            outline: none;
        }

        .event-edit-form .form-control::placeholder {
            color: #a0aec0;
            font-size: 15px;
        }

        .event-edit-form .form-text,
        .event-edit-form .text-muted {
            font-size: 13px;
            margin-top: 8px;
            margin-bottom: 0;
            display: block;
            color: #718096;
            line-height: 1.5;
            padding-left: 4px;
        }

        .event-edit-form .alert {
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 14px;
            margin-bottom: 20px;
            border: none;
            line-height: 1.6;
        }

        .event-edit-form .alert-danger {
            background-color: #fed7d7;
            color: #c53030;
        }

        .event-edit-form .alert-warning {
            background-color: #feebc8;
            color: #c05621;
        }

        .event-edit-form .alert-info {
            background-color: #bee3f8;
            color: #2c5282;
        }

        .event-edit-form .alert-success {
            background-color: #c6f6d5;
            color: #22543d;
        }

        .event-edit-form .btn {
            font-size: 17px;
            padding: 16px 24px;
            border-radius: 14px;
            font-weight: 700;
            width: 100%;
            margin-top: 12px;
            transition: all 0.3s ease;
            border: none;
            text-transform: none;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .event-edit-form .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
        }

        .event-edit-form .btn-primary:active {
            transform: translateY(2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .event-edit-form .btn-secondary {
            width: 100%;
            padding: 14px 20px;
            font-size: 16px;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: #ffffff;
            font-weight: 600;
            border: none;
            box-shadow: 0 4px 12px rgba(245, 87, 108, 0.3);
        }

        .event-edit-form .btn-secondary:active {
            transform: translateY(2px);
            box-shadow: 0 2px 8px rgba(245, 87, 108, 0.4);
        }

        .event-edit-form .btn-outline-secondary {
            width: 100%;
            padding: 14px 20px;
            font-size: 16px;
            margin-top: 12px;
            margin-left: 0 !important;
            background: #ffffff;
            color: #718096;
            border: 2px solid #e2e8f0;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .event-edit-form .btn-outline-secondary:active {
            transform: translateY(2px);
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
            background-color: #f7fafc;
        }

        .event-edit-form .btn-outline-primary {
            border: 2px solid #667eea;
            color: #667eea;
            background: #ffffff;
            font-weight: 600;
            min-width: 50px;
        }

        .event-edit-form .btn-outline-primary:hover {
            background: #667eea;
            color: #ffffff;
        }

        .event-edit-form .btn-outline-primary:active {
            transform: translateY(2px);
            background: #5568d3;
        }

        .event-edit-form .btn-outline-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .event-edit-form #locationStatus {
            margin-top: 12px;
        }

        .event-edit-form #locationStatus .alert {
            margin-bottom: 0;
        }

        /* Улучшение datetime-local на мобильных */
        .event-edit-form input[type="datetime-local"],
        .event-edit-form input[type="number"] {
            min-height: 52px;
            cursor: pointer;
        }

        /* Улучшение иконок */
        .event-edit-form .bi {
            margin-right: 8px;
            font-size: 18px;
            vertical-align: middle;
        }

        /* Разделители между секциями */
        .event-edit-form .mb-3:not(:last-child) {
            position: relative;
        }

        .event-edit-form .mb-3:not(:last-child)::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
        }

        /* Улучшение для ошибок */
        .event-edit-container>.alert {
            border-radius: 14px;
            padding: 16px 20px;
            margin-bottom: 20px;
            font-size: 15px;
            box-shadow: 0 4px 12px rgba(197, 48, 48, 0.15);
        }

        /* Текущее фото */
        .current-photo {
            margin-top: 12px;
            padding: 12px;
            background: #f7fafc;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
        }

        .current-photo img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
        }
    }

    /* Десктоп версия */
    @media (min-width: 768px) {
        .event-edit-container {
            max-width: 700px;
            margin: 20px auto;
        }

        .event-edit-form .card-body {
            padding: 30px;
        }
    }

    .event-publish-tariff {
        background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 55%, #eef2ff 100%);
        border: 1px solid #c7d2fe;
        border-radius: 16px;
        padding: 18px 16px;
        margin-bottom: 4px;
        box-shadow: 0 8px 22px rgba(79, 70, 229, 0.1);
    }

    .event-publish-tariff h6 {
        margin: 0 0 10px 0;
        color: #1f2937;
        font-weight: 800;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .event-publish-tariff h6 i {
        color: #6366f1;
    }

    .event-publish-tariff-amount {
        font-size: 28px;
        font-weight: 800;
        color: #4338ca;
        letter-spacing: -0.5px;
        line-height: 1.2;
    }

    .event-publish-tariff-note {
        margin-top: 10px;
        font-size: 13px;
        color: #475569;
        line-height: 1.45;
    }

    .event-publish-tariff-free {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 12px;
        padding: 6px 12px;
        border-radius: 999px;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #047857;
        font-size: 13px;
        font-weight: 700;
    }
</style>

<div class="event-edit-container mt-4">
    <h2 class="mb-4">Редактировать мероприятие</h2>

    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        <strong>Важно:</strong> После редактирования мероприятие будет отправлено на повторную модерацию администратору или менеджеру.
        Вы получите уведомление после проверки.
        <span class="d-block mt-2">
            Размещение на платформе: <strong><?= number_format($eventPublishPrice, 0, ',', ' ') ?> ₸</strong> за мероприятие.
            <?php if (!$eventPublishPaid): ?>
                <strong>Сейчас — без оплаты</strong>.
            <?php endif; ?>
        </span>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?= Helper::escape($error) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= Helper::escape($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>events/update?id=<?= (int)$event['id'] ?>" class="event-edit-form" enctype="multipart/form-data">
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="title" class="form-label">Название мероприятия *</label>
                    <input type="text"
                        class="form-control"
                        id="title"
                        name="title"
                        required
                        value="<?= Helper::escape($event['title'] ?? '') ?>"
                        placeholder="Например: Концерт в парке">
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Описание *</label>
                    <textarea class="form-control"
                        id="description"
                        name="description"
                        rows="5"
                        required
                        placeholder="Опишите ваше мероприятие..."><?= Helper::escape($event['description'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="photo" class="form-label">Баннер мероприятия</label>
                    <?php if (!empty($event['photo'])): ?>
                        <div class="current-photo">
                            <small class="text-muted d-block mb-2">Текущее фото:</small>
                            <img src="<?= BASE_URL ?>uploads/photos/<?= Helper::escape($event['photo']) ?>" alt="Текущее фото мероприятия">
                        </div>
                    <?php endif; ?>
                    <input type="file"
                        class="form-control mt-2"
                        id="photo"
                        name="photo"
                        accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                    <div class="form-text">Максимальный размер: 10MB. Форматы: JPG, PNG, GIF, WebP. Оставьте пустым, чтобы сохранить текущее фото.</div>
                    <div id="photo-preview" class="mt-3" style="display: none;">
                        <img id="photo-preview-img" src="" alt="Предпросмотр" style="max-width: 100%; max-height: 300px; border-radius: 8px; border: 2px solid #e2e8f0;">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="event_date" class="form-label">Дата и время проведения *</label>
                    <input type="datetime-local"
                        class="form-control"
                        id="event_date"
                        name="event_date"
                        max="<?= Helper::getMaxPlanningDateTimeLocal() ?>"
                        required
                        value="<?= !empty($event['event_date']) ? date('Y-m-d\TH:i', strtotime($event['event_date'])) : '' ?>">
                </div>

                <div class="mb-3" style="position: relative;">
                    <label for="city" class="form-label">Город *</label>
                    <!-- Скрытое поле для отправки формы -->
                    <input type="text"
                        class="form-control"
                        id="city"
                        name="city"
                        required
                        value="<?= Helper::escape($parsedAddress['city']) ?>"
                        autocomplete="off"
                        style="position: absolute; left: -9999px; opacity: 0; pointer-events: none; width: 0; height: 0; padding: 0; border: none; overflow: hidden;">
                    
                    <div class="city-location-container">
                        <button type="button" 
                                class="btn btn-outline-primary city-location-btn" 
                                id="detectLocationBtn"
                                onclick="detectLocation()"
                                style="width: 100%; padding: 16px 20px; border-radius: 12px; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2); transition: all 0.3s ease; font-weight: 600;">
                            <i class="bi bi-geo-alt-fill" style="font-size: 18px;"></i>
                            <span>Геолокация</span>
                        </button>
                        <small class="city-location-hint" style="display: block; margin-top: 10px; padding: 12px 16px; background: #f7fafc; border-radius: 10px; color: #4a5568; font-size: 13px; line-height: 1.5; border-left: 3px solid #667eea;">
                            <i class="bi bi-info-circle" style="color: #667eea; margin-right: 8px;"></i>
                            <strong>Зачем нужна геолокация?</strong> Мы автоматически определим ваш город, чтобы другие пользователи могли найти ваше мероприятие. Это поможет участникам быстрее найти события в их регионе.
                        </small>
                    </div>
                    
                    <div id="city-suggestions" class="list-group" style="position: absolute; z-index: 1000; max-height: 200px; overflow-y: auto; display: none; margin-top: 8px; width: 100%; border-radius: 12px; box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15); border: 1px solid #e2e8f0; background: white;"></div>
                    <div id="city-selected-info" class="city-selected-info" style="<?= !empty($parsedAddress['city']) ? 'display: block;' : 'display: none;' ?> margin-top: 12px; padding: 12px 16px; background: linear-gradient(135deg, #f0f4ff 0%, #e8edff 100%); border-radius: 12px; border-left: 4px solid #667eea; box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1); animation: fadeIn 0.3s ease;">
                        <div class="d-flex align-items-center" style="gap: 10px;">
                            <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);">
                                <i class="bi bi-geo-alt-fill" style="color: white; font-size: 16px;"></i>
                            </div>
                            <div style="flex: 1;">
                                <small class="text-muted" style="display: block; font-size: 12px; margin-bottom: 2px; color: #718096;">Выбранный город:</small>
                                <strong id="city-selected-text" style="color: #667eea; font-size: 15px; font-weight: 600;"><?= Helper::escape($parsedAddress['city']) ?><?= !empty($parsedAddress['country']) ? ', ' . Helper::escape($parsedAddress['country']) : '' ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3" style="position: relative;">
                    <label for="street" class="form-label">Улица *</label>
                    <input type="text"
                        class="form-control"
                        id="street"
                        name="street"
                        required
                        value="<?= Helper::escape($parsedAddress['street']) ?>"
                        autocomplete="off"
                        placeholder="Сначала выберите город, затем выберите улицу из списка"
                        <?= empty($parsedAddress['city']) ? 'readonly' : '' ?>>
                    <div id="street-suggestions" class="list-group" style="position: absolute; z-index: 1000; max-height: 300px; overflow-y: auto; display: none; margin-top: 2px; width: 100%;"></div>
                    <small class="form-text text-muted d-block mt-2">
                        Сначала выберите город. После выбора города появится список всех улиц для выбора.
                    </small>
                </div>

                <div class="mb-3">
                    <label for="house_number" class="form-label">Номер дома *</label>
                    <input type="text"
                        class="form-control"
                        id="house_number"
                        name="house_number"
                        required
                        value="<?= Helper::escape($parsedAddress['house_number']) ?>"
                        placeholder="Например: 150">
                </div>

                <div class="mb-3">
                    <label for="location" class="form-label">Дополнительная информация о месте</label>
                    <input type="text"
                        class="form-control"
                        id="location"
                        name="location"
                        value="<?= Helper::escape($parsedAddress['location']) ?>"
                        placeholder="Например: Парк Горького, вход со стороны главной аллеи">
                    <small class="form-text text-muted d-block mt-2">
                        Укажите дополнительные детали, если нужно (название парка, здания и т.д.)
                    </small>
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Цена <span id="currency-symbol">₸</span></label>
                    <input type="number"
                        class="form-control"
                        id="price"
                        name="price"
                        value="<?= (int)($event['price'] ?? 0) ?>"
                        min="0"
                        step="100">
                    <small class="text-muted">Укажите 0 если мероприятие бесплатное</small>
                    <input type="hidden" id="currency_code" name="currency_code" value="KZT">
                </div>

                <div class="event-publish-tariff mb-3">
                    <h6><i class="bi bi-wallet2"></i> Оплата размещения на платформе</h6>
                    <div class="event-publish-tariff-amount"><?= number_format($eventPublishPrice, 0, ',', ' ') ?> ₸</div>
                    <p class="event-publish-tariff-note mb-0">
                        Отдельная плата за публикацию в ленте Aru (не цена билета для участников).
                    </p>
                    <?php if (!$eventPublishPaid): ?>
                        <div class="event-publish-tariff-free">
                            <i class="bi bi-gift-fill"></i> Сейчас <strong>бесплатно</strong>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mt-3 mb-0 py-2 small">
                            В конфигурации включена оплата — при подключении платёжного шага здесь появится списание.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Проверка адреса</label>
                    <button type="button" class="btn btn-secondary" onclick="geocodeAddress()">
                        <i class="bi bi-geo-alt"></i> Найти адрес на карте
                    </button>
                    <input type="hidden" id="latitude" name="latitude" value="<?= !empty($event['latitude']) ? (float)$event['latitude'] : '' ?>">
                    <input type="hidden" id="longitude" name="longitude" value="<?= !empty($event['longitude']) ? (float)$event['longitude'] : '' ?>">
                    <div id="locationStatus" class="mt-2">
                        <?php if (!empty($event['latitude']) && !empty($event['longitude'])): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> Координаты уже установлены<br>
                                <small>Координаты: <?= number_format((float)$event['latitude'], 6) ?>, <?= number_format((float)$event['longitude'], 6) ?></small>
                            </div>
                        <?php endif; ?>
                    </div>
                    <small class="form-text text-muted d-block mt-2">
                        Нажмите кнопку для проверки адреса и получения координат. Это необходимо для отображения мероприятия на карте.
                    </small>
                </div>

                <button type="submit" class="btn btn-primary btn-lg">
                    <?php if (!$eventPublishPaid): ?>
                        <i class="bi bi-gift"></i> Сохранить (сейчас бесплатно)
                    <?php else: ?>
                        <i class="bi bi-check-circle"></i> Сохранить изменения
                    <?php endif; ?>
                </button>
                <a href="<?= BASE_URL ?>events" class="btn btn-outline-secondary btn-lg">
                    Отмена
                </a>
            </div>
        </div>
    </form>
</div>

<style>
    /* Стили для автодополнения */
    #city-suggestions,
    #street-suggestions {
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border: 1px solid #e2e8f0;
        background: white;
    }

    .list-group-item {
        padding: 12px 16px;
        cursor: pointer;
        border: none;
        border-bottom: 1px solid #f0f0f0;
        background: white;
        transition: background 0.2s;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .list-group-item:hover,
    .list-group-item.active {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        color: #667eea;
    }

    .list-group-item:last-child {
        border-bottom: none;
    }

    .suggestion-text {
        font-weight: 500;
        color: #2d3748;
        flex: 1;
    }

    .suggestion-type {
        font-size: 11px;
        color: #718096;
        margin-left: 8px;
        white-space: nowrap;
    }

    @media (max-width: 767px) {
        .list-group-item {
            padding: 10px 12px;
            font-size: 14px;
        }

        .suggestion-type {
            font-size: 10px;
        }
    }

    /* Стили для кнопки геолокации */
    .city-location-btn {
        border: 2px solid #667eea !important;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%) !important;
        color: #667eea !important;
        position: relative;
        overflow: hidden;
    }

    .city-location-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        transition: left 0.3s ease;
        z-index: 0;
    }

    .city-location-btn:hover::before {
        left: 0;
    }

    .city-location-btn:hover {
        border-color: #667eea !important;
        color: #ffffff !important;
        box-shadow: 0 4px 16px rgba(102, 126, 234, 0.4) !important;
        transform: translateY(-2px);
    }

    .city-location-btn i,
    .city-location-btn span {
        position: relative;
        z-index: 1;
        transition: transform 0.3s ease;
    }

    .city-location-btn:hover i {
        transform: scale(1.1);
    }

    .city-location-btn span {
        font-weight: 600;
        font-size: 16px;
    }

    .city-location-btn:active {
        transform: translateY(0) !important;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3) !important;
    }

    .city-location-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none !important;
    }

    /* Контейнер для геолокации */
    .city-location-container {
        position: relative;
    }

    /* Стили для информационной подсказки */
    .city-location-hint {
        display: block;
    }

    /* Анимация появления информационного блока */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .city-selected-info {
        animation: fadeIn 0.3s ease;
    }

    /* Адаптивные стили для кнопки геолокации */
    @media (max-width: 767px) {
        .city-location-btn {
            padding: 14px 16px !important;
        }

        .city-location-btn span {
            font-size: 15px;
        }

        .city-location-btn i {
            font-size: 16px !important;
        }

        .city-location-hint {
            font-size: 12px !important;
            padding: 10px 14px !important;
        }
    }
</style>

<script>
    let citySuggestionsTimeout;
    let streetSuggestionsTimeout;
    let selectedCity = '<?= Helper::escape($parsedAddress['city']) ?>';
    let selectedCityData = null; // Хранит данные о выбранном городе (название, страна)

    // Инициализация данных города при загрузке страницы
    <?php if (!empty($parsedAddress['city'])): ?>
    // Пытаемся определить страну по координатам или используем из адреса
    selectedCityData = {
        name: '<?= Helper::escape($parsedAddress['city']) ?>',
        country: '<?= Helper::escape($parsedAddress['country']) ?>',
        countryCode: '',
        lat: <?= !empty($event['latitude']) ? (float)$event['latitude'] : 'null' ?>,
        lon: <?= !empty($event['longitude']) ? (float)$event['longitude'] : 'null' ?>
    };
    <?php endif; ?>

    /**
     * Экранирование HTML для безопасного отображения
     */
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Определение валюты по стране
     */
    function getCurrencyByCountry(country, countryCode) {
        const currencyMap = {
            // Казахстан
            'KZ': { symbol: '₸', code: 'KZT', name: 'тенге' },
            'Казахстан': { symbol: '₸', code: 'KZT', name: 'тенге' },
            'Kazakhstan': { symbol: '₸', code: 'KZT', name: 'тенге' },
            
            // Россия
            'RU': { symbol: '₽', code: 'RUB', name: 'рублей' },
            'Россия': { symbol: '₽', code: 'RUB', name: 'рублей' },
            'Russia': { symbol: '₽', code: 'RUB', name: 'рублей' },
            'Российская Федерация': { symbol: '₽', code: 'RUB', name: 'рублей' },
            
            // Беларусь
            'BY': { symbol: 'Br', code: 'BYN', name: 'белорусских рублей' },
            'Беларусь': { symbol: 'Br', code: 'BYN', name: 'белорусских рублей' },
            'Belarus': { symbol: 'Br', code: 'BYN', name: 'белорусских рублей' },
            
            // Украина
            'UA': { symbol: '₴', code: 'UAH', name: 'гривен' },
            'Украина': { symbol: '₴', code: 'UAH', name: 'гривен' },
            'Ukraine': { symbol: '₴', code: 'UAH', name: 'гривен' },
            
            // США
            'US': { symbol: '$', code: 'USD', name: 'долларов' },
            'США': { symbol: '$', code: 'USD', name: 'долларов' },
            'United States': { symbol: '$', code: 'USD', name: 'долларов' },
            
            // Европа (EUR)
            'DE': { symbol: '€', code: 'EUR', name: 'евро' }, // Германия
            'FR': { symbol: '€', code: 'EUR', name: 'евро' }, // Франция
            'IT': { symbol: '€', code: 'EUR', name: 'евро' }, // Италия
            'ES': { symbol: '€', code: 'EUR', name: 'евро' }, // Испания
            'NL': { symbol: '€', code: 'EUR', name: 'евро' }, // Нидерланды
            'BE': { symbol: '€', code: 'EUR', name: 'евро' }, // Бельгия
            'AT': { symbol: '€', code: 'EUR', name: 'евро' }, // Австрия
            'PT': { symbol: '€', code: 'EUR', name: 'евро' }, // Португалия
            'GR': { symbol: '€', code: 'EUR', name: 'евро' }, // Греция
            'IE': { symbol: '€', code: 'EUR', name: 'евро' }, // Ирландия
            'FI': { symbol: '€', code: 'EUR', name: 'евро' }, // Финляндия
            'Германия': { symbol: '€', code: 'EUR', name: 'евро' },
            'Франция': { symbol: '€', code: 'EUR', name: 'евро' },
            'Италия': { symbol: '€', code: 'EUR', name: 'евро' },
            'Испания': { symbol: '€', code: 'EUR', name: 'евро' },
            'Germany': { symbol: '€', code: 'EUR', name: 'евро' },
            'France': { symbol: '€', code: 'EUR', name: 'евро' },
            'Italy': { symbol: '€', code: 'EUR', name: 'евро' },
            'Spain': { symbol: '€', code: 'EUR', name: 'евро' },
            
            // Великобритания
            'GB': { symbol: '£', code: 'GBP', name: 'фунтов стерлингов' },
            'Великобритания': { symbol: '£', code: 'GBP', name: 'фунтов стерлингов' },
            'United Kingdom': { symbol: '£', code: 'GBP', name: 'фунтов стерлингов' },
            
            // Китай
            'CN': { symbol: '¥', code: 'CNY', name: 'юаней' },
            'Китай': { symbol: '¥', code: 'CNY', name: 'юаней' },
            'China': { symbol: '¥', code: 'CNY', name: 'юаней' },
            
            // Япония
            'JP': { symbol: '¥', code: 'JPY', name: 'иен' },
            'Япония': { symbol: '¥', code: 'JPY', name: 'иен' },
            'Japan': { symbol: '¥', code: 'JPY', name: 'иен' },
            
            // Турция
            'TR': { symbol: '₺', code: 'TRY', name: 'турецких лир' },
            'Турция': { symbol: '₺', code: 'TRY', name: 'турецких лир' },
            'Turkey': { symbol: '₺', code: 'TRY', name: 'турецких лир' },
            
            // Кыргызстан
            'KG': { symbol: 'сом', code: 'KGS', name: 'сом' },
            'Кыргызстан': { symbol: 'сом', code: 'KGS', name: 'сом' },
            'Kyrgyzstan': { symbol: 'сом', code: 'KGS', name: 'сом' },
            
            // Узбекистан
            'UZ': { symbol: 'сум', code: 'UZS', name: 'сум' },
            'Узбекистан': { symbol: 'сум', code: 'UZS', name: 'сум' },
            'Uzbekistan': { symbol: 'сум', code: 'UZS', name: 'сум' },
        };

        // Сначала проверяем по коду страны
        if (countryCode && currencyMap[countryCode.toUpperCase()]) {
            return currencyMap[countryCode.toUpperCase()];
        }

        // Затем проверяем по названию страны
        if (country) {
            const countryUpper = country.trim();
            if (currencyMap[countryUpper]) {
                return currencyMap[countryUpper];
            }
        }

        // По умолчанию возвращаем тенге (Казахстан)
        return { symbol: '₸', code: 'KZT', name: 'тенге' };
    }

    /**
     * Обновление валюты в поле цены
     */
    function updateCurrency(cityData) {
        if (!cityData) {
            // Если данных о городе нет, используем валюту по умолчанию
            const defaultCurrency = { symbol: '₸', code: 'KZT', name: 'тенге' };
            document.getElementById('currency-symbol').textContent = defaultCurrency.symbol;
            document.getElementById('currency_code').value = defaultCurrency.code;
            return;
        }

        const currency = getCurrencyByCountry(cityData.country, cityData.countryCode);
        document.getElementById('currency-symbol').textContent = currency.symbol;
        document.getElementById('currency_code').value = currency.code;
    }

    /**
     * Обновление информации о выбранном городе и стране
     */
    function updateCitySelectedInfo(cityData) {
        const cityInfoEl = document.getElementById('city-selected-info');
        const cityTextEl = document.getElementById('city-selected-text');
        
        if (!cityData || !cityData.country) {
            if (cityInfoEl) {
                cityInfoEl.style.display = 'none';
            }
            return;
        }

        if (cityInfoEl && cityTextEl) {
            const cityName = cityData.name || '';
            const countryName = cityData.country || '';
            cityTextEl.textContent = cityName + ', ' + countryName;
            cityInfoEl.style.display = 'block';
        }
    }

    /**
     * Определение города по геолокации (ручной запрос)
     */
    window.detectLocation = function() {
        const cityInput = document.getElementById('city');
        const detectBtn = document.getElementById('detectLocationBtn');
        const locationStatus = document.getElementById('locationStatus');
        
        if (!navigator.geolocation) {
            if (locationStatus) {
                locationStatus.innerHTML =
                    '<div class="alert alert-warning">' +
                    '<i class="bi bi-exclamation-triangle"></i> Геолокация не поддерживается вашим браузером' +
                    '</div>';
            }
            return;
        }

        // Показываем индикатор загрузки
        if (detectBtn) {
            detectBtn.disabled = true;
            detectBtn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
        }

        navigator.geolocation.getCurrentPosition(
            async function(position) {
                try {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&addressdetails=1`;
                    
                    const response = await fetch(url, {
                        headers: {
                            'User-Agent': 'Tanisu App'
                        }
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        const address = data.address || {};
                        
                        // Получаем название города
                        const cityName = address.city || 
                                       address.town || 
                                       address.village || 
                                       address.municipality ||
                                       address.county ||
                                       '';
                        
                        const country = address.country || '';
                        const countryCode = address.country_code || '';
                        
                        if (cityName && cityInput) {
                            // Заполняем поле города
                            cityInput.value = cityName;
                            selectedCity = cityName;
                            
                            // Создаем объект с данными о городе
                            selectedCityData = {
                                name: cityName,
                                country: country,
                                countryCode: countryCode,
                                lat: lat,
                                lon: lon
                            };
                            
                            // Обновляем валюту
                            updateCurrency(selectedCityData);
                            
                            // Показываем информацию о выбранном городе
                            updateCitySelectedInfo(selectedCityData);
                            
                            // Автоматически загружаем улицы для найденного города
                            loadCityStreets(cityName, selectedCityData);
                            
                            // Устанавливаем координаты
                            const latInput = document.getElementById('latitude');
                            const lonInput = document.getElementById('longitude');
                            if (latInput) {
                                latInput.value = lat;
                            }
                            if (lonInput) {
                                lonInput.value = lon;
                            }
                            
                            // Показываем уведомление только если locationStatus существует и пустой
                            if (locationStatus && !locationStatus.innerHTML.trim()) {
                                locationStatus.innerHTML =
                                    '<div class="alert alert-success">' +
                                    '<i class="bi bi-check-circle"></i> Город определен по геолокации!<br>' +
                                    '<small>Город: ' + escapeHtml(cityName) + (country ? ', ' + escapeHtml(country) : '') + '</small><br>' +
                                    '<small>Выберите улицу из списка ниже</small>' +
                                    '</div>';
                            }
                        } else {
                            // Если город не найден, но поле уже заполнено, просто обновляем координаты
                            if (cityInput && cityInput.value.trim()) {
                                const latInput = document.getElementById('latitude');
                                const lonInput = document.getElementById('longitude');
                                if (latInput) latInput.value = lat;
                                if (lonInput) lonInput.value = lon;
                                
                                if (locationStatus && !locationStatus.innerHTML.trim()) {
                                    locationStatus.innerHTML =
                                        '<div class="alert alert-info">' +
                                        '<i class="bi bi-info-circle"></i> Координаты обновлены по геолокации' +
                                        '</div>';
                                }
                            } else {
                                // Только если город не заполнен, показываем предупреждение
                                if (locationStatus && !locationStatus.innerHTML.trim()) {
                                    locationStatus.innerHTML =
                                        '<div class="alert alert-warning">' +
                                        '<i class="bi bi-exclamation-triangle"></i> Не удалось определить город по геолокации. Введите город вручную.' +
                                        '</div>';
                                }
                            }
                        }
                    } else {
                        // Если запрос не удался, но город уже заполнен, просто обновляем координаты
                        if (cityInput && cityInput.value.trim()) {
                            const latInput = document.getElementById('latitude');
                            const lonInput = document.getElementById('longitude');
                            if (latInput) latInput.value = position.coords.latitude;
                            if (lonInput) lonInput.value = position.coords.longitude;
                        } else {
                            throw new Error('Ошибка запроса к сервису геокодирования');
                        }
                    }
                } catch (error) {
                    console.error('Ошибка определения города:', error);
                    // Не показываем ошибку, если город уже заполнен
                    if (cityInput && !cityInput.value.trim() && locationStatus && !locationStatus.innerHTML.trim()) {
                        locationStatus.innerHTML =
                            '<div class="alert alert-warning">' +
                            '<i class="bi bi-exclamation-triangle"></i> Не удалось определить город. Введите город вручную или используйте кнопку "Найти адрес на карте".' +
                            '</div>';
                    }
                } finally {
                    if (detectBtn) {
                        detectBtn.disabled = false;
                        detectBtn.innerHTML = '<i class="bi bi-geo-alt"></i>';
                    }
                }
            },
            function(error) {
                console.error('Ошибка геолокации:', error);
                // Не показываем ошибку, если город уже заполнен
                if (cityInput && !cityInput.value.trim() && locationStatus && !locationStatus.innerHTML.trim()) {
                    let errorMessage = 'Не удалось определить местоположение. ';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage += 'Разрешите доступ к геолокации в настройках браузера.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage += 'Информация о местоположении недоступна.';
                            break;
                        case error.TIMEOUT:
                            errorMessage += 'Превышено время ожидания.';
                            break;
                        default:
                            errorMessage += 'Введите город вручную.';
                            break;
                    }
                    
                    locationStatus.innerHTML =
                        '<div class="alert alert-warning">' +
                        '<i class="bi bi-exclamation-triangle"></i> ' + errorMessage +
                        '</div>';
                }
                
                if (detectBtn) {
                    detectBtn.disabled = false;
                    detectBtn.innerHTML = '<i class="bi bi-geo-alt"></i>';
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    };

    /**
     * Автодополнение для городов (поиск по всему миру через Nominatim)
     */
    async function searchCities(query) {
        if (query.length < 2) {
            return [];
        }

        try {
            const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=10&featuretype=city&addressdetails=1`;
            
            const response = await fetch(url, {
                headers: {
                    'User-Agent': 'Tanisu App'
                }
            });

            if (!response.ok) {
                return [];
            }

            const data = await response.json();
            
            // Фильтруем и форматируем результаты
            const cities = [];
            const seen = new Set();
            
            for (const item of data) {
                const address = item.address || {};
                const cityName = address.city || 
                                address.town || 
                                address.village || 
                                address.municipality ||
                                address.county ||
                                item.display_name.split(',')[0];
                
                const country = address.country || '';
                const countryCode = address.country_code || '';
                
                // Создаем уникальный ключ
                const key = `${cityName}, ${country}`.toLowerCase();
                
                if (cityName && !seen.has(key)) {
                    seen.add(key);
                    cities.push({
                        name: cityName,
                        country: country,
                        countryCode: countryCode,
                        displayName: item.display_name,
                        bbox: item.boundingbox || item.bbox,
                        lat: item.lat,
                        lon: item.lon
                    });
                }
            }
            
            return cities;
        } catch (error) {
            console.error('Ошибка поиска городов:', error);
            return [];
        }
    }

    /**
     * Автодополнение для городов
     */
    function initCityAutocomplete() {
        const cityInput = document.getElementById('city');
        const citySuggestions = document.getElementById('city-suggestions');

        if (!cityInput || !citySuggestions) return;

        // Устанавливаем ширину выпадающего списка равной ширине поля ввода
        function updateSuggestionsWidth() {
            // Учитываем, что поле города теперь в контейнере с кнопкой
            const cityContainer = cityInput.parentElement;
            if (cityContainer) {
                citySuggestions.style.width = cityContainer.offsetWidth + 'px';
            } else {
                citySuggestions.style.width = cityInput.offsetWidth + 'px';
            }
        }

        cityInput.addEventListener('input', function() {
            const query = this.value.trim();

            clearTimeout(citySuggestionsTimeout);

            if (query.length < 2) {
                citySuggestions.style.display = 'none';
                selectedCity = '';
                selectedCityData = null;
                updateCitySelectedInfo(null);
                return;
            }

            citySuggestionsTimeout = setTimeout(async () => {
                const cities = await searchCities(query);

                if (cities.length === 0) {
                    citySuggestions.style.display = 'none';
                    return;
                }

                updateSuggestionsWidth();
                citySuggestions.innerHTML = '';
                
                cities.forEach(city => {
                    const item = document.createElement('div');
                    item.className = 'list-group-item';
                    item.innerHTML = `
                        <span class="suggestion-text">${city.name}</span>
                        <span class="suggestion-type">${city.country}</span>
                    `;
                    item.addEventListener('click', function() {
                        cityInput.value = city.name;
                        selectedCity = city.name;
                        selectedCityData = city;
                        citySuggestions.style.display = 'none';
                        // Обновляем валюту в зависимости от страны
                        updateCurrency(city);
                        // Показываем информацию о выбранном городе и стране
                        updateCitySelectedInfo(city);
                        // Загружаем улицы выбранного города
                        loadCityStreets(city.name, city);
                    });
                    citySuggestions.appendChild(item);
                });

                citySuggestions.style.display = 'block';
            }, 300);
        });

        // Скрываем подсказки при клике вне поля
        document.addEventListener('click', function(e) {
            if (!cityInput.contains(e.target) && !citySuggestions.contains(e.target)) {
                citySuggestions.style.display = 'none';
            }
        });

        // Сохраняем выбранный город и загружаем улицы
        cityInput.addEventListener('blur', function() {
            setTimeout(async () => {
                if (this.value && this.value.length >= 2) {
                    // Если город не был выбран из списка, пытаемся найти его
                    if (!selectedCityData || selectedCityData.name !== this.value) {
                        const cities = await searchCities(this.value);
                        if (cities.length > 0) {
                            selectedCity = cities[0].name;
                            selectedCityData = cities[0];
                            // Обновляем валюту в зависимости от страны
                            updateCurrency(cities[0]);
                            // Показываем информацию о выбранном городе и стране
                            updateCitySelectedInfo(cities[0]);
                            loadCityStreets(cities[0].name, cities[0]);
                        }
                    } else {
                        // Обновляем валюту в зависимости от страны
                        updateCurrency(selectedCityData);
                        // Показываем информацию о выбранном городе и стране
                        updateCitySelectedInfo(selectedCityData);
                        loadCityStreets(selectedCity, selectedCityData);
                    }
                } else {
                    // Если поле пустое, скрываем информацию
                    updateCitySelectedInfo(null);
                }
            }, 200);
        });

        // Обновляем ширину при изменении размера окна
        window.addEventListener('resize', updateSuggestionsWidth);
    }

    /**
     * Загрузка всех улиц выбранного города
     */
    let allCityStreets = []; // Хранит все улицы текущего города
    let streetSearchHandlerAdded = false; // Флаг, что обработчик поиска уже добавлен

    /**
     * Альтернативный метод поиска улиц через Nominatim (для случаев, когда Overpass не работает)
     */
    async function searchStreetsViaNominatim(city, cityData = null) {
        try {
            const country = cityData ? (cityData.countryCode || '') : '';
            const searchQueries = country ? 
                [`street, ${city}, ${country}`, `road, ${city}, ${country}`, city] :
                [`street, ${city}`, `road, ${city}`, city];

            const allStreets = new Set();
            const streetsList = [];

            // Делаем несколько запросов для получения большего количества улиц
            for (const query of searchQueries) {
                const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=50&addressdetails=1`;

                const response = await fetch(url, {
                    headers: {
                        'User-Agent': 'Tanisu App'
                    }
                });

                if (!response.ok) continue;

                const data = await response.json();
                if (!data || data.length === 0) continue;

                for (const item of data) {
                    const address = item.address || {};
                    let streetName = address.road || 
                                   address.street || 
                                   address.pedestrian || 
                                   address.path || 
                                   address.footway || 
                                   address.residential;

                    // Если не нашли в address, пробуем из display_name
                    if (!streetName && item.display_name) {
                        const parts = item.display_name.split(',');
                        for (let part of parts) {
                            part = part.trim();
                            // Пропускаем части с городом и страной
                            if (part.toLowerCase().includes(city.toLowerCase()) ||
                                (cityData && cityData.country && part.toLowerCase().includes(cityData.country.toLowerCase()))) {
                                continue;
                            }
                            // Проверяем, содержит ли часть название улицы
                            if (part.match(/(street|st|road|rd|avenue|ave|boulevard|blvd|проспект|пр|улица|ул|проезд|пер|переулок|бульвар)/i)) {
                                streetName = part;
                                break;
                            }
                        }
                    }

                    if (streetName) {
                        // Очищаем название
                        streetName = streetName.toString().trim();
                        streetName = streetName.replace(/^(улица|ул\.|ул|проспект|пр\.|пр|проезд|пер\.|переулок|бульвар|б-р|б\.|street|st|road|rd|avenue|ave|boulevard|blvd)\s+/i, '').trim();
                        streetName = streetName.replace(/\s+(улица|ул\.|ул|street|st|road|rd|avenue|ave|boulevard|blvd)$/i, '').trim();

                        const streetLower = streetName.toLowerCase();
                        if (streetName.length > 1 &&
                            !streetName.match(/^\d+$/) &&
                            !allStreets.has(streetLower)) {
                            allStreets.add(streetLower);
                            streetsList.push(streetName);
                        }
                    }
                }
            }

            // Сортируем по алфавиту
            streetsList.sort((a, b) => a.localeCompare(b));
            return streetsList;
        } catch (error) {
            console.error('Ошибка поиска улиц через Nominatim:', error);
            return [];
        }
    }

    async function loadCityStreets(city, cityData = null) {
        const streetInput = document.getElementById('street');
        const streetSuggestions = document.getElementById('street-suggestions');

        if (!city || city.length < 2) {
            return;
        }

        // Делаем поле доступным для поиска
        streetInput.removeAttribute('readonly');
        streetInput.placeholder = 'Начните вводить для поиска улицы или выберите из списка';

        // Показываем индикатор загрузки
        const cityDisplayName = cityData ? `${city}, ${cityData.country}` : city;
        streetSuggestions.innerHTML = '<div class="list-group-item"><span class="suggestion-text">Загрузка улиц города ' + cityDisplayName + '...</span></div>';
        streetSuggestions.style.display = 'block';
        streetSuggestions.style.width = streetInput.offsetWidth + 'px';

        let streetsList = [];
        let useOverpass = true;

        try {
            let cityBbox;
            
            // Если данные о городе уже есть, используем их
            if (cityData && cityData.bbox) {
                cityBbox = cityData.bbox;
            } else {
                // Шаг 1: Получаем bounding box города через Nominatim (без ограничения по стране)
                const citySearchUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(city)}&limit=1&featuretype=city&addressdetails=1`;

                const cityResponse = await fetch(citySearchUrl, {
                    headers: {
                        'User-Agent': 'Tanisu App'
                    }
                });

                if (!cityResponse.ok) {
                    throw new Error('Не удалось найти город');
                }

                const cityResponseData = await cityResponse.json();
                if (!cityResponseData || cityResponseData.length === 0) {
                    throw new Error('Город не найден');
                }

                cityBbox = cityResponseData[0].boundingbox || cityResponseData[0].bbox;
                if (!cityBbox) {
                    throw new Error('Не удалось получить границы города');
                }
            }

            const [south, north, west, east] = cityBbox.map(parseFloat);

            // Шаг 2: Пробуем использовать Overpass API для получения всех улиц
            try {
                const overpassUrl = 'https://overpass-api.de/api/interpreter';
                const overpassQuery = `[out:json][timeout:25];
(
  way["highway"~"^(primary|secondary|tertiary|residential|unclassified|living_street|pedestrian|service|trunk)$"]["name"](${south},${west},${north},${east});
);
out body;
>;
out skel qt;`;

                const overpassResponse = await fetch(overpassUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'User-Agent': 'Tanisu App'
                    },
                    body: `data=${encodeURIComponent(overpassQuery)}`
                });

                if (overpassResponse.ok) {
                    const overpassData = await overpassResponse.json();

                    const allStreets = new Set();

                    // Извлекаем названия улиц
                    if (overpassData.elements && overpassData.elements.length > 0) {
                        overpassData.elements.forEach(element => {
                            if (element.tags && element.tags.name) {
                                let streetName = element.tags.name.toString().trim();

                                // Очищаем название (универсальная очистка для разных языков)
                                streetName = streetName.replace(/^(улица|ул\.|ул|проспект|пр\.|пр|проезд|пер\.|переулок|бульвар|б-р|б\.|street|st|road|rd|avenue|ave|boulevard|blvd|via|viale|calle|callejón|rue|straße|straße)\s+/i, '').trim();
                                streetName = streetName.replace(/\s+(улица|ул\.|ул|street|st|road|rd|avenue|ave|boulevard|blvd)$/i, '').trim();
                                streetName = streetName.replace(/\s+\d+[а-яa-z]*$/i, '').trim();

                                if (streetName.length > 1 &&
                                    !streetName.match(/^\d+$/) &&
                                    !allStreets.has(streetName.toLowerCase())) {
                                    allStreets.add(streetName.toLowerCase());
                                    streetsList.push(streetName);
                                }
                            }
                        });

                        // Сортируем по алфавиту
                        streetsList.sort((a, b) => a.localeCompare(b));
                        useOverpass = true;
                    } else {
                        // Overpass вернул пустой результат, пробуем альтернативный метод
                        useOverpass = false;
                    }
                } else {
                    // Overpass API недоступен, пробуем альтернативный метод
                    useOverpass = false;
                }
            } catch (overpassError) {
                console.log('Overpass API недоступен, используем альтернативный метод:', overpassError);
                useOverpass = false;
            }

            // Если Overpass не сработал или вернул мало результатов, используем Nominatim
            if (!useOverpass || streetsList.length < 10) {
                const nominatimStreets = await searchStreetsViaNominatim(city, cityData);
                if (nominatimStreets.length > 0) {
                    // Объединяем результаты, убирая дубликаты
                    const combinedSet = new Set(streetsList.map(s => s.toLowerCase()));
                    for (const street of nominatimStreets) {
                        if (!combinedSet.has(street.toLowerCase())) {
                            streetsList.push(street);
                            combinedSet.add(street.toLowerCase());
                        }
                    }
                    // Сортируем заново
                    streetsList.sort((a, b) => a.localeCompare(b));
                }
            }

            // Сохраняем список для фильтрации
            allCityStreets = streetsList;

            // Показываем все улицы
            displayStreetsList(streetsList, city);

        } catch (error) {
            console.error('Ошибка загрузки улиц:', error);
            // Пробуем альтернативный метод через Nominatim
            try {
                const nominatimStreets = await searchStreetsViaNominatim(city, cityData);
                if (nominatimStreets.length > 0) {
                    allCityStreets = nominatimStreets;
                    displayStreetsList(nominatimStreets, city);
                } else {
                    // Если и альтернативный метод не сработал, просто разрешаем ручной ввод
                    streetSuggestions.innerHTML = `
                        <div class="list-group-item">
                            <span class="suggestion-text text-muted">Автоматическая загрузка улиц недоступна для этого города. Вы можете ввести название улицы вручную.</span>
                        </div>
                    `;
                    streetInput.removeAttribute('readonly');
                    streetInput.placeholder = 'Введите название улицы вручную';
                }
            } catch (fallbackError) {
                console.error('Ошибка альтернативного метода:', fallbackError);
                streetSuggestions.innerHTML = `
                    <div class="list-group-item">
                        <span class="suggestion-text text-muted">Автоматическая загрузка улиц недоступна. Вы можете ввести название улицы вручную.</span>
                    </div>
                `;
                streetInput.removeAttribute('readonly');
                streetInput.placeholder = 'Введите название улицы вручную';
            }
        }
    }

    /**
     * Отображение списка улиц с поиском
     */
    function displayStreetsList(streetsList, city) {
        const streetInput = document.getElementById('street');
        const streetSuggestions = document.getElementById('street-suggestions');

        if (streetsList.length === 0) {
            streetSuggestions.innerHTML = `
                <div class="list-group-item">
                    <span class="suggestion-text text-muted">Улицы не найдены для города "${city}". Вы можете ввести название улицы вручную.</span>
                </div>
            `;
            streetSuggestions.style.display = 'block';
            return;
        }

        // Отображаем все улицы
        renderStreets(streetsList, city);

        // Добавляем поиск при вводе (только один раз)
        if (!streetSearchHandlerAdded) {
            streetInput.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();
                clearTimeout(streetSuggestionsTimeout);

                streetSuggestionsTimeout = setTimeout(() => {
                    if (query === '') {
                        renderStreets(allCityStreets, city);
                    } else {
                        const filtered = allCityStreets.filter(street =>
                            street.toLowerCase().includes(query)
                        );
                        renderStreets(filtered, city);
                    }
                }, 200);
            });
            streetSearchHandlerAdded = true;
        }
    }

    /**
     * Рендеринг списка улиц
     */
    function renderStreets(streetsList, city) {
        const streetInput = document.getElementById('street');
        const streetSuggestions = document.getElementById('street-suggestions');

        // Обновляем ширину списка
        streetSuggestions.style.width = streetInput.offsetWidth + 'px';

        if (streetsList.length === 0) {
            streetSuggestions.innerHTML = `
                <div class="list-group-item">
                    <span class="suggestion-text text-muted">Улицы не найдены. Попробуйте другой поисковый запрос.</span>
                </div>
            `;
            streetSuggestions.style.display = 'block';
            return;
        }

        streetSuggestions.innerHTML = '';

        // Показываем первые 100 улиц (для производительности)
        const displayList = streetsList.slice(0, 100);

        displayList.forEach(street => {
            const item = document.createElement('div');
            item.className = 'list-group-item';
            item.innerHTML = `<span class="suggestion-text">${street}</span>`;
            item.addEventListener('click', function() {
                streetInput.value = street;
                streetSuggestions.style.display = 'none';
            });
            streetSuggestions.appendChild(item);
        });

        if (streetsList.length > 100) {
            const moreItem = document.createElement('div');
            moreItem.className = 'list-group-item';
            moreItem.innerHTML = `<span class="suggestion-text text-muted">... и еще ${streetsList.length - 100} улиц. Используйте поиск для уточнения.</span>`;
            streetSuggestions.appendChild(moreItem);
        }

        streetSuggestions.style.display = 'block';
    }

    /**
     * Инициализация обработчиков для улиц
     */
    function initStreetHandlers() {
        const streetInput = document.getElementById('street');
        const streetSuggestions = document.getElementById('street-suggestions');

        if (!streetInput || !streetSuggestions) return;

        // Скрываем подсказки при клике вне поля
        document.addEventListener('click', function(e) {
            if (!streetInput.contains(e.target) && !streetSuggestions.contains(e.target)) {
                streetSuggestions.style.display = 'none';
            }
        });

        // Обновляем ширину списка при изменении размера окна
        window.addEventListener('resize', function() {
            if (streetSuggestions.style.display !== 'none') {
                streetSuggestions.style.width = streetInput.offsetWidth + 'px';
            }
        });
    }

    /**
     * Геокодирование адреса в координаты
     * Использует OpenStreetMap Nominatim API (бесплатный)
     * Работает с любыми странами
     */
    async function geocodeAddress() {
        const city = document.getElementById('city').value.trim();
        const street = document.getElementById('street').value.trim();
        const houseNumber = document.getElementById('house_number').value.trim();
        const location = document.getElementById('location').value.trim();
        const latInput = document.getElementById('latitude');
        const lonInput = document.getElementById('longitude');

        // Проверяем обязательные поля
        if (!city || !street || !houseNumber) {
            document.getElementById('locationStatus').innerHTML =
                '<div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> Заполните все поля адреса (город, улица, номер дома)</div>';
            return;
        }

        // Показываем статус загрузки
        document.getElementById('locationStatus').innerHTML =
            '<div class="alert alert-info"><i class="bi bi-hourglass-split"></i> Поиск адреса на карте...</div>';

        // Получаем информацию о стране и координатах города
        const country = selectedCityData ? selectedCityData.country : '';
        const cityLat = selectedCityData && selectedCityData.lat ? parseFloat(selectedCityData.lat) : null;
        const cityLon = selectedCityData && selectedCityData.lon ? parseFloat(selectedCityData.lon) : null;

        // Стратегия 1: Если есть координаты города, используем их для поиска улицы в радиусе
        if (cityLat && cityLon && !isNaN(cityLat) && !isNaN(cityLon)) {
            try {
                // Ищем улицу в радиусе 5км от центра города
                const searchQuery = `${street} ${houseNumber}, ${city}`;
                const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery)}&limit=10&addressdetails=1&bounded=1&viewbox=${(cityLon - 0.05)},${(cityLat - 0.05)},${(cityLon + 0.05)},${(cityLat + 0.05)}`;

                const response = await fetch(url, {
                    headers: {
                        'User-Agent': 'Tanisu App'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data && data.length > 0) {
                        // Ищем наиболее подходящий результат
                        for (const result of data) {
                            const resultAddress = result.address || {};
                            const resultCity = resultAddress.city || resultAddress.town || resultAddress.village || resultAddress.municipality || '';
                            const resultStreet = resultAddress.road || resultAddress.street || '';
                            
                            const cityMatch = resultCity.toLowerCase().includes(city.toLowerCase()) || 
                                           city.toLowerCase().includes(resultCity.toLowerCase());
                            
                            const streetMatch = resultStreet.toLowerCase().includes(street.toLowerCase()) ||
                                              street.toLowerCase().includes(resultStreet.toLowerCase());
                            
                            if (cityMatch && streetMatch) {
                                const lat = parseFloat(result.lat);
                                const lon = parseFloat(result.lon);

                                if (!isNaN(lat) && !isNaN(lon)) {
                                    if (latInput) latInput.value = lat;
                                    if (lonInput) lonInput.value = lon;

                                    document.getElementById('locationStatus').innerHTML =
                                        '<div class="alert alert-success">' +
                                        '<i class="bi bi-check-circle"></i> Адрес найден на карте!<br>' +
                                        '<small>Координаты: ' + lat.toFixed(6) + ', ' + lon.toFixed(6) + '</small><br>' +
                                        '<small>Найденный адрес: ' + escapeHtml(result.display_name) + '</small>' +
                                        '</div>';
                                    return;
                                }
                            }
                        }
                    }
                }
            } catch (error) {
                console.log('Ошибка поиска в радиусе:', error);
            }
        }

        // Стратегия 2: Поиск по полному адресу с разными форматами
        const searchQueries = [];
        
        // Формируем запросы в порядке приоритета
        if (country) {
            // С номером дома и страной
            searchQueries.push(`${houseNumber} ${street}, ${city}, ${country}`);
            searchQueries.push(`${street} ${houseNumber}, ${city}, ${country}`);
            searchQueries.push(`${city}, ${street} ${houseNumber}, ${country}`);
        }
        
        // Без страны
        searchQueries.push(`${houseNumber} ${street}, ${city}`);
        searchQueries.push(`${street} ${houseNumber}, ${city}`);
        searchQueries.push(`${city}, ${street} ${houseNumber}`);

        let found = false;
        let bestResult = null;

        // Пробуем каждый вариант поиска
        for (const query of searchQueries) {
            try {
                const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=10&addressdetails=1`;

                const response = await fetch(url, {
                    headers: {
                        'User-Agent': 'Tanisu App'
                    }
                });

                if (!response.ok) continue;

                const data = await response.json();
                if (!data || data.length === 0) continue;

                // Ищем наиболее подходящий результат
                for (const result of data) {
                    const resultAddress = result.address || {};
                    const resultCity = resultAddress.city || resultAddress.town || resultAddress.village || resultAddress.municipality || '';
                    const resultStreet = resultAddress.road || resultAddress.street || '';
                    
                    // Проверяем совпадение города (более строгая проверка)
                    const cityLower = city.toLowerCase();
                    const resultCityLower = resultCity.toLowerCase();
                    const displayNameLower = result.display_name.toLowerCase();
                    
                    const cityMatch = resultCityLower.includes(cityLower) || 
                                     cityLower.includes(resultCityLower) ||
                                     displayNameLower.includes(cityLower);
                    
                    // Проверяем совпадение улицы
                    const streetLower = street.toLowerCase();
                    const resultStreetLower = resultStreet.toLowerCase();
                    
                    const streetMatch = resultStreetLower.includes(streetLower) ||
                                      streetLower.includes(resultStreetLower) ||
                                      displayNameLower.includes(streetLower);
                    
                    if (cityMatch && streetMatch) {
                        const lat = parseFloat(result.lat);
                        const lon = parseFloat(result.lon);

                        if (!isNaN(lat) && !isNaN(lon)) {
                            bestResult = result;
                            found = true;
                            break;
                        }
                    }
                }
                
                if (found) break;
            } catch (error) {
                console.log('Ошибка поиска:', error);
                continue;
            }
        }

        // Если нашли результат
        if (found && bestResult) {
            const lat = parseFloat(bestResult.lat);
            const lon = parseFloat(bestResult.lon);

            if (latInput) latInput.value = lat;
            if (lonInput) lonInput.value = lon;

            document.getElementById('locationStatus').innerHTML =
                '<div class="alert alert-success">' +
                '<i class="bi bi-check-circle"></i> Адрес найден на карте!<br>' +
                '<small>Координаты: ' + lat.toFixed(6) + ', ' + lon.toFixed(6) + '</small><br>' +
                '<small>Найденный адрес: ' + escapeHtml(bestResult.display_name) + '</small>' +
                '</div>';
            return;
        }

        // Стратегия 3: Поиск без номера дома
        const queriesWithoutHouse = [];
        if (country) {
            queriesWithoutHouse.push(`${street}, ${city}, ${country}`);
            queriesWithoutHouse.push(`${city}, ${street}, ${country}`);
        }
        queriesWithoutHouse.push(`${street}, ${city}`);
        queriesWithoutHouse.push(`${city}, ${street}`);

        for (const query of queriesWithoutHouse) {
            try {
                const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=10&addressdetails=1`;

                const response = await fetch(url, {
                    headers: {
                        'User-Agent': 'Tanisu App'
                    }
                });

                if (!response.ok) continue;

                const data = await response.json();
                if (!data || data.length === 0) continue;

                for (const result of data) {
                    const resultAddress = result.address || {};
                    const resultCity = resultAddress.city || resultAddress.town || resultAddress.village || resultAddress.municipality || '';
                    const resultStreet = resultAddress.road || resultAddress.street || '';
                    
                    const cityLower = city.toLowerCase();
                    const resultCityLower = resultCity.toLowerCase();
                    const displayNameLower = result.display_name.toLowerCase();
                    
                    const cityMatch = resultCityLower.includes(cityLower) || 
                                     cityLower.includes(resultCityLower) ||
                                     displayNameLower.includes(cityLower);
                    
                    const streetLower = street.toLowerCase();
                    const resultStreetLower = resultStreet.toLowerCase();
                    
                    const streetMatch = resultStreetLower.includes(streetLower) ||
                                      streetLower.includes(resultStreetLower) ||
                                      displayNameLower.includes(streetLower);
                    
                    if (cityMatch && streetMatch) {
                        const lat = parseFloat(result.lat);
                        const lon = parseFloat(result.lon);

                        if (!isNaN(lat) && !isNaN(lon)) {
                            if (latInput) latInput.value = lat;
                            if (lonInput) lonInput.value = lon;

                            document.getElementById('locationStatus').innerHTML =
                                '<div class="alert alert-warning">' +
                                '<i class="bi bi-exclamation-triangle"></i> Адрес найден приблизительно (без номера дома)<br>' +
                                '<small>Координаты: ' + lat.toFixed(6) + ', ' + lon.toFixed(6) + '</small><br>' +
                                '<small>Найденный адрес: ' + escapeHtml(result.display_name) + '</small><br>' +
                                '<small>Проверьте правильность адреса на карте</small>' +
                                '</div>';
                            
                            found = true;
                            return;
                        }
                    }
                }
            } catch (error) {
                continue;
            }
        }

        // Стратегия 4: Используем координаты центра города (гарантированный результат)
        if (cityLat && cityLon && !isNaN(cityLat) && !isNaN(cityLon)) {
            if (latInput) latInput.value = cityLat;
            if (lonInput) lonInput.value = cityLon;

            document.getElementById('locationStatus').innerHTML =
                '<div class="alert alert-success">' +
                '<i class="bi bi-check-circle"></i> Координаты установлены!<br>' +
                '<small>Координаты: ' + cityLat.toFixed(6) + ', ' + cityLon.toFixed(6) + '</small><br>' +
                '<small>Используются координаты центра города ' + escapeHtml(city) + '</small><br>' +
                '<small>Адрес: ' + escapeHtml(houseNumber + ', ' + street + ', ' + city + (country ? ', ' + country : '')) + '</small>' +
                '</div>';
            return;
        }

        // Если ничего не сработало
        document.getElementById('locationStatus').innerHTML =
            '<div class="alert alert-danger">' +
            '<i class="bi bi-exclamation-triangle"></i> Не удалось найти адрес на карте.<br>' +
            '<small>Проверьте правильность введенного адреса. Убедитесь, что указаны: город, улица и номер дома.</small><br>' +
            '<small>Попробуйте:</small><br>' +
            '<small>• Убедиться, что город определен автоматически или выбран из списка</small><br>' +
            '<small>• Убедиться, что улица выбрана из списка после выбора города</small><br>' +
            '<small>• Проверить правильность написания названия улицы</small>' +
            '</div>';
    }

    // Предпросмотр фото
    document.addEventListener('DOMContentLoaded', function() {
        const photoInput = document.getElementById('photo');
        const photoPreview = document.getElementById('photo-preview');
        const photoPreviewImg = document.getElementById('photo-preview-img');

        if (photoInput) {
            photoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        photoPreviewImg.src = e.target.result;
                        photoPreview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                } else {
                    photoPreview.style.display = 'none';
                }
            });
        }

        // Инициализируем автодополнение
        initCityAutocomplete();
        initStreetHandlers();
        
        // Обновляем валюту и информацию о городе при загрузке
        if (selectedCityData) {
            updateCurrency(selectedCityData);
            updateCitySelectedInfo(selectedCityData);
            // Загружаем улицы для существующего города
            if (selectedCity && selectedCity.length >= 2) {
                loadCityStreets(selectedCity, selectedCityData);
            }
        } else {
            // Инициализируем валюту по умолчанию (тенге для Казахстана)
            updateCurrency(null);
        }

        // Валидация формы перед отправкой
        const form = document.querySelector('.event-edit-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const latitude = document.getElementById('latitude').value;
                const longitude = document.getElementById('longitude').value;

                if (!latitude || !longitude) {
                    e.preventDefault();
                    document.getElementById('locationStatus').innerHTML =
                        '<div class="alert alert-danger">' +
                        '<i class="bi bi-exclamation-triangle"></i> Необходимо найти адрес на карте перед отправкой формы!<br>' +
                        '<small>Заполните поля адреса (город, улица, номер дома) и нажмите кнопку "Найти адрес на карте"</small>' +
                        '</div>';

                    // Прокручиваем к сообщению об ошибке
                    document.getElementById('locationStatus').scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    return false;
                }
            });
        }
    });
</script>

<?php
$content = ob_get_clean();
$title = 'Редактировать мероприятие';
include __DIR__ . '/../layout.php';
?>

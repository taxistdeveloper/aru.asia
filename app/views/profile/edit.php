<?php

/**
 * РЕДАКТИРОВАНИЕ ПРОФИЛЯ
 */

ob_start();
?>

<style>
    /* Стили для Toast уведомлений */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        max-width: 400px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .custom-toast {
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        border: none;
        backdrop-filter: blur(10px);
        animation: slideInRight 0.3s ease-out;
    }

    .custom-toast .toast-header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        background: transparent;
        padding: 12px 16px;
        border-radius: 12px 12px 0 0;
    }

    .custom-toast .toast-body {
        padding: 14px 16px;
        font-size: 15px;
        line-height: 1.5;
    }

    .custom-toast.toast-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }

    .custom-toast.toast-danger .toast-header {
        color: white;
        border-bottom-color: rgba(255, 255, 255, 0.2);
    }

    .custom-toast.toast-danger .btn-close {
        filter: invert(1);
    }

    .custom-toast.toast-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        color: #212529;
    }

    .custom-toast.toast-success {
        background: linear-gradient(135deg, #28a745 0%, #218838 100%);
        color: white;
    }

    .custom-toast.toast-success .toast-header {
        color: white;
        border-bottom-color: rgba(255, 255, 255, 0.2);
    }

    .custom-toast.toast-success .btn-close {
        filter: invert(1);
    }

    .custom-toast.toast-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
    }

    .custom-toast.toast-info .toast-header {
        color: white;
        border-bottom-color: rgba(255, 255, 255, 0.2);
    }

    .custom-toast.toast-info .btn-close {
        filter: invert(1);
    }

    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }

        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }

    .custom-toast.hiding {
        animation: slideOutRight 0.3s ease-out forwards;
    }

    @media (max-width: 576px) {
        .toast-container {
            top: 10px;
            right: 10px;
            left: 10px;
            max-width: none;
        }
    }

    .mobile-form-container .btn-location {
        width: auto;
        padding: 5px 12px;
        font-size: 13px;
        font-weight: 500;
        margin-top: 0;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
        white-space: nowrap;
    }

    /* Стили для фотографий */
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

    @media (min-width: 768px) {
        .photos-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
</style>

<!-- Toast контейнер для уведомлений -->
<div class="toast-container" id="toastContainer"></div>

<div class="mobile-form-container">
    <h2 class="mb-4">Редактировать профиль</h2>

    <?php if (isset($isBlocked) && $isBlocked && !empty($adminRemark)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5 class="alert-heading">
                <i class="bi bi-exclamation-triangle-fill"></i> Ваш профиль заблокирован
            </h5>
            <?php
            $fieldNames = [
                'full_name' => 'ФИО (Полное имя)',
                'about' => 'О себе',
                'photo' => 'Фотография'
            ];
            $fieldName = isset($remarkType) && isset($fieldNames[$remarkType]) ? $fieldNames[$remarkType] : null;
            ?>
            <?php if ($fieldName): ?>
                <p class="mb-2"><strong>Замечание по полю: <span class="text-warning"><?= Helper::escape($fieldName) ?></span></strong></p>
            <?php else: ?>
                <p class="mb-2"><strong>Замечание от администратора:</strong></p>
            <?php endif; ?>
            <p class="mb-3"><?= nl2br(Helper::escape($adminRemark)) ?></p>
            <p class="mb-0">
                <strong>Исправьте указанные ошибки и сохраните профиль. После сохранения профиль будет автоматически разблокирован.</strong>
            </p>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?= Helper::escape($error) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['photo_upload_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?= Helper::escape($_SESSION['photo_upload_error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['photo_upload_error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['photo_upload_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?= Helper::escape($_SESSION['photo_upload_success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['photo_upload_success']); ?>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>profile/update" enctype="multipart/form-data" id="profileEditForm">
        <input type="hidden" id="photoCount" value="<?= count($photos) ?>">
        <input type="hidden" id="minPhotos" value="<?= MIN_PHOTOS ?>">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Основная информация</h5>

                <div class="mb-3">
                    <label for="full_name" class="form-label">ФИО *</label>
                    <input type="text"
                        class="form-control"
                        id="full_name"
                        name="full_name"
                        value="<?= Helper::escape($user['full_name'] ?? '') ?>"
                        placeholder="Введите ваше полное имя"
                        required>
                </div>

                <div class="mb-3">
                    <label for="gender" class="form-label">Пол *</label>
                    <select class="form-select" id="gender" name="gender" required>
                        <option value="">Выберите пол</option>
                        <option value="male" <?= $user['gender'] === 'male' ? 'selected' : '' ?>>Мужской</option>
                        <option value="female" <?= $user['gender'] === 'female' ? 'selected' : '' ?>>Женский</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="age" class="form-label">Возраст *</label>
                    <input type="number"
                        class="form-control"
                        id="age"
                        name="age"
                        value="<?= $user['age'] ?? '' ?>"
                        required
                        min="18"
                        max="100">
                    <?php if ($user['age_changes_count'] >= 2): ?>
                        <div class="alert alert-warning mt-2">
                            <strong>Внимание!</strong> Вы уже изменили возраст максимальное количество раз (2 раза).
                            Это последнее изменение!
                        </div>
                    <?php elseif ($user['age_changes_count'] > 0): ?>
                        <small class="text-muted">
                            Вы уже изменили возраст <?= $user['age_changes_count'] ?> раз(а).
                            Осталось <?= 2 - $user['age_changes_count'] ?> изменение(й).
                        </small>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="marital_status" class="form-label">Семейный статус *</label>
                    <select class="form-select" id="marital_status" name="marital_status" required>
                        <option value="">Выберите семейный статус</option>
                        <option value="single" <?= ($user['marital_status'] ?? '') === 'single' ? 'selected' : '' ?>>Холост/Не замужем</option>
                        <option value="married" <?= ($user['marital_status'] ?? '') === 'married' ? 'selected' : '' ?>>Женат/Замужем</option>
                    </select>
                    <small class="text-muted">
                        Если вы выберете "Женат/Замужем", ваша кнопка "Создать свидание" будет скрыта,
                        и ваш профиль не будет отображаться в общем поиске.
                    </small>
                </div>

                <!-- Геолокация -->
                <div class="mb-3">
                    <label class="form-label">Местоположение *</label>
                    <div>
                        <button type="button" class="btn btn-primary btn-sm btn-location d-inline-flex align-items-center gap-1" onclick="getLocation()">
                            <i class="bi bi-geo-alt"></i> Определить местоположение
                        </button>
                        <input type="hidden" id="latitude" name="latitude" value="<?= $user['latitude'] ?? '' ?>">
                        <input type="hidden" id="longitude" name="longitude" value="<?= $user['longitude'] ?? '' ?>">
                        <div id="locationStatus" class="mt-2"></div>
                        <small class="text-muted d-block mt-1">
                            Разрешите доступ к геолокации, чтобы автоматически определить страну и город
                        </small>
                    </div>
                </div>

                <!-- Поля для страны и города (скрыты по умолчанию, показываются при необходимости) -->
                <div class="mb-3" id="countryField" style="display: none;">
                    <label for="country" class="form-label">Страна *</label>
                    <input type="text"
                        class="form-control"
                        id="country"
                        name="country"
                        value="<?= Helper::escape($user['country'] ?? '') ?>"
                        placeholder="Введите страну"
                        required>
                </div>

                <div class="mb-3" id="cityField" style="display: none;">
                    <label for="city" class="form-label">Город *</label>
                    <input type="text"
                        class="form-control"
                        id="city"
                        name="city"
                        value="<?= Helper::escape($user['city'] ?? '') ?>"
                        placeholder="Введите город"
                        required>
                </div>

                <div class="mb-3">
                    <label for="about" class="form-label">О себе *</label>
                    <textarea class="form-control"
                        id="about"
                        name="about"
                        rows="4"
                        required><?= Helper::escape($user['about'] ?? '') ?></textarea>
                    <div class="invalid-feedback">
                        В поле «О себе» запрещены мат и бессмысленный набор символов.
                    </div>
                    <small class="form-text text-muted">
                        Строк: <span id="aboutLinesCounter">1 / 10</span>
                    </small>
                </div>
            </div>
        </div>

        <!-- Фотографии -->
        <div class="card mb-4 profile-card">
            <div class="card-body">
                <h5 class="card-title photos-title">
                    <i class="bi bi-images"></i> Фотографии * (<?= count($photos) ?>/<?= MAX_PHOTOS ?>)
                </h5>
                <p class="text-danger small mb-3">
                    <i class="bi bi-exclamation-triangle-fill"></i> Загрузите своё фото. Чужие или случайные изображения приведут к блокировке
                </p>

                <!-- Поле для добавления фотографий (интегрировано в основную форму) -->
                <?php if (count($photos) < MAX_PHOTOS): ?>
                    <div class="mb-3">
                        <label for="photos" class="form-label">Добавить фотографии</label>
                        <input type="file"
                            class="form-control"
                            id="photos"
                            name="photos[]"
                            accept="image/*"
                            multiple>
                        <small class="form-text text-muted">
                            Загрузка до <?= MAX_PHOTOS - count($photos) ?> фотографий. Фотографии загружаются при нажатии кнопки «Сохранить изменения».
                        </small>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle"></i> Достигнут лимит фотографий (<?= MAX_PHOTOS ?>)
                    </div>
                <?php endif; ?>

                <!-- Постоянные фотографии -->
                <?php if (count($photos) > 0): ?>
                    <div class="photos-grid mb-3">
                        <?php foreach ($photos as $photo): ?>
                            <div class="photo-item position-relative">
                                <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . $photo['photo'] ?>"
                                    class="img-fluid"
                                    alt="Фото профиля">
                                <a href="<?= BASE_URL ?>profile/deletePhoto?id=<?= $photo['id'] ?>"
                                    class="btn btn-sm btn-danger text-white position-absolute top-0 end-0 m-2"
                                    onclick="return confirm('Удалить эту фотографию?')"
                                    title="Удалить фотографию">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Временные фотографии (загружены, но еще не сохранены) -->
                <div id="tempPhotosContainer" class="mb-3" style="display: none;">
                    <h6 class="text-muted mb-2">
                        <i class="bi bi-clock"></i> Новые фотографии (будут сохранены при нажатии "Сохранить изменения"):
                    </h6>
                    <div id="tempPhotosGrid" class="photos-grid"></div>
                </div>

                <?php if (count($photos) == 0 && empty($tempPhotos)): ?>
                    <p class="text-muted text-center py-4">
                        <i class="bi bi-image" style="font-size: 2rem;"></i><br>
                        Фотографии не загружены
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <button type="submit" form="profileEditForm" class="btn btn-primary btn-lg w-100">
        <i class="bi bi-check-circle"></i> Сохранить изменения
    </button>
</div>

<script>
    // Получение геолокации и определение страны/города
    function getLocation() {
        if (navigator.geolocation) {
            document.getElementById('locationStatus').innerHTML =
                '<div class="alert alert-info">Определение местоположения...</div>';

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;

                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lon;

                    // Получаем страну и город через обратный геокодинг
                    getCountryAndCity(lat, lon);
                },
                function(error) {
                    let errorMessage = 'Не удалось получить геолокацию. ';
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage += 'Вы запретили доступ к геолокации.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage += 'Информация о местоположении недоступна.';
                            break;
                        case error.TIMEOUT:
                            errorMessage += 'Превышено время ожидания.';
                            break;
                    }
                    document.getElementById('locationStatus').innerHTML =
                        '<div class="alert alert-warning">' + errorMessage + '</div>';
                }
            );
        } else {
            document.getElementById('locationStatus').innerHTML =
                '<div class="alert alert-danger">Геолокация не поддерживается вашим браузером</div>';
        }
    }

    // Получение страны и города по координатам через OpenStreetMap Nominatim API
    function getCountryAndCity(lat, lon) {
        const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&addressdetails=1&accept-language=ru&zoom=10`;

        // Nominatim требует User-Agent заголовок
        fetch(url, {
                headers: {
                    'User-Agent': 'ARU-App/1.0'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Nominatim API response:', data);
                console.log('Full response data:', JSON.stringify(data, null, 2));

                let country = '';
                let city = '';

                // Проверяем наличие данных
                if (!data) {
                    console.warn('No data received from API');
                    document.getElementById('locationStatus').innerHTML =
                        '<div class="alert alert-warning">Геолокация получена, но API не вернул данные. Координаты сохранены.</div>';
                    return;
                }

                // Если есть ошибка в ответе
                if (data.error) {
                    console.error('API returned error:', data.error);
                    document.getElementById('locationStatus').innerHTML =
                        '<div class="alert alert-warning">Ошибка API: ' + data.error + '. Координаты сохранены.</div>';
                    return;
                }

                // Пытаемся извлечь данные из address объекта
                if (data.address) {
                    const address = data.address;

                    // Определяем страну - проверяем все возможные поля
                    country = address.country ||
                        address['country:ru'] ||
                        address['country:en'] ||
                        address.country_code ||
                        address['ISO3166-1:alpha2'] ||
                        address['ISO3166-1:alpha3'] ||
                        '';

                    // Если страна в формате кода, пытаемся получить полное название
                    if (country && country.length <= 3 && data.display_name) {
                        const parts = data.display_name.split(',');
                        const lastPart = parts[parts.length - 1]?.trim();
                        if (lastPart && lastPart.length > 3) {
                            country = lastPart;
                        }
                    }

                    // Определяем город - проверяем все возможные варианты в порядке приоритета
                    city = address.city ||
                        address['city:ru'] ||
                        address['city:en'] ||
                        address.town ||
                        address.village ||
                        address.municipality ||
                        address.city_district ||
                        address.suburb ||
                        address.locality ||
                        address.county ||
                        address.state_district ||
                        address.region ||
                        address.state ||
                        '';
                }

                // Если нет данных в address, пытаемся извлечь из display_name
                if ((!country || !city) && data.display_name) {
                    console.log('Extracting from display_name:', data.display_name);
                    const parts = data.display_name.split(',').map(p => p.trim()).filter(p => p);

                    // Страна - обычно последний элемент
                    if (!country && parts.length > 0) {
                        // Пробуем последние 2 элемента (иногда перед страной идет регион)
                        for (let i = parts.length - 1; i >= Math.max(0, parts.length - 3); i--) {
                            const part = parts[i];
                            // Пропускаем коды стран, номера и короткие строки
                            if (part &&
                                part.length > 3 &&
                                !/^[A-Z]{2,3}$/.test(part) &&
                                !/^\d+$/.test(part) &&
                                !part.match(/^(область|регион|район|region|oblast)/i)) {
                                country = part;
                                break;
                            }
                        }
                    }

                    // Город - ищем в середине списка
                    if (!city && parts.length > 2) {
                        // Пропускаем первый элемент (обычно адрес) и последние 1-2 (регион/страна)
                        const startIndex = 1;
                        const endIndex = Math.max(startIndex, parts.length - 2);

                        for (let i = startIndex; i < endIndex; i++) {
                            const part = parts[i];
                            // Пропускаем номера, улицы, короткие строки и служебные слова
                            if (part &&
                                part.length > 2 &&
                                !/^\d+$/.test(part) &&
                                !part.match(/^(улица|ул\.|проспект|пр\.|переулок|пер\.|бульвар|бул\.|площадь|пл\.|область|регион|район)/i)) {
                                city = part;
                                break;
                            }
                        }
                    }
                }

                // Если всё ещё нет данных, пробуем альтернативный формат парсинга
                if ((!country || !city) && data.display_name) {
                    // Пробуем найти паттерны типа "Город, Регион, Страна"
                    const displayParts = data.display_name.split(',').map(p => p.trim()).filter(p => p);

                    if (displayParts.length >= 2) {
                        // Последний элемент - страна
                        if (!country && displayParts.length > 0) {
                            const last = displayParts[displayParts.length - 1];
                            if (last && last.length > 3) {
                                country = last;
                            }
                        }

                        // Предпоследний или второй элемент - город
                        if (!city) {
                            if (displayParts.length >= 3) {
                                city = displayParts[displayParts.length - 2] || displayParts[1];
                            } else if (displayParts.length >= 2) {
                                city = displayParts[0];
                            }
                        }
                    }
                }

                // Преобразуем длинные названия городов в короткие
                if (city) {
                    city = normalizeCityName(city);
                }

                // Если данные не извлечены, пробуем альтернативный метод парсинга
                if (!country && !city && data.display_name) {
                    console.log('Trying alternative parsing method...');
                    // Пробуем более простой парсинг display_name
                    const simpleParts = data.display_name.split(',').map(p => p.trim());
                    if (simpleParts.length >= 2) {
                        // Берем последний элемент как страну
                        country = simpleParts[simpleParts.length - 1];
                        // Берем предпоследний или первый как город
                        if (simpleParts.length >= 3) {
                            city = simpleParts[simpleParts.length - 2] || simpleParts[0];
                        } else {
                            city = simpleParts[0];
                        }

                        // Очищаем от лишних символов
                        country = country.replace(/[^\p{L}\s-]/gu, '').trim();
                        city = city.replace(/[^\p{L}\s-]/gu, '').trim();

                        if (city) {
                            city = normalizeCityName(city);
                        }

                        console.log('Alternative parsing - country:', country, 'city:', city);
                    }
                }

                // Логируем финальные извлеченные данные
                console.log('Final extracted country:', country);
                console.log('Final extracted city:', city);

                // Заполняем поля автоматически
                if (country) {
                    document.getElementById('country').value = country;
                    document.getElementById('countryField').style.display = 'none';
                } else {
                    // Если страна не найдена, скрываем поле (не требуем ручного ввода)
                    document.getElementById('countryField').style.display = 'none';
                }

                if (city) {
                    document.getElementById('city').value = city;
                    document.getElementById('cityField').style.display = 'none';
                } else {
                    // Если город не найден, скрываем поле (не требуем ручного ввода)
                    document.getElementById('cityField').style.display = 'none';
                }

                // Заполняем поля, если данные найдены
                if (country) {
                    document.getElementById('country').value = country;
                }
                if (city) {
                    document.getElementById('city').value = city;
                }

                // Формируем сообщение о результате
                let message = '<div class="alert alert-success">' +
                    '<i class="bi bi-check-circle"></i> Местоположение определено! ';

                if (country && city) {
                    message += `Страна: ${country}, Город: ${city}`;
                } else if (country) {
                    message += `Страна: ${country}`;
                } else if (city) {
                    message += `Город: ${city}`;
                } else {
                    message += 'Координаты сохранены. Проверьте консоль браузера для отладки.';
                }

                message += '</div>';
                document.getElementById('locationStatus').innerHTML = message;
            })
            .catch(error => {
                console.error('Ошибка при получении адреса:', error);
                // При ошибке не показываем поля для ручного ввода, просто сообщаем
                document.getElementById('countryField').style.display = 'none';
                document.getElementById('cityField').style.display = 'none';
                document.getElementById('locationStatus').innerHTML =
                    '<div class="alert alert-warning">Геолокация получена, но не удалось определить адрес автоматически. ' +
                    'Координаты сохранены.</div>';
            });
    }

    // Функция для нормализации названия города
    function normalizeCityName(city) {
        if (!city) return city;

        // Специальные случаи для известных городов
        const specialCases = {
            'Карагандинская городская администрация': 'Караганда',
            'Алматинская городская администрация': 'Алматы',
            'Астанинская городская администрация': 'Астана',
        };

        // Проверяем специальные случаи
        if (specialCases[city]) {
            return specialCases[city];
        }

        // Универсальная обработка паттерна "Названиеская/ая городская администрация"
        const match = city.match(/^(.+?)(?:ская|ая)\s+городская\s+администрация$/i);
        if (match) {
            const cityName = match[1].trim();

            // Специальные случаи для корней названий
            const rootCases = {
                'Карагандин': 'Караганда',
                'Алматин': 'Алматы',
                'Астанин': 'Астана',
            };

            if (rootCases[cityName]) {
                return rootCases[cityName];
            }

            // Универсальное преобразование: убираем "ин" в конце, если есть
            return cityName.replace(/ин$/, '');
        }

        return city;
    }

    // Функция для показа красивых Toast уведомлений
    function showToast(message, type = 'danger', duration = 5000) {
        const toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) return;

        // Создаем уникальный ID для toast
        const toastId = 'toast-' + Date.now();

        // Иконки для разных типов
        const icons = {
            'danger': '<i class="bi bi-exclamation-triangle-fill"></i>',
            'warning': '<i class="bi bi-exclamation-circle-fill"></i>',
            'success': '<i class="bi bi-check-circle-fill"></i>',
            'info': '<i class="bi bi-info-circle-fill"></i>'
        };

        // Заголовки для разных типов
        const headers = {
            'danger': 'Ошибка',
            'warning': 'Внимание',
            'success': 'Успешно',
            'info': 'Информация'
        };

        // Создаем HTML для toast
        const toastHTML = `
            <div id="${toastId}" class="toast custom-toast toast-${type}" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="${duration}">
                <div class="toast-header">
                    <strong class="me-auto">${icons[type] || ''} ${headers[type] || 'Уведомление'}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Закрыть"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;

        // Добавляем toast в контейнер
        toastContainer.insertAdjacentHTML('beforeend', toastHTML);

        // Инициализируем Bootstrap Toast
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: duration
        });

        // Показываем toast
        toast.show();

        // Удаляем элемент после скрытия
        toastElement.addEventListener('hidden.bs.toast', function() {
            toastElement.remove();
        });

        return toast;
    }

    // Функция проверки текста "О себе" на нецензурную лексику (многоязычная — RU, EN и др.)
    const profanityPatterns = <?= json_encode(ProfanityFilter::getJsPatternSources()) ?>;

    function hasProfanity(text) {
        if (!text) return false;
        try {
            return profanityPatterns.some((src) => new RegExp(src, 'giu').test(text));
        } catch (e) {
            console.warn('Profanity filter regex error:', e);
            return false;
        }
    }

    // Функция проверки "бессмысленного" текста в поле "О себе"
    function isGibberishAbout(text) {
        if (!text) return true;

        const normalized = text.replace(/\s+/g, ' ').trim();
        if (normalized.length < 20) {
            return true; // слишком коротко
        }

        const noSpaces = normalized.replace(/\s+/g, '');

        // Только "хз", "хзхз" и т.п.
        if (/^(хз)+$/i.test(noSpaces)) {
            return true;
        }

        const hasLatin = /[A-Za-z]/.test(normalized);
        const hasCyrillic = /[А-Яа-яЁё]/.test(normalized);

        // Смешение латиницы и кириллицы считаем мусором (asdsadsa ывавыа)
        if (hasLatin && hasCyrillic) {
            return true;
        }

        // Если вообще нет гласных (рус/англ) — тоже считаем мусором
        if (!/[аеёиоуыэюяaeiouy]/i.test(normalized)) {
            return true;
        }

        return false;
    }

    // Функция для сохранения данных формы в localStorage
    function saveFormData() {
        const form = document.getElementById('profileEditForm');
        if (!form) return;

        const formData = {};
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            if (input.type === 'checkbox' || input.type === 'radio') {
                formData[input.name] = input.checked ? input.value : '';
            } else {
                formData[input.name] = input.value;
            }
        });

        localStorage.setItem('profileEditFormData', JSON.stringify(formData));
    }

    // Функция для восстановления данных формы из localStorage
    function restoreFormData() {
        const savedData = localStorage.getItem('profileEditFormData');
        if (!savedData) return;

        try {
            const formData = JSON.parse(savedData);
            const form = document.getElementById('profileEditForm');
            if (!form) return;

            Object.keys(formData).forEach(name => {
                const input = form.querySelector(`[name="${name}"]`);
                if (input) {
                    if (input.type === 'checkbox' || input.type === 'radio') {
                        input.checked = (input.value === formData[name]);
                    } else {
                        input.value = formData[name];
                    }
                }
            });

            // Очищаем сохраненные данные после восстановления
            localStorage.removeItem('profileEditFormData');
        } catch (e) {
            console.error('Ошибка при восстановлении данных формы:', e);
            localStorage.removeItem('profileEditFormData');
        }
    }

    // Автоматическое определение местоположения при загрузке страницы, если координаты не заполнены
    document.addEventListener('DOMContentLoaded', function() {
        // Восстанавливаем данные формы, если они были сохранены
        restoreFormData();

        const lat = document.getElementById('latitude').value;
        const lon = document.getElementById('longitude').value;
        const country = document.getElementById('country').value;
        const city = document.getElementById('city').value;

        // Поля скрыты по умолчанию - всё определяется автоматически
        document.getElementById('countryField').style.display = 'none';
        document.getElementById('cityField').style.display = 'none';

        // Если координаты есть, но страна или город не заполнены, определяем их автоматически
        if (lat && lon && (!country || !city)) {
            getCountryAndCity(lat, lon);
        }

        // Счётчик строк для "О себе"
        const aboutField = document.getElementById('about');
        const aboutLinesCounter = document.getElementById('aboutLinesCounter');
        const maxAboutLines = 10;

        function updateAboutLines() {
            if (!aboutField || !aboutLinesCounter) return;
            const lines = aboutField.value.split(/\r\n|\r|\n/).length || 1;
            aboutLinesCounter.textContent = lines + ' / ' + maxAboutLines;
            if (lines > maxAboutLines) {
                aboutLinesCounter.classList.add('text-danger');
            } else {
                aboutLinesCounter.classList.remove('text-danger');
            }
        }

        if (aboutField && aboutLinesCounter) {
            updateAboutLines();
            aboutField.addEventListener('input', function() {
                let lines = this.value.split(/\r\n|\r|\n/);
                if (lines.length > maxAboutLines) {
                    this.value = lines.slice(0, maxAboutLines).join('\n');
                }
                updateAboutLines();
            });
        }

        // Сохраняем данные формы при каждом изменении
        const profileForm = document.getElementById('profileEditForm');
        if (profileForm) {
            profileForm.addEventListener('input', saveFormData);
            profileForm.addEventListener('change', saveFormData);
        }

        // Валидация формы перед отправкой
        if (profileForm) {
            profileForm.addEventListener('submit', function(e) {
                // Проверка местоположения
                const latitude = document.getElementById('latitude').value.trim();
                const longitude = document.getElementById('longitude').value.trim();

                if (!latitude || !longitude) {
                    e.preventDefault();
                    showToast('Пожалуйста, определите местоположение, нажав кнопку "Определить местоположение"', 'danger', 6000);
                    document.getElementById('locationStatus').innerHTML =
                        '<div class="alert alert-danger">Местоположение обязательно для заполнения. Пожалуйста, нажмите кнопку "Определить местоположение"</div>';
                    // Прокручиваем к местоположению
                    document.querySelector('[onclick="getLocation()"]').scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    return false;
                }

                // Проверка описания "О себе" на наличие мата и "бессмысленного" текста
                const aboutField = document.getElementById('about');
                if (aboutField) {
                    const aboutText = aboutField.value.trim();

                    if (hasProfanity(aboutText)) {
                        e.preventDefault();
                        showToast('В поле "О себе" запрещена нецензурная лексика. Удалите её и попробуйте ещё раз.', 'danger', 7000);
                        aboutField.classList.add('is-invalid');
                        aboutField.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        aboutField.focus();
                        return false;
                    }

                    if (isGibberishAbout(aboutText)) {
                        e.preventDefault();
                        showToast('Пожалуйста, опишите себя нормальным текстом. Короткие или бессмысленные наборы символов (типа "хз", "asdasd", "ывавыавы") в поле "О себе" запрещены.', 'warning', 8000);
                        aboutField.classList.add('is-invalid');
                        aboutField.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        aboutField.focus();
                        return false;
                    }

                    aboutField.classList.remove('is-invalid');
                }

                // Проверка фотографий (учитываем как постоянные, так и временные)
                const photoCount = parseInt(document.getElementById('photoCount').value) || 0;
                const tempPhotoCount = document.querySelectorAll('#tempPhotosGrid .photo-item').length;
                const totalPhotoCount = photoCount + tempPhotoCount;
                const minPhotos = parseInt(document.getElementById('minPhotos').value) || 1;

                if (totalPhotoCount < minPhotos) {
                    e.preventDefault();
                    showToast('Пожалуйста, загрузите хотя бы ' + minPhotos + ' фотографию(и) для завершения профиля', 'warning', 6000);
                    // Прокручиваем к секции фотографий
                    document.querySelector('.profile-card').scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    return false;
                }

                // Очищаем сохраненные данные при успешной отправке
                localStorage.removeItem('profileEditFormData');
            });
        }

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

        // Автоматическая загрузка фотографий при выборе файла (временное хранилище)
        const fileInput = document.getElementById('photos');
        const tempPhotosContainer = document.getElementById('tempPhotosContainer');
        const tempPhotosGrid = document.getElementById('tempPhotosGrid');

        // Загружаем временные фотографии из PHP, если они есть
        <?php if (!empty($tempPhotos)): ?>
            const existingTempPhotos = <?= json_encode($tempPhotos) ?>;
            existingTempPhotos.forEach(function(photo) {
                addTempPhotoToGrid(photo.filename, '<?= BASE_URL . UPLOAD_DIR . "photos/" ?>' + photo.filename);
            });
        <?php endif; ?>

        function addTempPhotoToGrid(filename, url) {
            const photoItem = document.createElement('div');
            photoItem.className = 'photo-item position-relative';
            photoItem.dataset.filename = filename;
            photoItem.innerHTML = `
                <img src="${url}" class="img-fluid" alt="Временное фото">
                <button type="button" class="btn btn-sm btn-danger text-white position-absolute top-0 end-0 m-2 delete-temp-photo" 
                    data-filename="${filename}" title="Удалить фотографию">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            tempPhotosGrid.appendChild(photoItem);
            tempPhotosContainer.style.display = 'block';
        }

        function removeTempPhotoFromGrid(filename) {
            const photoItem = tempPhotosGrid.querySelector(`[data-filename="${filename}"]`);
            if (photoItem) {
                photoItem.remove();
            }
            if (tempPhotosGrid.children.length === 0) {
                tempPhotosContainer.style.display = 'none';
            }
        }

        // Обработчик выбора файлов - автоматическая загрузка
        if (fileInput) {
            fileInput.addEventListener('change', async function(e) {
                if (!this.files || this.files.length === 0) {
                    return;
                }

                const formData = new FormData();
                Array.from(this.files).forEach(file => {
                    formData.append('photos[]', file);
                });

                // Показываем индикатор загрузки
                const originalText = this.nextElementSibling.textContent;
                this.nextElementSibling.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Загрузка...';
                this.disabled = true;

                try {
                    const response = await fetch('<?= BASE_URL ?>profile/uploadTempPhoto', {
                        method: 'POST',
                        body: formData
                    });

                    const text = await response.text();
                    let result;
                    try {
                        result = text ? JSON.parse(text) : {};
                    } catch (e) {
                        console.error('Ответ сервера не JSON:', text.slice(0, 500));
                        showToast('Ответ сервера в неверном формате. Попробуйте ещё раз или выберите другие фото.', 'danger', 6000);
                        return;
                    }

                    if (result.success && result.files) {
                        // Добавляем загруженные фотографии в сетку
                        result.files.forEach(function(file) {
                            addTempPhotoToGrid(file.filename, file.url);
                        });
                        showToast(result.message || 'Фотографии загружены', 'success', 3000);
                        // Очищаем input
                        this.value = '';
                    } else {
                        showToast(result.error || 'Ошибка при загрузке фотографий', 'danger', 5000);
                    }
                } catch (error) {
                    console.error('Ошибка:', error);
                    showToast('Произошла ошибка при загрузке фотографий', 'danger', 5000);
                } finally {
                    this.disabled = false;
                    this.nextElementSibling.textContent = originalText;
                }
            });
        }

        // Обработчик удаления временных фотографий
        document.addEventListener('click', async function(e) {
            if (e.target.closest('.delete-temp-photo')) {
                const button = e.target.closest('.delete-temp-photo');
                const filename = button.dataset.filename;

                if (!confirm('Удалить эту фотографию?')) {
                    return;
                }

                try {
                    const response = await fetch(`<?= BASE_URL ?>profile/deleteTempPhoto?filename=${encodeURIComponent(filename)}`);
                    const text = await response.text();
                    let result;
                    try {
                        result = text ? JSON.parse(text) : {};
                    } catch (e) {
                        console.error('Ответ сервера не JSON:', text.slice(0, 200));
                        showToast('Ответ сервера в неверном формате', 'danger', 4000);
                        return;
                    }

                    if (result.success) {
                        removeTempPhotoFromGrid(filename);
                        showToast('Фотография удалена', 'success', 2000);
                    } else {
                        showToast(result.error || 'Ошибка при удалении фотографии', 'danger', 3000);
                    }
                } catch (error) {
                    console.error('Ошибка:', error);
                    showToast('Произошла ошибка при удалении фотографии', 'danger', 3000);
                }
            }
        });
    });
</script>

<?php
$content = ob_get_clean();
$title = 'Редактировать профиль';
include __DIR__ . '/../layout.php';
?>
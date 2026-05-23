<?php

/**
 * СТРАНИЦА КАРТЫ С МЕРОПРИЯТИЯМИ
 */

ob_start();

/**
 * Форматирование числа для вывода в JavaScript (всегда точка как десятичный разделитель).
 * Исключает ошибки из-за локали PHP (например, "51,1694" вместо "51.1694").
 */
function jsCoord($value)
{
    if ($value === null || $value === '') {
        return '0';
    }
    return number_format((float) $value, 8, '.', '');
}

// Функция форматирования адреса в формат: "Страна, Город, улица, номер"
function formatAddress($location)
{
    $locationParts = array_map('trim', explode(',', $location));
    $count = count($locationParts);
    
    $result = '';

    // Если формат: "Третий этаж, офис номер 6, 46, Лободы, Караганда, Казахстан" (6 частей)
    if ($count === 6) {
        // [0]="Третий этаж", [1]="офис номер 6", [2]="46", [3]="Лободы", [4]="Караганда", [5]="Казахстан"
        // Пропускаем первые две части (доп. инфо), берем: страна, город, улица, номер
        $result = $locationParts[5] . ', ' .      // Казахстан
            $locationParts[4] . ', ' .    // Караганда
            $locationParts[3] . ', ' .    // Лободы
            $locationParts[2];            // 46
    }
    // Если формат: "БЦ Казахстан, 66, Бухар Жырау, Караганда, Казахстан" (5 частей)
    elseif ($count === 5) {
        // [0]="БЦ Казахстан" (доп. инфо), [1]="66", [2]="Бухар Жырау", [3]="Караганда", [4]="Казахстан"
        // Пропускаем первую часть, берем: страна, город, улица, номер
        $result = $locationParts[4] . ', ' .     // Казахстан
            $locationParts[3] . ', ' .    // Караганда
            $locationParts[2] . ', ' .     // Бухар Жырау
            $locationParts[1];             // 66
    }
    // Если формат: "46, Лободы, Караганда, Казахстан" (4 части)
    elseif ($count === 4) {
        // [0]="46", [1]="Лободы", [2]="Караганда", [3]="Казахстан"
        $result = $locationParts[3] . ', ' .     // Казахстан
            $locationParts[2] . ', ' .     // Караганда
            $locationParts[1] . ', ' .     // Лободы
            $locationParts[0];             // 46
    }
    // Если формат: "Лободы, 46, Караганда, Казахстан" (4 части, другой порядок)
    elseif ($count >= 3) {
        // Пытаемся найти страну (обычно последний элемент)
        $country = end($locationParts);
        // Пытаемся найти город (предпоследний элемент)
        $city = $locationParts[$count - 2] ?? '';
        // Остальные части - улица и номер дома
        $remainingParts = array_slice($locationParts, 0, $count - 2);

        // Ищем номер дома (элемент, содержащий только число)
        $houseNumber = '';
        $street = '';

        for ($i = 0; $i < count($remainingParts); $i++) {
            if (preg_match('/^\d+$/', $remainingParts[$i])) {
                $houseNumber = $remainingParts[$i];
                // Улица - это другой элемент
                $streetParts = [];
                for ($j = 0; $j < count($remainingParts); $j++) {
                    if ($j != $i) {
                        $streetParts[] = $remainingParts[$j];
                    }
                }
                $street = implode(' ', $streetParts);
                break;
            }
        }

        // Если не нашли номер дома, берем первую часть как улицу, вторую как номер
        if (empty($houseNumber) || empty($street)) {
            $street = $remainingParts[0] ?? '';
            $houseNumber = $remainingParts[1] ?? '';
        }

        $result = $country . ', ' . $city . ', ' . $street . ', ' . $houseNumber;
    }
    // Если формат не распознан, возвращаем как есть
    else {
        return $location;
    }
    
    // Убираем дубликаты из результата
    $resultParts = array_map('trim', explode(',', $result));
    $uniqueParts = [];
    $seen = [];
    
    foreach ($resultParts as $part) {
        $partLower = mb_strtolower($part, 'UTF-8');
        if (!empty($part) && !isset($seen[$partLower])) {
            $uniqueParts[] = $part;
            $seen[$partLower] = true;
        }
    }
    
    return implode(', ', $uniqueParts);
}
?>

<div class="mobile-page-container">
    <h2 class="mb-4">Карта мероприятий</h2>

    <?php if (Helper::isLoggedIn() && (empty($userLat) || empty($userLon))): ?>
        <div class="alert alert-info mb-3">
            <h5>Геолокация не определена</h5>
            <p>Для просмотра мероприятий в вашем районе укажите ваше местоположение в <a href="<?= BASE_URL ?>profile/edit">личном кабинете</a>.</p>
            <p class="mb-0">Ниже показаны все доступные мероприятия на карте.</p>
        </div>
    <?php elseif (!Helper::isLoggedIn()): ?>
        <div class="alert alert-info mb-3">
            <p class="mb-0">Вы можете просматривать все мероприятия на карте. <a href="<?= BASE_URL ?>auth/register">Зарегистрируйтесь</a> чтобы создавать свои мероприятия и видеть их в вашем районе.</p>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Карта -->
        <div class="col-lg-8 mb-4">
            <div id="map" style="height: 500px; border-radius: 10px; overflow: hidden;"></div>
        </div>

        <!-- Список мероприятий -->
        <div class="col-lg-4">
            <h5><?= Helper::isLoggedIn() && $userLat && $userLon ? 'Мероприятия в радиусе 50км' : 'Все мероприятия' ?></h5>
            <?php if (empty($events)): ?>
                <div class="alert alert-info">
                    <?php if (Helper::isLoggedIn() && $userLat && $userLon): ?>
                        В радиусе 50км нет мероприятий
                    <?php else: ?>
                        Пока нет доступных мероприятий. <a href="<?= BASE_URL ?>auth/register">Зарегистрируйтесь</a> чтобы создать мероприятие.
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($events as $event): ?>
                        <?php
                        $formattedLocation = formatAddress($event['location']);
                        ?>
                        <div class="list-group-item mb-2"
                            onclick="showEvent(<?= (int) $event['id'] ?>, <?= jsCoord($event['latitude']) ?>, <?= jsCoord($event['longitude']) ?>)"
                            style="cursor: pointer;">
                            <h6><?= Helper::escape($event['title']) ?></h6>
                            <small class="text-muted">
                                <i class="bi bi-geo-alt"></i>
                                Адрес: <?= Helper::escape($formattedLocation) ?><br>
                                <i class="bi bi-calendar"></i>
                                Дата: <?= date('d.m.Y', strtotime($event['event_date'])) ?> Время: <?= date('H:i', strtotime($event['event_date'])) ?><br>
                                <i class="bi bi-currency-exchange"></i>
                                <?= number_format($event['price'], 0) ?> ₸<br>
                                <?php if (Helper::isLoggedIn() && isset($event['distance']) && $event['distance'] > 0): ?>
                                    <i class="bi bi-rulers"></i>
                                    <?= number_format($event['distance'], 1) ?> км
                                <?php endif; ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Простая карта с использованием Leaflet (OpenStreetMap) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Координаты в Leaflet всегда: широта (lat), долгота (lng) — как в OpenStreetMap / WGS84
    function latLng(lat, lon) {
        return L.latLng(parseFloat(lat), parseFloat(lon));
    }

    var map = L.map('map').setView(
        latLng(<?= jsCoord($mapCenterLat) ?>, <?= jsCoord($mapCenterLon) ?>),
        <?= !empty($events) ? 10 : 6 ?>
    );

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    var bounds = L.latLngBounds();

    // Маркер текущего местоположения (только если пользователь авторизован и есть геолокация)
    <?php if (Helper::isLoggedIn() && !empty($userLat) && !empty($userLon)): ?>
        var userLocationIcon = L.divIcon({
            className: 'user-location-marker',
            html: '<div style="background-color: #dc3545; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"></div>',
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        });
        var userLat = <?= jsCoord($userLat) ?>;
        var userLon = <?= jsCoord($userLon) ?>;
        var userPoint = latLng(userLat, userLon);
        bounds.extend(userPoint);
        L.marker(userPoint, { icon: userLocationIcon })
            .addTo(map)
            .bindPopup('<strong>Вы здесь</strong>')
            .openPopup();
    <?php endif; ?>

    // Маркеры мероприятий — координаты как в Nominatim (lat, lon)
    <?php foreach ($events as $event): ?>
        <?php
        $formattedLocation = formatAddress($event['location']);
        $distanceText = '';
        if (Helper::isLoggedIn() && isset($event['distance']) && $event['distance'] > 0) {
            $distanceText = '<br>📏 Расстояние: ' . number_format($event['distance'], 1) . ' км';
        }
        $popupContent = '<div style="min-width: 200px;">' .
            '<strong>' . Helper::escape($event['title']) . '</strong><br>' .
            '📍 Адрес: ' . Helper::escape($formattedLocation) . '<br>' .
            '📅 Дата: ' . date('d.m.Y', strtotime($event['event_date'])) . ' Время: ' . date('H:i', strtotime($event['event_date'])) . '<br>' .
            '💰 Цена: ' . number_format($event['price'], 0) . ' ₸' . $distanceText .
            '<br><br><a href="' . BASE_URL . 'events#event-' . (int)$event['id'] . '" style="display: block; width: 100%; text-align: center; background-color: #0d6efd; color: white; padding: 8px 12px; border-radius: 4px; text-decoration: none; font-size: 14px; font-weight: 500;">Подробнее</a>' .
            '</div>';
        ?>
        (function() {
            var lat = <?= jsCoord($event['latitude']) ?>;
            var lon = <?= jsCoord($event['longitude']) ?>;
            var point = latLng(lat, lon);
            bounds.extend(point);
            L.marker(point)
                .addTo(map)
                .bindPopup(<?= json_encode($popupContent, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
        })();
    <?php endforeach; ?>

    // Подгоняем область карты под все маркеры (как на OpenStreetMap — всё чётко в кадре)
    if (bounds.isValid() && bounds.getSouthWest().distanceTo(bounds.getNorthEast()) > 0) {
        map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
    }

    function showEvent(id, lat, lon) {
        map.setView(latLng(lat, lon), 15);
    }
</script>

<?php
$content = ob_get_clean();
$title = 'Карта';
include __DIR__ . '/../layout.php';
?>

<?php

/**
 * КОНТРОЛЛЕР КАРТЫ
 *
 * Отображает карту с мероприятиями в радиусе 50км
 */

class MapController
{
    private $eventModel;

    public function __construct()
    {
        $this->eventModel = new Event();
    }

    /**
     * Показывает карту с мероприятиями
     */
    public function index()
    {
        // Проверяем, не заблокирован ли профиль пользователя (только для авторизованных)
        if (Helper::isLoggedIn()) {
            Helper::checkProfileBlocked();
        }

        $events = [];
        $userLat = null;
        $userLon = null;
        $defaultLat = 51.1694; // Широта Алматы (по умолчанию)
        $defaultLon = 71.4491; // Долгота Алматы (по умолчанию)

        // Если пользователь авторизован, получаем его геолокацию
        if (Helper::isLoggedIn()) {
            $userModel = new User();
            $user = $userModel->findById(Helper::getUserId());
            $userLat = $user['latitude'] ?? null;
            $userLon = $user['longitude'] ?? null;
        }

        // Если есть геолокация, получаем мероприятия в радиусе
        if ($userLat && $userLon) {
            $events = $this->eventModel->getInRadius($userLat, $userLon, RADIUS_KM);
        } else {
            // Для неавторизованных или без геолокации показываем все активные мероприятия
            $events = $this->eventModel->getAllActive();
            // Добавляем расстояние как 0 или null, так как геолокации нет
            foreach ($events as &$event) {
                $event['distance'] = 0;
            }
        }

        // Оставляем только мероприятия с валидными координатами (для корректного отображения на карте)
        $eventsWithCoords = [];
        foreach ($events as $e) {
            $lat = isset($e['latitude']) ? (float) $e['latitude'] : null;
            $lon = isset($e['longitude']) ? (float) $e['longitude'] : null;
            if ($lat !== null && $lon !== null
                && $lat >= -90 && $lat <= 90
                && $lon >= -180 && $lon <= 180) {
                $eventsWithCoords[] = $e;
            }
        }
        $events = $eventsWithCoords;

        // Определяем центр карты
        $mapCenterLat = $userLat ?? $defaultLat;
        $mapCenterLon = $userLon ?? $defaultLon;

        // Если есть мероприятия с координатами, центрируем карту по среднему арифметическому
        if (!empty($events)) {
            $avgLat = array_sum(array_column($events, 'latitude')) / count($events);
            $avgLon = array_sum(array_column($events, 'longitude')) / count($events);
            $mapCenterLat = $avgLat;
            $mapCenterLon = $avgLon;
        }

        View::render('map/index', [
            'events' => $events,
            'userLat' => $userLat,
            'userLon' => $userLon,
            'mapCenterLat' => $mapCenterLat,
            'mapCenterLon' => $mapCenterLon,
            'isMobile' => View::isMobile()
        ]);
    }
}

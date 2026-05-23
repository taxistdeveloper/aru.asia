<?php

/**
 * КОНТРОЛЛЕР МЕРОПРИЯТИЙ
 *
 * Обрабатывает создание и просмотр мероприятий
 */

class EventsController
{
    private $eventModel;
    private $userModel;
    private $messageModel;

    public function __construct()
    {
        $this->eventModel = new Event();
        $this->userModel = new User();
        $this->messageModel = new Message();
    }

    /**
     * Показывает список мероприятий
     */
    public function index()
    {
        // Проверяем, не заблокирован ли профиль пользователя (если авторизован)
        if (Helper::isLoggedIn()) {
            Helper::checkProfileBlocked();
        }

        // Автоматически удаляем просроченные мероприятия
        $this->eventModel->deleteExpired();

        $events = [];
        $myEvents = [];
        $myEventChats = [];
        $userLat = null;
        $userLon = null;
        $currentUserId = null;
        $userEvent = null;

        // Если пользователь авторизован
        if (Helper::isLoggedIn()) {
            $currentUserId = Helper::getUserId();
            $user = $this->userModel->findById($currentUserId);
            $userLat = $user['latitude'] ?? null;
            $userLon = $user['longitude'] ?? null;

            // Получаем активное мероприятие пользователя
            $userEvent = $this->eventModel->getByUserId($currentUserId);

            // Получаем мои мероприятия
            $myEvents = $this->eventModel->getAllByUserId($currentUserId);

            // Получаем мероприятия, в которых пользователь участвует в чате
            $eventIdsWithChat = $this->messageModel->getEventIdsWithChat($currentUserId);
            $myEventChats = [];
            if (!empty($eventIdsWithChat)) {
                $myEventChats = $this->eventModel->getByIds($eventIdsWithChat);
                // Исключаем свои мероприятия из списка чатов
                $myEventIds = array_column($myEvents, 'id');
                $myEventChats = array_filter($myEventChats, function($event) use ($myEventIds, $currentUserId) {
                    return !in_array($event['id'], $myEventIds) && (int)$event['user_id'] !== (int)$currentUserId;
                });
                $myEventChats = array_values($myEventChats);
                
                // Добавляем информацию о непрочитанных сообщениях
                foreach ($myEventChats as &$event) {
                    $event['unread_count'] = $this->messageModel->getUnreadCountForEvent($event['id'], $currentUserId);
                }
            }

            // Получаем мероприятия в радиусе
            if ($userLat && $userLon) {
                $events = $this->eventModel->getInRadius($userLat, $userLon, RADIUS_KM);
            } else {
                // Если у пользователя нет геолокации, показываем все одобренные мероприятия
                $events = $this->eventModel->getAllActive();
                // Добавляем расстояние как 0 или null, так как геолокации нет
                foreach ($events as &$event) {
                    $event['distance'] = 0;
                }
            }

            // Добавляем информацию о непрочитанных сообщениях для моих мероприятий
            foreach ($myEvents as &$event) {
                $event['unread_count'] = $this->messageModel->getUnreadCountForEvent($event['id'], $currentUserId);
            }

            // Добавляем информацию о непрочитанных сообщениях для других мероприятий
            foreach ($events as &$event) {
                $event['unread_count'] = $this->messageModel->getUnreadCountForEvent($event['id'], $currentUserId);
            }
        } else {
            // Для неавторизованных пользователей показываем все активные мероприятия
            $events = $this->eventModel->getAllActive();
            // Добавляем расстояние как 0 или null, так как геолокации нет
            foreach ($events as &$event) {
                $event['distance'] = 0;
            }
            $myEventChats = [];
        }

        View::render('events/index', [
            'events' => $events,
            'myEvents' => $myEvents ?? [],
            'myEventChats' => $myEventChats ?? [],
            'currentUserId' => $currentUserId,
            'userLat' => $userLat,
            'userLon' => $userLon,
            'userEvent' => $userEvent,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Показывает форму создания мероприятия
     */
    public function create()
    {
        if (!Helper::isLoggedIn()) {
            Helper::redirect('auth/login');
            return;
        }

        // Проверяем, не заблокирован ли профиль пользователя
        Helper::checkProfileBlocked();

        // Проверяем, заполнен ли профиль
        Helper::checkProfileComplete();

        $userId = Helper::getUserId();
        $userEvent = $this->eventModel->getByUserId($userId);

        // Проверяем что у пользователя еще нет активного мероприятия
        if ($userEvent) {
            $error = "У вас уже есть активное мероприятие";
        }

        View::render('events/create', [
            'error' => $error ?? null,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Сохраняет новое мероприятие
     */
    public function store()
    {
        if (!Helper::isLoggedIn()) {
            Helper::redirect('auth/login');
            return;
        }

        // Проверяем, не заблокирован ли профиль пользователя
        Helper::checkProfileBlocked();

        // Проверяем, заполнен ли профиль
        Helper::checkProfileComplete();

        $userId = Helper::getUserId();

        // Проверяем что у пользователя еще нет активного мероприятия
        if ($this->eventModel->getByUserId($userId)) {
            $_SESSION['error_message'] = 'У вас уже есть активное мероприятие. Создать новое можно после завершения текущего.';
            Helper::redirect('events/create');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Оплата публикации: при EVENT_PUBLISH_PAYMENT_ENABLED === true сюда встраивается проверка оплаты на EVENT_PUBLISH_PRICE_KZT; пока флаг false — создание бесплатное.

            // Формируем полный адрес из отдельных полей
            $city = trim($_POST['city'] ?? '');
            $street = trim($_POST['street'] ?? '');
            $houseNumber = trim($_POST['house_number'] ?? '');
            $additionalInfo = trim($_POST['location'] ?? '');

            // Проверяем обязательные поля адреса
            if (empty($city) || empty($street) || empty($houseNumber)) {
                $_SESSION['error_message'] = 'Заполните все поля адреса: город, улица, номер дома';
                Helper::redirect('events/create');
                return;
            }

            // Проверяем наличие координат или геокодируем адрес на сервере
            $latitude = !empty($_POST['latitude']) ? floatval($_POST['latitude']) : null;
            $longitude = !empty($_POST['longitude']) ? floatval($_POST['longitude']) : null;

            if (!$latitude || !$longitude) {
                // Пробуем получить координаты по адресу через Nominatim
                $geocoded = $this->geocodeAddress($houseNumber, $street, $city, $_POST['country'] ?? '');
                if ($geocoded) {
                    $latitude = $geocoded['lat'];
                    $longitude = $geocoded['lon'];
                }
            }

            if (!$latitude || !$longitude) {
                $_SESSION['error_message'] = 'Не удалось определить координаты адреса. Заполните все поля (город, улица, номер дома) и нажмите кнопку "Найти адрес на карте".';
                Helper::redirect('events/create');
                return;
            }

            // Определяем страну по координатам через reverse geocoding
            $country = $this->getCountryByCoordinates($latitude, $longitude);
            if (!$country) {
                // Если не удалось определить, используем страну из формы или по умолчанию
                $country = $_POST['country'] ?? 'Казахстан';
            }

            // Формируем полный адрес с правильной страной
            $fullAddress = $houseNumber . ', ' . $street . ', ' . $city . ', ' . $country;
            if (!empty($additionalInfo)) {
                $fullAddress = $additionalInfo . ', ' . $fullAddress;
            }

            // Проверка даты мероприятия
            $eventDate = trim($_POST['event_date'] ?? '');
            if (empty($eventDate)) {
                $_SESSION['error_message'] = 'Укажите дату и время проведения мероприятия';
                Helper::redirect('events/create');
                return;
            }
            $eventTimestamp = strtotime($eventDate);
            if ($eventTimestamp === false) {
                $_SESSION['error_message'] = 'Неверный формат даты и времени';
                Helper::redirect('events/create');
                return;
            }
            $endOfCurrentYear = strtotime(date('Y') . '-12-31 23:59:59');
            if ($eventTimestamp > $endOfCurrentYear) {
                $_SESSION['error_message'] = 'Дата проведения не может быть позже 31 декабря ' . date('Y') . ' года';
                Helper::redirect('events/create');
                return;
            }
            // Преобразуем в формат MySQL (YYYY-MM-DD HH:MM:SS)
            $eventDate = date('Y-m-d H:i:s', $eventTimestamp);

            // Обработка загрузки фото (приоритет у результата редактора обрезки — как в рекламе)
            $photoPath = null;
            $photoCroppedBase64 = trim($_POST['photo_cropped_base64'] ?? '');
            if ($photoCroppedBase64 !== '' && strpos($photoCroppedBase64, 'data:image/') === 0) {
                $b64Result = $this->saveEventPhotoFromDataUrl($photoCroppedBase64);
                if ($b64Result['success']) {
                    $photoPath = $b64Result['filename'];
                } else {
                    $_SESSION['error_message'] = $b64Result['error'] ?? 'Ошибка сохранения обрезанного фото';
                    Helper::redirect('events/create');
                    return;
                }
            } elseif (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $photoPath = Helper::uploadFile($_FILES['photo'], UPLOAD_DIR . 'photos/');
                if (!$photoPath) {
                    $_SESSION['error_message'] = 'Ошибка загрузки фото. Проверьте формат файла (JPG, PNG, GIF, WebP) и размер (максимум 10MB).';
                    Helper::redirect('events/create');
                    return;
                }
            }

            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            if (empty($title)) {
                $_SESSION['error_message'] = 'Введите название мероприятия';
                Helper::redirect('events/create');
                return;
            }
            if (empty($description)) {
                $_SESSION['error_message'] = 'Введите описание мероприятия';
                Helper::redirect('events/create');
                return;
            }

            $data = [
                'user_id' => $userId,
                'title' => $title,
                'description' => $description,
                'event_date' => $eventDate,
                'location' => $fullAddress,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'price' => (isset($_POST['price']) && $_POST['price'] !== '' && is_numeric($_POST['price'])) ? (float)$_POST['price'] : 0,
                'photo' => $photoPath,
                'status' => 'pending' // Новые мероприятия создаются со статусом pending
            ];

            if ($this->eventModel->create($data)) {
                $_SESSION['success_message'] = 'Ваше мероприятие отправлено на модерацию. Вы получите уведомление после проверки.';
                Helper::redirect('events');
            } else {
                $_SESSION['error_message'] = 'Ошибка при создании мероприятия. Попробуйте еще раз.';
                Helper::redirect('events/create');
            }
        }

        Helper::redirect('events/create');
    }

    /**
     * Показывает форму редактирования мероприятия
     */
    public function edit()
    {
        if (!Helper::isLoggedIn()) {
            Helper::redirect('auth/login');
            return;
        }

        // Проверяем, не заблокирован ли профиль пользователя
        Helper::checkProfileBlocked();

        // Проверяем, заполнен ли профиль
        Helper::checkProfileComplete();

        $userId = Helper::getUserId();
        $eventId = $_GET['id'] ?? 0;

        if (!$eventId) {
            Helper::redirect('events');
            return;
        }

        $event = $this->eventModel->getById($eventId);

        // Проверяем, что мероприятие принадлежит текущему пользователю
        if (!$event || (int)$event['user_id'] !== (int)$userId) {
            Helper::redirect('events');
            return;
        }

        View::render('events/edit', [
            'event' => $event,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Обновляет существующее мероприятие
     */
    public function update()
    {
        if (!Helper::isLoggedIn()) {
            Helper::redirect('auth/login');
            return;
        }

        $userId = Helper::getUserId();
        $eventId = $_GET['id'] ?? 0;

        if (!$eventId) {
            Helper::redirect('events');
            return;
        }

        // Проверяем, что мероприятие принадлежит текущему пользователю
        $event = $this->eventModel->getById($eventId);
        if (!$event || (int)$event['user_id'] !== (int)$userId) {
            Helper::redirect('events');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Оплата публикации: при EVENT_PUBLISH_PAYMENT_ENABLED === true — проверка оплаты на EVENT_PUBLISH_PRICE_KZT; пока false — правки бесплатны.

            // Формируем полный адрес из отдельных полей
            $city = trim($_POST['city'] ?? '');
            $street = trim($_POST['street'] ?? '');
            $houseNumber = trim($_POST['house_number'] ?? '');
            $additionalInfo = trim($_POST['location'] ?? '');

            // Проверяем обязательные поля адреса
            if (empty($city) || empty($street) || empty($houseNumber)) {
                $_SESSION['error_message'] = 'Заполните все поля адреса: город, улица, номер дома';
                Helper::redirect('events/edit?id=' . $eventId);
                return;
            }

            // Проверяем наличие координат
            $latitude = !empty($_POST['latitude']) ? floatval($_POST['latitude']) : null;
            $longitude = !empty($_POST['longitude']) ? floatval($_POST['longitude']) : null;

            if (!$latitude || !$longitude) {
                $_SESSION['error_message'] = 'Необходимо найти адрес на карте. Нажмите кнопку "Найти адрес на карте" после заполнения адреса.';
                Helper::redirect('events/edit?id=' . $eventId);
                return;
            }

            // Определяем страну по координатам через reverse geocoding
            $country = $this->getCountryByCoordinates($latitude, $longitude);
            if (!$country) {
                // Если не удалось определить, используем страну из формы или по умолчанию
                $country = $_POST['country'] ?? 'Казахстан';
            }

            // Формируем полный адрес с правильной страной
            $fullAddress = $houseNumber . ', ' . $street . ', ' . $city . ', ' . $country;
            if (!empty($additionalInfo)) {
                $fullAddress = $additionalInfo . ', ' . $fullAddress;
            }

            // Обработка загрузки фото (приоритет у обрезанного изображения из редактора)
            $photoPath = $event['photo'] ?? null;
            $photoCroppedBase64 = trim($_POST['photo_cropped_base64'] ?? '');
            if ($photoCroppedBase64 !== '' && strpos($photoCroppedBase64, 'data:image/') === 0) {
                $b64Result = $this->saveEventPhotoFromDataUrl($photoCroppedBase64);
                if ($b64Result['success']) {
                    if (!empty($event['photo']) && file_exists(UPLOAD_DIR . 'photos/' . $event['photo'])) {
                        @unlink(UPLOAD_DIR . 'photos/' . $event['photo']);
                    }
                    $photoPath = $b64Result['filename'];
                } else {
                    $_SESSION['error_message'] = $b64Result['error'] ?? 'Ошибка сохранения обрезанного фото';
                    Helper::redirect('events/edit?id=' . $eventId);
                    return;
                }
            } elseif (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $newPath = Helper::uploadFile($_FILES['photo'], UPLOAD_DIR . 'photos/');
                if (!$newPath) {
                    $_SESSION['error_message'] = 'Ошибка загрузки фото. Проверьте формат файла (JPG, PNG, GIF, WebP) и размер (максимум 10MB).';
                    Helper::redirect('events/edit?id=' . $eventId);
                    return;
                }
                if (!empty($event['photo']) && file_exists(UPLOAD_DIR . 'photos/' . $event['photo'])) {
                    @unlink(UPLOAD_DIR . 'photos/' . $event['photo']);
                }
                $photoPath = $newPath;
            }

            $eventDate = trim($_POST['event_date'] ?? '');
            $endOfCurrentYear = strtotime(date('Y') . '-12-31 23:59:59');
            if (!empty($eventDate) && strtotime($eventDate) > $endOfCurrentYear) {
                $_SESSION['error_message'] = 'Дата проведения не может быть позже 31 декабря ' . date('Y') . ' года';
                Helper::redirect('events/edit?id=' . $eventId);
                return;
            }

            $data = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'event_date' => $eventDate,
                'location' => $fullAddress,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'price' => (isset($_POST['price']) && $_POST['price'] !== '' && is_numeric($_POST['price'])) ? (float)$_POST['price'] : 0,
                'photo' => $photoPath
            ];

            if ($this->eventModel->update($eventId, $userId, $data)) {
                $_SESSION['success_message'] = 'Мероприятие обновлено и отправлено на повторную модерацию.';
                Helper::redirect('events');
            } else {
                $_SESSION['error_message'] = 'Ошибка при обновлении мероприятия. Попробуйте еще раз.';
                Helper::redirect('events/edit?id=' . $eventId);
            }
        }

        Helper::redirect('events');
    }

    /**
     * Удаляет мероприятие
     */
    public function delete()
    {
        if (!Helper::isLoggedIn()) {
            Helper::redirect('auth/login');
            return;
        }

        // Проверяем, не заблокирован ли профиль пользователя
        Helper::checkProfileBlocked();

        // Проверяем, заполнен ли профиль
        Helper::checkProfileComplete();

        $userId = Helper::getUserId();
        $eventId = $_GET['id'] ?? 0;

        if ($eventId) {
            $this->eventModel->delete($eventId, $userId);
            $_SESSION['success_message'] = 'Мероприятие успешно удалено.';
        }

        Helper::redirect('events');
    }

    /**
     * Геокодирует адрес в координаты через Nominatim (OpenStreetMap)
     */
    private function geocodeAddress($houseNumber, $street, $city, $country = '')
    {
        if (empty($city) || empty($street) || empty($houseNumber)) {
            return null;
        }

        $queries = [];
        if ($country) {
            $queries[] = "{$houseNumber} {$street}, {$city}, {$country}";
            $queries[] = "{$street} {$houseNumber}, {$city}, {$country}";
        }
        $queries[] = "{$houseNumber} {$street}, {$city}";
        $queries[] = "{$street} {$houseNumber}, {$city}";

        foreach ($queries as $query) {
            try {
                $url = 'https://nominatim.openstreetmap.org/search?format=json&q=' . urlencode($query) . '&limit=1&addressdetails=1';
                $context = stream_context_create([
                    'http' => [
                        'method' => 'GET',
                        'header' => "User-Agent: Tanisu App\r\n",
                        'timeout' => 5
                    ]
                ]);
                $response = @file_get_contents($url, false, $context);
                if ($response) {
                    $data = json_decode($response, true);
                    if (!empty($data[0]['lat']) && !empty($data[0]['lon'])) {
                        return [
                            'lat' => (float)$data[0]['lat'],
                            'lon' => (float)$data[0]['lon']
                        ];
                    }
                }
            } catch (Exception $e) {
                continue;
            }
        }
        return null;
    }

    /**
     * Определяет страну по координатам через reverse geocoding
     */
    private function getCountryByCoordinates($latitude, $longitude)
    {
        if (!$latitude || !$longitude) {
            return null;
        }

        try {
            $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$latitude}&lon={$longitude}&addressdetails=1";
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => [
                        'User-Agent: Tanisu App'
                    ],
                    'timeout' => 5
                ]
            ]);

            $response = @file_get_contents($url, false, $context);
            
            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['address']['country'])) {
                    return $data['address']['country'];
                }
            }
        } catch (Exception $e) {
            // Игнорируем ошибки, возвращаем null
        }

        return null;
    }

    /**
     * Сохраняет обрезанное фото мероприятия из data URL в uploads/photos/ (как баннер после редактора).
     *
     * @return array{success:bool, filename?:string, error?:string}
     */
    private function saveEventPhotoFromDataUrl(string $dataUrl): array
    {
        if (!preg_match('/^data:image\/(jpeg|jpg|png|gif|webp);base64,(.+)$/s', $dataUrl, $m)) {
            return ['success' => false, 'error' => 'Недопустимый формат изображения'];
        }

        $ext = strtolower($m[1]);
        if ($ext === 'jpeg') {
            $ext = 'jpg';
        }

        $raw = base64_decode($m[2], true);
        if ($raw === false || $raw === '') {
            return ['success' => false, 'error' => 'Не удалось декодировать изображение'];
        }

        $maxBytes = 10 * 1024 * 1024;
        if (strlen($raw) > $maxBytes) {
            return ['success' => false, 'error' => 'Размер файла не должен превышать 10MB'];
        }

        $projectRoot = dirname(__DIR__, 2);
        $dir = $projectRoot . '/' . rtrim(str_replace('\\', '/', UPLOAD_DIR), '/') . '/photos/';
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0775, true)) {
                return ['success' => false, 'error' => 'Не удалось создать папку для загрузок'];
            }
        }

        $filename = uniqid('event_', true) . '.' . $ext;
        $targetPath = $dir . $filename;

        if (file_put_contents($targetPath, $raw) === false) {
            return ['success' => false, 'error' => 'Не удалось сохранить файл'];
        }

        $imageInfo = @getimagesize($targetPath);
        if ($imageInfo === false) {
            @unlink($targetPath);
            return ['success' => false, 'error' => 'Файл не является изображением или повреждён'];
        }

        $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($imageInfo['mime'], $allowedMime, true)) {
            @unlink($targetPath);
            return ['success' => false, 'error' => 'Недопустимый тип изображения'];
        }

        return ['success' => true, 'filename' => $filename];
    }
}

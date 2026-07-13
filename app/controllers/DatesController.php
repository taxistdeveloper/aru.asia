<?php

/**
 * КОНТРОЛЛЕР СВИДАНИЙ
 *
 * Обрабатывает создание и просмотр объявлений о свиданиях
 */

class DatesController
{
    private $dateModel;
    private $userModel;
    private $categoryModel;
    private $messageModel;

    public function __construct()
    {
        $this->dateModel = new Date();
        $this->userModel = new User();
        $this->categoryModel = new Category();
        $this->messageModel = new Message();
    }

    /**
     * Показывает список свиданий
     */
    public function index()
    {
        // Проверяем, не заблокирован ли профиль пользователя (если авторизован)
        if (Helper::isLoggedIn()) {
            Helper::checkProfileBlocked();
        }

        // Автоматически удаляем просроченные свидания
        $this->dateModel->deleteExpired();

        $dates = [];
        $myDates = [];
        $myDateChats = [];
        $userLat = null;
        $userLon = null;
        $userGender = null;
        $currentUserId = null;

        $userDate = null;

        // Если пользователь авторизован
        if (Helper::isLoggedIn()) {
            $currentUserId = Helper::getUserId();
            $user = $this->userModel->findById($currentUserId);
            $userLat = $user['latitude'] ?? null;
            $userLon = $user['longitude'] ?? null;
            $userGender = $user['gender'] ?? null;

            // Получаем активное свидание пользователя
            $userDate = $this->dateModel->getByUserId($currentUserId);

            // Получаем мои свидания
            $myDates = $this->dateModel->getAllByUserId($currentUserId);
            // Убеждаемся, что это массив
            $myDates = is_array($myDates) ? $myDates : [];

            // Получаем свидания, в которых пользователь участвует в чате
            $dateIdsWithChat = $this->messageModel->getDateIdsWithChat($currentUserId);
            $myDateChats = [];
            if (!empty($dateIdsWithChat)) {
                // Включаем все свидания с перепиской (и свои, и чужие)
                // Если свидание есть в списке, значит там есть переписка
                $myDateChats = $this->dateModel->getByIds($dateIdsWithChat);

                // Добавляем информацию о непрочитанных сообщениях
                foreach ($myDateChats as &$date) {
                    $date['unread_count'] = $this->messageModel->getUnreadCountForDate($date['id'], $currentUserId);
                }
            }

            // Получаем свидания противоположного пола
            if ($userLat && $userLon && $userGender) {
                // Если есть геолокация - ищем в радиусе
                $dates = $this->dateModel->getInRadius($userLat, $userLon, $userGender, RADIUS_KM);
                // Убеждаемся, что это массив
                $dates = is_array($dates) ? $dates : [];

                // Если не найдено в радиусе, показываем все активные противоположного пола
                if (empty($dates) && $userGender) {
                    $dates = $this->dateModel->getAllActiveByGender($userGender);
                    $dates = is_array($dates) ? $dates : [];
                }

                // Если все еще пусто, показываем ВСЕ активные свидания (для диагностики)
                if (empty($dates)) {
                    $dates = $this->dateModel->getAllActive();
                    $dates = is_array($dates) ? $dates : [];
                }
            } elseif ($userGender) {
                // Если нет геолокации, но есть пол - показываем все активные свидания противоположного пола
                $dates = $this->dateModel->getAllActiveByGender($userGender);
                // Убеждаемся, что это массив
                $dates = is_array($dates) ? $dates : [];

                // Если пусто, показываем все активные
                if (empty($dates)) {
                    $dates = $this->dateModel->getAllActive();
                    $dates = is_array($dates) ? $dates : [];
                }
            } else {
                // Если нет пола - показываем все активные свидания
                $dates = $this->dateModel->getAllActive();
                // Убеждаемся, что это массив
                $dates = is_array($dates) ? $dates : [];
            }

            // Добавляем информацию о непрочитанных сообщениях для моих свиданий
            if (!empty($myDates) && is_array($myDates)) {
                foreach ($myDates as &$date) {
                    $date['unread_count'] = $this->messageModel->getUnreadCountForDate($date['id'], $currentUserId);
                }
            }

            // Добавляем информацию о непрочитанных сообщениях для других свиданий
            if (!empty($dates) && is_array($dates)) {
                foreach ($dates as &$date) {
                    $date['unread_count'] = $this->messageModel->getUnreadCountForDate($date['id'], $currentUserId);
                }
            }
        } else {
            // Для неавторизованных пользователей показываем все активные свидания
            $dates = $this->dateModel->getAllActive();
            // Убеждаемся, что это массив
            $dates = is_array($dates) ? $dates : [];
            // Добавляем расстояние как 0 или null, так как геолокации нет
            if (!empty($dates) && is_array($dates)) {
                foreach ($dates as &$date) {
                    $date['distance'] = 0;
                }
            }
            $myDateChats = [];
        }

        View::render('dates/index', [
            'dates' => $dates,
            'myDates' => $myDates,
            'myDateChats' => $myDateChats ?? [],
            'currentUserId' => $currentUserId,
            'userLat' => $userLat,
            'userLon' => $userLon,
            'userDate' => $userDate,
            'user' => $user ?? null,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Показывает форму создания свидания
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
        $user = $this->userModel->findById($userId);

        // Проверяем семейный статус - женатые/замужние не могут создавать свидания
        if (($user['marital_status'] ?? '') === 'married') {
            Helper::redirect('profile');
            return;
        }

        $userDate = $this->dateModel->getByUserId($userId);

        // Проверяем что у пользователя еще нет активного свидания
        if ($userDate) {
            // Перенаправляем обратно на страницу свиданий, если уже есть активное свидание
            Helper::redirect('dates');
            return;
        }

        // Получаем активные категории
        $categories = $this->categoryModel->getAllActive();

        // Если нет категорий, показываем предупреждение
        $error = null;
        if (empty($categories)) {
            $error = 'Категории еще не добавлены. Обратитесь к менеджеру.';
        }

        View::render('dates/create', [
            'error' => $error,
            'categories' => $categories ?? [],
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Сохраняет новое свидание
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
        $user = $this->userModel->findById($userId);

        // Проверяем семейный статус - женатые/замужние не могут создавать свидания
        if (($user['marital_status'] ?? '') === 'married') {
            Helper::redirect('profile');
            return;
        }

        // Проверяем что у пользователя еще нет активного свидания
        if ($this->dateModel->getByUserId($userId)) {
            // Перенаправляем на страницу свиданий, если уже есть активное свидание
            Helper::redirect('dates');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dateTime = trim($_POST['date_time'] ?? '');
            $dateError = Helper::validatePlanningDateTime($dateTime);
            if ($dateError !== null) {
                $categories = $this->categoryModel->getAllActive();
                View::render('dates/create', [
                    'error' => $dateError,
                    'categories' => $categories ?? [],
                    'isMobile' => View::isMobile()
                ]);
                return;
            }

            $data = [
                'user_id' => $userId,
                'title' => $_POST['title'] ?? '',
                'category_id' => $_POST['category_id'] ?? null,
                'date_time' => $dateTime,
                'location' => $_POST['location'] ?? '',
                'latitude' => $_POST['latitude'] ?? null,
                'longitude' => $_POST['longitude'] ?? null
            ];

            if ($this->dateModel->create($data)) {
                // Получаем созданное свидание для отправки уведомлений
                $createdDate = $this->dateModel->getByUserId($userId);

                // Отправляем push-уведомления пользователям противоположного пола в радиусе
                if ($createdDate && $createdDate['latitude'] && $createdDate['longitude']) {
                    $pushService = new PushNotificationService();
                    $userGender = $user['gender'] ?? null;
                    if ($userGender) {
                        $pushService->sendNewDateNotification(
                            $createdDate['id'],
                            $createdDate['title'],
                            $createdDate['location'],
                            $createdDate['latitude'],
                            $createdDate['longitude'],
                            $userGender,
                            RADIUS_KM
                        );
                    }
                }

                Helper::redirect('dates');
            }
        }

        Helper::redirect('dates/create');
    }

    /**
     * Форма редактирования свидания
     */
    public function edit()
    {
        if (!Helper::isLoggedIn()) {
            Helper::redirect('auth/login');
            return;
        }

        $userId = Helper::getUserId();
        $dateId = $_GET['id'] ?? 0;

        if (!$dateId) {
            Helper::redirect('profile');
            return;
        }

        $date = $this->dateModel->getById($dateId);

        // Проверяем, что свидание принадлежит текущему пользователю
        if (!$date || (int)$date['user_id'] !== (int)$userId) {
            Helper::redirect('profile');
            return;
        }

        // Получаем активные категории
        $categories = $this->categoryModel->getAllActive();

        $error = $_SESSION['error_message'] ?? null;
        if (isset($_SESSION['error_message'])) {
            unset($_SESSION['error_message']);
        }

        View::render('dates/edit', [
            'date' => $date,
            'categories' => $categories,
            'isMobile' => View::isMobile(),
            'error' => $error
        ]);
    }

    /**
     * Обновляет существующее свидание
     */
    public function update()
    {
        if (!Helper::isLoggedIn()) {
            Helper::redirect('auth/login');
            return;
        }

        $userId = Helper::getUserId();

        // Проверяем, не заблокирован ли профиль пользователя
        Helper::checkProfileBlocked();

        // Проверяем, заполнен ли профиль
        Helper::checkProfileComplete();

        $dateId = $_GET['id'] ?? 0;

        if (!$dateId) {
            Helper::redirect('profile');
            return;
        }

        // Проверяем, что свидание принадлежит текущему пользователю
        $date = $this->dateModel->getById($dateId);
        if (!$date || (int)$date['user_id'] !== (int)$userId) {
            Helper::redirect('profile');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dateTime = trim($_POST['date_time'] ?? '');
            $dateError = Helper::validatePlanningDateTime($dateTime);
            if ($dateError !== null) {
                $_SESSION['error_message'] = $dateError;
                Helper::redirect('dates/edit?id=' . (int)$dateId);
                return;
            }

            $data = [
                'title' => $_POST['title'] ?? '',
                'category_id' => $_POST['category_id'] ?? null,
                'date_time' => $dateTime,
                'location' => $_POST['location'] ?? '',
                'latitude' => $_POST['latitude'] ?? null,
                'longitude' => $_POST['longitude'] ?? null
            ];

            if ($this->dateModel->update($dateId, $userId, $data)) {
                Helper::redirect('profile');
                return;
            }
        }

        Helper::redirect('dates/edit?id=' . (int)$dateId);
    }

    /**
     * Удаляет свидание
     */
    public function delete()
    {
        if (!Helper::isLoggedIn()) {
            Helper::redirect('auth/login');
            return;
        }

        $userId = Helper::getUserId();
        $dateId = $_GET['id'] ?? 0;

        if ($dateId) {
            $this->dateModel->delete($dateId, $userId);
        }

        Helper::redirect('profile');
    }

    /**
     * AJAX: Удаляет все просроченные свидания и мероприятия
     * Вызывается когда таймер истекает на клиенте
     */
    public function deleteExpired()
    {
        header('Content-Type: application/json');

        try {
            // Удаляем просроченные свидания
            $deletedDates = $this->dateModel->deleteExpired();
            
            // Также удаляем просроченные мероприятия
            $eventModel = new Event();
            $eventModel->deleteExpired();
            
            echo json_encode([
                'success' => true,
                'deleted_dates' => $deletedDates
            ]);
        } catch (Exception $e) {
            error_log("Error in deleteExpired AJAX: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Ошибка при удалении просроченных записей'
            ]);
        }
    }
}

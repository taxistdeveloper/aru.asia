<?php

/**
 * КОНТРОЛЛЕР ПАНЕЛИ МЕНЕДЖЕРА
 *
 * Управление функциями для менеджеров
 * Менеджер имеет ограниченные права по сравнению с администратором
 */

class ManagerController
{
    private $userModel;
    private $eventModel;
    private $dateModel;
    private $categoryModel;
    private $adModel;
    private $feedbackModel;
    private $messageModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->eventModel = new Event();
        $this->dateModel = new Date();
        $this->categoryModel = new Category();
        $this->adModel = new Ad();
        $this->feedbackModel = new Feedback();
        $this->messageModel = new Message();
    }

    /**
     * Главная страница панели менеджера (Dashboard)
     */
    public function index()
    {
        // Проверяем права менеджера
        Helper::requireManager();

        // Проверяем, не заблокирован ли профиль пользователя (для менеджеров)
        if (Helper::isLoggedIn() && !Helper::isAdminLoggedIn()) {
            Helper::checkProfileBlocked();
        }

        $db = Database::getInstance()->getConnection();
        $adminStats = new AdminStats();
        $stats = $adminStats->getSummary();

        // Последние пользователи
        $recent_users = $db->query("SELECT id, email, gender, age, city, email_verified, role, created_at
                                   FROM users
                                   ORDER BY created_at DESC
                                   LIMIT 10")->fetchAll();

        // Последние мероприятия
        $recent_events = $db->query("SELECT e.*, u.email as user_email
                                    FROM events e
                                    LEFT JOIN users u ON e.user_id = u.id
                                    ORDER BY e.created_at DESC
                                    LIMIT 10")->fetchAll();

        View::render('manager/index', [
            'stats' => $stats,
            'recent_users' => $recent_users,
            'recent_events' => $recent_events,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Страница статистики (для менеджера)
     */
    public function stats()
    {
        Helper::requireManager();

        // Проверяем, не заблокирован ли профиль пользователя (для менеджеров)
        if (Helper::isLoggedIn() && !Helper::isAdminLoggedIn()) {
            Helper::checkProfileBlocked();
        }

        try {
            $adminStats = new AdminStats();
            $stats = $adminStats->getSummary();
            $daily_visits = $adminStats->getDailyVisits(30);

            View::render('manager/stats', [
                'stats' => $stats,
                'daily_visits' => $daily_visits,
                'isMobile' => View::isMobile()
            ]);
        } catch (Exception $e) {
            error_log("ManagerController::stats - Fatal error: " . $e->getMessage());
            die("Ошибка при загрузке статистики. Проверьте логи сервера.");
        }
    }

    /**
     * Управление пользователями (полный функционал для менеджера)
     */
    public function users()
    {
        Helper::requireManager();

        // Проверяем, не заблокирован ли профиль пользователя (для менеджеров)
        if (Helper::isLoggedIn() && !Helper::isAdminLoggedIn()) {
            Helper::checkProfileBlocked();
        }

        $db = Database::getInstance()->getConnection();

        // Получаем параметр фильтра
        $filter = $_GET['filter'] ?? 'all';

        // Поиск по IP — все, кто регистрировался с этого IP (для выявления мультиаккаунтов)
        $searchIp = isset($_GET['search_ip']) ? trim($_GET['search_ip']) : '';

        // Формируем SQL в зависимости от фильтра и поиска по IP
        $whereClause = "";
        $params = [];
        if ($filter === 'new') {
            $whereClause = "WHERE u.created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)";
        }
        if ($searchIp !== '') {
            $whereClause .= ($whereClause ? " AND " : "WHERE ") . "u.registration_ip = :search_ip";
            $params['search_ip'] = $searchIp;
        }

        // Получаем общее количество пользователей
        $countQuery = "SELECT COUNT(*) FROM users u $whereClause";
        if (!empty($params)) {
            $stmt = $db->prepare($countQuery);
            $stmt->execute($params);
            $totalUsers = (int)$stmt->fetchColumn();
        } else {
            $totalUsers = (int)$db->query($countQuery)->fetchColumn();
        }

        // Количество записей на странице
        $perPage = 11;
        $totalPages = $totalUsers > 0 ? ceil($totalUsers / $perPage) : 1;

        // Получаем номер страницы
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

        $offset = ($page - 1) * $perPage;

        // Получаем пользователей с первой фотографией, IP и страной
        $usersQuery = "
            SELECT u.*,
                   (SELECT photo FROM user_photos WHERE user_id = u.id AND photo IS NOT NULL AND TRIM(photo) <> '' ORDER BY created_at ASC LIMIT 1) as first_photo
            FROM users u
            $whereClause
            ORDER BY u.created_at DESC
            LIMIT $perPage OFFSET $offset
        ";
        if (!empty($params)) {
            $stmt = $db->prepare($usersQuery);
            $stmt->execute($params);
            $users = $stmt->fetchAll();
        } else {
            $users = $db->query($usersQuery)->fetchAll();
        }

        View::render('manager/users', [
            'users' => $users,
            'filter' => $filter,
            'searchIp' => $searchIp,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalUsers' => $totalUsers,
            'perPage' => $perPage,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Обновляет роль пользователя
     */
    public function updateUserRole()
    {
        Helper::requireManager();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect('manager/users');
            return;
        }

        $userId = $_POST['user_id'] ?? null;
        $role = $_POST['role'] ?? null;

        if ($userId && $role) {
            if ($this->userModel->updateRole($userId, $role)) {
                $_SESSION['success_message'] = 'Роль пользователя успешно обновлена';
            } else {
                $_SESSION['error_message'] = 'Ошибка при обновлении роли';
            }
        } else {
            $_SESSION['error_message'] = 'Неверные данные';
        }

        Helper::redirect('manager/users');
    }

    /**
     * Удаляет пользователя полностью
     */
    public function deleteUser()
    {
        Helper::requireManager();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect('manager/users');
            return;
        }

        $userId = $_POST['user_id'] ?? null;

        if ($userId) {
            // Проверяем, что пользователь существует
            $user = $this->userModel->findById($userId);
            if ($user) {
                if ($this->userModel->delete($userId)) {
                    $_SESSION['success_message'] = 'Пользователь успешно удален';
                } else {
                    $_SESSION['error_message'] = 'Ошибка при удалении пользователя';
                }
            } else {
                $_SESSION['error_message'] = 'Пользователь не найден';
            }
        } else {
            $_SESSION['error_message'] = 'Неверные данные';
        }

        Helper::redirect('manager/users');
    }

    /**
     * Страница добавления замечания для пользователя
     */
    public function addRemark()
    {
        Helper::requireManager();

        $userId = $_GET['user_id'] ?? null;

        if (!$userId) {
            $_SESSION['error_message'] = 'Неверные данные';
            Helper::redirect('manager/users');
            return;
        }

        // Проверяем, что пользователь существует
        $user = $this->userModel->findById($userId);
        if (!$user) {
            $_SESSION['error_message'] = 'Пользователь не найден';
            Helper::redirect('manager/users');
            return;
        }

        $title = 'Добавить замечание - Панель менеджера';
        View::render('manager/add_remark', [
            'user' => $user,
            'title' => $title
        ]);
    }

    /**
     * Страница просмотра замечания для пользователя
     */
    public function viewRemark()
    {
        Helper::requireManager();

        $userId = $_GET['user_id'] ?? null;

        if (!$userId) {
            $_SESSION['error_message'] = 'Неверные данные';
            Helper::redirect('manager/users');
            return;
        }

        // Проверяем, что пользователь существует
        $user = $this->userModel->findById($userId);
        if (!$user) {
            $_SESSION['error_message'] = 'Пользователь не найден';
            Helper::redirect('manager/users');
            return;
        }

        // Проверяем, что у пользователя есть замечание
        if (empty($user['admin_remark'])) {
            $_SESSION['error_message'] = 'У пользователя нет замечания';
            Helper::redirect('manager/users');
            return;
        }

        $title = 'Просмотр замечания - Панель менеджера';
        View::render('manager/view_remark', [
            'user' => $user,
            'title' => $title
        ]);
    }

    /**
     * Устанавливает замечание для профиля пользователя и блокирует его
     */
    public function setUserRemark()
    {
        Helper::requireManager();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect('manager/users');
            return;
        }

        $userId = $_POST['user_id'] ?? null;
        $remark = trim($_POST['remark'] ?? '');
        $remarkType = $_POST['remark_type'] ?? null;

        if (!$userId) {
            $_SESSION['error_message'] = 'Неверные данные';
            Helper::redirect('manager/users');
            return;
        }

        if (empty($remark)) {
            $_SESSION['error_message'] = 'Введите замечание';
            Helper::redirect('manager/users');
            return;
        }

        if (empty($remarkType)) {
            $_SESSION['error_message'] = 'Выберите тип замечания';
            Helper::redirect('manager/users');
            return;
        }

        // Проверяем, что пользователь существует
        $user = $this->userModel->findById($userId);
        if (!$user) {
            $_SESSION['error_message'] = 'Пользователь не найден';
            Helper::redirect('manager/users');
            return;
        }

        // Определяем название поля для сообщения
        $fieldNames = [
            'full_name' => 'ФИО (Полное имя)',
            'about' => 'О себе',
            'photo' => 'Фотография'
        ];
        $fieldName = $fieldNames[$remarkType] ?? 'профиль';

        // Устанавливаем замечание и блокируем профиль
        if ($this->userModel->setAdminRemark($userId, $remark, $remarkType)) {
            $_SESSION['success_message'] = 'Замечание добавлено, профиль заблокирован';

            // Отправляем уведомление пользователю
            $pushService = new PushNotificationService();
            $message = "Ваш профиль заблокирован. Менеджер оставил замечание по полю \"{$fieldName}\":\n\n{$remark}\n\nПожалуйста, исправьте указанные ошибки в профиле.";
            $pushService->sendAdminNotification($userId, 'Профиль заблокирован', $message);
        } else {
            $_SESSION['error_message'] = 'Ошибка при установке замечания';
        }

        Helper::redirect('manager/users');
    }

    /**
     * Снимает блокировку с профиля пользователя (разблокирует профиль)
     */
    public function unblockUser()
    {
        Helper::requireManager();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect('manager/users');
            return;
        }

        $userId = $_POST['user_id'] ?? null;

        if (!$userId) {
            $_SESSION['error_message'] = 'Неверные данные';
            Helper::redirect('manager/users');
            return;
        }

        // Проверяем, что пользователь существует
        $user = $this->userModel->findById($userId);
        if (!$user) {
            $_SESSION['error_message'] = 'Пользователь не найден';
            Helper::redirect('manager/users');
            return;
        }

        // Снимаем блокировку
        if ($this->userModel->clearAdminRemark($userId)) {
            $_SESSION['success_message'] = 'Профиль разблокирован';
        } else {
            $_SESSION['error_message'] = 'Ошибка при разблокировке профиля';
        }

        Helper::redirect('manager/users');
    }

    /**
     * Страница отправки сообщений пользователям
     */
    public function sendMessage()
    {
        Helper::requireManager();

        $users = [];
        $search = $_GET['search'] ?? '';
        $selectedUserId = $_GET['user_id'] ?? null;

        // Если указан конкретный пользователь, выбираем его
        if ($selectedUserId) {
            $selectedUser = $this->userModel->findById($selectedUserId);
            if ($selectedUser) {
                $users = [$selectedUser];
            }
        } elseif (!empty($search)) {
            // Получаем список пользователей для выбора
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT id, email, full_name, gender, age, city
                    FROM users
                    WHERE email LIKE :search
                    OR full_name LIKE :search2
                    ORDER BY created_at DESC
                    LIMIT 50";
            $stmt = $db->prepare($sql);
            $searchParam = '%' . $search . '%';
            $stmt->execute([
                ':search' => $searchParam,
                ':search2' => $searchParam
            ]);
            $users = $stmt->fetchAll();
        } else {
            // Показываем последних 20 пользователей
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT id, email, full_name, gender, age, city
                    FROM users
                    ORDER BY created_at DESC
                    LIMIT 20";
            $users = $db->query($sql)->fetchAll();
        }

        View::render('admin/send_message', [
            'users' => $users,
            'search' => $search,
            'selectedUserId' => $selectedUserId,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Обрабатывает отправку сообщения пользователю
     */
    public function submitMessage()
    {
        Helper::requireManager();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect('manager/send-message');
            return;
        }

        $managerId = Helper::getUserId();
        $recipientType = $_POST['recipient_type'] ?? 'single';
        $toUserId = $_POST['to_user_id'] ?? null;
        $message = trim($_POST['message'] ?? '');

        if (!$managerId || empty($message)) {
            $_SESSION['error_message'] = 'Заполните все поля';
            Helper::redirect('manager/send-message');
            return;
        }

        $messageModel = new Message();
        $pushService = new PushNotificationService();
        $successCount = 0;
        $errorCount = 0;

        if ($recipientType === 'all') {
            // Отправляем всем пользователям
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT id FROM users WHERE email_verified = 1";
            $users = $db->query($sql)->fetchAll();

            foreach ($users as $user) {
                // Отправляем сообщение
                if ($messageModel->send($managerId, $user['id'], $message)) {
                    // Отправляем push-уведомление
                    $pushService->sendAdminNotification($user['id'], 'Сообщение от менеджера', $message);
                    $successCount++;
                } else {
                    $errorCount++;
                }
            }

            if ($successCount > 0) {
                $_SESSION['success_message'] = "Сообщение успешно отправлено {$successCount} пользователям" .
                    ($errorCount > 0 ? " ({$errorCount} ошибок)" : "");
            } else {
                $_SESSION['error_message'] = 'Не удалось отправить сообщения';
            }
        } else {
            // Отправляем одному пользователю
            if (!$toUserId) {
                $_SESSION['error_message'] = 'Выберите получателя';
                Helper::redirect('manager/send-message');
                return;
            }

            // Проверяем существование пользователя
            $user = $this->userModel->findById($toUserId);
            if (!$user) {
                $_SESSION['error_message'] = 'Пользователь не найден';
                Helper::redirect('manager/send-message');
                return;
            }

            // Отправляем сообщение
            if ($messageModel->send($managerId, $toUserId, $message)) {
                // Отправляем push-уведомление
                $pushService->sendAdminNotification($toUserId, 'Сообщение от менеджера', $message);

                $_SESSION['success_message'] = 'Сообщение успешно отправлено пользователю';
            } else {
                $_SESSION['error_message'] = 'Ошибка при отправке сообщения';
            }
        }

        Helper::redirect('manager/send-message');
    }

    /**
     * Управление категориями свиданий
     */
    public function categories()
    {
        Helper::requireManager();

        $categories = $this->categoryModel->getAll();

        View::render('manager/categories', [
            'categories' => $categories,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Создание новой категории
     */
    public function categoryStore()
    {
        Helper::requireManager();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? null,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            if (!empty($data['name'])) {
                $this->categoryModel->create($data);
            }
        }

        Helper::redirect('manager/categories');
    }

    /**
     * Обновление категории
     */
    public function categoryUpdate()
    {
        Helper::requireManager();

        $id = $_GET['id'] ?? 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
            $data = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? null,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            if (!empty($data['name'])) {
                $this->categoryModel->update($id, $data);
            }
        }

        Helper::redirect('manager/categories');
    }

    /**
     * Удаление категории
     */
    public function categoryDelete()
    {
        Helper::requireManager();

        $id = $_GET['id'] ?? 0;

        if ($id) {
            $this->categoryModel->delete($id);
        }

        Helper::redirect('manager/categories');
    }

    /**
     * Управление мероприятиями (модерация)
     */
    public function events()
    {
        Helper::requireManager();

        $pendingEvents = $this->eventModel->getPending();

        View::render('manager/events', [
            'pendingEvents' => $pendingEvents,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Просмотр всех мероприятий
     */
    public function allEvents()
    {
        Helper::requireManager();

        // Проверяем, не заблокирован ли профиль пользователя (для менеджеров)
        if (Helper::isLoggedIn() && !Helper::isAdminLoggedIn()) {
            Helper::checkProfileBlocked();
        }

        $db = Database::getInstance()->getConnection();

        // Получаем общее количество мероприятий
        $totalEvents = (int)$db->query("SELECT COUNT(*) FROM events")->fetchColumn();

        // Количество записей на странице
        $perPage = 4;
        $totalPages = $totalEvents > 0 ? ceil($totalEvents / $perPage) : 1;

        // Получаем номер страницы
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

        $offset = ($page - 1) * $perPage;

        $events = $db->query("
            SELECT e.*, u.email as user_email, u.full_name
            FROM events e
            LEFT JOIN users u ON e.user_id = u.id
            ORDER BY e.created_at DESC
            LIMIT $perPage OFFSET $offset
        ")->fetchAll();

        View::render('manager/all_events', [
            'events' => $events,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalEvents' => $totalEvents,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Просмотр всех свиданий
     */
    public function allDates()
    {
        Helper::requireManager();

        // Проверяем, не заблокирован ли профиль пользователя (для менеджеров)
        if (Helper::isLoggedIn() && !Helper::isAdminLoggedIn()) {
            Helper::checkProfileBlocked();
        }

        $db = Database::getInstance()->getConnection();

        // Получаем общее количество свиданий
        $totalDates = (int)$db->query("SELECT COUNT(*) FROM dates")->fetchColumn();

        // Количество записей на странице
        $perPage = 4;
        $totalPages = $totalDates > 0 ? ceil($totalDates / $perPage) : 1;

        // Получаем номер страницы
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

        $offset = ($page - 1) * $perPage;

        $dates = $db->query("
            SELECT d.id, d.user_id, d.title, d.category_id, d.date_time, d.location,
                   d.latitude, d.longitude, d.created_at,
                   d.description,
                   u.email as user_email, u.full_name,
                   dc.name as category_name,
                   (SELECT photo FROM user_photos WHERE user_id = u.id AND photo IS NOT NULL AND TRIM(photo) <> '' ORDER BY created_at ASC LIMIT 1) as user_photo
            FROM dates d
            LEFT JOIN users u ON d.user_id = u.id
            LEFT JOIN date_categories dc ON d.category_id = dc.id
            ORDER BY d.created_at DESC
            LIMIT $perPage OFFSET $offset
        ")->fetchAll();

        View::render('manager/all_dates', [
            'dates' => $dates,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalDates' => $totalDates,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Увеличивает дедлайн мероприятия
     */
    public function extendEventDeadline()
    {
        Helper::requireManager();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect('manager/events/all');
            return;
        }

        $eventId = $_POST['event_id'] ?? null;
        $days = (int)($_POST['days'] ?? 0);

        if (!$eventId || $days <= 0) {
            $_SESSION['error_message'] = 'Неверные данные';
            Helper::redirect('manager/events/all');
            return;
        }

        // Получаем текущую дату мероприятия
        $event = $this->eventModel->getById($eventId);
        if (!$event) {
            $_SESSION['error_message'] = 'Мероприятие не найдено';
            Helper::redirect('manager/events/all');
            return;
        }

        // Вычисляем новую дату
        $currentDate = new DateTime($event['event_date']);
        $currentDate->modify("+{$days} days");
        $newDate = $currentDate->format('Y-m-d H:i:s');

        // Обновляем дату
        if ($this->eventModel->updateDate($eventId, $newDate)) {
            $_SESSION['success_message'] = "Дедлайн мероприятия успешно увеличен на {$days} " . ($days == 1 ? 'день' : ($days < 5 ? 'дня' : 'дней'));
        } else {
            $_SESSION['error_message'] = 'Ошибка при обновлении дедлайна';
        }

        Helper::redirect('manager/events/all');
    }

    /**
     * Увеличивает дедлайн свидания
     */
    public function extendDateDeadline()
    {
        Helper::requireManager();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect('manager/dates/all');
            return;
        }

        $dateId = $_POST['date_id'] ?? null;
        $days = (int)($_POST['days'] ?? 0);

        if (!$dateId || $days <= 0) {
            $_SESSION['error_message'] = 'Неверные данные';
            Helper::redirect('manager/dates/all');
            return;
        }

        // Получаем текущую дату свидания
        $date = $this->dateModel->getById($dateId);
        if (!$date) {
            $_SESSION['error_message'] = 'Свидание не найдено';
            Helper::redirect('manager/dates/all');
            return;
        }

        // Вычисляем новую дату
        $currentDate = new DateTime($date['date_time']);
        $currentDate->modify("+{$days} days");
        $newDate = $currentDate->format('Y-m-d H:i:s');

        // Обновляем дату
        if ($this->dateModel->updateDate($dateId, $newDate)) {
            $_SESSION['success_message'] = "Дедлайн свидания успешно увеличен на {$days} " . ($days == 1 ? 'день' : ($days < 5 ? 'дня' : 'дней'));
        } else {
            $_SESSION['error_message'] = 'Ошибка при обновлении дедлайна';
        }

        Helper::redirect('manager/dates/all');
    }

    /**
     * Удаляет свидание (для менеджера)
     */
    public function deleteDate()
    {
        Helper::requireManager();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect('manager/dates/all');
            return;
        }

        $dateId = $_POST['date_id'] ?? null;

        if (!$dateId) {
            $_SESSION['error_message'] = 'Неверные данные';
            Helper::redirect('manager/dates/all');
            return;
        }

        // Получаем информацию о свидании перед удалением (для уведомления)
        $date = $this->dateModel->getById($dateId);
        if (!$date) {
            $_SESSION['error_message'] = 'Свидание не найдено';
            Helper::redirect('manager/dates/all');
            return;
        }

        // Удаляем свидание
        if ($this->dateModel->deleteById($dateId)) {
            $_SESSION['success_message'] = 'Свидание успешно удалено';

            // Отправляем уведомление пользователю
            $pushService = new PushNotificationService();
            $message = "Ваше свидание \"{$date['title']}\" было удалено менеджером.";
            $pushService->sendAdminNotification($date['user_id'], 'Свидание удалено', $message);
        } else {
            $_SESSION['error_message'] = 'Ошибка при удалении свидания';
        }

        Helper::redirect('manager/dates/all');
    }

    /**
     * Устанавливает замечание для свидания
     */
    public function setDateRemark()
    {
        Helper::requireManager();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect('manager/dates/all');
            return;
        }

        $dateId = $_POST['date_id'] ?? null;
        $remark = trim($_POST['remark'] ?? '');

        if (!$dateId) {
            $_SESSION['error_message'] = 'Неверные данные';
            Helper::redirect('manager/dates/all');
            return;
        }

        if (empty($remark)) {
            $_SESSION['error_message'] = 'Введите замечание';
            Helper::redirect('manager/dates/all');
            return;
        }

        // Получаем информацию о свидании
        $date = $this->dateModel->getById($dateId);
        if (!$date) {
            $_SESSION['error_message'] = 'Свидание не найдено';
            Helper::redirect('manager/dates/all');
            return;
        }

        // Отправляем сообщение пользователю с замечанием
        $messageModel = new Message();
        $managerId = Helper::getUserId();
        $message = "Менеджер оставил замечание к вашему свиданию \"{$date['title']}\":\n\n{$remark}\n\nПожалуйста, исправьте указанные ошибки или создайте новое свидание.";

        if ($messageModel->send($managerId, $date['user_id'], $message, null, $dateId)) {
            // Отправляем push-уведомление
            $pushService = new PushNotificationService();
            $pushService->sendAdminNotification($date['user_id'], 'Замечание к свиданию', $message, $dateId);

            $_SESSION['success_message'] = 'Замечание успешно отправлено пользователю';
        } else {
            $_SESSION['error_message'] = 'Ошибка при отправке замечания';
        }

        Helper::redirect('manager/dates/all');
    }

    /**
     * Одобряет мероприятие
     */
    public function approveEvent()
    {
        Helper::requireManager();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $eventId = $_POST['event_id'] ?? 0;
            $userId = Helper::getUserId();

            if ($eventId && $userId) {
                $event = $this->eventModel->getById($eventId);
                if ($event && $this->eventModel->approve($eventId, $userId)) {
                    // Отправляем уведомление пользователю
                    $this->sendEventNotification($event['user_id'], $eventId, 'approved');
                    $_SESSION['success_message'] = 'Мероприятие одобрено';
                } else {
                    $_SESSION['error_message'] = 'Ошибка при одобрении мероприятия';
                }
            }
        }

        Helper::redirect('manager/events');
    }

    /**
     * Отклоняет мероприятие
     */
    public function rejectEvent()
    {
        Helper::requireManager();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $eventId = $_POST['event_id'] ?? 0;
            $reason = $_POST['rejection_reason'] ?? '';
            $userId = Helper::getUserId();

            if ($eventId && $userId && !empty($reason)) {
                $event = $this->eventModel->getById($eventId);
                if ($event && $this->eventModel->reject($eventId, $userId, $reason)) {
                    // Отправляем уведомление пользователю
                    $this->sendEventNotification($event['user_id'], $eventId, 'rejected', $reason);
                    $_SESSION['success_message'] = 'Мероприятие отклонено';
                } else {
                    $_SESSION['error_message'] = 'Ошибка при отклонении мероприятия';
                }
            } else {
                $_SESSION['error_message'] = 'Укажите причину отклонения';
            }
        }

        Helper::redirect('manager/events');
    }

    /**
     * Удаляет мероприятие (модерация — удалить без одобрения/отклонения)
     */
    public function deleteEvent()
    {
        Helper::requireManager();

        $redirectTo = (isset($_POST['from']) && $_POST['from'] === 'all') ? 'manager/events/all' : 'manager/events';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect($redirectTo);
            return;
        }

        $eventId = (int)($_POST['event_id'] ?? 0);
        if (!$eventId) {
            $_SESSION['error_message'] = 'Не указано мероприятие';
            Helper::redirect($redirectTo);
            return;
        }

        $event = $this->eventModel->getById($eventId);
        if (!$event) {
            $_SESSION['error_message'] = 'Мероприятие не найдено';
            Helper::redirect($redirectTo);
            return;
        }

        if ($this->eventModel->delete($eventId, (int)$event['user_id'])) {
            $_SESSION['success_message'] = 'Мероприятие удалено';
        } else {
            $_SESSION['error_message'] = 'Не удалось удалить мероприятие';
        }

        Helper::redirect($redirectTo);
    }

    /**
     * Отправляет уведомление пользователю о статусе мероприятия
     */
    private function sendEventNotification($userId, $eventId, $status, $reason = null)
    {
        $messageModel = new Message();
        $event = $this->eventModel->getById($eventId);

        if ($status === 'approved') {
            $message = "Ваше мероприятие \"{$event['title']}\" успешно одобрено и теперь отображается на сайте!";
        } else {
            $message = "Ваше мероприятие \"{$event['title']}\" было отклонено.\n\nПричина: {$reason}\n\nПожалуйста, исправьте указанные ошибки и создайте мероприятие заново.";
        }

        // Отправляем сообщение от менеджера БЕЗ привязки к event_id,
        // чтобы оно отображалось в "Уведомлениях от администратора"
        $managerId = Helper::getUserId();
        $messageModel->send($managerId, $userId, $message, null, null);

        // Отправляем push-уведомление пользователю
        $pushService = new PushNotificationService();
        $title = $status === 'approved' ? 'Мероприятие одобрено' : 'Мероприятие отклонено';
        $pushService->sendAdminNotification($userId, $title, $message);

        // Если мероприятие одобрено, отправляем push-уведомления всем пользователям в радиусе
        if ($status === 'approved' && $event['latitude'] && $event['longitude']) {
            $pushService->sendNewEventNotification(
                $eventId,
                $event['title'],
                $event['location'],
                $event['latitude'],
                $event['longitude'],
                RADIUS_KM
            );
        }
    }

    /**
     * Управление рекламой
     */
    public function ads()
    {
        Helper::requireManager();

        $db = Database::getInstance()->getConnection();
        $ads = $db->query("
            SELECT ads.*,
                   users.full_name,
                   users.gender,
                   users.age,
                   users.city as user_city,
                   users.country as user_country
            FROM ads
            LEFT JOIN users ON LOWER(ads.advertiser_email) = LOWER(users.email)
            ORDER BY ads.created_at DESC
        ")->fetchAll();

        View::render('admin/ads', [
            'ads' => $ads,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Одобряет рекламу
     */
    public function approveAd()
    {
        Helper::requireManager();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $adId = $_POST['ad_id'] ?? 0;

            if ($adId) {
                $ad = $this->adModel->findById($adId);
                if ($ad && $this->adModel->approve($adId)) {
                    // Уведомляем рекламодателя (сообщение в личный кабинет + push)
                    $this->sendAdApprovalNotification($ad);
                    $_SESSION['success_message'] = 'Реклама одобрена';
                } else {
                    $_SESSION['error_message'] = 'Ошибка при одобрении рекламы';
                }
            }
        }

        Helper::redirect('manager/ads');
    }

    /**
     * Отклоняет рекламу
     */
    public function rejectAd()
    {
        Helper::requireManager();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $adId = $_POST['ad_id'] ?? 0;
            $rejectionReason = trim($_POST['rejection_reason'] ?? '');

            if ($adId) {
                if (empty($rejectionReason)) {
                    $_SESSION['error_message'] = 'Укажите причину отказа';
                    Helper::redirect('manager/ads');
                    return;
                }

                $ad = $this->adModel->findById($adId);
                if ($ad && $this->adModel->reject($adId, $rejectionReason)) {
                    // Отправляем email рекламодателю с причиной отказа
                    $this->sendAdRejectionEmail($ad, $rejectionReason);
                    // Отправляем уведомление в личный кабинет
                    $this->sendAdRejectionNotification($ad, $rejectionReason);
                    $_SESSION['success_message'] = 'Реклама отклонена. Причина отказа отправлена рекламодателю.';
                } else {
                    $_SESSION['error_message'] = 'Ошибка при отклонении рекламы';
                }
            }
        }

        Helper::redirect('manager/ads');
    }

    /**
     * Отправляет уведомление в личный кабинет рекламодателю о отклонении рекламы
     */
    private function sendAdRejectionNotification($ad, $reason)
    {
        $advertiserEmail = $ad['advertiser_email'];

        if (empty($advertiserEmail)) {
            error_log("ManagerController: No email for ad rejection notification: ad #{$ad['id']}");
            return false;
        }

        // Находим пользователя по email
        $user = $this->userModel->findByEmail($advertiserEmail);
        if (!$user || empty($user['id'])) {
            error_log("ManagerController: User not found for email $advertiserEmail for ad #{$ad['id']}");
            return false;
        }

        $userId = $user['id'];
        $managerId = Helper::getUserId();

        if (!$managerId) {
            error_log("ManagerController: Manager ID not found for ad rejection notification");
            return false;
        }

        // Формируем сообщение для личного кабинета
        $message = "Ваша реклама была отклонена.\n\n";
        $message .= "Рекламодатель: " . $ad['advertiser_name'] . "\n";
        $message .= "Период показа: " . date('d.m.Y', strtotime($ad['start_date'])) . " - " . date('d.m.Y', strtotime($ad['end_date'])) . "\n";
        $message .= "Страна: " . $ad['country'] . "\n\n";
        $message .= "Причина отказа:\n" . $reason . "\n\n";
        $message .= "Пожалуйста, исправьте указанные ошибки и создайте рекламу заново.";

        // Отправляем сообщение в личный кабинет
        if ($this->messageModel->send($managerId, $userId, $message)) {
            // Отправляем push-уведомление
            $pushService = new PushNotificationService();
            $pushService->sendAdminNotification($userId, 'Реклама отклонена', 'Ваша реклама была отклонена. Причина указана в сообщении.');

            error_log("ManagerController: Ad rejection notification sent to user #$userId for ad #{$ad['id']}");
            return true;
        } else {
            error_log("ManagerController: Failed to send ad rejection notification to user #$userId for ad #{$ad['id']}");
            return false;
        }
    }

    /**
     * Отправляет уведомление рекламодателю об одобрении рекламы (личный кабинет + push)
     */
    private function sendAdApprovalNotification($ad)
    {
        $advertiserEmail = $ad['advertiser_email'] ?? '';

        if (empty($advertiserEmail)) {
            error_log("ManagerController: No email for ad approval notification: ad #{$ad['id']}");
            return false;
        }

        // Находим пользователя по email
        $user = $this->userModel->findByEmail($advertiserEmail);
        if (!$user || empty($user['id'])) {
            error_log("ManagerController: User not found for email $advertiserEmail for ad #{$ad['id']}");
            return false;
        }

        $userId = (int)$user['id'];
        $managerId = Helper::getUserId();
        if (!$managerId) {
            error_log("ManagerController: Manager ID not found for ad approval notification");
            return false;
        }

        $message = "Ваша реклама одобрена и активирована.\n\n";
        $message .= "Рекламодатель: " . ($ad['advertiser_name'] ?? '-') . "\n";
        $message .= "Период показа: " . date('d.m.Y', strtotime($ad['start_date'])) . " - " . date('d.m.Y', strtotime($ad['end_date'])) . "\n";
        $message .= "Страна: " . ($ad['country'] ?? '-') . "\n";
        $message .= "Город: " . (!empty($ad['city']) ? $ad['city'] : '—') . "\n\n";
        $message .= "Спасибо! Ваша реклама будет показана пользователям согласно указанным параметрам.";

        // Сообщение в личный кабинет
        if ($this->messageModel->send($managerId, $userId, $message)) {
            // Push
            $pushService = new PushNotificationService();
            $pushService->sendAdminNotification($userId, 'Реклама одобрена', 'Ваша реклама одобрена и активирована. Детали в сообщении.');
            error_log("ManagerController: Ad approval notification sent to user #$userId for ad #{$ad['id']}");
            return true;
        }

        error_log("ManagerController: Failed to send ad approval notification to user #$userId for ad #{$ad['id']}");
        return false;
    }

    /**
     * Отправляет email рекламодателю о отклонении рекламы
     */
    private function sendAdRejectionEmail($ad, $reason)
    {
        $emailService = new EmailService();
        $advertiserEmail = $ad['advertiser_email'];

        if (empty($advertiserEmail) || !filter_var($advertiserEmail, FILTER_VALIDATE_EMAIL)) {
            error_log("ManagerController: Invalid email for ad rejection: $advertiserEmail");
            return false;
        }

        $subject = 'Ваша реклама была отклонена #' . $ad['id'];

        $body = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">';
        $body .= '<h2 style="color: #333;">Уведомление о рекламе</h2>';
        $body .= '<p>Здравствуйте!</p>';
        $body .= '<p>К сожалению, ваша заявка на размещение рекламы была отклонена модератором.</p>';
        $body .= '<div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;">';
        $body .= '<h3 style="margin-top: 0; color: #856404;">Информация о рекламе:</h3>';
        $body .= '<p style="margin: 5px 0;"><strong>Рекламодатель:</strong> ' . Helper::escape($ad['advertiser_name']) . '</p>';
        $body .= '<p style="margin: 5px 0;"><strong>Период показа:</strong> ' . date('d.m.Y', strtotime($ad['start_date'])) . ' - ' . date('d.m.Y', strtotime($ad['end_date'])) . '</p>';
        $body .= '<p style="margin: 5px 0;"><strong>Страна:</strong> ' . Helper::escape($ad['country']) . '</p>';
        $body .= '</div>';
        $body .= '<div style="background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0;">';
        $body .= '<h3 style="margin-top: 0; color: #721c24;">Причина отказа:</h3>';
        $body .= '<p style="margin: 0; white-space: pre-wrap;">' . nl2br(Helper::escape($reason)) . '</p>';
        $body .= '</div>';
        $body .= '<p style="margin-top: 20px;">Если у вас есть вопросы, пожалуйста, обратитесь в службу поддержки.</p>';
        $body .= '<p style="color: #666; font-size: 12px; margin-top: 20px;">С уважением,<br>Команда модерации</p>';
        $body .= '</div>';

        if ($emailService->send($advertiserEmail, $subject, $body)) {
            error_log("ManagerController: Ad rejection email sent to $advertiserEmail for ad #{$ad['id']}");
            return true;
        } else {
            error_log("ManagerController: Failed to send ad rejection email to $advertiserEmail for ad #{$ad['id']}");
            return false;
        }
    }

    /**
     * Удаляет рекламу
     */
    public function deleteAd()
    {
        Helper::requireManager();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $adId = $_POST['ad_id'] ?? 0;

            if ($adId) {
                $ad = $this->adModel->findById($adId);
                if ($ad && $this->adModel->delete($adId)) {
                    $_SESSION['success_message'] = 'Реклама успешно удалена';
                } else {
                    $_SESSION['error_message'] = 'Ошибка при удалении рекламы';
                }
            } else {
                $_SESSION['error_message'] = 'Не указан ID рекламы';
            }
        }

        Helper::redirect('manager/ads');
    }

    /**
     * Управление обратной связью от пользователей
     */
    public function feedback()
    {
        Helper::requireManager();

        // Проверяем, не заблокирован ли профиль пользователя (для менеджеров)
        if (Helper::isLoggedIn() && !Helper::isAdminLoggedIn()) {
            Helper::checkProfileBlocked();
        }

        $status = $_GET['status'] ?? 'all';
        $feedbackList = [];

        if ($status === 'all') {
            $feedbackList = $this->feedbackModel->getAll(100);
        } else {
            $feedbackList = $this->feedbackModel->getByStatus($status, 100);
        }

        // Получаем статистику
        $stats = [
            'total' => count($this->feedbackModel->getAll(1000)),
            'new' => $this->feedbackModel->getNewCount(),
            'in_progress' => count($this->feedbackModel->getByStatus('in_progress', 1000)),
            'resolved' => count($this->feedbackModel->getByStatus('resolved', 1000)),
            'closed' => count($this->feedbackModel->getByStatus('closed', 1000))
        ];

        View::render('manager/feedback', [
            'feedback' => $feedbackList,
            'stats' => $stats,
            'currentStatus' => $status,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Обновляет статус заявки обратной связи
     */
    public function updateFeedbackStatus()
    {
        Helper::requireManager();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect('manager/feedback');
            return;
        }

        $feedbackId = $_POST['feedback_id'] ?? null;
        $status = $_POST['status'] ?? null;
        $adminNotes = trim($_POST['admin_notes'] ?? '');
        $adminReply = trim($_POST['admin_reply'] ?? '');

        if ($feedbackId && $status) {
            $allowedStatuses = ['new', 'in_progress', 'resolved', 'closed'];
            if (in_array($status, $allowedStatuses)) {
                // Получаем информацию о заявке для отправки ответа
                $feedback = $this->feedbackModel->findById($feedbackId);

                if ($feedback) {
                    // Обновляем статус и заметки
                    if ($this->feedbackModel->updateStatus($feedbackId, $status, $adminNotes)) {
                        $successMessage = 'Статус заявки успешно обновлен';

                        // Если заполнен ответ, отправляем email и сообщение в аккаунт пользователю
                        if (!empty($adminReply)) {
                            $recipientEmail = $feedback['email'] ?? $feedback['user_email'] ?? null;
                            $recipientUserId = $feedback['user_id'] ?? null;
                            $managerId = Helper::getUserId();

                            // Формируем текст сообщения для пользователя
                            $messageText = "Ответ на ваше обращение #{$feedbackId}\n\n";
                            $messageText .= "Тема: " . $feedback['subject'] . "\n\n";
                            $messageText .= $adminReply;

                            // Если пользователь авторизован, отправляем сообщение в аккаунт
                            if ($recipientUserId && $managerId) {
                                try {
                                    // Сохраняем сообщение в аккаунт пользователя
                                    if ($this->messageModel->send($managerId, $recipientUserId, $messageText)) {
                                        // Отправляем push-уведомление
                                        $pushService = new PushNotificationService();
                                        $pushService->sendAdminNotification(
                                            $recipientUserId,
                                            'Ответ на ваше обращение',
                                            'Ответ на обращение: ' . mb_substr($feedback['subject'], 0, 50)
                                        );
                                        $successMessage .= '. Ответ отправлен в аккаунт пользователя';
                                        error_log("ManagerController: Feedback reply sent to user account #$recipientUserId for feedback #$feedbackId");
                                    } else {
                                        error_log("ManagerController: Failed to send feedback reply to user account #$recipientUserId for feedback #$feedbackId");
                                    }
                                } catch (Exception $e) {
                                    error_log("ManagerController: Exception while sending feedback reply to user account: " . $e->getMessage());
                                }
                            }

                            // Отправляем email (если указан email)
                            if ($recipientEmail && filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                                try {
                                    $emailService = new EmailService();

                                    // Формируем тему письма
                                    $subject = 'Ответ на ваше обращение #' . $feedbackId;

                                    // Формируем тело письма
                                    $body = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">';
                                    $body .= '<h2 style="color: #333;">Ответ на ваше обращение</h2>';
                                    $body .= '<p>Здравствуйте!</p>';
                                    $body .= '<p>Вы оставили обращение с темой: <strong>' . Helper::escape($feedback['subject']) . '</strong></p>';
                                    $body .= '<div style="background-color: #f5f5f5; padding: 15px; border-left: 4px solid #007bff; margin: 20px 0;">';
                                    $body .= '<p style="margin: 0; white-space: pre-wrap;">' . nl2br(Helper::escape($adminReply)) . '</p>';
                                    $body .= '</div>';
                                    $body .= '<p style="color: #666; font-size: 12px; margin-top: 20px;">С уважением,<br>Команда поддержки</p>';
                                    $body .= '</div>';

                                    // Отправляем email
                                    if ($emailService->send($recipientEmail, $subject, $body)) {
                                        $successMessage .= '. Ответ отправлен на email';
                                        error_log("ManagerController: Feedback reply sent to $recipientEmail for feedback #$feedbackId");
                                    } else {
                                        if (empty($recipientUserId)) {
                                            $_SESSION['error_message'] = 'Статус обновлен, но не удалось отправить ответ на email';
                                        }
                                        error_log("ManagerController: Failed to send feedback reply to $recipientEmail for feedback #$feedbackId");
                                    }
                                } catch (Exception $e) {
                                    error_log("ManagerController: Exception while sending feedback reply email: " . $e->getMessage());
                                    if (empty($recipientUserId)) {
                                        $_SESSION['error_message'] = 'Статус обновлен, но произошла ошибка при отправке ответа на email';
                                    }
                                }
                            } elseif (empty($recipientUserId)) {
                                $_SESSION['error_message'] = 'Статус обновлен, но email пользователя не указан или неверен';
                            }
                        }

                        $_SESSION['success_message'] = $successMessage;
                    } else {
                        $_SESSION['error_message'] = 'Ошибка при обновлении статуса';
                    }
                } else {
                    $_SESSION['error_message'] = 'Заявка не найдена';
                }
            } else {
                $_SESSION['error_message'] = 'Неверный статус';
            }
        } else {
            $_SESSION['error_message'] = 'Неверные данные';
        }

        Helper::redirect('manager/feedback');
    }

    /**
     * Удаляет заявку обратной связи
     */
    public function deleteFeedback()
    {
        Helper::requireManager();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect('manager/feedback');
            return;
        }

        $feedbackId = $_POST['feedback_id'] ?? null;

        if ($feedbackId) {
            // Проверяем, что заявка существует
            $feedback = $this->feedbackModel->findById($feedbackId);
            if ($feedback) {
                if ($this->feedbackModel->delete($feedbackId)) {
                    $_SESSION['success_message'] = 'Заявка успешно удалена';
                } else {
                    $_SESSION['error_message'] = 'Ошибка при удалении заявки';
                }
            } else {
                $_SESSION['error_message'] = 'Заявка не найдена';
            }
        } else {
            $_SESSION['error_message'] = 'Неверные данные';
        }

        Helper::redirect('manager/feedback');
    }
}

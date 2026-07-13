<?php

/**
 * РОУТЕР - МАРШРУТИЗАТОР ЗАПРОСОВ
 *
 * Роутер определяет какой контроллер и метод нужно вызвать
 * в зависимости от URL адреса.
 *
 * Например:
 * /home -> HomeController->index()
 * /profile -> ProfileController->index()
 * /auth/login -> AuthController->login()
 */

class Router
{
    private $routes = []; // Массив маршрутов

    /**
     * Конструктор - вызывается при создании объекта
     * Здесь мы определяем все маршруты нашего приложения
     */
    public function __construct()
    {
        // Главная страница (landing)
        $this->routes[''] = ['controller' => 'Home', 'method' => 'landing'];
        $this->routes['home'] = ['controller' => 'Home', 'method' => 'landing'];
        // Платформа (функционал с пользователями)
        $this->routes['platform'] = ['controller' => 'Home', 'method' => 'platform'];
        $this->routes['info'] = ['controller' => 'Home', 'method' => 'info'];

        // Публичный API (JSON)
        $this->routes['api/exchange-rates'] = ['controller' => 'Api', 'method' => 'exchangeRates'];

        // Авторизация и регистрация
        $this->routes['auth/login'] = ['controller' => 'Auth', 'method' => 'login'];
        $this->routes['auth/register'] = ['controller' => 'Auth', 'method' => 'register'];
        $this->routes['auth/logout'] = ['controller' => 'Auth', 'method' => 'logout'];
        $this->routes['auth/verify'] = ['controller' => 'Auth', 'method' => 'verify'];
        $this->routes['auth/forgot-password'] = ['controller' => 'Auth', 'method' => 'forgotPassword'];
        $this->routes['auth/reset-password'] = ['controller' => 'Auth', 'method' => 'resetPassword'];

        // Личный кабинет
        $this->routes['profile'] = ['controller' => 'Profile', 'method' => 'index'];
        $this->routes['profile/view'] = ['controller' => 'Profile', 'method' => 'view'];
        $this->routes['profile/edit'] = ['controller' => 'Profile', 'method' => 'edit'];
        $this->routes['profile/update'] = ['controller' => 'Profile', 'method' => 'update'];
        $this->routes['profile/deletePhoto'] = ['controller' => 'Profile', 'method' => 'deletePhoto'];
        $this->routes['profile/addPhoto'] = ['controller' => 'Profile', 'method' => 'addPhoto'];
        $this->routes['profile/uploadTempPhoto'] = ['controller' => 'Profile', 'method' => 'uploadTempPhoto'];
        $this->routes['profile/deleteTempPhoto'] = ['controller' => 'Profile', 'method' => 'deleteTempPhoto'];

        // Карта
        $this->routes['map'] = ['controller' => 'Map', 'method' => 'index'];

        // Сообщения
        $this->routes['messages'] = ['controller' => 'Messages', 'method' => 'index'];
        $this->routes['messages/send'] = ['controller' => 'Messages', 'method' => 'send'];
        $this->routes['messages/block'] = ['controller' => 'Messages', 'method' => 'block'];
        $this->routes['messages/deleteConversation'] = ['controller' => 'Messages', 'method' => 'deleteConversation'];
        $this->routes['messages/deleteAdminNotification'] = ['controller' => 'Messages', 'method' => 'deleteAdminNotification'];
        $this->routes['messages/clearNotifications'] = ['controller' => 'Messages', 'method' => 'clearNotifications'];
        $this->routes['messages/unread'] = ['controller' => 'Messages', 'method' => 'getUnreadCount'];
        $this->routes['messages/new'] = ['controller' => 'Messages', 'method' => 'getNewMessages'];
        $this->routes['messages/unread-date'] = ['controller' => 'Messages', 'method' => 'getUnreadDateCount'];
        $this->routes['messages/unread-event'] = ['controller' => 'Messages', 'method' => 'getUnreadEventCount'];
        $this->routes['messages/unread-dates-total'] = ['controller' => 'Messages', 'method' => 'getTotalUnreadDatesCount'];
        $this->routes['messages/unread-events-total'] = ['controller' => 'Messages', 'method' => 'getTotalUnreadEventsCount'];
        $this->routes['messages/date'] = ['controller' => 'Messages', 'method' => 'dateChat'];
        $this->routes['messages/dates-list'] = ['controller' => 'Messages', 'method' => 'dateChatsList'];
        $this->routes['messages/event'] = ['controller' => 'Messages', 'method' => 'eventChat'];
        $this->routes['messages/events-list'] = ['controller' => 'Messages', 'method' => 'eventChatsList'];
        $this->routes['messages/event-updates'] = ['controller' => 'Messages', 'method' => 'getEventChatUpdates'];
        $this->routes['messages/deleteEventChat'] = ['controller' => 'Messages', 'method' => 'deleteEventChat'];
        $this->routes['messages/deleteDateChat'] = ['controller' => 'Messages', 'method' => 'deleteDateChat'];
        $this->routes['messages/blockAndDeleteDateChat'] = ['controller' => 'Messages', 'method' => 'blockAndDeleteDateChat'];
        $this->routes['messages/blockAndDeleteEventChat'] = ['controller' => 'Messages', 'method' => 'blockAndDeleteEventChat'];
        $this->routes['messages/blockAndDeleteConversation'] = ['controller' => 'Messages', 'method' => 'blockAndDeleteConversation'];

        // Push-уведомления
        $this->routes['push-notifications/register'] = ['controller' => 'PushNotification', 'method' => 'register'];
        $this->routes['push-notifications/unregister'] = ['controller' => 'PushNotification', 'method' => 'unregister'];

        // Обратная связь
        $this->routes['feedback/submit'] = ['controller' => 'Feedback', 'method' => 'submit'];
        $this->routes['feedback/check-status'] = ['controller' => 'Feedback', 'method' => 'checkStatus'];

        // Реклама
        $this->routes['ads/create'] = ['controller' => 'Ad', 'method' => 'create'];
        $this->routes['ads/store'] = ['controller' => 'Ad', 'method' => 'store'];
        $this->routes['ads/view'] = ['controller' => 'Ad', 'method' => 'view'];
        $this->routes['ads/delete'] = ['controller' => 'Ad', 'method' => 'delete'];

        // Свидания
        $this->routes['dates'] = ['controller' => 'Dates', 'method' => 'index'];
        $this->routes['dates/create'] = ['controller' => 'Dates', 'method' => 'create'];
        $this->routes['dates/store'] = ['controller' => 'Dates', 'method' => 'store'];
        $this->routes['dates/edit'] = ['controller' => 'Dates', 'method' => 'edit'];
        $this->routes['dates/update'] = ['controller' => 'Dates', 'method' => 'update'];
        $this->routes['dates/delete'] = ['controller' => 'Dates', 'method' => 'delete'];
        $this->routes['dates/deleteExpired'] = ['controller' => 'Dates', 'method' => 'deleteExpired'];

        // Мероприятия
        $this->routes['events'] = ['controller' => 'Events', 'method' => 'index'];
        $this->routes['events/create'] = ['controller' => 'Events', 'method' => 'create'];
        $this->routes['events/store'] = ['controller' => 'Events', 'method' => 'store'];
        $this->routes['events/edit'] = ['controller' => 'Events', 'method' => 'edit'];
        $this->routes['events/update'] = ['controller' => 'Events', 'method' => 'update'];
        $this->routes['events/delete'] = ['controller' => 'Events', 'method' => 'delete'];

        // Админка
        $this->routes['admin'] = ['controller' => 'Admin', 'method' => 'index'];
        $this->routes['admin/stats'] = ['controller' => 'Admin', 'method' => 'stats'];
        $this->routes['admin/login'] = ['controller' => 'Admin', 'method' => 'login'];
        $this->routes['admin/logout'] = ['controller' => 'Admin', 'method' => 'logout'];
        $this->routes['admin/change-password'] = ['controller' => 'Admin', 'method' => 'changePassword'];
        $this->routes['admin/users'] = ['controller' => 'Admin', 'method' => 'users'];
        $this->routes['admin/users/update-role'] = ['controller' => 'Admin', 'method' => 'updateUserRole'];
        $this->routes['admin/users/delete'] = ['controller' => 'Admin', 'method' => 'deleteUser'];
        $this->routes['admin/users/set-remark'] = ['controller' => 'Admin', 'method' => 'setUserRemark'];
        $this->routes['admin/users/unblock'] = ['controller' => 'Admin', 'method' => 'unblockUser'];
        $this->routes['admin/ads'] = ['controller' => 'Admin', 'method' => 'ads'];
        $this->routes['admin/ads/approve'] = ['controller' => 'Admin', 'method' => 'approveAd'];
        $this->routes['admin/ads/reject'] = ['controller' => 'Admin', 'method' => 'rejectAd'];
        $this->routes['admin/ads/delete'] = ['controller' => 'Admin', 'method' => 'deleteAd'];
        $this->routes['admin/events'] = ['controller' => 'Admin', 'method' => 'events'];
        $this->routes['admin/events/all'] = ['controller' => 'Admin', 'method' => 'allEvents'];
        $this->routes['admin/events/approve'] = ['controller' => 'Admin', 'method' => 'approveEvent'];
        $this->routes['admin/events/reject'] = ['controller' => 'Admin', 'method' => 'rejectEvent'];
        $this->routes['admin/events/delete'] = ['controller' => 'Admin', 'method' => 'deleteEvent'];
        $this->routes['admin/dates/all'] = ['controller' => 'Admin', 'method' => 'allDates'];
        $this->routes['admin/dates/delete'] = ['controller' => 'Admin', 'method' => 'deleteDate'];
        $this->routes['admin/feedback'] = ['controller' => 'Admin', 'method' => 'feedback'];
        $this->routes['admin/feedback/update-status'] = ['controller' => 'Admin', 'method' => 'updateFeedbackStatus'];
        $this->routes['admin/feedback/delete'] = ['controller' => 'Admin', 'method' => 'deleteFeedback'];
        $this->routes['admin/send-message'] = ['controller' => 'Admin', 'method' => 'sendMessage'];
        $this->routes['admin/send-message/submit'] = ['controller' => 'Admin', 'method' => 'submitMessage'];
        $this->routes['admin/activity-logs'] = ['controller' => 'Admin', 'method' => 'activityLogs'];

        // Панель менеджера
        $this->routes['manager'] = ['controller' => 'Manager', 'method' => 'index'];
        $this->routes['manager/stats'] = ['controller' => 'Manager', 'method' => 'stats'];
        $this->routes['manager/users'] = ['controller' => 'Manager', 'method' => 'users'];
        $this->routes['manager/users/update-role'] = ['controller' => 'Manager', 'method' => 'updateUserRole'];
        $this->routes['manager/users/delete'] = ['controller' => 'Manager', 'method' => 'deleteUser'];
        $this->routes['manager/users/add-remark'] = ['controller' => 'Manager', 'method' => 'addRemark'];
        $this->routes['manager/users/view-remark'] = ['controller' => 'Manager', 'method' => 'viewRemark'];
        $this->routes['manager/users/set-remark'] = ['controller' => 'Manager', 'method' => 'setUserRemark'];
        $this->routes['manager/users/unblock'] = ['controller' => 'Manager', 'method' => 'unblockUser'];
        $this->routes['manager/categories'] = ['controller' => 'Manager', 'method' => 'categories'];
        $this->routes['manager/categories/store'] = ['controller' => 'Manager', 'method' => 'categoryStore'];
        $this->routes['manager/categories/update'] = ['controller' => 'Manager', 'method' => 'categoryUpdate'];
        $this->routes['manager/categories/delete'] = ['controller' => 'Manager', 'method' => 'categoryDelete'];
        $this->routes['manager/events'] = ['controller' => 'Manager', 'method' => 'events'];
        $this->routes['manager/events/all'] = ['controller' => 'Manager', 'method' => 'allEvents'];
        $this->routes['manager/events/approve'] = ['controller' => 'Manager', 'method' => 'approveEvent'];
        $this->routes['manager/events/reject'] = ['controller' => 'Manager', 'method' => 'rejectEvent'];
        $this->routes['manager/events/delete'] = ['controller' => 'Manager', 'method' => 'deleteEvent'];
        $this->routes['manager/events/extend-deadline'] = ['controller' => 'Manager', 'method' => 'extendEventDeadline'];
        $this->routes['manager/dates/all'] = ['controller' => 'Manager', 'method' => 'allDates'];
        $this->routes['manager/dates/extend-deadline'] = ['controller' => 'Manager', 'method' => 'extendDateDeadline'];
        $this->routes['manager/dates/delete'] = ['controller' => 'Manager', 'method' => 'deleteDate'];
        $this->routes['manager/dates/set-remark'] = ['controller' => 'Manager', 'method' => 'setDateRemark'];
        $this->routes['manager/send-message'] = ['controller' => 'Manager', 'method' => 'sendMessage'];
        $this->routes['manager/send-message/submit'] = ['controller' => 'Manager', 'method' => 'submitMessage'];
        $this->routes['manager/ads'] = ['controller' => 'Manager', 'method' => 'ads'];
        $this->routes['manager/ads/approve'] = ['controller' => 'Manager', 'method' => 'approveAd'];
        $this->routes['manager/ads/reject'] = ['controller' => 'Manager', 'method' => 'rejectAd'];
        $this->routes['manager/ads/delete'] = ['controller' => 'Manager', 'method' => 'deleteAd'];
        $this->routes['manager/feedback'] = ['controller' => 'Manager', 'method' => 'feedback'];
        $this->routes['manager/feedback/update-status'] = ['controller' => 'Manager', 'method' => 'updateFeedbackStatus'];
        $this->routes['manager/feedback/delete'] = ['controller' => 'Manager', 'method' => 'deleteFeedback'];
    }

    /**
     * Обрабатывает запрос и вызывает нужный контроллер
     */
    public function dispatch()
    {
        // Получаем URL без параметров
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        // Убираем параметры запроса
        $uri = strtok($uri, '?');
        // Убираем базовый путь проекта
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/' && $scriptName !== '\\') {
            $uri = str_replace($scriptName, '', $uri);
        }

        // Если определен BASE_URL, убираем его путь из URI
        if (defined('BASE_URL')) {
            $basePath = parse_url(BASE_URL, PHP_URL_PATH);
            if ($basePath && $basePath !== '/') {
                $basePath = trim($basePath, '/');
                // Убираем базовый путь из URI
                if (strpos($uri, '/' . $basePath) === 0) {
                    $uri = substr($uri, strlen('/' . $basePath) + 1);
                }
            }
        }

        $uri = trim($uri, '/');

        // Если пустой URI, используем главную страницу
        if (empty($uri)) {
            $uri = '';
        }

        // Если есть маршрут для этого URL
        if (isset($this->routes[$uri])) {
            $route = $this->routes[$uri];
            $controllerName = $route['controller'] . 'Controller';
            $methodName = $route['method'];

            // Логирование действий пользователей (не админка)
            $isUserLoggedIn = Helper::isLoggedIn();
            $isAdminLoggedIn = Helper::isAdminLoggedIn();
            if ($isUserLoggedIn && !$isAdminLoggedIn && strpos($uri, 'admin') !== 0 && strpos($uri, 'api/') !== 0) {
                $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
                $action = strtoupper($requestMethod) === 'GET' ? 'view' : 'submit';
                $params = strtoupper($requestMethod) === 'POST' ? ($_POST ?? []) : ($_GET ?? []);
                $queryString = $_SERVER['QUERY_STRING'] ?? '';
                $userId = Helper::getUserId();
                if ($userId) {
                    $activityLog = new UserActivityLog();
                    $activityLog->logRequest($userId, $uri, $requestMethod, $action, $params, $queryString);
                }
            }

            // Создаем объект контроллера
            $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                $controller = new $controllerName();

                // Вызываем нужный метод
                if (method_exists($controller, $methodName)) {
                    $controller->$methodName();
                } else {
                    $this->error404();
                }
            } else {
                $this->error404();
            }
        } else {
            $this->error404();
        }
    }

    /**
     * Показывает страницу 404 (страница не найдена)
     */
    private function error404()
    {
        http_response_code(404);
        View::render('error/404', []);
    }
}

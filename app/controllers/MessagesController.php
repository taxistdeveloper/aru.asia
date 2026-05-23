<?php

/**
 * КОНТРОЛЛЕР СООБЩЕНИЙ
 *
 * Обрабатывает отправку и получение сообщений между пользователями
 */

class MessagesController
{
    private $messageModel;
    private $blockedModel;
    private $dateModel;
    private $eventModel;
    private $userModel;
    private $adminModel;

    public function __construct()
    {
        $this->messageModel = new Message();
        $this->blockedModel = new BlockedUser();
        $this->dateModel = new Date();
        $this->eventModel = new Event();
        $this->userModel = new User();
        $this->adminModel = new Admin();
    }

    /**
     * Показывает список диалогов
     */
    public function index()
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
        $conversations = $this->messageModel->getConversations($userId);
        $blockedUsers = $this->blockedModel->getBlockedUsers($userId);

        // Получаем уведомления (новые входящие сообщения)
        $notifications = $this->messageModel->getNewMessages($userId);
        $unreadCount = $this->messageModel->getUnreadCount($userId);

        // Получаем уведомления от администраторов
        $adminNotifications = $this->messageModel->getAdminNotifications($userId);
        $unreadAdminCount = $this->messageModel->getUnreadAdminNotificationsCount($userId);

        // Получаем конкретный диалог если выбран
        $selectedUserId = $_GET['user_id'] ?? null;
        $messages = [];
        $isBlockedByMe = false;
        $isBlockedByOther = false;

        if ($selectedUserId) {
            // Проверяем статус блокировки
            $isBlockedByMe = $this->blockedModel->isBlocked($userId, $selectedUserId);
            $isBlockedByOther = $this->blockedModel->isBlocked($selectedUserId, $userId);

            // Проверяем не заблокирован ли пользователь
            if (!$isBlockedByMe && !$isBlockedByOther) {
                $messages = $this->messageModel->getConversation($userId, $selectedUserId);
                // Помечаем все сообщения от выбранного пользователя как прочитанные
                $this->messageModel->markConversationAsRead($userId, $selectedUserId);
            }
        }

        View::render('messages/index', [
            'conversations' => $conversations,
            'blockedUsers' => $blockedUsers,
            'messages' => $messages,
            'selectedUserId' => $selectedUserId,
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'adminNotifications' => $adminNotifications,
            'unreadAdminCount' => $unreadAdminCount,
            'isMobile' => View::isMobile(),
            'isBlockedByMe' => $isBlockedByMe,
            'isBlockedByOther' => $isBlockedByOther
        ]);
    }

    /**
     * Отправляет сообщение
     */
    public function send()
    {
        if (!Helper::isLoggedIn()) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Не авторизован']);
                return;
            }
            Helper::redirect('auth/login');
            return;
        }

        // Проверяем, не заблокирован ли профиль пользователя
        Helper::checkProfileBlocked();

        // Проверяем, заполнен ли профиль
        Helper::checkProfileComplete();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fromUserId = Helper::getUserId();
            $toUserId = $_POST['to_user_id'] ?? 0;
            $message = $_POST['message'] ?? '';
            $dateId = $_POST['date_id'] ?? null;
            $eventId = $_POST['event_id'] ?? null;

            // Если это чат свидания или мероприятия, определяем получателя
            if ($dateId) {
                $date = $this->dateModel->getById($dateId);
                if ($date) {
                    // Если указан конкретный получатель (ответ на сообщение), используем его
                    if ($toUserId > 0) {
                        // to_user_id уже установлен из POST
                    } elseif ($fromUserId == $date['user_id']) {
                        // Если отправитель - владелец свидания, находим получателя из всех сообщений в чате
                        // Получаем все сообщения в чате, чтобы найти второго участника
                        $allMessages = $this->messageModel->getDateChat($dateId);
                        $participants = [];
                        foreach ($allMessages as $msg) {
                            if ($msg['from_user_id'] != $fromUserId) {
                                $participants[$msg['from_user_id']] = true;
                            }
                            if ($msg['to_user_id'] != $fromUserId && $msg['to_user_id'] != $date['user_id']) {
                                $participants[$msg['to_user_id']] = true;
                            }
                        }
                        // Берем первого найденного участника
                        if (!empty($participants)) {
                            $toUserId = key($participants); // Получаем первый ключ массива
                        } else {
                            // Если не нашли получателя, значит это первое сообщение от владельца
                            // В этом случае не отправляем уведомление (никто еще не писал в чат)
                            $toUserId = 0;
                        }
                    } else {
                        // Если отправитель - не владелец, получатель - владелец
                        $toUserId = $date['user_id'];
                    }
                }
            } elseif ($eventId) {
                $event = $this->eventModel->getById($eventId);
                if ($event) {
                    // Если указан конкретный получатель (ответ на сообщение), используем его
                    if ($toUserId > 0) {
                        // to_user_id уже установлен из POST
                    } elseif ($fromUserId == $event['user_id']) {
                        // Если отправитель - владелец мероприятия, находим получателя из всех сообщений в чате
                        // Получаем все сообщения в чате, чтобы найти второго участника
                        $allMessages = $this->messageModel->getEventChat($eventId);
                        $participants = [];
                        foreach ($allMessages as $msg) {
                            if ($msg['from_user_id'] != $fromUserId) {
                                $participants[$msg['from_user_id']] = true;
                            }
                            if ($msg['to_user_id'] != $fromUserId && $msg['to_user_id'] != $event['user_id']) {
                                $participants[$msg['to_user_id']] = true;
                            }
                        }
                        // Берем первого найденного участника
                        if (!empty($participants)) {
                            $toUserId = key($participants); // Получаем первый ключ массива
                        } else {
                            // Если не нашли получателя, значит это первое сообщение от владельца
                            // В этом случае не отправляем уведомление (никто еще не писал в чат)
                            $toUserId = 0;
                        }
                    } else {
                        // Если отправитель - не владелец, получатель - владелец
                        $toUserId = $event['user_id'];
                    }
                }
            }

            // Проверяем, не является ли получатель администратором (пользователи не могут писать администратору)
            $isAdmin = $this->adminModel->findById($toUserId);
            if ($isAdmin) {
                if ($this->isAjaxRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'error' => 'Нельзя отправлять сообщения администратору'
                    ]);
                    return;
                }
                $_SESSION['error_message'] = 'Нельзя отправлять сообщения администратору';
                Helper::redirect('messages');
                return;
            }

            // Проверяем не заблокирован ли пользователь и что получатель определен
            if ($toUserId > 0) {
                // Проверяем, заблокировал ли получатель отправителя
                if ($this->blockedModel->isBlocked($toUserId, $fromUserId)) {
                    $errorMsg = 'Пользователь вас заблокировал';
                    if ($this->isAjaxRequest()) {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => false,
                            'error' => $errorMsg
                        ]);
                        return;
                    }
                    $_SESSION['error_message'] = $errorMsg;
                    if ($dateId) {
                        Helper::redirect('messages/date?date_id=' . $dateId);
                    } elseif ($eventId) {
                        Helper::redirect('messages/event?event_id=' . $eventId);
                    } else {
                        Helper::redirect('messages?user_id=' . $toUserId);
                    }
                    return;
                }

                // Проверяем, заблокировал ли отправитель получателя
                if ($this->blockedModel->isBlocked($fromUserId, $toUserId)) {
                    $errorMsg = 'Вы заблокировали этого пользователя';
                    if ($this->isAjaxRequest()) {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => false,
                            'error' => $errorMsg
                        ]);
                        return;
                    }
                    $_SESSION['error_message'] = $errorMsg;
                    if ($dateId) {
                        Helper::redirect('messages/date?date_id=' . $dateId);
                    } elseif ($eventId) {
                        Helper::redirect('messages/event?event_id=' . $eventId);
                    } else {
                        Helper::redirect('messages?user_id=' . $toUserId);
                    }
                    return;
                }

                // Если не заблокирован, отправляем сообщение
                if (!empty($message)) {
                    $this->messageModel->send($fromUserId, $toUserId, $message, $dateId, $eventId);

                    // Отправляем push-уведомление получателю (только если получатель определен)
                    // Уведомление отправляется только если пользователь не находится в активном чате
                    if ($toUserId > 0) {
                        $pushService = new PushNotificationService();
                        // Передаем информацию о том, что это сообщение из чата
                        $pushService->sendMessageNotification($toUserId, $fromUserId, $message, $dateId, $eventId);
                    }

                    // Если это AJAX запрос, возвращаем JSON
                    if ($this->isAjaxRequest()) {
                        // Получаем последнее сообщение
                        if ($dateId) {
                            $messages = $this->messageModel->getDateChat($dateId);
                        } elseif ($eventId) {
                            $messages = $this->messageModel->getEventChat($eventId);
                        } else {
                            $conversation = $this->messageModel->getConversation($fromUserId, $toUserId);
                            $messages = $conversation;
                        }
                        $lastMessage = end($messages);

                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true,
                            'message' => $lastMessage
                        ]);
                        return;
                    }
                }
            }

            if (!$this->isAjaxRequest()) {
                if ($dateId) {
                    Helper::redirect('messages/date?date_id=' . $dateId);
                } elseif ($eventId) {
                    Helper::redirect('messages/event?event_id=' . $eventId);
                } else {
                    Helper::redirect('messages?user_id=' . $toUserId);
                }
            }
        }
    }

    /**
     * Проверяет является ли запрос AJAX
     */
    private function isAjaxRequest()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    /**
     * Блокирует пользователя
     */
    public function block()
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
        $blockedUserId = $_POST['blocked_user_id'] ?? $_GET['user_id'] ?? 0;

        if ($blockedUserId) {
            $this->blockedModel->block($userId, $blockedUserId);
        }

        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            return;
        }

        Helper::redirect('messages');
    }

    /**
     * Удаляет диалог
     */
    public function deleteConversation()
    {
        if (!Helper::isLoggedIn()) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Не авторизован']);
                return;
            }
            Helper::redirect('auth/login');
            return;
        }

        // Проверяем, не заблокирован ли профиль пользователя
        Helper::checkProfileBlocked();

        // Проверяем, заполнен ли профиль
        Helper::checkProfileComplete();

        $userId = Helper::getUserId();
        $otherUserId = $_POST['other_user_id'] ?? $_GET['user_id'] ?? 0;

        if (!$otherUserId) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Не указан ID пользователя']);
                return;
            }
            Helper::redirect('messages');
            return;
        }

        // Проверяем, что otherUserId - это число
        $otherUserId = (int)$otherUserId;
        if ($otherUserId <= 0) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Некорректный ID пользователя']);
                return;
            }
            Helper::redirect('messages');
            return;
        }

        $result = $this->messageModel->deleteConversation($userId, $otherUserId);

        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Диалог успешно удален']);
            } else {
                // Логируем для отладки
                error_log('Не удалось удалить диалог. userId: ' . $userId . ', otherUserId: ' . $otherUserId);
                echo json_encode([
                    'success' => false,
                    'error' => 'Не удалось удалить диалог. Возможно, диалог уже был удален или произошла ошибка базы данных.',
                    'debug' => [
                        'userId' => $userId,
                        'otherUserId' => $otherUserId
                    ]
                ]);
            }
            return;
        }

        Helper::redirect('messages');
    }

    /**
     * Удаляет уведомление от администратора
     */
    public function deleteAdminNotification()
    {
        if (!Helper::isLoggedIn()) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Не авторизован']);
                return;
            }
            Helper::redirect('auth/login');
            return;
        }

        $userId = Helper::getUserId();
        $messageId = $_POST['message_id'] ?? $_GET['message_id'] ?? null;

        if (!$messageId) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Не указан ID сообщения']);
                return;
            }
            Helper::redirect('messages');
            return;
        }

        $result = $this->messageModel->deleteAdminNotification($messageId, $userId);

        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Уведомление успешно удалено']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Не удалось удалить уведомление']);
            }
            return;
        }

        Helper::redirect('messages');
    }

    /**
     * Очищает все уведомления (помечает все входящие сообщения как прочитанные)
     */
    public function clearNotifications()
    {
        if (!Helper::isLoggedIn()) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Не авторизован']);
                return;
            }
            Helper::redirect('auth/login');
            return;
        }

        $userId = Helper::getUserId();
        $this->messageModel->markAllAsRead($userId);

        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            return;
        }

        Helper::redirect('messages');
    }

    /**
     * API: Получает количество непрочитанных сообщений (JSON)
     * Включает обычные сообщения + уведомления от администраторов
     */
    public function getUnreadCount()
    {
        if (!Helper::isLoggedIn()) {
            header('Content-Type: application/json');
            echo json_encode(['count' => 0]);
            return;
        }

        $userId = Helper::getUserId();
        $lastCheckTime = $_GET['last_check'] ?? null;

        // Считаем обычные непрочитанные сообщения
        $count = $this->messageModel->getUnreadCount($userId, $lastCheckTime);
        
        // Добавляем непрочитанные уведомления от администраторов/менеджеров
        $adminCount = $this->messageModel->getUnreadAdminNotificationsCount($userId);
        
        $totalCount = $count + $adminCount;

        header('Content-Type: application/json');
        echo json_encode(['count' => $totalCount]);
    }

    /**
     * API: Получает новые сообщения (JSON)
     */
    public function getNewMessages()
    {
        if (!Helper::isLoggedIn()) {
            header('Content-Type: application/json');
            echo json_encode(['messages' => []]);
            return;
        }

        $userId = Helper::getUserId();
        $lastCheckTime = $_GET['last_check'] ?? null;

        $messages = $this->messageModel->getNewMessages($userId, $lastCheckTime);

        header('Content-Type: application/json');
        echo json_encode(['messages' => $messages]);
    }

    /**
     * API: Получает количество непрочитанных сообщений для свидания (JSON)
     */
    public function getUnreadDateCount()
    {
        if (!Helper::isLoggedIn()) {
            header('Content-Type: application/json');
            echo json_encode(['count' => 0]);
            return;
        }

        $userId = Helper::getUserId();
        $dateId = $_GET['date_id'] ?? null;

        if (!$dateId) {
            header('Content-Type: application/json');
            echo json_encode(['count' => 0]);
            return;
        }

        $count = $this->messageModel->getUnreadCountForDate($dateId, $userId);

        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
    }

    /**
     * API: Получает количество непрочитанных сообщений для мероприятия (JSON)
     */
    public function getUnreadEventCount()
    {
        if (!Helper::isLoggedIn()) {
            header('Content-Type: application/json');
            echo json_encode(['count' => 0]);
            return;
        }

        $userId = Helper::getUserId();
        $eventId = $_GET['event_id'] ?? null;

        if (!$eventId) {
            header('Content-Type: application/json');
            echo json_encode(['count' => 0]);
            return;
        }

        $count = $this->messageModel->getUnreadCountForEvent($eventId, $userId);

        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
    }

    /**
     * API: Получает общее количество непрочитанных сообщений из всех свиданий (JSON)
     */
    public function getTotalUnreadDatesCount()
    {
        if (!Helper::isLoggedIn()) {
            header('Content-Type: application/json');
            echo json_encode(['count' => 0]);
            return;
        }

        $userId = Helper::getUserId();
        $count = $this->messageModel->getTotalUnreadDatesCount($userId);

        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
    }

    /**
     * API: Получает общее количество непрочитанных сообщений из всех мероприятий (JSON)
     */
    public function getTotalUnreadEventsCount()
    {
        if (!Helper::isLoggedIn()) {
            header('Content-Type: application/json');
            echo json_encode(['count' => 0]);
            return;
        }

        $userId = Helper::getUserId();
        $count = $this->messageModel->getTotalUnreadEventsCount($userId);

        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
    }

    /**
     * API: Получает новые сообщения для чата мероприятия (JSON)
     * Используется для обновления чата в реальном времени без перезагрузки страницы
     */
    public function getEventChatUpdates()
    {
        if (!Helper::isLoggedIn()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'messages' => [], 'error' => 'Не авторизован']);
            return;
        }

        // Проверяем, не заблокирован ли профиль пользователя
        Helper::checkProfileBlocked();

        // Проверяем, заполнен ли профиль
        Helper::checkProfileComplete();

        $eventId = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
        $lastId = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

        if ($eventId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'messages' => [], 'error' => 'Не указан ID мероприятия']);
            return;
        }

        $userId = Helper::getUserId();

        // Получаем новые сообщения
        if ($lastId > 0) {
            $messages = $this->messageModel->getEventChatAfterId($eventId, $lastId);
        } else {
            $messages = $this->messageModel->getEventChat($eventId);
        }

        // Фильтруем системные сообщения об одобрении мероприятия (они уже есть в уведомлениях)
        $messages = array_filter($messages, function($msg) {
            $messageText = $msg['message'] ?? '';
            return strpos($messageText, 'успешно одобрено и теперь отображается на сайте') === false;
        });
        $messages = array_values($messages);

        // Помечаем сообщения как прочитанные для текущего пользователя
        $this->messageModel->markEventChatAsRead($eventId, $userId);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'messages' => $messages
        ]);
    }

    /**
     * Показывает чат для свидания
     */
    public function dateChat()
    {
        if (!Helper::isLoggedIn()) {
            Helper::redirect('auth/login');
            return;
        }

        // Проверяем, не заблокирован ли профиль пользователя
        Helper::checkProfileBlocked();

        // Проверяем, заполнен ли профиль
        Helper::checkProfileComplete();

        $dateId = $_GET['date_id'] ?? null;
        if (!$dateId) {
            Helper::redirect('dates');
            return;
        }

        $date = $this->dateModel->getById($dateId);
        if (!$date) {
            Helper::redirect('dates');
            return;
        }

        $userId = Helper::getUserId();
        $dateOwner = $this->userModel->findById($date['user_id']);

        // Получаем список диалогов с участниками свидания
        $conversations = $this->messageModel->getDateConversations($dateId, $userId);

        // Проверяем статус блокировки с владельцем свидания
        $isOwnerBlockedByMe = false;
        $isOwnerBlockedByOther = false;
        if ($dateOwner && $userId != $date['user_id']) {
            $isOwnerBlockedByMe = $this->blockedModel->isBlocked($userId, $date['user_id']);
            $isOwnerBlockedByOther = $this->blockedModel->isBlocked($date['user_id'], $userId);
        }

        // Если есть выбранный пользователь, показываем диалог с ним
        $selectedUserId = $_GET['user_id'] ?? null;
        $messages = [];
        $isBlockedByMe = false;
        $isBlockedByOther = false;

        if ($selectedUserId) {
            // Проверяем статус блокировки
            $isBlockedByMe = $this->blockedModel->isBlocked($userId, $selectedUserId);
            $isBlockedByOther = $this->blockedModel->isBlocked($selectedUserId, $userId);

            // Проверяем не заблокирован ли пользователь
            if (!$isBlockedByMe && !$isBlockedByOther) {
                // Получаем переписку с этим пользователем (только для этого свидания)
                $allMessages = $this->messageModel->getDateChat($dateId);
                foreach ($allMessages as $msg) {
                    if (($msg['from_user_id'] == $userId && $msg['to_user_id'] == $selectedUserId) ||
                        ($msg['from_user_id'] == $selectedUserId && $msg['to_user_id'] == $userId)
                    ) {
                        $messages[] = $msg;
                    }
                }
                // Помечаем сообщения от выбранного пользователя как прочитанные
                $this->messageModel->markConversationAsRead($userId, $selectedUserId);
            }
        }

        View::render('messages/date_chat', [
            'date' => $date,
            'dateOwner' => $dateOwner,
            'conversations' => $conversations,
            'messages' => $messages,
            'selectedUserId' => $selectedUserId,
            'currentUserId' => $userId,
            'isBlockedByMe' => $isBlockedByMe,
            'isBlockedByOther' => $isBlockedByOther,
            'isOwnerBlockedByMe' => $isOwnerBlockedByMe,
            'isOwnerBlockedByOther' => $isOwnerBlockedByOther,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Показывает чат для мероприятия
     */
    public function eventChat()
    {
        if (!Helper::isLoggedIn()) {
            Helper::redirect('auth/login');
            return;
        }

        // Проверяем, не заблокирован ли профиль пользователя
        Helper::checkProfileBlocked();

        // Проверяем, заполнен ли профиль
        Helper::checkProfileComplete();

        $eventId = $_GET['event_id'] ?? null;
        if (!$eventId) {
            Helper::redirect('events');
            return;
        }

        $event = $this->eventModel->getById($eventId);
        if (!$event) {
            Helper::redirect('events');
            return;
        }

        $userId = Helper::getUserId();
        $messages = $this->messageModel->getEventChat($eventId);

        // Фильтруем системные сообщения об одобрении мероприятия (они уже есть в уведомлениях)
        $messages = array_filter($messages, function($msg) {
            $messageText = $msg['message'] ?? '';
            // Исключаем сообщения об одобрении мероприятия
            return strpos($messageText, 'успешно одобрено и теперь отображается на сайте') === false;
        });
        // Переиндексируем массив после фильтрации
        $messages = array_values($messages);

        $eventOwner = $this->userModel->findById($event['user_id']);

        // Помечаем все сообщения в чате мероприятия как прочитанные
        $this->messageModel->markEventChatAsRead($eventId, $userId);

        View::render('messages/event_chat', [
            'event' => $event,
            'eventOwner' => $eventOwner,
            'messages' => $messages,
            'currentUserId' => $userId,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Удаляет чат мероприятия для текущего пользователя
     */
    public function deleteEventChat()
    {
        if (!Helper::isLoggedIn()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Не авторизован']);
            return;
        }

        header('Content-Type: application/json');
        
        $eventId = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
        $userId = Helper::getUserId();

        // Логируем для отладки
        error_log('=== Попытка удалить чат мероприятия ===');
        error_log('event_id: ' . $eventId);
        error_log('user_id: ' . $userId);
        error_log('POST data: ' . print_r($_POST, true));

        if ($eventId <= 0) {
            error_log('Ошибка: event_id <= 0');
            echo json_encode(['success' => false, 'error' => 'Не указан ID мероприятия']);
            return;
        }

        if ($userId <= 0) {
            error_log('Ошибка: user_id <= 0');
            echo json_encode(['success' => false, 'error' => 'Ошибка авторизации']);
            return;
        }

        // Удаляем чат напрямую без проверки существования
        // Если пользователь видит кнопку удаления, значит чат существует
        try {
            $result = $this->messageModel->deleteEventChatForUser($eventId, $userId);

            if ($result) {
                error_log('Чат мероприятия успешно удален для eventId: ' . $eventId . ', userId: ' . $userId);
                echo json_encode(['success' => true]);
            } else {
                error_log('Не удалось удалить чат мероприятия для eventId: ' . $eventId . ', userId: ' . $userId . '. Подробности в логах модели.');
                echo json_encode(['success' => false, 'error' => 'Не удалось удалить чат. Попробуйте еще раз или обратитесь к администратору.']);
            }
        } catch (Exception $e) {
            error_log('Исключение при удалении чата мероприятия: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            echo json_encode(['success' => false, 'error' => 'Произошла ошибка при удалении чата. Попробуйте еще раз.']);
        }
    }

    /**
     * Показывает список чатов свиданий
     */
    public function dateChatsList()
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

        // Получаем свидания, в которых пользователь участвует в чате
        $dateIdsWithChat = $this->messageModel->getDateIdsWithChat($userId);
        
        // Логируем для отладки
        error_log('=== Получение списка чатов свиданий ===');
        error_log('user_id: ' . $userId);
        error_log('Найдено date_ids с чатами: ' . count($dateIdsWithChat));
        error_log('date_ids: ' . implode(', ', $dateIdsWithChat));
        
        $myDateChats = [];
        if (!empty($dateIdsWithChat)) {
            // Получаем свидания, которые еще существуют в БД
            $foundDates = $this->dateModel->getByIds($dateIdsWithChat);
            $foundDateIds = array_column($foundDates, 'id');
            $missingDateIds = array_diff($dateIdsWithChat, $foundDateIds);
            
            // Логируем результаты
            error_log('Получено свиданий из БД: ' . count($foundDates));
            error_log('Найдены: ' . implode(', ', $foundDateIds));
            
            if (!empty($missingDateIds)) {
                error_log('ВНИМАНИЕ: Не найдены свидания (возможно удалены): ' . implode(', ', $missingDateIds));
                // Получаем информацию о чатах из сообщений для удаленных свиданий
                foreach ($missingDateIds as $missingDateId) {
                    $chatInfo = $this->messageModel->getDateChatInfoFromMessages($missingDateId, $userId);
                    if ($chatInfo) {
                        $foundDates[] = $chatInfo;
                        error_log('Добавлен чат из сообщений для date_id: ' . $missingDateId);
                    }
                }
            }
            
            $myDateChats = $foundDates;
            
            foreach ($myDateChats as $date) {
                error_log('  - date_id: ' . $date['id'] . ', title: ' . ($date['title'] ?? 'N/A') . ', user_id: ' . ($date['user_id'] ?? 'N/A'));
            }
            
            // Добавляем информацию о непрочитанных сообщениях и определяем собеседника
            foreach ($myDateChats as &$date) {
                $date['unread_count'] = $this->messageModel->getUnreadCountForDate($date['id'], $userId);
                // Определяем ID собеседника в чате (того, с кем ведется переписка)
                $date['chat_participant_id'] = $this->messageModel->getDateChatParticipant($date['id'], $userId);
                // Если не нашли собеседника, используем владельца свидания (если текущий пользователь не владелец)
                // Или если текущий пользователь - владелец, пытаемся найти любого участника чата
                if (!$date['chat_participant_id']) {
                    if ($date['user_id'] != $userId) {
                        // Если текущий пользователь не владелец, используем владельца
                        $date['chat_participant_id'] = $date['user_id'];
                    } else {
                        // Если текущий пользователь - владелец, ищем любого участника чата
                        $allMessages = $this->messageModel->getDateChat($date['id']);
                        foreach ($allMessages as $msg) {
                            if ($msg['from_user_id'] != $userId) {
                                $date['chat_participant_id'] = $msg['from_user_id'];
                                break;
                            }
                            if ($msg['to_user_id'] != $userId) {
                                $date['chat_participant_id'] = $msg['to_user_id'];
                                break;
                            }
                        }
                    }
                }
            }
            
            // Убеждаемся, что это массив
            $myDateChats = is_array($myDateChats) ? $myDateChats : [];
        } else {
            error_log('Список date_ids пуст - чатов не найдено');
        }
        
        error_log('Итого чатов для отображения: ' . count($myDateChats));

        View::render('messages/date_chats_list', [
            'myDateChats' => $myDateChats,
            'currentUserId' => $userId,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Показывает список чатов мероприятий
     */
    public function eventChatsList()
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

        // Получаем мероприятия, в которых пользователь участвует в чате
        $eventIdsWithChat = $this->messageModel->getEventIdsWithChat($userId);
        
        $myEventChats = [];
        if (!empty($eventIdsWithChat)) {
            // Получаем мероприятия, которые еще существуют в БД
            $eventModel = new Event();
            $foundEvents = $eventModel->getByIds($eventIdsWithChat);
            
            $myEventChats = $foundEvents;
            
            // Добавляем информацию о непрочитанных сообщениях
            foreach ($myEventChats as &$event) {
                $event['unread_count'] = $this->messageModel->getUnreadCountForEvent($event['id'], $userId);
            }
            
            // Убеждаемся, что это массив
            $myEventChats = is_array($myEventChats) ? $myEventChats : [];
        }

        View::render('messages/event_chats_list', [
            'myEventChats' => $myEventChats,
            'currentUserId' => $userId,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Удаляет чат свидания для текущего пользователя
     */
    public function deleteDateChat()
    {
        if (!Helper::isLoggedIn()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Не авторизован']);
            return;
        }

        header('Content-Type: application/json');
        
        $dateId = isset($_POST['date_id']) ? (int)$_POST['date_id'] : 0;
        $userId = Helper::getUserId();

        // Логируем для отладки
        error_log('=== Попытка удалить чат ===');
        error_log('date_id: ' . $dateId);
        error_log('user_id: ' . $userId);
        error_log('POST data: ' . print_r($_POST, true));

        if ($dateId <= 0) {
            error_log('Ошибка: date_id <= 0');
            echo json_encode(['success' => false, 'error' => 'Не указан ID свидания']);
            return;
        }

        if ($userId <= 0) {
            error_log('Ошибка: user_id <= 0');
            echo json_encode(['success' => false, 'error' => 'Ошибка авторизации']);
            return;
        }

        // Удаляем чат напрямую без проверки существования
        // Если пользователь видит кнопку удаления, значит чат существует
        try {
            $result = $this->messageModel->deleteDateChatForUser($dateId, $userId);
            
            error_log('Результат deleteDateChatForUser: ' . var_export($result, true));
            
            if ($result === true) {
                error_log('✓ Чат успешно удален: date_id=' . $dateId . ', user_id=' . $userId);
                echo json_encode(['success' => true, 'message' => 'Чат успешно удален']);
            } else {
                error_log('✗ ОШИБКА: метод вернул false. date_id=' . $dateId . ', user_id=' . $userId);
                echo json_encode(['success' => false, 'error' => 'Не удалось удалить чат. Попробуйте еще раз или обратитесь к администратору.']);
            }
        } catch (Exception $e) {
            error_log('✗ ИСКЛЮЧЕНИЕ при удалении чата: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            echo json_encode(['success' => false, 'error' => 'Ошибка: ' . $e->getMessage()]);
        }
    }

    /**
     * Блокирует пользователя и удаляет чат свидания
     */
    public function blockAndDeleteDateChat()
    {
        if (!Helper::isLoggedIn()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Не авторизован']);
            return;
        }

        header('Content-Type: application/json');
        
        $dateId = isset($_POST['date_id']) ? (int)$_POST['date_id'] : 0;
        $blockedUserId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
        $userId = Helper::getUserId();

        // Логируем для отладки
        error_log('=== Попытка заблокировать и удалить чат свидания ===');
        error_log('date_id: ' . $dateId);
        error_log('blocked_user_id: ' . $blockedUserId);
        error_log('user_id: ' . $userId);
        error_log('POST data: ' . print_r($_POST, true));

        if ($dateId <= 0) {
            error_log('Ошибка: date_id <= 0');
            echo json_encode(['success' => false, 'error' => 'Не указан ID свидания']);
            return;
        }

        if ($blockedUserId <= 0) {
            error_log('Ошибка: blocked_user_id <= 0');
            echo json_encode(['success' => false, 'error' => 'Не указан ID пользователя']);
            return;
        }

        if ($userId <= 0) {
            error_log('Ошибка: user_id <= 0');
            echo json_encode(['success' => false, 'error' => 'Ошибка авторизации']);
            return;
        }

        if ($userId == $blockedUserId) {
            error_log('Ошибка: попытка заблокировать самого себя');
            echo json_encode(['success' => false, 'error' => 'Нельзя заблокировать самого себя']);
            return;
        }

        try {
            // Блокируем пользователя
            error_log('Блокируем пользователя: user_id=' . $userId . ', blocked_user_id=' . $blockedUserId);
            $blockResult = $this->blockedModel->block($userId, $blockedUserId);
            error_log('Результат блокировки: ' . var_export($blockResult, true));
            
            // Удаляем чат
            error_log('Удаляем чат: date_id=' . $dateId . ', user_id=' . $userId);
            $result = $this->messageModel->deleteDateChatForUser($dateId, $userId);
            error_log('Результат удаления чата: ' . var_export($result, true));
            
            if ($result === true) {
                error_log('✓ Успешно: пользователь заблокирован и чат удален');
                echo json_encode(['success' => true, 'message' => 'Пользователь заблокирован и чат удален']);
            } else {
                error_log('✗ ОШИБКА: не удалось удалить чат');
                echo json_encode(['success' => false, 'error' => 'Пользователь заблокирован, но не удалось удалить чат. Попробуйте еще раз.']);
            }
        } catch (Exception $e) {
            error_log('✗ ИСКЛЮЧЕНИЕ при блокировке и удалении чата свидания: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            echo json_encode(['success' => false, 'error' => 'Ошибка: ' . $e->getMessage()]);
        }
    }

    /**
     * Блокирует пользователя и удаляет чат мероприятия
     */
    public function blockAndDeleteEventChat()
    {
        if (!Helper::isLoggedIn()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Не авторизован']);
            return;
        }

        header('Content-Type: application/json');
        
        $eventId = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
        $blockedUserId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
        $userId = Helper::getUserId();

        // Логируем для отладки
        error_log('=== Попытка заблокировать и удалить чат мероприятия ===');
        error_log('event_id: ' . $eventId);
        error_log('blocked_user_id: ' . $blockedUserId);
        error_log('user_id: ' . $userId);
        error_log('POST data: ' . print_r($_POST, true));

        if ($eventId <= 0) {
            error_log('Ошибка: event_id <= 0');
            echo json_encode(['success' => false, 'error' => 'Не указан ID мероприятия']);
            return;
        }

        if ($blockedUserId <= 0) {
            error_log('Ошибка: blocked_user_id <= 0');
            echo json_encode(['success' => false, 'error' => 'Не указан ID пользователя']);
            return;
        }

        if ($userId <= 0) {
            error_log('Ошибка: user_id <= 0');
            echo json_encode(['success' => false, 'error' => 'Ошибка авторизации']);
            return;
        }

        if ($userId == $blockedUserId) {
            error_log('Ошибка: попытка заблокировать самого себя');
            echo json_encode(['success' => false, 'error' => 'Нельзя заблокировать самого себя']);
            return;
        }

        try {
            // Блокируем пользователя
            error_log('Блокируем пользователя: user_id=' . $userId . ', blocked_user_id=' . $blockedUserId);
            $blockResult = $this->blockedModel->block($userId, $blockedUserId);
            error_log('Результат блокировки: ' . var_export($blockResult, true));
            
            // Удаляем чат
            error_log('Удаляем чат: event_id=' . $eventId . ', user_id=' . $userId);
            $result = $this->messageModel->deleteEventChatForUser($eventId, $userId);
            error_log('Результат удаления чата: ' . var_export($result, true));
            
            if ($result === true) {
                error_log('✓ Успешно: пользователь заблокирован и чат удален');
                echo json_encode(['success' => true, 'message' => 'Пользователь заблокирован и чат удален']);
            } else {
                error_log('✗ ОШИБКА: не удалось удалить чат');
                echo json_encode(['success' => false, 'error' => 'Пользователь заблокирован, но не удалось удалить чат. Попробуйте еще раз.']);
            }
        } catch (Exception $e) {
            error_log('✗ ИСКЛЮЧЕНИЕ при блокировке и удалении чата мероприятия: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            echo json_encode(['success' => false, 'error' => 'Ошибка: ' . $e->getMessage()]);
        }
    }

    /**
     * Блокирует пользователя и удаляет диалог
     */
    public function blockAndDeleteConversation()
    {
        if (!Helper::isLoggedIn()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Не авторизован']);
            return;
        }

        header('Content-Type: application/json');
        
        $otherUserId = isset($_POST['other_user_id']) ? (int)$_POST['other_user_id'] : 0;
        $userId = Helper::getUserId();

        // Логируем для отладки
        error_log('=== Попытка заблокировать и удалить диалог ===');
        error_log('other_user_id: ' . $otherUserId);
        error_log('user_id: ' . $userId);
        error_log('POST data: ' . print_r($_POST, true));

        if ($otherUserId <= 0) {
            error_log('Ошибка: other_user_id <= 0');
            echo json_encode(['success' => false, 'error' => 'Не указан ID пользователя']);
            return;
        }

        if ($userId <= 0) {
            error_log('Ошибка: user_id <= 0');
            echo json_encode(['success' => false, 'error' => 'Ошибка авторизации']);
            return;
        }

        if ($userId == $otherUserId) {
            error_log('Ошибка: попытка заблокировать самого себя');
            echo json_encode(['success' => false, 'error' => 'Нельзя заблокировать самого себя']);
            return;
        }

        try {
            // Блокируем пользователя
            error_log('Блокируем пользователя: user_id=' . $userId . ', other_user_id=' . $otherUserId);
            $blockResult = $this->blockedModel->block($userId, $otherUserId);
            error_log('Результат блокировки: ' . var_export($blockResult, true));
            
            // Удаляем диалог
            error_log('Удаляем диалог: user_id=' . $userId . ', other_user_id=' . $otherUserId);
            $result = $this->messageModel->deleteConversation($userId, $otherUserId);
            error_log('Результат удаления диалога: ' . var_export($result, true));
            
            if ($result === true) {
                error_log('✓ Успешно: пользователь заблокирован и диалог удален');
                echo json_encode(['success' => true, 'message' => 'Пользователь заблокирован и диалог удален']);
            } else {
                error_log('✗ ОШИБКА: не удалось удалить диалог');
                echo json_encode(['success' => false, 'error' => 'Пользователь заблокирован, но не удалось удалить диалог. Попробуйте еще раз.']);
            }
        } catch (Exception $e) {
            error_log('✗ ИСКЛЮЧЕНИЕ при блокировке и удалении диалога: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            echo json_encode(['success' => false, 'error' => 'Ошибка: ' . $e->getMessage()]);
        }
    }
}

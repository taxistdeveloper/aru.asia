<?php

/**
 * КОНТРОЛЛЕР АДМИН-ПАНЕЛИ
 *
 * Управление всеми функциями сайта для администраторов
 */

class AdminController
{
    private $userModel;
    private $eventModel;
    private $dateModel;
    private $adModel;
    private $adminModel;
    private $feedbackModel;
    private $messageModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->eventModel = new Event();
        $this->dateModel = new Date();
        $this->adModel = new Ad();
        $this->adminModel = new Admin();
        $this->feedbackModel = new Feedback();
        $this->messageModel = new Message();
    }

    /**
     * Показывает форму входа в админ-панель
     */
    public function login()
    {
        // Если уже авторизован как администратор, перенаправляем в админку
        if (Helper::isAdminLoggedIn()) {
            Helper::redirect('admin');
            return;
        }

        // Если форма отправлена
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // Находим администратора
            $admin = $this->adminModel->findByEmail($email);

            // Проверяем пароль
            if ($admin && $this->adminModel->verifyPassword($password, $admin['password'])) {
                // Сохраняем данные в сессию
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_email'] = $admin['email'];

                Helper::redirect('admin');
            } else {
                $error = "Неверный email или пароль";
            }
        }

        View::render('admin/login', [
            'error' => $error ?? null,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Выход администратора
     */
    public function logout()
    {
        // Удаляем только админские данные из сессии
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_email']);
        Helper::redirect('admin/login');
    }

    /**
     * Смена пароля администратора
     */
    public function changePassword()
    {
        Helper::requireAdmin();

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $newPasswordConfirmation = $_POST['new_password_confirmation'] ?? '';

            if (empty($currentPassword) || empty($newPassword) || empty($newPasswordConfirmation)) {
                $errors[] = 'Заполните все поля';
            }

            if ($newPassword !== $newPasswordConfirmation) {
                $errors[] = 'Новый пароль и подтверждение не совпадают';
            }

            if (strlen($newPassword) < 8) {
                $errors[] = 'Новый пароль должен содержать минимум 8 символов';
            }

            $adminId = Helper::getAdminId();
            $admin = $adminId ? $this->adminModel->findById($adminId) : null;

            if (!$admin) {
                $errors[] = 'Администратор не найден';
            } else {
                if (empty($currentPassword) || !$this->adminModel->verifyPassword($currentPassword, $admin['password'])) {
                    $errors[] = 'Текущий пароль указан неверно';
                }
            }

            if (empty($errors) && $admin) {
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                if ($this->adminModel->updatePassword($admin['id'], $newHash)) {
                    $_SESSION['success_message'] = 'Пароль успешно изменён';
                    Helper::redirect('admin/change-password');
                    return;
                } else {
                    $errors[] = 'Ошибка при сохранении нового пароля';
                }
            }
        }

        View::render('admin/change_password', [
            'errors' => $errors,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Главная страница админки (Dashboard)
     */
    public function index()
    {
        // Проверяем права администратора
        Helper::requireAdmin();

        try {
            $db = Database::getInstance()->getConnection();
            $adminStats = new AdminStats();
            $stats = $adminStats->getSummary();

            // Функция для безопасного получения списка
            $safeFetchAll = function ($query, $default = []) use ($db) {
                try {
                    $stmt = $db->query($query);
                    if ($stmt === false) {
                        return $default;
                    }
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    return $result !== false ? $result : $default;
                } catch (Exception $e) {
                    error_log("AdminController::index - FetchAll error: " . $e->getMessage());
                    return $default;
                }
            };

            // Последние пользователи
            $recent_users = $safeFetchAll("SELECT id, email, gender, age, city, email_verified, role, created_at
                                           FROM users
                                           ORDER BY created_at DESC
                                           LIMIT 5", []);

            // Последние мероприятия
            $recent_events = $safeFetchAll("SELECT e.*, u.email as user_email
                                            FROM events e
                                            LEFT JOIN users u ON e.user_id = u.id
                                            ORDER BY e.created_at DESC
                                            LIMIT 5", []);

            // Последние свидания
            $recent_dates = $safeFetchAll("SELECT d.*, u.email as user_email
                                           FROM dates d
                                           LEFT JOIN users u ON d.user_id = u.id
                                           ORDER BY d.created_at DESC
                                           LIMIT 5", []);

            // Ожидающие модерации рекламы
            $pending_ads = $safeFetchAll("SELECT * FROM ads WHERE status = 'pending' ORDER BY created_at DESC LIMIT 5", []);

            View::render('admin/index', [
                'stats' => $stats,
                'recent_users' => $recent_users,
                'recent_events' => $recent_events,
                'recent_dates' => $recent_dates,
                'pending_ads' => $pending_ads,
                'isMobile' => View::isMobile()
            ]);
        } catch (Exception $e) {
            error_log("AdminController::index - Fatal error: " . $e->getMessage());
            die("Ошибка при загрузке админ-панели. Проверьте логи сервера.");
        }
    }

    /**
     * Страница статистики
     */
    public function stats()
    {
        Helper::requireAdmin();

        try {
            $adminStats = new AdminStats();
            $stats = $adminStats->getSummary();
            $daily_visits = $adminStats->getDailyVisits(30);

            View::render('admin/stats', [
                'stats' => $stats,
                'daily_visits' => $daily_visits,
                'isMobile' => View::isMobile()
            ]);
        } catch (Exception $e) {
            error_log("AdminController::stats - Fatal error: " . $e->getMessage());
            die("Ошибка при загрузке статистики. Проверьте логи сервера.");
        }
    }

    /**
     * Логи действий пользователей
     */
    public function activityLogs()
    {
        Helper::requireAdmin();

        $query = trim($_GET['q'] ?? '');
        $method = trim($_GET['method'] ?? '');
        $userId = (int)($_GET['user_id'] ?? 0);

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $filters = [
            'query' => $query,
            'method' => $method,
            'user_id' => $userId ?: null
        ];

        $logModel = new UserActivityLog();
        $total = $logModel->getCount($filters);
        $totalPages = max(1, (int)ceil($total / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $perPage;
        }

        $logs = $logModel->getLogs($perPage, $offset, $filters);

        View::render('admin/activity_logs', [
            'logs' => $logs,
            'query' => $query,
            'method' => $method,
            'userId' => $userId,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ]);
    }

    /**
     * Управление пользователями
     */
    public function users()
    {
        // Проверяем права администратора
        Helper::requireAdmin();

        $db = Database::getInstance()->getConnection();
        $sql = "SELECT u.*,
                (SELECT photo FROM user_photos WHERE user_id = u.id AND photo IS NOT NULL AND TRIM(photo) <> '' ORDER BY created_at ASC LIMIT 1) as main_photo
                FROM users u
                ORDER BY u.created_at DESC
                LIMIT 100";
        $users = $db->query($sql)->fetchAll();

        View::render('admin/users', [
            'users' => $users,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Обновляет роль пользователя
     */
    public function updateUserRole()
    {
        // Проверяем права администратора
        Helper::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect('admin/users');
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

        Helper::redirect('admin/users');
    }

    /**
     * Удаляет пользователя полностью
     */
    public function deleteUser()
    {
        // Проверяем права администратора
        Helper::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect('admin/users');
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

        Helper::redirect('admin/users');
    }

    /**
     * Управление рекламой
     */
    public function ads()
    {
        // Проверяем права администратора
        Helper::requireAdmin();

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
        Helper::requireAdmin();

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

        Helper::redirect('admin/ads');
    }

    /**
     * Отклоняет рекламу
     */
    public function rejectAd()
    {
        Helper::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $adId = $_POST['ad_id'] ?? 0;
            $rejectionReason = trim($_POST['rejection_reason'] ?? '');

            if ($adId) {
                if (empty($rejectionReason)) {
                    $_SESSION['error_message'] = 'Укажите причину отказа';
                    Helper::redirect('admin/ads');
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

        Helper::redirect('admin/ads');
    }

    /**
     * Отправляет email рекламодателю о отклонении рекламы
     */
    private function sendAdRejectionEmail($ad, $reason)
    {
        $emailService = new EmailService();
        $advertiserEmail = $ad['advertiser_email'];

        if (empty($advertiserEmail) || !filter_var($advertiserEmail, FILTER_VALIDATE_EMAIL)) {
            error_log("AdminController: Invalid email for ad rejection: $advertiserEmail");
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
            error_log("AdminController: Ad rejection email sent to $advertiserEmail for ad #{$ad['id']}");
            return true;
        } else {
            error_log("AdminController: Failed to send ad rejection email to $advertiserEmail for ad #{$ad['id']}");
            return false;
        }
    }

    /**
     * Отправляет уведомление в личный кабинет рекламодателю о отклонении рекламы
     */
    private function sendAdRejectionNotification($ad, $reason)
    {
        $advertiserEmail = $ad['advertiser_email'];

        if (empty($advertiserEmail)) {
            error_log("AdminController: No email for ad rejection notification: ad #{$ad['id']}");
            return false;
        }

        // Находим пользователя по email
        $user = $this->userModel->findByEmail($advertiserEmail);
        if (!$user || empty($user['id'])) {
            error_log("AdminController: User not found for email $advertiserEmail for ad #{$ad['id']}");
            return false;
        }

        $userId = $user['id'];
        $adminId = Helper::getAdminId();

        if (!$adminId) {
            error_log("AdminController: Admin ID not found for ad rejection notification");
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
        if ($this->messageModel->send($adminId, $userId, $message)) {
            // Отправляем push-уведомление
            $pushService = new PushNotificationService();
            $pushService->sendAdminNotification($userId, 'Реклама отклонена', 'Ваша реклама была отклонена. Причина указана в сообщении.');

            error_log("AdminController: Ad rejection notification sent to user #$userId for ad #{$ad['id']}");
            return true;
        } else {
            error_log("AdminController: Failed to send ad rejection notification to user #$userId for ad #{$ad['id']}");
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
            error_log("AdminController: No email for ad approval notification: ad #{$ad['id']}");
            return false;
        }

        // Находим пользователя по email
        $user = $this->userModel->findByEmail($advertiserEmail);
        if (!$user || empty($user['id'])) {
            error_log("AdminController: User not found for email $advertiserEmail for ad #{$ad['id']}");
            return false;
        }

        $userId = (int)$user['id'];
        $adminId = Helper::getAdminId();
        if (!$adminId) {
            error_log("AdminController: Admin ID not found for ad approval notification");
            return false;
        }

        $message = "Ваша реклама одобрена и активирована.\n\n";
        $message .= "Рекламодатель: " . ($ad['advertiser_name'] ?? '-') . "\n";
        $message .= "Период показа: " . date('d.m.Y', strtotime($ad['start_date'])) . " - " . date('d.m.Y', strtotime($ad['end_date'])) . "\n";
        $message .= "Страна: " . ($ad['country'] ?? '-') . "\n";
        $message .= "Город: " . (!empty($ad['city']) ? $ad['city'] : '—') . "\n\n";
        $message .= "Спасибо! Ваша реклама будет показана пользователям согласно указанным параметрам.";

        // Сообщение в личный кабинет
        if ($this->messageModel->send($adminId, $userId, $message)) {
            // Push
            $pushService = new PushNotificationService();
            $pushService->sendAdminNotification($userId, 'Реклама одобрена', 'Ваша реклама одобрена и активирована. Детали в сообщении.');
            error_log("AdminController: Ad approval notification sent to user #$userId for ad #{$ad['id']}");
            return true;
        }

        error_log("AdminController: Failed to send ad approval notification to user #$userId for ad #{$ad['id']}");
        return false;
    }

    /**
     * Удаляет рекламу и отправляет уведомление рекламодателю
     */
    public function deleteAd()
    {
        Helper::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $adId = $_POST['ad_id'] ?? 0;

            if ($adId) {
                $ad = $this->adModel->findById($adId);
                if ($ad && $this->adModel->delete($adId)) {
                    $this->sendAdDeletedNotification($ad);
                    $_SESSION['success_message'] = 'Реклама успешно удалена';
                } else {
                    $_SESSION['error_message'] = 'Ошибка при удалении рекламы';
                }
            } else {
                $_SESSION['error_message'] = 'Не указан ID рекламы';
            }
        }

        Helper::redirect('admin/ads');
    }

    /**
     * Отправляет уведомление рекламодателю об удалении рекламы (сообщение в личный кабинет + push)
     */
    private function sendAdDeletedNotification($ad)
    {
        $advertiserEmail = $ad['advertiser_email'] ?? '';
        if (empty($advertiserEmail)) {
            return;
        }

        $user = $this->userModel->findByEmail($advertiserEmail);
        if (!$user || empty($user['id'])) {
            return;
        }

        $userId = (int)$user['id'];
        $adminId = Helper::getAdminId();
        if (!$adminId) {
            return;
        }

        $message = "Ваша реклама удалена администратором.\n\n";
        $message .= "Рекламодатель: " . ($ad['advertiser_name'] ?? '-') . "\n";
        $message .= "Период показа был: " . date('d.m.Y', strtotime($ad['start_date'])) . " - " . date('d.m.Y', strtotime($ad['end_date'])) . "\n";

        $this->messageModel->send($adminId, $userId, $message);

        $pushService = new PushNotificationService();
        $pushService->sendAdminNotification($userId, 'Ваша реклама удалена', 'Ваша реклама была удалена администратором. Подробности в сообщениях.');
    }

    /**
     * Управление мероприятиями (модерация)
     */
    public function events()
    {
        Helper::requireAdmin();

        $pendingEvents = $this->eventModel->getPending();

        View::render('admin/events', [
            'pendingEvents' => $pendingEvents,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Одобряет мероприятие
     */
    public function approveEvent()
    {
        Helper::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $eventId = $_POST['event_id'] ?? 0;
            $adminId = Helper::getAdminId();

            if ($eventId && $adminId) {
                $event = $this->eventModel->getById($eventId);
                if ($event && $this->eventModel->approve($eventId, $adminId)) {
                    // Отправляем уведомление пользователю
                    $this->sendEventNotification($event['user_id'], $eventId, 'approved');
                    $_SESSION['success_message'] = 'Мероприятие одобрено';
                } else {
                    $_SESSION['error_message'] = 'Ошибка при одобрении мероприятия';
                }
            }
        }

        Helper::redirect('admin/events');
    }

    /**
     * Отклоняет мероприятие
     */
    public function rejectEvent()
    {
        Helper::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $eventId = $_POST['event_id'] ?? 0;
            $reason = $_POST['rejection_reason'] ?? '';
            $adminId = Helper::getAdminId();

            if ($eventId && $adminId && !empty($reason)) {
                $event = $this->eventModel->getById($eventId);
                if ($event && $this->eventModel->reject($eventId, $adminId, $reason)) {
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

        Helper::redirect('admin/events');
    }

    /**
     * Список всех мероприятий (одобренные, на модерации, отклонённые) — для удаления и просмотра
     */
    public function allEvents()
    {
        Helper::requireAdmin();

        $db = Database::getInstance()->getConnection();
        $totalEvents = (int)$db->query("SELECT COUNT(*) FROM events")->fetchColumn();
        $perPage = 8;
        $totalPages = $totalEvents > 0 ? ceil($totalEvents / $perPage) : 1;
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

        View::render('admin/all_events', [
            'events' => $events,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalEvents' => $totalEvents,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Удаление мероприятия администратором (после одобрения или в любой момент)
     */
    public function deleteEvent()
    {
        Helper::requireAdmin();

        $redirectTo = (isset($_POST['from']) && $_POST['from'] === 'moderation') ? 'admin/events' : 'admin/events/all';

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
     * Список всех свиданий — для просмотра и удаления
     */
    public function allDates()
    {
        Helper::requireAdmin();

        $db = Database::getInstance()->getConnection();
        $totalDates = (int)$db->query("SELECT COUNT(*) FROM dates")->fetchColumn();
        $perPage = 8;
        $totalPages = $totalDates > 0 ? ceil($totalDates / $perPage) : 1;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        if ($page > $totalPages && $totalPages > 0) $page = $totalPages;
        $offset = ($page - 1) * $perPage;

        $dates = $db->query("
            SELECT d.id, d.user_id, d.title, d.category_id, d.date_time, d.location, d.created_at,
                   u.email as user_email, u.full_name,
                   dc.name as category_name
            FROM dates d
            LEFT JOIN users u ON d.user_id = u.id
            LEFT JOIN date_categories dc ON d.category_id = dc.id
            ORDER BY d.created_at DESC
            LIMIT $perPage OFFSET $offset
        ")->fetchAll();

        View::render('admin/all_dates', [
            'dates' => $dates,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalDates' => $totalDates,
            'isMobile' => View::isMobile()
        ]);
    }

    /**
     * Удаление свидания администратором
     */
    public function deleteDate()
    {
        Helper::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect('admin/dates/all');
            return;
        }

        $dateId = (int)($_POST['date_id'] ?? 0);
        if (!$dateId) {
            $_SESSION['error_message'] = 'Не указано свидание';
            Helper::redirect('admin/dates/all');
            return;
        }

        $date = $this->dateModel->getById($dateId);
        if (!$date) {
            $_SESSION['error_message'] = 'Свидание не найдено';
            Helper::redirect('admin/dates/all');
            return;
        }

        if ($this->dateModel->deleteById($dateId)) {
            $_SESSION['success_message'] = 'Свидание удалено';
        } else {
            $_SESSION['error_message'] = 'Не удалось удалить свидание';
        }

        Helper::redirect('admin/dates/all');
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

        // Отправляем сообщение от админа БЕЗ привязки к event_id,
        // чтобы оно отображалось в "Уведомлениях от администратора"
        $adminId = Helper::getAdminId();
        $messageModel->send($adminId, $userId, $message, null, null);

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
     * Управление обратной связью от пользователей
     */
    public function feedback()
    {
        Helper::requireAdmin();

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

        View::render('admin/feedback', [
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
        Helper::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect('admin/feedback');
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
                            $adminId = Helper::getAdminId();

                            // Формируем текст сообщения для пользователя
                            $messageText = "Ответ на ваше обращение #{$feedbackId}\n\n";
                            $messageText .= "Тема: " . $feedback['subject'] . "\n\n";
                            $messageText .= $adminReply;

                            // Если пользователь авторизован, отправляем сообщение в аккаунт
                            if ($recipientUserId && $adminId) {
                                try {
                                    // Сохраняем сообщение в аккаунт пользователя
                                    if ($this->messageModel->send($adminId, $recipientUserId, $messageText)) {
                                        // Отправляем push-уведомление
                                        $pushService = new PushNotificationService();
                                        $pushService->sendAdminNotification(
                                            $recipientUserId,
                                            'Ответ на ваше обращение',
                                            'Ответ на обращение: ' . mb_substr($feedback['subject'], 0, 50)
                                        );
                                        $successMessage .= '. Ответ отправлен в аккаунт пользователя';
                                        error_log("AdminController: Feedback reply sent to user account #$recipientUserId for feedback #$feedbackId");
                                    } else {
                                        error_log("AdminController: Failed to send feedback reply to user account #$recipientUserId for feedback #$feedbackId");
                                    }
                                } catch (Exception $e) {
                                    error_log("AdminController: Exception while sending feedback reply to user account: " . $e->getMessage());
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
                                        error_log("AdminController: Feedback reply sent to $recipientEmail for feedback #$feedbackId");
                                    } else {
                                        if (empty($recipientUserId)) {
                                            $_SESSION['error_message'] = 'Статус обновлен, но не удалось отправить ответ на email';
                                        }
                                        error_log("AdminController: Failed to send feedback reply to $recipientEmail for feedback #$feedbackId");
                                    }
                                } catch (Exception $e) {
                                    error_log("AdminController: Exception while sending feedback reply email: " . $e->getMessage());
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

        Helper::redirect('admin/feedback');
    }

    /**
     * Удаляет заявку обратной связи
     */
    public function deleteFeedback()
    {
        Helper::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect('admin/feedback');
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

        Helper::redirect('admin/feedback');
    }

    /**
     * Страница отправки сообщений пользователям
     */
    public function sendMessage()
    {
        Helper::requireAdmin();

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
        Helper::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect('admin/send-message');
            return;
        }

        $adminId = Helper::getAdminId();
        $recipientType = $_POST['recipient_type'] ?? 'single';
        $toUserId = $_POST['to_user_id'] ?? null;
        $message = trim($_POST['message'] ?? '');

        if (!$adminId || empty($message)) {
            $_SESSION['error_message'] = 'Заполните все поля';
            Helper::redirect('admin/send-message');
            return;
        }

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
                if ($this->messageModel->send($adminId, $user['id'], $message)) {
                    // Отправляем push-уведомление
                    $pushService->sendAdminNotification($user['id'], 'Сообщение от администратора', $message);
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
                Helper::redirect('admin/send-message');
                return;
            }

            // Проверяем существование пользователя
            $user = $this->userModel->findById($toUserId);
            if (!$user) {
                $_SESSION['error_message'] = 'Пользователь не найден';
                Helper::redirect('admin/send-message');
                return;
            }

            // Отправляем сообщение
            if ($this->messageModel->send($adminId, $toUserId, $message)) {
                // Отправляем push-уведомление
                $pushService->sendAdminNotification($toUserId, 'Сообщение от администратора', $message);

                $_SESSION['success_message'] = 'Сообщение успешно отправлено пользователю';
            } else {
                $_SESSION['error_message'] = 'Ошибка при отправке сообщения';
            }
        }

        Helper::redirect('admin/send-message');
    }

    /**
     * Устанавливает замечание для профиля пользователя и блокирует его
     */
    public function setUserRemark()
    {
        Helper::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect('admin/users');
            return;
        }

        $userId = $_POST['user_id'] ?? null;
        $remark = trim($_POST['remark'] ?? '');
        $remarkType = $_POST['remark_type'] ?? null;

        if (!$userId) {
            $_SESSION['error_message'] = 'Неверные данные';
            Helper::redirect('admin/users');
            return;
        }

        if (empty($remark)) {
            $_SESSION['error_message'] = 'Введите замечание';
            Helper::redirect('admin/users');
            return;
        }

        if (empty($remarkType)) {
            $_SESSION['error_message'] = 'Выберите тип замечания';
            Helper::redirect('admin/users');
            return;
        }

        // Проверяем, что пользователь существует
        $user = $this->userModel->findById($userId);
        if (!$user) {
            $_SESSION['error_message'] = 'Пользователь не найден';
            Helper::redirect('admin/users');
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
            $message = "Ваш профиль заблокирован. Администратор оставил замечание по полю \"{$fieldName}\":\n\n{$remark}\n\nПожалуйста, исправьте указанные ошибки в профиле.";
            $pushService->sendAdminNotification($userId, 'Профиль заблокирован', $message);
        } else {
            $_SESSION['error_message'] = 'Ошибка при установке замечания';
        }

        Helper::redirect('admin/users');
    }

    /**
     * Снимает блокировку с профиля пользователя (разблокирует профиль)
     */
    public function unblockUser()
    {
        Helper::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect('admin/users');
            return;
        }

        $userId = $_POST['user_id'] ?? null;

        if (!$userId) {
            $_SESSION['error_message'] = 'Неверные данные';
            Helper::redirect('admin/users');
            return;
        }

        // Проверяем, что пользователь существует
        $user = $this->userModel->findById($userId);
        if (!$user) {
            $_SESSION['error_message'] = 'Пользователь не найден';
            Helper::redirect('admin/users');
            return;
        }

        // Снимаем блокировку
        if ($this->userModel->clearAdminRemark($userId)) {
            $_SESSION['success_message'] = 'Профиль разблокирован';
        } else {
            $_SESSION['error_message'] = 'Ошибка при разблокировке профиля';
        }

        Helper::redirect('admin/users');
    }
}

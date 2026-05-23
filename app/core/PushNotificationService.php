<?php

/**
 * СЕРВИС ОТПРАВКИ PUSH-УВЕДОМЛЕНИЙ
 *
 * Отправляет push-уведомления через Web Push API
 */

class PushNotificationService
{
    private $pushModel;
    private $userModel;

    public function __construct()
    {
        $this->pushModel = new PushNotification();
        $this->userModel = new User();
    }

    /**
     * Отправляет уведомление пользователю
     */
    public function sendNotification($userId, $title, $body, $data = [], $type = 'message')
    {
        $tokens = $this->pushModel->getUserTokens($userId);

        if (empty($tokens)) {
            return false;
        }

        $successCount = 0;
        $failedTokens = [];

        foreach ($tokens as $tokenData) {
            $token = $tokenData['token'];

            // Подготавливаем данные для уведомления
            // Используем notification_tag из данных, если он есть (для группировки сообщений)
            $tag = $data['notification_tag'] ?? ($type . '_' . ($data['message_id'] ?? time()));

            $notificationData = array_merge([
                'title' => $title,
                'body' => $body,
                'icon' => BASE_URL . 'assets/images/icon-192x192.png', // Можно добавить иконку
                'badge' => BASE_URL . 'assets/images/badge-72x72.png',
                'tag' => $tag,
                'data' => array_merge($data, [
                    'type' => $type,
                    'url' => $this->getNotificationUrl($type, $data)
                ])
            ], $data);

            // Отправляем уведомление через Web Push API
            if ($this->sendWebPush($token, $notificationData)) {
                $successCount++;
            } else {
                $failedTokens[] = $token;
            }
        }

        // Логируем отправку
        $this->pushModel->logNotification(
            $userId,
            $type,
            $title,
            $body,
            $data,
            $successCount > 0 ? 'sent' : 'failed'
        );

        // Удаляем невалидные токены
        foreach ($failedTokens as $failedToken) {
            $this->pushModel->unregisterToken($userId, $failedToken);
        }

        return $successCount > 0;
    }

    /**
     * Отправляет уведомление через Web Push API
     *
     * ВАЖНО: Для работы push-уведомлений в продакшене необходимо:
     * 1. Установить библиотеку для Web Push (например, minishlink/web-push через Composer)
     * 2. Сгенерировать VAPID ключи (можно использовать https://web-push-codelab.glitch.me/)
     * 3. Настроить VAPID ключи в конфигурации
     *
     * Пример установки библиотеки:
     * composer require minishlink/web-push
     *
     * Пример использования:
     * $webPush = new \Minishlink\WebPush\WebPush($auth);
     * $webPush->queueNotification($subscription, json_encode($data));
     * $webPush->flush();
     */
    private function sendWebPush($token, $data)
    {
        // Парсим токен (subscription object в формате JSON)
        $subscription = json_decode($token, true);
        if (!$subscription || !isset($subscription['endpoint'])) {
            return false;
        }

        // Подготавливаем данные для отправки
        $payload = json_encode([
            'title' => $data['title'],
            'body' => $data['body'],
            'icon' => $data['icon'] ?? null,
            'badge' => $data['badge'] ?? null,
            'tag' => $data['tag'] ?? null,
            'data' => $data['data'] ?? []
        ]);

        // ВАЖНО: Для реальной отправки нужно использовать библиотеку Web Push
        // Например: minishlink/web-push
        //
        // Здесь упрощенная версия, которая логирует намерение отправить уведомление
        // В продакшене замените на реальную отправку через Web Push библиотеку

        // Пример кода для реальной отправки (требует установки minishlink/web-push):
        /*
        use Minishlink\WebPush\WebPush;
        use Minishlink\WebPush\Subscription;

        $auth = [
            'VAPID' => [
                'subject' => 'mailto:admin@tanisu-app.com',
                'publicKey' => 'YOUR_PUBLIC_VAPID_KEY',
                'privateKey' => 'YOUR_PRIVATE_VAPID_KEY',
            ],
        ];

        $webPush = new WebPush($auth);
        $subscription = Subscription::create($subscription);
        $result = $webPush->sendOneNotification($subscription, $payload);
        $webPush->flush();

        return $result->isSuccess();
        */

        // Временная заглушка - всегда возвращаем true
        // В реальной реализации здесь должна быть отправка через Web Push
        return true;
    }

    /**
     * Отправляет уведомление о новом сообщении в чате
     */
    public function sendMessageNotification($toUserId, $fromUserId, $message, $dateId = null, $eventId = null)
    {
        $fromUser = $this->userModel->findById($fromUserId);
        $fromName = $fromUser['full_name'] ?? $fromUser['email'] ?? 'Пользователь';

        // Определяем тип уведомления и заголовок
        if ($dateId) {
            $title = 'Новое сообщение в свидании';
            $body = $fromName . ': ' . mb_substr($message, 0, 100);
            $type = 'date_message';
            $data = ['date_id' => $dateId, 'from_user_id' => $fromUserId];
            // Используем одинаковый tag для всех сообщений от одного отправителя в одном свидании
            $data['notification_tag'] = 'date_message_' . $dateId . '_' . $fromUserId;
        } elseif ($eventId) {
            $title = 'Новое сообщение в мероприятии';
            $body = $fromName . ': ' . mb_substr($message, 0, 100);
            $type = 'event_message';
            $data = ['event_id' => $eventId, 'from_user_id' => $fromUserId];
            // Используем одинаковый tag для всех сообщений от одного отправителя в одном мероприятии
            $data['notification_tag'] = 'event_message_' . $eventId . '_' . $fromUserId;
        } else {
            $title = 'Новое сообщение';
            $body = $fromName . ': ' . mb_substr($message, 0, 100);
            $type = 'message';
            $data = ['from_user_id' => $fromUserId];
            // Используем одинаковый tag для всех сообщений от одного отправителя
            $data['notification_tag'] = 'message_' . $fromUserId;
        }

        return $this->sendNotification($toUserId, $title, $body, $data, $type);
    }

    /**
     * Отправляет уведомление от админа/модератора
     */
    public function sendAdminNotification($toUserId, $title, $message, $eventId = null)
    {
        $data = ['from_admin' => true];
        if ($eventId) {
            $data['event_id'] = $eventId;
        }

        return $this->sendNotification($toUserId, $title, $message, $data, 'admin_message');
    }

    /**
     * Отправляет уведомления о новом свидании пользователям противоположного пола в радиусе
     */
    public function sendNewDateNotification($dateId, $dateTitle, $dateLocation, $dateLat, $dateLon, $creatorGender, $radius = 50)
    {
        // Определяем противоположный пол
        $oppositeGender = $creatorGender === 'male' ? 'female' : 'male';

        // Получаем всех пользователей противоположного пола в радиусе
        $users = $this->getUsersInRadius($dateLat, $dateLon, $oppositeGender, $radius);

        $successCount = 0;

        foreach ($users as $user) {
            $title = 'Новое свидание рядом с вами';
            $body = $dateTitle . ' - ' . $dateLocation;

            $data = [
                'date_id' => $dateId,
                'latitude' => $dateLat,
                'longitude' => $dateLon
            ];

            if ($this->sendNotification($user['id'], $title, $body, $data, 'new_date')) {
                $successCount++;
            }
        }

        return $successCount;
    }

    /**
     * Отправляет уведомления о новом одобренном мероприятии пользователям в радиусе
     */
    public function sendNewEventNotification($eventId, $eventTitle, $eventLocation, $eventLat, $eventLon, $radius = 50)
    {
        // Получаем всех пользователей в радиусе (независимо от пола)
        $users = $this->getUsersInRadius($eventLat, $eventLon, null, $radius);

        $successCount = 0;

        foreach ($users as $user) {
            $title = 'Новое мероприятие рядом с вами';
            $body = $eventTitle . ' - ' . $eventLocation;

            $data = [
                'event_id' => $eventId,
                'latitude' => $eventLat,
                'longitude' => $eventLon
            ];

            if ($this->sendNotification($user['id'], $title, $body, $data, 'new_event')) {
                $successCount++;
            }
        }

        return $successCount;
    }

    /**
     * Получает пользователей в радиусе (для отправки уведомлений)
     */
    private function getUsersInRadius($lat, $lon, $gender = null, $radius = 50)
    {
        $db = Database::getInstance()->getConnection();

        $sql = "SELECT u.id, u.email, u.latitude, u.longitude,
                (6371 * acos(cos(radians(:lat1)) * cos(radians(u.latitude)) *
                cos(radians(u.longitude) - radians(:lon1)) +
                sin(radians(:lat2)) * sin(radians(u.latitude)))) AS distance
                FROM users u
                WHERE u.latitude IS NOT NULL
                AND u.longitude IS NOT NULL";

        if ($gender !== null) {
            $sql .= " AND u.gender = :gender";
        }

        $sql .= " HAVING distance <= :radius
                ORDER BY distance";

        $stmt = $db->prepare($sql);

        $params = [
            ':lat1' => $lat,
            ':lat2' => $lat,
            ':lon1' => $lon,
            ':radius' => $radius
        ];

        if ($gender !== null) {
            $params[':gender'] = $gender;
        }

        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Получает URL для перехода при клике на уведомление
     */
    private function getNotificationUrl($type, $data)
    {
        switch ($type) {
            case 'date_message':
                return BASE_URL . 'messages/date?date_id=' . ($data['date_id'] ?? '');
            case 'event_message':
                return BASE_URL . 'messages/event?event_id=' . ($data['event_id'] ?? '');
            case 'new_date':
                return BASE_URL . 'dates';
            case 'new_event':
                return BASE_URL . 'events';
            case 'admin_message':
                if (isset($data['event_id'])) {
                    return BASE_URL . 'events';
                }
                return BASE_URL . 'messages';
            case 'message':
            default:
                if (isset($data['from_user_id'])) {
                    return BASE_URL . 'messages?user_id=' . $data['from_user_id'];
                }
                return BASE_URL . 'messages';
        }
    }
}


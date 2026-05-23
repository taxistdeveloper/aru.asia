<?php

/**
 * МОДЕЛЬ СООБЩЕНИЙ
 *
 * Работает с таблицей messages - хранит переписку между пользователями
 */

class Message
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Отправляет сообщение
     */
    public function send($fromUserId, $toUserId, $message, $dateId = null, $eventId = null)
    {
        $sql = "INSERT INTO messages (from_user_id, to_user_id, date_id, event_id, message, created_at)
                VALUES (:from_user_id, :to_user_id, :date_id, :event_id, :message, NOW())";
        $stmt = $this->db->prepare($sql);
        
        // Логируем для отладки
        error_log('=== Message::send ===');
        error_log('from_user_id: ' . $fromUserId);
        error_log('to_user_id: ' . $toUserId);
        error_log('date_id: ' . ($dateId ?? 'NULL'));
        error_log('event_id: ' . ($eventId ?? 'NULL'));
        error_log('message: ' . substr($message, 0, 50) . '...');
        
        $result = $stmt->execute([
            ':from_user_id' => $fromUserId,
            ':to_user_id' => $toUserId,
            ':date_id' => $dateId,
            ':event_id' => $eventId,
            ':message' => $message
        ]);
        
        if ($result) {
            $messageId = $this->db->lastInsertId();
            error_log('Сообщение успешно сохранено с ID: ' . $messageId);
        } else {
            $errorInfo = $stmt->errorInfo();
            error_log('Ошибка сохранения сообщения: ' . print_r($errorInfo, true));
        }
        
        return $result;
    }

    /**
     * Получает переписку между двумя пользователями
     */
    public function getConversation($userId1, $userId2)
    {
        // Используем разные имена параметров для каждого использования
        // Используем LEFT JOIN для админов, так как они могут не быть в таблице users
        $sql = "SELECT m.*,
                u1.email as from_email,
                u1.full_name as from_full_name,
                u1.role as from_role,
                CASE WHEN a1.id IS NOT NULL THEN 1 ELSE 0 END as from_is_admin,
                u2.email as to_email,
                u2.full_name as to_full_name,
                u2.role as to_role
                FROM messages m
                LEFT JOIN users u1 ON m.from_user_id = u1.id
                LEFT JOIN admins a1 ON m.from_user_id = a1.id
                LEFT JOIN users u2 ON m.to_user_id = u2.id
                WHERE (m.from_user_id = :user1 AND m.to_user_id = :user2)
                OR (m.from_user_id = :user2_dup AND m.to_user_id = :user1_dup)
                ORDER BY m.created_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user1' => $userId1,
            ':user2' => $userId2,
            ':user2_dup' => $userId2,
            ':user1_dup' => $userId1
        ]);
        $messages = $stmt->fetchAll();

        // Обрабатываем сообщения от админов, которые могут не быть в таблице users
        foreach ($messages as &$msg) {
            if ($msg['from_is_admin'] && empty($msg['from_email'])) {
                // Если это админ, получаем его данные из таблицы admins
                $adminSql = "SELECT email FROM admins WHERE id = :id";
                $adminStmt = $this->db->prepare($adminSql);
                $adminStmt->execute([':id' => $msg['from_user_id']]);
                $admin = $adminStmt->fetch();
                if ($admin) {
                    $msg['from_email'] = $admin['email'];
                    $msg['from_full_name'] = 'Администратор';
                }
            }
        }

        return $messages;
    }

    /**
     * Получает список диалогов пользователя
     * Показывает диалоги, где есть хотя бы одно сообщение (входящее или исходящее) (обычный чат)
     */
    public function getConversations($userId)
    {
        // Получаем всех собеседников с последним временем сообщения
        // Показываем диалоги, где есть хотя бы одно сообщение в любом направлении
        // Исключаем заблокированных пользователей (когда текущий пользователь заблокировал другого или другой заблокировал текущего)
        // Исключаем сообщения от менеджеров (они показываются в "Уведомлениях от администратора")
        // Только обычный чат (без date_id и event_id)
        // Используем UNION для объединения входящих и исходящих сообщений
        $sql = "SELECT
                other_user_id,
                MAX(last_time) as last_time
                FROM (
                    -- Входящие сообщения (когда другой пользователь отправил текущему)
                    SELECT
                        m.from_user_id as other_user_id,
                        m.created_at as last_time
                    FROM messages m
                    WHERE m.to_user_id = :user_id1
                    AND m.from_user_id != :user_id2
                    AND m.date_id IS NULL
                    AND m.event_id IS NULL
                    AND NOT EXISTS (
                        SELECT 1 FROM blocked_users bu1
                        WHERE bu1.user_id = :user_id3
                        AND bu1.blocked_user_id = m.from_user_id
                    )
                    AND NOT EXISTS (
                        SELECT 1 FROM blocked_users bu2
                        WHERE bu2.blocked_user_id = :user_id4
                        AND bu2.user_id = m.from_user_id
                    )
                    -- Исключаем сообщения от менеджеров (показываются в уведомлениях от администратора)
                    AND NOT EXISTS (
                        SELECT 1 FROM users mgr
                        WHERE mgr.id = m.from_user_id AND mgr.role = 'manager'
                    )
                    
                    UNION
                    
                    -- Исходящие сообщения (когда текущий пользователь отправил другому)
                    SELECT
                        m.to_user_id as other_user_id,
                        m.created_at as last_time
                    FROM messages m
                    WHERE m.from_user_id = :user_id5
                    AND m.to_user_id != :user_id6
                    AND m.date_id IS NULL
                    AND m.event_id IS NULL
                    AND NOT EXISTS (
                        SELECT 1 FROM blocked_users bu3
                        WHERE bu3.user_id = :user_id7
                        AND bu3.blocked_user_id = m.to_user_id
                    )
                    AND NOT EXISTS (
                        SELECT 1 FROM blocked_users bu4
                        WHERE bu4.blocked_user_id = :user_id8
                        AND bu4.user_id = m.to_user_id
                    )
                ) as all_conversations
                GROUP BY other_user_id
                ORDER BY last_time DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id1', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id2', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id3', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id4', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id5', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id6', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id7', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id8', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $conversationIds = $stmt->fetchAll();

        if (empty($conversationIds)) {
            return [];
        }

        // Получаем детальную информацию для каждого диалога
        $result = [];
        foreach ($conversationIds as $conv) {
            $otherUserId = $conv['other_user_id'];

            // Информация о пользователе
            $userSql = "SELECT id, email, full_name, gender FROM users WHERE id = :other_id";
            $userStmt = $this->db->prepare($userSql);
            $userStmt->bindValue(':other_id', $otherUserId, PDO::PARAM_INT);
            $userStmt->execute();
            $otherUser = $userStmt->fetch();

            if (!$otherUser) continue;

            // Фото пользователя
            $photoSql = "SELECT photo FROM user_photos WHERE user_id = :other_id AND photo IS NOT NULL AND TRIM(photo) <> '' ORDER BY created_at ASC LIMIT 1";
            $photoStmt = $this->db->prepare($photoSql);
            $photoStmt->bindValue(':other_id', $otherUserId, PDO::PARAM_INT);
            $photoStmt->execute();
            $photo = $photoStmt->fetch();

            // Последнее сообщение (только обычный чат)
            // Используем разные имена параметров для каждого использования
            $lastMsgSql = "SELECT message, created_at FROM messages
                          WHERE ((from_user_id = :user_id AND to_user_id = :other_id)
                          OR (from_user_id = :other_id_dup AND to_user_id = :user_id_dup))
                          AND date_id IS NULL
                          AND event_id IS NULL
                          ORDER BY created_at DESC LIMIT 1";
            $lastMsgStmt = $this->db->prepare($lastMsgSql);
            $lastMsgStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $lastMsgStmt->bindValue(':other_id', $otherUserId, PDO::PARAM_INT);
            $lastMsgStmt->bindValue(':other_id_dup', $otherUserId, PDO::PARAM_INT);
            $lastMsgStmt->bindValue(':user_id_dup', $userId, PDO::PARAM_INT);
            $lastMsgStmt->execute();
            $lastMessage = $lastMsgStmt->fetch();

            // Количество непрочитанных сообщений от этого пользователя
            $unreadSql = "SELECT COUNT(*) as count FROM messages
                         WHERE from_user_id = :other_id
                         AND to_user_id = :user_id
                         AND date_id IS NULL
                         AND event_id IS NULL
                         AND (is_read = 0 OR is_read IS NULL)";
            $unreadStmt = $this->db->prepare($unreadSql);
            $unreadStmt->bindValue(':other_id', $otherUserId, PDO::PARAM_INT);
            $unreadStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $unreadStmt->execute();
            $unreadResult = $unreadStmt->fetch();
            $unreadCount = (int)($unreadResult['count'] ?? 0);

            $result[] = [
                'other_user_id' => $otherUserId,
                'other_user_email' => $otherUser['email'],
                'other_user_full_name' => $otherUser['full_name'] ?? null,
                'other_user_gender' => $otherUser['gender'],
                'photo' => $photo['photo'] ?? null,
                'last_message' => $lastMessage['message'] ?? null,
                'last_message_time' => $lastMessage['created_at'] ?? null,
                'unread_count' => $unreadCount
            ];
        }

        return $result;
    }

    /**
     * Получает количество непрочитанных сообщений для пользователя
     */
    public function getUnreadCount($userId, $lastCheckTime = null)
    {
        if ($lastCheckTime === null) {
            // Если время не указано, считаем все непрочитанные входящие сообщения
            // Исключаем сообщения от заблокированных пользователей
            // Исключаем сообщения от свиданий (date_id IS NOT NULL)
            // Исключаем сообщения от мероприятий (event_id IS NOT NULL) - они показываются только в чатах мероприятий
            // Исключаем сообщения от менеджеров - они показываются в уведомлениях от администратора
            $sql = "SELECT COUNT(*) as count
                    FROM messages m
                    WHERE m.to_user_id = :user_id
                    AND (m.is_read = 0 OR m.is_read IS NULL)
                    AND m.date_id IS NULL
                    AND m.event_id IS NULL
                    AND NOT EXISTS (
                        SELECT 1 FROM blocked_users bu1
                        WHERE bu1.user_id = :user_id2
                        AND bu1.blocked_user_id = m.from_user_id
                    )
                    AND NOT EXISTS (
                        SELECT 1 FROM blocked_users bu2
                        WHERE bu2.blocked_user_id = :user_id3
                        AND bu2.user_id = m.from_user_id
                    )
                    AND NOT EXISTS (
                        SELECT 1 FROM users mgr
                        WHERE mgr.id = m.from_user_id AND mgr.role = 'manager'
                    )";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':user_id2' => $userId,
                ':user_id3' => $userId
            ]);
        } else {
            // Считаем только непрочитанные сообщения после указанного времени
            // Исключаем сообщения от заблокированных пользователей
            // Исключаем сообщения от свиданий (date_id IS NOT NULL)
            // Исключаем сообщения от мероприятий (event_id IS NOT NULL) - они показываются только в чатах мероприятий
            // Исключаем сообщения от менеджеров - они показываются в уведомлениях от администратора
            $sql = "SELECT COUNT(*) as count
                    FROM messages m
                    WHERE m.to_user_id = :user_id
                    AND m.created_at > :last_check
                    AND (m.is_read = 0 OR m.is_read IS NULL)
                    AND m.date_id IS NULL
                    AND m.event_id IS NULL
                    AND NOT EXISTS (
                        SELECT 1 FROM blocked_users bu1
                        WHERE bu1.user_id = :user_id2
                        AND bu1.blocked_user_id = m.from_user_id
                    )
                    AND NOT EXISTS (
                        SELECT 1 FROM blocked_users bu2
                        WHERE bu2.blocked_user_id = :user_id3
                        AND bu2.user_id = m.from_user_id
                    )
                    AND NOT EXISTS (
                        SELECT 1 FROM users mgr
                        WHERE mgr.id = m.from_user_id AND mgr.role = 'manager'
                    )";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':last_check' => $lastCheckTime,
                ':user_id2' => $userId,
                ':user_id3' => $userId
            ]);
        }
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Получает новые сообщения после указанного времени
     */
    public function getNewMessages($userId, $lastCheckTime = null)
    {
        if ($lastCheckTime === null) {
            // Если время не указано, возвращаем последние 10 непрочитанных входящих сообщений
            // Исключаем сообщения от заблокированных пользователей
            // Исключаем сообщения от свиданий (date_id IS NOT NULL)
            // Исключаем сообщения от мероприятий (event_id IS NOT NULL) - они показываются только в чатах мероприятий
            // Исключаем сообщения от менеджеров - они показываются в уведомлениях от администратора
            $sql = "SELECT m.*, u.email as from_email, u.full_name as from_full_name, u.role as from_role
                    FROM messages m
                    JOIN users u ON m.from_user_id = u.id
                    WHERE m.to_user_id = :user_id
                    AND (m.is_read = 0 OR m.is_read IS NULL)
                    AND m.date_id IS NULL
                    AND m.event_id IS NULL
                    AND NOT EXISTS (
                        SELECT 1 FROM blocked_users bu1
                        WHERE bu1.user_id = :user_id2
                        AND bu1.blocked_user_id = m.from_user_id
                    )
                    AND NOT EXISTS (
                        SELECT 1 FROM blocked_users bu2
                        WHERE bu2.blocked_user_id = :user_id3
                        AND bu2.user_id = m.from_user_id
                    )
                    AND u.role != 'manager'
                    ORDER BY m.created_at DESC
                    LIMIT 10";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':user_id2' => $userId,
                ':user_id3' => $userId
            ]);
        } else {
            // Возвращаем только непрочитанные сообщения после указанного времени
            // Исключаем сообщения от заблокированных пользователей
            // Исключаем сообщения от свиданий (date_id IS NOT NULL)
            // Исключаем сообщения от мероприятий (event_id IS NOT NULL) - они показываются только в чатах мероприятий
            // Исключаем сообщения от менеджеров - они показываются в уведомлениях от администратора
            $sql = "SELECT m.*, u.email as from_email, u.full_name as from_full_name, u.role as from_role
                    FROM messages m
                    JOIN users u ON m.from_user_id = u.id
                    WHERE m.to_user_id = :user_id
                    AND m.created_at > :last_check
                    AND (m.is_read = 0 OR m.is_read IS NULL)
                    AND m.date_id IS NULL
                    AND m.event_id IS NULL
                    AND NOT EXISTS (
                        SELECT 1 FROM blocked_users bu1
                        WHERE bu1.user_id = :user_id2
                        AND bu1.blocked_user_id = m.from_user_id
                    )
                    AND NOT EXISTS (
                        SELECT 1 FROM blocked_users bu2
                        WHERE bu2.blocked_user_id = :user_id3
                        AND bu2.user_id = m.from_user_id
                    )
                    AND u.role != 'manager'
                    ORDER BY m.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':last_check' => $lastCheckTime,
                ':user_id2' => $userId,
                ':user_id3' => $userId
            ]);
        }
        return $stmt->fetchAll();
    }

    /**
     * Получает уведомления от администраторов и менеджеров
     */
    public function getAdminNotifications($userId, $limit = 50)
    {
        // Объединяем уведомления от админов (таблица admins) и менеджеров (пользователи с role='manager')
        $sql = "SELECT * FROM (
                    -- Уведомления от администраторов
                    SELECT m.*,
                        a.email as from_email,
                        'Администратор' as from_full_name,
                        'admin' as from_role,
                        1 as from_is_admin
                    FROM messages m
                    JOIN admins a ON m.from_user_id = a.id
                    WHERE m.to_user_id = :user_id1
                    AND m.date_id IS NULL
                    AND m.event_id IS NULL
                    AND NOT EXISTS (
                        SELECT 1 FROM blocked_users bu1
                        WHERE bu1.user_id = :user_id2
                        AND bu1.blocked_user_id = m.from_user_id
                    )
                    
                    UNION ALL
                    
                    -- Уведомления от менеджеров (пользователи с role='manager')
                    SELECT m.*,
                        u.email as from_email,
                        'Администратор' as from_full_name,
                        'manager' as from_role,
                        1 as from_is_admin
                    FROM messages m
                    JOIN users u ON m.from_user_id = u.id AND u.role = 'manager'
                    WHERE m.to_user_id = :user_id3
                    AND m.date_id IS NULL
                    AND m.event_id IS NULL
                    AND NOT EXISTS (
                        SELECT 1 FROM blocked_users bu2
                        WHERE bu2.user_id = :user_id4
                        AND bu2.blocked_user_id = m.from_user_id
                    )
                ) as admin_notifications
                ORDER BY created_at DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id1', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id2', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id3', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id4', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Получает количество непрочитанных уведомлений от администраторов и менеджеров
     */
    public function getUnreadAdminNotificationsCount($userId)
    {
        // Считаем уведомления от админов (таблица admins) и менеджеров (пользователи с role='manager')
        $sql = "SELECT COUNT(*) as count FROM (
                    -- Уведомления от администраторов
                    SELECT m.id
                    FROM messages m
                    JOIN admins a ON m.from_user_id = a.id
                    WHERE m.to_user_id = :user_id1
                    AND m.date_id IS NULL
                    AND m.event_id IS NULL
                    AND (m.is_read = 0 OR m.is_read IS NULL)
                    AND NOT EXISTS (
                        SELECT 1 FROM blocked_users bu1
                        WHERE bu1.user_id = :user_id2
                        AND bu1.blocked_user_id = m.from_user_id
                    )
                    
                    UNION ALL
                    
                    -- Уведомления от менеджеров (пользователи с role='manager')
                    SELECT m.id
                    FROM messages m
                    JOIN users u ON m.from_user_id = u.id AND u.role = 'manager'
                    WHERE m.to_user_id = :user_id3
                    AND m.date_id IS NULL
                    AND m.event_id IS NULL
                    AND (m.is_read = 0 OR m.is_read IS NULL)
                    AND NOT EXISTS (
                        SELECT 1 FROM blocked_users bu2
                        WHERE bu2.user_id = :user_id4
                        AND bu2.blocked_user_id = m.from_user_id
                    )
                ) as admin_notifications";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id1' => $userId,
            ':user_id2' => $userId,
            ':user_id3' => $userId,
            ':user_id4' => $userId
        ]);

        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0);
    }

    /**
     * Получает переписку по свиданию
     */
    public function getDateChat($dateId)
    {
        $sql = "SELECT m.*,
                u1.email as from_email,
                u1.full_name as from_full_name,
                u1.role as from_role,
                u2.email as to_email,
                u2.full_name as to_full_name,
                u2.role as to_role
                FROM messages m
                JOIN users u1 ON m.from_user_id = u1.id
                JOIN users u2 ON m.to_user_id = u2.id
                WHERE m.date_id = :date_id
                ORDER BY m.created_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':date_id' => $dateId]);
        return $stmt->fetchAll();
    }

    /**
     * Получает список диалогов для свидания (все участники с последними сообщениями)
     */
    public function getDateConversations($dateId, $userId)
    {
        // Получаем всех уникальных участников чата
        // Используем разные имена параметров для каждого использования
        $sql = "SELECT DISTINCT
                CASE
                    WHEN m.from_user_id = :user_id1 THEN m.to_user_id
                    ELSE m.from_user_id
                END as other_user_id,
                MAX(m.created_at) as last_time
                FROM messages m
                WHERE m.date_id = :date_id
                AND (m.from_user_id = :user_id2 OR m.to_user_id = :user_id3)
                GROUP BY other_user_id
                ORDER BY last_time DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':date_id' => $dateId,
            ':user_id1' => $userId,
            ':user_id2' => $userId,
            ':user_id3' => $userId
        ]);
        $conversationIds = $stmt->fetchAll();

        if (empty($conversationIds)) {
            return [];
        }

        $result = [];
        foreach ($conversationIds as $conv) {
            $otherUserId = $conv['other_user_id'];

            // Информация о пользователе
            $userSql = "SELECT id, email, full_name, gender FROM users WHERE id = :other_id";
            $userStmt = $this->db->prepare($userSql);
            $userStmt->execute([':other_id' => $otherUserId]);
            $otherUser = $userStmt->fetch();

            if (!$otherUser) continue;

            // Фото пользователя
            $photoSql = "SELECT photo FROM user_photos WHERE user_id = :other_id AND photo IS NOT NULL AND TRIM(photo) <> '' ORDER BY created_at ASC LIMIT 1";
            $photoStmt = $this->db->prepare($photoSql);
            $photoStmt->execute([':other_id' => $otherUserId]);
            $photo = $photoStmt->fetch();

            // Последнее сообщение в этом диалоге (только для этого свидания)
            // Используем разные имена параметров для каждого использования
            $lastMsgSql = "SELECT message, created_at FROM messages
                          WHERE date_id = :date_id
                          AND ((from_user_id = :user_id1 AND to_user_id = :other_id1)
                          OR (from_user_id = :other_id2 AND to_user_id = :user_id2))
                          ORDER BY created_at DESC LIMIT 1";
            $lastMsgStmt = $this->db->prepare($lastMsgSql);
            $lastMsgStmt->execute([
                ':date_id' => $dateId,
                ':user_id1' => $userId,
                ':other_id1' => $otherUserId,
                ':other_id2' => $otherUserId,
                ':user_id2' => $userId
            ]);
            $lastMessage = $lastMsgStmt->fetch();

            // Количество непрочитанных сообщений от этого пользователя в этом свидании
            $unreadSql = "SELECT COUNT(*) as count FROM messages
                         WHERE date_id = :date_id
                         AND from_user_id = :other_id
                         AND to_user_id = :user_id
                         AND (is_read = 0 OR is_read IS NULL)";
            $unreadStmt = $this->db->prepare($unreadSql);
            $unreadStmt->execute([
                ':date_id' => $dateId,
                ':other_id' => $otherUserId,
                ':user_id' => $userId
            ]);
            $unreadResult = $unreadStmt->fetch();
            $unreadCount = (int)($unreadResult['count'] ?? 0);

            $result[] = [
                'other_user_id' => $otherUserId,
                'other_user_email' => $otherUser['email'],
                'other_user_full_name' => $otherUser['full_name'] ?? null,
                'other_user_gender' => $otherUser['gender'],
                'photo' => $photo['photo'] ?? null,
                'last_message' => $lastMessage['message'] ?? null,
                'last_message_time' => $lastMessage['created_at'] ?? null,
                'unread_count' => $unreadCount
            ];
        }

        return $result;
    }

    /**
     * Получает переписку по мероприятию
     */
    public function getEventChat($eventId)
    {
        $sql = $this->getEventChatBaseSql() . " AND m.event_id = :event_id ORDER BY m.created_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':event_id' => $eventId]);
        $messages = $stmt->fetchAll();

        $this->enrichAdminMessages($messages);

        return $messages;
    }

    /**
     * Получает сообщения мероприятия, начиная с указанного ID (для «живого» чата)
     */
    public function getEventChatAfterId($eventId, $lastMessageId)
    {
        $sql = $this->getEventChatBaseSql() . " AND m.event_id = :event_id AND m.id > :last_id ORDER BY m.created_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':event_id' => $eventId,
            ':last_id' => $lastMessageId
        ]);
        $messages = $stmt->fetchAll();

        $this->enrichAdminMessages($messages);

        return $messages;
    }

    /**
     * Базовый SQL для выборки сообщений чата мероприятия
     */
    private function getEventChatBaseSql()
    {
        // Используем LEFT JOIN для админов, так как они могут не быть в таблице users
        return "SELECT m.*,
                u1.email as from_email,
                u1.full_name as from_full_name,
                u1.role as from_role,
                CASE WHEN a1.id IS NOT NULL THEN 1 ELSE 0 END as from_is_admin,
                u2.email as to_email,
                u2.full_name as to_full_name,
                u2.role as to_role
                FROM messages m
                LEFT JOIN users u1 ON m.from_user_id = u1.id
                LEFT JOIN admins a1 ON m.from_user_id = a1.id
                LEFT JOIN users u2 ON m.to_user_id = u2.id
                WHERE 1 = 1";
    }

    /**
     * Дополняет данные сообщений от админов (которые могут отсутствовать в таблице users)
     *
     * @param array $messages
     * @return void
     */
    private function enrichAdminMessages(array &$messages)
    {
        foreach ($messages as &$msg) {
            if (!empty($msg['from_is_admin']) && empty($msg['from_email'])) {
                // Если это админ, получаем его данные из таблицы admins
                $adminSql = "SELECT email FROM admins WHERE id = :id";
                $adminStmt = $this->db->prepare($adminSql);
                $adminStmt->execute([':id' => $msg['from_user_id']]);
                $admin = $adminStmt->fetch();
                if ($admin) {
                    $msg['from_email'] = $admin['email'];
                    $msg['from_full_name'] = 'Администратор';
                    $msg['from_role'] = 'admin';
                }
            }
        }
    }

    /**
     * Удаляет сообщение
     */
    public function delete($messageId, $userId)
    {
        $sql = "DELETE FROM messages WHERE id = :id AND from_user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $messageId,
            ':user_id' => $userId
        ]);
    }

    /**
     * Удаляет уведомление от администратора или менеджера (где пользователь является получателем)
     */
    public function deleteAdminNotification($messageId, $userId)
    {
        // Удаляем уведомления от админов (таблица admins) или менеджеров (пользователи с role='manager')
        $sql = "DELETE FROM messages
                WHERE id = :id
                AND to_user_id = :user_id
                AND (
                    EXISTS (SELECT 1 FROM admins a WHERE a.id = messages.from_user_id)
                    OR EXISTS (SELECT 1 FROM users u WHERE u.id = messages.from_user_id AND u.role = 'manager')
                )";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $messageId,
            ':user_id' => $userId
        ]);
    }

    /**
     * Помечает сообщение как прочитанное
     */
    public function markAsRead($messageId, $userId)
    {
        $sql = "UPDATE messages
                SET is_read = 1
                WHERE id = :id AND to_user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $messageId,
            ':user_id' => $userId
        ]);
    }

    /**
     * Помечает все входящие сообщения пользователя как прочитанные
     */
    public function markAllAsRead($userId)
    {
        $sql = "UPDATE messages
                SET is_read = 1
                WHERE to_user_id = :user_id
                AND (is_read = 0 OR is_read IS NULL)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId
        ]);
    }

    /**
     * Помечает все сообщения в диалоге между двумя пользователями как прочитанные
     */
    public function markConversationAsRead($userId, $otherUserId)
    {
        $sql = "UPDATE messages
                SET is_read = 1
                WHERE to_user_id = :user_id
                AND from_user_id = :other_user_id
                AND (is_read = 0 OR is_read IS NULL)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':other_user_id' => $otherUserId
        ]);
    }

    /**
     * Получает количество непрочитанных сообщений для свидания
     */
    public function getUnreadCountForDate($dateId, $userId)
    {
        $sql = "SELECT COUNT(*) as count
                FROM messages m
                WHERE m.date_id = :date_id
                AND m.to_user_id = :user_id
                AND m.from_user_id != :user_id2
                AND (m.is_read = 0 OR m.is_read IS NULL)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':date_id' => $dateId,
            ':user_id' => $userId,
            ':user_id2' => $userId
        ]);
        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0);
    }

    /**
     * Получает количество непрочитанных сообщений для мероприятия
     */
    public function getUnreadCountForEvent($eventId, $userId)
    {
        $sql = "SELECT COUNT(*) as count
                FROM messages m
                WHERE m.event_id = :event_id
                AND m.to_user_id = :user_id
                AND m.from_user_id != :user_id2
                AND (m.is_read = 0 OR m.is_read IS NULL)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':event_id' => $eventId,
            ':user_id' => $userId,
            ':user_id2' => $userId
        ]);
        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0);
    }

    /**
     * Получает общее количество непрочитанных сообщений из всех свиданий пользователя
     */
    public function getTotalUnreadDatesCount($userId)
    {
        $sql = "SELECT COUNT(*) as count
                FROM messages m
                WHERE m.date_id IS NOT NULL
                AND m.to_user_id = :user_id
                AND m.from_user_id != :user_id2
                AND (m.is_read = 0 OR m.is_read IS NULL)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':user_id2' => $userId
        ]);
        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0);
    }

    /**
     * Получает общее количество непрочитанных сообщений из всех мероприятий пользователя
     */
    public function getTotalUnreadEventsCount($userId)
    {
        $sql = "SELECT COUNT(*) as count
                FROM messages m
                WHERE m.event_id IS NOT NULL
                AND m.to_user_id = :user_id
                AND m.from_user_id != :user_id2
                AND (m.is_read = 0 OR m.is_read IS NULL)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':user_id2' => $userId
        ]);
        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0);
    }

    /**
     * Получает список ID мероприятий, в которых пользователь участвует в чате
     */
    public function getEventIdsWithChat($userId)
    {
        $sql = "SELECT m.event_id, MAX(m.created_at) as last_message_time
                FROM messages m
                WHERE m.event_id IS NOT NULL
                AND (m.from_user_id = :user_id OR m.to_user_id = :user_id2)
                GROUP BY m.event_id
                ORDER BY last_message_time DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':user_id2' => $userId
        ]);
        $results = $stmt->fetchAll();
        return array_column($results, 'event_id');
    }

    /**
     * Получает список ID свиданий, в которых пользователь участвует в чате
     */
    public function getDateIdsWithChat($userId)
    {
        // Ищем все сообщения, где пользователь является отправителем ИЛИ получателем
        // Это включает как отправленные, так и полученные сообщения
        $sql = "SELECT DISTINCT m.date_id, MAX(m.created_at) as last_message_time
                FROM messages m
                WHERE m.date_id IS NOT NULL
                AND (m.from_user_id = :user_id OR m.to_user_id = :user_id2)
                GROUP BY m.date_id
                ORDER BY last_message_time DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':user_id2' => $userId
        ]);
        $results = $stmt->fetchAll();
        
        // Логируем для отладки
        error_log('=== getDateIdsWithChat ===');
        error_log('user_id: ' . $userId);
        error_log('Найдено записей: ' . count($results));
        foreach ($results as $row) {
            error_log('  - date_id: ' . $row['date_id'] . ', last_message: ' . $row['last_message_time']);
        }
        
        $dateIds = array_column($results, 'date_id');
        error_log('Возвращаемые date_ids: ' . implode(', ', $dateIds));
        
        // Дополнительная проверка: ищем все сообщения для этого пользователя
        $debugSql = "SELECT COUNT(*) as total, 
                    COUNT(CASE WHEN from_user_id = :user_id3 THEN 1 END) as sent,
                    COUNT(CASE WHEN to_user_id = :user_id4 THEN 1 END) as received
                    FROM messages 
                    WHERE date_id IS NOT NULL 
                    AND (from_user_id = :user_id5 OR to_user_id = :user_id6)";
        $debugStmt = $this->db->prepare($debugSql);
        $debugStmt->execute([
            ':user_id3' => $userId,
            ':user_id4' => $userId,
            ':user_id5' => $userId,
            ':user_id6' => $userId
        ]);
        $debugResult = $debugStmt->fetch();
        error_log('Всего сообщений для user_id ' . $userId . ': отправлено=' . $debugResult['sent'] . ', получено=' . $debugResult['received'] . ', всего=' . $debugResult['total']);
        
        return $dateIds;
    }

    /**
     * Получает ID собеседника в чате свидания для текущего пользователя
     * Возвращает ID пользователя, с которым ведется переписка в этом чате
     * Если есть несколько участников, возвращает того, с кем было последнее сообщение
     */
    public function getDateChatParticipant($dateId, $userId)
    {
        // Получаем последнее сообщение в чате, где участвует текущий пользователь
        // Определяем другого участника (не текущего пользователя)
        $sql = "SELECT 
                CASE 
                    WHEN from_user_id = :user_id1 THEN to_user_id
                    ELSE from_user_id
                END as other_user_id,
                from_user_id,
                to_user_id
                FROM messages
                WHERE date_id = :date_id
                AND (from_user_id = :user_id2 OR to_user_id = :user_id3)
                ORDER BY created_at DESC
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':date_id' => $dateId,
            ':user_id1' => $userId,
            ':user_id2' => $userId,
            ':user_id3' => $userId
        ]);
        
        $result = $stmt->fetch();
        
        error_log('getDateChatParticipant для date_id=' . $dateId . ', user_id=' . $userId);
        if ($result) {
            error_log('  Найдено сообщение: from_user_id=' . $result['from_user_id'] . ', to_user_id=' . $result['to_user_id'] . ', other_user_id=' . ($result['other_user_id'] ?? 'NULL'));
        } else {
            error_log('  Сообщения не найдены');
        }
        
        if ($result && !empty($result['other_user_id']) && $result['other_user_id'] != $userId) {
            error_log('  Возвращаем other_user_id=' . $result['other_user_id']);
            return (int)$result['other_user_id'];
        }
        
        error_log('  Возвращаем NULL');
        return null;
    }

    /**
     * Помечает все сообщения в чате свидания как прочитанные
     */
    public function markDateChatAsRead($dateId, $userId)
    {
        $sql = "UPDATE messages
                SET is_read = 1
                WHERE date_id = :date_id
                AND to_user_id = :user_id
                AND (is_read = 0 OR is_read IS NULL)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':date_id' => $dateId,
            ':user_id' => $userId
        ]);
    }

    /**
     * Помечает все сообщения в чате мероприятия как прочитанные
     */
    public function markEventChatAsRead($eventId, $userId)
    {
        $sql = "UPDATE messages
                SET is_read = 1
                WHERE event_id = :event_id
                AND to_user_id = :user_id
                AND (is_read = 0 OR is_read IS NULL)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':event_id' => $eventId,
            ':user_id' => $userId
        ]);
    }

    /**
     * Удаляет весь диалог между двумя пользователями
     */
    public function deleteConversation($userId, $otherUserId)
    {
        try {
            // Убеждаемся, что параметры - целые числа
            $userId = (int)$userId;
            $otherUserId = (int)$otherUserId;

            if ($userId <= 0 || $otherUserId <= 0) {
                error_log('Ошибка: некорректные ID пользователей при удалении диалога. userId: ' . $userId . ', otherUserId: ' . $otherUserId);
                return false;
            }

            // Удаляем все сообщения между двумя пользователями
            // Используем разные имена параметров для каждого использования (как в getConversation)
            // Удаляем только обычные сообщения (не связанные с date_id или event_id)
            $sql = "DELETE FROM messages
                    WHERE ((from_user_id = :user1 AND to_user_id = :user2)
                    OR (from_user_id = :user2_dup AND to_user_id = :user1_dup))
                    AND (date_id IS NULL AND event_id IS NULL)";
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                $errorInfo = $this->db->errorInfo();
                error_log('Ошибка подготовки запроса при удалении диалога: ' . print_r($errorInfo, true));
                return false;
            }

            $result = $stmt->execute([
                ':user1' => $userId,
                ':user2' => $otherUserId,
                ':user2_dup' => $otherUserId,
                ':user1_dup' => $userId
            ]);

            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log('Ошибка выполнения запроса при удалении диалога: ' . print_r($errorInfo, true));
                return false;
            }

            // Проверяем количество удаленных строк
            $deletedCount = $stmt->rowCount();
            error_log('Удалено сообщений: ' . $deletedCount . ' для диалога между пользователями ' . $userId . ' и ' . $otherUserId);

            // Возвращаем true, даже если удалено 0 строк (диалога может не быть)
            return true;
        } catch (PDOException $e) {
            error_log('PDO исключение при удалении диалога: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return false;
        } catch (Exception $e) {
            error_log('Общее исключение при удалении диалога: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Удаляет все сообщения пользователя в чате мероприятия
     */
    public function deleteEventChatForUser($eventId, $userId)
    {
        try {
            $eventId = (int)$eventId;
            $userId = (int)$userId;

            error_log('deleteEventChatForUser вызван с параметрами: eventId=' . $eventId . ', userId=' . $userId);

            if ($eventId <= 0 || $userId <= 0) {
                error_log('Ошибка: некорректные параметры при удалении чата мероприятия. eventId: ' . $eventId . ', userId: ' . $userId);
                return false;
            }

            // Проверяем подключение к БД
            if (!$this->db) {
                error_log('Ошибка: нет подключения к БД');
                return false;
            }

            // Удаляем все сообщения пользователя в чате мероприятия (отправленные и полученные)
            // Используем разные имена параметров для корректной привязки
            $sql = "DELETE FROM messages
                    WHERE event_id = :event_id
                    AND (from_user_id = :user_id_from OR to_user_id = :user_id_to)";
            
            error_log('SQL запрос: ' . $sql);
            error_log('Параметры: event_id=' . $eventId . ', user_id_from=' . $userId . ', user_id_to=' . $userId);
            
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                $errorInfo = $this->db->errorInfo();
                error_log('Ошибка подготовки запроса при удалении чата мероприятия: ' . print_r($errorInfo, true));
                return false;
            }

            $params = [
                ':event_id' => $eventId,
                ':user_id_from' => $userId,
                ':user_id_to' => $userId
            ];

            $result = $stmt->execute($params);

            error_log('Результат execute: ' . var_export($result, true));

            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log('Ошибка выполнения запроса при удалении чата мероприятия. Params: ' . print_r($params, true) . ' Error: ' . print_r($errorInfo, true));
                return false;
            }

            $deletedCount = $stmt->rowCount();
            error_log('Удалено сообщений в чате мероприятия: ' . $deletedCount . ' для event_id: ' . $eventId . ', user_id: ' . $userId);

            return true;
        } catch (PDOException $e) {
            error_log('PDOException при удалении чата мероприятия: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return false;
        } catch (Exception $e) {
            error_log('Общее исключение при удалении чата мероприятия: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Получает информацию о свидании из сообщений (для удаленных свиданий)
     */
    public function getDateChatInfoFromMessages($dateId, $userId)
    {
        try {
            $dateId = (int)$dateId;
            $userId = (int)$userId;

            // Получаем информацию о свидании из таблицы dates (если еще существует)
            $sql = "SELECT 
                    d.id,
                    d.user_id,
                    d.title,
                    d.date_time,
                    d.category_id,
                    dc.name as category_name,
                    dc.description as category_description,
                    u.email,
                    u.age,
                    u.gender,
                    (SELECT photo FROM user_photos WHERE user_id = d.user_id AND photo IS NOT NULL AND TRIM(photo) <> '' ORDER BY created_at ASC LIMIT 1) as photo
                    FROM dates d
                    LEFT JOIN users u ON d.user_id = u.id
                    LEFT JOIN date_categories dc ON d.category_id = dc.id
                    WHERE d.id = :date_id
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':date_id' => $dateId]);
            
            $result = $stmt->fetch();
            
            if ($result && !empty($result['id'])) {
                $result['distance'] = 0;
                return $result;
            }
            
            // Если свидание удалено, возвращаем базовую информацию
            // Определяем user_id второго участника чата
            $otherUserSql = "SELECT DISTINCT
                            CASE 
                                WHEN from_user_id = :user_id THEN to_user_id
                                ELSE from_user_id
                            END as other_user_id
                            FROM messages
                            WHERE date_id = :date_id
                            AND (from_user_id = :user_id2 OR to_user_id = :user_id3)
                            LIMIT 1";
            $otherUserStmt = $this->db->prepare($otherUserSql);
            $otherUserStmt->execute([
                ':user_id' => $userId,
                ':user_id2' => $userId,
                ':user_id3' => $userId,
                ':date_id' => $dateId
            ]);
            $otherUser = $otherUserStmt->fetch();
            
            return [
                'id' => $dateId,
                'user_id' => $otherUser['other_user_id'] ?? null,
                'title' => 'Удаленное свидание',
                'date_time' => date('Y-m-d H:i:s'),
                'category_id' => null,
                'category_name' => null,
                'category_description' => null,
                'photo' => null,
                'distance' => 0,
                'email' => null,
                'age' => null,
                'gender' => null
            ];
        } catch (Exception $e) {
            error_log('Ошибка при получении информации о чате из сообщений: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Удаляет все сообщения пользователя в чате свидания
     */
    public function deleteDateChatForUser($dateId, $userId)
    {
        try {
            $dateId = (int)$dateId;
            $userId = (int)$userId;

            error_log('deleteDateChatForUser вызван с параметрами: dateId=' . $dateId . ', userId=' . $userId);

            if ($dateId <= 0 || $userId <= 0) {
                error_log('Ошибка: некорректные параметры при удалении чата свидания. dateId: ' . $dateId . ', userId: ' . $userId);
                return false;
            }

            // Проверяем подключение к БД
            if (!$this->db) {
                error_log('Ошибка: нет подключения к БД');
                return false;
            }

            // Удаляем все сообщения пользователя в чате свидания (отправленные и полученные)
            $sql = "DELETE FROM messages
                    WHERE date_id = :date_id
                    AND (from_user_id = :user_id OR to_user_id = :user_id2)";
            
            error_log('SQL запрос: ' . $sql);
            error_log('Параметры: date_id=' . $dateId . ', user_id=' . $userId);
            
            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                $errorInfo = $this->db->errorInfo();
                error_log('Ошибка подготовки запроса при удалении чата свидания: ' . print_r($errorInfo, true));
                return false;
            }

            $result = $stmt->execute([
                ':date_id' => $dateId,
                ':user_id' => $userId,
                ':user_id2' => $userId
            ]);

            error_log('Результат execute: ' . var_export($result, true));

            if ($result === false) {
                $errorInfo = $stmt->errorInfo();
                error_log('Ошибка выполнения запроса при удалении чата свидания: ' . print_r($errorInfo, true));
                return false;
            }

            // Проверяем, были ли удалены записи
            $deletedCount = $stmt->rowCount();
            error_log('Успешно удалено сообщений в чате свидания: ' . $deletedCount . ' для date_id: ' . $dateId . ', user_id: ' . $userId);

            // Возвращаем true даже если удалено 0 записей (чат может быть уже пустым)
            // Важно: execute() возвращает true даже если строк не найдено, это нормально
            return true;
        } catch (PDOException $e) {
            error_log('PDO исключение при удалении чата свидания: ' . $e->getMessage());
            error_log('Код ошибки: ' . $e->getCode());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return false;
        } catch (Exception $e) {
            error_log('Общее исключение при удалении чата свидания: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }
}

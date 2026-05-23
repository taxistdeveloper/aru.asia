<?php

/**
 * МОДЕЛЬ PUSH-УВЕДОМЛЕНИЙ
 * 
 * Работает с таблицами push_notification_tokens и push_notification_logs
 */

class PushNotification
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Регистрирует токен устройства для пользователя
     */
    public function registerToken($userId, $token, $deviceType = 'web', $userAgent = null)
    {
        // Проверяем, не зарегистрирован ли уже этот токен для этого пользователя
        $existing = $this->getTokenByValue($userId, $token);
        
        if ($existing) {
            // Обновляем существующий токен
            $sql = "UPDATE push_notification_tokens 
                    SET device_type = :device_type, 
                        user_agent = :user_agent,
                        updated_at = NOW()
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $existing['id'],
                ':device_type' => $deviceType,
                ':user_agent' => $userAgent
            ]);
        } else {
            // Создаем новый токен
            $sql = "INSERT INTO push_notification_tokens 
                    (user_id, token, device_type, user_agent, created_at) 
                    VALUES (:user_id, :token, :device_type, :user_agent, NOW())";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':user_id' => $userId,
                ':token' => $token,
                ':device_type' => $deviceType,
                ':user_agent' => $userAgent
            ]);
        }
    }

    /**
     * Удаляет токен устройства
     */
    public function unregisterToken($userId, $token)
    {
        $sql = "DELETE FROM push_notification_tokens 
                WHERE user_id = :user_id AND token = :token";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':token' => $token
        ]);
    }

    /**
     * Получает токен по значению
     */
    public function getTokenByValue($userId, $token)
    {
        $sql = "SELECT * FROM push_notification_tokens 
                WHERE user_id = :user_id AND token = :token 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':token' => $token
        ]);
        return $stmt->fetch();
    }

    /**
     * Получает все токены пользователя
     */
    public function getUserTokens($userId)
    {
        $sql = "SELECT * FROM push_notification_tokens 
                WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Удаляет все токены пользователя
     */
    public function deleteUserTokens($userId)
    {
        $sql = "DELETE FROM push_notification_tokens WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':user_id' => $userId]);
    }

    /**
     * Логирует отправку уведомления
     */
    public function logNotification($userId, $type, $title, $body, $data = null, $status = 'sent')
    {
        $sql = "INSERT INTO push_notification_logs 
                (user_id, notification_type, title, body, data, status, sent_at) 
                VALUES (:user_id, :type, :title, :body, :data, :status, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':type' => $type,
            ':title' => $title,
            ':body' => $body,
            ':data' => $data ? json_encode($data) : null,
            ':status' => $status
        ]);
    }
}


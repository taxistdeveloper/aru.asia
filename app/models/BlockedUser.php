<?php
/**
 * МОДЕЛЬ ЗАБЛОКИРОВАННЫХ ПОЛЬЗОВАТЕЛЕЙ
 * 
 * Работает с таблицей blocked_users - хранит список заблокированных пользователей
 */

class BlockedUser {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Блокирует пользователя
     */
    public function block($userId, $blockedUserId) {
        $sql = "INSERT INTO blocked_users (user_id, blocked_user_id, created_at) 
                VALUES (:user_id, :blocked_user_id, NOW())
                ON DUPLICATE KEY UPDATE created_at = NOW()";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':blocked_user_id' => $blockedUserId
        ]);
    }
    
    /**
     * Разблокирует пользователя
     */
    public function unblock($userId, $blockedUserId) {
        $sql = "DELETE FROM blocked_users 
                WHERE user_id = :user_id AND blocked_user_id = :blocked_user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':blocked_user_id' => $blockedUserId
        ]);
    }
    
    /**
     * Проверяет заблокирован ли пользователь
     */
    public function isBlocked($userId, $blockedUserId) {
        $sql = "SELECT COUNT(*) as count FROM blocked_users 
                WHERE user_id = :user_id AND blocked_user_id = :blocked_user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':blocked_user_id' => $blockedUserId
        ]);
        $result = $stmt->fetch();
        return ($result['count'] ?? 0) > 0;
    }
    
    /**
     * Получает список заблокированных пользователей
     */
    public function getBlockedUsers($userId) {
        $sql = "SELECT bu.*, u.email, u.gender, u.age,
                (SELECT photo FROM user_photos WHERE user_id = u.id AND photo IS NOT NULL AND TRIM(photo) <> '' ORDER BY created_at ASC LIMIT 1) as photo
                FROM blocked_users bu
                JOIN users u ON bu.blocked_user_id = u.id
                WHERE bu.user_id = :user_id
                ORDER BY bu.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }
}


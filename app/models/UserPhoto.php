<?php
/**
 * МОДЕЛЬ ФОТОГРАФИЙ ПОЛЬЗОВАТЕЛЯ
 * 
 * Работает с таблицей user_photos - хранит фотографии пользователей
 */

class UserPhoto {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Добавляет фото пользователю
     */
    public function add($userId, $photoPath) {
        $sql = "INSERT INTO user_photos (user_id, photo, created_at) 
                VALUES (:user_id, :photo, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':photo' => $photoPath
        ]);
    }
    
    /**
     * Получает все фото пользователя
     */
    public function getByUserId($userId) {
        $sql = "SELECT * FROM user_photos WHERE user_id = :user_id AND photo IS NOT NULL AND TRIM(photo) <> '' ORDER BY created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Удаляет фото
     */
    public function delete($photoId, $userId) {
        $sql = "DELETE FROM user_photos WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $photoId,
            ':user_id' => $userId
        ]);
    }
    
    /**
     * Подсчитывает количество фото пользователя
     */
    public function countByUserId($userId) {
        $sql = "SELECT COUNT(*) as count FROM user_photos WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
}


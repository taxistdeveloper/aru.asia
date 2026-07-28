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
     * Получает все фото пользователя, у которых файл реально есть на диске.
     * Записи без файла удаляются из БД, чтобы не показывать пустые квадраты
     * и не блокировать лимит загрузки.
     */
    public function getByUserId($userId) {
        $sql = "SELECT * FROM user_photos WHERE user_id = :user_id AND photo IS NOT NULL AND TRIM(photo) <> '' ORDER BY created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $photos = $stmt->fetchAll();

        $projectRoot = dirname(__DIR__, 2);
        $existing = [];

        foreach ($photos as $photo) {
            $filename = $photo['photo'] ?? '';
            if ($filename === '') {
                $this->delete((int)$photo['id'], $userId);
                continue;
            }

            $photoPath = $projectRoot . '/' . UPLOAD_DIR . 'photos/' . $filename;
            if (is_file($photoPath)) {
                $existing[] = $photo;
            } else {
                $this->delete((int)$photo['id'], $userId);
            }
        }

        return $existing;
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
     * Подсчитывает количество фото пользователя (только с существующими файлами)
     */
    public function countByUserId($userId) {
        return count($this->getByUserId($userId));
    }
}


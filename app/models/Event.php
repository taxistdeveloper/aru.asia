<?php
/**
 * МОДЕЛЬ МЕРОПРИЯТИЙ
 *
 * Работает с таблицей events - хранит информацию о мероприятиях
 */

class Event {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Создает новое мероприятие
     */
    public function create($data) {
        $sql = "INSERT INTO events
                (user_id, title, description, event_date, location, latitude, longitude, price, photo, status, created_at)
                VALUES
                (:user_id, :title, :description, :event_date, :location, :latitude, :longitude, :price, :photo, :status, NOW())";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $data['user_id'],
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':event_date' => $data['event_date'],
            ':location' => $data['location'],
            ':latitude' => $data['latitude'],
            ':longitude' => $data['longitude'],
            ':price' => (isset($data['price']) && $data['price'] !== '' && is_numeric($data['price'])) ? (float)$data['price'] : 0,
            ':photo' => $data['photo'] ?? null,
            ':status' => $data['status'] ?? 'pending'
        ]);
    }

    /**
     * Получает мероприятия в радиусе 50км (только одобренные)
     */
    public function getInRadius($lat, $lon, $radius = 50) {
        // Используем разные имена параметров для каждого использования
        $sql = "SELECT e.*, u.email, u.gender,
                (6371 * acos(cos(radians(:lat1)) * cos(radians(e.latitude)) *
                cos(radians(e.longitude) - radians(:lon1)) +
                sin(radians(:lat2)) * sin(radians(e.latitude)))) AS distance
                FROM events e
                JOIN users u ON e.user_id = u.id
                WHERE e.event_date >= NOW()
                AND e.status = 'approved'
                AND e.latitude IS NOT NULL
                AND e.longitude IS NOT NULL
                HAVING distance <= :radius
                ORDER BY distance, e.event_date";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':lat1' => $lat,
            ':lat2' => $lat,
            ':lon1' => $lon,
            ':radius' => $radius
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Получает мероприятие по ID
     */
    public function getById($id) {
        $sql = "SELECT e.*, u.email, u.gender
                FROM events e
                JOIN users u ON e.user_id = u.id
                WHERE e.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Получает мероприятие по ID (алиас для совместимости)
     */
    public function findById($id) {
        return $this->getById($id);
    }

    /**
     * Получает мероприятие пользователя (только одно активное и одобренное)
     */
    public function getByUserId($userId) {
        $sql = "SELECT * FROM events
                WHERE user_id = :user_id
                AND event_date >= NOW()
                AND status = 'approved'
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch();
    }

    /**
     * Получает все мероприятия ожидающие модерации
     */
    public function getPending() {
        $sql = "SELECT e.*, u.email as user_email
                FROM events e
                LEFT JOIN users u ON e.user_id = u.id
                WHERE e.status = 'pending'
                ORDER BY e.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Одобряет мероприятие
     */
    public function approve($eventId, $moderatorId) {
        $sql = "UPDATE events
                SET status = 'approved',
                    moderated_by = :moderator_id,
                    moderated_at = NOW(),
                    rejection_reason = NULL
                WHERE id = :event_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':event_id' => $eventId,
            ':moderator_id' => $moderatorId
        ]);
    }

    /**
     * Отклоняет мероприятие
     */
    public function reject($eventId, $moderatorId, $reason) {
        $sql = "UPDATE events
                SET status = 'rejected',
                    moderated_by = :moderator_id,
                    moderated_at = NOW(),
                    rejection_reason = :reason
                WHERE id = :event_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':event_id' => $eventId,
            ':moderator_id' => $moderatorId,
            ':reason' => $reason
        ]);
    }

    /**
     * Получает мероприятия пользователя со статусами
     */
    public function getByUserIdWithStatus($userId) {
        $sql = "SELECT * FROM events WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Получает все мероприятия пользователя (алиас для совместимости)
     */
    public function getAllByUserId($userId) {
        return $this->getByUserIdWithStatus($userId);
    }

    /**
     * Обновляет мероприятие
     */
    public function update($id, $userId, $data) {
        // Если photo передан (даже если null), обновляем его
        if (isset($data['photo'])) {
            $sql = "UPDATE events
                    SET title = :title,
                        description = :description,
                        event_date = :event_date,
                        location = :location,
                        latitude = :latitude,
                        longitude = :longitude,
                        price = :price,
                        photo = :photo,
                        status = 'pending'
                    WHERE id = :id AND user_id = :user_id";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':user_id' => $userId,
                ':title' => $data['title'],
                ':description' => $data['description'],
                ':event_date' => $data['event_date'],
                ':location' => $data['location'],
                ':latitude' => $data['latitude'],
                ':longitude' => $data['longitude'],
                ':price' => (isset($data['price']) && $data['price'] !== '' && is_numeric($data['price'])) ? (float)$data['price'] : 0,
                ':photo' => $data['photo']
            ]);
        } else {
            // Если photo не передан, не обновляем его
            $sql = "UPDATE events
                    SET title = :title,
                        description = :description,
                        event_date = :event_date,
                        location = :location,
                        latitude = :latitude,
                        longitude = :longitude,
                        price = :price,
                        status = 'pending'
                    WHERE id = :id AND user_id = :user_id";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':user_id' => $userId,
                ':title' => $data['title'],
                ':description' => $data['description'],
                ':event_date' => $data['event_date'],
                ':location' => $data['location'],
                ':latitude' => $data['latitude'],
                ':longitude' => $data['longitude'],
                ':price' => (isset($data['price']) && $data['price'] !== '' && is_numeric($data['price'])) ? (float)$data['price'] : 0
            ]);
        }
    }

    /**
     * Удаляет мероприятие
     */
    public function delete($id, $userId) {
        $sql = "DELETE FROM events WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId
        ]);
    }

    /**
     * Обновляет дату мероприятия (для менеджера)
     */
    public function updateDate($id, $newDate) {
        $sql = "UPDATE events SET event_date = :event_date WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':event_date' => $newDate
        ]);
    }

    /**
     * Обновляет фотографию мероприятия без изменения статуса модерации
     */
    public function updatePhoto($id, $photo) {
        $sql = "UPDATE events SET photo = :photo WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':photo' => $photo
        ]);
    }

    /**
     * Получает все активные мероприятия (без фильтрации по геолокации)
     * Используется для неавторизованных пользователей и пользователей без геолокации
     */
    public function getAllActive() {
        $sql = "SELECT e.*, u.email, u.gender,
                CASE 
                    WHEN e.latitude IS NOT NULL AND e.longitude IS NOT NULL THEN 0
                    ELSE NULL
                END AS distance
                FROM events e
                JOIN users u ON e.user_id = u.id
                WHERE e.event_date >= NOW()
                AND e.status = 'approved'
                ORDER BY e.event_date";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Удаляет просроченные мероприятия (автоматически)
     * Не удаляет pending — они должны пройти модерацию
     */
    public function deleteExpired() {
        $sql = "DELETE FROM events WHERE event_date < NOW() AND status IN ('approved', 'rejected')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }

    /**
     * Получает общее количество активных мероприятий
     */
    public function getActiveCount() {
        $sql = "SELECT COUNT(*) as total FROM events 
                WHERE event_date >= NOW() AND status = 'approved'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    }

    /**
     * Получает общее количество всех мероприятий (включая прошедшие)
     */
    public function getTotalCount() {
        $sql = "SELECT COUNT(*) as total FROM events WHERE status = 'approved'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    }

    /**
     * Получает мероприятия по списку ID
     */
    public function getByIds($ids) {
        if (empty($ids)) {
            return [];
        }
        
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT e.*, u.email, u.gender,
                CASE 
                    WHEN e.latitude IS NOT NULL AND e.longitude IS NOT NULL THEN 0
                    ELSE NULL
                END AS distance
                FROM events e
                JOIN users u ON e.user_id = u.id
                WHERE e.id IN ($placeholders)
                ORDER BY e.event_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($ids);
        return $stmt->fetchAll();
    }
}


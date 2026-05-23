<?php

/**
 * МОДЕЛЬ СВИДАНИЙ
 *
 * Работает с таблицей dates - хранит объявления о свиданиях
 */

class Date
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Создает новое объявление о свидании
     */
    public function create($data)
    {
        $sql = "INSERT INTO dates
                (user_id, title, category_id, date_time, location, latitude, longitude, created_at)
                VALUES
                (:user_id, :title, :category_id, :date_time, :location, :latitude, :longitude, NOW())";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $data['user_id'],
            ':title' => $data['title'],
            ':category_id' => $data['category_id'] ?? null,
            ':date_time' => $data['date_time'],
            ':location' => $data['location'],
            ':latitude' => $data['latitude'],
            ':longitude' => $data['longitude']
        ]);
    }

    /**
     * Получает свидания противоположного пола в радиусе 50км
     */
    public function getInRadius($lat, $lon, $userGender, $radius = 50)
    {
        // Проверяем и приводим координаты к числовому типу
        $lat = floatval($lat);
        $lon = floatval($lon);
        $radius = floatval($radius);

        // Проверяем валидность координат
        if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
            error_log("Invalid coordinates in getInRadius: lat=$lat, lon=$lon");
            return [];
        }

        // Мужчины видят женские объявления, женщины - мужские
        $oppositeGender = $userGender === 'male' ? 'female' : 'male';

        // Используем формулу гаверсинуса для расчета расстояния
        // Исправленная формула с защитой от ошибок округления
        $sql = "SELECT d.id, d.user_id, d.title, d.category_id, d.date_time, d.location, d.latitude, d.longitude, d.created_at,
                u.email, u.age, u.gender,
                dc.name as category_name, dc.description as category_description,
                (SELECT photo FROM user_photos WHERE user_id = u.id AND photo IS NOT NULL AND TRIM(photo) <> '' ORDER BY created_at ASC LIMIT 1) as photo,
                ROUND(
                    6371 * acos(
                        GREATEST(-1.0, LEAST(1.0,
                            cos(radians(:lat1)) * cos(radians(d.latitude)) *
                            cos(radians(d.longitude) - radians(:lon1)) +
                            sin(radians(:lat2)) * sin(radians(d.latitude))
                        ))
                    ),
                    2
                ) AS distance
                FROM dates d
                JOIN users u ON d.user_id = u.id
                LEFT JOIN date_categories dc ON d.category_id = dc.id
                WHERE u.gender = :gender
                AND d.date_time >= NOW()
                AND d.latitude IS NOT NULL
                AND d.longitude IS NOT NULL
                AND CAST(d.latitude AS DECIMAL(10,8)) BETWEEN -90 AND 90
                AND CAST(d.longitude AS DECIMAL(11,8)) BETWEEN -180 AND 180
                HAVING distance <= :radius
                ORDER BY distance, d.date_time";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':lat1' => $lat,
                ':lat2' => $lat,
                ':lon1' => $lon,
                ':gender' => $oppositeGender,
                ':radius' => $radius
            ]);
            $result = $stmt->fetchAll();

            // Отладочная информация
            if (empty($result)) {
                // Проверяем, есть ли вообще свидания с такими условиями (без фильтра по расстоянию)
                $debugSql = "SELECT COUNT(*) as count,
                            COUNT(CASE WHEN u.gender = :gender THEN 1 END) as gender_count,
                            COUNT(CASE WHEN d.date_time >= NOW() THEN 1 END) as active_count,
                            COUNT(CASE WHEN d.latitude IS NOT NULL AND d.longitude IS NOT NULL THEN 1 END) as has_coords
                            FROM dates d
                            JOIN users u ON d.user_id = u.id
                            WHERE d.date_time >= NOW()
                            AND d.latitude IS NOT NULL
                            AND d.longitude IS NOT NULL";
                $debugStmt = $this->db->prepare($debugSql);
                $debugStmt->execute([':gender' => $oppositeGender]);
                $debugResult = $debugStmt->fetch();
                error_log("getInRadius debug - Total: {$debugResult['count']}, Gender match: {$debugResult['gender_count']}, Active: {$debugResult['active_count']}, Has coords: {$debugResult['has_coords']}, User coords: lat=$lat, lon=$lon, radius=$radius");
            }

            return $result !== false ? $result : [];
        } catch (Exception $e) {
            error_log("Error in getInRadius: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Params: lat=$lat, lon=$lon, gender=$oppositeGender, radius=$radius");
            return [];
        }
    }

    /**
     * Получает свидание по ID
     */
    public function getById($id)
    {
        $sql = "SELECT d.*, u.email, u.gender,
                dc.name as category_name, dc.description as category_description
                FROM dates d
                JOIN users u ON d.user_id = u.id
                LEFT JOIN date_categories dc ON d.category_id = dc.id
                WHERE d.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Получает свидание пользователя (только одно)
     */
    public function getByUserId($userId)
    {
        $sql = "SELECT * FROM dates WHERE user_id = :user_id AND date_time >= NOW() LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch();
    }

    /**
     * Получает все свидания пользователя
     */
    public function getAllByUserId($userId)
    {
        $sql = "SELECT d.id, d.user_id, d.title, d.category_id, d.date_time, d.location, d.latitude, d.longitude, d.created_at,
                dc.name as category_name, dc.description as category_description,
                (SELECT photo FROM user_photos WHERE user_id = :user_id1 AND photo IS NOT NULL AND TRIM(photo) <> '' ORDER BY created_at ASC LIMIT 1) as photo
                FROM dates d
                LEFT JOIN date_categories dc ON d.category_id = dc.id
                WHERE d.user_id = :user_id2
                ORDER BY d.date_time DESC, d.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id1' => $userId,
            ':user_id2' => $userId
        ]);
        $result = $stmt->fetchAll();
        return $result !== false ? $result : [];
    }

    /**
     * Обновляет свидание пользователя
     */
    public function update($id, $userId, $data)
    {
        $sql = "UPDATE dates
                SET title = :title,
                    category_id = :category_id,
                    date_time = :date_time,
                    location = :location,
                    latitude = :latitude,
                    longitude = :longitude
                WHERE id = :id AND user_id = :user_id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId,
            ':title' => $data['title'],
            ':category_id' => $data['category_id'] ?? null,
            ':date_time' => $data['date_time'],
            ':location' => $data['location'],
            ':latitude' => $data['latitude'],
            ':longitude' => $data['longitude']
        ]);
    }

    /**
     * Удаляет свидание
     */
    public function delete($id, $userId)
    {
        $sql = "DELETE FROM dates WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId
        ]);
    }

    /**
     * Удаляет свидание по ID (для менеджера, без проверки пользователя)
     */
    public function deleteById($id)
    {
        $sql = "DELETE FROM dates WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id
        ]);
    }

    /**
     * Обновляет дату свидания (для менеджера)
     */
    public function updateDate($id, $newDate)
    {
        $sql = "UPDATE dates SET date_time = :date_time WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':date_time' => $newDate
        ]);
    }

    /**
     * Получает все активные свидания (без фильтрации по геолокации)
     * Используется для неавторизованных пользователей
     */
    public function getAllActive()
    {
        $sql = "SELECT d.id, d.user_id, d.title, d.category_id, d.date_time, d.location, d.latitude, d.longitude, d.created_at,
                u.email, u.age, u.gender,
                dc.name as category_name, dc.description as category_description,
                (SELECT photo FROM user_photos WHERE user_id = u.id AND photo IS NOT NULL AND TRIM(photo) <> '' ORDER BY created_at ASC LIMIT 1) as photo,
                0 as distance
                FROM dates d
                JOIN users u ON d.user_id = u.id
                LEFT JOIN date_categories dc ON d.category_id = dc.id
                WHERE d.date_time >= NOW()
                ORDER BY d.date_time";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();

            // Отладочная информация
            if (empty($result)) {
                // Проверяем, есть ли вообще свидания в базе
                $debugSql = "SELECT COUNT(*) as total,
                            COUNT(CASE WHEN d.date_time >= NOW() THEN 1 END) as active,
                            COUNT(CASE WHEN d.latitude IS NOT NULL AND d.longitude IS NOT NULL THEN 1 END) as has_coords
                            FROM dates d";
                $debugStmt = $this->db->prepare($debugSql);
                $debugStmt->execute();
                $debugResult = $debugStmt->fetch();
                error_log("getAllActive debug - Total dates: {$debugResult['total']}, Active: {$debugResult['active']}, Has coords: {$debugResult['has_coords']}");
            }

            return $result !== false ? $result : [];
        } catch (Exception $e) {
            error_log("Error in getAllActive: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Получает все активные свидания противоположного пола (без фильтрации по геолокации)
     * Используется когда у пользователя нет геолокации, но есть пол
     */
    public function getAllActiveByGender($userGender)
    {
        // Мужчины видят женские объявления, женщины - мужские
        $oppositeGender = $userGender === 'male' ? 'female' : 'male';

        $sql = "SELECT d.id, d.user_id, d.title, d.category_id, d.date_time, d.location, d.latitude, d.longitude, d.created_at,
                u.email, u.age, u.gender,
                dc.name as category_name, dc.description as category_description,
                (SELECT photo FROM user_photos WHERE user_id = u.id AND photo IS NOT NULL AND TRIM(photo) <> '' ORDER BY created_at ASC LIMIT 1) as photo,
                0 as distance
                FROM dates d
                JOIN users u ON d.user_id = u.id
                LEFT JOIN date_categories dc ON d.category_id = dc.id
                WHERE u.gender = :gender
                AND d.date_time >= NOW()
                ORDER BY d.date_time";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':gender' => $oppositeGender
            ]);
            $result = $stmt->fetchAll();

            // Отладочная информация
            if (empty($result)) {
                error_log("getAllActiveByGender: No dates found for gender=$oppositeGender");
                // Проверяем, есть ли вообще свидания
                $debugSql = "SELECT COUNT(*) as total,
                            COUNT(CASE WHEN u.gender = :gender THEN 1 END) as gender_match,
                            COUNT(CASE WHEN d.date_time >= NOW() THEN 1 END) as active
                            FROM dates d
                            JOIN users u ON d.user_id = u.id";
                $debugStmt = $this->db->prepare($debugSql);
                $debugStmt->execute([':gender' => $oppositeGender]);
                $debugResult = $debugStmt->fetch();
                error_log("getAllActiveByGender debug - Total dates: {$debugResult['total']}, Gender match: {$debugResult['gender_match']}, Active: {$debugResult['active']}");
            }

            return $result !== false ? $result : [];
        } catch (Exception $e) {
            error_log("Error in getAllActiveByGender: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Удаляет просроченные свидания (автоматически)
     * Сообщения связанные со свиданиями удаляются автоматически благодаря ON DELETE CASCADE
     * @return int Количество удаленных записей
     */
    public function deleteExpired()
    {
        try {
            $sql = "DELETE FROM dates WHERE date_time < NOW()";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            // Возвращаем количество удаленных записей
            $deletedCount = $stmt->rowCount();
            if ($deletedCount > 0) {
                error_log("Deleted $deletedCount expired date(s)");
            }

            return $deletedCount;
        } catch (Exception $e) {
            error_log("Error deleting expired dates: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Получает общее количество активных свиданий
     */
    public function getActiveCount()
    {
        $sql = "SELECT COUNT(*) as total FROM dates WHERE date_time >= NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    }

    /**
     * Получает общее количество всех свиданий
     */
    public function getTotalCount()
    {
        $sql = "SELECT COUNT(*) as total FROM dates";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    }

    /**
     * Получает свидания по списку ID
     */
    public function getByIds($ids)
    {
        if (empty($ids)) {
            return [];
        }

        // Убеждаемся, что все ID - числа
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, function ($id) {
            return $id > 0;
        });
        $ids = array_values(array_unique($ids)); // Убираем дубликаты и переиндексируем

        if (empty($ids)) {
            error_log('getByIds: после фильтрации не осталось валидных ID');
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        // Получаем только существующие свидания
        $sql = "SELECT d.id, d.user_id, d.title, d.category_id, d.date_time, d.location, d.latitude, d.longitude, d.created_at,
                u.email, u.age, u.gender,
                dc.name as category_name, dc.description as category_description,
                (SELECT photo FROM user_photos WHERE user_id = u.id AND photo IS NOT NULL AND TRIM(photo) <> '' ORDER BY created_at ASC LIMIT 1) as photo,
                0 as distance
                FROM dates d
                JOIN users u ON d.user_id = u.id
                LEFT JOIN date_categories dc ON d.category_id = dc.id
                WHERE d.id IN ($placeholders)
                ORDER BY d.date_time DESC";

        error_log('getByIds запрос: ' . $sql);
        error_log('getByIds параметры: ' . implode(', ', $ids));

        $stmt = $this->db->prepare($sql);
        $stmt->execute($ids);
        $result = $stmt->fetchAll();

        error_log('getByIds найдено записей: ' . count($result));
        foreach ($result as $date) {
            error_log('  - id: ' . $date['id'] . ', title: ' . $date['title'] . ', user_id: ' . $date['user_id']);
        }

        // Проверяем, все ли ID были найдены
        $foundIds = array_column($result, 'id');
        $missingIds = array_diff($ids, $foundIds);
        if (!empty($missingIds)) {
            error_log('ВНИМАНИЕ: Не найдены свидания с ID: ' . implode(', ', $missingIds));
        }

        return $result !== false ? $result : [];
    }
}

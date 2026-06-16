<?php

/**
 * МОДЕЛЬ ПОЛЬЗОВАТЕЛЯ
 *
 * Этот класс работает с таблицей users в базе данных.
 * Здесь методы для создания, чтения, обновления пользователей.
 */

class User
{
    private $db; // Подключение к базе данных

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Создает нового пользователя
     */
    public function create($email, $password, $token, $ip = null, $country = null)
    {
        // Нормализуем email: убираем пробелы и приводим к нижнему регистру
        $email = trim(strtolower($email));

        $sql = "INSERT INTO users (email, password, verification_token, registration_ip, registration_country, created_at)
                VALUES (:email, :password, :token, :ip, :country, NOW())";

        $stmt = $this->db->prepare($sql);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        return $stmt->execute([
            ':email' => $email,
            ':password' => $hashedPassword,
            ':token' => $token,
            ':ip' => $ip,
            ':country' => $country
        ]);
    }

    /**
     * Находит пользователя по email
     */
    public function findByEmail($email)
    {
        // Нормализуем email: убираем пробелы и приводим к нижнему регистру
        $email = trim(strtolower($email));

        // Ищем по нормализованному email (для совместимости с существующими данными используем LOWER(TRIM))
        $sql = "SELECT * FROM users WHERE LOWER(TRIM(email)) = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    /**
     * Находит пользователя по ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Подтверждает email пользователя
     */
    public function verifyEmail($token)
    {
        $sql = "UPDATE users SET email_verified = 1, verification_token = NULL
                WHERE verification_token = :token";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':token' => $token]);
    }

    /**
     * Обновляет профиль пользователя
     * Если профиль был заблокирован и пользователь сохраняет изменения, автоматически снимаем блокировку
     */
    public function updateProfile($userId, $data)
    {
        // Проверяем, был ли профиль заблокирован
        $user = $this->findById($userId);
        $wasBlocked = $user && ($user['profile_blocked'] ?? 0) == 1;

        // Если профиль был заблокирован, снимаем блокировку после обновления
        if ($wasBlocked) {
            $sql = "UPDATE users SET
                    full_name = :full_name,
                    gender = :gender,
                    age = :age,
                    marital_status = :marital_status,
                    country = :country,
                    city = :city,
                    about = :about,
                    latitude = :latitude,
                    longitude = :longitude,
                    age_changes_count = :age_changes_count,
                    admin_remark = NULL,
                    remark_type = NULL,
                    profile_blocked = 0,
                    updated_at = NOW()
                    WHERE id = :id";
        } else {
            $sql = "UPDATE users SET
                    full_name = :full_name,
                    gender = :gender,
                    age = :age,
                    marital_status = :marital_status,
                    country = :country,
                    city = :city,
                    about = :about,
                    latitude = :latitude,
                    longitude = :longitude,
                    age_changes_count = :age_changes_count,
                    updated_at = NOW()
                    WHERE id = :id";
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $userId,
            ':full_name' => $data['full_name'] ?? null,
            ':gender' => $data['gender'] ?? null,
            ':age' => $data['age'] ?? null,
            ':marital_status' => $data['marital_status'] ?? null,
            ':country' => $data['country'] ?? null,
            ':city' => $data['city'] ?? null,
            ':about' => $data['about'] ?? null,
            ':latitude' => $data['latitude'] ?? null,
            ':longitude' => $data['longitude'] ?? null,
            ':age_changes_count' => $data['age_changes_count'] ?? 0
        ]);
    }

    /**
     * Пользователи для ленты (лендинг / платформа): с фото и без.
     * Без фото внизу списка (после всех с валидным фото).
     * Если указан пол текущего пользователя — только противоположный пол.
     * Если указана страна — только из той же страны.
     * Исключает заблокированных (и по правилам ниже текущего пользователя).
     */
    public function getAllWithPhotos($limit = 20, $excludeUserId = null, $userGender = null, $userCountry = null)
    {
        $withPhoto = [];
        $brokenPhoto = [];
        $offset = 0;
        $pageSize = max($limit, 50);

        while (count($withPhoto) < $limit) {
            $page = $this->fetchFeedUsers($pageSize, $excludeUserId, $userGender, $userCountry, true, $offset);
            if ($page === []) {
                break;
            }

            foreach ($page as $user) {
                if ($this->userHasExistingPhotoFile($user)) {
                    $withPhoto[] = $user;
                    if (count($withPhoto) >= $limit) {
                        break 2;
                    }
                    continue;
                }

                if (!empty($user['main_photo']) && trim((string) $user['main_photo']) !== '') {
                    $brokenPhoto[] = $this->clearMissingPhoto($user);
                }
            }

            $offset += count($page);
            if (count($page) < $pageSize) {
                break;
            }
        }

        $result = array_slice($withPhoto, 0, $limit);
        $remaining = $limit - count($result);

        if ($remaining > 0 && $brokenPhoto !== []) {
            $result = array_merge($result, array_slice($brokenPhoto, 0, $remaining));
            $remaining = $limit - count($result);
        }

        if ($remaining > 0) {
            $withoutPhoto = $this->fetchFeedUsers($remaining, $excludeUserId, $userGender, $userCountry, false);
            $result = array_merge($result, $withoutPhoto);
        }

        return array_slice($result, 0, $limit);
    }

    /**
     * Проверяет, что у пользователя есть фото и файл существует на диске.
     */
    private function userHasExistingPhotoFile(array $user): bool
    {
        $photo = trim((string) ($user['main_photo'] ?? ''));
        if ($photo === '') {
            return false;
        }

        $projectRoot = dirname(__DIR__, 2);
        $photoPath = $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, UPLOAD_DIR . 'photos/' . $photo);

        return is_file($photoPath);
    }

    /**
     * Сбрасывает main_photo, если файла нет на диске.
     */
    private function clearMissingPhoto(array $user): array
    {
        if (!$this->userHasExistingPhotoFile($user)) {
            $user['main_photo'] = null;
        }

        return $user;
    }

    /**
     * Выборка пользователей для ленты: только с фото или только без.
     */
    private function fetchFeedUsers($limit, $excludeUserId, $userGender, $userCountry, $withPhoto, $offset = 0)
    {
        $sql = "SELECT u.*,
                (SELECT photo FROM user_photos WHERE user_id = u.id AND photo IS NOT NULL AND TRIM(photo) <> '' ORDER BY created_at ASC LIMIT 1) as main_photo
                FROM users u
                WHERE u.email_verified = 1
                AND (u.marital_status IS NULL OR u.marital_status != 'married')
                AND (u.profile_blocked IS NULL OR u.profile_blocked = 0)";

        $params = [];

        if ($excludeUserId) {
            $sql .= " AND u.id != :exclude_user_id";
            $params[':exclude_user_id'] = $excludeUserId;

            $sql .= " AND NOT EXISTS (
                        SELECT 1 FROM blocked_users bu1
                        WHERE bu1.user_id = :exclude_user_id2
                        AND bu1.blocked_user_id = u.id
                     )";
            $params[':exclude_user_id2'] = $excludeUserId;

            $sql .= " AND NOT EXISTS (
                        SELECT 1 FROM blocked_users bu2
                        WHERE bu2.blocked_user_id = :exclude_user_id3
                        AND bu2.user_id = u.id
                     )";
            $params[':exclude_user_id3'] = $excludeUserId;
        }

        if ($userGender) {
            $oppositeGender = $userGender === 'male' ? 'female' : 'male';
            $sql .= " AND u.gender = :gender";
            $params[':gender'] = $oppositeGender;
        }

        if ($userCountry) {
            $sql .= " AND u.country = :country";
            $params[':country'] = $userCountry;
        }

        if ($withPhoto) {
            $sql .= " AND EXISTS (
                        SELECT 1 FROM user_photos up
                        WHERE up.user_id = u.id
                        AND up.photo IS NOT NULL
                        AND TRIM(up.photo) <> ''
                    )";
        } else {
            $sql .= " AND NOT EXISTS (
                        SELECT 1 FROM user_photos up
                        WHERE up.user_id = u.id
                        AND up.photo IS NOT NULL
                        AND TRIM(up.photo) <> ''
                    )";
        }

        $sql .= " ORDER BY u.created_at DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Получает пользователей в радиусе 50км
     * Если указан пол текущего пользователя, показывает только противоположный пол
     * Исключает заблокированных пользователей
     */
    public function getUsersInRadius($lat, $lon, $radius = 50, $excludeUserId = null, $userGender = null)
    {
        // Используем формулу для поиска в радиусе
        // Используем разные имена параметров для каждого использования
        $sql = "SELECT *,
                (6371 * acos(cos(radians(:lat1)) * cos(radians(latitude)) *
                cos(radians(longitude) - radians(:lon1)) +
                sin(radians(:lat2)) * sin(radians(latitude)))) AS distance
                FROM users
                WHERE email_verified = 1
                AND latitude IS NOT NULL
                AND longitude IS NOT NULL
                AND (marital_status IS NULL OR marital_status != 'married')
                AND (profile_blocked IS NULL OR profile_blocked = 0)";

        $params = [
            ':lat1' => $lat,
            ':lat2' => $lat,
            ':lon1' => $lon,
            ':radius' => $radius
        ];

        // Исключаем текущего пользователя
        if ($excludeUserId) {
            $sql .= " AND id != :exclude_user_id";
            $params[':exclude_user_id'] = $excludeUserId;

            // Исключаем пользователей, которых заблокировал текущий пользователь
            $sql .= " AND NOT EXISTS (
                        SELECT 1 FROM blocked_users bu1
                        WHERE bu1.user_id = :exclude_user_id2
                        AND bu1.blocked_user_id = users.id
                     )";
            $params[':exclude_user_id2'] = $excludeUserId;

            // Исключаем пользователей, которые заблокировали текущего пользователя
            $sql .= " AND NOT EXISTS (
                        SELECT 1 FROM blocked_users bu2
                        WHERE bu2.blocked_user_id = :exclude_user_id3
                        AND bu2.user_id = users.id
                     )";
            $params[':exclude_user_id3'] = $excludeUserId;
        }

        // Фильтруем по противоположному полу
        if ($userGender) {
            $oppositeGender = $userGender === 'male' ? 'female' : 'male';
            $sql .= " AND gender = :gender";
            $params[':gender'] = $oppositeGender;
        }

        $sql .= " HAVING distance <= :radius ORDER BY distance";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Обновляет роль пользователя
     */
    public function updateRole($userId, $role)
    {
        $allowedRoles = ['user', 'manager'];
        if (!in_array($role, $allowedRoles)) {
            return false;
        }

        $sql = "UPDATE users SET role = :role, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $userId,
            ':role' => $role
        ]);
    }

    /**
     * Получает роль пользователя
     */
    public function getRole($userId)
    {
        $user = $this->findById($userId);
        return $user['role'] ?? 'user';
    }

    /**
     * Сохраняет remember token для пользователя
     */
    public function saveRememberToken($userId, $token)
    {
        $sql = "UPDATE users SET remember_token = :token, remember_token_expires = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':token' => $token,
            ':id' => $userId
        ]);
    }

    /**
     * Находит пользователя по remember token
     */
    public function findByRememberToken($token)
    {
        $sql = "SELECT * FROM users WHERE remember_token = :token AND remember_token_expires > NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':token' => $token]);
        return $stmt->fetch();
    }

    /**
     * Удаляет remember token пользователя
     */
    public function clearRememberToken($userId)
    {
        $sql = "UPDATE users SET remember_token = NULL, remember_token_expires = NULL WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $userId]);
    }

    /**
     * Сохраняет токен восстановления пароля для пользователя
     */
    public function savePasswordResetToken($userId, $token)
    {
        // Токен действителен 1 час
        $expires = date('Y-m-d H:i:s', time() + 3600);
        $sql = "UPDATE users SET password_reset_token = :token, password_reset_expires = :expires WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':token' => $token,
            ':expires' => $expires,
            ':id' => $userId
        ]);
    }

    /**
     * Находит пользователя по токену восстановления пароля
     */
    public function findByPasswordResetToken($token)
    {
        $sql = "SELECT * FROM users WHERE password_reset_token = :token AND password_reset_expires > NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':token' => $token]);
        return $stmt->fetch();
    }

    /**
     * Обновляет пароль пользователя
     */
    public function updatePassword($userId, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = :password, password_reset_token = NULL, password_reset_expires = NULL WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':password' => $hashedPassword,
            ':id' => $userId
        ]);
    }

    /**
     * Удаляет пользователя и все связанные данные
     * Также удаляет физические файлы фотографий
     */
    public function delete($userId)
    {
        // Получаем все фотографии пользователя перед удалением
        $photoModel = new UserPhoto();
        $photos = $photoModel->getByUserId($userId);

        // Определяем корень проекта (на уровень выше app/)
        $projectRoot = dirname(__DIR__, 2);

        // Удаляем физические файлы фотографий
        foreach ($photos as $photo) {
            $photoPath = $projectRoot . '/' . UPLOAD_DIR . 'photos/' . $photo['photo'];
            if (file_exists($photoPath)) {
                @unlink($photoPath);
            }
        }

        // Удаляем пользователя из базы данных
        // CASCADE автоматически удалит связанные записи (events, dates, messages, user_photos, blocked_users)
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $userId]);
    }

    /**
     * Устанавливает замечание от администратора и блокирует профиль
     */
    public function setAdminRemark($userId, $remark, $remarkType = null)
    {
        $sql = "UPDATE users SET admin_remark = :remark, remark_type = :remark_type, profile_blocked = 1, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':remark' => $remark,
            ':remark_type' => $remarkType,
            ':id' => $userId
        ]);
    }

    /**
     * Снимает блокировку профиля (когда пользователь исправил замечание)
     */
    public function clearAdminRemark($userId)
    {
        $sql = "UPDATE users SET admin_remark = NULL, remark_type = NULL, profile_blocked = 0, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $userId]);
    }

    /**
     * Проверяет, заблокирован ли профиль пользователя
     */
    public function isProfileBlocked($userId)
    {
        $user = $this->findById($userId);
        return $user && ($user['profile_blocked'] ?? 0) == 1;
    }

    /**
     * Получает замечание от администратора для пользователя
     */
    public function getAdminRemark($userId)
    {
        $user = $this->findById($userId);
        return $user['admin_remark'] ?? null;
    }

    /**
     * Получает тип замечания от администратора для пользователя
     */
    public function getRemarkType($userId)
    {
        $user = $this->findById($userId);
        return $user['remark_type'] ?? null;
    }

    /**
     * Получает общее количество зарегистрированных и подтвержденных пользователей
     */
    public function getTotalCount()
    {
        $sql = "SELECT COUNT(*) as total FROM users WHERE email_verified = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    }

    /**
     * Проверяет, заполнен ли профиль пользователя (отредактирован)
     * Профиль считается заполненным, если указаны все обязательные поля:
     * full_name, gender, age, marital_status, country, city
     */
    public function isProfileComplete($userId)
    {
        $user = $this->findById($userId);
        if (!$user) {
            return false;
        }

        // Проверяем наличие всех обязательных полей
        return !empty($user['full_name']) &&
               !empty($user['gender']) &&
               !empty($user['age']) &&
               !empty($user['marital_status']) &&
               !empty($user['country']) &&
               !empty($user['city']);
    }
}

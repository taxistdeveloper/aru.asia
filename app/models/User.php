<?php

/**
 * МОДЕЛЬ ПОЛЬЗОВАТЕЛЯ
 *
 * Этот класс работает с таблицей users в базе данных.
 * Здесь методы для создания, чтения, обновления пользователей.
 */

class User
{
    /** Сколько месяцев хранить аккаунт после удаления (для восстановления по email) */
    public const SOFT_DELETE_MONTHS = 6;

    /** Считаем пользователя онлайн, если был активен за последние N минут */
    public const ONLINE_THRESHOLD_MINUTES = 5;

    private static ?bool $lastActivityColumnReady = null;

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
        $aruCode = $this->generateUniqueAruCode();

        $sql = "INSERT INTO users (email, password, verification_token, registration_ip, registration_country, aru_code, created_at)
                VALUES (:email, :password, :token, :ip, :country, :aru_code, NOW())";

        $stmt = $this->db->prepare($sql);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        return $stmt->execute([
            ':email' => $email,
            ':password' => $hashedPassword,
            ':token' => $token,
            ':ip' => $ip,
            ':country' => $country,
            ':aru_code' => $aruCode,
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
     * Сортировка: онлайн → недавняя активность → новые регистрации.
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
     * Сортировка: онлайн сверху → недавняя активность → новые регистрации.
     */
    private function fetchFeedUsers($limit, $excludeUserId, $userGender, $userCountry, $withPhoto, $offset = 0)
    {
        self::ensureLastActivityColumn();

        $onlineMinutes = (int) self::ONLINE_THRESHOLD_MINUTES;

        $sql = "SELECT u.*,
                (SELECT photo FROM user_photos WHERE user_id = u.id AND photo IS NOT NULL AND TRIM(photo) <> '' ORDER BY created_at ASC LIMIT 1) as main_photo
                FROM users u
                WHERE u.email_verified = 1
                AND u.deleted_at IS NULL
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

        $sql .= " ORDER BY
                (u.last_activity_at IS NOT NULL AND u.last_activity_at >= DATE_SUB(NOW(), INTERVAL {$onlineMinutes} MINUTE)) DESC,
                COALESCE(u.last_activity_at, u.created_at) DESC,
                u.created_at DESC
                LIMIT :limit OFFSET :offset";
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
                AND deleted_at IS NULL
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
        $sql = "SELECT * FROM users WHERE remember_token = :token AND remember_token_expires > NOW() AND deleted_at IS NULL";
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
     * Проверяет, помечен ли аккаунт как удалённый
     */
    public function isSoftDeleted(array $user): bool
    {
        return !empty($user['deleted_at']);
    }

    /**
     * Можно ли восстановить аккаунт (срок хранения ещё не истёк)
     */
    public function canRestore(array $user): bool
    {
        if (!$this->isSoftDeleted($user)) {
            return false;
        }

        $deletedAt = strtotime($user['deleted_at']);
        if ($deletedAt === false) {
            return false;
        }

        $expiresAt = strtotime('+' . self::SOFT_DELETE_MONTHS . ' months', $deletedAt);
        return $expiresAt !== false && time() < $expiresAt;
    }

    /**
     * Мягкое удаление: аккаунт сохраняется 6 месяцев для входа по email.
     * Переписка, фото, свидания и мероприятия удаляются сразу.
     */
    public function softDelete($userId)
    {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return false;
        }

        $user = $this->findById($userId);
        if (!$user || $this->isSoftDeleted($user)) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            $this->purgeUserContent($userId);
            $this->clearRememberToken($userId);

            $sql = "UPDATE users SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND deleted_at IS NULL";
            $stmt = $this->db->prepare($sql);
            $ok = $stmt->execute([':id' => $userId]);

            if (!$ok || $stmt->rowCount() === 0) {
                $this->db->rollBack();
                return false;
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('User::softDelete error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Восстанавливает аккаунт после мягкого удаления
     */
    public function restore($userId)
    {
        $sql = "UPDATE users SET deleted_at = NULL, updated_at = NOW() WHERE id = :id AND deleted_at IS NOT NULL";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $userId]);
    }

    /**
     * Удаляет контент пользователя (фото, сообщения, свидания, мероприятия, push-токены)
     */
    public function purgeUserContent($userId)
    {
        $userId = (int)$userId;
        $projectRoot = dirname(__DIR__, 2);

        // Фото: файлы + строки (даже если getByUserId уже подчистил «битые»)
        $photoStmt = $this->db->prepare("SELECT photo FROM user_photos WHERE user_id = :user_id");
        $photoStmt->execute([':user_id' => $userId]);
        foreach ($photoStmt->fetchAll() as $photo) {
            $filename = $photo['photo'] ?? '';
            if ($filename !== '') {
                $photoPath = $projectRoot . '/' . UPLOAD_DIR . 'photos/' . $filename;
                if (file_exists($photoPath)) {
                    @unlink($photoPath);
                }
            }
        }
        $this->db->prepare("DELETE FROM user_photos WHERE user_id = :user_id")->execute([':user_id' => $userId]);

        // Сообщения (в т.ч. from_user_id без CASCADE FK)
        $this->db->prepare(
            "DELETE FROM messages WHERE from_user_id = :uid1 OR to_user_id = :uid2"
        )->execute([':uid1' => $userId, ':uid2' => $userId]);

        $this->db->prepare("DELETE FROM dates WHERE user_id = :user_id")->execute([':user_id' => $userId]);
        $this->db->prepare("DELETE FROM events WHERE user_id = :user_id")->execute([':user_id' => $userId]);
        $this->db->prepare(
            "DELETE FROM blocked_users WHERE user_id = :uid1 OR blocked_user_id = :uid2"
        )->execute([':uid1' => $userId, ':uid2' => $userId]);

        $this->db->prepare(
            "DELETE FROM push_notification_tokens WHERE user_id = :user_id"
        )->execute([':user_id' => $userId]);
    }

    /**
     * Окончательно удаляет аккаунты, у которых истёк срок хранения (6 месяцев)
     */
    public function purgeExpiredSoftDeleted()
    {
        $sql = "SELECT id FROM users
                WHERE deleted_at IS NOT NULL
                AND deleted_at < DATE_SUB(NOW(), INTERVAL " . (int)self::SOFT_DELETE_MONTHS . " MONTH)";
        $stmt = $this->db->query($sql);
        $ids = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];

        $purged = 0;
        foreach ($ids as $id) {
            if ($this->delete((int)$id)) {
                $purged++;
            }
        }

        return $purged;
    }

    /**
     * Удаляет пользователя и все связанные данные
     * Также удаляет физические файлы фотографий
     */
    public function delete($userId)
    {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return false;
        }

        // Контент и сообщения (from_user_id без CASCADE) — до удаления строки users
        $this->purgeUserContent($userId);

        // Удаляем пользователя из базы данных
        // CASCADE удалит оставшиеся связанные записи
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
        $sql = "SELECT COUNT(*) as total FROM users WHERE email_verified = 1 AND deleted_at IS NULL";
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

    /**
     * Публичный номер: aru + случайный код (например aru789136)
     */
    public static function formatAruNumber($aruCode)
    {
        $code = preg_replace('/\D+/', '', (string) $aruCode);
        if ($code === '') {
            return null;
        }
        return 'aru' . $code;
    }

    /**
     * Извлекает цифровой код из запроса: aru789136, aru 789136, 789136
     */
    public static function parseAruNumber($query)
    {
        $query = trim((string) $query);
        if ($query === '') {
            return null;
        }

        if (preg_match('/^aru[:_\s-]*(\d+)$/iu', $query, $m)) {
            return $m[1];
        }

        if (preg_match('/^\d+$/', $query)) {
            return $query;
        }

        return null;
    }

    /**
     * Генерирует уникальный 6-значный код
     */
    public function generateUniqueAruCode()
    {
        for ($attempt = 0; $attempt < 50; $attempt++) {
            $code = (string) random_int(100000, 999999);
            $sql = "SELECT id FROM users WHERE aru_code = :code LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':code' => $code]);
            if (!$stmt->fetch()) {
                return $code;
            }
        }

        // Запасной вариант: более длинный код
        return (string) random_int(1000000, 999999999);
    }

    /**
     * Гарантирует наличие aru_code у пользователя (для старых аккаунтов)
     */
    public function ensureAruCode($userId)
    {
        $user = $this->findById($userId);
        if (!$user) {
            return null;
        }

        if (!empty($user['aru_code'])) {
            return $user['aru_code'];
        }

        $code = $this->generateUniqueAruCode();
        $sql = "UPDATE users SET aru_code = :code, updated_at = NOW() WHERE id = :id AND (aru_code IS NULL OR aru_code = '')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':code' => $code,
            ':id' => (int) $userId,
        ]);

        $fresh = $this->findById($userId);
        return $fresh['aru_code'] ?? $code;
    }

    /**
     * Находит пользователя по коду номера (без префикса aru)
     */
    public function findByAruCode($code)
    {
        $code = preg_replace('/\D+/', '', (string) $code);
        if ($code === '') {
            return null;
        }

        $sql = "SELECT * FROM users WHERE aru_code = :code LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':code' => $code]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Включён ли номер для публичного поиска
     */
    public function isAruNumberEnabled($userId)
    {
        $user = $this->findById($userId);
        if (!$user) {
            return false;
        }
        // По умолчанию включён (для строк до миграции)
        return (int) ($user['aru_number_enabled'] ?? 1) === 1;
    }

    /**
     * Включить / отключить публичный поиск по номеру
     */
    public function setAruNumberEnabled($userId, $enabled)
    {
        $sql = "UPDATE users SET aru_number_enabled = :enabled, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => (int) $userId,
            ':enabled' => $enabled ? 1 : 0,
        ]);
    }

    /**
     * Поиск пользователя по номеру aru789136.
     * Возвращает: ['status' => 'found'|'disabled'|'not_found', 'user' => ?array, 'aru_number' => ?string]
     */
    public function searchByAruNumber($query)
    {
        $code = self::parseAruNumber($query);
        if ($code === null || $code === '') {
            return [
                'status' => 'not_found',
                'user' => null,
                'aru_number' => null,
            ];
        }

        $aruNumber = self::formatAruNumber($code);
        $user = $this->findByAruCode($code);

        if (
            !$user
            || !empty($user['deleted_at'])
            || (int) ($user['email_verified'] ?? 0) !== 1
            || (int) ($user['profile_blocked'] ?? 0) === 1
        ) {
            return [
                'status' => 'not_found',
                'user' => null,
                'aru_number' => $aruNumber,
            ];
        }

        if ((int) ($user['aru_number_enabled'] ?? 1) !== 1) {
            return [
                'status' => 'disabled',
                'user' => null,
                'aru_number' => $aruNumber,
            ];
        }

        return [
            'status' => 'found',
            'user' => $user,
            'aru_number' => $aruNumber,
        ];
    }

    /**
     * Добавляет колонку last_activity_at, если её ещё нет.
     */
    public static function ensureLastActivityColumn(): void
    {
        if (self::$lastActivityColumnReady === true) {
            return;
        }

        try {
            $db = Database::getInstance()->getConnection();
            $check = $db->query("SHOW COLUMNS FROM users LIKE 'last_activity_at'");
            if ($check->rowCount() === 0) {
                $db->exec("ALTER TABLE users ADD COLUMN last_activity_at DATETIME NULL DEFAULT NULL");
                $db->exec("CREATE INDEX idx_users_last_activity_at ON users (last_activity_at)");
            }

            self::$lastActivityColumnReady = true;
        } catch (Exception $e) {
            error_log('User::ensureLastActivityColumn error: ' . $e->getMessage());
            self::$lastActivityColumnReady = false;
        }
    }

    /**
     * Обновляет время последней активности (не чаще раза в минуту на сессию).
     */
    public static function touchLastActivity(?int $userId = null): void
    {
        if ($userId === null) {
            $userId = Helper::getUserId();
        }

        if (!$userId) {
            return;
        }

        $now = time();
        $lastTouch = (int) ($_SESSION['last_activity_touch'] ?? 0);
        if ($now - $lastTouch < 60) {
            return;
        }

        $_SESSION['last_activity_touch'] = $now;

        self::ensureLastActivityColumn();

        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare('UPDATE users SET last_activity_at = NOW() WHERE id = :id');
            $stmt->execute([':id' => $userId]);
        } catch (Exception $e) {
            error_log('User::touchLastActivity error: ' . $e->getMessage());
        }
    }

    /**
     * Проверяет, онлайн ли пользователь по last_activity_at.
     */
    public static function isOnline($user): bool
    {
        $lastActivity = is_array($user)
            ? ($user['last_activity_at'] ?? null)
            : $user;

        if (empty($lastActivity)) {
            return false;
        }

        $timestamp = strtotime((string) $lastActivity);
        if ($timestamp === false) {
            return false;
        }

        return (time() - $timestamp) <= (self::ONLINE_THRESHOLD_MINUTES * 60);
    }
}

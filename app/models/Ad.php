<?php
/**
 * МОДЕЛЬ РЕКЛАМЫ
 *
 * Работает с таблицей ads - хранит рекламные баннеры
 */

class Ad {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Создает рекламный баннер
     */
    public function create($data) {
        $sql = "INSERT INTO ads
                (advertiser_name, advertiser_email, country, city, image_path, video_path,
                 start_date, end_date, click_url, created_at)
                VALUES
                (:advertiser_name, :advertiser_email, :country, :city, :image_path, :video_path,
                 :start_date, :end_date, :click_url, NOW())";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':advertiser_name' => $data['advertiser_name'],
            ':advertiser_email' => $data['advertiser_email'],
            ':country' => $data['country'],
            ':city' => $data['city'],
            ':image_path' => $data['image_path'] ?? null,
            ':video_path' => $data['video_path'] ?? null,
            ':start_date' => $data['start_date'],
            ':end_date' => $data['end_date'],
            ':click_url' => $data['click_url']
        ]);
    }

    /**
     * Получает активную рекламу для страны
     */
    public function getActiveByCountry($country) {
        $sql = "SELECT * FROM ads
                WHERE country = :country
                AND status = 'active'
                AND start_date <= NOW()
                AND end_date >= NOW()
                ORDER BY created_at DESC
                LIMIT 5";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':country' => $country]);
        return $stmt->fetchAll();
    }

    /**
     * Получает активную рекламу для страны и города пользователя, затем рекламы только для страны, затем общие рекламы
     */
    public function getActiveForUser($country = null, $city = null, $limit = 10) {
        if ($country && $city) {
            // Сначала получаем рекламы для города пользователя
            $sql = "SELECT * FROM ads
                    WHERE status = 'active'
                    AND start_date <= NOW()
                    AND end_date >= NOW()
                    AND country = :country
                    AND city = :city
                    ORDER BY created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':country', $country, PDO::PARAM_STR);
            $stmt->bindValue(':city', $city, PDO::PARAM_STR);
            $stmt->execute();
            $cityAds = $stmt->fetchAll();

            // Если рекламы для города есть, но меньше лимита, добавляем рекламы для страны (без города)
            if (count($cityAds) < $limit) {
                $remainingLimit = $limit - count($cityAds);

                $sql = "SELECT * FROM ads
                        WHERE status = 'active'
                        AND start_date <= NOW()
                        AND end_date >= NOW()
                        AND country = :country
                        AND (city IS NULL OR city = '' OR city = 'ВСЕ ГОРОДА')
                        AND id NOT IN (" . implode(',', array_column($cityAds, 'id') ?: [0]) . ")
                        ORDER BY created_at DESC
                        LIMIT :limit";

                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':country', $country, PDO::PARAM_STR);
                $stmt->bindValue(':limit', $remainingLimit, PDO::PARAM_INT);
                $stmt->execute();
                $countryAds = $stmt->fetchAll();

                // Объединяем результаты: сначала рекламы для города, затем для страны
                $combinedAds = array_merge($cityAds, $countryAds);

                // Если все еще меньше лимита, добавляем общие рекламы
                if (count($combinedAds) < $limit) {
                    $remainingLimit = $limit - count($combinedAds);

                    $sql = "SELECT * FROM ads
                            WHERE status = 'active'
                            AND start_date <= NOW()
                            AND end_date >= NOW()
                            AND (country IS NULL OR country = '')
                            AND id NOT IN (" . implode(',', array_column($combinedAds, 'id') ?: [0]) . ")
                            ORDER BY created_at DESC
                            LIMIT :limit";

                    $stmt = $this->db->prepare($sql);
                    $stmt->bindValue(':limit', $remainingLimit, PDO::PARAM_INT);
                    $stmt->execute();
                    $generalAds = $stmt->fetchAll();

                    // Объединяем все результаты
                    return array_merge($combinedAds, $generalAds);
                } else {
                    return $combinedAds;
                }
            } else {
                // Если рекламы для города достаточно, возвращаем только их (с лимитом)
                return array_slice($cityAds, 0, $limit);
            }
        } elseif ($country) {
            // Если город не указан, но страна есть - получаем рекламы для страны
            $sql = "SELECT * FROM ads
                    WHERE status = 'active'
                    AND start_date <= NOW()
                    AND end_date >= NOW()
                    AND country = :country
                    ORDER BY created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':country', $country, PDO::PARAM_STR);
            $stmt->execute();
            $countryAds = $stmt->fetchAll();

            // Если рекламы для страны есть, но меньше лимита, добавляем общие рекламы
            if (count($countryAds) < $limit) {
                $remainingLimit = $limit - count($countryAds);

                $sql = "SELECT * FROM ads
                        WHERE status = 'active'
                        AND start_date <= NOW()
                        AND end_date >= NOW()
                        AND (country IS NULL OR country = '')
                        AND id NOT IN (" . implode(',', array_column($countryAds, 'id') ?: [0]) . ")
                        ORDER BY created_at DESC
                        LIMIT :limit";

                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':limit', $remainingLimit, PDO::PARAM_INT);
                $stmt->execute();
                $generalAds = $stmt->fetchAll();

                // Объединяем результаты: сначала рекламы для страны, затем общие
                return array_merge($countryAds, $generalAds);
            } else {
                // Если рекламы для страны достаточно, возвращаем только их (с лимитом)
                return array_slice($countryAds, 0, $limit);
            }
        } else {
            // Если страна не указана, показываем все активные рекламы
            return $this->getAllActive($limit);
        }
    }

    /**
     * Получает все активные рекламы (для лендинга)
     */
    public function getAllActive($limit = 10) {
        $sql = "SELECT * FROM ads
                WHERE status = 'active'
                AND start_date <= NOW()
                AND end_date >= NOW()
                ORDER BY created_at DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Удаляет просроченную рекламу
     */
    public function deleteExpired() {
        $sql = "UPDATE ads SET status = 'expired' WHERE end_date < NOW()";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }

    /**
     * Получает все рекламы пользователя по email
     */
    public function getByUserEmail($email) {
        $sql = "SELECT * FROM ads
                WHERE LOWER(advertiser_email) = LOWER(:email)
                ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => trim((string)$email)]);
        return $stmt->fetchAll();
    }

    /**
     * Получает рекламу по ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM ads WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Проверяет, есть ли у пользователя реклама со статусом pending
     */
    public function hasPendingByEmail($email) {
        $sql = "SELECT COUNT(*) as count FROM ads
                WHERE LOWER(advertiser_email) = LOWER(:email)
                AND status = 'pending'";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => trim((string)$email)]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Получает рекламу со статусом pending по email
     */
    public function getPendingByEmail($email) {
        $sql = "SELECT * FROM ads
                WHERE LOWER(advertiser_email) = LOWER(:email)
                AND status = 'pending'
                ORDER BY created_at DESC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => trim((string)$email)]);
        return $stmt->fetch();
    }

    /**
     * Одобряет рекламу
     */
    public function approve($adId) {
        $sql = "UPDATE ads
                SET status = 'active'
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $adId]);
    }

    /**
     * Отклоняет рекламу с причиной отказа
     */
    public function reject($adId, $reason = null) {
        $sql = "UPDATE ads
                SET status = 'rejected', rejection_reason = :reason
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $adId,
            ':reason' => $reason
        ]);
    }

    /**
     * Удаляет рекламу пользователя (проверяет принадлежность по email)
     */
    public function deleteByUser($adId, $userEmail) {
        // Сначала проверяем, что реклама принадлежит пользователю
        $ad = $this->findById($adId);
        if (
            !$ad ||
            strtolower(trim((string)$ad['advertiser_email'])) !== strtolower(trim((string)$userEmail))
        ) {
            return false;
        }

        // Удаляем файл изображения, если он существует
        if (!empty($ad['image_path'])) {
            $imagePath = __DIR__ . '/../../uploads/ads/' . $ad['image_path'];
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }

        // Удаляем запись из базы данных
        $sql = "DELETE FROM ads WHERE id = :id AND advertiser_email = :email";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $adId,
            ':email' => $userEmail
        ]);
    }

    /**
     * Удаляет рекламу по ID (для менеджера/администратора)
     */
    public function delete($adId) {
        // Получаем рекламу для удаления файла
        $ad = $this->findById($adId);
        if (!$ad) {
            return false;
        }

        // Удаляем файл изображения, если он существует
        if (!empty($ad['image_path'])) {
            $imagePath = __DIR__ . '/../../uploads/ads/' . $ad['image_path'];
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }

        // Удаляем запись из базы данных
        $sql = "DELETE FROM ads WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $adId]);
    }
}

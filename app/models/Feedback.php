<?php

/**
 * МОДЕЛЬ ОБРАТНОЙ СВЯЗИ
 *
 * Этот класс работает с таблицей feedback в базе данных.
 * Здесь методы для создания, чтения, обновления заявок от пользователей.
 */

class Feedback
{
    private $db; // Подключение к базе данных

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Создает новую заявку обратной связи
     */
    public function create($data)
    {
        $sql = "INSERT INTO feedback (user_id, type, subject, message, email, status, created_at)
                VALUES (:user_id, :type, :subject, :message, :email, 'new', NOW())";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':user_id' => $data['user_id'] ?? null,
            ':type' => $data['type'],
            ':subject' => $data['subject'],
            ':message' => $data['message'],
            ':email' => $data['email'] ?? null
        ]);
    }

    /**
     * Получает все заявки (для админки)
     */
    public function getAll($limit = 100, $offset = 0)
    {
        $sql = "SELECT f.*, u.email as user_email, u.full_name as user_name
                FROM feedback f
                LEFT JOIN users u ON f.user_id = u.id
                ORDER BY f.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Получает заявку по ID
     */
    public function findById($id)
    {
        $sql = "SELECT f.*, u.email as user_email, u.full_name as user_name
                FROM feedback f
                LEFT JOIN users u ON f.user_id = u.id
                WHERE f.id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch();
    }

    /**
     * Получает количество новых заявок
     */
    public function getNewCount()
    {
        $sql = "SELECT COUNT(*) FROM feedback WHERE status = 'new'";
        $stmt = $this->db->query($sql);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Обновляет статус заявки
     */
    public function updateStatus($id, $status, $adminNotes = null)
    {
        $sql = "UPDATE feedback SET status = :status, admin_notes = :admin_notes, updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':status' => $status,
            ':admin_notes' => $adminNotes
        ]);
    }

    /**
     * Получает заявки по статусу
     */
    public function getByStatus($status, $limit = 100)
    {
        $sql = "SELECT f.*, u.email as user_email, u.full_name as user_name
                FROM feedback f
                LEFT JOIN users u ON f.user_id = u.id
                WHERE f.status = :status
                ORDER BY f.created_at DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Проверяет наличие активных заявок у пользователя
     * Активными считаются заявки со статусом 'new' или 'in_progress'
     */
    public function hasActiveFeedback($userId, $email = null)
    {
        if ($userId) {
            $sql = "SELECT COUNT(*) FROM feedback
                    WHERE user_id = :user_id
                    AND status IN ('new', 'in_progress')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            $count = (int)$stmt->fetchColumn();
            return $count > 0;
        } elseif ($email) {
            $sql = "SELECT COUNT(*) FROM feedback
                    WHERE email = :email
                    AND user_id IS NULL
                    AND status IN ('new', 'in_progress')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => $email]);
            $count = (int)$stmt->fetchColumn();
            return $count > 0;
        }
        return false;
    }

    /**
     * Получает последнюю заявку пользователя
     */
    public function getLastFeedback($userId, $email = null)
    {
        if ($userId) {
            $sql = "SELECT * FROM feedback
                    WHERE user_id = :user_id
                    ORDER BY created_at DESC
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetch();
        } elseif ($email) {
            $sql = "SELECT * FROM feedback
                    WHERE email = :email
                    AND user_id IS NULL
                    ORDER BY created_at DESC
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => $email]);
            return $stmt->fetch();
        }
        return null;
    }

    /**
     * Удаляет заявку обратной связи
     */
    public function delete($id)
    {
        $sql = "DELETE FROM feedback WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}

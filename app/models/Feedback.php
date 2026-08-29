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

    /**
     * ID пользователя, от имени которого открывается чат по заявке.
     */
    public function resolveChatSenderId($preferredUserId = null)
    {
        $userModel = new User();

        if ($preferredUserId) {
            $user = $userModel->findById($preferredUserId);
            if ($user && empty($user['deleted_at'])) {
                return (int)$user['id'];
            }
        }

        $manager = $userModel->findFirstManager();
        if ($manager) {
            return (int)$manager['id'];
        }

        if (!empty($_SESSION['admin_email'])) {
            $user = $userModel->findByEmail($_SESSION['admin_email']);
            if ($user && empty($user['deleted_at'])) {
                return (int)$user['id'];
            }
        }

        return null;
    }

    /**
     * Участники чата по заявке: пользователь и сотрудник поддержки.
     */
    public function getChatParticipants(array $feedback)
    {
        $userId = !empty($feedback['user_id']) ? (int)$feedback['user_id'] : 0;
        if ($userId <= 0) {
            return null;
        }

        Message::ensureFeedbackIdColumn();

        $sql = "SELECT from_user_id, to_user_id FROM messages
                WHERE feedback_id = :fid
                ORDER BY id ASC
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':fid' => (int)$feedback['id']]);
        $row = $stmt->fetch();

        if ($row) {
            $fromId = (int)$row['from_user_id'];
            $toId = (int)$row['to_user_id'];
            $staffId = ($fromId === $userId) ? $toId : $fromId;
        } else {
            $staffId = $this->resolveChatSenderId(Helper::getUserId());
        }

        if (!$staffId) {
            return null;
        }

        return [
            'user_id' => $userId,
            'staff_id' => (int)$staffId
        ];
    }

    /**
     * Сообщения чата заявки для панели админа/менеджера.
     */
    public function formatChatMessages(array $feedback)
    {
        $out = [
            [
                'id' => 0,
                'from_user_id' => (int)($feedback['user_id'] ?? 0),
                'is_staff' => false,
                'from_name' => $feedback['user_name'] ?? 'Пользователь',
                'message' => (string)($feedback['message'] ?? ''),
                'created_at' => $feedback['created_at'] ?? null,
                'time' => !empty($feedback['created_at']) ? date('d.m.Y H:i', strtotime($feedback['created_at'])) : ''
            ]
        ];

        $pair = $this->getChatParticipants($feedback);
        if (!$pair) {
            return $out;
        }

        $messageModel = new Message();
        $raw = $messageModel->getConversation($pair['staff_id'], $pair['user_id']);
        foreach ($raw as $msg) {
            $fromId = (int)$msg['from_user_id'];
            $isStaff = $fromId !== $pair['user_id'];
            $out[] = [
                'id' => (int)$msg['id'],
                'from_user_id' => $fromId,
                'is_staff' => $isStaff,
                'from_name' => $isStaff
                    ? ($msg['from_full_name'] ?? 'Поддержка')
                    : ($msg['from_full_name'] ?? $feedback['user_name'] ?? 'Пользователь'),
                'message' => (string)$msg['message'],
                'created_at' => $msg['created_at'],
                'time' => date('d.m.Y H:i', strtotime($msg['created_at']))
            ];
        }
        return $out;
    }

    public function getChatForStaff($feedbackId)
    {
        $feedback = $this->findById($feedbackId);
        if (!$feedback) {
            return ['success' => false, 'error' => 'Заявка не найдена'];
        }

        return [
            'success' => true,
            'messages' => $this->formatChatMessages($feedback),
            'closed' => ($feedback['status'] === 'closed'),
            'status' => $feedback['status'],
            'has_user' => !empty($feedback['user_id'])
        ];
    }

    /**
     * Отправка сообщения в чат заявки из админки (без ухода на страницу «Сообщения»).
     */
    public function sendStaffChat($feedbackId, $text, $preferredSenderId = null, $status = null)
    {
        $feedback = $this->findById($feedbackId);
        if (!$feedback) {
            return ['success' => false, 'error' => 'Заявка не найдена'];
        }

        $text = trim((string)$text);
        $previousStatus = $feedback['status'];
        $wasClosed = ($previousStatus === 'closed');
        $allowedStatuses = ['new', 'in_progress', 'resolved', 'closed'];
        if ($status && in_array($status, $allowedStatuses, true)) {
            if ($text !== '' && $status === 'new') {
                $status = 'in_progress';
            }
            $this->updateStatus($feedbackId, $status, $feedback['admin_notes'] ?? null);
            $feedback['status'] = $status;
        } elseif ($text !== '' && $feedback['status'] === 'new') {
            $this->updateStatus($feedbackId, 'in_progress', $feedback['admin_notes'] ?? null);
            $feedback['status'] = 'in_progress';
        }

        $nowClosed = ($feedback['status'] === 'closed');
        $justClosed = $nowClosed && !$wasClosed;

        if ($wasClosed && $nowClosed && $text !== '') {
            $result = $this->getChatForStaff($feedbackId);
            $result['success'] = false;
            $result['error'] = 'Чат закрыт';
            $result['closed'] = true;
            return $result;
        }

        if ($justClosed) {
            $closeText = 'Ваше обращение закрыто.';
            $text = $text !== '' ? ($text . "\n\n" . $closeText) : $closeText;
        }

        $delivered = ['message' => false, 'email' => false];
        if ($text !== '') {
            $pair = $this->getChatParticipants($feedback);
            $hasChat = $pair && $this->hasFeedbackChat((int)$feedback['id']);

            if ($hasChat && $pair) {
                try {
                    $messageModel = new Message();
                    if ($messageModel->send($pair['staff_id'], $pair['user_id'], $text, null, null, (int)$feedback['id'])) {
                        $delivered['message'] = true;
                        $pushService = new PushNotificationService();
                        $pushService->sendMessageNotification($pair['user_id'], $pair['staff_id'], $text);
                    }
                } catch (Exception $e) {
                    error_log('Feedback::sendStaffChat: ' . $e->getMessage());
                }
            } else {
                $delivered = $this->deliverReply($feedback, $text, $preferredSenderId);
            }
        }

        $result = $this->getChatForStaff($feedbackId);
        $result['delivered'] = $delivered;
        if ($text !== '' && empty($delivered['message']) && empty($delivered['email'])) {
            $result['success'] = false;
            $result['error'] = 'Не удалось отправить сообщение';
        }
        return $result;
    }

    private function hasFeedbackChat($feedbackId)
    {
        Message::ensureFeedbackIdColumn();
        $sql = "SELECT id FROM messages WHERE feedback_id = :fid LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':fid' => (int)$feedbackId]);
        return (bool)$stmt->fetch();
    }

    /**
     * Закрыт ли чат обращения для пользователя (он больше не может писать).
     */
    public function isUserSupportChatClosed($userId, $otherUserId)
    {
        $userId = (int)$userId;
        $otherUserId = (int)$otherUserId;
        if ($userId <= 0 || $otherUserId <= 0) {
            return false;
        }

        Message::ensureFeedbackIdColumn();

        $sql = "SELECT f.status
                FROM messages m
                INNER JOIN feedback f ON f.id = m.feedback_id
                WHERE f.user_id = :user_id
                AND (
                    (m.from_user_id = :u1 AND m.to_user_id = :o1)
                    OR (m.from_user_id = :o2 AND m.to_user_id = :u2)
                )
                ORDER BY m.id DESC
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':u1' => $userId,
            ':o1' => $otherUserId,
            ':o2' => $otherUserId,
            ':u2' => $userId
        ]);
        $status = $stmt->fetchColumn();

        return $status === 'closed';
    }

    /**
     * Доставляет ответ сотрудника пользователю.
     * Зарегистрированному — в личные сообщения (+ push).
     * Если указан email — также письмо.
     *
     * @param array $feedback строка заявки
     * @param string $replyText текст ответа
     * @param int|null $fromUserId ID отправителя (user_id менеджера или admin_id)
     * @return array{message: bool, email: bool}
     */
    public function deliverReply(array $feedback, $replyText, $preferredSenderId = null)
    {
        $result = ['message' => false, 'email' => false, 'to_user_id' => null, 'sender_id' => null];
        $replyText = trim((string)$replyText);
        if ($replyText === '') {
            return $result;
        }

        $feedbackId = (int)($feedback['id'] ?? 0);
        $recipientUserId = !empty($feedback['user_id']) ? (int)$feedback['user_id'] : null;
        $recipientEmail = $feedback['email'] ?? $feedback['user_email'] ?? null;
        if (is_string($recipientEmail)) {
            $recipientEmail = trim($recipientEmail);
            if ($recipientEmail === '') {
                $recipientEmail = null;
            }
        }

        if (!$recipientUserId && $recipientEmail) {
            $userModel = new User();
            $user = $userModel->findByEmail($recipientEmail);
            if ($user && empty($user['deleted_at'])) {
                $recipientUserId = (int)$user['id'];
            }
        }

        $fromUserId = $this->resolveChatSenderId($preferredSenderId);
        $result['to_user_id'] = $recipientUserId;
        $result['sender_id'] = $fromUserId;

        $subject = (string)($feedback['subject'] ?? '');
        $messageText = "Ответ на ваше обращение #{$feedbackId}\n\n";
        $messageText .= "Тема: " . $subject . "\n\n";
        $messageText .= $replyText;

        if ($recipientUserId && $fromUserId && $recipientUserId !== $fromUserId) {
            try {
                $messageModel = new Message();
                if ($messageModel->send($fromUserId, $recipientUserId, $messageText, null, null, $feedbackId)) {
                    $result['message'] = true;
                    $pushService = new PushNotificationService();
                    $pushService->sendMessageNotification(
                        $recipientUserId,
                        $fromUserId,
                        $messageText
                    );
                    error_log("Feedback::deliverReply: chat opened with user #{$recipientUserId} for feedback #{$feedbackId}");
                } else {
                    error_log("Feedback::deliverReply: failed to send PM to user #{$recipientUserId} for feedback #{$feedbackId}");
                }
            } catch (Exception $e) {
                error_log('Feedback::deliverReply message: ' . $e->getMessage());
            }
        }

        if ($recipientEmail && filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            try {
                $emailService = new EmailService();
                $mailSubject = 'Ответ на ваше обращение #' . $feedbackId;
                $body = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">';
                $body .= '<h2 style="color: #333;">Ответ на ваше обращение</h2>';
                $body .= '<p>Здравствуйте!</p>';
                $body .= '<p>Вы оставили обращение с темой: <strong>' . Helper::escape($subject) . '</strong></p>';
                $body .= '<div style="background-color: #f5f5f5; padding: 15px; border-left: 4px solid #007bff; margin: 20px 0;">';
                $body .= '<p style="margin: 0; white-space: pre-wrap;">' . nl2br(Helper::escape($replyText)) . '</p>';
                $body .= '</div>';
                $body .= '<p style="color: #666; font-size: 12px; margin-top: 20px;">С уважением,<br>Команда поддержки</p>';
                $body .= '</div>';

                if ($emailService->send($recipientEmail, $mailSubject, $body)) {
                    $result['email'] = true;
                    error_log("Feedback::deliverReply: email sent to {$recipientEmail} for feedback #{$feedbackId}");
                } else {
                    error_log("Feedback::deliverReply: failed to send email to {$recipientEmail} for feedback #{$feedbackId}");
                }
            } catch (Exception $e) {
                error_log('Feedback::deliverReply email: ' . $e->getMessage());
            }
        }

        return $result;
    }
}

<?php

/**
 * КОНТРОЛЛЕР ОБРАТНОЙ СВЯЗИ
 *
 * Контроллер обрабатывает запросы на отправку обратной связи от пользователей.
 */

class FeedbackController
{
    private $feedbackModel;

    public function __construct()
    {
        $this->feedbackModel = new Feedback();
    }

    /**
     * Обрабатывает отправку формы обратной связи
     */
    public function submit()
    {
        // Устанавливаем заголовок для JSON ответа
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                'message' => 'Метод не разрешен'
            ]);
            return;
        }

        // Базовая защита от ботов/спама
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userId = Helper::isLoggedIn() ? Helper::getUserId() : null;

        // Honeypot: боты часто заполняют скрытые поля
        $honeypot = trim((string)($_POST['website'] ?? ''));
        if ($honeypot !== '') {
            // Тихо "успешно" принимаем, но ничего не сохраняем (чтобы боту было сложнее понять защиту)
            echo json_encode([
                'success' => true,
                'message' => 'Спасибо! Ваше сообщение отправлено разработчикам. Мы рассмотрим его в ближайшее время.'
            ]);
            return;
        }

        // Ограничение частоты отправки по IP
        // - не чаще 1 раза в 30 секунд
        // - не более 10 раз в час
        if (!RateLimiter::allow('feedback:ip:' . $ip . ':30s', 1, 30) || !RateLimiter::allow('feedback:ip:' . $ip . ':1h', 10, 3600)) {
            echo json_encode([
                'success' => false,
                'message' => 'Слишком много запросов. Попробуйте позже.'
            ]);
            return;
        }

        // Получаем данные из формы
        $type = $_POST['type'] ?? '';
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $email = trim($_POST['email'] ?? '');

        // Проверка "слишком быстро отправили" (гости)
        // JS в форме проставляет _fb_started_at (ms). Если форма отправлена слишком быстро — похоже на бота.
        if (!$userId) {
            $startedAt = (int)($_POST['_fb_started_at'] ?? 0);
            if ($startedAt > 0) {
                // если пришли секунды — конвертируем в ms
                if ($startedAt < 1000000000000) {
                    $startedAt *= 1000;
                }
                $nowMs = (int)floor(microtime(true) * 1000);
                $delta = $nowMs - $startedAt;
                if ($delta >= 0 && $delta < 1200) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Спасибо! Ваше сообщение отправлено разработчикам. Мы рассмотрим его в ближайшее время.'
                    ]);
                    return;
                }
            }
        }

        // Валидация
        if (empty($type) || empty($subject) || empty($message)) {
            echo json_encode([
                'success' => false,
                'message' => 'Пожалуйста, заполните все обязательные поля'
            ]);
            return;
        }

        // Ограничения по длине (снижают спам и нагрузку)
        if (mb_strlen($subject) < 3 || mb_strlen($subject) > 150) {
            echo json_encode([
                'success' => false,
                'message' => 'Тема должна быть от 3 до 150 символов'
            ]);
            return;
        }
        if (mb_strlen($message) < 10 || mb_strlen($message) > 3000) {
            echo json_encode([
                'success' => false,
                'message' => 'Сообщение должно быть от 10 до 3000 символов'
            ]);
            return;
        }

        // Проверяем тип
        $allowedTypes = ['bug', 'suggestion', 'feature', 'other'];
        if (!in_array($type, $allowedTypes)) {
            echo json_encode([
                'success' => false,
                'message' => 'Неверный тип сообщения'
            ]);
            return;
        }

        // Валидация email (если указан)
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                'success' => false,
                'message' => 'Неверный формат email'
            ]);
            return;
        }

        // Если пользователь авторизован, но не указал email, берем из профиля
        if ($userId && empty($email)) {
            $userModel = new User();
            $user = $userModel->findById($userId);
            if ($user && !empty($user['email'])) {
                $email = $user['email'];
            }
        }

        // Дедупликация (гости): одинаковое сообщение с одного IP за короткое время — почти всегда бот
        if (!$userId) {
            $fingerprint = sha1(mb_strtolower(trim($type)) . '|' . mb_strtolower(trim($subject)) . '|' . mb_strtolower(trim($message)));
            if (!RateLimiter::allow('feedback:dedupe:' . $ip . ':' . $fingerprint, 1, 600)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Спасибо! Ваше сообщение отправлено разработчикам. Мы рассмотрим его в ближайшее время.'
                ]);
                return;
            }
        }

        // Проверяем наличие активных заявок
        $hasActive = $this->feedbackModel->hasActiveFeedback($userId, $email);

        if ($hasActive) {
            echo json_encode([
                'success' => false,
                'message' => 'Ваша заявка еще обрабатывается. Пожалуйста, дождитесь ответа администратора.'
            ]);
            return;
        }

        // Подготавливаем данные для сохранения
        $data = [
            'user_id' => $userId,
            'type' => $type,
            'subject' => $subject,
            'message' => $message,
            'email' => !empty($email) ? $email : null
        ];

        // Сохраняем заявку
        if ($this->feedbackModel->create($data)) {
            // Проверяем последнюю заявку (если была resolved/closed, показываем специальное сообщение)
            $lastFeedback = $this->feedbackModel->getLastFeedback($userId, $email);
            $responseMessage = 'Спасибо! Ваше сообщение отправлено разработчикам. Мы рассмотрим его в ближайшее время.';

            if ($lastFeedback && in_array($lastFeedback['status'], ['resolved', 'closed'])) {
                $responseMessage = 'Исправили ваш запрос. Можете еще раз отправить заявку, если что-то нашли.';
            }

            echo json_encode([
                'success' => true,
                'message' => $responseMessage
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Произошла ошибка при отправке сообщения. Попробуйте позже.'
            ]);
        }
    }

    /**
     * Проверяет статус заявок пользователя (для показа в модальном окне)
     */
    public function checkStatus()
    {
        header('Content-Type: application/json');

        $userId = Helper::isLoggedIn() ? Helper::getUserId() : null;
        $email = $_GET['email'] ?? null;

        // Если пользователь авторизован, но не указан email, берем из профиля
        if ($userId && empty($email)) {
            $userModel = new User();
            $user = $userModel->findById($userId);
            if ($user && !empty($user['email'])) {
                $email = $user['email'];
            }
        }

        $hasActive = $this->feedbackModel->hasActiveFeedback($userId, $email);
        $lastFeedback = $this->feedbackModel->getLastFeedback($userId, $email);

        $response = [
            'hasActive' => $hasActive,
            'lastStatus' => $lastFeedback ? $lastFeedback['status'] : null,
            'message' => null
        ];

        if ($hasActive) {
            $response['message'] = 'Ваша заявка еще обрабатывается. Пожалуйста, дождитесь ответа администратора.';
        } elseif ($lastFeedback && in_array($lastFeedback['status'], ['resolved', 'closed'])) {
            $response['message'] = 'Исправили ваш запрос. Можете еще раз отправить заявку, если что-то нашли.';
        }

        echo json_encode($response);
    }
}

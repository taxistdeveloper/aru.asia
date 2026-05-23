<?php

/**
 * КОНТРОЛЛЕР PUSH-УВЕДОМЛЕНИЙ
 * 
 * Обрабатывает регистрацию токенов и отправку уведомлений
 */

class PushNotificationController
{
    private $pushModel;

    public function __construct()
    {
        $this->pushModel = new PushNotification();
    }

    /**
     * Регистрирует токен устройства для текущего пользователя
     */
    public function register()
    {
        if (!Helper::isLoggedIn()) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Не авторизован']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Метод не разрешен']);
            return;
        }

        $userId = Helper::getUserId();
        $token = $_POST['token'] ?? '';
        $deviceType = $_POST['device_type'] ?? 'web';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        if (empty($token)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Токен не указан']);
            return;
        }

        if ($this->pushModel->registerToken($userId, $token, $deviceType, $userAgent)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Ошибка регистрации токена']);
        }
    }

    /**
     * Удаляет токен устройства
     */
    public function unregister()
    {
        if (!Helper::isLoggedIn()) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Не авторизован']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Метод не разрешен']);
            return;
        }

        $userId = Helper::getUserId();
        $token = $_POST['token'] ?? '';

        if (empty($token)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Токен не указан']);
            return;
        }

        if ($this->pushModel->unregisterToken($userId, $token)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Ошибка удаления токена']);
        }
    }
}


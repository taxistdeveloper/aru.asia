<?php

/**
 * ГЛАВНАЯ ТОЧКА ВХОДА В ПРИЛОЖЕНИЕ
 *
 * Этот файл - это первая точка входа для всех запросов к нашему приложению.
 * Здесь мы подключаем автозагрузчик классов и запускаем роутер,
 * который определяет какой контроллер и метод нужно вызвать.
 */

// Включаем отображение ошибок для разработки (в продакшене убрать!)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Запускаем сессию для хранения данных пользователя
session_start();

// Подключаем автозагрузчик классов (автоматически загружает нужные классы)
require_once 'app/config/autoload.php';

// Подключаем конфигурацию
require_once 'app/config/config.php';

// Устанавливаем часовой пояс (если не установлен в config.php)
if (!defined('APP_TIMEZONE')) {
    date_default_timezone_set('Asia/Almaty');
} else {
    date_default_timezone_set(APP_TIMEZONE);
}

// Обработка файлов верификации поисковых систем (отдаем напрямую без роутинга)
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestUri = strtok($requestUri, '?'); // Убираем параметры запроса
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
if ($scriptName !== '/' && $scriptName !== '\\') {
    $requestUri = str_replace($scriptName, '', $requestUri);
}
$requestUri = trim($requestUri, '/');

// Проверяем, является ли запрос файлом верификации
if (preg_match('/^(yandex_|google|.*verification).*\.html?$/i', $requestUri)) {
    $verificationFile = __DIR__ . '/' . basename($requestUri);
    if (file_exists($verificationFile)) {
        // Устанавливаем правильный Content-Type
        header('Content-Type: text/html; charset=UTF-8');
        // Отдаем содержимое файла
        readfile($verificationFile);
        exit;
    }
}

// Явно подключаем основные классы (на случай если автозагрузчик не сработает)
require_once 'app/core/Router.php';
require_once 'app/core/Database.php';
require_once 'app/core/View.php';
require_once 'app/core/Helper.php';
require_once 'app/models/DailyVisit.php';

// Автоматический вход по remember_token если пользователь не авторизован
if (!Helper::isLoggedIn() && isset($_COOKIE['remember_token'])) {
    $userModel = new User();
    $user = $userModel->findByRememberToken($_COOKIE['remember_token']);

    if ($user) {
        // Автоматически авторизуем пользователя
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_gender'] = $user['gender'];
        $_SESSION['user_role'] = $user['role'] ?? 'user';
    } else {
        // Токен недействителен, удаляем cookie
        setcookie('remember_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
}

// Проверяем существование пользователя в базе данных
// Если пользователь был удален админом, выполняется выход с сообщением
Helper::checkUserExists();

// Счётчик посещений: только гости на публичных страницах (не админка, не обновления)
DailyVisit::trackToday();

// Создаем и запускаем роутер
$router = new Router();
$router->dispatch();

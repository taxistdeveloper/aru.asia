<?php

/**
 * КОНФИГУРАЦИЯ ПРИЛОЖЕНИЯ
 *
 * Здесь хранятся все основные настройки приложения:
 * - Подключение к базе данных
 * - Настройки сайта
 * - Константы
 */

// Определяем базовый URL сайта (для ссылок)
// Динамически определяем URL по текущему запросу — иначе картинки не загружаются при доступе через другой порт/хост (например, localhost:8888)
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $dir = dirname($scriptName);
    if ($dir === '/' || $dir === '.' || $dir === '//') {
        $basePath = '';
    } else {
        $basePath = trim($dir, '/') . '/';
    }
    define('BASE_URL', $protocol . '://' . $host . '/' . $basePath);
}

// Настройки базы данных MySQL
define('DB_HOST', 'localhost');      // Адрес сервера БД
define('DB_NAME', 'aru');     // Имя базы данных
define('DB_USER', 'root');           // Имя пользователя БД
define('DB_PASS', 'root');           // Пароль БД (для MAMP обычно root)
define('DB_CHARSET', 'utf8mb4');     // Кодировка


// Настройки приложения
define('APP_NAME', 'aru App');    // Название приложения
define('RADIUS_KM', 50);             // Радиус поиска в километрах (50км)

// Настройки для работы с изображениями
define('UPLOAD_DIR', 'uploads/');    // Папка для загрузки файлов
define('MAX_PHOTOS', 10);            // Максимальное количество фото
define('MIN_PHOTOS', 1);             // Минимальное количество фото
define('MAX_FILE_SIZE_MB', 10);      // Макс. размер одного файла (МБ). Должен быть ≤ upload_max_filesize в PHP

/*
 * УВЕЛИЧЕНИЕ ЛИМИТА ЗАГРУЖКИ ФАЙЛОВ
 *
 * Если появляется ошибка «Файл слишком большой» или «превышен лимит php.ini»,
 * нужно поднять лимиты PHP. Варианты:
 *
 * 1) php.ini (рекомендуется для MAMP/XAMPP)
 *    Файл: MAMP → /Applications/MAMP/bin/php/phpX.X.X/conf/php.ini
 *    Изменить:
 *      upload_max_filesize = 20M
 *      post_max_size = 25M        (должен быть >= upload_max_filesize)
 *      max_execution_time = 120   (при необходимости)
 *    Затем перезапустить MAMP.
 *
 * 2) .htaccess (Apache + mod_php)
 *    В корне проекта уже добавлены php_value. Работает только если PHP
 *    запущен как модуль Apache (типично для MAMP).
 *
 * 3) .user.ini (общий хостинг, PHP-FPM/CGI)
 *    Создать в корне сайта .user.ini:
 *      upload_max_filesize = 20M
 *      post_max_size = 25M
 *    Подхватывается при следующем запросе (или перезапуске PHP).
 *
 * 4) ini_set() в коде НЕ меняет upload_max_filesize/post_max_size — только php.ini/.htaccess/.user.ini.
 */

// Настройки для рекламы
define('AD_IMAGE_WIDTH', 728);       // Ширина рекламного баннера (пиксели)
define('AD_IMAGE_HEIGHT', 90);       // Высота рекламного баннера (пиксели)

// Публикация мероприятия: тариф за размещение на платформе (тенге за одно мероприятие)
define('EVENT_PUBLISH_PRICE_KZT', 500);
// false — публикация бесплатно (как сейчас). true — включить приём оплаты (нужно доработать EventsController под платёжку)
define('EVENT_PUBLISH_PAYMENT_ENABLED', false);

// Настройки почты (для подтверждения регистрации)
define('MAIL_FROM', 'shotaev96@gmail.com');
define('MAIL_FROM_NAME', 'Aru');

// Настройки SMTP для Gmail
// ВАЖНО: Для Gmail требуется App Password (не обычный пароль аккаунта)
// Получить App Password: https://myaccount.google.com/apppasswords
// Должен быть включен: Двухэтапная аутентификация
define('SMTP_ENABLED', true);  // Установите false для использования только mail()
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'shotaev96@gmail.com');
define('SMTP_PASSWORD', 'kzfv wlqf stlm tzuq');  // App Password (пробелы будут удалены автоматически)
define('SMTP_AUTH', true);

// Настройки часового пояса
// Устанавливаем часовой пояс для приложения (Asia/Almaty для Казахстана, UTC+6)
define('APP_TIMEZONE', 'Asia/Almaty');
date_default_timezone_set(APP_TIMEZONE);

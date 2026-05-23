<?php
/**
 * ДИНАМИЧЕСКИЙ ГЕНЕРАТОР SITEMAP.XML
 * 
 * Этот файл генерирует sitemap.xml динамически с актуальными датами.
 * Можно использовать вместо статического sitemap.xml для автоматического обновления.
 * 
 * Использование:
 * 1. Переименуйте sitemap.xml в sitemap.xml.backup
 * 2. Переименуйте этот файл в sitemap.php или используйте через .htaccess rewrite
 * 3. Убедитесь, что в .htaccess настроен rewrite для sitemap.xml -> sitemap.php
 */

// Подключаем конфигурацию для получения BASE_URL
require_once __DIR__ . '/app/config/config.php';

// Устанавливаем правильный Content-Type
header('Content-Type: application/xml; charset=utf-8');

// Получаем базовый URL (для продакшена должен быть https://aru.asia/)
$baseUrl = defined('BASE_URL') ? BASE_URL : 'https://aru.asia/';
// Убираем слеш в конце если есть
$baseUrl = rtrim($baseUrl, '/') . '/';
// Заменяем http://localhost на https://aru.asia для продакшена
if (strpos($baseUrl, 'localhost') !== false || strpos($baseUrl, '127.0.0.1') !== false) {
    $baseUrl = 'https://aru.asia/';
}

// Текущая дата в формате ISO 8601
$lastmod = date('Y-m-d');

// Начало XML
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
echo '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
echo '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9' . "\n";
echo '        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";
echo "\n";

// Функция для вывода URL
function outputUrl($baseUrl, $path, $lastmod, $changefreq, $priority) {
    echo "    <url>\n";
    echo "        <loc>" . htmlspecialchars($baseUrl . $path, ENT_XML1, 'UTF-8') . "</loc>\n";
    echo "        <lastmod>" . htmlspecialchars($lastmod, ENT_XML1, 'UTF-8') . "</lastmod>\n";
    echo "        <changefreq>" . htmlspecialchars($changefreq, ENT_XML1, 'UTF-8') . "</changefreq>\n";
    echo "        <priority>" . htmlspecialchars($priority, ENT_XML1, 'UTF-8') . "</priority>\n";
    echo "    </url>\n";
    echo "\n";
}

// Главная страница (Landing) - наивысший приоритет
outputUrl($baseUrl, '', $lastmod, 'daily', '1.0');

// Платформа знакомств
outputUrl($baseUrl, 'platform', $lastmod, 'daily', '0.9');

// Страница мероприятий
outputUrl($baseUrl, 'events', $lastmod, 'daily', '0.9');

// Страница свиданий
outputUrl($baseUrl, 'dates', $lastmod, 'daily', '0.9');

// Карта
outputUrl($baseUrl, 'map', $lastmod, 'weekly', '0.7');

// Регистрация
outputUrl($baseUrl, 'auth/register', $lastmod, 'monthly', '0.8');

// Вход
outputUrl($baseUrl, 'auth/login', $lastmod, 'monthly', '0.7');

// Информационная страница
outputUrl($baseUrl, 'info', $lastmod, 'monthly', '0.6');

// Восстановление пароля
outputUrl($baseUrl, 'auth/forgot-password', $lastmod, 'monthly', '0.5');

// Закрытие XML
echo '</urlset>' . "\n";
?>








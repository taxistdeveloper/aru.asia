<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
$db = Database::getInstance()->getConnection();

function esc($v) {
    if ($v === null) return 'NULL';
    return "'" . str_replace("'", "''", (string)$v) . "'";
}

ob_start();
echo "-- Тестовые данные: 20 мероприятий + 20 свиданий (Караганда)\n";
echo "-- Важно:\n";
echo "-- 1) user_id должны существовать в таблице users\n";
echo "-- 2) у каждого user_id только одно активное мероприятие и одно свидание\n";
echo "-- 3) category_id 24-37 должны быть в date_categories\n";
echo "-- 4) фото мероприятий: uploads/photos/seed_event_*.jpg\n";
echo "-- 5) фото свиданий берутся из user_photos автора\n";
echo "-- 6) перед вставкой при необходимости сдвиньте даты: event_date / date_time >= NOW()\n\n";

echo "-- Мероприятия (20 шт, status=approved)\n";
foreach ($db->query("SELECT * FROM events WHERE id >= 44 ORDER BY id") as $e) {
    echo "INSERT INTO events (user_id, title, description, event_date, location, latitude, longitude, price, photo, status, created_at) VALUES (";
    echo (int)$e['user_id'] . ", " . esc($e['title']) . ", " . esc($e['description']) . ", " . esc($e['event_date']) . ", ";
    echo esc($e['location']) . ", " . $e['latitude'] . ", " . $e['longitude'] . ", " . (float)$e['price'] . ", ";
    echo ($e['photo'] ? esc($e['photo']) : 'NULL') . ", " . esc($e['status']) . ", NOW());\n";
}

echo "\n-- Свидания (20 шт)\n";
foreach ($db->query("SELECT * FROM dates WHERE id >= 117 ORDER BY id") as $d) {
    echo "INSERT INTO dates (user_id, title, category_id, date_time, location, latitude, longitude, created_at) VALUES (";
    echo (int)$d['user_id'] . ", " . esc($d['title']) . ", " . (int)$d['category_id'] . ", " . esc($d['date_time']) . ", ";
    echo esc($d['location']) . ", " . $d['latitude'] . ", " . $d['longitude'] . ", NOW());\n";
}

echo "\n-- Фото профиля user #73 (для свидания без фото)\n";
echo "INSERT INTO user_photos (user_id, photo, created_at)\n";
echo "SELECT 73, 'seed_user_73.jpg', NOW()\n";
echo "WHERE NOT EXISTS (SELECT 1 FROM user_photos WHERE user_id = 73);\n";

$sql = ob_get_clean();
$out = __DIR__ . '/seed_karaganda.sql';
file_put_contents($out, $sql);
echo $sql;

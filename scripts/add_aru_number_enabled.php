<?php
/**
 * Добавляет колонку aru_number_enabled в users
 * (включение/отключение публичного поиска по номеру aru_N).
 * Запуск: php scripts/add_aru_number_enabled.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$_SERVER['HTTPS'] = 'off';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/index.php';

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

$db = Database::getInstance()->getConnection();

$check = $db->query("SHOW COLUMNS FROM users LIKE 'aru_number_enabled'");
if ($check->fetch()) {
    echo "Column aru_number_enabled already exists.\n";
    exit(0);
}

$db->exec("ALTER TABLE users ADD COLUMN aru_number_enabled TINYINT(1) NOT NULL DEFAULT 1");
echo "Column aru_number_enabled added successfully.\n";

<?php
/**
 * Добавляет колонку deleted_at в users (мягкое удаление аккаунта).
 * Запуск: php scripts/add_deleted_at.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// CLI: BASE_URL не нужен, но config требует SCRIPT_NAME
$_SERVER['HTTPS'] = 'off';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/index.php';

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

$db = Database::getInstance()->getConnection();

$check = $db->query("SHOW COLUMNS FROM users LIKE 'deleted_at'");
if ($check->fetch()) {
    echo "Column deleted_at already exists.\n";
    exit(0);
}

$db->exec("ALTER TABLE users ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL, ADD INDEX idx_deleted_at (deleted_at)");
echo "Column deleted_at added successfully.\n";

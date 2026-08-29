<?php
/**
 * Добавляет колонку feedback_id в messages
 * (чат по заявке обратной связи, отдельно от рассылок менеджера).
 * Запуск: php scripts/add_messages_feedback_id.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$_SERVER['HTTPS'] = 'off';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/index.php';

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

$db = Database::getInstance()->getConnection();

$check = $db->query("SHOW COLUMNS FROM messages LIKE 'feedback_id'");
if ($check->fetch()) {
    echo "Column feedback_id already exists.\n";
    exit(0);
}

$db->exec("ALTER TABLE messages ADD COLUMN feedback_id INT(11) NULL DEFAULT NULL AFTER event_id");
try {
    $db->exec("ALTER TABLE messages ADD INDEX idx_messages_feedback_id (feedback_id)");
} catch (Exception $e) {
    // индекс уже есть
}
echo "Column feedback_id added successfully.\n";

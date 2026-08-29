<?php
/**
 * Создаёт таблицу activity_logs (журнал действий и ошибок админки).
 * Запуск: php scripts/add_activity_logs.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$_SERVER['HTTPS'] = $_SERVER['HTTPS'] ?? 'off';
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['SCRIPT_NAME'] = $_SERVER['SCRIPT_NAME'] ?? '/index.php';

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/ActivityLog.php';

$model = new ActivityLog();
$model->ensureTable();

$db = Database::getInstance()->getConnection();
$check = $db->query("SHOW TABLES LIKE 'activity_logs'");
if ($check->fetch()) {
    echo "Table activity_logs is ready.\n";
    exit(0);
}

echo "Failed to create activity_logs. Check DB credentials and logs.\n";
exit(1);

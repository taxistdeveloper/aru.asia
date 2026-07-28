<?php
/**
 * Окончательное удаление аккаунтов после 6 месяцев мягкого удаления.
 * Запуск по расписанию (Task Scheduler / cron), например раз в сутки:
 *   php scripts/purge_deleted_users.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$_SERVER['HTTPS'] = 'off';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/index.php';

require_once __DIR__ . '/../app/config/autoload.php';
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

$userModel = new User();
$purged = $userModel->purgeExpiredSoftDeleted();

echo date('Y-m-d H:i:s') . " — permanently deleted {$purged} account(s).\n";

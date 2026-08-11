<?php
/**
 * Добавляет колонку aru_code (случайный номер вида 789136 → aru789136)
 * и выдаёт коды всем существующим пользователям.
 * Запуск: php scripts/add_aru_code.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$_SERVER['HTTPS'] = 'off';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/index.php';

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

$db = Database::getInstance()->getConnection();

$check = $db->query("SHOW COLUMNS FROM users LIKE 'aru_code'");
if (!$check->fetch()) {
    $db->exec("ALTER TABLE users ADD COLUMN aru_code VARCHAR(12) NULL DEFAULT NULL, ADD UNIQUE INDEX idx_aru_code (aru_code)");
    echo "Column aru_code added successfully.\n";
} else {
    echo "Column aru_code already exists.\n";
}

// Выдаём случайные коды тем, у кого ещё нет
$users = $db->query("SELECT id FROM users WHERE aru_code IS NULL OR aru_code = ''")->fetchAll(PDO::FETCH_COLUMN);
$assigned = 0;

foreach ($users as $userId) {
    $code = null;
    for ($attempt = 0; $attempt < 50; $attempt++) {
        $candidate = (string) random_int(100000, 999999);
        $stmt = $db->prepare("SELECT id FROM users WHERE aru_code = :code LIMIT 1");
        $stmt->execute([':code' => $candidate]);
        if (!$stmt->fetch()) {
            $code = $candidate;
            break;
        }
    }

    if ($code === null) {
        echo "Failed to assign code for user id={$userId}\n";
        continue;
    }

    $upd = $db->prepare("UPDATE users SET aru_code = :code WHERE id = :id");
    $upd->execute([':code' => $code, ':id' => $userId]);
    $assigned++;
}

echo "Assigned aru_code to {$assigned} users.\n";

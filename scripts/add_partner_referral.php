<?php
/**
 * Партнёрская ссылка и автокомиссия 50%:
 * - users.referred_by_partner_user_id, users.is_partner
 * - ads.paid_amount
 * - partner_earnings.ad_id (идемпотентность)
 *
 * Запуск: php scripts/add_partner_referral.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$_SERVER['HTTPS'] = 'off';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/index.php';

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

$db = Database::getInstance()->getConnection();

function columnExists(PDO $db, string $table, string $column): bool
{
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $column = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
    $stmt = $db->query("SHOW COLUMNS FROM `{$table}` LIKE " . $db->quote($column));
    return (bool)$stmt->fetch();
}

if (!columnExists($db, 'users', 'referred_by_partner_user_id')) {
    $db->exec("ALTER TABLE users ADD COLUMN referred_by_partner_user_id INT UNSIGNED NULL DEFAULT NULL AFTER aru_code");
    $db->exec("ALTER TABLE users ADD INDEX idx_users_referred_by (referred_by_partner_user_id)");
    echo "Added users.referred_by_partner_user_id\n";
} else {
    echo "users.referred_by_partner_user_id already exists\n";
}

if (!columnExists($db, 'users', 'is_partner')) {
    $db->exec("ALTER TABLE users ADD COLUMN is_partner TINYINT(1) NOT NULL DEFAULT 0 AFTER referred_by_partner_user_id");
    echo "Added users.is_partner\n";
} else {
    echo "users.is_partner already exists\n";
}

if (!columnExists($db, 'ads', 'paid_amount')) {
    $db->exec("ALTER TABLE ads ADD COLUMN paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER click_url");
    echo "Added ads.paid_amount\n";
} else {
    echo "ads.paid_amount already exists\n";
}

if (!columnExists($db, 'partner_earnings', 'ad_id')) {
    $db->exec("ALTER TABLE partner_earnings ADD COLUMN ad_id INT UNSIGNED NULL DEFAULT NULL AFTER company_id");
    $db->exec("ALTER TABLE partner_earnings ADD UNIQUE INDEX uk_partner_earnings_ad_id (ad_id)");
    echo "Added partner_earnings.ad_id\n";
} else {
    echo "partner_earnings.ad_id already exists\n";
}

echo "Done.\n";

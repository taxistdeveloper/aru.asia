<?php
/**
 * Создаёт таблицы партнёрской программы (заработок):
 * partner_companies, partner_earnings, partner_payouts
 *
 * Запуск: php scripts/add_partner_earnings.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$_SERVER['HTTPS'] = 'off';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/index.php';

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

$db = Database::getInstance()->getConnection();

function tableExists(PDO $db, string $table): bool
{
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $stmt = $db->query("SHOW TABLES LIKE " . $db->quote($table));
    return (bool)$stmt->fetchColumn();
}

if (!tableExists($db, 'partner_companies')) {
    $db->exec("
        CREATE TABLE partner_companies (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            partner_user_id INT UNSIGNED NOT NULL,
            company_name VARCHAR(255) NOT NULL,
            contact_name VARCHAR(255) NULL,
            contact_email VARCHAR(255) NULL,
            contact_phone VARCHAR(64) NULL,
            website VARCHAR(255) NULL,
            description TEXT NULL,
            status ENUM('pending','approved','rejected','active') NOT NULL DEFAULT 'pending',
            bind_key VARCHAR(255) NOT NULL COMMENT 'Уникальный ключ для защиты от перепривязки',
            admin_note TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_partner_companies_bind_key (bind_key),
            KEY idx_partner_companies_partner (partner_user_id),
            KEY idx_partner_companies_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table partner_companies created.\n";
} else {
    echo "Table partner_companies already exists.\n";
}

if (!tableExists($db, 'partner_earnings')) {
    $db->exec("
        CREATE TABLE partner_earnings (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            partner_user_id INT UNSIGNED NOT NULL,
            company_id INT UNSIGNED NULL,
            amount DECIMAL(12,2) NOT NULL,
            currency VARCHAR(3) NOT NULL DEFAULT 'KZT',
            description VARCHAR(500) NULL,
            created_by_admin_id INT UNSIGNED NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_partner_earnings_partner (partner_user_id),
            KEY idx_partner_earnings_company (company_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table partner_earnings created.\n";
} else {
    echo "Table partner_earnings already exists.\n";
}

if (!tableExists($db, 'partner_payouts')) {
    $db->exec("
        CREATE TABLE partner_payouts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            partner_user_id INT UNSIGNED NOT NULL,
            amount DECIMAL(12,2) NOT NULL,
            currency VARCHAR(3) NOT NULL DEFAULT 'KZT',
            payment_details TEXT NOT NULL,
            status ENUM('pending','approved','paid','rejected') NOT NULL DEFAULT 'pending',
            admin_note TEXT NULL,
            processed_by_admin_id INT UNSIGNED NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            processed_at DATETIME NULL,
            KEY idx_partner_payouts_partner (partner_user_id),
            KEY idx_partner_payouts_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Table partner_payouts created.\n";
} else {
    echo "Table partner_payouts already exists.\n";
}

echo "Done.\n";

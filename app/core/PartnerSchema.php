<?php

/**
 * Гарантирует наличие таблиц/колонок партнёрской программы.
 * Безопасно вызывать повторно (idempotent).
 */

class PartnerSchema
{
    private static $ready = false;

    public static function ensure(): void
    {
        if (self::$ready) {
            return;
        }

        try {
            $db = Database::getInstance()->getConnection();

            if (!self::tableExists($db, 'partner_companies')) {
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
                        bind_key VARCHAR(255) NOT NULL,
                        admin_note TEXT NULL,
                        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
                        UNIQUE KEY uk_partner_companies_bind_key (bind_key),
                        KEY idx_partner_companies_partner (partner_user_id),
                        KEY idx_partner_companies_status (status)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            }

            if (!self::tableExists($db, 'partner_earnings')) {
                $db->exec("
                    CREATE TABLE partner_earnings (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        partner_user_id INT UNSIGNED NOT NULL,
                        company_id INT UNSIGNED NULL,
                        ad_id INT UNSIGNED NULL,
                        amount DECIMAL(12,2) NOT NULL,
                        currency VARCHAR(3) NOT NULL DEFAULT 'KZT',
                        description VARCHAR(500) NULL,
                        created_by_admin_id INT UNSIGNED NULL,
                        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        UNIQUE KEY uk_partner_earnings_ad_id (ad_id),
                        KEY idx_partner_earnings_partner (partner_user_id),
                        KEY idx_partner_earnings_company (company_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            } elseif (!self::columnExists($db, 'partner_earnings', 'ad_id')) {
                $db->exec("ALTER TABLE partner_earnings ADD COLUMN ad_id INT UNSIGNED NULL DEFAULT NULL AFTER company_id");
                try {
                    $db->exec("ALTER TABLE partner_earnings ADD UNIQUE INDEX uk_partner_earnings_ad_id (ad_id)");
                } catch (Throwable $e) {
                    // индекс уже есть
                }
            }

            if (!self::tableExists($db, 'partner_payouts')) {
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
            }

            if (!self::columnExists($db, 'users', 'referred_by_partner_user_id')) {
                $db->exec("ALTER TABLE users ADD COLUMN referred_by_partner_user_id INT UNSIGNED NULL DEFAULT NULL");
                try {
                    $db->exec("ALTER TABLE users ADD INDEX idx_users_referred_by (referred_by_partner_user_id)");
                } catch (Throwable $e) {
                }
            }

            if (!self::columnExists($db, 'users', 'is_partner')) {
                $db->exec("ALTER TABLE users ADD COLUMN is_partner TINYINT(1) NOT NULL DEFAULT 0");
            }

            if (!self::columnExists($db, 'ads', 'paid_amount')) {
                $db->exec("ALTER TABLE ads ADD COLUMN paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0");
            }

            self::$ready = true;
        } catch (Throwable $e) {
            error_log('PartnerSchema::ensure failed: ' . $e->getMessage());
            // не кэшируем ready — попробуем снова в следующем запросе
            throw $e;
        }
    }

    private static function tableExists(PDO $db, string $table): bool
    {
        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        $stmt = $db->query('SHOW TABLES LIKE ' . $db->quote($table));
        return (bool)$stmt->fetchColumn();
    }

    private static function columnExists(PDO $db, string $table, string $column): bool
    {
        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        $column = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
        $stmt = $db->query("SHOW COLUMNS FROM `{$table}` LIKE " . $db->quote($column));
        return (bool)$stmt->fetch();
    }
}

<?php

/**
 * Если партнёр не заходит 12 месяцев, невыведенный остаток
 * и люди с его ссылки переходят на рабочий кошелёк Aru.
 * Уже поданные заявки на выплату не трогаем.
 */

class PartnerReclaim
{
    public const INACTIVE_MONTHS = 12;
    public const HOUSE_EMAIL = 'house-wallet@aru.asia';

    public static function runIfDue(): void
    {
        $lock = sys_get_temp_dir() . '/aru-partner-reclaim-' . md5((string)DB_NAME);
        $last = @filemtime($lock);
        if ($last && (time() - $last) < 86400) {
            return;
        }

        $handle = @fopen($lock, 'c');
        if (!$handle || !flock($handle, LOCK_EX | LOCK_NB)) {
            if ($handle) {
                fclose($handle);
            }
            return;
        }

        try {
            self::reclaimDormant();
            @touch($lock);
        } catch (Throwable $e) {
            error_log('PartnerReclaim failed: ' . $e->getMessage());
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    public static function houseUserId(): int
    {
        PartnerSchema::ensure();
        $db = Database::getInstance()->getConnection();

        $stmt = $db->query(
            'SELECT id FROM users WHERE is_house_wallet = 1 AND deleted_at IS NULL ORDER BY id ASC LIMIT 1'
        );
        $id = (int)$stmt->fetchColumn();
        if ($id > 0) {
            return $id;
        }

        $userModel = new User();
        $existing = $userModel->findByEmail(self::HOUSE_EMAIL);
        if ($existing) {
            $db->prepare(
                'UPDATE users
                 SET is_house_wallet = 1, is_partner = 1, full_name = :name,
                     profile_blocked = 1, updated_at = NOW()
                 WHERE id = :id'
            )->execute([
                ':name' => 'Рабочий кошелёк Aru',
                ':id' => (int)$existing['id'],
            ]);
            return (int)$existing['id'];
        }

        $userModel->create(
            self::HOUSE_EMAIL,
            bin2hex(random_bytes(24)),
            bin2hex(random_bytes(16)),
            '127.0.0.1',
            null
        );
        $created = $userModel->findByEmail(self::HOUSE_EMAIL);
        if (!$created) {
            throw new RuntimeException('Не удалось создать рабочий кошелёк');
        }

        $db->prepare(
            'UPDATE users
             SET full_name = :name, is_partner = 1, is_house_wallet = 1,
                 email_verified = 0, profile_blocked = 1, updated_at = NOW()
             WHERE id = :id'
        )->execute([
            ':name' => 'Рабочий кошелёк Aru',
            ':id' => (int)$created['id'],
        ]);

        return (int)$created['id'];
    }

    /**
     * @return int сколько аккаунтов передано
     */
    public static function reclaimDormant(): int
    {
        PartnerSchema::ensure();
        $houseId = self::houseUserId();
        $db = Database::getInstance()->getConnection();
        $months = (int)self::INACTIVE_MONTHS;

        $sql = "
            SELECT u.id
            FROM users u
            WHERE COALESCE(u.is_house_wallet, 0) = 0
              AND u.id <> :house_id
              AND COALESCE(u.last_activity_at, u.created_at) < DATE_SUB(NOW(), INTERVAL {$months} MONTH)
              AND (
                    COALESCE((SELECT SUM(amount) FROM partner_earnings e WHERE e.partner_user_id = u.id), 0)
                    - COALESCE((
                        SELECT SUM(amount) FROM partner_payouts p
                        WHERE p.partner_user_id = u.id
                          AND p.status IN ('pending','approved','paid')
                    ), 0) > 0.009
                 OR EXISTS (
                        SELECT 1 FROM users r
                        WHERE r.referred_by_partner_user_id = u.id
                        LIMIT 1
                    )
                 OR EXISTS (
                        SELECT 1 FROM partner_companies c
                        WHERE c.partner_user_id = u.id
                        LIMIT 1
                    )
                 OR (COALESCE(u.is_partner, 0) = 1 AND u.earnings_redirect_user_id IS NULL)
              )
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([':house_id' => $houseId]);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

        $moved = 0;
        foreach ($ids as $id) {
            if (self::reclaimOne((int)$id, $houseId)) {
                $moved++;
            }
        }

        return $moved;
    }

    private static function reclaimOne(int $userId, int $houseId): bool
    {
        if ($userId <= 0 || $userId === $houseId) {
            return false;
        }

        $db = Database::getInstance()->getConnection();
        $partner = new Partner();
        $userModel = new User();
        $companyModel = new PartnerCompany();
        $earningModel = new PartnerEarning();

        $db->beginTransaction();
        try {
            $lock = $db->prepare('SELECT id, is_house_wallet, earnings_redirect_user_id FROM users WHERE id = :id FOR UPDATE');
            $lock->execute([':id' => $userId]);
            $row = $lock->fetch();
            if (!$row || !empty($row['is_house_wallet'])) {
                $db->rollBack();
                return false;
            }

            $balance = $partner->getBalance($userId);
            $available = (float)$balance['available'];
            $referrals = $userModel->reassignReferrals($userId, $houseId);
            $companies = $companyModel->reassignAll($userId, $houseId);

            $redirected = false;
            if (empty($row['earnings_redirect_user_id'])) {
                $db->prepare(
                    'UPDATE users
                     SET earnings_redirect_user_id = :house, partner_reclaimed_at = NOW(), updated_at = NOW()
                     WHERE id = :id'
                )->execute([
                    ':house' => $houseId,
                    ':id' => $userId,
                ]);
                $redirected = true;
            }

            if ($available > 0) {
                $debit = $earningModel->create([
                    'partner_user_id' => $userId,
                    'amount' => -1 * $available,
                    'description' => 'Перевод невыведенного остатка на рабочий кошелёк: аккаунт неактивен '
                        . self::INACTIVE_MONTHS . ' мес.',
                ]);
                $credit = $earningModel->create([
                    'partner_user_id' => $houseId,
                    'amount' => $available,
                    'description' => 'Остаток неактивного аккаунта #' . $userId
                        . ' (' . self::INACTIVE_MONTHS . ' мес. без входа)',
                ]);
                if (!$debit || !$credit) {
                    throw new RuntimeException('Не удалось перевести остаток #' . $userId);
                }
            } elseif (!$redirected && $referrals === 0 && $companies === 0) {
                $db->rollBack();
                return false;
            }

            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('PartnerReclaim user ' . $userId . ': ' . $e->getMessage());
            return false;
        }

        ActivityLogger::info(
            'partner.reclaim_dormant',
            'Неактивный аккаунт передан на рабочий кошелёк',
            'user',
            $userId,
            [
                'house_user_id' => $houseId,
                'available' => $available,
                'referrals' => $referrals,
                'companies' => $companies,
                'months' => self::INACTIVE_MONTHS,
            ],
            $userId
        );

        return true;
    }
}

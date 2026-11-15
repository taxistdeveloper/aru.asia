<?php

/**
 * СВОДНАЯ МОДЕЛЬ ПАРТНЁРСКОЙ ПРОГРАММЫ
 *
 * Баланс, список партнёров для админки, агрегаты выплат.
 */

class Partner
{
    private $db;
    private $companyModel;
    private $earningModel;
    private $payoutModel;

    public function __construct()
    {
        PartnerSchema::ensure();
        $this->db = Database::getInstance()->getConnection();
        $this->companyModel = new PartnerCompany();
        $this->earningModel = new PartnerEarning();
        $this->payoutModel = new PartnerPayout();
    }

    /**
     * Баланс партнёра:
     * earned — всего начислено
     * paid — выплачено (approved+paid)
     * reserved — заявки на вывод в работе (pending+approved+paid)
     * available — можно вывести сейчас
     * remaining — осталось выплатить (earned - paid)
     */
    public function getBalance(int $partnerUserId): array
    {
        $earned = $this->earningModel->sumByPartner($partnerUserId);
        $paid = $this->payoutModel->sumPaidByPartner($partnerUserId);
        $reserved = $this->payoutModel->sumReservedByPartner($partnerUserId);
        $available = max(0, $earned - $reserved);
        $remaining = max(0, $earned - $paid);

        return [
            'earned' => round($earned, 2),
            'paid' => round($paid, 2),
            'reserved' => round($reserved, 2),
            'available' => round($available, 2),
            'remaining' => round($remaining, 2),
            'currency' => 'KZT',
        ];
    }

    /**
     * Список партнёров: пользователи с компаниями / начислениями / выплатами.
     */
    public function getPartnersList(int $limit = 200): array
    {
        $sql = "
            SELECT
                u.id,
                u.email,
                u.full_name,
                u.city,
                u.created_at,
                u.last_activity_at,
                u.is_partner,
                u.is_house_wallet,
                u.partner_reclaimed_at,
                u.earnings_redirect_user_id,
                (SELECT photo FROM user_photos
                 WHERE user_id = u.id AND photo IS NOT NULL AND TRIM(photo) <> ''
                 ORDER BY created_at ASC LIMIT 1) AS main_photo,
                COALESCE(cc.companies_count, 0) AS companies_count,
                COALESCE(rr.referrals_count, 0) AS referrals_count,
                COALESCE(ee.earned, 0) AS earned,
                COALESCE(pp.paid, 0) AS paid,
                COALESCE(rv.reserved, 0) AS reserved
            FROM users u
            LEFT JOIN (
                SELECT partner_user_id, COUNT(*) AS companies_count
                FROM partner_companies
                GROUP BY partner_user_id
            ) cc ON cc.partner_user_id = u.id
            LEFT JOIN (
                SELECT referred_by_partner_user_id AS partner_user_id, COUNT(*) AS referrals_count
                FROM users
                WHERE referred_by_partner_user_id IS NOT NULL
                GROUP BY referred_by_partner_user_id
            ) rr ON rr.partner_user_id = u.id
            LEFT JOIN (
                SELECT partner_user_id, SUM(amount) AS earned
                FROM partner_earnings
                GROUP BY partner_user_id
            ) ee ON ee.partner_user_id = u.id
            LEFT JOIN (
                SELECT partner_user_id, SUM(amount) AS paid
                FROM partner_payouts
                WHERE status IN ('approved','paid')
                GROUP BY partner_user_id
            ) pp ON pp.partner_user_id = u.id
            LEFT JOIN (
                SELECT partner_user_id, SUM(amount) AS reserved
                FROM partner_payouts
                WHERE status IN ('pending','approved','paid')
                GROUP BY partner_user_id
            ) rv ON rv.partner_user_id = u.id
            WHERE (
                    COALESCE(u.is_partner, 0) = 1
                 OR COALESCE(u.is_house_wallet, 0) = 1
                 OR u.partner_reclaimed_at IS NOT NULL
                 OR u.earnings_redirect_user_id IS NOT NULL
                 OR COALESCE(cc.companies_count, 0) > 0
                 OR COALESCE(rr.referrals_count, 0) > 0
                 OR COALESCE(ee.earned, 0) <> 0
                 OR EXISTS (
                     SELECT 1 FROM partner_payouts px WHERE px.partner_user_id = u.id LIMIT 1
                 )
              )
            ORDER BY COALESCE(u.is_house_wallet, 0) DESC, COALESCE(ee.earned, 0) DESC, companies_count DESC, u.id DESC
            LIMIT :lim
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll() ?: [];

        foreach ($rows as &$row) {
            $earned = (float)$row['earned'];
            $paid = (float)$row['paid'];
            $reserved = (float)$row['reserved'];
            $row['earned'] = round($earned, 2);
            $row['paid'] = round($paid, 2);
            $row['reserved'] = round($reserved, 2);
            $row['available'] = round(max(0, $earned - $reserved), 2);
            $row['remaining'] = round(max(0, $earned - $paid), 2);
            $row['companies_count'] = (int)$row['companies_count'];
            $row['referrals_count'] = (int)$row['referrals_count'];
        }
        unset($row);

        return $rows;
    }

    /**
     * Сводка для админки: оплаты рекламы, 50%, ожидание и движения денег.
     */
    public function getFinanceBoard(int $limit = 150): array
    {
        $earned = (float)$this->db->query('SELECT COALESCE(SUM(amount), 0) FROM partner_earnings')->fetchColumn();
        $paid = (float)$this->db->query(
            "SELECT COALESCE(SUM(amount), 0) FROM partner_payouts WHERE status IN ('approved','paid')"
        )->fetchColumn();
        $pending = (float)$this->db->query(
            "SELECT COALESCE(SUM(amount), 0) FROM partner_payouts WHERE status = 'pending'"
        )->fetchColumn();

        $adsStmt = $this->db->prepare(
            "SELECT
                a.id,
                a.advertiser_name,
                a.advertiser_email,
                a.city,
                a.status,
                a.paid_amount,
                a.created_at,
                e.id AS earning_id,
                e.amount AS commission,
                e.created_at AS credited_at,
                e.partner_user_id AS credited_partner_id,
                cu.full_name AS credited_partner_name,
                cu.email AS credited_partner_email,
                COALESCE(pc.partner_user_id, ru.referred_by_partner_user_id) AS link_partner_id,
                COALESCE(cou.full_name, pu.full_name) AS link_partner_name,
                COALESCE(cou.email, pu.email) AS link_partner_email,
                COALESCE(cou.earnings_redirect_user_id, pu.earnings_redirect_user_id) AS link_redirect_id
             FROM ads a
             LEFT JOIN partner_earnings e ON e.ad_id = a.id
             LEFT JOIN users cu ON cu.id = e.partner_user_id
             LEFT JOIN users ru
                ON a.advertiser_email <> ''
               AND LOWER(TRIM(ru.email)) = LOWER(TRIM(a.advertiser_email))
             LEFT JOIN users pu ON pu.id = ru.referred_by_partner_user_id
             LEFT JOIN partner_companies pc
                ON pc.contact_email IS NOT NULL
               AND pc.contact_email <> ''
               AND LOWER(TRIM(pc.contact_email)) = LOWER(TRIM(a.advertiser_email))
             LEFT JOIN users cou ON cou.id = pc.partner_user_id
             ORDER BY a.created_at DESC
             LIMIT :lim"
        );
        $adsStmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $adsStmt->execute();
        $ads = $adsStmt->fetchAll() ?: [];

        $waiting = 0;
        foreach ($ads as &$ad) {
            $paidAmount = (float)$ad['paid_amount'];
            $hasPartner = !empty($ad['link_partner_id']) || !empty($ad['credited_partner_id']);
            if (!empty($ad['earning_id'])) {
                $ad['commission_state'] = 'credited';
            } elseif ($paidAmount > 0 && $hasPartner) {
                $ad['commission_state'] = 'waiting';
                $waiting++;
            } elseif ($paidAmount > 0) {
                $ad['commission_state'] = 'no_partner';
            } else {
                $ad['commission_state'] = 'unpaid';
            }
            $ad['expected_commission'] = round($paidAmount * PartnerReferral::COMMISSION_RATE, 2);
        }
        unset($ad);

        $earnStmt = $this->db->query(
            "SELECT e.id, e.partner_user_id, e.amount, e.description, e.created_at, e.ad_id,
                    u.full_name, u.email, u.is_house_wallet
             FROM partner_earnings e
             JOIN users u ON u.id = e.partner_user_id
             ORDER BY e.created_at DESC
             LIMIT 80"
        );
        $payStmt = $this->db->query(
            "SELECT p.id, p.partner_user_id, p.amount, p.status, p.created_at, p.payment_details,
                    u.full_name, u.email
             FROM partner_payouts p
             JOIN users u ON u.id = p.partner_user_id
             ORDER BY p.created_at DESC
             LIMIT 80"
        );

        return [
            'totals' => [
                'earned' => round($earned, 2),
                'paid' => round($paid, 2),
                'pending_payouts' => round($pending, 2),
                'remaining' => round(max(0, $earned - $paid), 2),
                'waiting_ads' => $waiting,
            ],
            'ads' => $ads,
            'earnings' => $earnStmt->fetchAll() ?: [],
            'payouts' => $payStmt->fetchAll() ?: [],
        ];
    }

    public function getPartnerCabinet(int $partnerUserId): ?array
    {
        $userModel = new User();
        $user = $userModel->findById($partnerUserId);
        if (!$user || !empty($user['deleted_at'])) {
            return null;
        }

        return [
            'user' => $user,
            'balance' => $this->getBalance($partnerUserId),
            'companies' => $this->companyModel->getByPartner($partnerUserId),
            'earnings' => $this->earningModel->getByPartner($partnerUserId),
            'payouts' => $this->payoutModel->getByPartner($partnerUserId),
            'companies_count' => $this->companyModel->countByPartner($partnerUserId),
        ];
    }

    public static function formatMoney(float $amount, string $currency = 'KZT'): string
    {
        return number_format($amount, 0, '.', ' ') . ' ' . $currency;
    }

    public static function statusLabel(string $status): string
    {
        $map = [
            'pending' => 'На проверке',
            'approved' => 'Одобрено',
            'rejected' => 'Отклонено',
            'active' => 'Активна',
            'paid' => 'Выплачено',
        ];
        return $map[$status] ?? $status;
    }

    public static function statusBadgeClass(string $status): string
    {
        $map = [
            'pending' => 'bg-warning text-dark',
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            'active' => 'bg-primary',
            'paid' => 'bg-success',
        ];
        return $map[$status] ?? 'bg-secondary';
    }
}

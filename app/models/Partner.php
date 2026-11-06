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
                (SELECT photo FROM user_photos
                 WHERE user_id = u.id AND photo IS NOT NULL AND TRIM(photo) <> ''
                 ORDER BY created_at ASC LIMIT 1) AS main_photo,
                COALESCE(cc.companies_count, 0) AS companies_count,
                COALESCE(ee.earned, 0) AS earned,
                COALESCE(pp.paid, 0) AS paid
            FROM users u
            LEFT JOIN (
                SELECT partner_user_id, COUNT(*) AS companies_count
                FROM partner_companies
                GROUP BY partner_user_id
            ) cc ON cc.partner_user_id = u.id
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
            WHERE u.deleted_at IS NULL
              AND (
                    COALESCE(cc.companies_count, 0) > 0
                 OR COALESCE(ee.earned, 0) <> 0
                 OR EXISTS (
                     SELECT 1 FROM partner_payouts px WHERE px.partner_user_id = u.id LIMIT 1
                 )
              )
            ORDER BY COALESCE(ee.earned, 0) DESC, companies_count DESC, u.id DESC
            LIMIT :lim
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll() ?: [];

        foreach ($rows as &$row) {
            $earned = (float)$row['earned'];
            $paid = (float)$row['paid'];
            $row['earned'] = round($earned, 2);
            $row['paid'] = round($paid, 2);
            $row['remaining'] = round(max(0, $earned - $paid), 2);
            $row['companies_count'] = (int)$row['companies_count'];
        }
        unset($row);

        return $rows;
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

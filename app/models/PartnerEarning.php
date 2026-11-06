<?php

/**
 * МОДЕЛЬ НАЧИСЛЕНИЙ ПАРТНЁРАМ
 */

class PartnerEarning
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $data): ?int
    {
        $partnerUserId = (int)($data['partner_user_id'] ?? 0);
        $amount = (float)($data['amount'] ?? 0);
        $adId = !empty($data['ad_id']) ? (int)$data['ad_id'] : null;

        if ($partnerUserId <= 0 || $amount == 0.0) {
            return null;
        }

        if ($adId) {
            $existing = $this->findByAdId($adId);
            if ($existing) {
                return (int)$existing['id'];
            }
        }

        $stmt = $this->db->prepare(
            "INSERT INTO partner_earnings
             (partner_user_id, company_id, ad_id, amount, currency, description, created_by_admin_id, created_at)
             VALUES
             (:partner_user_id, :company_id, :ad_id, :amount, :currency, :description, :created_by_admin_id, NOW())"
        );

        try {
            $ok = $stmt->execute([
                ':partner_user_id' => $partnerUserId,
                ':company_id' => !empty($data['company_id']) ? (int)$data['company_id'] : null,
                ':ad_id' => $adId,
                ':amount' => $amount,
                ':currency' => $data['currency'] ?? 'KZT',
                ':description' => trim((string)($data['description'] ?? '')) ?: null,
                ':created_by_admin_id' => !empty($data['created_by_admin_id']) ? (int)$data['created_by_admin_id'] : null,
            ]);
        } catch (PDOException $e) {
            // UNIQUE ad_id — повторное начисление
            if ($adId && $this->findByAdId($adId)) {
                return (int)$this->findByAdId($adId)['id'];
            }
            return null;
        }

        return $ok ? (int)$this->db->lastInsertId() : null;
    }

    public function findByAdId(int $adId)
    {
        if ($adId <= 0) {
            return null;
        }
        $stmt = $this->db->prepare('SELECT * FROM partner_earnings WHERE ad_id = :ad_id LIMIT 1');
        $stmt->execute([':ad_id' => $adId]);
        return $stmt->fetch() ?: null;
    }

    public function getByPartner(int $partnerUserId, int $limit = 100): array
    {
        $stmt = $this->db->prepare(
            "SELECT e.*, c.company_name
             FROM partner_earnings e
             LEFT JOIN partner_companies c ON c.id = e.company_id
             WHERE e.partner_user_id = :uid
             ORDER BY e.created_at DESC
             LIMIT :lim"
        );
        $stmt->bindValue(':uid', $partnerUserId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    public function sumByPartner(int $partnerUserId): float
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(SUM(amount), 0) FROM partner_earnings WHERE partner_user_id = :uid'
        );
        $stmt->execute([':uid' => $partnerUserId]);
        return (float)$stmt->fetchColumn();
    }
}

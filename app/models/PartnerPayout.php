<?php

/**
 * МОДЕЛЬ ВЫПЛАТ ПАРТНЁРАМ
 */

class PartnerPayout
{
    private $db;

    public const STATUSES = ['pending', 'approved', 'paid', 'rejected'];

    public function __construct()
    {
        PartnerSchema::ensure();
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $data): ?int
    {
        $partnerUserId = (int)($data['partner_user_id'] ?? 0);
        $amount = (float)($data['amount'] ?? 0);
        $paymentDetails = trim((string)($data['payment_details'] ?? ''));

        if ($partnerUserId <= 0 || $amount <= 0 || $paymentDetails === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO partner_payouts
             (partner_user_id, amount, currency, payment_details, status, created_at)
             VALUES
             (:partner_user_id, :amount, :currency, :payment_details, 'pending', NOW())"
        );

        $ok = $stmt->execute([
            ':partner_user_id' => $partnerUserId,
            ':amount' => $amount,
            ':currency' => $data['currency'] ?? 'KZT',
            ':payment_details' => $paymentDetails,
        ]);

        return $ok ? (int)$this->db->lastInsertId() : null;
    }

    public function findById(int $id)
    {
        $stmt = $this->db->prepare('SELECT * FROM partner_payouts WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function getByPartner(int $partnerUserId, int $limit = 100): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM partner_payouts WHERE partner_user_id = :uid ORDER BY created_at DESC LIMIT :lim'
        );
        $stmt->bindValue(':uid', $partnerUserId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Сумма выплат в статусах, которые «замораживают» или уже списали баланс.
     * pending/approved/paid — уменьшают доступный баланс; rejected — нет.
     */
    public function sumReservedByPartner(int $partnerUserId): float
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(amount), 0)
             FROM partner_payouts
             WHERE partner_user_id = :uid
               AND status IN ('pending','approved','paid')"
        );
        $stmt->execute([':uid' => $partnerUserId]);
        return (float)$stmt->fetchColumn();
    }

    public function sumPaidByPartner(int $partnerUserId): float
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(amount), 0)
             FROM partner_payouts
             WHERE partner_user_id = :uid
               AND status IN ('approved','paid')"
        );
        $stmt->execute([':uid' => $partnerUserId]);
        return (float)$stmt->fetchColumn();
    }

    public function updateStatus(int $id, string $status, ?int $adminId = null, ?string $adminNote = null): bool
    {
        if (!in_array($status, self::STATUSES, true)) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE partner_payouts
             SET status = :status,
                 admin_note = :admin_note,
                 processed_by_admin_id = :admin_id,
                 processed_at = NOW()
             WHERE id = :id"
        );

        return $stmt->execute([
            ':status' => $status,
            ':admin_note' => $adminNote,
            ':admin_id' => $adminId,
            ':id' => $id,
        ]);
    }

    public function getPending(int $limit = 100): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, u.email AS partner_email, u.full_name AS partner_name
             FROM partner_payouts p
             LEFT JOIN users u ON u.id = p.partner_user_id
             WHERE p.status = 'pending'
             ORDER BY p.created_at ASC
             LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }
}

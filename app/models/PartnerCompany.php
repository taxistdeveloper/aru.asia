<?php

/**
 * МОДЕЛЬ КОМПАНИЙ ПАРТНЁРОВ
 *
 * Компании, приведённые партнёрами.
 * bind_key защищает от перепривязки одной компании другому партнёру.
 */

class PartnerCompany
{
    private $db;

    public const STATUSES = ['pending', 'approved', 'rejected', 'active'];

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Нормализует ключ привязки: email (приоритет) или телефон.
     * Одна компания = один ключ = один партнёр навсегда.
     */
    public static function makeBindKey(?string $email, ?string $phone): ?string
    {
        $email = strtolower(trim((string)$email));
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'email:' . $email;
        }

        $digits = preg_replace('/\D+/', '', (string)$phone);
        if ($digits !== null && strlen($digits) >= 10) {
            // Берём последние 10 цифр — устойчивость к +7 / 8
            return 'phone:' . substr($digits, -10);
        }

        return null;
    }

    /**
     * Создаёт заявку на компанию. Не даёт перепривязать существующий bind_key.
     *
     * @return array{ok:bool,error?:string,id?:int}
     */
    public function create(array $data): array
    {
        $partnerUserId = (int)($data['partner_user_id'] ?? 0);
        $companyName = trim((string)($data['company_name'] ?? ''));
        $contactEmail = trim((string)($data['contact_email'] ?? ''));
        $contactPhone = trim((string)($data['contact_phone'] ?? ''));

        if ($partnerUserId <= 0) {
            return ['ok' => false, 'error' => 'Неверный партнёр'];
        }
        if ($companyName === '') {
            return ['ok' => false, 'error' => 'Укажите название компании'];
        }

        $bindKey = self::makeBindKey($contactEmail, $contactPhone);
        if ($bindKey === null) {
            return ['ok' => false, 'error' => 'Укажите корректный email или телефон компании (от 10 цифр)'];
        }

        $existing = $this->findByBindKey($bindKey);
        if ($existing) {
            if ((int)$existing['partner_user_id'] === $partnerUserId) {
                return ['ok' => false, 'error' => 'Эта компания уже есть в вашем списке'];
            }
            return [
                'ok' => false,
                'error' => 'Эта компания уже закреплена за другим партнёром. Перепривязка запрещена.'
            ];
        }

        $sql = "INSERT INTO partner_companies
                (partner_user_id, company_name, contact_name, contact_email, contact_phone,
                 website, description, status, bind_key, created_at)
                VALUES
                (:partner_user_id, :company_name, :contact_name, :contact_email, :contact_phone,
                 :website, :description, 'pending', :bind_key, NOW())";

        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            ':partner_user_id' => $partnerUserId,
            ':company_name' => $companyName,
            ':contact_name' => trim((string)($data['contact_name'] ?? '')) ?: null,
            ':contact_email' => $contactEmail !== '' ? $contactEmail : null,
            ':contact_phone' => $contactPhone !== '' ? $contactPhone : null,
            ':website' => trim((string)($data['website'] ?? '')) ?: null,
            ':description' => trim((string)($data['description'] ?? '')) ?: null,
            ':bind_key' => $bindKey,
        ]);

        if (!$ok) {
            return ['ok' => false, 'error' => 'Не удалось сохранить компанию'];
        }

        return ['ok' => true, 'id' => (int)$this->db->lastInsertId()];
    }

    public function findById(int $id)
    {
        $stmt = $this->db->prepare('SELECT * FROM partner_companies WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findByBindKey(string $bindKey)
    {
        $stmt = $this->db->prepare('SELECT * FROM partner_companies WHERE bind_key = :k LIMIT 1');
        $stmt->execute([':k' => $bindKey]);
        return $stmt->fetch() ?: null;
    }

    public function getByPartner(int $partnerUserId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM partner_companies WHERE partner_user_id = :uid ORDER BY created_at DESC'
        );
        $stmt->execute([':uid' => $partnerUserId]);
        return $stmt->fetchAll() ?: [];
    }

    public function countByPartner(int $partnerUserId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM partner_companies WHERE partner_user_id = :uid'
        );
        $stmt->execute([':uid' => $partnerUserId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Обновляет статус. partner_user_id и bind_key НЕ меняются — защита от перепривязки.
     */
    public function updateStatus(int $id, string $status, ?string $adminNote = null): bool
    {
        if (!in_array($status, self::STATUSES, true)) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE partner_companies
             SET status = :status, admin_note = :admin_note, updated_at = NOW()
             WHERE id = :id'
        );

        return $stmt->execute([
            ':status' => $status,
            ':admin_note' => $adminNote,
            ':id' => $id,
        ]);
    }

    /**
     * Явный запрет перепривязки: метод всегда возвращает false.
     */
    public function rebind(int $companyId, int $newPartnerUserId): bool
    {
        // Защита от перепривязки — компания навсегда закреплена за первым партнёром
        return false;
    }

    /**
     * Обновляет название, если оно пустое или выглядит как заглушка.
     */
    public function updateNameIfEmpty(int $id, string $name): bool
    {
        $name = trim($name);
        if ($name === '') {
            return false;
        }

        $company = $this->findById($id);
        if (!$company) {
            return false;
        }

        $current = trim((string)($company['company_name'] ?? ''));
        $isPlaceholder = $current === '' || stripos($current, 'Бизнес ') === 0;
        if (!$isPlaceholder) {
            return true;
        }

        $stmt = $this->db->prepare(
            'UPDATE partner_companies SET company_name = :name, updated_at = NOW() WHERE id = :id'
        );
        return $stmt->execute([':name' => $name, ':id' => $id]);
    }
}

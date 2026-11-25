<?php

/**
 * ОПЛАТЫ И ЧЕКИ
 *
 * Поток:
 *  1. Пользователь нажимает «СОГЛАСЕН ОПЛАТИТЬ» → createPending() (статус pending, фиксируем согласие, IP, время).
 *  2. Администратор в «Оплаты и чеки» нажимает «Оплата получена» → confirm():
 *       - формируется чек с номером ARU-ГГГГ-NNNNNN;
 *       - экземпляр 1 — на email, указанный при регистрации;
 *       - экземпляр 2 — в «Сообщения» пользователя от Администрации;
 *       - экземпляр 3 — архив: таблица payments + файл app/storage/receipts/ГГГГ/ММ-Месяц/ARU-....html
 *  Позже сюда же подключается платёжный шлюз / онлайн-касса (ОФД): их callback вызывает confirm().
 */
class PaymentService
{
    private static $ready = false;

    public const MONTHS = [
        1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель', 5 => 'Май', 6 => 'Июнь',
        7 => 'Июль', 8 => 'Август', 9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь',
    ];

    public const KIND_LABELS = [
        'ad' => 'Баннерная реклама',
        'event' => 'Размещение мероприятия',
    ];

    /** Создаёт таблицу payments при первом обращении (idempotent). */
    public static function ensureSchema(): void
    {
        if (self::$ready) {
            return;
        }
        $db = Database::getInstance()->getConnection();
        $db->exec("
            CREATE TABLE IF NOT EXISTS payments (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                kind ENUM('ad','event') NOT NULL,
                ad_id INT UNSIGNED NULL,
                event_id INT UNSIGNED NULL,
                user_id INT UNSIGNED NULL,
                payer_email VARCHAR(255) NOT NULL,
                payer_name VARCHAR(255) NULL,
                description VARCHAR(500) NOT NULL,
                amount DECIMAL(12,2) NOT NULL,
                currency VARCHAR(3) NOT NULL DEFAULT 'KZT',
                status ENUM('pending','paid','cancelled') NOT NULL DEFAULT 'pending',
                agreed_at DATETIME NOT NULL,
                agreed_ip VARCHAR(64) NULL,
                paid_at DATETIME NULL,
                confirmed_by_admin_id INT UNSIGNED NULL,
                receipt_number VARCHAR(32) NULL,
                receipt_path VARCHAR(255) NULL,
                receipt_email_sent_at DATETIME NULL,
                receipt_message_id INT UNSIGNED NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uk_payments_receipt_number (receipt_number),
                KEY idx_payments_status (status),
                KEY idx_payments_paid_at (paid_at),
                KEY idx_payments_user (user_id),
                KEY idx_payments_ad (ad_id),
                KEY idx_payments_event (event_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        self::$ready = true;
    }

    private static function db(): PDO
    {
        self::ensureSchema();
        return Database::getInstance()->getConnection();
    }

    /** Фиксирует согласие пользователя на оплату. Возвращает id оплаты или null. */
    public static function createPending(array $data): ?int
    {
        try {
            $stmt = self::db()->prepare("
                INSERT INTO payments (kind, ad_id, event_id, user_id, payer_email, payer_name, description, amount, currency, status, agreed_at, agreed_ip)
                VALUES (:kind, :ad_id, :event_id, :user_id, :payer_email, :payer_name, :description, :amount, 'KZT', 'pending', NOW(), :ip)
            ");
            $stmt->execute([
                ':kind' => $data['kind'],
                ':ad_id' => $data['ad_id'] ?? null,
                ':event_id' => $data['event_id'] ?? null,
                ':user_id' => $data['user_id'] ?? null,
                ':payer_email' => strtolower(trim((string)$data['payer_email'])),
                ':payer_name' => $data['payer_name'] ?? null,
                ':description' => mb_substr((string)$data['description'], 0, 500),
                ':amount' => (float)$data['amount'],
                ':ip' => Helper::getClientIp(),
            ]);
            return (int)self::db()->lastInsertId();
        } catch (Throwable $e) {
            error_log('PaymentService::createPending: ' . $e->getMessage());
            return null;
        }
    }

    public static function find(int $id): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM payments WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function listByStatus(string $status, int $limit = 200): array
    {
        $stmt = self::db()->prepare("SELECT * FROM payments WHERE status = :s ORDER BY created_at DESC LIMIT :l");
        $stmt->bindValue(':s', $status);
        $stmt->bindValue(':l', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Дерево архива: [год => [месяц => ['count'=>..,'sum'=>..]]], новые сверху. */
    public static function archiveTree(): array
    {
        $rows = self::db()->query("
            SELECT YEAR(paid_at) y, MONTH(paid_at) m, COUNT(*) c, SUM(amount) s
            FROM payments WHERE status = 'paid' AND paid_at IS NOT NULL
            GROUP BY YEAR(paid_at), MONTH(paid_at)
            ORDER BY y DESC, m ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
        $tree = [];
        foreach ($rows as $r) {
            $tree[(int)$r['y']][(int)$r['m']] = ['count' => (int)$r['c'], 'sum' => (float)$r['s']];
        }
        return $tree;
    }

    public static function receiptsForMonth(int $year, int $month): array
    {
        $stmt = self::db()->prepare("
            SELECT * FROM payments
            WHERE status = 'paid' AND YEAR(paid_at) = :y AND MONTH(paid_at) = :m
            ORDER BY paid_at DESC
        ");
        $stmt->execute([':y' => $year, ':m' => $month]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function receiptsForUser(int $userId): array
    {
        $stmt = self::db()->prepare("SELECT * FROM payments WHERE user_id = ? AND status = 'paid' ORDER BY paid_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function cancel(int $id): bool
    {
        $stmt = self::db()->prepare("UPDATE payments SET status = 'cancelled' WHERE id = ? AND status = 'pending'");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Подтверждает оплату и выдаёт три экземпляра чека.
     * Возвращает ['ok'=>bool, 'error'=>?string, 'email'=>bool, 'message'=>bool].
     */
    public static function confirm(int $id, int $adminId): array
    {
        $db = self::db();
        $payment = self::find($id);
        if (!$payment) {
            return ['ok' => false, 'error' => 'Оплата не найдена'];
        }
        if ($payment['status'] !== 'pending') {
            return ['ok' => false, 'error' => 'Оплата уже обработана (статус: ' . $payment['status'] . ')'];
        }

        $paidAt = date('Y-m-d H:i:s');
        $receiptNumber = sprintf('ARU-%s-%06d', date('Y', strtotime($paidAt)), $id);

        $stmt = $db->prepare("
            UPDATE payments SET status = 'paid', paid_at = :paid_at, confirmed_by_admin_id = :admin, receipt_number = :num
            WHERE id = :id AND status = 'pending'
        ");
        $stmt->execute([':paid_at' => $paidAt, ':admin' => $adminId, ':num' => $receiptNumber, ':id' => $id]);
        if ($stmt->rowCount() === 0) {
            return ['ok' => false, 'error' => 'Не удалось подтвердить оплату'];
        }
        $payment = self::find($id);

        // Экземпляр 3 — архив (файл в папке ГГГГ/ММ-Месяц)
        $html = self::renderReceiptHtml($payment);
        $relPath = self::archiveRelativePath($payment);
        $absPath = self::storageRoot() . '/' . $relPath;
        if (!is_dir(dirname($absPath))) {
            @mkdir(dirname($absPath), 0750, true);
        }
        if (@file_put_contents($absPath, $html) !== false) {
            $db->prepare("UPDATE payments SET receipt_path = ? WHERE id = ?")->execute([$relPath, $id]);
        } else {
            error_log("PaymentService: не удалось сохранить файл чека {$absPath}");
        }

        // Экземпляр 1 — email, указанный при регистрации
        $user = !empty($payment['user_id']) ? (new User())->findById((int)$payment['user_id']) : null;
        $email = ($user && !empty($user['email'])) ? $user['email'] : $payment['payer_email'];
        $emailSent = false;
        try {
            $emailSent = (new EmailService())->send($email, 'Чек об оплате № ' . $receiptNumber . ' — Aru', $html);
        } catch (Throwable $e) {
            error_log('PaymentService email: ' . $e->getMessage());
        }
        if ($emailSent) {
            $db->prepare("UPDATE payments SET receipt_email_sent_at = NOW() WHERE id = ?")->execute([$id]);
        }

        // Экземпляр 2 — сообщение пользователю на сайте от Администрации
        $messageSent = false;
        if ($user) {
            $text = "Оплата получена. Чек № {$receiptNumber}\n\n"
                . "Услуга: " . $payment['description'] . "\n"
                . "Сумма: " . number_format((float)$payment['amount'], 0, ',', ' ') . " ₸\n"
                . "Дата оплаты: " . date('d.m.Y H:i', strtotime($paidAt)) . "\n\n"
                . "Открыть чек: " . self::absoluteUrl('receipts/view?id=' . $id) . "\n\n"
                . "Копия чека отправлена на " . $email . ".\n"
                . "После оплаты и выхода публикации средства не возвращаются.";
            $messageModel = new Message();
            if ($messageModel->send($adminId, (int)$user['id'], $text)) {
                $messageSent = true;
                $msgId = (int)$db->lastInsertId();
                if ($msgId) {
                    $db->prepare("UPDATE payments SET receipt_message_id = ? WHERE id = ?")->execute([$msgId, $id]);
                }
                try {
                    (new PushNotificationService())->sendAdminNotification((int)$user['id'], 'Оплата получена', 'Чек № ' . $receiptNumber . ' — в сообщениях.');
                } catch (Throwable $e) {
                    error_log('PaymentService push: ' . $e->getMessage());
                }
            }
        }

        if (class_exists('ActivityLogger')) {
            ActivityLogger::info('payment.confirm', 'Оплата подтверждена, чек выдан', 'payment', $id, [
                'receipt' => $receiptNumber, 'email' => $emailSent, 'message' => $messageSent,
            ]);
        }

        return ['ok' => true, 'error' => null, 'email' => $emailSent, 'message' => $messageSent, 'receipt' => $receiptNumber];
    }

    /** Корень архива чеков (вне публичного uploads/, закрыт от прямого доступа). */
    public static function storageRoot(): string
    {
        $root = dirname(__DIR__) . '/storage/receipts';
        if (!is_dir($root)) {
            @mkdir($root, 0750, true);
        }
        $deny = dirname($root) . '/.htaccess';
        if (!file_exists($deny)) {
            @file_put_contents($deny, "Require all denied\nDeny from all\n");
        }
        return $root;
    }

    /** Папка месяца: «11-Ноябрь». */
    public static function monthFolder(int $month): string
    {
        return sprintf('%02d-%s', $month, self::MONTHS[$month] ?? (string)$month);
    }

    public static function archiveRelativePath(array $payment): string
    {
        $ts = strtotime((string)$payment['paid_at']);
        return date('Y', $ts) . '/' . self::monthFolder((int)date('n', $ts)) . '/' . $payment['receipt_number'] . '.html';
    }

    /** HTML чека: из архива, а если файла нет — формируется заново из БД. */
    public static function receiptHtml(array $payment): string
    {
        if (!empty($payment['receipt_path'])) {
            $abs = self::storageRoot() . '/' . $payment['receipt_path'];
            if (is_file($abs)) {
                return (string)file_get_contents($abs);
            }
        }
        return self::renderReceiptHtml($payment);
    }

    private static function absoluteUrl(string $path): string
    {
        $base = defined('BASE_URL') ? BASE_URL : '/';
        if (!preg_match('#^https?://#', $base)) {
            $host = $_SERVER['HTTP_HOST'] ?? 'aru.asia';
            $base = 'https://' . $host . '/' . ltrim($base, '/');
        }
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }

    private static function cfg(string $name, string $default): string
    {
        return defined($name) && (string)constant($name) !== '' ? (string)constant($name) : $default;
    }

    public static function renderReceiptHtml(array $p): string
    {
        $e = function ($v) {
            return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
        };
        $seller = self::cfg('RECEIPT_COMPANY_NAME', 'Aru (aru.asia)');
        $bin = self::cfg('RECEIPT_COMPANY_BIN', '—');
        $address = self::cfg('RECEIPT_COMPANY_ADDRESS', '—');
        $kkm = self::cfg('RECEIPT_KKM_RNM', '');
        $fiscalSign = self::cfg('RECEIPT_OFD_NAME', '');
        $amount = number_format((float)$p['amount'], 2, ',', ' ');
        $paidAt = !empty($p['paid_at']) ? date('d.m.Y H:i:s', strtotime($p['paid_at'])) : '—';
        $kind = self::KIND_LABELS[$p['kind']] ?? $p['kind'];
        $ref = $p['kind'] === 'ad' ? ('Реклама № ' . (int)$p['ad_id']) : ('Мероприятие № ' . (int)$p['event_id']);

        $fiscalRows = '';
        if ($kkm !== '') {
            $fiscalRows .= '<tr><td>РНМ ККМ</td><td>' . $e($kkm) . '</td></tr>';
        }
        if ($fiscalSign !== '') {
            $fiscalRows .= '<tr><td>ОФД</td><td>' . $e($fiscalSign) . '</td></tr>';
        }

        return '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8"><title>Чек № ' . $e($p['receipt_number']) . '</title></head>'
            . '<body style="margin:0;padding:24px;background:#f4f5f7;font-family:Arial,Helvetica,sans-serif;color:#1a202c;">'
            . '<div style="max-width:420px;margin:0 auto;background:#fff;border:1px dashed #a0aec0;border-radius:8px;padding:24px;">'
            . '<div style="text-align:center;font-weight:bold;font-size:18px;">' . $e($seller) . '</div>'
            . '<div style="text-align:center;font-size:12px;color:#4a5568;">БИН/ИИН: ' . $e($bin) . '<br>' . $e($address) . '</div>'
            . '<hr style="border:none;border-top:1px dashed #a0aec0;margin:14px 0;">'
            . '<div style="text-align:center;font-weight:bold;font-size:16px;">ЧЕК ОБ ОПЛАТЕ № ' . $e($p['receipt_number']) . '</div>'
            . '<div style="text-align:center;font-size:12px;color:#4a5568;">Продажа · ' . $e($paidAt) . '</div>'
            . '<table style="width:100%;font-size:14px;margin-top:14px;border-collapse:collapse;" cellpadding="4">'
            . '<tr><td style="color:#4a5568;">Услуга</td><td style="text-align:right;">' . $e($kind) . '</td></tr>'
            . '<tr><td style="color:#4a5568;">Описание</td><td style="text-align:right;">' . $e($p['description']) . '</td></tr>'
            . '<tr><td style="color:#4a5568;">Заказ</td><td style="text-align:right;">' . $e($ref) . '</td></tr>'
            . '<tr><td style="color:#4a5568;">Покупатель</td><td style="text-align:right;">' . $e($p['payer_name'] ?: '—') . '<br>' . $e($p['payer_email']) . '</td></tr>'
            . $fiscalRows
            . '</table>'
            . '<hr style="border:none;border-top:1px dashed #a0aec0;margin:14px 0;">'
            . '<table style="width:100%;font-size:18px;font-weight:bold;"><tr><td>ИТОГО</td><td style="text-align:right;">' . $amount . ' ₸</td></tr></table>'
            . '<div style="font-size:12px;color:#4a5568;margin-top:6px;">Валюта: ' . $e($p['currency']) . '</div>'
            . '<hr style="border:none;border-top:1px dashed #a0aec0;margin:14px 0;">'
            . '<div style="font-size:12px;color:#9b2c2c;">Согласие на оплату получено ' . $e(date('d.m.Y H:i', strtotime($p['agreed_at']))) . '. После оплаты и выхода публикации средства не возвращаются.</div>'
            . '<div style="text-align:center;font-size:12px;color:#718096;margin-top:14px;">Спасибо за оплату!</div>'
            . '</div></body></html>';
    }
}

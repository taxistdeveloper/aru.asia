<?php

/**
 * Партнёрская реферальная программа:
 * ссылка → cookie → привязка при регистрации → 50% от оплаты рекламы навсегда.
 */

class PartnerReferral
{
    public const COOKIE = 'partner_ref';
    public const COMMISSION_RATE = 0.50;
    public const COOKIE_DAYS = 90;

    /** Тарифы как в форме рекламы (KZT) */
    public static function tariffs(): array
    {
        return [
            '1' => ['1' => 5000, '3' => 12000, '6' => 21000],
            '2' => ['1' => 5500, '3' => 13200, '6' => 23100],
            '3' => ['1' => 6000, '3' => 14400, '6' => 25200],
            '4' => ['1' => 6500, '3' => 15600, '6' => 27300],
            '5' => ['1' => 7000, '3' => 16800, '6' => 29400],
            'all' => ['1' => 10000, '3' => 24000, '6' => 42000],
        ];
    }

    public static function calcTariffAmount(string $cityCount, $period): float
    {
        $cityCount = (string)$cityCount;
        $period = (string)$period;
        if (!in_array($period, ['1', '3', '6'], true)) {
            $period = '1';
        }
        $tariffs = self::tariffs();
        return (float)($tariffs[$cityCount][$period] ?? $tariffs['1']['1']);
    }

    public static function periodFromDates(?string $start, ?string $end): string
    {
        $s = strtotime((string)$start);
        $e = strtotime((string)$end);
        if (!$s || !$e || $e <= $s) {
            return '1';
        }
        $months = (int)round(($e - $s) / (30 * 24 * 3600));
        if ($months >= 5) {
            return '6';
        }
        if ($months >= 2) {
            return '3';
        }
        return '1';
    }

    public static function buildLink(string $aruCode): string
    {
        $aruCode = preg_replace('/\D+/', '', $aruCode);
        return rtrim(BASE_URL, '/') . '/r?ref=' . urlencode($aruCode);
    }

    public static function captureFromRequest(): void
    {
        $ref = trim((string)($_GET['ref'] ?? $_GET['partner'] ?? ''));
        $ref = preg_replace('/\D+/', '', $ref);
        if ($ref === '') {
            return;
        }

        $partner = (new User())->findByAruCode($ref);
        if (!$partner) {
            return;
        }

        // Нельзя привязать себя
        if (Helper::isLoggedIn() && (int)Helper::getUserId() === (int)$partner['id']) {
            return;
        }

        setcookie(self::COOKIE, (string)$partner['aru_code'], [
            'expires' => time() + (self::COOKIE_DAYS * 24 * 60 * 60),
            'path' => '/',
            'domain' => '',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        $_COOKIE[self::COOKIE] = (string)$partner['aru_code'];
    }

    public static function getRefCodeFromCookie(): ?string
    {
        $code = preg_replace('/\D+/', '', (string)($_COOKIE[self::COOKIE] ?? ''));
        return $code !== '' ? $code : null;
    }

    public static function resolvePartnerUserIdFromCookie(): ?int
    {
        $code = self::getRefCodeFromCookie();
        if (!$code) {
            return null;
        }
        $partner = (new User())->findByAruCode($code);
        return $partner ? (int)$partner['id'] : null;
    }

    /**
     * Навсегда привязывает нового пользователя к партнёру (перепривязка запрещена).
     * Создаёт запись компании по email.
     */
    public static function attributeNewUser(int $newUserId, string $email): void
    {
        $userModel = new User();
        $user = $userModel->findById($newUserId);
        if (!$user) {
            return;
        }

        // Уже привязан — не меняем
        if (!empty($user['referred_by_partner_user_id'])) {
            self::ensureCompanyForUser($user);
            return;
        }

        $partnerId = self::resolvePartnerUserIdFromCookie();
        if (!$partnerId || $partnerId === $newUserId) {
            return;
        }

        $userModel->setReferredByPartner($newUserId, $partnerId);
        $fresh = $userModel->findById($newUserId);
        if ($fresh) {
            self::ensureCompanyForUser($fresh);
        }
    }

    /**
     * Гарантирует компанию-привязку по email пользователя.
     */
    public static function ensureCompanyForUser(array $user): void
    {
        $partnerId = (int)($user['referred_by_partner_user_id'] ?? 0);
        $email = strtolower(trim((string)($user['email'] ?? '')));
        if ($partnerId <= 0 || $email === '') {
            return;
        }

        $companyModel = new PartnerCompany();
        $bindKey = PartnerCompany::makeBindKey($email, null);
        if ($bindKey === null) {
            return;
        }

        $existing = $companyModel->findByBindKey($bindKey);
        if ($existing) {
            return; // уже чья-то — перепривязка запрещена
        }

        $companyModel->create([
            'partner_user_id' => $partnerId,
            'company_name' => $user['full_name'] ?: ('Бизнес ' . $email),
            'contact_name' => $user['full_name'] ?? null,
            'contact_email' => $email,
            'contact_phone' => null,
            'description' => 'Зарегистрирован по партнёрской ссылке',
        ]);

        // Сразу активная привязка
        $created = $companyModel->findByBindKey($bindKey);
        if ($created) {
            $companyModel->updateStatus((int)$created['id'], 'active');
        }
    }

    /**
     * Находит партнёра для рекламодателя: компания по email, иначе referred_by пользователя.
     */
    public static function findPartnerForAdvertiser(string $advertiserEmail, ?string $advertiserName = null): ?array
    {
        $email = strtolower(trim($advertiserEmail));
        if ($email === '') {
            return null;
        }

        $companyModel = new PartnerCompany();
        $bindKey = PartnerCompany::makeBindKey($email, null);
        $company = $bindKey ? $companyModel->findByBindKey($bindKey) : null;

        if ($company) {
            return [
                'partner_user_id' => (int)$company['partner_user_id'],
                'company_id' => (int)$company['id'],
            ];
        }

        $user = (new User())->findByEmail($email);
        if ($user && !empty($user['referred_by_partner_user_id'])) {
            // Автосоздание компании при первой оплате
            self::ensureCompanyForUser($user);
            $company = $bindKey ? $companyModel->findByBindKey($bindKey) : null;
            return [
                'partner_user_id' => (int)$user['referred_by_partner_user_id'],
                'company_id' => $company ? (int)$company['id'] : null,
            ];
        }

        return null;
    }

    /**
     * Автоначисление 50% от оплаты рекламы. Идемпотентно по ad_id.
     *
     * @return array{ok:bool,amount?:float,error?:string}
     */
    public static function creditFromAd(array $ad): array
    {
        $adId = (int)($ad['id'] ?? 0);
        $paid = (float)($ad['paid_amount'] ?? 0);

        if ($adId <= 0) {
            return ['ok' => false, 'error' => 'no_ad'];
        }
        if ($paid <= 0) {
            return ['ok' => false, 'error' => 'no_amount'];
        }

        $earningModel = new PartnerEarning();
        if ($earningModel->findByAdId($adId)) {
            return ['ok' => false, 'error' => 'already_credited'];
        }

        $partner = self::findPartnerForAdvertiser(
            (string)($ad['advertiser_email'] ?? ''),
            $ad['advertiser_name'] ?? null
        );

        if (!$partner) {
            return ['ok' => false, 'error' => 'no_partner'];
        }

        // Нельзя начислить самому себе
        $advertiserUser = (new User())->findByEmail((string)($ad['advertiser_email'] ?? ''));
        if ($advertiserUser && (int)$advertiserUser['id'] === (int)$partner['partner_user_id']) {
            return ['ok' => false, 'error' => 'self'];
        }

        $commission = round($paid * self::COMMISSION_RATE, 2);
        if ($commission <= 0) {
            return ['ok' => false, 'error' => 'zero'];
        }

        // Обновим имя компании, если было заглушкой
        if (!empty($partner['company_id']) && !empty($ad['advertiser_name'])) {
            (new PartnerCompany())->updateNameIfEmpty((int)$partner['company_id'], (string)$ad['advertiser_name']);
        }

        $id = $earningModel->create([
            'partner_user_id' => $partner['partner_user_id'],
            'company_id' => $partner['company_id'],
            'ad_id' => $adId,
            'amount' => $commission,
            'description' => '50% от оплаты рекламы #' . $adId
                . ' (' . Partner::formatMoney($paid) . ')',
        ]);

        if (!$id) {
            return ['ok' => false, 'error' => 'insert_failed'];
        }

        ActivityLogger::info(
            'partner.auto_commission',
            'Автоначисление 50% за рекламу',
            'partner_earning',
            $id,
            [
                'ad_id' => $adId,
                'paid_amount' => $paid,
                'commission' => $commission,
                'partner_user_id' => $partner['partner_user_id'],
            ],
            $partner['partner_user_id']
        );

        return ['ok' => true, 'amount' => $commission];
    }
}

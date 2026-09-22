<?php

/**
 * ПРОВЕРКА СРОКА ДЕЙСТВИЯ SSL-СЕРТИФИКАТА
 *
 * Подключается к сайту по 443 порту, читает сертификат и считает,
 * сколько дней осталось до истечения. Результат кэшируется в app/cache,
 * чтобы не дергать сеть на каждой загрузке страницы админки.
 */
class SslCertificateChecker
{
    /** За сколько дней до истечения начинаем предупреждать */
    public const WARNING_DAYS = 30;

    /** За сколько дней предупреждение становится критическим */
    public const CRITICAL_DAYS = 7;

    /** Время жизни кэша при успешной проверке (6 часов) */
    private const CACHE_TTL = 21600;

    /** Время жизни кэша при ошибке проверки (30 минут) */
    private const CACHE_TTL_ERROR = 1800;

    /** Таймаут подключения к хосту, секунды */
    private const CONNECT_TIMEOUT = 5;

    /** Хосты, для которых проверка не имеет смысла */
    private const LOCAL_HOSTS = ['localhost', '127.0.0.1', '::1', '0.0.0.0'];

    /**
     * Текущий статус сертификата.
     *
     * Возвращает массив с ключами:
     *  state      - ok | warning | critical | expired | unknown | skipped
     *  host       - проверенный домен
     *  days_left  - сколько дней осталось (null, если неизвестно)
     *  valid_to   - timestamp окончания (null, если неизвестно)
     *  valid_from - timestamp начала (null, если неизвестно)
     *  issuer     - кто выдал сертификат
     *  error      - текст ошибки, если проверка не удалась
     *  checked_at - timestamp проверки
     */
    public static function status(?string $host = null, bool $force = false): array
    {
        $host = $host !== null ? $host : self::detectHost();

        if ($host === '' || self::isLocalHost($host)) {
            return self::result('skipped', $host, ['error' => 'Локальный хост, проверка не выполняется']);
        }

        if (!$force) {
            $cached = self::readCache($host);
            if ($cached !== null) {
                return $cached;
            }
        }

        $status = self::fetch($host);
        self::writeCache($host, $status);
        self::logIfNeeded($status);

        return $status;
    }

    /**
     * Нужно ли показывать администратору уведомление.
     */
    public static function needsAttention(array $status): bool
    {
        return in_array($status['state'] ?? 'unknown', ['warning', 'critical', 'expired'], true);
    }

    /**
     * Пишем запись в журнал только при свежей проверке проблемного сертификата,
     * чтобы админ увидел проблему и в журнале, а не только в баннере.
     */
    private static function logIfNeeded(array $status): void
    {
        if (!self::needsAttention($status) || !class_exists('ActivityLogger')) {
            return;
        }

        $message = $status['state'] === 'expired'
            ? 'Срок действия SSL-сертификата истёк'
            : 'SSL-сертификат истекает через ' . (int) $status['days_left'] . ' дн.';

        try {
            ActivityLogger::warning('system.ssl_expiring', $message, 'system', null, [
                'host' => $status['host'],
                'days_left' => $status['days_left'],
                'valid_to' => $status['valid_to'] ? date('c', (int) $status['valid_to']) : null,
            ]);
        } catch (Throwable $e) {
            error_log('SslCertificateChecker - log failed: ' . $e->getMessage());
        }
    }

    /**
     * Реальная проверка сертификата по сети.
     */
    private static function fetch(string $host): array
    {
        if (!function_exists('openssl_x509_parse')) {
            return self::result('unknown', $host, ['error' => 'Расширение OpenSSL недоступно в PHP']);
        }

        $context = stream_context_create([
            'ssl' => [
                'capture_peer_cert' => true,
                'SNI_enabled' => true,
                'peer_name' => $host,
                // Нас интересует только срок действия, а не доверие к цепочке,
                // иначе самоподписанный сертификат вообще не дал бы прочитать даты.
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ]);

        $errorCode = 0;
        $errorMessage = '';
        $client = @stream_socket_client(
            'ssl://' . $host . ':443',
            $errorCode,
            $errorMessage,
            self::CONNECT_TIMEOUT,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if ($client === false) {
            return self::result('unknown', $host, [
                'error' => trim($errorMessage) !== '' ? trim($errorMessage) : 'Не удалось подключиться к ' . $host . ':443',
            ]);
        }

        $params = stream_context_get_params($client);
        fclose($client);

        $certificate = $params['options']['ssl']['peer_certificate'] ?? null;
        if ($certificate === null) {
            return self::result('unknown', $host, ['error' => 'Сервер не вернул сертификат']);
        }

        $parsed = openssl_x509_parse($certificate);
        if (!is_array($parsed) || empty($parsed['validTo_time_t'])) {
            return self::result('unknown', $host, ['error' => 'Не удалось разобрать сертификат']);
        }

        $validTo = (int) $parsed['validTo_time_t'];
        $daysLeft = (int) floor(($validTo - time()) / 86400);

        return self::result(self::stateForDays($daysLeft), $host, [
            'days_left' => $daysLeft,
            'valid_to' => $validTo,
            'valid_from' => isset($parsed['validFrom_time_t']) ? (int) $parsed['validFrom_time_t'] : null,
            'issuer' => self::issuerName($parsed),
        ]);
    }

    private static function stateForDays(int $daysLeft): string
    {
        if ($daysLeft < 0) {
            return 'expired';
        }

        if ($daysLeft <= self::criticalDays()) {
            return 'critical';
        }

        if ($daysLeft <= self::warningDays()) {
            return 'warning';
        }

        return 'ok';
    }

    private static function warningDays(): int
    {
        return defined('SSL_WARNING_DAYS') ? max(1, (int) SSL_WARNING_DAYS) : self::WARNING_DAYS;
    }

    private static function criticalDays(): int
    {
        return defined('SSL_CRITICAL_DAYS') ? max(0, (int) SSL_CRITICAL_DAYS) : self::CRITICAL_DAYS;
    }

    private static function issuerName(array $parsed): string
    {
        $issuer = $parsed['issuer'] ?? [];
        $name = $issuer['O'] ?? ($issuer['CN'] ?? '');

        if (is_array($name)) {
            $name = reset($name);
        }

        return is_string($name) ? $name : '';
    }

    /**
     * Домен, который нужно проверять: из конфига либо из текущего запроса.
     */
    private static function detectHost(): string
    {
        if (defined('SSL_CHECK_HOST') && SSL_CHECK_HOST !== '') {
            return (string) SSL_CHECK_HOST;
        }

        $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');
        $host = strtolower(trim((string) $host));

        // Отрезаем порт (aru.asia:8890 -> aru.asia), IPv6 оставляем как есть
        if ($host !== '' && strpos($host, ']') === false && strpos($host, ':') !== false) {
            $host = substr($host, 0, strpos($host, ':'));
        }

        return $host;
    }

    private static function isLocalHost(string $host): bool
    {
        if (in_array($host, self::LOCAL_HOSTS, true)) {
            return true;
        }

        return (bool) preg_match('/(\.local|\.localhost|\.test|\.loc)$/', $host);
    }

    private static function result(string $state, string $host, array $extra = []): array
    {
        return array_merge([
            'state' => $state,
            'host' => $host,
            'days_left' => null,
            'valid_to' => null,
            'valid_from' => null,
            'issuer' => '',
            'error' => null,
            'checked_at' => time(),
        ], $extra);
    }

    private static function cacheFile(string $host): string
    {
        return __DIR__ . '/../cache/ssl_cert_' . md5($host) . '.json';
    }

    private static function readCache(string $host): ?array
    {
        $file = self::cacheFile($host);
        if (!is_file($file)) {
            return null;
        }

        $raw = @file_get_contents($file);
        if ($raw === false) {
            return null;
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || empty($data['state'])) {
            return null;
        }

        $ttl = in_array($data['state'], ['unknown', 'skipped'], true) ? self::CACHE_TTL_ERROR : self::CACHE_TTL;
        if ((int) ($data['checked_at'] ?? 0) + $ttl < time()) {
            return null;
        }

        // Дни пересчитываем от текущего момента, чтобы кэш не «замораживал» счетчик
        if (!empty($data['valid_to'])) {
            $data['days_left'] = (int) floor(((int) $data['valid_to'] - time()) / 86400);
            $data['state'] = self::stateForDays($data['days_left']);
        }

        return $data;
    }

    private static function writeCache(string $host, array $status): void
    {
        $file = self::cacheFile($host);
        $dir = dirname($file);

        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return;
        }

        @file_put_contents($file, json_encode($status), LOCK_EX);
    }
}

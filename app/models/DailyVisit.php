<?php

/**
 * DAILY VISITS — посещения по разделам сайта.
 *
 * Считает уникальные заходы (1 браузер = 1 визит в раздел за сутки).
 * Не учитывает админку, менеджер-панель, API и действия администратора.
 */
class DailyVisit
{
    private static ?bool $tablesReady = null;

    public static function getSectionLabels(): array
    {
        return [
            'landing' => 'Главная',
            'platform' => 'Платформа',
            'dates' => 'Свидания',
            'events' => 'Мероприятия',
            'map' => 'Карта',
            'info' => 'Инфо',
            'profile' => 'Профиль',
            'messages' => 'Сообщения',
            'ads' => 'Реклама',
            'auth' => 'Вход / регистрация',
        ];
    }

    /**
     * Нормализует URI запроса так же, как Router.
     */
    public static function resolveRequestUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = strtok($uri, '?');
        $scriptName = dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php');
        if ($scriptName !== '/' && $scriptName !== '\\') {
            $uri = str_replace($scriptName, '', $uri);
        }

        if (defined('BASE_URL')) {
            $basePath = parse_url(BASE_URL, PHP_URL_PATH);
            if ($basePath && $basePath !== '/') {
                $basePath = trim($basePath, '/');
                if (strpos($uri, '/' . $basePath) === 0) {
                    $uri = substr($uri, strlen('/' . $basePath) + 1);
                }
            }
        }

        return trim($uri, '/');
    }

    /**
     * Определяет раздел сайта по URI или null, если не считаем.
     */
    public static function resolveSection(?string $uri = null): ?string
    {
        $uri = $uri ?? self::resolveRequestUri();

        if ($uri === '' || $uri === 'home') {
            return 'landing';
        }

        if (preg_match('#^(admin|manager|api)(/|$)#', $uri)) {
            return null;
        }

        if (preg_match('#\.(css|js|png|jpe?g|gif|webp|ico|svg|woff2?|map|txt|xml)$#i', $uri)) {
            return null;
        }

        $map = [
            'platform' => 'platform',
            'info' => 'info',
            'map' => 'map',
        ];

        if (isset($map[$uri])) {
            return $map[$uri];
        }

        if ($uri === 'dates' || str_starts_with($uri, 'dates/')) {
            return 'dates';
        }

        if ($uri === 'events' || str_starts_with($uri, 'events/')) {
            return 'events';
        }

        if (str_starts_with($uri, 'profile')) {
            return 'profile';
        }

        if (str_starts_with($uri, 'messages')) {
            return 'messages';
        }

        if (str_starts_with($uri, 'ads')) {
            return 'ads';
        }

        if (str_starts_with($uri, 'auth/login') || str_starts_with($uri, 'auth/register')) {
            return 'auth';
        }

        return null;
    }

    /**
     * Нужно ли учитывать текущий HTTP-запрос.
     */
    public static function shouldTrackRequest(): bool
    {
        if (php_sapi_name() === 'cli') {
            return false;
        }

        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            return false;
        }

        if (Helper::isAdminLoggedIn()) {
            return false;
        }

        if (Helper::isLoggedIn() && Helper::getUserRole() === 'manager') {
            return false;
        }

        $secPurpose = strtolower($_SERVER['HTTP_SEC_PURPOSE'] ?? $_SERVER['HTTP_PURPOSE'] ?? '');
        if ($secPurpose !== '' && str_contains($secPurpose, 'prefetch')) {
            return false;
        }

        return self::resolveSection() !== null;
    }

    public static function ensureTable(): void
    {
        self::ensureTables();
    }

    public static function ensureTables(): void
    {
        if (self::$tablesReady === true) {
            return;
        }

        try {
            $db = Database::getInstance()->getConnection();

            $db->exec("
                CREATE TABLE IF NOT EXISTS daily_visits (
                    visit_date DATE NOT NULL PRIMARY KEY,
                    visits_total INT UNSIGNED NOT NULL DEFAULT 0,
                    unique_total INT UNSIGNED NOT NULL DEFAULT 0,
                    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $db->exec("
                CREATE TABLE IF NOT EXISTS daily_section_visits (
                    visit_date DATE NOT NULL,
                    section VARCHAR(32) NOT NULL,
                    visits_total INT UNSIGNED NOT NULL DEFAULT 0,
                    unique_total INT UNSIGNED NOT NULL DEFAULT 0,
                    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (visit_date, section)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            self::$tablesReady = true;
        } catch (Exception $e) {
            error_log('DailyVisit::ensureTables error: ' . $e->getMessage());
            self::$tablesReady = false;
        }
    }

    /**
     * Регистрирует визит в соответствующий раздел за сегодня.
     */
    public static function trackToday(): void
    {
        $section = self::resolveSection();
        if ($section === null) {
            return;
        }

        if (!self::shouldTrackRequest()) {
            return;
        }

        $today = date('Y-m-d');
        $cookieName = 'aru_uv_' . $section . '_' . date('Ymd');

        $isUnique = empty($_COOKIE[$cookieName]) && empty($_SESSION[$cookieName]);

        if (!$isUnique) {
            return;
        }

        $_SESSION[$cookieName] = 1;

        $endOfDay = strtotime('tomorrow') - 1;
        setcookie($cookieName, '1', [
            'expires' => $endOfDay,
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        try {
            self::ensureTables();
            $db = Database::getInstance()->getConnection();

            $sectionSql = "
                INSERT INTO daily_section_visits (visit_date, section, visits_total, unique_total)
                VALUES (:visit_date, :section, 1, 1)
                ON DUPLICATE KEY UPDATE
                    visits_total = visits_total + 1,
                    unique_total = unique_total + 1,
                    updated_at = CURRENT_TIMESTAMP
            ";
            $stmt = $db->prepare($sectionSql);
            $stmt->execute([
                ':visit_date' => $today,
                ':section' => $section,
            ]);

            if ($section === 'landing') {
                $landingSql = "
                    INSERT INTO daily_visits (visit_date, visits_total, unique_total)
                    VALUES (:visit_date, 1, 1)
                    ON DUPLICATE KEY UPDATE
                        visits_total = visits_total + 1,
                        unique_total = unique_total + 1,
                        updated_at = CURRENT_TIMESTAMP
                ";
                $landingStmt = $db->prepare($landingSql);
                $landingStmt->execute([':visit_date' => $today]);
            }
        } catch (Exception $e) {
            error_log('DailyVisit::trackToday error: ' . $e->getMessage());
        }
    }
}

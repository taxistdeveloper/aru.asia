<?php

/**
 * DAILY VISITS — посещения по разделам сайта.
 *
 * Считает уникальных людей (не действия):
 * - авторизованный пользователь = 1 человек по user_id;
 * - гость = 1 человек по постоянному cookie.
 * Повторные заходы и сообщения одного человека не увеличивают счётчик.
 * Не учитывает админку, менеджер-панель, API и фоновые AJAX/fetch-запросы.
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
            'auth_login' => 'Вход',
            'auth_register' => 'Регистрация',
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

        // Фоновые эндпоинты сообщений (поллинг) — не заходы в раздел
        if (preg_match('#^messages/(getUnreadCount|getNewMessages|getUnreadDateCount|getUnreadEventCount|getTotalUnreadDatesCount|getTotalUnreadEventsCount|event-updates)$#', $uri)) {
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

        if (str_starts_with($uri, 'auth/login')) {
            return 'auth_login';
        }

        // Страницу регистрации не считаем как «зарегистрировались» —
        // в статистике берём реальные новые аккаунты из users.
        if (str_starts_with($uri, 'auth/register')) {
            return null;
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

        // Только полноценные переходы по страницам, не AJAX/fetch/iframe
        $fetchDest = strtolower($_SERVER['HTTP_SEC_FETCH_DEST'] ?? '');
        if ($fetchDest !== '' && $fetchDest !== 'document') {
            return false;
        }

        $xrw = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        if ($xrw === 'xmlhttprequest') {
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

            $db->exec("
                CREATE TABLE IF NOT EXISTS daily_section_visitors (
                    visit_date DATE NOT NULL,
                    section VARCHAR(32) NOT NULL,
                    visitor_key VARCHAR(64) NOT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (visit_date, section, visitor_key),
                    KEY idx_section_period (section, visit_date)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            self::$tablesReady = true;
        } catch (Exception $e) {
            error_log('DailyVisit::ensureTables error: ' . $e->getMessage());
            self::$tablesReady = false;
        }
    }

    /**
     * Ключ уникального человека: user_id либо постоянный guest cookie.
     */
    public static function getVisitorKey(): string
    {
        if (Helper::isLoggedIn()) {
            $userId = (int)Helper::getUserId();
            if ($userId > 0) {
                return 'u:' . $userId;
            }
        }

        $cookieName = 'aru_vid';
        $visitorId = $_COOKIE[$cookieName] ?? ($_SESSION[$cookieName] ?? '');

        if (!is_string($visitorId) || !preg_match('/^[a-f0-9]{32}$/', $visitorId)) {
            $visitorId = bin2hex(random_bytes(16));
            $_SESSION[$cookieName] = $visitorId;
            setcookie($cookieName, $visitorId, [
                'expires' => time() + 86400 * 400,
                'path' => '/',
                'secure' => !empty($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }

        return 'v:' . $visitorId;
    }

    /**
     * Регистрирует визит в соответствующий раздел за сегодня (1 человек = 1).
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
        $visitorKey = self::getVisitorKey();

        try {
            self::ensureTables();
            if (self::$tablesReady !== true) {
                return;
            }

            $db = Database::getInstance()->getConnection();

            $visitorSql = "
                INSERT IGNORE INTO daily_section_visitors (visit_date, section, visitor_key)
                VALUES (:visit_date, :section, :visitor_key)
            ";
            $visitorStmt = $db->prepare($visitorSql);
            $visitorStmt->execute([
                ':visit_date' => $today,
                ':section' => $section,
                ':visitor_key' => $visitorKey,
            ]);

            // Уже учитывали этого человека в разделе сегодня
            if ($visitorStmt->rowCount() === 0) {
                return;
            }

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

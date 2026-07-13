<?php

/**
 * DAILY VISITS (посещения по дням)
 *
 * Считает только заходы на главную страницу сайта (лендинг).
 * Не учитывает: админку, платформу, другие разделы, обновления страницы.
 */
class DailyVisit
{
    private static ?bool $tableReady = null;

    /**
     * Только главная страница (лендинг).
     */
    private static function getTrackableRoutes(): array
    {
        return [
            '',
            'home',
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
     * Нужно ли учитывать текущий HTTP-запрос в статистике посещений.
     */
    public static function shouldTrackRequest(): bool
    {
        if (php_sapi_name() === 'cli') {
            return false;
        }

        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            return false;
        }

        // Действия администратора и менеджера не считаем за посещения сайта
        if (Helper::isAdminLoggedIn()) {
            return false;
        }

        if (Helper::isLoggedIn() && Helper::getUserRole() === 'manager') {
            return false;
        }

        // Фоновые/prefetch-запросы браузера
        $secPurpose = strtolower($_SERVER['HTTP_SEC_PURPOSE'] ?? $_SERVER['HTTP_PURPOSE'] ?? '');
        if ($secPurpose !== '' && str_contains($secPurpose, 'prefetch')) {
            return false;
        }

        $uri = self::resolveRequestUri();

        if (!in_array($uri, self::getTrackableRoutes(), true)) {
            return false;
        }

        if (preg_match('#\.(css|js|png|jpe?g|gif|webp|ico|svg|woff2?|map|txt|xml)$#i', $uri)) {
            return false;
        }

        return true;
    }

    /**
     * Создаёт таблицу daily_visits, если её ещё нет.
     */
    public static function ensureTable(): void
    {
        if (self::$tableReady === true) {
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
            self::$tableReady = true;
        } catch (Exception $e) {
            error_log('DailyVisit::ensureTable error: ' . $e->getMessage());
            self::$tableReady = false;
        }
    }

    /**
     * Регистрирует визит за сегодня.
     * Один браузер = один визит в сутки (повторные обновления не увеличивают счётчик).
     */
    public static function trackToday(): void
    {
        if (!self::shouldTrackRequest()) {
            return;
        }

        $today = date('Y-m-d');
        $cookieName = 'aru_uv_' . date('Ymd');

        $isUnique = empty($_COOKIE[$cookieName]) && empty($_SESSION[$cookieName]);

        // Уже считали этого посетителя сегодня — обновление страницы не добавляет визит
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
            self::ensureTable();
            $db = Database::getInstance()->getConnection();

            $sql = "
                INSERT INTO daily_visits (visit_date, visits_total, unique_total)
                VALUES (:visit_date, 1, 1)
                ON DUPLICATE KEY UPDATE
                    visits_total = visits_total + 1,
                    unique_total = unique_total + 1,
                    updated_at = CURRENT_TIMESTAMP
            ";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':visit_date' => $today,
            ]);
        } catch (Exception $e) {
            error_log('DailyVisit::trackToday error: ' . $e->getMessage());
        }
    }
}

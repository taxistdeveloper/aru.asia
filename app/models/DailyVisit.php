<?php

/**
 * DAILY VISITS (посещения по дням)
 *
 * Считает:
 * - visits_total: все заходы (pageviews) за день
 * - unique_total: уникальные за день (1 раз в сутки на браузер/сессию)
 *
 * Важно: если таблицы нет (миграцию не применили), приложение не должно падать.
 */
class DailyVisit
{
    private static ?bool $tableReady = null;

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

        $uri = trim($uri, '/');

        if ($uri === '') {
            return true;
        }

        if (preg_match('#^(admin|manager|api)(/|$)#', $uri)) {
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
     * Уникальность: 1 раз в сутки на браузер (cookie) + на сессию.
     */
    public static function trackToday(): void
    {
        if (!self::shouldTrackRequest()) {
            return;
        }

        $today = date('Y-m-d');
        $cookieName = 'aru_uv_' . date('Ymd');

        $isUnique = empty($_COOKIE[$cookieName]) && empty($_SESSION[$cookieName]);

        // Ставим cookie/сессионный флаг сразу (чтобы не удваивать при редиректах)
        if ($isUnique) {
            $_SESSION[$cookieName] = 1;

            $endOfDay = strtotime('tomorrow') - 1;
            setcookie($cookieName, '1', [
                'expires' => $endOfDay,
                'path' => '/',
                'secure' => !empty($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }

        try {
            self::ensureTable();
            $db = Database::getInstance()->getConnection();

            $uniqueInc = $isUnique ? 1 : 0;

            // visit_date - PRIMARY KEY, поэтому делаем UPSERT
            $sql = "
                INSERT INTO daily_visits (visit_date, visits_total, unique_total)
                VALUES (:visit_date, 1, :unique_inc)
                ON DUPLICATE KEY UPDATE
                    visits_total = visits_total + 1,
                    unique_total = unique_total + VALUES(unique_total),
                    updated_at = CURRENT_TIMESTAMP
            ";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':visit_date' => $today,
                ':unique_inc' => $uniqueInc
            ]);
        } catch (Exception $e) {
            // Таблицы может не быть (если миграцию ещё не применили) — не ломаем сайт
            error_log('DailyVisit::trackToday error: ' . $e->getMessage());
        }
    }
}



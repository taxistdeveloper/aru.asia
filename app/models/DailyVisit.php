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
    /**
     * Регистрирует визит за сегодня.
     * Уникальность: 1 раз в сутки на браузер (cookie) + на сессию.
     */
    public static function trackToday(): void
    {
        // Считаем только HTML-страницы (не CLI/не крон)
        if (php_sapi_name() === 'cli') {
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



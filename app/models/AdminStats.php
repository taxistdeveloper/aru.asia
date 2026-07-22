<?php

/**
 * Сводная статистика для админки и панели менеджера.
 * Все показатели считаются из реальных данных БД.
 * По разделам — уникальные люди, а не число действий.
 */
class AdminStats
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Основные KPI для dashboard и страницы /stats.
     */
    public function getSummary(): array
    {
        DailyVisit::ensureTable();

        $userFilter = "role = 'user' OR role IS NULL OR role = ''";

        return [
            'total_users' => $this->queryInt("SELECT COUNT(*) FROM users WHERE {$userFilter}"),
            'verified_users' => $this->queryInt("SELECT COUNT(*) FROM users WHERE ({$userFilter}) AND email_verified = 1"),
            'unverified_users' => $this->queryInt("SELECT COUNT(*) FROM users WHERE ({$userFilter}) AND email_verified = 0"),
            'managers' => $this->queryInt("SELECT COUNT(*) FROM users WHERE role = 'manager'"),

            'total_events' => $this->queryInt("SELECT COUNT(*) FROM events WHERE status = 'approved'"),
            'active_events' => $this->queryInt("SELECT COUNT(*) FROM events WHERE status = 'approved' AND event_date >= NOW()"),
            'pending_events' => $this->queryInt("SELECT COUNT(*) FROM events WHERE status = 'pending'"),
            'rejected_events' => $this->queryInt("SELECT COUNT(*) FROM events WHERE status = 'rejected'"),

            'total_dates' => $this->queryInt('SELECT COUNT(*) FROM dates'),
            'active_dates' => $this->queryInt('SELECT COUNT(*) FROM dates WHERE date_time >= NOW()'),

            'active_ads' => $this->queryInt("SELECT COUNT(*) FROM ads WHERE status = 'active'"),
            'pending_ads' => $this->queryInt("SELECT COUNT(*) FROM ads WHERE status = 'pending'"),
            'total_messages' => $this->queryInt('SELECT COUNT(*) FROM messages'),

            'users_today' => $this->queryInt("SELECT COUNT(*) FROM users WHERE ({$userFilter}) AND DATE(created_at) = CURDATE()"),
            'events_today' => $this->queryInt("SELECT COUNT(*) FROM events WHERE DATE(created_at) = CURDATE()"),
            'dates_today' => $this->queryInt('SELECT COUNT(*) FROM dates WHERE DATE(created_at) = CURDATE()'),

            'users_week' => $this->queryInt("SELECT COUNT(*) FROM users WHERE ({$userFilter}) AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"),
            'events_week' => $this->queryInt("SELECT COUNT(*) FROM events WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"),
            'dates_week' => $this->queryInt('SELECT COUNT(*) FROM dates WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)'),

            'visits_today' => $this->queryInt('SELECT COALESCE(visits_total, 0) FROM daily_visits WHERE visit_date = CURDATE()'),
            'unique_today' => $this->queryInt('SELECT COALESCE(unique_total, 0) FROM daily_visits WHERE visit_date = CURDATE()'),
        ];
    }

    /**
     * Посещения по дням за последние N дней (главная).
     */
    public function getDailyVisits(int $days = 30): array
    {
        $days = max(1, min(365, $days));

        return $this->fetchAll("
            SELECT visit_date, visits_total, unique_total
            FROM daily_visits
            WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL {$days} DAY)
            ORDER BY visit_date DESC
        ");
    }

    /**
     * Уникальные люди по разделам за сегодня.
     *
     * @return array<string, int>
     */
    public function getSectionVisitsToday(): array
    {
        DailyVisit::ensureTables();

        $labels = DailyVisit::getSectionLabels();
        $result = array_fill_keys(array_keys($labels), 0);

        $rows = $this->fetchAll("
            SELECT section, COUNT(DISTINCT visitor_key) AS total
            FROM daily_section_visitors
            WHERE visit_date = CURDATE()
            GROUP BY section
        ");

        foreach ($rows as $row) {
            $section = $row['section'] ?? '';
            if (isset($result[$section])) {
                $result[$section] = (int)($row['total'] ?? 0);
            }
        }

        // Регистрация — реальные новые аккаунты, не заходы на страницу
        $result['auth_register'] = $this->getRegistrationsCount(0);

        return $result;
    }

    /**
     * Заходы по разделам за последние N дней (сводная таблица по датам).
     */
    public function getSectionVisitsByDay(int $days = 30): array
    {
        DailyVisit::ensureTables();
        $days = max(1, min(365, $days));

        return $this->fetchAll("
            SELECT visit_date, section, COUNT(DISTINCT visitor_key) AS unique_total
            FROM daily_section_visitors
            WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL {$days} DAY)
            GROUP BY visit_date, section
            ORDER BY visit_date DESC, section ASC
        ");
    }

    /**
     * Уникальные люди по разделу за период (один человек = 1 за весь период).
     */
    public function getSectionVisitsTotals(int $days = 30): array
    {
        DailyVisit::ensureTables();
        $days = max(1, min(365, $days));

        $labels = DailyVisit::getSectionLabels();
        $result = array_fill_keys(array_keys($labels), 0);

        $rows = $this->fetchAll("
            SELECT section, COUNT(DISTINCT visitor_key) AS total
            FROM daily_section_visitors
            WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL {$days} DAY)
            GROUP BY section
        ");

        foreach ($rows as $row) {
            $section = $row['section'] ?? '';
            if (isset($result[$section])) {
                $result[$section] = (int)($row['total'] ?? 0);
            }
        }

        // Регистрация — реальные новые аккаунты за период
        $result['auth_register'] = $this->getRegistrationsCount($days);

        return $result;
    }

    /**
     * Количество новых пользователей (роль user) за сегодня или за N дней.
     * $days = 0 — только сегодня.
     */
    private function getRegistrationsCount(int $days): int
    {
        $userFilter = "(role = 'user' OR role IS NULL OR role = '')";

        if ($days <= 0) {
            return $this->queryInt("
                SELECT COUNT(*) FROM users
                WHERE {$userFilter} AND DATE(created_at) = CURDATE()
            ");
        }

        $days = max(1, min(365, $days));
        return $this->queryInt("
            SELECT COUNT(*) FROM users
            WHERE {$userFilter}
              AND created_at >= DATE_SUB(CURDATE(), INTERVAL {$days} DAY)
        ");
    }

    private function queryInt(string $sql, int $default = 0): int
    {
        try {
            $stmt = $this->db->query($sql);
            if ($stmt === false) {
                return $default;
            }
            $result = $stmt->fetchColumn();
            return $result !== false ? (int)$result : $default;
        } catch (Exception $e) {
            error_log('AdminStats::queryInt error: ' . $e->getMessage());
            return $default;
        }
    }

    private function fetchAll(string $sql, array $default = []): array
    {
        try {
            $stmt = $this->db->query($sql);
            if ($stmt === false) {
                return $default;
            }
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result !== false ? $result : $default;
        } catch (Exception $e) {
            error_log('AdminStats::fetchAll error: ' . $e->getMessage());
            return $default;
        }
    }
}

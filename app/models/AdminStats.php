<?php

/**
 * Сводная статистика для админки и панели менеджера.
 * Все показатели считаются из реальных данных БД.
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
     * Посещения по дням за последние N дней.
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

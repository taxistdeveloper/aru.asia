<?php

/**
 * Журнал действий и ошибок (activity_logs).
 * Аудит значимых событий, не access-log каждого HTTP-запроса.
 */
class ActivityLog
{
    private $db;
    private static $tableReady = false;

    private const ALLOWED_LEVELS = ['info', 'warning', 'error'];

    /** Белый список SQL-границ для countUniqueLoginsSince — не интерполировать произвольную строку. */
    private const LOGIN_SINCE_SQL = [
        'CURDATE()' => 'CURDATE()',
        'NOW() - INTERVAL 1 DAY' => 'NOW() - INTERVAL 1 DAY',
        'NOW() - INTERVAL 7 DAY' => 'NOW() - INTERVAL 7 DAY',
        'NOW() - INTERVAL 24 HOUR' => 'NOW() - INTERVAL 24 HOUR',
        'NOW() - INTERVAL 30 DAY' => 'NOW() - INTERVAL 30 DAY',
    ];

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureTable();
    }

    public function ensureTable(): void
    {
        if (self::$tableReady === true) {
            return;
        }

        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS activity_logs (
                    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    user_id INT UNSIGNED NULL,
                    user_name VARCHAR(120) NULL,
                    action VARCHAR(64) NOT NULL,
                    level ENUM('info','warning','error') NOT NULL DEFAULT 'info',
                    entity_type VARCHAR(32) NULL,
                    entity_id INT UNSIGNED NULL,
                    message VARCHAR(500) NOT NULL,
                    context_json JSON NULL,
                    ip VARCHAR(45) NULL,
                    user_agent VARCHAR(255) NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY idx_created_at (created_at),
                    KEY idx_level (level),
                    KEY idx_action (action),
                    KEY idx_user_id (user_id),
                    KEY idx_entity (entity_type, entity_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            self::$tableReady = true;
        } catch (Exception $e) {
            error_log('ActivityLog::ensureTable error: ' . $e->getMessage());
            self::$tableReady = false;
        }
    }

    /**
     * INSERT записи. Невалидный level → info. context → JSON_UNESCAPED_UNICODE.
     *
     * @param array $data
     * @return bool
     */
    public function write(array $data): bool
    {
        $this->ensureTable();
        if (!self::$tableReady) {
            return false;
        }

        $level = strtolower((string)($data['level'] ?? 'info'));
        if (!in_array($level, self::ALLOWED_LEVELS, true)) {
            $level = 'info';
        }

        $contextJson = null;
        if (isset($data['context']) && $data['context'] !== null && $data['context'] !== '') {
            if (is_string($data['context'])) {
                $contextJson = $data['context'];
            } else {
                $encoded = json_encode($data['context'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $contextJson = ($encoded !== false) ? $encoded : null;
            }
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO activity_logs
                    (user_id, user_name, action, level, entity_type, entity_id, message, context_json, ip, user_agent, created_at)
                VALUES
                    (:user_id, :user_name, :action, :level, :entity_type, :entity_id, :message, :context_json, :ip, :user_agent, NOW())
            ");

            return $stmt->execute([
                ':user_id' => $data['user_id'] ?? null,
                ':user_name' => $data['user_name'] ?? null,
                ':action' => $data['action'],
                ':level' => $level,
                ':entity_type' => $data['entity_type'] ?? null,
                ':entity_id' => $data['entity_id'] ?? null,
                ':message' => $data['message'],
                ':context_json' => $contextJson,
                ':ip' => $data['ip'] ?? null,
                ':user_agent' => $data['user_agent'] ?? null,
            ]);
        } catch (Exception $e) {
            error_log('ActivityLog::write error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Поиск с фильтрами. perPage clamp 10..100.
     *
     * @param array $filters level, action, user_id, q
     * @return array{items: array, total: int, page: int, per_page: int, pages: int}
     */
    public function search(array $filters = [], int $page = 1, int $perPage = 40): array
    {
        $this->ensureTable();

        $page = max(1, $page);
        $perPage = max(10, min(100, $perPage));

        $where = [];
        $params = [];

        $level = strtolower(trim((string)($filters['level'] ?? '')));
        if (in_array($level, self::ALLOWED_LEVELS, true)) {
            $where[] = 'level = :level';
            $params[':level'] = $level;
        }

        $action = trim((string)($filters['action'] ?? ''));
        if ($action !== '') {
            if (strpos($action, '.') !== false) {
                $where[] = 'action = :action';
                $params[':action'] = $action;
            } else {
                $where[] = 'action LIKE :action_prefix';
                $params[':action_prefix'] = $action . '.%';
            }
        }

        $userId = (int)($filters['user_id'] ?? 0);
        if ($userId > 0) {
            $where[] = 'user_id = :user_id';
            $params[':user_id'] = $userId;
        }

        $q = trim((string)($filters['q'] ?? ''));
        if ($q !== '') {
            $like = '%' . $q . '%';
            $where[] = '(message LIKE :q1 OR user_name LIKE :q2 OR action LIKE :q3 OR CAST(entity_id AS CHAR) LIKE :q4)';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
            $params[':q4'] = $like;
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        try {
            $countStmt = $this->db->prepare("SELECT COUNT(*) FROM activity_logs {$whereSql}");
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();

            $pages = max(1, (int)ceil($total / $perPage));
            if ($page > $pages) {
                $page = $pages;
            }
            $offset = ($page - 1) * $perPage;

            $sql = "
                SELECT *
                FROM activity_logs
                {$whereSql}
                ORDER BY id DESC
                LIMIT :limit OFFSET :offset
            ";
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            return [
                'items' => $items,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'pages' => $pages,
            ];
        } catch (Exception $e) {
            error_log('ActivityLog::search error: ' . $e->getMessage());
            return [
                'items' => [],
                'total' => 0,
                'page' => $page,
                'per_page' => $perPage,
                'pages' => 1,
            ];
        }
    }

    /**
     * @return array{info: int, warning: int, error: int}
     */
    public function countByLevel(): array
    {
        $this->ensureTable();
        $result = ['info' => 0, 'warning' => 0, 'error' => 0];

        try {
            $rows = $this->db->query("
                SELECT level, COUNT(*) AS total
                FROM activity_logs
                GROUP BY level
            ")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $level = $row['level'] ?? '';
                if (isset($result[$level])) {
                    $result[$level] = (int)$row['total'];
                }
            }
        } catch (Exception $e) {
            error_log('ActivityLog::countByLevel error: ' . $e->getMessage());
        }

        return $result;
    }

    public function recentErrorCount(int $hours = 24): int
    {
        $this->ensureTable();
        $hours = max(1, min(168, $hours));

        try {
            $stmt = $this->db->query("
                SELECT COUNT(*) FROM activity_logs
                WHERE level = 'error' AND created_at >= NOW() - INTERVAL {$hours} HOUR
            ");
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log('ActivityLog::recentErrorCount error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * COUNT DISTINCT user_id WHERE action='auth.login' AND created_at >= whitelist SQL.
     */
    public function countUniqueLoginsSince(string $sinceSql): int
    {
        $this->ensureTable();
        $bound = self::LOGIN_SINCE_SQL[$sinceSql] ?? null;
        if ($bound === null) {
            return 0;
        }

        try {
            $sql = "
                SELECT COUNT(DISTINCT user_id)
                FROM activity_logs
                WHERE action = 'auth.login'
                  AND user_id IS NOT NULL
                  AND created_at >= {$bound}
            ";
            return (int)$this->db->query($sql)->fetchColumn();
        } catch (Exception $e) {
            error_log('ActivityLog::countUniqueLoginsSince error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * @return string[]
     */
    public function distinctActionPrefixes(): array
    {
        $this->ensureTable();

        try {
            $rows = $this->db->query("
                SELECT DISTINCT SUBSTRING_INDEX(action, '.', 1) AS prefix
                FROM activity_logs
                WHERE action IS NOT NULL AND action <> ''
                ORDER BY prefix ASC
            ")->fetchAll(PDO::FETCH_COLUMN);
            return array_values(array_filter($rows, function ($p) {
                return $p !== null && $p !== '';
            }));
        } catch (Exception $e) {
            error_log('ActivityLog::distinctActionPrefixes error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Последние события пользователя (для карточки).
     *
     * @return array<int, array>
     */
    public function recentForUser(int $userId, int $limit = 10): array
    {
        $this->ensureTable();
        $userId = (int)$userId;
        $limit = max(1, min(50, $limit));
        if ($userId <= 0) {
            return [];
        }

        try {
            $stmt = $this->db->prepare("
                SELECT * FROM activity_logs
                WHERE user_id = :user_id
                ORDER BY id DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('ActivityLog::recentForUser error: ' . $e->getMessage());
            return [];
        }
    }

    public static function lastInsertId(): int
    {
        try {
            return (int)Database::getInstance()->getConnection()->lastInsertId();
        } catch (Throwable $e) {
            return 0;
        }
    }
}

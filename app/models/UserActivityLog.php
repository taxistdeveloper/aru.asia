<?php

/**
 * ЛОГИ ДЕЙСТВИЙ ПОЛЬЗОВАТЕЛЕЙ
 */
class UserActivityLog
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function logRequest($userId, $route, $method, $action = null, $params = [], $query = '')
    {
        try {
            $ip = Helper::getClientIp();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            if ($userAgent !== null) {
                $userAgent = mb_substr($userAgent, 0, 250);
            }

            $safeParams = $this->sanitizeParams($params);
            $paramsJson = $safeParams ? json_encode($safeParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
            if ($paramsJson !== null && mb_strlen($paramsJson) > 1000) {
                $paramsJson = mb_substr($paramsJson, 0, 1000) . '...';
            }

            $route = mb_substr($route, 0, 255);
            $query = $query ? mb_substr($query, 0, 255) : null;
            $method = strtoupper($method ?: 'GET');
            $action = $action ? mb_substr($action, 0, 100) : null;

            $stmt = $this->db->prepare("
                INSERT INTO user_activity_logs
                    (user_id, route, query_string, method, action, ip_address, user_agent, params_json, created_at)
                VALUES
                    (:user_id, :route, :query_string, :method, :action, :ip_address, :user_agent, :params_json, NOW())
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':route' => $route,
                ':query_string' => $query,
                ':method' => $method,
                ':action' => $action,
                ':ip_address' => $ip,
                ':user_agent' => $userAgent,
                ':params_json' => $paramsJson
            ]);
        } catch (Exception $e) {
            error_log("UserActivityLog::logRequest error: " . $e->getMessage());
        }
    }

    public function getLogs($limit = 50, $offset = 0, $filters = [])
    {
        $where = [];
        $params = [];

        if (!empty($filters['method'])) {
            $where[] = 'l.method = :method';
            $params[':method'] = strtoupper($filters['method']);
        }
        if (!empty($filters['user_id'])) {
            $where[] = 'l.user_id = :user_id';
            $params[':user_id'] = (int)$filters['user_id'];
        }
        if (!empty($filters['query'])) {
            $where[] = '(u.email LIKE :query OR l.route LIKE :query OR l.action LIKE :query)';
            $params[':query'] = '%' . $filters['query'] . '%';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT l.*, u.email AS user_email
            FROM user_activity_logs l
            LEFT JOIN users u ON l.user_id = u.id
            {$whereSql}
            ORDER BY l.created_at DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCount($filters = [])
    {
        $where = [];
        $params = [];

        if (!empty($filters['method'])) {
            $where[] = 'l.method = :method';
            $params[':method'] = strtoupper($filters['method']);
        }
        if (!empty($filters['user_id'])) {
            $where[] = 'l.user_id = :user_id';
            $params[':user_id'] = (int)$filters['user_id'];
        }
        if (!empty($filters['query'])) {
            $where[] = '(u.email LIKE :query OR l.route LIKE :query OR l.action LIKE :query)';
            $params[':query'] = '%' . $filters['query'] . '%';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT COUNT(*) as total
            FROM user_activity_logs l
            LEFT JOIN users u ON l.user_id = u.id
            {$whereSql}
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)($result['total'] ?? 0);
    }

    private function sanitizeParams($params)
    {
        if (empty($params) || !is_array($params)) {
            return [];
        }

        $blockedKeys = [
            'password',
            'password_confirmation',
            'confirm_password',
            'current_password',
            'new_password',
            'new_password_confirmation',
            'token',
            'verification_token',
            'reset_token',
            'remember_token',
            'message',
            'text',
            'body',
            'about',
            'admin_reply',
            'admin_notes',
            'remark'
        ];

        $safe = [];
        foreach ($params as $key => $value) {
            if (in_array($key, $blockedKeys, true)) {
                continue;
            }
            if (is_array($value)) {
                $safe[$key] = '[array]';
            } else {
                $safe[$key] = mb_substr((string)$value, 0, 200);
            }
        }

        return $safe;
    }
}

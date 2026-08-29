<?php

/**
 * Журнал действий и ошибок.
 * Запись никогда не ломает основной запрос (try/catch + флаг $writing).
 */
class ActivityLogger
{
    private static $writing = false;

    private const LEN_ACTION = 64;
    private const LEN_MESSAGE = 500;
    private const LEN_USER_NAME = 120;
    private const LEN_ENTITY_TYPE = 32;
    private const LEN_IP = 45;
    private const LEN_USER_AGENT = 255;

    private const SECRET_KEYS = [
        'password', 'password_confirmation', 'confirm_password',
        'current_password', 'new_password', 'new_password_confirmation',
        'token', 'verification_token', 'reset_token', 'remember_token',
        'secret', 'api_key', 'apikey', 'access_token', 'refresh_token',
        'card', 'card_number', 'cvv', 'cvc',
    ];

    private const FATAL_ERRORS = [
        E_ERROR,
        E_PARSE,
        E_CORE_ERROR,
        E_COMPILE_ERROR,
        E_USER_ERROR,
    ];

    /**
     * @param mixed $context
     */
    public static function log(
        string $action,
        string $message,
        string $level = 'info',
        ?string $entityType = null,
        $entityId = null,
        $context = null,
        $userId = null,
        ?string $userName = null
    ): void {
        if (self::$writing) {
            return;
        }
        self::$writing = true;

        try {
            $resolved = self::resolveActor($userId, $userName);

            $data = [
                'user_id' => $resolved['user_id'],
                'user_name' => self::trunc($resolved['user_name'], self::LEN_USER_NAME),
                'action' => self::trunc($action, self::LEN_ACTION),
                'level' => $level,
                'entity_type' => $entityType !== null ? self::trunc($entityType, self::LEN_ENTITY_TYPE) : null,
                'entity_id' => ($entityId !== null && $entityId !== '') ? (int)$entityId : null,
                'message' => self::trunc($message, self::LEN_MESSAGE),
                'context' => self::sanitizeContext($context),
                'ip' => self::trunc(self::clientIp(), self::LEN_IP),
                'user_agent' => self::trunc(self::userAgent(), self::LEN_USER_AGENT),
            ];

            (new ActivityLog())->write($data);
        } catch (Throwable $e) {
            error_log('ActivityLogger::log failed: ' . $e->getMessage());
        } finally {
            self::$writing = false;
        }
    }

    /**
     * @param mixed $context
     */
    public static function info(
        string $action,
        string $message,
        ?string $entityType = null,
        $entityId = null,
        $context = null,
        $userId = null,
        ?string $userName = null
    ): void {
        self::log($action, $message, 'info', $entityType, $entityId, $context, $userId, $userName);
    }

    /**
     * @param mixed $context
     */
    public static function warning(
        string $action,
        string $message,
        ?string $entityType = null,
        $entityId = null,
        $context = null,
        $userId = null,
        ?string $userName = null
    ): void {
        self::log($action, $message, 'warning', $entityType, $entityId, $context, $userId, $userName);
    }

    /**
     * @param mixed $context
     */
    public static function error(
        string $action,
        string $message,
        ?string $entityType = null,
        $entityId = null,
        $context = null,
        $userId = null,
        ?string $userName = null
    ): void {
        self::log($action, $message, 'error', $entityType, $entityId, $context, $userId, $userName);
    }

    public static function exception(Throwable $e, string $action = 'system.exception'): void
    {
        $message = self::trunc($e->getMessage(), 400);
        if ($message === '') {
            $message = get_class($e);
        }

        $trace = $e->getTraceAsString();
        if (mb_strlen($trace) > 2000) {
            $trace = mb_substr($trace, 0, 2000);
        }

        self::error($action, $message, 'system', null, [
            'class' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $trace,
        ]);
    }

    public static function actionLabel(string $action): string
    {
        $action = trim($action);
        if ($action === '') {
            return '';
        }

        $key = 'admin.log_action_' . str_replace('.', '_', $action);
        if (class_exists('Lang') && Lang::has($key)) {
            return Lang::t($key);
        }

        $prefix = strpos($action, '.') !== false ? strstr($action, '.', true) : $action;
        $catKey = 'admin.log_cat_' . $prefix;
        if (class_exists('Lang') && Lang::has($catKey)) {
            return Lang::t($catKey) . ' · ' . $action;
        }

        return $action;
    }

    public static function clientIp(): ?string
    {
        $candidates = [];

        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $candidates[] = trim((string)$_SERVER['HTTP_CF_CONNECTING_IP']);
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $first = trim(explode(',', (string)$_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
            if ($first !== '') {
                $candidates[] = $first;
            }
        }
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $candidates[] = trim((string)$_SERVER['REMOTE_ADDR']);
        }

        foreach ($candidates as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return null;
    }

    public static function userAgent(): ?string
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
        if ($ua === null || $ua === '') {
            return null;
        }
        return (string)$ua;
    }

    public static function registerHandlers(): void
    {
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
    }

    public static function handleException(Throwable $e): void
    {
        self::exception($e, 'system.exception');

        if (!headers_sent()) {
            http_response_code(500);
        }

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($isAjax) {
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode([
                'success' => false,
                'error' => 'Ошибка записана в журнал. Попробуйте позже.',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $viewFile = dirname(__DIR__) . '/views/error/500.php';
        if (is_file($viewFile)) {
            require $viewFile;
            return;
        }

        echo '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8"><title>Ошибка</title></head>';
        echo '<body><p>Ошибка записана в журнал. Попробуйте позже.</p></body></html>';
    }

    /**
     * @return bool false — пусть PHP обработает ошибку как обычно
     */
    public static function handleError(int $errno, string $errstr, string $errfile = '', int $errline = 0): bool
    {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $level = in_array($errno, self::FATAL_ERRORS, true) ? 'error' : 'warning';
        $message = self::trunc($errstr, 400);

        self::log('system.php_error', $message !== '' ? $message : 'PHP error', $level, 'system', null, [
            'errno' => $errno,
            'file' => $errfile,
            'line' => $errline,
        ]);

        return false;
    }

    /**
     * @param mixed $userId
     * @return array{user_id: int|null, user_name: string|null}
     */
    private static function resolveActor($userId, ?string $userName): array
    {
        if ($userId !== null && $userId !== '' && (int)$userId > 0) {
            $id = (int)$userId;
            $name = $userName;
            if ($name === null || $name === '') {
                $name = self::currentUserName() ?? ('#' . $id);
            }
            return ['user_id' => $id, 'user_name' => $name];
        }

        if ($userName !== null && $userName !== '') {
            return ['user_id' => null, 'user_name' => $userName];
        }

        if (class_exists('Helper') && Helper::isLoggedIn()) {
            $id = (int)Helper::getUserId();
            return [
                'user_id' => $id > 0 ? $id : null,
                'user_name' => self::currentUserName(),
            ];
        }

        if (class_exists('Helper') && Helper::isAdminLoggedIn()) {
            $email = $_SESSION['admin_email'] ?? null;
            return [
                'user_id' => null,
                'user_name' => $email ? ('Админ: ' . $email) : 'Администратор',
            ];
        }

        return ['user_id' => null, 'user_name' => null];
    }

    private static function currentUserName(): ?string
    {
        $email = $_SESSION['user_email'] ?? null;
        if (is_string($email) && $email !== '') {
            return $email;
        }
        return null;
    }

    /**
     * @param mixed $context
     * @return mixed
     */
    private static function sanitizeContext($context)
    {
        if ($context === null || $context === '' || $context === []) {
            return null;
        }
        if (!is_array($context)) {
            if (is_scalar($context)) {
                return ['value' => $context];
            }
            return null;
        }
        return self::stripSecrets($context);
    }

    /**
     * @param array $data
     * @return array
     */
    private static function stripSecrets(array $data): array
    {
        $clean = [];
        foreach ($data as $key => $value) {
            $keyLower = strtolower((string)$key);
            $blocked = false;
            foreach (self::SECRET_KEYS as $secret) {
                if ($keyLower === $secret || strpos($keyLower, $secret) !== false) {
                    $blocked = true;
                    break;
                }
            }
            if ($blocked) {
                continue;
            }
            if (is_array($value)) {
                $clean[$key] = self::stripSecrets($value);
            } elseif (is_scalar($value) || $value === null) {
                $clean[$key] = $value;
            }
        }
        return $clean;
    }

    private static function trunc(?string $value, int $max): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = (string)$value;
        if ($value === '') {
            return $value;
        }
        if (mb_strlen($value) <= $max) {
            return $value;
        }
        return mb_substr($value, 0, $max);
    }
}

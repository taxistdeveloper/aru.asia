<?php

/**
 * Простые словари админки (ru / kk).
 * Ключи плоские: admin.logs, admin.log_action_auth_login, …
 */
class Lang
{
    private static $strings = null;
    private static $locale = 'ru';

    public static function locale(): string
    {
        if (defined('APP_LOCALE') && is_string(APP_LOCALE) && APP_LOCALE !== '') {
            return strtolower(APP_LOCALE);
        }
        return self::$locale;
    }

    public static function t(string $key, ?string $default = null): string
    {
        self::load();
        if (isset(self::$strings[$key]) && self::$strings[$key] !== '') {
            return (string)self::$strings[$key];
        }
        return $default !== null ? $default : $key;
    }

    public static function has(string $key): bool
    {
        self::load();
        return isset(self::$strings[$key]) && self::$strings[$key] !== '';
    }

    private static function load(): void
    {
        if (self::$strings !== null) {
            return;
        }

        $locale = self::locale();
        $dir = dirname(__DIR__) . '/lang';
        $fallback = $dir . '/ru.php';
        $file = $dir . '/' . $locale . '.php';

        $strings = [];
        if (is_file($fallback)) {
            $loaded = include $fallback;
            if (is_array($loaded)) {
                $strings = $loaded;
            }
        }
        if ($locale !== 'ru' && is_file($file)) {
            $loaded = include $file;
            if (is_array($loaded)) {
                $strings = array_merge($strings, $loaded);
            }
        }

        self::$strings = $strings;
    }
}

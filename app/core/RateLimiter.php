<?php

/**
 * SIMPLE FILE-BASED RATE LIMITER
 *
 * Используется для базовой защиты от ботов/спама без внешних зависимостей.
 * Хранит список "хитов" (timestamps) в JSON-файле в sys_get_temp_dir().
 */
class RateLimiter
{
    private static function getDir(): string
    {
        return rtrim(sys_get_temp_dir(), "\\/") . DIRECTORY_SEPARATOR . 'aru_rate_limits';
    }

    private static function getFilePath(string $key): string
    {
        return self::getDir() . DIRECTORY_SEPARATOR . sha1($key) . '.json';
    }

    /**
     * Проверяет/фиксирует запрос в лимите.
     * Возвращает true если разрешено, false если лимит превышен.
     */
    public static function allow(string $key, int $limit, int $windowSeconds): bool
    {
        if ($limit <= 0 || $windowSeconds <= 0) {
            return true;
        }

        $dir = self::getDir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $file = self::getFilePath($key);
        $now = time();
        $cutoff = $now - $windowSeconds;

        $fh = @fopen($file, 'c+');
        if (!$fh) {
            // Если не можем записать лимитер, лучше не ломать функционал.
            return true;
        }

        @flock($fh, LOCK_EX);

        $contents = stream_get_contents($fh);
        $decoded = $contents ? json_decode($contents, true) : null;
        $hits = (is_array($decoded) && isset($decoded['hits']) && is_array($decoded['hits'])) ? $decoded['hits'] : [];

        // Очищаем старые хиты и мусор
        $filtered = [];
        foreach ($hits as $t) {
            if (is_int($t) && $t >= $cutoff) {
                $filtered[] = $t;
            } elseif (is_string($t) && ctype_digit($t) && (int)$t >= $cutoff) {
                $filtered[] = (int)$t;
            }
        }
        $hits = array_values($filtered);

        if (count($hits) >= $limit) {
            @flock($fh, LOCK_UN);
            @fclose($fh);
            return false;
        }

        $hits[] = $now;

        @ftruncate($fh, 0);
        @rewind($fh);
        @fwrite($fh, json_encode(['hits' => $hits], JSON_UNESCAPED_UNICODE));
        @fflush($fh);

        @flock($fh, LOCK_UN);
        @fclose($fh);

        return true;
    }
}


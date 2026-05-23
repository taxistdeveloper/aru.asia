<?php

/**
 * Курсы валют к тенге (KZT) с кэшем на диске.
 * Источник: open.er-api.com (список кодов ISO из ответа API).
 */
class CurrencyExchangeService
{
    private const API_URL = 'https://open.er-api.com/v6/latest/KZT';
    private const CACHE_TTL = 43200; // 12 часов

    /**
     * @return array{base:string,rates:array<string,float>,cached:bool,fetched_at:int,error?:string}
     */
    public static function getLatestFromKzt()
    {
        $cacheDir = __DIR__ . '/../cache';
        $cacheFile = $cacheDir . '/exchange_rates_kzt.json';

        if (is_readable($cacheFile)) {
            $raw = json_decode((string) file_get_contents($cacheFile), true);
            if (is_array($raw) && !empty($raw['rates']) && !empty($raw['fetched_at'])) {
                $age = time() - (int) $raw['fetched_at'];
                if ($age >= 0 && $age < self::CACHE_TTL) {
                    return [
                        'base' => 'KZT',
                        'rates' => $raw['rates'],
                        'cached' => true,
                        'fetched_at' => (int) $raw['fetched_at'],
                    ];
                }
            }
        }

        $body = self::httpGet(self::API_URL);
        if ($body === null || $body === '') {
            return self::staleOrFallback($cacheFile);
        }

        $json = json_decode($body, true);
        if (!is_array($json) || empty($json['rates']) || !is_array($json['rates'])) {
            return self::staleOrFallback($cacheFile);
        }

        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        $payload = [
            'fetched_at' => isset($json['time_last_update_unix']) ? (int) $json['time_last_update_unix'] : time(),
            'rates' => $json['rates'],
        ];
        @file_put_contents($cacheFile, json_encode($payload, JSON_UNESCAPED_UNICODE));

        return [
            'base' => 'KZT',
            'rates' => $json['rates'],
            'cached' => false,
            'fetched_at' => $payload['fetched_at'],
        ];
    }

    /**
     * @return array{base:string,rates:array<string,float>,cached:bool,fetched_at:int,error?:string}
     */
    private static function staleOrFallback($cacheFile)
    {
        if (is_readable($cacheFile)) {
            $raw = json_decode((string) file_get_contents($cacheFile), true);
            if (is_array($raw) && !empty($raw['rates'])) {
                return [
                    'base' => 'KZT',
                    'rates' => $raw['rates'],
                    'cached' => true,
                    'fetched_at' => (int) ($raw['fetched_at'] ?? time()),
                    'error' => 'stale_cache',
                ];
            }
        }

        return [
            'base' => 'KZT',
            'rates' => self::minimalFallback(),
            'cached' => false,
            'fetched_at' => time(),
            'error' => 'fallback',
        ];
    }

    /**
     * @return array<string,float>
     */
    private static function minimalFallback()
    {
        return [
            'KZT' => 1.0,
            'USD' => 0.00213,
            'EUR' => 0.00196,
            'RUB' => 0.205,
            'UZS' => 27.2,
        ];
    }

    private static function httpGet($url)
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch === false) {
                return null;
            }
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 12,
                CURLOPT_USERAGENT => 'AruApp/1.0',
            ]);
            $out = curl_exec($ch);
            curl_close($ch);
            return $out === false ? null : (string) $out;
        }

        $ctx = stream_context_create([
            'http' => [
                'timeout' => 12,
                'header' => "User-Agent: AruApp/1.0\r\n",
            ],
        ]);
        $out = @file_get_contents($url, false, $ctx);
        return $out === false ? null : (string) $out;
    }
}

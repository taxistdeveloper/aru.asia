<?php

/**
 * Многоязычный фильтр ненормативной лексики
 *
 * Проверяет текст на нецензурные выражения на русском, английском
 * и других языках. Поддерживает leet-speak и транслитерацию.
 */

class ProfanityFilter
{
    /**
     * Возвращает PCRE-паттерны для PHP preg_match
     */
    public static function getPatterns()
    {
        return array_merge(
            self::getRussianPatterns(),
            self::getEnglishPatterns(),
            self::getTransliterationPatterns(),
            self::getOtherLanguagesPatterns()
        );
    }

    /**
     * Возвращает паттерны в виде строк для JavaScript RegExp (без слэшей)
     */
    public static function getJsPatternSources()
    {
        $patterns = self::getPatterns();
        $result = [];
        foreach ($patterns as $p) {
            // Извлекаем источник регулярки между / и /флаги
            if (preg_match('#^/(.+)/[a-zA-Z]*$#', $p, $m)) {
                $result[] = $m[1];
            }
        }
        return $result;
    }

    /**
     * Проверяет текст на наличие нецензурной лексики
     */
    public static function containsProfanity($text)
    {
        if (empty($text)) {
            return false;
        }

        foreach (self::getPatterns() as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Русскоязычные паттерны
     */
    private static function getRussianPatterns()
    {
        return [
            '/[хx][уy]+[йиыяеёю]+/iu',
            '/[нn][аa][хx][уyйиыяеёю]*/iu',
            '/[пp][иi1][з3][дpb]+[аaооеёяию]*/iu',
            '/(?<![сc])[еeё]б+[аaооеёиыу]*?/iu',  // ебать и т.п., но не «себе»
            '/[бb][лl][яia][дdтt]+[ьюя]*/iu',
            '/[сc][уy][кk][аaооеё]*?/iu',
            '/[мm][уy][дd][аaооеё]+[кkч4]*?/iu',
            '/[гg][аa][нn][дd][оo0][нn]/iu',
            '/[дd][оo0][лl1][бb][оo0][еeё][бb6]/iu',
            '/[хx][еe3][рp][аaооеё]*?/iu',
            '/шлюх[аиыеоуяё]/iu',
            '/[сc][рp][аa][нn][ььыеёя]*/iu',
        ];
    }

    /**
     * Англоязычные паттерны (с учётом leet-speak: 0=o, 1=i, 3=e, 4=a, 5=s, @=a, $=s и т.д.)
     */
    private static function getEnglishPatterns()
    {
        return [
            // f*ck и вариации
            '/\b[fph][uùúûü4]+[c¢kq]+/iu',
            '/\bf+[uùúûü4]+ck+/iu',
            '/\bfuk+/iu',
            '/\bfck+/iu',
            '/\bf4ck/iu',
            '/\bphuck/iu',

            // sh*t
            '/\b[s5$]h+[i1!|]+t+/iu',
            '/\b5hit/iu',
            '/\bsh1t/iu',

            // b*tch
            '/\bb+[i1!]+t+[c¢]?h+/iu',
            '/\bb1tch/iu',

            // a*s (только как отдельное слово)
            '/\b[a4@][s5$]{2}\b/iu',

            // d*ck
            '/\bd+[i1!]+[c¢k]+/iu',
            '/\bd1ck/iu',

            // c*nt
            '/\b[c¢][uùúûü4]+n+t+/iu',

            // wh*re
            '/\bw+h+[o0]+r+e+/iu',

            // sl*t
            '/\bs+l+[uùúûü4]+t+/iu',

            // b*stard
            '/\bb+[a4@]s+t+[a4@]r+d+/iu',

            // d*mb, d*mn
            '/\bd+[uùúûü4]+mb+/iu',
            '/\bd+[a4@]+mn+/iu',

            // shi*, cra*
            '/\b[s5$]hi+t\b/iu',
            '/\bcra+p\b/iu',

            // piss, dickhead
            '/\bpiss(?:\s*off)?\b/iu',
            '/\bdickhead/iu',

            // motherf*cker, mf
            '/\bmother\s*f+[uùúûü4]+ck+/iu',
            '/\bmf\b/iu',
        ];
    }

    /**
     * Транслитерация (латиницей русские слова)
     */
    private static function getTransliterationPatterns()
    {
        return [
            '/b[l1]y?a?t[\'`]?/iu',
            '/x[uy]+y/iu',
            '/p[i1]zd[aaoe]/iu',
            '/suka/iu',
            '/blyad/iu',
        ];
    }

    /**
     * Другие языки (испанский, немецкий, казахский — базовые распространённые слова)
     */
    private static function getOtherLanguagesPatterns()
    {
        return [
            // Испанский
            '/\bputa\b/iu',
            '/\bcara[jj]o\b/iu',
            '/\bco[jn][i1]o\b/iu',
            '/\bmierda\b/iu',

            // Немецкий
            '/\b[s5$]chei[s5$]e\b/iu',
            '/\b[hH]uren\s*[s5$]ohn/iu',
            '/\b[s5$]chwanz/iu',

            // Французский
            '/\bmerde\b/iu',
            '/\bputain\b/iu',

            // Турецкий / тюркские
            '/\b[s5$]eref\s*siz/iu',
            '/\bamele\b/iu',

            // Общие международные (латинские корни)
            '/\bfuq/iu',
            '/\bwtf\b/iu',
            '/\bffs\b/iu',
        ];
    }
}

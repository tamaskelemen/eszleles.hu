<?php

namespace app\components\related;

/**
 * Comparing free texts typed by the users.
 */
class Text
{
    const ACCENTS = ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ö' => 'o', 'ő' => 'o', 'ú' => 'u', 'ü' => 'u',
        'ű' => 'u', 'ä' => 'a', 'ô' => 'o', 'č' => 'c', 'š' => 's', 'ž' => 'z'];

    /**
     * Lowercase, without accents, words separated by a single space ("Sky-Watcher EQ5" → "sky watcher eq5").
     *
     * @param string|null $text
     * @return string
     */
    public static function normalize($text)
    {
        $text = strtr(mb_strtolower((string)$text), self::ACCENTS);
        return trim(preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text));
    }

    /**
     * @param string|null $text
     * @return string the first word of the normalized text, "" if there is none
     */
    public static function firstWord($text)
    {
        return explode(' ', self::normalize($text))[0];
    }

    /**
     * @param string $a normalized
     * @param string $b normalized
     * @return float 1 for the same texts, 0 for completely different ones (by the edit distance)
     */
    public static function similarity($a, $b)
    {
        if ($a === $b) {
            return 1.0;
        }
        $length = max(mb_strlen($a), mb_strlen($b));
        if ($length === 0) {
            return 1.0;
        }
        // the normalized texts are mostly ASCII, the few other letters only count a bit more
        return max(0.0, 1 - levenshtein($a, $b) / $length);
    }
}

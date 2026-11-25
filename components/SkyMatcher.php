<?php
namespace app\components;

/**
 * Finds out from the title of an observation which deep sky objects it is about.
 *
 * Catalog numbers ("NGC 7000", "M81-M82") are trusted. Nicknames ("Rozetta-köd") are used only if there is no known
 * catalog number in the title, and they have to be reviewed.
 */
class SkyMatcher
{
    const STATUS_AUTO = 'auto';
    const STATUS_REVIEW = 'review';
    const STATUS_NONE = 'none';

    const REASON_NICKNAME = 'csak becenév alapján';
    const REASON_CONSTELLATION = 'a csillagkép nem egyezik';
    const REASON_NICKNAME_CONFLICT = 'a becenév másik objektumot jelöl';
    const REASON_FAR = 'az objektumok messze vannak egymástól';

    /** Every way a catalog is written => the prefix of its keys */
    const CATALOGS = [
        'NGC' => 'NGC',
        'IC' => 'IC',
        'MESSIER' => 'M',
        'M' => 'M',
        'CALDWELL' => 'C',
        'C' => 'C',
        'SHARPLESS' => 'SH2',
        'BARNARD' => 'B',
        'B' => 'B',
        'COLLINDER' => 'CR',
        'CR' => 'CR',
        'MELOTTE' => 'MEL',
        'MEL' => 'MEL',
        'TRUMPLER' => 'TR',
        'TR' => 'TR',
        'STOCK' => 'STOCK',
        'BERKELEY' => 'BERKELEY',
        'KING' => 'KING',
        'LBN' => 'LBN',
        'LDN' => 'LDN',
        'VDB' => 'VDB',
        'CED' => 'CED',
        'ABELL' => 'ABELL',
        'ARP' => 'ARP',
        'HICKSON' => 'HCG',
        'HCG' => 'HCG',
        'UGC' => 'UGC',
        'PGC' => 'PGC',
        'GUM' => 'GUM',
        'SIMEIS' => 'SIMEIS',
    ];

    /** Catalogs where "NGC 3185, 3187" is a list of numbers */
    const LISTABLE_CATALOGS = ['NGC', 'IC', 'M', 'SH2', 'B', 'LDN', 'LBN', 'VDB'];

    /** Objects listed or nicknamed together can not be farther from each other, in degrees */
    const MAX_DISTANCE = 8;

    /** Two objects of a title farther from each other than this are suspicious, in degrees */
    const SUSPICIOUS_DISTANCE = 45;

    const NICKNAME_MAX_WORDS = 6;
    const NICKNAME_MIN_LENGTH = 5;

    /** @var callable */
    private $lookup;

    /**
     * @param callable $lookup gets a list of alias keys and returns lists of objects indexed by alias key, an object
     * is an array with at least id, constellation, ra and dec (see SkyObject::findByAliases())
     */
    public function __construct(callable $lookup)
    {
        $this->lookup = $lookup;
    }

    /**
     * @param string $title
     * @param string|null $constellation
     * @return array status (STATUS_*), objects, unknown (catalog numbers we do not know), reasons (of the review)
     */
    public function match($title, $constellation = null)
    {
        $title = self::clean($title);
        $designations = self::findDesignations($title);
        $nicknames = self::findNicknameCandidates($title);

        $found = call_user_func($this->lookup, array_values(array_unique(array_merge(
            self::collectKeys($designations),
            array_column($nicknames, 'key')
        ))));

        list($objects, $unknown) = $this->resolveDesignations($designations, $found);
        $nicknamed = $this->resolveNicknames($nicknames, $found);
        $reasons = [];

        if (empty($objects)) {
            $objects = $nicknamed;

            if (!empty($objects)) {
                $reasons[] = self::REASON_NICKNAME;
            }
        } else if (!empty($nicknamed) && !self::isAnyNear($objects, $nicknamed)) {
            $reasons[] = self::REASON_NICKNAME_CONFLICT;
        }

        if (self::isScattered($objects)) {
            $reasons[] = self::REASON_FAR;
        }

        if (!empty($objects) && !self::isInConstellation($objects, $title, $constellation)) {
            $reasons[] = self::REASON_CONSTELLATION;
        }

        $status = self::STATUS_AUTO;

        if (empty($objects)) {
            $status = self::STATUS_NONE;
        } else if (!empty($reasons)) {
            $status = self::STATUS_REVIEW;
        }

        return [
            'status' => $status,
            'objects' => array_values($objects),
            'unknown' => $unknown,
            'reasons' => $reasons,
        ];
    }

    /**
     * Alias key of a catalog number.
     *
     * @param string $designation
     * @return string|null null, if it is not a catalog number
     */
    public static function designationKey($designation)
    {
        $designation = self::clean($designation);
        $designations = self::findDesignations($designation);

        if (count($designations) !== 1 || $designations[0]['text'] !== $designation) {
            return null;
        }

        return $designations[0]['keys'][0];
    }

    /**
     * Alias key of a nickname. The words are joined, because "Bagoly-halmaz" is often written as "Bagolyhalmaz".
     *
     * @param string $nickname
     * @return string
     */
    public static function nicknameKey($nickname)
    {
        return self::joinWords(self::splitWords(self::clean($nickname)));
    }

    /**
     * @param array $a
     * @param array $b
     * @return float distance in degrees
     */
    public static function distance(array $a, array $b)
    {
        $decA = deg2rad($a['dec']);
        $decB = deg2rad($b['dec']);

        $cos = sin($decA) * sin($decB) + cos($decA) * cos($decB) * cos(deg2rad($a['ra'] - $b['ra']));

        return rad2deg(acos(max(-1, min(1, $cos))));
    }

    /**
     * Unifies the dashes, the spaces and the many forms of Sharpless numbers.
     */
    private static function clean($title)
    {
        $title = preg_replace('~[\x{2010}-\x{2015}\x{2212}]~u', '-', $title);
        $title = preg_replace('~\s+~u', ' ', $title);
        $title = preg_replace('~(?<![\p{L}\p{N}])sh(?:arpless)?[ -]?2[ -]+(\d{1,3})(?!\d)~iu', 'SHARPLESS $1', $title);

        return trim($title);
    }

    /**
     * @param string $title
     * @return array each has text, keys (the first known one counts), anchor (index of the first number of a list)
     */
    private static function findDesignations($title)
    {
        $catalogs = implode('|', array_keys(self::CATALOGS));

        // "M 1-67" is a number of Minkowski, not of Messier
        $pattern = "~(?<![\p{L}\p{N}])(?!M ?[1-4]-\d)($catalogs)[ .:_-]*(\d{1,6})(?![\d/]|\.\d)(?:([A-Z])(?![\p{L}\p{N}]))?~iu";
        // without spaces ("M81-82 galaxisok") anything may follow, otherwise it may be an exposure ("M8, 20 perc")
        $listPattern = '~\G(?|[/+&-](\d{1,4})(?![\d.\p{L}])| *(?:[,;/+&-]|és(?: az?)?) *(\d{1,4})(?= *(?:$|[,;/+&()-]|és |(?:GX|GH|NY|DF|PL|SK)(?!\p{L}))))~iu';

        preg_match_all($pattern, $title, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        $designations = [];

        foreach ($matches as $match) {
            $catalog = self::CATALOGS[strtoupper($match[1][0])];
            $anchor = count($designations);

            // comets and telescopes are written like this too
            if ($catalog === 'C' && (int)$match[2][0] > 109) {
                continue;
            }

            $key = self::buildKey($catalog, $match[2][0]);
            $designations[] = [
                'text' => $match[0][0],
                'keys' => isset($match[3]) ? [$key . strtoupper($match[3][0]), $key] : [$key],
                'anchor' => null,
            ];

            if (!in_array($catalog, self::LISTABLE_CATALOGS)) {
                continue;
            }

            $offset = $match[0][1] + strlen($match[0][0]);

            while (preg_match($listPattern, $title, $item, 0, $offset)) {
                $designations[] = [
                    'text' => $item[0],
                    'keys' => [self::buildKey($catalog, $item[1])],
                    'anchor' => $anchor,
                ];
                $offset += strlen($item[0]);
            }
        }

        return $designations;
    }

    private static function buildKey($catalog, $number)
    {
        return $catalog . ' ' . (int)$number;
    }

    /**
     * @param string $title
     * @return array groups of words (longest first), each has key, start, end
     */
    private static function findNicknameCandidates($title)
    {
        $words = self::splitWords($title);
        $candidates = [];

        for ($length = min(self::NICKNAME_MAX_WORDS, count($words)); $length > 0; $length--) {
            for ($start = 0; $start + $length <= count($words); $start++) {
                $key = self::joinWords(array_slice($words, $start, $length));

                if (strlen($key) >= self::NICKNAME_MIN_LENGTH) {
                    $candidates[] = ['key' => $key, 'start' => $start, 'end' => $start + $length];
                }
            }
        }

        return $candidates;
    }

    private static function splitWords($title)
    {
        $title = mb_strtolower(Helper::replaceUnaccent($title));
        // not in the list of Helper
        $title = strtr($title, ['ő' => 'o', 'ű' => 'u', 'ü' => 'u', "'" => '', '’' => '']);

        return preg_split('~[^a-z0-9]+~', $title, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * The suffix of the last word is dropped ("Szélkerék-galaxisban").
     */
    private static function joinWords(array $words)
    {
        $words = array_values($words);

        if (!empty($words)) {
            $last = count($words) - 1;
            $words[$last] = preg_replace('~^(.*(?:kod|galaxis|halmaz|lanc|kaszkad|triplet))[a-z]{1,5}$~', '$1', $words[$last]);
        }

        // keys of catalog numbers are upper case, so they never clash
        return implode('', $words);
    }

    private static function collectKeys(array $designations)
    {
        $keys = [];

        foreach ($designations as $designation) {
            $keys = array_merge($keys, $designation['keys']);
        }

        return $keys;
    }

    private function resolveDesignations(array $designations, array $found)
    {
        $objects = [];
        $anchors = [];
        $unknown = [];

        foreach ($designations as $index => $designation) {
            $known = array_values(array_filter($designation['keys'], function ($key) use ($found) {
                return !empty($found[$key]);
            }));

            if ($designation['anchor'] !== null) {
                $anchor = $anchors[$designation['anchor']] ?? null;

                // it is not the next number of the list, but a date or something like that
                if (empty($known) || $anchor === null || self::distance($anchor, $found[$known[0]][0]) > self::MAX_DISTANCE) {
                    continue;
                }
            }

            if (empty($known)) {
                $unknown[] = end($designation['keys']);
                continue;
            }

            $anchors[$index] = $found[$known[0]][0];

            foreach ($found[$known[0]] as $object) {
                // the first number counts, "NGC 2282 (IC 2172)"
                $objects += [$object['id'] => $object];
            }
        }

        return [$objects, array_values(array_unique($unknown))];
    }

    private function resolveNicknames(array $candidates, array $found)
    {
        $objects = [];
        $used = [];

        foreach ($candidates as $candidate) {
            $words = range($candidate['start'], $candidate['end'] - 1);

            if (empty($found[$candidate['key']]) || array_intersect($words, $used)) {
                continue;
            }

            $used = array_merge($used, $words);

            foreach ($found[$candidate['key']] as $object) {
                $objects[$object['id']] = $object;
            }
        }

        return $objects;
    }

    private static function isAnyNear(array $objects, array $others)
    {
        foreach ($objects as $object) {
            foreach ($others as $other) {
                if (self::distance($object, $other) <= self::MAX_DISTANCE) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Only titles with two objects are checked, there are mosaics of more.
     */
    private static function isScattered(array $objects)
    {
        $objects = array_values($objects);

        return count($objects) === 2 && self::distance($objects[0], $objects[1]) > self::SUSPICIOUS_DISTANCE;
    }

    /**
     * The constellation is checked only if it is a valid abbreviation, either given or written like "M13 GH (Her)".
     */
    private static function isInConstellation(array $objects, $title, $constellation)
    {
        $valid = array_map('strtolower', array_keys(Data::listConstellation()));
        $actual = array_map('strtolower', array_column($objects, 'constellation'));

        preg_match_all('~\( ?([a-z]{3}) ?\)~i', $title, $matches);
        $expected = array_map('strtolower', array_merge($matches[1], [trim((string)$constellation)]));

        $expected = array_intersect($expected, $valid);

        return empty($expected) || !empty(array_intersect($expected, $actual));
    }
}

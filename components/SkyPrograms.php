<?php
namespace app\components;

use app\models\ObservationSkyObject;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\Html;

/**
 * Observing programs: lists of deep sky objects to be observed. The items are in sky_list_items.
 */
class SkyPrograms
{
    const MESSIER = 'messier';
    const CALDWELL = 'caldwell';
    const NGC = 'ngc';
    const HERSCHEL_400 = 'herschel-400';
    const RASC = 'rasc';
    const SHARPLESS = 'sharpless';
    const BARNARD = 'barnard';
    const VDB = 'vdb';

    /** Objects to the south of this do not count as visible from Hungary, in degrees */
    const VISIBLE_DEC = -35;

    /** The existence of this Messier object is disputed */
    const DISPUTED_MESSIER = 102;

    /** Items made from the aliases of the objects, program => prefix of the alias */
    const ALIAS_PREFIXES = [
        self::MESSIER => 'M',
        self::CALDWELL => 'C',
        self::NGC => 'NGC',
        self::SHARPLESS => 'SH2',
        self::BARNARD => 'B',
        self::VDB => 'VDB',
    ];

    /** Items read from a file, program => file and the expected number of items */
    const FILES = [
        self::HERSCHEL_400 => ['herschel400.csv', 400],
        self::RASC => ['rasc.csv', 110],
    ];

    /**
     * @return array programs indexed by key: name, glyph (on the badge), unit (after a number), prefix (before the number
     * of an item), levels (the partial and the full one are added by Badges), description, and optionally links
     * (text in the description => URL, linked by getDescriptionHtml())
     */
    public static function getAll()
    {
        return [
            self::MESSIER => [
                'name' => 'Messier-katalógus',
                'glyph' => 'M',
                'unit' => 'Messier-objektum',
                'prefix' => 'M ',
                'levels' => [10, 25, 50, 75],
                'description' => 'Charles Messier XVIII. századi katalógusa, a legismertebb mélyég-objektumokkal. Mind a 110 látható Magyarországról. '
                    . 'Az M 102 kiléte vitatott, nálunk az NGC 5866, ezért a nélküle teljes katalógusért (109) külön jelvény jár.',
                'links' => [
                    'Charles Messier' => 'https://hu.wikipedia.org/wiki/Charles_Messier',
                ],
            ],
            self::CALDWELL => [
                'name' => 'Caldwell-katalógus',
                'glyph' => 'C',
                'unit' => 'Caldwell-objektum',
                'prefix' => 'C ',
                'levels' => [10, 25, 50],
                'description' => 'Patrick Caldwell-Moore 1995-ös válogatása azokból a látványos objektumokból, amelyek kimaradtak Messier katalógusából.',
                'links' => [
                    'Patrick Caldwell-Moore' => 'https://en.wikipedia.org/wiki/Patrick_Moore',
                ],
            ],
            self::HERSCHEL_400 => [
                'name' => 'Herschel 400',
                'glyph' => 'H',
                'unit' => 'Herschel 400-objektum',
                'prefix' => 'NGC ',
                'levels' => [25, 50, 100, 200, 300],
                'description' => 'Négyszáz objektum William Herschel felfedezései közül, az Astronomical League észlelőprogramja. '
                    . 'Két számpárja (NGC 2371 és 2372, NGC 6882 és 6885) a katalógusunkban ugyanaz az objektum.',
                'links' => [
                    'William Herschel' => 'https://hu.wikipedia.org/wiki/William_Herschel',
                ],
            ],
            self::RASC => [
                'name' => 'RASC Finest NGC',
                'glyph' => 'RASC',
                'unit' => 'RASC Finest NGC-objektum',
                'prefix' => '',
                'levels' => [25, 50],
                'description' => 'A Kanadai Királyi Csillagászati Társaság (RASC) listája a legszebb NGC-objektumokról, Alan Dyer válogatása. Egyik sem Messier-objektum.',
                'links' => [
                    'legszebb NGC-objektumokról' => 'https://www.rasc.ca/finest-ngc-objects',
                ],
            ],
            self::NGC => [
                'name' => 'NGC-katalógus',
                'glyph' => 'NGC',
                'unit' => 'NGC-objektum',
                'prefix' => 'NGC ',
                'levels' => [10, 50, 100, 250, 500, 1000, 2000, 3000, 5000],
                'description' => 'A New General Catalogue 7840 számot tartalmaz. Amelyik számok ugyanazt az objektumot jelölik, azokat egynek vesszük, '
                    . 'a nem létezőket pedig kihagyjuk.',
                'links' => [
                    'New General Catalogue' => 'https://vcse.hu/ngc-es-ic-melyeg-katalogus-csizmadia-szilard/',
                ],
            ],
            self::SHARPLESS => [
                'name' => 'Sharpless-katalógus',
                'glyph' => 'Sh2',
                'unit' => 'Sharpless-objektum',
                'prefix' => 'Sh2-',
                'levels' => [10, 25, 50, 100],
                'description' => 'Stewart Sharpless 1959-es katalógusa a HII-régiókról.',
                'links' => [
                    '1959-es katalógusa' => 'https://en.wikipedia.org/wiki/Sharpless_catalog',
                ],
            ],
            self::BARNARD => [
                'name' => 'Barnard-katalógus',
                'glyph' => 'B',
                'unit' => 'Barnard-objektum',
                'prefix' => 'B ',
                'levels' => [10, 25, 50, 100],
                'description' => 'Edward Emerson Barnard 1927-es katalógusa a sötét ködökről.',
                'links' => [
                    '1927-es katalógusa' => 'https://en.wikipedia.org/wiki/Barnard_Catalogue',
                ],
            ],
            self::VDB => [
                'name' => 'van den Bergh-katalógus',
                'glyph' => 'vdB',
                'unit' => 'vdB-objektum',
                'prefix' => 'vdB ',
                'levels' => [10, 25, 50, 100],
                'description' => 'Sidney van den Bergh 1966-os katalógusa a reflexiós ködökről.',
                'links' => [
                    '1966-os katalógusa' => 'https://en.wikipedia.org/wiki/Van_den_Bergh_Catalog',
                ],
            ],
        ];
    }

    /**
     * The encoded description with the program's links in it.
     */
    public static function getDescriptionHtml(array $program)
    {
        $html = Html::encode($program['description']);

        foreach ($program['links'] ?? [] as $text => $url) {
            $encoded = Html::encode($text);
            $html = str_replace($encoded, Html::a($encoded, $url, ['target' => '_blank', 'rel' => 'noopener']), $html);
        }

        return $html;
    }

    /**
     * @return string what counts as visible from Hungary
     */
    public static function getVisibilityNote()
    {
        return 'Magyarországról láthatónak a ' . str_replace('-', '−', (string)self::VISIBLE_DEC) . '° deklinációtól északra lévő objektumokat vesszük: '
            . 'ezek Budapestről nézve legalább 7,5° magasra emelkednek.';
    }

    /**
     * Every program has two goals at most: a part of it, and the whole.
     *
     * @param string $program
     * @param int $number
     * @param bool $isVisible
     * @return bool whether the item belongs to the part
     */
    public static function isInPart($program, $number, $isVisible)
    {
        return $program === self::MESSIER ? (int)$number !== self::DISPUTED_MESSIER : $isVisible;
    }

    /**
     * @return array total and part (numbers of items) indexed by program, programs without any item are left out
     */
    public static function getTotals()
    {
        $rows = (new Query())
            ->select(['list', 'total' => 'COUNT(*)', 'visible' => 'SUM(CASE WHEN is_visible THEN 1 ELSE 0 END)'])
            ->from(['v' => self::visibilityQuery()])
            ->groupBy('list')
            ->all();
        $totals = [];

        foreach ($rows as $row) {
            $totals[$row['list']] = [
                'total' => (int)$row['total'],
                'part' => $row['list'] === self::MESSIER ? (int)$row['total'] - 1 : (int)$row['visible'],
            ];
        }

        return $totals;
    }

    /**
     * @param int $userId
     * @return array lists of done items indexed by program, an item has date (when it was uploaded first) and isInPart
     */
    public static function findDone(int $userId)
    {
        $rows = self::observedItemQuery($userId)
            ->select(['i.list', 'i.number', 'v.is_visible', 'date' => 'MIN(o.uploaded_at)'])
            ->innerJoin(['v' => self::visibilityQuery()], 'v.list = i.list AND v.number = i.number')
            ->groupBy(['i.list', 'i.number', 'v.is_visible'])
            ->all();
        $done = [];

        foreach ($rows as $row) {
            $done[$row['list']][] = [
                'date' => $row['date'],
                'isInPart' => self::isInPart($row['list'], $row['number'], (bool)$row['is_visible']),
            ];
        }

        return $done;
    }

    /**
     * @param string $program
     * @param int $userId
     * @return array observations (id, type, date), the first one first, indexed by the number of the item
     */
    public static function findObservations($program, int $userId)
    {
        $rows = self::observedItemQuery($userId)
            ->select(['i.number', 'o.id', 'o.type', 'o.date'])
            ->distinct()
            ->andWhere(['i.list' => $program])
            ->orderBy(['o.date' => SORT_ASC, 'o.id' => SORT_ASC])
            ->all();
        $observations = [];

        foreach ($rows as $row) {
            $observations[$row['number']][] = $row;
        }

        return $observations;
    }

    /**
     * @param string $program
     * @return array objects (as arrays, with is_observed) indexed by the number of the item
     */
    public static function findItems($program)
    {
        $observed = (new Query())
            ->select('sky_object_id')
            ->distinct()
            ->from('observation_sky_objects')
            ->where(['status' => ObservationSkyObject::VISIBLE_STATUSES]);
        $rows = (new Query())
            ->select(['i.number', 's.*', 'is_observed' => new Expression('l.sky_object_id IS NOT NULL')])
            ->from(['i' => 'sky_list_items'])
            ->innerJoin(['s' => 'sky_objects'], 's.id = i.sky_object_id')
            ->leftJoin(['l' => $observed], 'l.sky_object_id = s.id')
            ->where(['i.list' => $program])
            ->orderBy(['i.number' => SORT_ASC, 'i.id' => SORT_ASC])
            ->all();
        $items = [];

        foreach ($rows as $row) {
            $items[$row['number']][] = $row;
        }

        return $items;
    }

    /**
     * Number of constellations: the ones with an object that can be seen from Hungary.
     *
     * @return int
     */
    public static function countVisibleConstellations()
    {
        return (int)(new Query())->from('sky_objects')->where(['>=', 'dec', self::VISIBLE_DEC])->count('DISTINCT constellation');
    }

    /**
     * @param int $userId
     * @return array list of constellations with date and isInPart
     */
    public static function findConstellations(int $userId)
    {
        $visible = (new Query())->select('constellation')->distinct()->from('sky_objects')->where(['>=', 'dec', self::VISIBLE_DEC])->column();
        $rows = (new Query())
            ->select(['s.constellation', 'date' => 'MIN(o.uploaded_at)'])
            ->from(['s' => 'sky_objects'])
            ->innerJoin(['l' => 'observation_sky_objects'], 'l.sky_object_id = s.id')
            ->innerJoin(['o' => 'observations'], 'o.id = l.observation_id')
            ->where(['o.observer_id' => $userId, 'l.status' => ObservationSkyObject::VISIBLE_STATUSES])
            ->groupBy('s.constellation')
            ->all();

        return array_map(function ($row) use ($visible) {
            return ['date' => $row['date'], 'isInPart' => in_array($row['constellation'], $visible, true)];
        }, $rows);
    }

    private static function observedItemQuery(int $userId)
    {
        return (new Query())
            ->from(['i' => 'sky_list_items'])
            ->innerJoin(['l' => 'observation_sky_objects'], 'l.sky_object_id = i.sky_object_id')
            ->innerJoin(['o' => 'observations'], 'o.id = l.observation_id')
            ->where(['o.observer_id' => $userId, 'l.status' => ObservationSkyObject::VISIBLE_STATUSES]);
    }

    private static function visibilityQuery()
    {
        return (new Query())
            ->select(['i.list', 'i.number', 'is_visible' => new Expression('MAX(s.dec) >= ' . self::VISIBLE_DEC)])
            ->from(['i' => 'sky_list_items'])
            ->innerJoin(['s' => 'sky_objects'], 's.id = i.sky_object_id')
            ->groupBy(['i.list', 'i.number']);
    }
}

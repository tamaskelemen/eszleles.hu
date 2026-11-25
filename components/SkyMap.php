<?php
namespace app\components;

use app\models\ObservationSkyObject;
use app\models\Observe;
use app\models\SkyObject;
use Yii;
use yii\db\Connection;
use yii\db\Query;

/**
 * Data of the sky map: the observed objects and the statistics of the coverage.
 */
class SkyMap
{
    const CACHE_KEY = 'sky-map-3';
    const CACHE_DURATION = 600;

    /** Objects to the south of this never rise in Hungary, in degrees */
    const HORIZON_DEC = -43;

    /** Gaps are looked for only where the sky is comfortable to observe from Hungary */
    const GAP_DEC = -25;
    const GAP_COUNT = 5;
    /** Centers of the gaps can not be closer than this, in degrees */
    const GAP_DISTANCE = 25;

    /** A point of the sky is covered if there is an observed object at most this far, in degrees */
    const COVER_RADIUS = 3;

    /** The sky is sampled at this many points */
    const POINT_COUNT = 6000;

    /** Catalogs we do not have or only partly, key of the matcher => name on the page */
    const UNMAPPED_CATALOGS = [
        'ABELL' => 'Abell',
        'CR' => self::UNMAPPED_CLUSTERS,
        'STOCK' => self::UNMAPPED_CLUSTERS,
        'MEL' => self::UNMAPPED_CLUSTERS,
        'BERKELEY' => self::UNMAPPED_CLUSTERS,
        'TR' => self::UNMAPPED_CLUSTERS,
        'KING' => self::UNMAPPED_CLUSTERS,
        'UGC' => 'UGC',
    ];
    const UNMAPPED_OTHER = 'Egyéb (Arp, Hickson, PGC, Simeis stb.)';
    const UNMAPPED_CLUSTERS = 'Nyílthalmazok (Collinder, Stock, Melotte, Berkeley stb.)';

    const MESSIER_COUNT = 110;
    const CALDWELL_COUNT = 109;

    /**
     * @return array objects, stats, gaps, missing (constellations), unmapped (catalogs)
     */
    public static function getData()
    {
        return Yii::$app->cache->getOrSet(self::CACHE_KEY, function () {
            $objects = self::findObjects();
            list($coverage, $gaps) = self::findCoverage($objects);

            foreach ($gaps as &$gap) {
                $gap['constellation'] = self::findConstellation($gap);
            }

            return [
                'objects' => $objects,
                'stats' => self::countStats($objects) + ['coverage' => $coverage],
                'gaps' => $gaps,
                'missing' => self::findMissingConstellations($objects),
                'unmapped' => self::countUnmappedCatalogs(),
            ];
        }, self::CACHE_DURATION);
    }

    /**
     * Has to be called when an identification changes. Inside of a transaction it takes effect after the commit, until then
     * others would only cache the old data again.
     */
    public static function clearCache()
    {
        $db = Yii::$app->db;

        if ($db->getTransaction() === null) {
            Yii::$app->cache->delete(self::CACHE_KEY);
            return;
        }

        $handler = [self::class, 'handleCommit'];
        $db->off(Connection::EVENT_COMMIT_TRANSACTION, $handler);
        $db->on(Connection::EVENT_COMMIT_TRANSACTION, $handler);
    }

    public static function handleCommit()
    {
        Yii::$app->db->off(Connection::EVENT_COMMIT_TRANSACTION, [self::class, 'handleCommit']);
        Yii::$app->cache->delete(self::CACHE_KEY);
    }

    /**
     * Share of the sky (that can be seen from Hungary) near to an observed object, and the largest areas without any.
     *
     * @param array $objects each has ra and dec
     * @return array coverage (0-1), gaps (each has ra, dec and radius in degrees)
     */
    public static function findCoverage(array $objects)
    {
        $vectors = array_map([self::class, 'toVector'], $objects);
        $minCos = cos(deg2rad(self::COVER_RADIUS));
        $visibleCount = 0;
        $coveredCount = 0;
        $candidates = [];

        foreach (self::getPoints() as $point) {
            if ($point['dec'] < self::HORIZON_DEC) {
                continue;
            }

            list($x, $y, $z) = self::toVector($point);
            $cos = -1;

            foreach ($vectors as $vector) {
                $value = $x * $vector[0] + $y * $vector[1] + $z * $vector[2];
                if ($value > $cos) {
                    $cos = $value;
                }
            }

            $visibleCount++;

            if ($cos >= $minCos) {
                $coveredCount++;
            } else if ($point['dec'] >= self::GAP_DEC) {
                $candidates[] = $point + ['radius' => rad2deg(acos(max(-1, min(1, $cos))))];
            }
        }

        usort($candidates, function ($a, $b) {
            return $b['radius'] <=> $a['radius'];
        });

        return [$coveredCount / $visibleCount, self::selectGaps($candidates)];
    }

    /**
     * @return array list of points distributed evenly on the sphere
     */
    private static function getPoints()
    {
        $points = [];
        $angle = 180 * (3 - sqrt(5));

        for ($i = 0; $i < self::POINT_COUNT; $i++) {
            $points[] = [
                'ra' => fmod($i * $angle, 360),
                'dec' => rad2deg(asin(1 - 2 * ($i + 0.5) / self::POINT_COUNT)),
            ];
        }

        return $points;
    }

    private static function selectGaps(array $candidates)
    {
        $gaps = [];

        foreach ($candidates as $candidate) {
            foreach ($gaps as $gap) {
                if (SkyMatcher::distance($gap, $candidate) < self::GAP_DISTANCE) {
                    continue 2;
                }
            }

            $gaps[] = $candidate;

            if (count($gaps) === self::GAP_COUNT) {
                break;
            }
        }

        return $gaps;
    }

    private static function toVector(array $point)
    {
        $ra = deg2rad($point['ra']);
        $dec = deg2rad($point['dec']);

        return [cos($dec) * cos($ra), cos($dec) * sin($ra), sin($dec)];
    }

    /**
     * @param int $userId
     * @return array|null null if the user has nothing to show on the map
     */
    public static function getUserStats(int $userId)
    {
        $objects = self::findObjects($userId);

        if (empty($objects)) {
            return null;
        }

        // only the coverage is slow, it depends on nothing else than the objects, so it never has to be cleared
        $key = [self::CACHE_KEY, 'coverage', array_column($objects, 'id')];
        $coverage = Yii::$app->cache->getOrSet($key, function () use ($objects) {
            return self::findCoverage($objects)[0];
        }, self::CACHE_DURATION);

        return self::countStats($objects, $userId) + ['coverage' => $coverage];
    }

    /**
     * @param int|null $userId only the observations of this user count
     * @return array the publicly identified objects, the most observed first
     */
    public static function findObjects(?int $userId = null)
    {
        $rows = self::linkQuery($userId)
            ->select([
                's.id', 's.name', 's.messier',
                // the number most observers use
                'designation' => 'MODE() WITHIN GROUP (ORDER BY COALESCE(l.designation, s.name))',
                's.common_name', 's.type', 's.constellation', 's.ra', 's.dec',
                'observations' => 'COUNT(DISTINCT o.id)',
                'observers' => 'COUNT(DISTINCT o.observer_id)',
            ])
            ->groupBy('s.id')
            ->orderBy(['observations' => SORT_DESC, 's.id' => SORT_ASC])
            ->all();

        return array_map(function ($row) {
            return self::buildObject($row) + [
                'observations' => (int)$row['observations'],
                'observers' => (int)$row['observers'],
            ];
        }, $rows);
    }

    /**
     * @param string $program
     * @return array objects of the observing program, without the number of observations
     */
    public static function findProgramObjects($program)
    {
        $rows = (new Query())
            ->select(['id', 'name', 'messier', 'common_name', 'type', 'constellation', 'ra', 'dec'])
            ->from('sky_objects')
            ->where(['id' => (new Query())->select('sky_object_id')->from('sky_list_items')->where(['list' => $program])])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        return array_map([self::class, 'buildObject'], $rows);
    }

    private static function buildObject(array $row)
    {
        return [
            'id' => (int)$row['id'],
            'label' => SkyObject::buildLabel($row),
            'type' => SkyObject::getTypeName($row['type']),
            'constellation' => $row['constellation'],
            'ra' => round($row['ra'], 3),
            'dec' => round($row['dec'], 3),
        ];
    }

    private static function countStats(array $objects, ?int $userId = null)
    {
        $ids = array_column($objects, 'id');

        return [
            'observations' => (int)self::linkQuery($userId)->count('DISTINCT o.id'),
            'observers' => (int)self::linkQuery($userId)->count('DISTINCT o.observer_id'),
            'objects' => count($objects),
            'constellations' => count(array_unique(array_column($objects, 'constellation'))),
            'messier' => (int)SkyObject::find()->where(['id' => $ids])->count('DISTINCT messier'),
            // some of the Caldwell numbers belong to more objects (C 14 is NGC 869 and NGC 884)
            'caldwell' => (int)(new Query())
                ->from('sky_object_aliases')
                ->where(['sky_object_id' => $ids])
                ->andWhere(['like', 'alias', 'C %', false])
                ->count('DISTINCT alias'),
        ];
    }

    private static function findMissingConstellations(array $objects)
    {
        $observed = array_column($objects, 'constellation', 'constellation');

        // these can be seen from Hungary at least partly
        $visible = SkyObject::find()->select('constellation')->distinct()->where(['>', 'dec', self::HORIZON_DEC])->column();
        $visible = array_diff($visible, array_keys($observed));
        sort($visible);

        return $visible;
    }

    /**
     * Not identified observations that have a catalog number we do not know, each is counted once, by its first one.
     *
     * @return int[] number of observations indexed by catalog, the most first
     */
    private static function countUnmappedCatalogs()
    {
        $links = ObservationSkyObject::find()
            ->where('observation_sky_objects.observation_id = observations.id')
            ->andWhere(['!=', 'observation_sky_objects.status', ObservationSkyObject::STATUS_REJECTED]);
        $titles = Observe::find()
            ->select('object_name')
            ->where(['type' => Observe::TYPE_DEEP_SKY, 'has_no_sky_object' => false])
            ->andWhere(['not exists', $links])
            ->column();

        $matcher = new SkyMatcher([SkyObject::class, 'findByAliases']);
        // every catalog is listed, even if there is no observation of it
        $counts = array_fill_keys(array_merge(self::UNMAPPED_CATALOGS, [self::UNMAPPED_OTHER]), 0);

        foreach ($titles as $title) {
            $match = $matcher->match($title);

            // if it has objects, they are only not stored yet
            if (!empty($match['objects']) || empty($match['unknown'])) {
                continue;
            }

            $catalog = explode(' ', $match['unknown'][0])[0];
            $label = self::UNMAPPED_CATALOGS[$catalog] ?? self::UNMAPPED_OTHER;
            $counts[$label]++;
        }

        arsort($counts);

        // the last one is always the rest
        $other = $counts[self::UNMAPPED_OTHER];
        unset($counts[self::UNMAPPED_OTHER]);
        $counts[self::UNMAPPED_OTHER] = $other;

        return $counts;
    }

    /**
     * The constellation of the nearest object of the catalog.
     */
    private static function findConstellation(array $point)
    {
        list($x, $y, $z) = self::toVector($point);

        $row = SkyObject::find()
            ->select('constellation')
            ->orderBy(new \yii\db\Expression(
                '(COS(RADIANS(dec)) * COS(RADIANS(ra)) * :x + COS(RADIANS(dec)) * SIN(RADIANS(ra)) * :y + SIN(RADIANS(dec)) * :z) DESC',
                [':x' => $x, ':y' => $y, ':z' => $z]
            ))
            ->limit(1)
            ->asArray()
            ->one();

        return $row['constellation'];
    }

    /**
     * @param int|null $userId
     * @return Query
     */
    private static function linkQuery(?int $userId = null)
    {
        return (new Query())
            ->from(['l' => 'observation_sky_objects'])
            ->innerJoin(['s' => 'sky_objects'], 's.id = l.sky_object_id')
            ->innerJoin(['o' => 'observations'], 'o.id = l.observation_id')
            ->where(['l.status' => ObservationSkyObject::VISIBLE_STATUSES])
            ->andFilterWhere(['o.observer_id' => $userId]);
    }
}

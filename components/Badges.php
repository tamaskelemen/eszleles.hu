<?php
namespace app\components;

use app\models\Comment;
use app\models\Observe;
use app\models\UserBadge;
use yii\helpers\ArrayHelper;

/**
 * Counts the badges of the users from the observations, the identified objects and the comments. The earned ones are
 * stored by BadgeSync, display those unless the progress is needed too.
 */
class Badges
{
    const GROUP_ACTIVITY = 'activity';
    const GROUP_PROGRAM = 'program';

    const OBSERVATION_LEVELS = [1, 10, 50, 100, 200, 500, 1000, 2000];
    const COMMENT_LEVELS = [1, 10, 50, 100, 200, 300, 500, 1000];
    const TYPE_LEVELS = [3, 5];
    const CONSTELLATION_LEVELS = [10, 25, 40];

    /** Number of colors, see .badge-mark in site.css */
    const TIER_COUNT = 8;

    /**
     * @return array key, group, name and glyph (null if it has an icon) of the categories, indexed by key
     */
    public static function getCategories()
    {
        $categories = [];

        foreach ([
            'observations' => 'Észlelések',
            'comments' => 'Hozzászólások',
            'types' => 'Sokoldalúság',
            'constellations' => 'Csillagképek',
        ] as $key => $name) {
            $categories[$key] = ['key' => $key, 'group' => self::GROUP_ACTIVITY, 'name' => $name, 'glyph' => null];
        }

        foreach (SkyPrograms::getAll() as $key => $program) {
            $categories[$key] = ['key' => $key, 'group' => self::GROUP_PROGRAM, 'name' => $program['name'], 'glyph' => $program['glyph']];
        }

        return $categories;
    }

    /**
     * What anyone can see: the stored badges only, without any progress.
     *
     * @param UserBadge[] $badges of one user
     * @return array categories with levels and note
     */
    public static function groupStored(array $badges)
    {
        $levels = [];

        foreach ($badges as $badge) {
            $levels[$badge->category][] = $badge->getLevel();
        }

        $categories = [];
        $totals = empty($levels) ? [] : SkyPrograms::getTotals();

        foreach (array_intersect_key(self::getCategories(), $levels) as $key => $category) {
            ArrayHelper::multisort($levels[$key], 'threshold');
            $categories[] = ['levels' => $levels[$key], 'note' => self::getNote($key, $totals)] + $category;
        }

        return $categories;
    }

    /**
     * @param string $key of the category
     * @param array $totals see SkyPrograms::getTotals()
     * @return string|null what counts
     */
    private static function getNote($key, array $totals)
    {
        if ($key === 'constellations') {
            return 'Azok a csillagképek számítanak, amelyekben van azonosított objektum az észlelések között. ' . SkyPrograms::getVisibilityNote();
        }

        $program = SkyPrograms::getAll()[$key] ?? null;

        if ($program === null) {
            return null;
        }

        $hasPart = isset($totals[$key]) && $totals[$key]['part'] < $totals[$key]['total'];

        return $program['description'] . ($hasPart && $key !== SkyPrograms::MESSIER ? ' ' . SkyPrograms::getVisibilityNote() : '');
    }

    /**
     * Counts everything, so it is slow.
     *
     * @param int $userId
     * @return array categories, see build()
     */
    public static function findAll(int $userId)
    {
        $definitions = self::getCategories();
        $categories = [
            self::build($definitions['observations'] + [
                'unit' => 'észlelés',
                'firstTitle' => 'Első észlelés',
                'levels' => self::OBSERVATION_LEVELS,
            ], self::toItems(Observe::find()->select('uploaded_at')->ofUser($userId)->column())),

            self::build($definitions['comments'] + [
                'unit' => 'hozzászólás',
                'firstTitle' => 'Első hozzászólás',
                'levels' => self::COMMENT_LEVELS,
            ], self::toItems(Comment::find()->select('created_at')->where(['user_id' => $userId])->column())),

            self::build($definitions['types'] + [
                'unit' => 'észleléstípus',
                'levels' => self::TYPE_LEVELS,
                'total' => count(Observe::getAllTypes()),
                'totalTitle' => 'Minden észleléstípus',
            ], self::toItems(Observe::find()->select('MIN(uploaded_at)')->ofUser($userId)->groupBy('type')->column())),
        ];

        $programs = SkyPrograms::getAll();
        $totals = SkyPrograms::getTotals();

        // the catalog is not imported yet
        if (empty($totals)) {
            return $categories;
        }

        $categories[] = self::build($definitions['constellations'] + [
            'unit' => 'csillagkép',
            'levels' => self::CONSTELLATION_LEVELS,
            'part' => SkyPrograms::countVisibleConstellations(),
            'partTitle' => 'Minden Magyarországról látható csillagkép',
            'partNote' => 'Magyarországról látható',
            'total' => count(Data::listConstellation()),
            'totalTitle' => 'Minden csillagkép',
            'note' => self::getNote('constellations', $totals),
        ], SkyPrograms::findConstellations($userId));

        $done = SkyPrograms::findDone($userId);

        foreach ($programs as $key => $program) {
            if (!isset($totals[$key])) {
                continue;
            }

            $isMessier = $key === SkyPrograms::MESSIER;

            $categories[] = self::build($definitions[$key] + [
                'unit' => $program['unit'],
                'levels' => $program['levels'],
                'part' => $totals[$key]['part'],
                'partTitle' => $isMessier ? 'Minden Messier-objektum az M 102 nélkül' : "Minden Magyarországról látható {$program['unit']}",
                'partNote' => $isMessier ? '(M 102 nélkül)' : 'Magyarországról látható',
                'total' => $totals[$key]['total'],
                'totalTitle' => "A teljes {$program['name']}",
                'note' => self::getNote($key, $totals),
            ], $done[$key] ?? []);
        }

        return $categories;
    }

    /**
     * @param array $definition key, group, name, unit, levels, and optionally firstTitle, glyph, note, part (number of the
     * items of the part), partTitle, partNote, total, totalTitle
     * @param array $items what the user has done, each has date and isInPart
     * @return array the definition with count, partCount, levels (threshold, title, tier, isEarned, date) and next (the
     * first missing level with value, null if there is none)
     */
    public static function build(array $definition, array $items)
    {
        $definition += ['firstTitle' => null, 'glyph' => null, 'note' => null, 'part' => null, 'partNote' => null, 'total' => null];

        // the part is not interesting if it is the whole
        if ($definition['part'] !== null && $definition['part'] >= $definition['total']) {
            $definition['part'] = null;
        }

        $dates = self::sortDates($items);
        $partDates = self::sortDates(array_filter($items, function ($item) {
            return $item['isInPart'];
        }));
        $goal = $definition['part'] ?? $definition['total'];
        $levels = [];
        $next = null;

        foreach ($definition['levels'] as $threshold) {
            if ($goal === null || $threshold < $goal) {
                $isFirst = $threshold === 1 && $definition['firstTitle'] !== null;
                $title = $isFirst ? $definition['firstTitle'] : "$threshold {$definition['unit']}";
                $levels[] = self::buildLevel($threshold, $title, $dates);
            }
        }

        if ($definition['part'] !== null) {
            $levels[] = self::buildLevel($definition['part'], "{$definition['partTitle']} ({$definition['part']})", $partDates);
        }

        if ($definition['total'] !== null) {
            $levels[] = self::buildLevel($definition['total'], "{$definition['totalTitle']} ({$definition['total']})", $dates);
        }

        foreach ($levels as $index => &$level) {
            // the first level always has the first color, the last one has the last
            $level['tier'] = count($levels) === 1 ? self::TIER_COUNT : 1 + (int)round($index / (count($levels) - 1) * (self::TIER_COUNT - 1));

            if (!$level['isEarned'] && $next === null) {
                $next = $level;
            }
        }
        unset($level);

        return [
            'count' => count($dates),
            'partCount' => count($partDates),
            'levels' => $levels,
            'next' => $next,
        ] + $definition;
    }

    /**
     * @param array $category
     * @return array
     */
    public static function getEarnedLevels(array $category)
    {
        return array_values(array_filter($category['levels'], function ($level) {
            return $level['isEarned'];
        }));
    }

    private static function buildLevel($threshold, $title, array $dates)
    {
        return [
            'threshold' => $threshold,
            'title' => $title,
            'value' => count($dates),
            'isEarned' => count($dates) >= $threshold,
            'date' => $dates[$threshold - 1] ?? null,
        ];
    }

    private static function toItems(array $dates)
    {
        return array_map(function ($date) {
            return ['date' => $date, 'isInPart' => false];
        }, $dates);
    }

    private static function sortDates(array $items)
    {
        $dates = array_map('strval', array_column($items, 'date'));
        // the ones without a date are the oldest
        sort($dates);

        return $dates;
    }
}

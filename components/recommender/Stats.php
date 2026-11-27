<?php

namespace app\components\recommender;

use Yii;
use yii\db\Expression;
use yii\db\Query;

/**
 * Daily usage counters of the recommender.
 */
class Stats
{
    /** The page was opened, the first recommendation was shown */
    const EVENT_OPEN = 'open';
    /** "Másikat kérek" */
    const EVENT_NEXT = 'next';
    /** A filter was changed or reset */
    const EVENT_FILTER = 'filter';
    /** An observation was written down under the recommendation */
    const EVENT_OBSERVE = 'observe';

    /**
     * @return string[] Hungarian labels by event
     */
    public static function getAllEvents()
    {
        return [
            self::EVENT_OPEN => 'Megnyitás',
            self::EVENT_NEXT => 'Másikat kérek',
            self::EVENT_FILTER => 'Szűrőváltás',
            self::EVENT_OBSERVE => 'Feljegyzett észlelés',
        ];
    }

    /**
     * Counts an event. A failure is only written to the application log, it must not break the recommender.
     *
     * @param string $event one of the EVENT_* constants
     * @param int|null $userId null for a guest
     */
    public static function record($event, $userId)
    {
        if (!isset(self::getAllEvents()[$event])) {
            return;
        }

        try {
            $db = Yii::$app->db;
            $day = date('Y-m-d');

            $db->createCommand()->upsert('recommender_stats', [
                'day' => $day,
                'event' => $event,
                'is_guest' => $userId === null,
                'count' => 1,
            ], [
                'count' => new Expression('recommender_stats.count + 1'),
            ])->execute();

            if ($userId !== null) {
                $db->createCommand()->upsert('recommender_user_days', ['day' => $day, 'user_id' => $userId], false)->execute();
            }
        } catch (\Exception $e) {
            Yii::error($e, __METHOD__);
        }
    }

    /**
     * @param int $days how many days back, today included
     * @return array rows by day (newest first): day, users, and [event][guest|user] counts
     */
    public static function getDaily($days)
    {
        $since = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
        $rows = [];

        for ($i = 0; $i < $days; $i++) {
            $day = date('Y-m-d', strtotime("-$i days"));
            $rows[$day] = ['day' => $day, 'users' => 0, 'events' => self::emptyCounts()];
        }

        $counts = (new Query())->from('recommender_stats')->where(['>=', 'day', $since])->all();
        foreach ($counts as $count) {
            $rows[$count['day']]['events'][$count['event']][$count['is_guest'] ? 'guest' : 'user'] = (int)$count['count'];
        }

        $users = (new Query())->select(['day', 'n' => 'COUNT(*)'])->from('recommender_user_days')
            ->where(['>=', 'day', $since])->groupBy('day')->all();
        foreach ($users as $user) {
            $rows[$user['day']]['users'] = (int)$user['n'];
        }

        return array_values($rows);
    }

    /**
     * @param string|null $since a day, null for all time
     * @return array users (distinct), and [event][guest|user] counts
     */
    public static function getTotals($since = null)
    {
        $totals = ['users' => 0, 'events' => self::emptyCounts()];

        $counts = (new Query())->select(['event', 'is_guest', 'n' => 'SUM(count)'])->from('recommender_stats')
            ->andFilterWhere(['>=', 'day', $since])->groupBy(['event', 'is_guest'])->all();
        foreach ($counts as $count) {
            if (isset($totals['events'][$count['event']])) {
                $totals['events'][$count['event']][$count['is_guest'] ? 'guest' : 'user'] = (int)$count['n'];
            }
        }

        $totals['users'] = (int)(new Query())->from('recommender_user_days')
            ->andFilterWhere(['>=', 'day', $since])->count('DISTINCT user_id');

        return $totals;
    }

    /**
     * @return array
     */
    private static function emptyCounts()
    {
        return array_fill_keys(array_keys(self::getAllEvents()), ['guest' => 0, 'user' => 0]);
    }
}

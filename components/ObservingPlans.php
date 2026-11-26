<?php
namespace app\components;

use app\models\ObservationSkyObject;
use yii\db\Expression;
use yii\db\Query;

/**
 * Progress of the observing plans. An object of a plan counts as done the same way as in SkyPrograms: the user has an
 * observation that is identified as that object. An own observation can also be attached to an object of a plan by hand.
 */
class ObservingPlans
{
    /**
     * @param int $planId
     * @return array objects (as arrays, with item_id, designation and is_observed) in the order of the plan, indexed by object id
     */
    public static function findItems(int $planId)
    {
        $observed = (new Query())
            ->select('sky_object_id')
            ->distinct()
            ->from('observation_sky_objects')
            ->where(['status' => ObservationSkyObject::VISIBLE_STATUSES]);

        return (new Query())
            ->select(['s.*', 'item_id' => 'i.id', 'i.designation', 'is_observed' => new Expression('l.sky_object_id IS NOT NULL')])
            ->from(['i' => 'observing_plan_items'])
            ->innerJoin(['s' => 'sky_objects'], 's.id = i.sky_object_id')
            ->leftJoin(['l' => $observed], 'l.sky_object_id = s.id')
            ->where(['i.plan_id' => $planId])
            ->orderBy(['i.position' => SORT_ASC, 'i.id' => SORT_ASC])
            ->indexBy('id')
            ->all();
    }

    /**
     * @param int $planId
     * @param int $userId
     * @return array observations (id, type, date, is_manual) of the user, the first one first, indexed by object id
     */
    public static function findObservations(int $planId, int $userId)
    {
        $rows = (new Query())
            ->from(['u' => self::observationQuery($userId, $planId)])
            ->orderBy(['u.date' => SORT_ASC, 'u.id' => SORT_ASC, 'u.is_manual' => SORT_ASC])
            ->all();
        $observations = [];

        foreach ($rows as $row) {
            // identified and attached as well
            if (!isset($observations[$row['sky_object_id']][$row['id']])) {
                $row['is_manual'] = (bool)$row['is_manual'];
                $observations[$row['sky_object_id']][$row['id']] = $row;
            }
        }

        return array_map('array_values', $observations);
    }

    /**
     * @param int[] $planIds
     * @return int[] number of objects indexed by plan id, empty plans are left out
     */
    public static function countItems(array $planIds)
    {
        return array_map('intval', (new Query())
            ->select(['total' => 'COUNT(*)', 'plan_id'])
            ->from('observing_plan_items')
            ->where(['plan_id' => $planIds])
            ->groupBy('plan_id')
            ->indexBy('plan_id')
            ->column());
    }

    /**
     * @param int[] $planIds
     * @param int $userId
     * @return int[] number of objects the user has observed indexed by plan id, plans without any are left out
     */
    public static function countDone(array $planIds, int $userId)
    {
        return array_map('intval', (new Query())
            ->select(['done' => 'COUNT(DISTINCT u.sky_object_id)', 'u.plan_id'])
            ->from(['u' => self::observationQuery($userId, $planIds)])
            ->groupBy('u.plan_id')
            ->indexBy('plan_id')
            ->column());
    }

    /**
     * @param int $userId
     * @param int|int[] $planIds
     * @return Query observations (plan_id, sky_object_id, id, type, date, is_manual) of the user for the objects of the
     * plans, identified or attached by hand
     */
    private static function observationQuery(int $userId, $planIds)
    {
        $columns = ['i.plan_id', 'i.sky_object_id', 'o.id', 'o.type', 'o.date'];
        $identified = (new Query())
            ->select(array_merge($columns, ['is_manual' => new Expression('false')]))
            ->from(['i' => 'observing_plan_items'])
            ->innerJoin(['l' => 'observation_sky_objects'], 'l.sky_object_id = i.sky_object_id')
            ->innerJoin(['o' => 'observations'], 'o.id = l.observation_id')
            ->where(['i.plan_id' => $planIds, 'o.observer_id' => $userId, 'l.status' => ObservationSkyObject::VISIBLE_STATUSES]);
        $attached = (new Query())
            ->select(array_merge($columns, ['is_manual' => new Expression('true')]))
            ->from(['i' => 'observing_plan_items'])
            ->innerJoin(['a' => 'observing_plan_item_observations'], 'a.item_id = i.id')
            ->innerJoin(['o' => 'observations'], 'o.id = a.observation_id')
            ->where(['i.plan_id' => $planIds, 'o.observer_id' => $userId]);

        return $identified->union($attached, true);
    }
}

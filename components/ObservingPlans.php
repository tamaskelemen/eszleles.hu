<?php
namespace app\components;

use app\models\ObservationSkyObject;
use yii\db\Expression;
use yii\db\Query;

/**
 * Progress of the observing plans. An object of a plan counts as done the same way as in SkyPrograms: the user has an
 * observation that is identified as that object.
 */
class ObservingPlans
{
    /**
     * @param int $planId
     * @return array objects (as arrays, with designation and is_observed) in the order of the plan, indexed by object id
     */
    public static function findItems(int $planId)
    {
        $observed = (new Query())
            ->select('sky_object_id')
            ->distinct()
            ->from('observation_sky_objects')
            ->where(['status' => ObservationSkyObject::VISIBLE_STATUSES]);

        return (new Query())
            ->select(['s.*', 'i.designation', 'is_observed' => new Expression('l.sky_object_id IS NOT NULL')])
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
     * @return array observations (id, type, date) of the user, the first one first, indexed by object id
     */
    public static function findObservations(int $planId, int $userId)
    {
        $rows = self::observedItemQuery($userId)
            ->select(['i.sky_object_id', 'o.id', 'o.type', 'o.date'])
            ->distinct()
            ->andWhere(['i.plan_id' => $planId])
            ->orderBy(['o.date' => SORT_ASC, 'o.id' => SORT_ASC])
            ->all();
        $observations = [];

        foreach ($rows as $row) {
            $observations[$row['sky_object_id']][] = $row;
        }

        return $observations;
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
        return array_map('intval', self::observedItemQuery($userId)
            ->select(['done' => 'COUNT(DISTINCT i.sky_object_id)', 'i.plan_id'])
            ->andWhere(['i.plan_id' => $planIds])
            ->groupBy('i.plan_id')
            ->indexBy('plan_id')
            ->column());
    }

    private static function observedItemQuery(int $userId)
    {
        return (new Query())
            ->from(['i' => 'observing_plan_items'])
            ->innerJoin(['l' => 'observation_sky_objects'], 'l.sky_object_id = i.sky_object_id')
            ->innerJoin(['o' => 'observations'], 'o.id = l.observation_id')
            ->where(['o.observer_id' => $userId, 'l.status' => ObservationSkyObject::VISIBLE_STATUSES]);
    }
}

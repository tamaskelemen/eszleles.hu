<?php

namespace app\components\recommender\filters;

use app\components\recommender\Context;
use app\components\recommender\Filter;
use app\models\ObservationSkyObject;
use yii\db\Query;

/**
 * Objects the user has not uploaded an observation of yet.
 */
class UnobservedFilter extends Filter
{
    public function getKey()
    {
        return 'uj';
    }

    public function getLabel()
    {
        return 'Csak amit még nem észleltem';
    }

    public function getDefault(Context $context)
    {
        return !$context->isGuest();
    }

    public function parse($input, Context $context)
    {
        return self::parseBool($input) ?? $this->getDefault($context);
    }

    public function apply(Query $query, $value, Context $context)
    {
        if ($value && !$context->isGuest()) {
            $observed = (new Query())
                ->select('l.sky_object_id')
                ->from(['l' => 'observation_sky_objects'])
                ->innerJoin(['ob' => 'observations'], 'ob.id = l.observation_id')
                ->where(['ob.observer_id' => $context->userId, 'l.status' => ObservationSkyObject::VISIBLE_STATUSES]);

            $query->andWhere(['not in', 'o.id', $observed]);
        }
    }
}

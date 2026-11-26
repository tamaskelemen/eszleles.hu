<?php

namespace app\components\recommender\filters;

use app\components\recommender\Context;
use app\components\recommender\Filter;
use app\models\ObservationSkyObject;
use yii\db\Query;

/**
 * Prefers the well known objects: the Messier and Caldwell ones, the bright ones and the often observed ones.
 * Guests always get this.
 */
class PopularFilter extends Filter
{
    public function getKey()
    {
        return 'nepszeru';
    }

    public function getLabel()
    {
        return 'Ismertebbek előnyben';
    }

    public function getDefault(Context $context)
    {
        return $context->isGuest();
    }

    public function parse($input, Context $context)
    {
        return self::parseBool($input) ?? $this->getDefault($context);
    }

    public function apply(Query $query, $value, Context $context)
    {
    }

    public function getWeight($value, Context $context)
    {
        if (!$value) {
            return null;
        }

        $statuses = "'" . implode("','", ObservationSkyObject::VISIBLE_STATUSES) . "'";

        return '(1'
            . ' + 8 * (o.messier IS NOT NULL)::int'
            . " + 4 * ('C' = ANY(o.catalogs))::int"
            . ' + GREATEST(0, 10 - COALESCE(o.magnitude, 12))'
            . " + LEAST(10, (SELECT COUNT(*) FROM observation_sky_objects l WHERE l.sky_object_id = o.id AND l.status IN ($statuses)))"
            . ')';
    }
}

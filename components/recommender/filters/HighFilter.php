<?php

namespace app\components\recommender\filters;

use app\components\recommender\Context;
use app\components\recommender\Filter;
use yii\db\Query;

/**
 * Prefers the objects high in the sky, where the air is the least in the way.
 */
class HighFilter extends Filter
{
    public function getKey()
    {
        return 'magasabb';
    }

    public function getLabel()
    {
        return 'Magasabban állók előnyben';
    }

    public function getDefault(Context $context)
    {
        return false;
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
        // an object in the zenith is about eight times as likely as one 20° high
        return $value ? 'power(GREATEST(0.05, sind(' . $context->altitudeSql() . ')), 2)' : null;
    }
}

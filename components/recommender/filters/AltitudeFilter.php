<?php

namespace app\components\recommender\filters;

use app\components\recommender\Context;
use yii\db\Query;

/**
 * Objects high enough above the horizon right now.
 */
class AltitudeFilter extends OptionFilter
{
    public function getKey()
    {
        return 'magassag';
    }

    public function getLabel()
    {
        return 'Legalább ilyen magasan';
    }

    public function getOptions()
    {
        return [0 => 'a horizont felett', 10 => '10°', 15 => '15°', 20 => '20°', 30 => '30°', 45 => '45°', 60 => '60°'];
    }

    public function getDefault(Context $context)
    {
        return 20;
    }

    public function apply(Query $query, $value, Context $context)
    {
        $query->andWhere($context->altitudeSql() . ' >= ' . $this->param('min'), [$this->param('min') => $value]);
    }
}

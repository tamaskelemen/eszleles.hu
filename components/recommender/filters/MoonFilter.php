<?php

namespace app\components\recommender\filters;

use app\components\recommender\Context;
use yii\db\Query;

/**
 * Objects far enough from the Moon, if it is above the horizon.
 */
class MoonFilter extends OptionFilter
{
    public function getKey()
    {
        return 'hold';
    }

    public function getLabel()
    {
        return 'Távolság a Holdtól';
    }

    public function getOptions()
    {
        return [0 => 'nem számít', 10 => 'legalább 10°', 20 => 'legalább 20°', 30 => 'legalább 30°', 45 => 'legalább 45°', 60 => 'legalább 60°'];
    }

    public function getDefault(Context $context)
    {
        return 20;
    }

    public function apply(Query $query, $value, Context $context)
    {
        if ($value > 0 && $this->isMoonUp($context)) {
            $query->andWhere(
                $context->distanceSql($context->moon['ra'], $context->moon['dec']) . ' >= ' . $this->param('min'),
                [$this->param('min') => $value]
            );
        }
    }

    public function getViewParams(Context $context)
    {
        return ['isMoonUp' => $this->isMoonUp($context), 'moon' => $context->moon];
    }

    private function isMoonUp(Context $context)
    {
        return $context->moon['altitude'] > 0;
    }
}

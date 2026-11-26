<?php

namespace app\components\recommender\filters;

use app\components\recommender\Context;
use yii\db\Query;

/**
 * Objects at least this big. The ones of unknown size are kept.
 */
class SizeFilter extends OptionFilter
{
    public function getKey()
    {
        return 'meret';
    }

    public function getLabel()
    {
        return 'Legkisebb látszó méret';
    }

    public function getOptions()
    {
        return ['0' => 'bármekkora', '0.5' => '0,5′', '1' => '1′', '2' => '2′', '5' => '5′', '10' => '10′', '30' => '30′'];
    }

    public function getDefault(Context $context)
    {
        // small, but bright planetary nebulae are still good targets
        return '0.5';
    }

    public function apply(Query $query, $value, Context $context)
    {
        if ((float)$value > 0) {
            $query->andWhere('o.size IS NULL OR o.size >= ' . $this->param('min'), [$this->param('min') => (float)$value]);
        }
    }
}

<?php

namespace app\components\recommender\filters;

use app\components\recommender\Context;
use app\components\recommender\Filter;
use app\components\SkyPrograms;
use yii\db\Query;

/**
 * Objects of an observing program.
 */
class ProgramFilter extends Filter
{
    public function getKey()
    {
        return 'program';
    }

    public function getLabel()
    {
        return 'Észlelőprogram';
    }

    public function getDefault(Context $context)
    {
        return '';
    }

    public function parse($input, Context $context)
    {
        return is_string($input) && isset(SkyPrograms::getAll()[$input]) ? $input : '';
    }

    public function apply(Query $query, $value, Context $context)
    {
        if ($value !== '') {
            $query->andWhere(
                'EXISTS (SELECT 1 FROM sky_list_items i WHERE i.sky_object_id = o.id AND i.list = ' . $this->param('list') . ')',
                [$this->param('list') => $value]
            );
        }
    }

    public function getViewParams(Context $context)
    {
        return ['programs' => array_map(function ($program) {
            return $program['name'];
        }, SkyPrograms::getAll())];
    }
}

<?php

namespace app\components\recommender\filters;

use app\components\recommender\Context;
use app\components\recommender\Filter;
use yii\db\Query;

/**
 * Objects of any of the chosen kinds.
 */
class TypeFilter extends Filter
{
    /** Kind => label and types of OpenNGC, a type can be in more kinds */
    const KINDS = [
        'galaxis' => ['galaxis', ['G', 'GPair', 'GTrpl', 'GGroup']],
        'galaxishalmaz' => ['galaxishalmaz', ['GCluster']],
        'nyilthalmaz' => ['nyílthalmaz', ['OCl', 'Cl+N']],
        'gombhalmaz' => ['gömbhalmaz', ['GCl']],
        'planetaris-kod' => ['planetáris köd', ['PN']],
        'fenyes-kod' => ['fényes köd', ['HII', 'EmN', 'RfN', 'Neb', 'Cl+N']],
        'sotet-kod' => ['sötét köd', ['DrkN']],
        'szupernova-maradvany' => ['szupernóva-maradvány', ['SNR']],
    ];

    public function getKey()
    {
        return 'tipus';
    }

    public function getLabel()
    {
        return 'Típus';
    }

    public function getDefault(Context $context)
    {
        return array_keys(self::KINDS);
    }

    public function parse($input, Context $context)
    {
        return $input === null ? $this->getDefault($context) : self::parseList($input, array_keys(self::KINDS));
    }

    public function apply(Query $query, $value, Context $context)
    {
        // none is chosen: any of them
        if (!empty($value)) {
            $types = array_merge(...array_map(function ($kind) {
                return self::KINDS[$kind][1];
            }, $value));

            $query->andWhere(['o.type' => array_values(array_unique($types))]);
        }
    }

    public function getViewParams(Context $context)
    {
        return ['kinds' => array_map(function ($kind) {
            return $kind[0];
        }, self::KINDS)];
    }
}

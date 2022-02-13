<?php
namespace app\models\observations;

use app\models\Observe;

class Eclipse extends Observe
{
    /**
     * @return array|string[]
     */
    public static function getVisibleAttributes()
    {
        return array_merge(
            parent::getVisibleAttributes(),
            [
                'duration',
                'coverage',
                //list here additional fields you would like to appear on view
            ]
        );
    }
}
<?php
namespace app\models\observations;

use app\models\Observe;

class Planet extends Observe
{
    /**
     * @return array|string[]
     */
    public static function getVisibleAttributes()
    {
        return array_merge(
            parent::getVisibleAttributes(),
            [
                //list here additional fields you would like to appear on view
            ]
        );
    }
}
<?php
namespace app\models\observations;

use app\models\Observe;

class Meteor extends Observe
{
    /**
     * @return array|string[]
     */
    public static function getVisibleAttributes()
    {
        return array_merge(
            parent::getVisibleAttributes(),
            [
                'meteor_membership',
                'brightness',
                'color',
                //list here additional fields you would like to appear on view
            ]
        );
    }
}
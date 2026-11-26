<?php
namespace app\components;

use Yii;

/**
 * Feature flags: parts of the site that are built but not shown yet. They are switched in `features` of
 * config/params.php.
 */
class Features
{
    /** User-made observing plans ("Észlelési tervek"), see README-eszlelesi-tervek.md */
    const OBSERVING_PLANS = 'observingPlans';

    /**
     * @param string $feature one of the constants
     * @return bool
     */
    public static function isEnabled(string $feature)
    {
        return !empty(Yii::$app->params['features'][$feature]);
    }
}

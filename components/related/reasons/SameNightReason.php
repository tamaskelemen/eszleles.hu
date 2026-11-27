<?php

namespace app\components\related\reasons;

/**
 * A day apart is still the same night: the date is the evening or the morning after.
 */
class SameNightReason extends CloseInTimeReason
{
    public function getLabel()
    {
        return 'Ugyanazon az éjszakán';
    }

    public function getWeight()
    {
        return 2;
    }

    public function getMaxDays()
    {
        return 1;
    }
}

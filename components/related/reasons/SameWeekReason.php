<?php

namespace app\components\related\reasons;

class SameWeekReason extends CloseInTimeReason
{
    public function getLabel()
    {
        return 'Egy héten belül';
    }

    public function getWeight()
    {
        return 1;
    }

    public function getMinDays()
    {
        return 2;
    }

    public function getMaxDays()
    {
        return 7;
    }
}

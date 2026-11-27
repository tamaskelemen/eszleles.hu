<?php

namespace app\components\related\reasons;

use app\components\related\Reason;
use app\models\Observe;

/**
 * Observed at most getMaxDays() days apart, but more than the closer reason's days (that one is shown instead).
 */
abstract class CloseInTimeReason extends Reason
{
    /**
     * @return int
     */
    abstract public function getMaxDays();

    /**
     * @return int
     */
    public function getMinDays()
    {
        return 0;
    }

    public function getColumns()
    {
        return ['date'];
    }

    public function appliesTo(Observe $own)
    {
        return (bool)$own->date;
    }

    public function matches(Observe $own, array $candidate)
    {
        if (!$candidate['date']) {
            return false;
        }
        $days = (int)round(abs(strtotime($own->date) - strtotime($candidate['date'])) / 86400);
        return $days >= $this->getMinDays() && $days <= $this->getMaxDays();
    }
}

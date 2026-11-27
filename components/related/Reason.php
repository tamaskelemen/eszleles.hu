<?php

namespace app\components\related;

use app\models\Observe;

/**
 * Why an observation is related to the shown one. To add one, write a subclass and list it in
 * RelatedObservations::REASONS.
 *
 * The candidates are the observations of the same type, as arrays of the columns the reasons asked for.
 */
abstract class Reason
{
    /**
     * @return string shown on the card, e.g. "Hasonló kamera"
     */
    abstract public function getLabel();

    /**
     * @return int how much the reason ranks a candidate higher, the candidates with the largest sum are shown
     */
    abstract public function getWeight();

    /**
     * @param Observe $own the shown observation
     * @param array $candidate
     * @return bool
     */
    abstract public function matches(Observe $own, array $candidate);

    /**
     * @return string[] columns of the observations matches() reads from a candidate
     */
    public function getColumns()
    {
        return [];
    }

    /**
     * Whether the reason can match anything for the shown observation (e.g. it has a camera at all). Only these are
     * prepared and asked.
     *
     * @param Observe $own
     * @return bool
     */
    public function appliesTo(Observe $own)
    {
        return true;
    }

    /**
     * Runs once before matches(), for loading what is needed for all the candidates in one query.
     *
     * @param Observe $own
     */
    public function prepare(Observe $own)
    {
    }

    /**
     * @return bool whether the candidate is of the same object; if none of the shown ones is, a random observation is
     * shown too
     */
    public function isSameObject()
    {
        return false;
    }
}

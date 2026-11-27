<?php

namespace app\components\related\reasons;

use app\components\related\Reason;
use app\components\related\Text;
use app\models\Observe;

/**
 * From a nearby place. The place is a free text, so its first words are compared: "Budapest, Normafa" and
 * "budapest XII. ker." are nearby.
 */
class NearbyReason extends Reason
{
    /** Their first word says nothing about where the observation was made */
    const NOT_A_PLACE = ['otthon', 'itthon', 'haz', 'hazunk', 'kert', 'kertbol', 'kertunk', 'erkely', 'erkelyrol',
        'udvar', 'magyarorszag', 'hungary', 'ismeretlen', 'nincs', 'teszt'];

    public function getLabel()
    {
        return 'Közeli helyszín';
    }

    public function getWeight()
    {
        return 2;
    }

    public function getColumns()
    {
        return ['location'];
    }

    public function appliesTo(Observe $own)
    {
        return $this->place($own->location) !== null;
    }

    public function matches(Observe $own, array $candidate)
    {
        $theirs = $this->place($candidate['location']);
        return $theirs !== null && $theirs === $this->place($own->location);
    }

    /**
     * @param string|null $location
     * @return string|null
     */
    private function place($location)
    {
        $place = Text::firstWord($location);
        return mb_strlen($place) < 3 || in_array($place, self::NOT_A_PLACE, true) ? null : $place;
    }
}

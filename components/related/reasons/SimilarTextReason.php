<?php

namespace app\components\related\reasons;

use app\components\related\Reason;
use app\components\related\Text;
use app\models\Observe;

/**
 * A free text column is about the same: case, accents, spaces and punctuation do not matter, and the rest has to be similar
 * enough (a typo or a missing letter is fine).
 */
abstract class SimilarTextReason extends Reason
{
    /** Texts at least this similar match */
    const MIN_SIMILARITY = 0.9;

    /** Texts meaning there is nothing, they do not match each other */
    const NOTHING = ['nincs', 'nem', 'semmi', 'ismeretlen', 'n a', 'na', 'egyeb', 'teszt', 'asd'];

    /**
     * @return string
     */
    abstract public function getColumn();

    public function getWeight()
    {
        return 1;
    }

    public function getColumns()
    {
        return [$this->getColumn()];
    }

    public function appliesTo(Observe $own)
    {
        return $this->value($own->{$this->getColumn()}) !== null;
    }

    public function matches(Observe $own, array $candidate)
    {
        $theirs = $this->value($candidate[$this->getColumn()]);
        if ($theirs === null) {
            return false;
        }
        // the spaces do not count: "Sky-Watcher EQ5" is "skywatcher eq-5"
        $ours = str_replace(' ', '', $this->value($own->{$this->getColumn()}));
        return Text::similarity($ours, str_replace(' ', '', $theirs)) >= static::MIN_SIMILARITY;
    }

    /**
     * @param string|null $text
     * @return string|null normalized, null if it says nothing
     */
    protected function value($text)
    {
        $text = Text::normalize($text);
        return mb_strlen($text) < 2 || in_array($text, self::NOTHING, true) ? null : $text;
    }
}

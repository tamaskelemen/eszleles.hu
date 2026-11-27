<?php

namespace app\components\related\reasons;

use app\models\Observe;

/**
 * Meteors of the same shower: "Perseidák", "perseida" and "Perseida meteorraj" are the same.
 */
class SameShowerReason extends SimilarTextReason
{
    /** Words around the name of the shower */
    const NOT_THE_NAME = ['meteorraj', 'meteorrajok', 'raj', 'meteor', 'meteorok', 'meteorai', 'a', 'az'];

    /** Not a shower: these meteors do not belong together */
    const NOT_A_SHOWER = ['sporadikus', 'sporadic', 'szporadikus', 'egyedi', 'tuzgomb'];

    public function getLabel()
    {
        return 'Ugyanaz a meteorraj';
    }

    public function getColumn()
    {
        return 'meteor_membership';
    }

    public function getWeight()
    {
        return 4;
    }

    public function isSameObject()
    {
        return true;
    }

    public function appliesTo(Observe $own)
    {
        return $own->type === Observe::TYPE_METEOR && parent::appliesTo($own);
    }

    protected function value($text)
    {
        $text = parent::value($text);
        if ($text === null) {
            return null;
        }

        $words = [];
        foreach (explode(' ', $text) as $word) {
            if (!in_array($word, self::NOT_THE_NAME, true)) {
                // the plural: "perseidak" is "perseida"
                $words[] = preg_replace('/(?<=[ae])k$/', '', $word);
            }
        }
        $text = implode(' ', $words);

        return $text === '' || in_array($text, self::NOT_A_SHOWER, true) ? null : $text;
    }
}

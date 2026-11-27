<?php

namespace app\components\related\reasons;

class SimilarMechanicsReason extends SimilarTextReason
{
    public function getLabel()
    {
        return 'Hasonló mechanika';
    }

    public function getColumn()
    {
        return 'mechanics';
    }
}

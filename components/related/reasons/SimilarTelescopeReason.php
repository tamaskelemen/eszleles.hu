<?php

namespace app\components\related\reasons;

class SimilarTelescopeReason extends SimilarTextReason
{
    public function getLabel()
    {
        return 'Hasonló távcső';
    }

    public function getColumn()
    {
        return 'telescope';
    }
}

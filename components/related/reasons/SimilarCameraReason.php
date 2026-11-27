<?php

namespace app\components\related\reasons;

class SimilarCameraReason extends SimilarTextReason
{
    public function getLabel()
    {
        return 'Hasonló kamera';
    }

    public function getColumn()
    {
        return 'camera';
    }
}

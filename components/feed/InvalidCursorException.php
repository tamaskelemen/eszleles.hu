<?php

namespace app\components\feed;

use yii\web\BadRequestHttpException;

class InvalidCursorException extends BadRequestHttpException
{
    public function __construct($message = 'Érvénytelen lapozási pozíció.')
    {
        parent::__construct($message);
    }
}

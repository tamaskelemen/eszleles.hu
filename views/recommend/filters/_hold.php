<?php
/* @var $this yii\web\View */
/* @var $filter app\components\recommender\filters\OptionFilter */
/* @var $value int */
/* @var $isMoonUp bool */
/* @var $moon array */

echo $this->render('_select', [
    'filter' => $filter,
    'value' => $value,
    'hint' => $isMoonUp
        ? 'A Hold most fent van (' . round($moon['fraction'] * 100) . '%-os).'
        : 'A Hold most a horizont alatt van, nem zavar.',
]);

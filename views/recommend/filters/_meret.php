<?php
/* @var $this yii\web\View */
/* @var $filter app\components\recommender\filters\OptionFilter */
/* @var $value string */

echo $this->render('_select', ['filter' => $filter, 'value' => $value, 'hint' => 'Az ismeretlen méretűek is jöhetnek.']);

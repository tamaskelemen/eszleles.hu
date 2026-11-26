<?php
/* @var $this yii\web\View */
/* @var $filter app\components\recommender\filters\OptionFilter */
/* @var $value int */

echo $this->render('_select', ['filter' => $filter, 'value' => $value, 'hint' => 'Mostani magasság a horizont felett.']);

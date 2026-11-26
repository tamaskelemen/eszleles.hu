<?php
/* @var $this yii\web\View */
/* @var $filter app\components\recommender\Filter */
/* @var $value string */
/* @var $programs array names indexed by key */

echo $this->render('_select', ['filter' => $filter, 'value' => $value, 'options' => ['' => 'bármelyik objektum'] + $programs]);

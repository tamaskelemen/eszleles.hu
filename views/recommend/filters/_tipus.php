<?php
/* @var $this yii\web\View */
/* @var $filter app\components\recommender\Filter */
/* @var $value string[] */
/* @var $kinds array labels indexed by kind */

echo $this->render('_checklist', ['filter' => $filter, 'value' => $value, 'items' => $kinds]);

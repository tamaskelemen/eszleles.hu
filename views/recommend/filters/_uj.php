<?php
/* @var $this yii\web\View */
/* @var $filter app\components\recommender\Filter */
/* @var $value bool */

echo $this->render('_checkbox', ['filter' => $filter, 'value' => $value, 'hint' => 'A saját feltöltött észleléseid alapján.']);

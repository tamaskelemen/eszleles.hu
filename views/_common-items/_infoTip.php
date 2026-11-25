<?php

use app\assets\FontAwesomeAsset;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $label string what the icon is about, for screen readers */
/* @var $text string */

// the admin layout does not have it
FontAwesomeAsset::register($this);

echo Html::button('<i class="fa fa-info-circle" aria-hidden="true"></i>', [
    'class' => 'info-tip',
    'title' => $text,
    'aria-label' => $label,
    'data-toggle' => 'tooltip',
    'data-template' => '<div class="tooltip tooltip--text" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
]);

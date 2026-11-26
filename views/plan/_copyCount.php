<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $plan app\models\ObservingPlan */

// only the public plans can be copied by the others, an old count stays visible after it is made private
if ($plan->is_public || $plan->copy_count > 0) {
    $text = $plan->copy_count > 0
        ? Yii::$app->formatter->asInteger($plan->copy_count) . ' alkalommal másolták a saját terveik közé'
        : 'Még senki nem másolta a saját tervei közé';

    echo Html::button('<i class="fa fa-bar-chart" aria-hidden="true"></i>', [
        'class' => 'info-tip plan-stat',
        'title' => $text,
        'aria-label' => $text,
        'data-toggle' => 'tooltip',
    ]);
}

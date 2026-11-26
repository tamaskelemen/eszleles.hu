<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\forms\ObservingPlanForm */

$this->title = 'Új észlelési terv';
$this->params['breadcrumbs'][] = ['label' => 'Észlelőprogramok', 'url' => ['/program/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container">
    <h1><?= Html::encode($this->title) ?></h1>

    <p class="skymap-lead">
        Állítsd össze a saját listádat a megfigyelni kívánt objektumokból. Az számít késznek, amelyikről van azonosított észlelésed.
    </p>

    <?= $this->render('_form', ['model' => $model]) ?>
</div>

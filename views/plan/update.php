<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\forms\ObservingPlanForm */

$plan = $model->getPlan();

$this->title = 'Észlelési terv módosítása';
$this->params['breadcrumbs'][] = ['label' => 'Észlelőprogramok', 'url' => ['/program/index']];
$this->params['breadcrumbs'][] = ['label' => $plan->getOldAttribute('name'), 'url' => ['view', 'id' => $plan->id]];
$this->params['breadcrumbs'][] = 'Módosítás';
?>
<div class="container">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', ['model' => $model]) ?>

    <hr>

    <?= Html::a('Terv törlése', ['delete', 'id' => $plan->id], [
        'class' => 'btn btn-outline-danger',
        'data' => [
            'confirm' => 'Biztosan törlöd az észlelési tervet? Az észleléseid megmaradnak.',
            'method' => 'post',
        ],
    ]) ?>
</div>

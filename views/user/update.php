<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = 'Felhasználói adatok módosítása';
$this->params['breadcrumbs'][] = ['label' => 'Felhasználók', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['profile', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Módosítás';
?>
<div class="users-update">
    <div class="text-center">
        <h1>
            <?= Html::encode($this->title) ?>
        </h1>
    </div>

    <div class="container">
        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>
    </div>

</div>

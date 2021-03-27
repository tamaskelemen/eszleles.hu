<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = 'Update Users: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Felhasználók', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['profile', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Módosítás';
?>
<div class="users-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Observe */

$this->title = 'Észlelés módosítása: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Hold észlelések', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Módosítás';
?>
<div class="observe-update">

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

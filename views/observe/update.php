<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\models\Observe */

$this->title = 'Észlelés módosítása: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Észlelések', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Módosítás';
?>
<div class="observe-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

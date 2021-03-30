<?php

use yii\helpers\Html;
use app\models\Observe;

/* @var $this yii\web\View */
/* @var $model app\models\Observe */

$typeName = Observe::getTypeName($model->type);

$this->title = 'Észlelés módosítása: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => $typeName . ' észlelések', 'url' => ['index']];
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
        <?= $this->render("../{$model->type}/_form", [
            'model' => $model,
        ]) ?>
    </div>
</div>

<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\forms\AbstractObserveForm */

$typeName = \app\models\Observe::getTypeName($model->type);

$this->title = $typeName . ' észlelés feltöltése';
$this->params['breadcrumbs'][] = ['label' => $typeName . ' észlelések', 'url' => ["/{$typeName}/index"]];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="observe-create">
    <div class="text-center">
        <h1>
            <?= Html::encode($this->title) ?>
        </h1>
    </div>

    <div class="container">
        <?= $this->render('../' . $model->type . '/_form', [
            'model' => $model,
        ]) ?>
    </div>
</div>

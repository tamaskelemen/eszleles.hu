<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\forms\PlanetForm */

$this->title = 'Bolygó észlelés feltöltése';
$this->params['breadcrumbs'][] = ['label' => 'Bolygó észlelések', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="observe-create">
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

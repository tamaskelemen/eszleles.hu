<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\forms\DeepSkyForm */

$this->title = 'Bolygó észlelés feltöltése';
$this->params['breadcrumbs'][] = ['label' => 'Bolygó észlelések', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="observe-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

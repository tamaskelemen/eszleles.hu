<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Observe */

$this->title = 'Észlelés feltöltése';
$this->params['breadcrumbs'][] = ['label' => 'Észlelések', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="observe-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

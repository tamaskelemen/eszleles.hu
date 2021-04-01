<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\BugReport */

$this->title = 'Create Bug Report';
$this->params['breadcrumbs'][] = ['label' => 'Bug Reports', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container bug-report-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

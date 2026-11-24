<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ActivitySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Tevékenységnapló';
?>
<div class="admin-index container pt-4">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_activities', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
        'showUser' => true,
    ]) ?>
</div>

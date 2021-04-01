<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\BugReport */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Bug Reports', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

?>
<div class="bug-report-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'user_id',
            'title:ntext',
            'description:ntext',
            [
                'attribute' => 'type',
                'value' => function ($model) {
                    return \app\models\BugReport::getTypes()[$model->type];
                }
            ],
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    return \app\models\BugReport::getTypes()[$model->status];
                }
            ],
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>

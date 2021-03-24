<?php

use app\models\Observe;
use yii\helpers\Html;
use yii\grid\GridView;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ObserveSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Észlelések';
$this->params['breadcrumbs'][] = $this->title;

$columns = [
    ['class' => 'yii\grid\SerialColumn'],
    [
        'attribute' => 'object_name',
        'format' => 'raw',
        'value' => function ($model) {
            return Html::a($model->object_name, $model->getViewUrl());
        }
    ],
    [
        'attribute' => 'type',
        'filter' => Observe::getAllTypes(),
        'value' => function ($model) {
            return Observe::getTypeName($model->type);
        }
    ],
    [
        'attribute' => 'date',
        'filter' => DatePicker::widget([
            'model' => $searchModel,
            'attribute' => 'date',
            'pluginOptions' => [
                'format' => 'yyyy-mm-dd',
            ]
        ]),
        'format' => 'html',
    ],
    [
            'attribute' => 'observer',
            'value' => 'observer.name'
    ],
];

if (!Yii::$app->user->isGuest) {
    $isAdmin = Yii::$app->user->identity->isAdmin();
    $template = ($isAdmin ? '{delete}' : '');

    $columns[] = [
        'class' => 'yii\grid\ActionColumn',
        'template' => $template,
    ];

}

?>
<div class="observe-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $columns,
        'tableOptions' => ['class' => 'table table-striped'],
    ]); ?>
</div>

<?php

use app\models\Observe;
use yii\helpers\Html;
use yii\grid\GridView;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ObserveSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Összes észlelés';
$this->params['breadcrumbs'][] = $this->title;

$columns = [
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
            'options' => [
                'autocomplete' => 'off'
            ],
            'pluginOptions' => [
                'format' => 'yyyy-mm-dd',
            ]
        ]),
        'format' => 'html',
    ],
    [
            'attribute' => 'observer',
            'value' => 'observer.name',
            'label' => 'Észlelő'
    ],
    'telescope'
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
    <div class="text-center">
        <h1><?= Html::encode($this->title) ?></h1>
<!--        --><?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>

    <div class="container">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $columns,
        'tableOptions' => ['class' => 'table table-striped'],
        'pager' => [
            'firstPageLabel' => 'Első',
            'lastPageLabel'  => 'Utolsó'
        ],
        'summary' => '{begin, number}-{end, number}</b> az összesen <b>{totalCount, number}</b> észlelésből'
    ]); ?>
    </div>
</div>

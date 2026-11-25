<?php

use app\models\Observe;
use yii\helpers\Html;
use yii\grid\GridView;
use app\widgets\DatePicker;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ObserveSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Összes észlelés';
$this->params['breadcrumbs'][] = $this->title;

$columns = [
    [
        'format' => 'raw',
        'contentOptions' => ['class' => 'grid-thumb'],
        'value' => function ($model) {
            return Html::a(
                Html::img($model->getThumbnailPath(), ['alt' => '', 'loading' => 'lazy', 'width' => 300, 'height' => 200]),
                $model->getViewUrl(),
                ['tabindex' => -1]
            );
        }
    ],
    [
        'attribute' => 'object_name',
        'format' => 'raw',
        'value' => function ($model) {
            return Html::a(Html::encode($model->object_name), $model->getViewUrl());
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
<div class="observe-search-page container">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_search', ['model' => $searchModel]) ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $columns,
        'tableOptions' => ['class' => 'table'],
        'layout' => "{summary}\n<div class=\"table-responsive\">{items}</div>\n{pager}",
        'pager' => [
            'firstPageLabel' => 'Első',
            'lastPageLabel'  => 'Utolsó'
        ],
        'summary' => '{begin, number}-{end, number}</b> az összesen <b>{totalCount, number}</b> észlelésből'
    ]); ?>
</div>

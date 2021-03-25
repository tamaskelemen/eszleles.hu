<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\date\DatePicker;
use yii\widgets\ListView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ObserveSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Észlelések';
$this->params['breadcrumbs'][] = $this->title;

$columns = [
    ['class' => 'yii\grid\SerialColumn'],
    'catalog_number',
    'telescope',
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
    [
        'attribute' => 'description',
        'value' => function ($model) {
            return substr($model->description, 0, 20);
        }
    ],
];

if (!Yii::$app->user->isGuest) {
    $isAdmin = Yii::$app->user->identity->isAdmin();
    $template = '{view} {update}' . ($isAdmin ? '{delete}' : '');

    $columns[] = [
        'class' => 'yii\grid\ActionColumn',
        'template' => $template
    ];

}

?>
<div class="observe-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <!--    --><?php // echo $this->render('_search', ['model' => $searchModel]); ?>



    <?= ListView::widget([
        'dataProvider' => $dataProvider,
        'itemView' => '../_common-items/_listItem'
    ])

    ?>




    <!--    --><?php //GridView::widget([
    //        'dataProvider' => $dataProvider,
    //        'filterModel' => $searchModel,
    //        'columns' => $columns
    //    ]); ?>
</div>

<?php

use yii\helpers\Html;
use app\widgets\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\BugReportSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Bug Reports';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bug-report-index container">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Bug Report', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
//            'id',
//            'user_id',
            [
                'attribute' => 'title',
                'format' => 'ntext',
                'mobile' => GridView::MOBILE_TITLE,
            ],
//            'description:ntext',
            [
                'attribute' => 'status',
                'mobile' => GridView::MOBILE_META,
                'value' => function ($model) {
                    return \app\models\BugReport::getStatuses()[$model->status];
                }
            ],
            [
                'attribute' => 'type',
                'mobile' => GridView::MOBILE_META,
                'value' => function ($model) {
                    return \app\models\BugReport::getTypes()[$model->type];
                }
            ],
            //'status',
            //'created_at',
            //'updated_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>

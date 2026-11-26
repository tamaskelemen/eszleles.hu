<?php

use yii\helpers\Html;
use app\widgets\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\CollectionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Listák';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="collection-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <div class="container">
        <h1><?= Html::encode($this->title) ?></h1>

        <p>
            <?= Html::a('Create Collection', ['create'], ['class' => 'btn btn-success']) ?>
        </p>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'name',
                    'mobile' => GridView::MOBILE_TITLE,
                ],
                'description',
                [
                    'attribute' => 'user_id',
                    'mobile' => GridView::MOBILE_META,
                ],
                //'is_public:boolean',
                //'is_open:boolean',
                //'from_date',
                //'to_date',
                //'created_at',
                //'edited_at',

                ['class' => 'yii\grid\ActionColumn'],
            ],
        ]); ?>
    </div>
</div>

<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Felhasználók';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="users-index container">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
//            ['class' => 'yii\grid\SerialColumn'],

//            'id',
            [
                'attribute' => 'email',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a($model->email, Url::to(['/user/profile', 'id' => $model->id]));
                }
            ],
            'name',
//            'last_login',
//            'password_hash',
            //'password_reset_token',
            //'auth_key',
            //'status',
            //'terms',
            //'newsletter:boolean',
            //'created_at',

//            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>

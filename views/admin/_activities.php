<?php

use app\models\Activity;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ActivitySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $showUser bool */
?>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'options' => ['class' => 'grid-view table-responsive'],
    'emptyText' => 'Nincs megjeleníthető tevékenység.',
    'columns' => [
        'created_at',
        [
            'attribute' => 'userName',
            'label' => $searchModel->getAttributeLabel('userName'),
            'format' => 'raw',
            'visible' => $showUser,
            'value' => function (Activity $model) {
                return Html::a(Html::encode($model->user->name ?: $model->user->email), ['/admin/user', 'id' => $model->user_id])
                    . '<br><small class="text-muted">' . Html::encode($model->user->email) . '</small>';
            },
        ],
        [
            'attribute' => 'type',
            'filter' => Activity::getAllTypes(),
            'value' => 'typeName',
            'contentOptions' => function (Activity $model) {
                return $model->type === Activity::TYPE_LOGIN_FAILED ? ['class' => 'text-danger'] : [];
            },
        ],
        'ip',
        [
            'attribute' => 'user_agent',
            'contentOptions' => ['style' => 'overflow-wrap: anywhere'],
        ],
    ],
]) ?>

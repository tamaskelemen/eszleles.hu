<?php

use app\models\Activity;
use app\models\Observe;
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
        [
            'attribute' => 'observationName',
            'label' => $searchModel->getAttributeLabel('observationName'),
            'format' => 'raw',
            'value' => function (Activity $model) {
                if ($model->observation_id === null) {
                    return '';
                }

                if ($model->observation === null) {
                    return '<span class="text-muted">Törölt észlelés (#' . $model->observation_id . ')</span>';
                }

                return Html::a(Html::encode($model->observation->object_name), $model->observation->getViewUrl())
                    . '<br><small class="text-muted">' . (Observe::getAllTypes()[$model->observation->type] ?? '') . '</small>';
            },
        ],
        'ip',
        [
            'attribute' => 'user_agent',
            'contentOptions' => ['style' => 'overflow-wrap: anywhere'],
        ],
    ],
]) ?>

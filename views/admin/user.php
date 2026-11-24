<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $user app\models\User */
/* @var $searchModel app\models\ActivitySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $user->name ?: $user->email;
?>
<div class="admin-user container pt-4">
    <p><?= Html::a('← Teljes napló', ['/admin/index']) ?></p>

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $user,
        'attributes' => [
            [
                'attribute' => 'name',
                'format' => 'raw',
                'value' => Html::a(Html::encode($this->title), ['/user/profile', 'id' => $user->id]),
            ],
            'email',
            'created_at',
            'last_login',
        ],
    ]) ?>

    <h2>Tevékenységek</h2>

    <?= $this->render('_activities', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
        'showUser' => false,
    ]) ?>
</div>

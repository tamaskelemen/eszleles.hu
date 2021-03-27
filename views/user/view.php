<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $user app\models\User */

$this->title = $user->name;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-view">
    <div class="text-center">
        <h1>
            <?= Html::encode($this->title) ?>
        </h1>

        <p>
            <?= Html::a('Adatok szerkesztése', ['update', 'id' => $user->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Jelszó megváltoztatása', ['change-password', 'id' => $user->id], ['class' => 'btn btn-primary']) ?>
        </p>
    </div>

    <div class="container">
        <?= DetailView::widget([
            'model' => $user,
            'attributes' => [
                'id',
                'email:email',
                'name',
                'last_login',
                'status',
//            'terms',
                'newsletter:boolean',
                'created_at',
//            'is_admin'
            ],
        ]) ?>
    </div>
</div>

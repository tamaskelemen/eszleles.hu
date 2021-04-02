<?php
/** @var $model \app\models\Comment */

use yii\helpers\Html;
use yii\helpers\Url;
$user = $model->user;
?>

<div>
    <p><?= Html::a($user->name, Url::toRoute(["/user/profile", "id"=> $user->id])) ?> - <?= $model->created_at ?></p>

    <p> <?= Html::encode($model->comment) ?></p>
</div>

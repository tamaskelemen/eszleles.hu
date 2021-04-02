<?php
/** @var $comment \app\models\Comment */

use yii\helpers\Html;
use yii\helpers\Url;
$user = $comment->user;
?>

<div>
    <p><?= Html::a($user->name, Url::toRoute(["/user/profile", "id"=> $user->id])) ?> - <?= $comment->created_at ?></p>

    <p> <?= Html::encode($comment->comment) ?></p>
</div>

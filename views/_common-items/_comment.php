<?php
/** @var $comment \app\models\Comment */

use yii\helpers\Html;
use yii\helpers\Url;

?>

<div>
    <p><?= Html::a($comment->user->name, Url::toRoute(["/user/profile", "id"=> $comment->user->id])) ?> - <?= $comment->created_at ?></p>

    <p> <?= Html::encode($comment->comment) ?></p>
</div>

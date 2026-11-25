<?php

use yii\helpers\Html;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $notification app\models\Notification */

$comment = $notification->comment;
$observation = $comment->observation;
?>
<img src="<?= $observation->getThumbnailPath() ?>" alt="" width="64" height="48">
<span>
    <span class="notification__text">
        <b><?= Html::encode($comment->user->name) ?></b>
        <?= $notification->getTypeName() ?>
        <b><?= Html::encode($observation->object_name) ?></b>
    </span>
    <span class="notification__comment"><?= Html::encode(StringHelper::truncate($comment->comment, 80)) ?></span>
    <span class="notification__time"><?= strip_tags(Yii::$app->formatter->asTimeAgo($notification->created_at)) ?></span>
</span>

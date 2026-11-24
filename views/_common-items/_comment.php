<?php
/** @var $model \app\models\Comment */
/** @var $replyFormId string|null the form opened by the reply button, null if there is no button */

use yii\helpers\Html;
use yii\helpers\Url;
$user = $model->user;
?>

<div class="comment-item" id="hozzaszolas-<?= $model->id ?>">
    <p class="comment-byline"><?= Html::a($user->name, Url::toRoute(["/user/profile", "id"=> $user->id])) ?> <?= Yii::$app->formatter->asTimeAgo($model->created_at) ?></p>

    <p><?= nl2br(Html::encode($model->comment)) ?></p>

    <?php if ($replyFormId !== null) { ?>
        <button class="btn btn-outline reply-button" type="button" aria-expanded="false" aria-controls="<?= $replyFormId ?>">
            <i class="fa fa-pencil" aria-hidden="true"></i>
            Válasz
        </button>
    <?php } ?>
</div>

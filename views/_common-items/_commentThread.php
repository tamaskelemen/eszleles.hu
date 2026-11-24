<?php
/** @var $model \app\models\Comment a first level comment */

use app\models\forms\CommentForm;

$replies = $model->replies;
$lastReply = end($replies);
$canReply = !Yii::$app->user->isGuest;
$replyFormId = 'valasz-' . $model->id;
?>
<div class="comment-thread">
    <?= $this->render('_comment', ['model' => $model, 'replyFormId' => $canReply ? $replyFormId : null]) ?>

    <?php if (!empty($replies)) { ?>
        <div class="comment-replies">
            <?php foreach ($replies as $reply) { ?>
                <?= $this->render('_comment', ['model' => $reply, 'replyFormId' => $canReply && $reply === $lastReply ? $replyFormId : null]) ?>
            <?php } ?>
        </div>
    <?php } ?>

    <?php if ($canReply) {
        echo $this->render('_replyForm', [
            'formId' => $replyFormId,
            'replyForm' => new CommentForm(['observation_id' => $model->observation_id, 'parent_id' => $model->id]),
        ]);
    } ?>
</div>

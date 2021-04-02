<?php
/** @var $comments \app\models\Comment[] */
/** @var $commentForm \app\models\forms\CommentForm */

?>
<div>
    <h2>Hozzászólások (<?= count($comments) ?>)</h2>
    <?php
    if (!Yii::$app->user->isGuest) {
        echo $this->render('../_common-items/_commentForm',
                ['commentForm' => $commentForm]
            );
    }

    foreach ($comments as $comment) {
        echo $this->render('../_common-items/_comment', ['comment' => $comment]);
    }
    ?>
</div>
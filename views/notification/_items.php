<?php

use yii\helpers\Html;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $notifications app\models\Notification[] */
/* @var $hasMore bool */
?>
<?php if (empty($notifications)) { ?>
    <p class="notifications__message">Még nincs értesítésed. Itt jelenik meg, ha valaki hozzászól egy észlelésedhez, vagy válaszol egy hozzászólásodra.</p>
<?php } ?>

<?php foreach ($notifications as $notification) {
    $comment = $notification->comment;
    $observation = $comment->observation;
    $isUnseen = $notification->seen_at === null;
    ?>
    <a class="dropdown-item notification <?= $isUnseen ? 'notification--unseen' : '' ?>" href="<?= $observation->getViewUrl() . '#hozzaszolas-' . $comment->id ?>">
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
        <?php if ($isUnseen) { ?>
            <span class="notification__dot"><span class="sr-only">Olvasatlan</span></span>
        <?php } ?>
    </a>
<?php } ?>

<?php if ($hasMore) { ?>
    <button class="notifications__action" type="button" data-before="<?= end($notifications)->id ?>">Korábbiak</button>
<?php } ?>

<?php

use app\models\Notification;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $notifications app\models\Notification[] */
/* @var $hasMore bool */
?>
<?php if (empty($notifications)) { ?>
    <p class="notifications__message">Még nincs értesítésed. Itt jelenik meg, ha valaki hozzászól egy észlelésedhez, vagy válaszol egy hozzászólásodra, illetve ha új jelvényt szerzel.</p>
<?php } ?>

<?php foreach ($notifications as $notification) {
    $isUnseen = $notification->seen_at === null;
    $isBadge = $notification->type === Notification::TYPE_BADGE;
    ?>
    <a class="dropdown-item notification <?= $isUnseen ? 'notification--unseen' : '' ?>" href="<?= Html::encode($notification->getUrl()) ?>">
        <?= $this->render($isBadge ? '_badge' : '_comment', ['notification' => $notification]) ?>
        <?php if ($isUnseen) { ?>
            <span class="notification__dot"><span class="sr-only">Olvasatlan</span></span>
        <?php } ?>
    </a>
<?php } ?>

<?php if ($hasMore) { ?>
    <button class="notifications__action" type="button" data-before="<?= end($notifications)->id ?>">Korábbiak</button>
<?php } ?>

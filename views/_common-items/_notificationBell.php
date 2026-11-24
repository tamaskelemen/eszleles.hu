<?php
/** @var $this yii\web\View */

use app\assets\AppAsset;
use app\models\Notification;
use yii\helpers\Url;

$this->registerJsFile('/js/notifications.js?v=' . filemtime(Yii::getAlias('@webroot/js/notifications.js')), [
    'depends' => [AppAsset::class],
]);

$unseenCount = Notification::countUnseen(Yii::$app->user->id);
$label = $unseenCount > 0 ? "Értesítések, $unseenCount olvasatlan" : 'Értesítések';
?>
<div class="dropdown notifications" id="notifications" data-url="<?= Url::toRoute('/notification/index') ?>" data-seen-url="<?= Url::toRoute('/notification/mark-all-seen') ?>">
    <button class="notification-bell" id="notificationBell" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Értesítések" aria-label="<?= $label ?>">
        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 16.5V11a6 6 0 0 1 12 0v5.5l1.5 1.5h-15zM10 20.5a2.2 2.2 0 0 0 4 0"/></svg>
        <span class="notification-bell__count" aria-hidden="true" <?= $unseenCount > 0 ? '' : 'hidden' ?>><?= $unseenCount > 99 ? '99+' : $unseenCount ?></span>
    </button>
    <div class="dropdown-menu dropdown-menu-right notifications__menu" aria-labelledby="notificationBell">
        <div class="notifications__head">
            <h2>Értesítések</h2>
            <button class="notifications__action" id="notifications-seen" type="button" <?= $unseenCount > 0 ? '' : 'hidden' ?>>Mind olvasott</button>
        </div>
        <div class="notifications__list" aria-live="polite"></div>
    </div>
</div>

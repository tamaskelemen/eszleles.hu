<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $notification app\models\Notification */

$badge = $notification->badge;
$category = $badge->getCategoryData();
?>
<span class="notification__badge badge-mark badge-mark--<?= $badge->tier ?>">
    <?= $this->render('//_common-items/_badgeIcon', ['category' => $category, 'threshold' => $badge->threshold]) ?>
</span>
<span>
    <span class="notification__text">
        <?= $notification->getTypeName() ?>
        <b><?= Html::encode($badge->title) ?></b>
    </span>
    <span class="notification__comment"><?= Html::encode($category['name']) ?></span>
    <span class="notification__time"><?= strip_tags(Yii::$app->formatter->asTimeAgo($notification->created_at)) ?></span>
</span>

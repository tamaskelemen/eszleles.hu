<?php
/** @var $model \app\models\Comment */
/** @var $index int
 */

use yii\helpers\Html;
use yii\helpers\Url;
$user = $model->user;
?>

<div class="comment-item" id="hozzaszolas-<?= $model->id ?>">
    <p class="comment-byline"><?= Html::a($user->name, Url::toRoute(["/user/profile", "id"=> $user->id])) ?> <?= Yii::$app->formatter->asTimeAgo($model->created_at) ?></p>

    <p><?= nl2br(Html::encode($model->comment)) ?></p>
</div>

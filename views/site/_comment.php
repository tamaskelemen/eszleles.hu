<?php
/** @var $model \app\models\Comment */
/** @var $index int
 */

use yii\helpers\Html;
use yii\helpers\Url;
$user = $model->user;
?>
<div class="comment-row">
    <a class="comment-row__img" href="<?= $model->observation->getViewUrl()?>">
        <img src="<?= $model->observation->getThumbnailPath() ?>" alt="<?= Html::encode($model->observation->object_name) ?>" width="300" height="200" loading="lazy">
    </a>
    <div>
        <p class="comment-byline"><?= Html::a($user->name, Url::toRoute(["/user/profile", "id"=> $user->id])) ?> <?= Yii::$app->formatter->asTimeAgo($model->created_at) ?></p>
        <p><?= nl2br(Html::encode($model->comment)) ?></p>
    </div>
</div>

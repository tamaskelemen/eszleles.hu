<?php
/** @var $model \app\models\Comment */
/** @var $index int
 */

use yii\helpers\Html;
use yii\helpers\Url;
$user = $model->user;
?>

<div class="p-3 <?= $index % 2 == 0 ? "light-grey-background" : "" ?>">
    <p><?= Html::a($user->name, Url::toRoute(["/user/profile", "id"=> $user->id])) ?> - <?= $model->created_at ?></p>

    <p> <?= nl2br(Html::encode($model->comment)) ?></p>
</div>

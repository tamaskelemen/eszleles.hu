<?php
/** @var $model \app\models\Comment */
/** @var $index int
 */

use yii\helpers\Html;
use yii\helpers\Url;
$user = $model->user;
?>
<div class="container">
    <div class="row p-3 <?= $index % 2 == 0 ? "light-grey-background" : "" ?>">
        <div class="col-lg-10 col-sm-12">
            <p><?= Html::a($user->name, Url::toRoute(["/user/profile", "id"=> $user->id])) ?> - <?= $model->created_at ?></p>

            <p> <?= nl2br(Html::encode($model->comment)) ?></p>
        </div>

        <div class="col-lg-2 col-sm-12">
            <a href="<?= $model->observation->getViewUrl()?> ">
                <img class="img-fluid" alt="asd" src="<?= $model->observation->getThumbnailPath() ?>">
            </a>
        </div>
    </div>
</div>


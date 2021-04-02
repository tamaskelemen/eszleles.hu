<?php
/** @var $model \app\models\Observe */

use yii\helpers\Html;
use yii\helpers\Url;

?>
<div class="col-lg-3 latest-item">
    <div class="item-img-container">
        <a href="<?= Url::toRoute(['/' . $model->type . '/view', 'id' => $model->id]) ?>">
            <img class="w-100 pt-3 pb-3" src="<?= $model->getThumbnailPath() ?>" alt="">
        </a>
        <div class="latest-bottom-left"><?= $model->observer->name ?> </div>
        <div class="latest-bottom-right"><?= count($model->comments) ?> hozzászólás </div>
    </div>

    <h3 class="item-title">
        <?= $model->object_name ?>
    </h3>

    <p class="item-description">
        <?= Html::encode($model->description) ?>
    </p>

    <a class="btn btn-default" href="<?= Url::toRoute(['/' . $model->type . '/view', 'id' => $model->id]) ?>">
        Tovább az észleléshez &raquo;
    </a>
</div>

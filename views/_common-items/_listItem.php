<?php

use app\models\Observe;
use yii\helpers\Url;

/** @var $model Observe */


?>
<div class="container list-item ">
    <a href="<?= Url::toRoute(['/' . $model->type . '/view', 'id' => $model->id])?>">
        <div class="row">
            <div class="col-md-3 col-12 item-img-container">
                <img class="w-100" src="<?= $model->getThumbnailPath() ?>" alt >
                <?php
                if ($model->isNew()) {
                ?>
                    <div class="list-top-right">Új feltöltés</div>
                <?php
                }
                ?>
                <div class="list-bottom-left"><?= $model->observer->name ?> </div>
                <div class="list-bottom-right"><?= count($model->comments) ?> hozzászólás</div>
            </div>
            <div class="col-md-9 col-12 ">
                <div class="font-weight-bold text-white">
                    <?= $model->object_name ?>
                </div>
                <div class="item-title">
                    <?= $model->date ?>
                </div>

                <div class="item-desc">
                    <?= $model->description?>
                </div>
            </div>

        </div>
    </a>
</div>



<?php

use app\models\Observe;
use yii\helpers\Url;

/** @var $model Observe */


?>
<div class="container list-item">
    <a href="<?= Url::toRoute(['/' . $model->type . '/view', 'id' => $model->id])?>">
        <div class="row">
            <div class="col-md-4 col-12 ">
                <img class="w-100" src="<?= $model->getThumbnailPath() ?>" alt >
            </div>
            <div class="col-md-8 col-12 ">
                <div class="font-weight-bold ">
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



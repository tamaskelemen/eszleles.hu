<?php

use app\models\Observe;
use yii\helpers\Url;

/** @var $model Observe */


?>
<div class="conatiner list-item">
    <a href="<?= Url::to(['deepsky/view', 'id' => $model->id])?>">
        <div class="row">
            <div class="col-md-3 col-12 ">
                <img class="w-100 mr-auto " src="/<?= $model->getImagePath() ?>" alt >
            </div>
            <div class="col-md-9 col-12 ">
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



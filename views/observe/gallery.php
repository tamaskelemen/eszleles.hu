<?php

use yii\helpers\Html;
/** @var \app\models\Observe $model */

$isOwner = false;
if (!Yii::$app->user->isGuest && $model->observer_id == Yii::$app->user->identity->id) {
    $isOwner = true;
}

if ($isOwner) {
    $this->registerJsFile('/js/image-tag-admin.js', ['depends' => 'app\assets\AppAsset']);
} else {
    $this->registerJsFile('/js/image-tag.js', ['depends' => 'app\assets\AppAsset']);
}

$image = $model->getImage()->one();

if ($image !== null) { ?>
    <div id="img-container" class="img-container">
        <?= Html::img(
                Yii::$app->getHomeUrl() . $image->path,
                [
                    'alt' => $model->object_name,
                    'class'=> 'img-fluid img-view m-auto',
                    'id' => $image->id,
                    'title' => $model->object_name,
                ]) ?>
    </div>

    <div id="tagbox">
    </div>

    <div id="taglist">

    </div>
    <?php
} else { ?>
    <p class="font-italic">Az észleléshez nem tartozik kép.</p>
<?php }
?>
<?php

use yii\helpers\Html;
/** @var \app\models\Observe $model */

$this->registerJsFile('/annotorius/annotorious.min.js', ['depends' => 'app\assets\AppAsset']);
$this->registerCssFile('/annotorius/annotorious.min.css', ['depends' => 'app\assets\AppAsset']);
$this->registerCssFile('/css/annotations.css', ['depends' => 'app\assets\AppAsset']);

$isOwner = false;
if (!Yii::$app->user->isGuest && $model->observer_id == Yii::$app->user->identity->id) {
    $isOwner = true;
}

if ($isOwner) {
    $this->registerJsFile('/js/annotate-admin.js', ['depends' => 'app\assets\AppAsset']);
//    $this->registerJsFile('/js/image-tag-admin.js', ['depends' => 'app\assets\AppAsset']);
} else {
    $this->registerJsFile('/js/annotate.js', ['depends' => 'app\assets\AppAsset']);
//    $this->registerJsFile('/js/image-tag.js', ['depends' => 'app\assets\AppAsset']);
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

<!--    <div class="custom-control custom-switch">-->
<!--        <input type="checkbox" class="custom-control-input" id="customSwitch1">-->
<!--        <label class="custom-control-label" for="customSwitch1">Toggle this switch element</label>-->
<!--    </div>-->
    <div id="tagbox">
    </div>

    <div id="taglist">

    </div>
    <?php
} else { ?>
    <p class="font-italic">Az észleléshez nem tartozik kép.</p>
<?php }
?>
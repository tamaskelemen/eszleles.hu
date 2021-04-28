<?php

use yii\helpers\Html;
/** @var \app\models\Observe $model */

$this->registerJsFile('/annotorius/annotorious.min.js', ['depends' => 'app\assets\AppAsset']);
$this->registerCssFile('/annotorius/annotorious.min.css', ['depends' => 'app\assets\AppAsset']);
$this->registerCssFile('/css/annotations.css', ['depends' => 'app\assets\AppAsset']);

$this->registerCssFile('/lightgallery/css/lightgallery.css', ['depends' => 'app\assets\AppAsset']);
$this->registerJsFile('/lightgallery/js/lightgallery.min.js', ['depends' => 'app\assets\AppAsset']);
$this->registerJsFile("/js/observation.js", ['depends' => 'app\assets\AppAsset']);

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
    <div id="lightgallery" >
        <div data-src="<?= Yii::$app->getHomeUrl() . $image->path ?>">
            <p class="mb-1">
                <span class="fa fa-window-maximize"></span>
                Teljes képernyő
            </p>
        </div>
    </div>
    <div id="img-container" class="img-container">
        <?= Html::img(
            Yii::$app->getHomeUrl() . $image->path,
            [
                'alt' => $model->object_name,
                'class'=> 'img-fluid m-auto img-view',
                'id' => $image->id,
                'title' => $model->object_name,
            ]) ?>
    </div>

    <div id="tagbox">
    </div>

    <div class="row">
        <div id="taglist" class="col-10 ml-auto">
            Jelölések:
        </div>

        <div class="col-2 text-right" data-toggle="popover" data-content="
                  A feltöltött képeiden meg tudod jelölni a látható objektumat azok nevével. Egy objektumnak akár több nevet is megadhatsz.
                  A látogatók ezután az észlelésed adatlapján láthatják azokat, ezzel megkönnyítve nekik a beazonosítást.
                  A képeiden a jelölések szerkesztéséhez be kell jelentkezned.
            ">
            Mi ez?
            <span class="fa fa-info-circle" ></span>
        </div>
    </div>
    <?php
} else { ?>
    <p class="font-italic">Az észleléshez nem tartozik kép.</p>
<?php }
?>
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
                <span class="fa fa-search-plus"></span>
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
        <?php if ($model->observer_id === Yii::$app->user->getId()) { ?>
            <div class="container">
                <div class="alert-info alert" role="alert">
                    Szerkesztő mód: a képen megjelölheted a látható objektumokat, látnivalókat.
                </div>
            </div>
        <?php } ?>

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

    <?php $previousVersions = $model->getPreviousImageVersions();
    if (!empty($previousVersions)) { ?>
        <div class="image-versions mt-3">
            <b class="text-white">Korábbi képverziók</b>
            <div id="image-versions" class="row mt-2">
                <?php foreach ($previousVersions as $version) {
                    if ($version->image === null) {
                        continue;
                    }
                    $thumbnailPath = $version->thumbnail !== null ? $version->thumbnail->path : $version->image->path;
                    $uploadedAtDate = substr($version->created_at, 0, 16);
                    $uploadedAt = "Feltöltve: " . $uploadedAtDate;
                    ?>
                    <a class="image-version col-6 col-md-3 mb-2"
                       href="<?= Yii::$app->getHomeUrl() . $version->image->path ?>"
                       data-sub-html="<?= Html::encode($uploadedAt) ?>">
                        <?= Html::img(Yii::$app->getHomeUrl() . $thumbnailPath, [
                            'alt' => $model->object_name,
                            'class' => 'img-fluid',
                            'title' => $uploadedAt,
                        ]) ?>
                        <small class="image-version-date d-block text-center mt-1"><?= Html::encode($uploadedAtDate) ?></small>
                    </a>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
    <?php
} else { ?>
    <p class="font-italic">Az észleléshez nem tartozik kép.</p>
<?php }
?>
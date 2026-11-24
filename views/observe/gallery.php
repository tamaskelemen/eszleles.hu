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
    <div class="stage">
        <div id="img-container" class="img-container">
            <?= Html::img(
                Yii::$app->getHomeUrl() . $image->path,
                [
                    'alt' => $model->object_name,
                    'class'=> 'img-view',
                    'id' => $image->id,
                    'title' => $model->object_name,
                ]) ?>
        </div>
    </div>

    <div id="tagbox">
    </div>

    <div class="stage-bar">
        <?php if ($model->observer_id === Yii::$app->user->getId()) { ?>
            <div class="alert-info alert" role="alert">
                Szerkesztő mód: a képen megjelölheted a látható objektumokat, látnivalókat.
            </div>
        <?php } ?>

        <div id="taglist">
            Jelölések:
        </div>

        <div class="stage-bar__help" data-toggle="popover" data-content="
                  A feltöltött képeiden meg tudod jelölni a látható objektumat azok nevével. Egy objektumnak akár több nevet is megadhatsz.
                  A látogatók ezután az észlelésed adatlapján láthatják azokat, ezzel megkönnyítve nekik a beazonosítást.
                  A képeiden a jelölések szerkesztéséhez be kell jelentkezned.
            ">
            Mi ez?
            <span class="fa fa-info-circle" ></span>
        </div>

        <div id="lightgallery">
            <div class="btn btn-outline" data-src="<?= Yii::$app->getHomeUrl() . $image->path ?>">
                <span class="fa fa-search-plus"></span>
                Teljes képernyő
            </div>
        </div>
    </div>

    <?php $previousVersions = $model->getPreviousImageVersions();
    if (!empty($previousVersions)) { ?>
        <div class="image-versions">
            <h2>Korábbi képverziók</h2>
            <div id="image-versions" class="image-versions__list">
                <?php foreach ($previousVersions as $version) {
                    if ($version->image === null) {
                        continue;
                    }
                    $thumbnailPath = $version->thumbnail !== null ? $version->thumbnail->path : $version->image->path;
                    $uploadedAtDate = substr($version->created_at, 0, 16);
                    $uploadedAtTag = Yii::$app->formatter->asTimeAgo($version->created_at, $uploadedAtDate);
                    $uploadedAt = "Feltöltve: " . $uploadedAtDate;
                    ?>
                    <a class="image-version"
                       href="<?= Yii::$app->getHomeUrl() . $version->image->path ?>"
                       data-sub-html="<?= Html::encode("Feltöltve: " . $uploadedAtTag) ?>">
                        <?= Html::img(Yii::$app->getHomeUrl() . $thumbnailPath, [
                            'alt' => $model->object_name,
                            'title' => $uploadedAt,
                        ]) ?>
                        <small class="image-version-date" title="<?= Html::encode($uploadedAt) ?>"><?= strip_tags($uploadedAtTag) ?></small>
                    </a>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
    <?php
} else { ?>
    <p class="no-image">Az észleléshez nem tartozik kép.</p>
<?php }
?>
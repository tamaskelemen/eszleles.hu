<?php

use app\components\recommender\Context;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\forms\LocationForm */
/* @var $user app\models\User */

$this->title = 'Helyszín';
$this->params['breadcrumbs'][] = ['label' => 'Felhasználók', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $user->name, 'url' => ['profile', 'id' => $user->id]];
$this->params['breadcrumbs'][] = 'Helyszín';

$this->registerCssFile('/leaflet/leaflet.css?v=1.9.4');
$this->registerJsFile('/leaflet/leaflet.js?v=1.9.4', ['depends' => \app\assets\AppAsset::class]);
$this->registerJsFile('/js/location.js?v=' . filemtime(Yii::getAlias('@webroot/js/location.js')), ['depends' => \app\assets\AppAsset::class]);

$saved = $user->getLocation();
$formatCoordinates = function ($latitude, $longitude) {
    return number_format(abs($latitude), 2, ',', '') . '° ' . ($latitude >= 0 ? 'É' : 'D') . ', '
        . number_format(abs($longitude), 2, ',', '') . '° ' . ($longitude >= 0 ? 'K' : 'Ny');
};
$start = $saved ?? Context::DEFAULT_PLACE;
// after a failed save the posted point is shown again
$hasPoint = is_numeric($model->latitude) && is_numeric($model->longitude) && !$model->hasErrors('latitude') && !$model->hasErrors('longitude');
?>
<div class="users-update location-edit">
    <div class="container container-narrow">
        <h1><?= Html::encode($this->title) ?></h1>

        <p>
            A főoldali <?= Html::a('Mit észleljek most?', Url::to(['/site/index', '#' => 'ajanlo'])) ?> ajánló ezzel a
            helyszínnel számolja ki, mi látszik most az égen. A koordinátákat körülbelül egy kilométerre kerekítve
            tároljuk, és senkinek nem mutatjuk meg.
        </p>

        <div class="panel location-current">
            <?php if ($saved === null) { ?>
                <p>Még nem adtál meg helyszínt, ezért <strong>Budapesttel</strong> számolunk.</p>
            <?php } else { ?>
                <p>
                    Jelenleg ezzel számolunk: <strong><?= Html::encode($saved['name']) ?></strong>
                    (<?= $formatCoordinates($saved['latitude'], $saved['longitude']) ?>)
                </p>
                <?= Html::beginForm(['delete-location'], 'post') ?>
                <?= Html::submitButton('Helyszín törlése', [
                    'class' => 'btn btn-outline btn-sm',
                    'data-confirm' => 'Biztosan törlöd a helyszínt? Az ajánló újra Budapesttel számol.',
                ]) ?>
                <?= Html::endForm() ?>
            <?php } ?>
        </div>

        <h2 class="location-step">Új helyszín</h2>

        <section class="location-option">
            <h3>Az eszközöd helyzete</h3>
            <p class="text-muted">A böngésző megkérdezi, megoszthatja-e veled a helyzetedet.</p>
            <button class="btn btn-primary" type="button" id="location-device">
                <i class="fa fa-location-arrow" aria-hidden="true"></i> Helyzetem lekérdezése
            </button>
            <p class="location-message" id="location-device-message" role="status"></p>
        </section>

        <section class="location-option">
            <h3>Település keresése</h3>
            <form class="location-search" id="location-search" role="search">
                <label class="sr-only" for="location-query">Település neve</label>
                <input class="form-control" id="location-query" type="search" placeholder="pl. Zalaegerszeg" autocomplete="off" required>
                <button class="btn btn-outline" type="submit">Keresés</button>
            </form>
            <p class="location-message" id="location-search-message" role="status"></p>
            <ul class="location-results" id="location-results"></ul>
        </section>

        <section class="location-option">
            <h3>Kijelölés a térképen</h3>
            <p class="text-muted">Kattints a térképre, vagy húzd a jelölőt a pontos helyre.</p>
            <div class="location-map" id="location-map"
                 data-latitude="<?= Html::encode($start['latitude']) ?>"
                 data-longitude="<?= Html::encode($start['longitude']) ?>"
                 data-zoom="<?= $saved === null ? 6 : 10 ?>"
                 data-has-point="<?= $saved === null ? '0' : '1' ?>"></div>
        </section>

        <?php $form = ActiveForm::begin(['id' => 'location-form', 'options' => ['class' => 'location-form']]); ?>
            <p class="location-chosen" id="location-chosen" <?= $hasPoint ? '' : 'hidden' ?>>
                Kiválasztott pont: <strong id="location-coordinates"><?= $hasPoint ? $formatCoordinates((float)$model->latitude, (float)$model->longitude) : '' ?></strong>
            </p>

            <?= Html::activeHiddenInput($model, 'latitude', ['id' => 'location-latitude']) ?>
            <?= Html::activeHiddenInput($model, 'longitude', ['id' => 'location-longitude']) ?>
            <?= Html::error($model, $model->hasErrors('latitude') ? 'latitude' : 'longitude', ['class' => 'invalid-feedback d-block']) ?>

            <?= $form->field($model, 'name')->textInput(['id' => 'location-name', 'maxlength' => 255])
                ->hint('Ez jelenik meg az ajánló alatt. Kitöltjük helyetted, de átírhatod, pl. „Hétvégi ház”.') ?>

            <div class="form-group">
                <?= Html::submitButton('Mentés', ['class' => 'btn btn-primary']) ?>
            </div>
        <?php ActiveForm::end(); ?>

        <p class="location-credit">
            Keresés és térkép: <?= Html::a('OpenStreetMap', 'https://www.openstreetmap.org/copyright') ?>-közreműködők,
            <?= Html::a('Nominatim', 'https://nominatim.org') ?>, <?= Html::a('Leaflet', 'https://leafletjs.com') ?>.
        </p>
    </div>
</div>

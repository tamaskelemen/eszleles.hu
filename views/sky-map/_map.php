<?php

use app\assets\AppAsset;
use app\components\Data;
use app\components\SkyMap;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $gaps array */
/* @var $userId int|null only the observations of this user are shown */

$userId = $userId ?? null;

$this->registerJsFile('/skymap/d3.min.js?v=7.9.0', ['depends' => AppAsset::class]);
$this->registerJsFile('/js/sky-map.js?v=' . filemtime(Yii::getAlias('@webroot/js/sky-map.js')), ['depends' => AppAsset::class]);
?>
<div class="skymap" id="skymap"
     data-objects-url="<?= Url::to(['/sky-map/data', 'userId' => $userId]) ?>"
     data-object-url="<?= Url::to(['/sky-map/object', 'id' => 0, 'userId' => $userId]) ?>"
     data-background-url="/skymap/background.json?v=<?= filemtime(Yii::getAlias('@webroot/skymap/background.json')) ?>"
     data-constellations="<?= Html::encode(Json::encode(Data::listConstellation())) ?>"
     data-gaps="<?= Html::encode(Json::encode($gaps)) ?>"
     data-horizon="<?= SkyMap::HORIZON_DEC ?>"
     <?= $userId === null ? '' : 'data-single-observer' ?>
>
    <div class="skymap__controls">
        <?php if ($userId === null) { ?>
            <fieldset class="skymap__metric">
                <legend class="sr-only">Mit mutasson a térkép?</legend>
                <label><input type="radio" name="skymap-metric" value="observations" checked> Észlelések száma</label>
                <label><input type="radio" name="skymap-metric" value="observers"> Észlelők száma</label>
            </fieldset>
        <?php } ?>

        <div class="skymap__layers">
            <label><input type="checkbox" data-layer="constellations" checked> Csillagképek</label>
            <label><input type="checkbox" data-layer="milkyWay" checked> Tejút</label>
            <label><input type="checkbox" data-layer="grid"> Koordináták</label>
            <?php if ($userId === null) { ?>
                <span class="skymap__option">
                    <label><input type="checkbox" data-layer="gaps"> Fehér foltok</label>
                    <?= $this->render('//_common-items/_infoTip', [
                        'label' => 'Mik azok a fehér foltok?',
                        'text' => 'Az égbolt legnagyobb olyan területei, ahonnan még nincs azonosított észlelés: a szaggatott '
                            . 'körökön belül egyetlen észlelt objektum sincs. A számuk a térkép alatti lista sorszáma. Csak a '
                            . 'Magyarországról jól észlelhető égen (' . str_replace('-', '−', SkyMap::GAP_DEC) . '° '
                            . 'deklinációtól északra) keressük őket.',
                    ]) ?>
                </span>
            <?php } ?>
        </div>
    </div>

    <div class="skymap__stage">
        <canvas class="skymap__canvas" tabindex="0" role="img"
                aria-label="Az égbolt térképe az észlelt objektumokkal. Nyilakkal mozgatható, a + és - gombbal nagyítható. <?= $userId === null ? 'Az adatok a térkép alatti táblázatban is megtalálhatók.' : 'Az észlelések a térkép alatt is megtalálhatók.' ?>"></canvas>

        <div class="skymap__buttons">
            <button type="button" data-action="in" aria-label="Nagyítás" title="Nagyítás">+</button>
            <button type="button" data-action="out" aria-label="Kicsinyítés" title="Kicsinyítés">−</button>
            <button type="button" data-action="reset" aria-label="Teljes égbolt" title="Teljes égbolt">⟲</button>
            <button type="button" data-action="fullscreen" aria-label="Teljes képernyő" title="Teljes képernyő" aria-pressed="false">⛶</button>
        </div>

        <div class="skymap__tooltip" hidden></div>
        <p class="skymap__message" role="status">A térkép betöltése…</p>

        <aside class="skymap__panel" aria-live="polite" hidden></aside>
    </div>

    <div class="skymap__foot">
        <div class="skymap__legend">
            <span>kevés</span>
            <span class="skymap__ramp" aria-hidden="true"></span>
            <span>sok <span data-legend-max></span></span>
        </div>
        <div class="skymap__program" hidden>
            <span class="skymap__dot" aria-hidden="true"></span>
            <span data-program-name role="status"></span>
            <button type="button" class="skymap-link" data-action="hideProgram">Elrejtés</button>
        </div>
        <p class="skymap__hint skymap__hint--mouse">Húzással mozgatható. Nagyítás: gombokkal, Ctrl + görgővel, vagy kattintás után görgővel.</p>
        <p class="skymap__hint skymap__hint--touch">Két ujjal mozgatható és nagyítható, teljes képernyőn egy ujjal is.</p>
    </div>
</div>

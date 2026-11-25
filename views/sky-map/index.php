<?php

use app\assets\AppAsset;
use app\components\Data;
use app\components\SkyMap;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $stats array */
/* @var $gaps array */
/* @var $missing string[] abbreviations of constellations */
/* @var $unmapped int[] number of observations indexed by catalog */

$this->title = 'Égtérkép';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('/skymap/d3.min.js?v=7.9.0', ['depends' => AppAsset::class]);
$this->registerJsFile('/js/sky-map.js?v=' . filemtime(Yii::getAlias('@webroot/js/sky-map.js')), ['depends' => AppAsset::class]);

$formatter = Yii::$app->formatter;
$tiles = [
    ['Azonosított észlelés', $formatter->asInteger($stats['observations']), $formatter->asInteger($stats['observers']) . ' észlelőtől'],
    ['Különböző objektum', $formatter->asInteger($stats['objects']), 'galaxisok, ködök, halmazok'],
    ['Csillagkép', $stats['constellations'] . ' / ' . count(Data::listConstellation()), 'amelyben már észleltünk'],
    ['Messier-objektum', $stats['messier'] . ' / ' . SkyMap::MESSIER_COUNT, $stats['messier'] >= SkyMap::MESSIER_COUNT ? 'a teljes katalógus megvan' : 'a katalógusból'],
    ['Caldwell-objektum', $stats['caldwell'] . ' / ' . SkyMap::CALDWELL_COUNT, 'a katalógusból'],
    ['Lefedettség', $formatter->asPercent($stats['coverage']), 'a Magyarországról látható ég ekkora részét észleltük már (objektumonként ' . SkyMap::COVER_RADIUS . '° sugarú körrel)'],
];
?>
<div class="skymap-page container">
    <h1><?= Html::encode($this->title) ?> <span class="skymap-beta">Béta</span></h1>

    <p class="skymap-lead">
        Így fedi le a közösség az égboltot: ahol világosabb a folt, ott több feltöltött mélyég-észlelés készült.
        Nagyíts rá, és válassz ki egy objektumot az észleléseihez.
    </p>

    <p class="skymap-beta-note">
        Az égtérkép kísérleti funkció, még fejlesztjük. Ha hibát találsz,
        <?= Html::a('jelezd nekünk', ['/bug-report/create']) ?>.
    </p>

    <div class="skymap" id="skymap"
         data-objects-url="<?= Url::to(['/sky-map/data']) ?>"
         data-object-url="<?= Url::to(['/sky-map/object', 'id' => 0]) ?>"
         data-background-url="/skymap/background.json?v=<?= filemtime(Yii::getAlias('@webroot/skymap/background.json')) ?>"
         data-constellations="<?= Html::encode(Json::encode(Data::listConstellation())) ?>"
         data-gaps="<?= Html::encode(Json::encode($gaps)) ?>"
         data-horizon="<?= SkyMap::HORIZON_DEC ?>"
    >
        <div class="skymap__controls">
            <fieldset class="skymap__metric">
                <legend class="sr-only">Mit mutasson a térkép?</legend>
                <label><input type="radio" name="skymap-metric" value="observations" checked> Észlelések száma</label>
                <label><input type="radio" name="skymap-metric" value="observers"> Észlelők száma</label>
            </fieldset>

            <div class="skymap__layers">
                <label><input type="checkbox" data-layer="constellations" checked> Csillagképek</label>
                <label><input type="checkbox" data-layer="milkyWay" checked> Tejút</label>
                <label><input type="checkbox" data-layer="grid"> Koordináták</label>
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
            </div>
        </div>

        <div class="skymap__stage">
            <canvas class="skymap__canvas" tabindex="0" role="img"
                    aria-label="Az égbolt térképe az észlelt objektumokkal. Nyilakkal mozgatható, a + és - gombbal nagyítható. Az adatok a térkép alatti táblázatban is megtalálhatók."></canvas>

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
            <p class="skymap__hint skymap__hint--mouse">Húzással mozgatható. Nagyítás: gombokkal, Ctrl + görgővel, vagy kattintás után görgővel.</p>
            <p class="skymap__hint skymap__hint--touch">Két ujjal mozgatható és nagyítható, teljes képernyőn egy ujjal is.</p>
        </div>
    </div>

    <section aria-labelledby="skymap-stats">
        <h2 id="skymap-stats">Számokban</h2>

        <dl class="skymap-tiles">
            <?php foreach ($tiles as list($label, $value, $note)) { ?>
                <div>
                    <dt><?= $label ?></dt>
                    <dd>
                        <b><?= $value ?></b>
                        <span><?= $note ?></span>
                    </dd>
                </div>
            <?php } ?>
        </dl>
    </section>

    <div class="skymap-columns">
        <section aria-labelledby="skymap-top">
            <h2 id="skymap-top">Legtöbbet észlelt objektumok</h2>
            <ol class="skymap-top"></ol>
        </section>

        <section aria-labelledby="skymap-gaps">
            <h2 id="skymap-gaps">Legnagyobb fehér foltok</h2>

            <p>
                Itt van a legtöbb hely a következő észlelésnek: ezeken a körökön belül még egyetlen azonosított objektum
                sincs.
            </p>

            <ol class="skymap-gaps">
                <?php foreach ($gaps as $index => $gap) { ?>
                    <li>
                        <button type="button" class="skymap-link" data-gap="<?= $index ?>">
                            <?= Html::encode(Data::listConstellation($gap['constellation'])) ?>
                        </button>
                        <span><?= round($gap['radius']) ?>° sugarú kör</span>
                    </li>
                <?php } ?>
            </ol>

            <?php if ($missing) { ?>
                <h3>Hiányzó csillagképek</h3>
                <p>
                    Magyarországról legalább részben látszanak, de még nincs bennük azonosított objektum:
                    <?= Html::encode(implode(', ', array_map([Data::class, 'listConstellation'], $missing))) ?>.
                </p>
            <?php } ?>
        </section>
    </div>

    <details class="skymap-table">
        <summary>Az összes észlelt objektum táblázatban</summary>
        <div class="table-responsive"></div>
    </details>

    <section class="skymap-unmapped" aria-labelledby="skymap-unmapped">
        <h2 id="skymap-unmapped">Ami még hiányzik a térképről</h2>

        <p>
            Ezeket a katalógusokat még nem, vagy csak részben ismeri a térkép. Mellettük az, hogy hány észlelés hiányzik
            miattuk.
        </p>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Katalógus</th>
                        <th scope="col" class="text-right">Hiányzó észlelés</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($unmapped as $catalog => $count) { ?>
                        <tr>
                            <td><?= Html::encode($catalog) ?></td>
                            <td class="text-right"><?= $formatter->asInteger($count) ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </section>

    <p class="skymap-credit">
        A térképen a nyilvánosan azonosított objektumok szerepelnek.
        Objektumok: <?= Html::a('OpenNGC', 'https://github.com/mattiaverga/OpenNGC') ?>, Mattia Verga
        (<?= Html::a('CC BY-SA 4.0', 'https://creativecommons.org/licenses/by-sa/4.0/') ?>), valamint Sharpless (1959), Barnard (1927), Lynds (1962, 1965) és van den Bergh (1966) katalógusa,
        <?= Html::a('VizieR', 'https://vizier.cds.unistra.fr') ?>, CDS Strasbourg.
        Csillagok, csillagképek, Tejút: <?= Html::a('d3-celestial', 'https://github.com/ofrohn/d3-celestial') ?>, Olaf Frohn (BSD).
    </p>
</div>

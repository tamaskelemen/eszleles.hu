<?php

use app\components\Data;
use app\components\SkyMap;

/* @var $this yii\web\View */
/* @var $stats array */
/* @var $isSingleObserver bool|null the statistics of one user, not of everybody */

$isSingleObserver = $isSingleObserver ?? false;
$formatter = Yii::$app->formatter;
$tiles = [
    ['Azonosított észlelés', $formatter->asInteger($stats['observations']), $isSingleObserver ? 'mélyég-objektumról' : $formatter->asInteger($stats['observers']) . ' észlelőtől'],
    ['Különböző objektum', $formatter->asInteger($stats['objects']), 'galaxisok, ködök, halmazok'],
    ['Csillagkép', $stats['constellations'] . ' / ' . count(Data::listConstellation()), $isSingleObserver ? 'amelyben már észlelt' : 'amelyben már észleltünk'],
    ['Messier-objektum', $stats['messier'] . ' / ' . SkyMap::MESSIER_COUNT, $stats['messier'] >= SkyMap::MESSIER_COUNT ? 'a teljes katalógus megvan' : 'a katalógusból'],
    ['Caldwell-objektum', $stats['caldwell'] . ' / ' . SkyMap::CALDWELL_COUNT, 'a katalógusból'],
    ['Lefedettség', $formatter->asPercent($stats['coverage'], $isSingleObserver ? 1 : 0), 'a Magyarországról látható ég ekkora részét ' . ($isSingleObserver ? 'észlelte' : 'észleltük') . ' már (objektumonként ' . SkyMap::COVER_RADIUS . '° sugarú körrel)'],
];
?>
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

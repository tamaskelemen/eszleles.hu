<?php

use app\components\Data;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $stats array */
/* @var $gaps array */
/* @var $missing string[] abbreviations of constellations */
/* @var $unmapped int[] number of observations indexed by catalog */
/* @var $plan app\models\ObservingPlan|null shown on the map when it is opened */

$this->title = 'Égtérkép';
$this->params['breadcrumbs'][] = $this->title;

$this->registerMetaTag(['property' => 'og:image', 'content' => Url::to('/pictures/egterkep.jpg', true)], 'og:image');
$this->registerMetaTag([
    'property' => 'og:description',
    'content' => 'Így fedi le a közösség az égboltot: a feltöltött mélyég-észlelések az égtérképen.',
], 'og:description');

$formatter = Yii::$app->formatter;
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

    <?= $this->render('_map', [
        'gaps' => $gaps,
        'program' => $plan === null ? null : [
            'url' => Url::to(['/sky-map/plan', 'id' => $plan->id]),
            'name' => $plan->name,
            'param' => 'terv',
        ],
    ]) ?>

    <?= $this->render('_stats', ['stats' => $stats]) ?>

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

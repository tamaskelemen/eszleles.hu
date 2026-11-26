<?php

use app\components\Data;
use app\components\SkyPrograms;
use app\controllers\ProgramController;
use app\models\SkyObject;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/* @var $this yii\web\View */
/* @var $key string */
/* @var $program array */
/* @var $totals array */
/* @var $progress array|null badge category of the visitor, null for guests */
/* @var $items array lists of objects indexed by the number of the item */
/* @var $observations array lists of own observations indexed by the number of the item */
/* @var $filter string|null */
/* @var $pagination yii\data\Pagination */

$this->title = $program['name'];
$this->params['breadcrumbs'][] = ['label' => 'Észlelőprogramok', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$isGuest = Yii::$app->user->isGuest;
$filters = [
    null => 'Összes',
    ProgramController::FILTER_DONE => 'Megvan',
    ProgramController::FILTER_MISSING => 'Hiányzik',
];
?>
<div class="container">
    <h1><?= Html::encode($this->title) ?></h1>

    <p class="skymap-lead"><?= SkyPrograms::getDescriptionHtml($program) ?></p>

    <?= $this->render('_totals', ['key' => $key, 'totals' => $totals]) ?>

    <?php if ($key !== SkyPrograms::MESSIER && $totals['part'] < $totals['total']) { ?>
        <p class="skymap-credit"><?= SkyPrograms::getVisibilityNote() ?> A többi halványabban szerepel.</p>
    <?php } ?>

    <?php if ($isGuest) { ?>
        <?= $this->render('_guestCta') ?>
    <?php } ?>

    <?php if ($progress !== null) { ?>
        <div class="badges__card program-progress">
            <?= $this->render('//_common-items/_badgeProgress', ['category' => $progress]) ?>
            <div class="badges__list">
                <?php foreach ($progress['levels'] as $level) { ?>
                    <?= $this->render('//_common-items/_badge', ['category' => $progress, 'level' => $level]) ?>
                <?php } ?>
            </div>
        </div>

        <ul class="nav nav-pills program-filter">
            <?php foreach ($filters as $value => $label) { ?>
                <li class="nav-item">
                    <?= Html::a($label, ['view', 'key' => $key, 'szures' => $value ?: null], [
                        'class' => 'nav-link' . ((string)$filter === (string)$value ? ' active' : ''),
                    ]) ?>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>

    <?php if (empty($items)) { ?>
        <p>Nincs ilyen objektum.</p>
    <?php } else { ?>
        <div class="table-responsive">
            <table class="table program-table<?= $isGuest ? '' : ' program-table--own' ?>">
                <thead>
                    <tr>
                        <th scope="col">Szám</th>
                        <th scope="col">Objektum</th>
                        <th scope="col">Típus</th>
                        <th scope="col">Csillagkép</th>
                        <th scope="col">Deklináció</th>
                        <?php if (!$isGuest) { ?>
                            <th scope="col">Észlelésed</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $number => $objects) {
                        $own = $observations[$number] ?? [];
                        $first = $objects[0];
                        $isVisible = max(array_column($objects, 'dec')) >= SkyPrograms::VISIBLE_DEC;
                        $typeName = SkyObject::getTypeName($first['type']);
                        $constellation = Html::encode(Data::listConstellation($first['constellation']));
                        $dec = str_replace(['-', '.'], ['−', ','], sprintf('%.1f', $first['dec'])) . '°';
                        $labels = array_map(function ($object) {
                            $label = Html::encode(SkyObject::buildLabel($object));

                            return $object['is_observed']
                                ? Html::a($label, ['/sky-map/index', 'objektum' => $object['id']], ['title' => 'Megnyitás az égtérképen'])
                                : $label;
                        }, $objects);
                        ?>
                        <tr class="<?= $own ? 'is-done' : '' ?> <?= $isVisible ? '' : 'is-hidden' ?>">
                            <th scope="row"><?= Html::encode($program['prefix'] . $number) ?></th>
                            <td class="program-table__object">
                                <?= implode(', ', $labels) ?>
                                <div class="program-table__meta"><?= $typeName ?> · <?= $constellation ?> · <?= $dec ?></div>
                            </td>
                            <td class="program-table__detail"><?= $typeName ?></td>
                            <td class="program-table__detail"><?= $constellation ?></td>
                            <td class="program-table__detail"><?= $dec ?></td>
                            <?php if (!$isGuest) { ?>
                                <td class="program-table__own">
                                    <?php if ($own) { ?>
                                        <i class="fa fa-check" aria-hidden="true"></i>
                                        <?= Html::a(Html::encode(substr((string)$own[0]['date'], 0, 10) ?: 'megvan'), Url::to(["/{$own[0]['type']}/view", 'id' => $own[0]['id']])) ?>
                                        <?= count($own) > 1 ? '(+' . (count($own) - 1) . ')' : '' ?>
                                    <?php } ?>
                                </td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <?= LinkPager::widget(['pagination' => $pagination]) ?>
    <?php } ?>

    <p class="skymap-credit">
        Objektumok: <?= Html::a('OpenNGC', 'https://github.com/mattiaverga/OpenNGC') ?>, Mattia Verga
        (<?= Html::a('CC BY-SA 4.0', 'https://creativecommons.org/licenses/by-sa/4.0/') ?>) és
        <?= Html::a('VizieR', 'https://vizier.cds.unistra.fr') ?>, CDS Strasbourg.
        Listák: <?= Html::a('Astronomical League', 'https://www.astroleague.org/herschel-400-observing-program/') ?> (Herschel 400),
        <?= Html::a('RASC', 'https://www.rasc.ca') ?> (Finest NGC).
    </p>
</div>

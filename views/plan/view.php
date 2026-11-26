<?php

use app\components\Data;
use app\components\SkyPrograms;
use app\controllers\ProgramController;
use app\models\SkyObject;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JqueryAsset;

/* @var $this yii\web\View */
/* @var $plan app\models\ObservingPlan */
/* @var $items array objects in the order of the plan, indexed by object id */
/* @var $positions int[] position in the plan indexed by object id, from 0 */
/* @var $observations array lists of own observations indexed by object id */
/* @var $total int */
/* @var $done int */
/* @var $filter string|null */

$this->title = $plan->name;
$this->params['breadcrumbs'][] = ['label' => 'Észlelőprogramok', 'url' => ['/program/index']];
$this->params['breadcrumbs'][] = $this->title;

$isGuest = Yii::$app->user->isGuest;
$formatter = Yii::$app->formatter;
$filters = [
    null => 'Összes',
    ProgramController::FILTER_DONE => 'Megvan',
    ProgramController::FILTER_MISSING => 'Hiányzik',
];
?>
<div class="container">
    <h1><?= Html::encode($this->title) ?></h1>

    <p class="plan-meta">
        Észlelési terv,
        <?= Html::a(Html::encode($plan->user->name), ['/user/profile', 'id' => $plan->user_id]) ?> összeállítása
        <?php if ($plan->isOwn()) { ?>
            · <?= $plan->is_public ? 'nyilvános' : 'csak te látod' ?>
            · <?= Html::a('<i class="fa fa-pencil" aria-hidden="true"></i> Módosítás', ['update', 'id' => $plan->id]) ?>
        <?php } ?>
    </p>

    <?php if ($plan->description) { ?>
        <p class="skymap-lead"><?= nl2br(Html::encode($plan->description)) ?></p>
    <?php } ?>

    <?php if ($isGuest) { ?>
        <?= $this->render('//program/_guestCta', ['hasBadges' => false]) ?>
    <?php } ?>

    <p class="badges__count">
        <?php if ($isGuest) { ?>
            <b><?= $formatter->asInteger($total) ?> objektum</b>
        <?php } else { ?>
            <b><?= $formatter->asInteger($done) ?> / <?= $formatter->asInteger($total) ?> objektum megvan</b>
        <?php } ?>
    </p>

    <?php if (!$isGuest && $total > 0) { ?>
        <ul class="nav nav-pills program-filter">
            <?php foreach ($filters as $value => $label) { ?>
                <li class="nav-item">
                    <?= Html::a($label, ['view', 'id' => $plan->id, 'szures' => $value ?: null], [
                        'class' => 'nav-link' . ((string)$filter === (string)$value ? ' active' : ''),
                    ]) ?>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>

    <?php if ($total === 0) { ?>
        <p>
            Ebben a tervben még nincs objektum.
            <?php if ($plan->isOwn()) { ?>
                <?= Html::a('Objektumok hozzáadása', ['update', 'id' => $plan->id]) ?>
            <?php } ?>
        </p>
    <?php } else if (empty($items)) { ?>
        <p>Nincs ilyen objektum.</p>
    <?php } else { ?>
        <div class="table-responsive">
            <table class="table program-table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
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
                    <?php foreach ($items as $id => $object) {
                        $own = $observations[$id] ?? [];
                        $label = Html::encode(SkyObject::buildLabel($object));
                        ?>
                        <tr id="terv-<?= $object['item_id'] ?>" class="<?= $own ? 'is-done' : '' ?> <?= $object['dec'] >= SkyPrograms::VISIBLE_DEC ? '' : 'is-hidden' ?>">
                            <th scope="row"><?= $positions[$id] + 1 ?></th>
                            <td>
                                <?= $object['is_observed']
                                    ? Html::a($label, ['/sky-map/index', 'objektum' => $id], ['title' => 'Megnyitás az égtérképen'])
                                    : $label ?>
                            </td>
                            <td><?= SkyObject::getTypeName($object['type']) ?></td>
                            <td><?= Html::encode(Data::listConstellation($object['constellation'])) ?></td>
                            <td><?= str_replace(['-', '.'], ['−', ','], sprintf('%.1f', $object['dec'])) ?>°</td>
                            <?php if (!$isGuest) { ?>
                                <td class="plan-observations">
                                    <?php
                                    $hidden = 0;

                                    foreach ($own as $i => $observation) {
                                        // the first one, and the ones attached by hand, so that they can be removed
                                        if ($i > 0 && !$observation['is_manual']) {
                                            $hidden++;
                                            continue;
                                        }
                                        ?>
                                        <span class="plan-observation">
                                            <i class="fa fa-check" aria-hidden="true"></i>
                                            <?= Html::a(
                                                Html::encode(substr((string)$observation['date'], 0, 10) ?: 'megvan'),
                                                Url::to(["/{$observation['type']}/view", 'id' => $observation['id']]),
                                                ['title' => $observation['is_manual'] ? 'Te választottad ki' : 'Azonosított észlelés']
                                            ) ?>
                                            <?php if ($observation['is_manual']) { ?>
                                                <?= Html::a('&times;', ['detach'], [
                                                    'class' => 'plan-observation__remove',
                                                    'title' => 'Eltávolítás',
                                                    'aria-label' => 'Eltávolítás: ' . substr((string)$observation['date'], 0, 10),
                                                    'data' => [
                                                        'method' => 'post',
                                                        'params' => ['item_id' => $object['item_id'], 'observation_id' => $observation['id'], 'szures' => $filter],
                                                    ],
                                                ]) ?>
                                            <?php } ?>
                                        </span>
                                    <?php } ?>
                                    <?= $hidden ? '(+' . $hidden . ')' : '' ?>
                                    <?php if ($own) { ?>
                                        <button type="button" class="plan-attach plan-attach--more"
                                                data-toggle="modal" data-target="#plan-attach"
                                                data-item-id="<?= $object['item_id'] ?>" data-label="<?= $label ?>"
                                                title="Még egy észlelésem" aria-label="Még egy észlelésem: <?= $label ?>">
                                            <i class="fa fa-plus" aria-hidden="true"></i>
                                        </button>
                                    <?php } else { ?>
                                        <button type="button" class="btn btn-outline btn-sm plan-attach"
                                                data-toggle="modal" data-target="#plan-attach"
                                                data-item-id="<?= $object['item_id'] ?>" data-label="<?= $label ?>"
                                                aria-label="Észleltem: <?= $label ?>">
                                            <i class="fa fa-check" aria-hidden="true"></i> Észleltem
                                        </button>
                                    <?php } ?>
                                </td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>

    <?php if (!$isGuest && !empty($items)) {
        $this->registerJsFile('/js/plan-attach.js?v=' . filemtime(Yii::getAlias('@webroot/js/plan-attach.js')), [
            'depends' => [JqueryAsset::class],
        ]);
        ?>
        <div class="modal fade" id="plan-attach" tabindex="-1" role="dialog" aria-labelledby="plan-attach-title" aria-hidden="true"
             data-url="<?= Url::to(['observations']) ?>">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <?= Html::beginForm(['attach'], 'post') ?>
                        <input type="hidden" name="item_id">
                        <input type="hidden" name="observation_id">
                        <?= Html::hiddenInput('szures', $filter) ?>

                        <div class="modal-header">
                            <h2 class="modal-title h5" id="plan-attach-title">Melyik észlelésed?</h2>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Bezárás">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small">
                                Ha nem ismertük fel, hogy már észlelted ezt az objektumot (<b class="plan-attach__object"></b>),
                                pl. egy nagy látómezős képen több is rajta van, válaszd ki itt a megfelelő észlelésed.
                                Csak a saját észleléseid közül választhatsz.
                            </p>
                            <label for="plan-attach-search" class="sr-only">Keresés az észleléseid címében</label>
                            <input type="text" id="plan-attach-search" class="form-control" autocomplete="off" maxlength="100"
                                   placeholder="Keresés az észleléseid címében">
                            <div class="list-group plan-attach__results" aria-live="polite"></div>
                        </div>
                    <?= Html::endForm() ?>
                </div>
            </div>
        </div>
    <?php } ?>
</div>
